<?php
class Backend extends CI_Controller {

    public function auth()
    {
        $private_key = $this->config->item("tw_private_key");
        $servers = $this->config->item("tw_worlds");

        $sid = explode("|", $this->input->get("sid"));
        $username = ($this->input->get("username") != false ? dsAPI::$username : '');
        $hash = $this->input->get("hash");

        $checkHash = md5($this->input->get("sid").$username.$private_key);

        $this->output->set_content_type('text/plain');

        if ($checkHash != $hash || !in_array($sid[0], $servers))
        {
            $this->output->append_output(site_url('usercp/accounts/error/1'));
            return;
        }

        // check if account exists on specified world
        $query = $this->db->query("SELECT * FROM ".$sid[0]."_player WHERE name = '".urlencode(utf8_encode($username))."'");

        if ($query->num_rows() == 0)
        {
            $this->output->append_output(site_url('usercp/accounts/error/2'));
            return;
        }

        // save dsid
        $row = $query->row();
        $dsID = $row->id;

        // check if contains valid api_hash
        $query = $this->db->query("SELECT * FROM users WHERE api_hash = ?", array($sid[1]));
        if ($query->num_rows() == 0)
        {
            $this->output->append_output(site_url('usercp/accounts/error/3'));
            return;
        }

        $row = $query->row();
        $userID = $row->id;

        // store in db
        $this->db->insert('linked_accounts', array(
            'user_id' => $userID,
            'ds_id' => $dsID,
            'world' => $sid[0]
        ));

        // follow myself
        $this->db->insert('follow_state', array(
            'user_id' => $userID,
            'type' => 'player',
            'type_id' => $dsID,
            'world' => $sid[0]
        ));

        $this->output->append_output(site_url('usercp/accounts'));
    }

    /**
     * 
     * @deprecated no longer needed, replaced by dsimport.py
     */
    public function import()
    {
        die('deprecated');
        
        $this->output->set_content_type('text/plain');

        set_time_limit(0);
        ini_set("memory_limit", "512M");
        ignore_user_abort(1);

        $this->load->library('tw_import');

        $to_import = array("player", "ally", "conquer", "village");

        $servers = $this->config->item("tw_worlds");

        foreach ($servers as $server)
        {
            $this->output->append_output("[+] Importing server $server\n");

            $this->tw_import->start_import($server);

            foreach ($to_import AS $importing)
            {
                $this->output->append_output("[|] Downloading $importing\n");
                $this->tw_import->cache_file($importing);

                $this->output->append_output("[|] Installing tables for $importing if needed\n");
                $this->tw_import->install_table($importing);

                $this->output->append_output("[-] Importing data $importing\n");
                $this->tw_import->load_into_db($importing);

                $this->output->append_output("[|] Clearing cache $importing\n");
                $this->tw_import->clear_cache($importing);
            }
        }
    }

    public function generate_conquer_posts()
    {
        $this->output->set_content_type('text/plain');

        set_time_limit(0);
        ini_set("memory_limit", "512M");
        ignore_user_abort(1);

        $this->load->library('Redis');
        $this->redis->connect();

        $servers = $this->config->item("tw_worlds");
        $this->load->model('Posts_model', 'Posts');
        $this->load->library('tw_import');

        $minTime = time() - 23 * 3600;

        foreach ($servers as $server)
        {
            $l = (substr($server, 0, 2) == 'de' || substr($server, 0, 2) == 'ch' ? 
                                          'german' : 'english');
            $lang_data = $this->lang->load('backend', $l, true);
            
            $count = 0;

            $q = $this->db->query('SELECT time FROM last_conquer_import WHERE world = "'.$server.'"');
            if ($q->num_rows() == 0)
            {
                $time = $minTime;
                $this->db->insert('last_conquer_import', array(
                    'world' => $server,
                    'time' => 0
                ));
            }
            else
            {
                $row = $q->row_array();
                $time = ($row['time'] < $minTime ? $minTime : $row['time']);
            }
            $q->free_result();

            // api call
            $host = Tw_import::get_tw_host($server);
            
            if ($host == '' || $host == null || $host == false) {
                continue;
            } 

            $lines = file($host."/interface.php?func=get_conquer&since=".$time);

            foreach ($lines as $line)
            {
                $count++;

                // $village_id, $unix_timestamp, $new_owner, $old_owner 
                $parts = explode(",", trim($line));
                
                // add to database
                $this->db->insert($server.'_conquer', array(
                    'village_id' => $parts[0],
                    'unix_timestamp' => $parts[1],
                    'new_owner' => $parts[2],
                    'old_owner' => $parts[3]
                ));
                
                if ($parts[2] == $parts[3]) {
                    continue;
                }

                // get name of village
                $query = $this->db->query('SELECT id, name FROM '.$server.'_village WHERE id = ?', array($parts[0]));
                $village = $query->row_array();
                $query->free_result();

                // post on village wall
                $this->Posts->make_post(-1, sprintf($lang_data['backend_village_owner_change'], $parts[3], $parts[2]), $server, $village['id'], urldecode($village['name']), 'village', 0, -1, -1, $parts[1]);

                // post on new owner wall
                $query = $this->db->query('SELECT id, name, ally FROM '.$server.'_player WHERE id = ?', array($parts[2]));
                $owner = $query->row_array();
                $query->free_result();

                $this->Posts->make_post(-1, sprintf($lang_data['backend_player_win_village'], $village['id'], $parts[3]), $server, $owner['id'], urldecode($owner['name']), 'player', 0, -1, -1, $parts[1]);

                if ($owner['ally'] != 0)
                {
                    $query = $this->db->query('SELECT id, name FROM '.$server.'_ally WHERE id = ?', array($owner['ally']));

                    if ($query->num_rows() > 0)
                    {
                        $ally = $query->row_array();
                        $query->free_result();

                        $this->Posts->make_post(-1, sprintf($lang_data['backend_ally_win_village'], $parts[2], $village['id'], $parts[3]), $server, $ally['id'], urldecode($ally['name']), 'ally', 0, -1, -1, $parts[1]);
                    }
                }

                // post on old owner wall
                if ($parts[3] != 0)
                {
                    $query = $this->db->query('SELECT id, name, ally FROM '.$server.'_player WHERE id = ?', array($parts[3]));
                    $owner = $query->row_array();
                    $query->free_result();

                    if (isset($owner['id'])) {
                        $this->Posts->make_post(-1, sprintf($lang_data['backend_player_loose_village'], $village['id'], $parts[2]), $server, $owner['id'], urldecode($owner['name']), 'player', 0, -1, -1, $parts[1]); #

                        if ($owner['ally'] != 0)
                        {
                            $query = $this->db->query('SELECT id, name FROM '.$server.'_ally WHERE id = ?', array($owner['ally']));

                            if ($query->num_rows() > 0)
                            {
                                $ally = $query->row_array();
                                $query->free_result();

                                $this->Posts->make_post(-1, sprintf($lang_data['backend_ally_loose_village'], $parts[3], $village['id'], $parts[2]), $server, $ally['id'], urldecode($ally['name']), 'ally', 0, -1, -1, $parts[1]);
                            }
                        }
                    }
                }
                
                // update village table
                $this->db->where('id', $parts[0]);
                $this->db->update($server.'_village', array(
                    'tribe' => $parts[2]
                ));
            }

            $this->db->where('world', $server);
            $this->db->update('last_conquer_import', array(
                'time' => time()
            ));

            $this->output->append_output("Server ".$server." (Lang: ".$l.") imported ".$count." conquers since ".date('d.m.Y - H:i:s', $time)."\n");
        }

        $this->redis->disconnect();
    }

    public function generate_posts()
    {
        $this->output->set_content_type('text/plain');
        
        set_time_limit(0);
        ini_set("memory_limit", "512M");
        ignore_user_abort(1);

        $servers = $this->config->item("tw_worlds");
        $this->load->model('Posts_model', 'Posts');

        foreach ($servers as $server)
        {
            $l = (substr($server, 0, 2) == 'de' || substr($server, 0, 2) == 'ch' ? 
                                          'german' : 'english');
            $lang_data = $this->lang->load('backend', $l, true);
            
            $count = array('p' => 0, 'a' => 0, 'v' => 0);
            
            // players
            $q = $this->db->query('SELECT
                p.id, p.name, p.points, p.villages, p.rank, p.ally
            FROM
                '.$server.'_player as p,
                follow_state as f
            WHERE
                f.type = "player"
            AND
                f.world = "'.$server.'"
            AND
                f.type_id = p.id
            GROUP BY 
                p.id');

            if ($q->num_rows() > 0)
            {
                foreach ($q->result_array() as $row)
                {
                    $this->Posts->make_post(-1, sprintf($lang_data['backend_player_status'], number_format($row["points"], 0, ',', '.'), $row["villages"], $row["rank"], $row["ally"]), $server, $row['id'], urldecode($row['name']), 'player');
                    $count['p']++;
                }
            }
            
            // allys
            $q = $this->db->query('SELECT
                p.id, p.name, p.points, p.villages, p.rank, p.members
            FROM
                '.$server.'_ally as p,
                follow_state as f
            WHERE
                f.type = "ally"
            AND
                f.world = "'.$server.'"
            AND
                f.type_id = p.id
            GROUP BY 
                p.id');

            if ($q->num_rows() > 0)
            {
                foreach ($q->result_array() as $row)
                {
                    $this->Posts->make_post(-1, sprintf($lang_data['backend_ally_status'], number_format($row["points"], 0, ',', '.'), $row["villages"], $row["members"], $row["rank"]), $server, $row['id'], urldecode($row['name']), 'ally');
                    $count['a']++;
                }
            }
            
            // villages
            $q = $this->db->query('SELECT
                p.id, p.name, p.points, p.tribe
            FROM
                '.$server.'_village as p,
                follow_state as f
            WHERE
                f.type = "village"
            AND
                f.world = "'.$server.'"
            AND
                f.type_id = p.id
            GROUP BY 
                p.id');

            if ($q->num_rows() > 0)
            {
                foreach ($q->result_array() as $row)
                {
                    $this->Posts->make_post(-1, sprintf($lang_data['backend_village_status'], number_format($row["points"], 0, ',', '.'), $row["tribe"]), $server, $row['id'], urldecode($row['name']), 'village');
                
                    $count['v']++;
                }
            }
            
            $this->output->append_output('Server '.$server.' has generated '.$count['p'].' new player posts, '.$count['a'].' new ally posts, '.$count['v'].' new village posts'."\n");
        }
    }

}

?>
