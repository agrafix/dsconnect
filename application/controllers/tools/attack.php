<?php

class Attack extends MY_Controller {

    /**
     * tribalwars units
     * @var array
     */
    private $tw_units;
    /**
     * speed of world
     * @var float
     */
    private $tw_speed;
    /**
     * relative unit speed to world
     * @var float
     */
    private $tw_unit_speed;
    /**
     * actual unit speed
     * @var float
     */
    private $tw_troop_speed;

    public function __construct()
    {
        parent::__construct();

        $this->load->library('tw_import');
        $this->load->library('tw_config');

        $this->lang->load('attack', $this->selected_lang);

        $this->tw_config->set_world($this->User->selected_world);

        $this->tw_units = $this->tw_config->get_all('unit_info');
        if (isset($this->tw_units["militia"]))
        {
            unset($this->tw_units["militia"]);
        }
        $this->tw_speed = $this->tw_config->get('speed');
        $this->tw_unit_speed = $this->tw_config->get('unit_speed');

        $this->tw_troop_speed = $this->tw_unit_speed * $this->tw_speed;
    }

    public function create()
    {
        $this->requireLogin();

        if ($this->input->post("desc"))
        {
            $this->db->insert('attack_plans', array(
                'desc' => $this->input->post("desc"),
                'created_at' => time(),
                'user_id' => $this->User->id(),
                'world' => $this->User->selected_world,
                'last_arrival' => time()
            ));

            redirect('tools/attack/edit/'.$this->db->insert_id());
        }
    }

    public function view($id=0)
    {
        
    }

    public function delete($id=0)
    {
        $this->requireLogin();
        
        $this->db->delete('attack_plans', array(
            'user_id' => $this->User->id(),
            'id' => $id
        ));
        
        redirect('tools/attack');
    }

    public function edit($id=0, $_action='', $_action_id=0)
    {
        $this->requireLogin();

        $this->load->library('tw_import');
        
        $this->db->from('attack_plans');
        $this->db->where('user_id', $this->User->id());
        $this->db->where('id', $id);
        $query = $this->db->get();

        if ($query->num_rows() == 0)
        {
            show_404();
        }

        $data['plan'] = $query->row_array();

        $data['current_tab'] = 'overview';
        $data['post_error'] = false;
        $data['wiz_step'] = 1;
        $data['wiz_errors'] = array();

        // handle tabs
        if ($this->input->post("tab"))
        {
            $this->lang->load("form_validation", $this->selected_lang);
            $this->load->library('form_validation');

            if ($this->input->post("tab") == "add")
            {
                if (!$this->tab_add($id))
                {
                    $data['current_tab'] = 'add';
                    $data['post_error'] = true;
                }
                else
                {
                    redirect('tools/attack/edit/'.$id);
                }
            }
            elseif ($this->input->post("tab") == "wizard")
            {
                $_d = $this->tab_wizard($id);
                $data['wiz_step'] = $_d['step'];
                $data['wiz_errors'] = $_d['errors'];

                $data['current_tab'] = 'wizard';
            }
        }

        // handle actions
        if ($_action != "")
        {
            switch ($_action)
            {
                case "delete_action":
                    $this->db->delete('attack_plans_action', array('id' => $_action_id, 'plan_id' => $id));
                    redirect('tools/attack/edit/'.$id);
                    break;
            }
        }

        // fetch attack plan
        $q = $this->db->query("
        SELECT
            a.*, 
            CONCAT(svill.name, ' (', svill.x, '|', svill.y, ')') AS start_vname,
            CONCAT(tvill.name, ' (', tvill.x, '|', tvill.y, ')') AS stop_vname,
            CONCAT(svill.x, ', ', svill.y) AS start_vcoords,
            CONCAT(tvill.x, ', ', tvill.y) AS stop_vcoords,
            splayer.id AS start_pid,
            splayer.name AS start_pname,
            tplayer.id AS stop_pid,
            tplayer.name AS stop_pname
        FROM
            attack_plans_action as a
        LEFT JOIN 
            ".$this->User->selected_world."_village AS svill ON (svill.id = a.start_village_id)
        LEFT JOIN 
            ".$this->User->selected_world."_village AS tvill ON (tvill.id = a.stop_village_id)
        LEFT JOIN 
            ".$this->User->selected_world."_player AS splayer ON (splayer.id = svill.tribe)
        LEFT JOIN 
            ".$this->User->selected_world."_player AS tplayer ON (tplayer.id = tvill.tribe)
        WHERE
            a.plan_id = ".$id."
        ");

        $data['actions'] = array();

        if ($q->num_rows() > 0)
        {
            foreach ($q->result_array() as $row)
            {
                $data['actions'][] = $row;
            }
        }

        // calculate map center
        $query = $this->db->query('
        SELECT
            AVG(v.x) AS cx,
            AVG(v.y) AS cy
        FROM
            '.$this->User->selected_world.'_village AS v
        INNER JOIN
            attack_plans_action AS act ON (act.plan_id = '.$id.' AND (act.start_village_id = v.id OR act.stop_village_id = v.id))
        ');

        $data['map_center'] = $query->row_array();

        $data['units'] = $this->tw_units;
        $data['unit_speed'] = $this->tw_troop_speed;

        $this->load->view('attack/edit', $data);
    }

    private function tab_wizard($plan_id)
    {
        $errors = array();

        $step = ($this->input->post("step") ? $this->input->post("step") : 1);
        if (!in_array($step, array(1, 2, 3, 4, 5)))
        {
            $step = 1;
        }

        $wizardData = $this->session->userdata('attack_wizard');

        switch ($step)
        {
            case 1:
                break;

            case 2:
                // reset
                $this->session->unset_userdata('attack_wizard');
                $wizardData = array();
                // end

                $lines = explode("\n", $this->input->post("villages"));
                $count = 1;
                foreach ($lines as $line)
                {
                    if (!preg_match('#^([0-9]{1,3})\|([0-9]{1,3}),([FOS]{1})#i', $line, $m))
                    {
                        $errors[] = sprintf(lang('attack_wizard_line_error'), $count);
                        $step = 1;
                        break;
                    }

                    // lookup village
                    $q = $this->db->get_where($this->User->selected_world.'_village', array(
                                'x' => $m[1],
                                'y' => $m[2]
                            ));

                    if ($q->num_rows() == 0)
                    {
                        $errors[] = sprintf(lang('attack_wizard_village_error'), $m[1]."|".$m[2]);
                        $step = 1;
                        break;
                    }

                    $vill = $q->row_array();
                    $vill['action_type'] = strtoupper($m[3]);

                    $wizardData['targets'][] = $vill;

                    $count++;
                }

                uasort($wizardData['targets'], function($a, $b)
                        {
                            $def = array('S' => 1, 'O' => 2, 'F' => 3);

                            if ($def[$a['action_type']] < $def[$b['action_type']])
                            {
                                return -1;
                            }
                            elseif ($def[$a['action_type']] > $def[$b['action_type']])
                            {
                                return 1;
                            }
                            else
                            {
                                return 0;
                            }
                        });

                break;

            case 3:
                $regexpr = '\((?P<x>[0-9]{1,3})\|(?P<y>[0-9]{1,3})\) K[0-9]*\s*[0-9]* \([0-9]*\)';
                foreach ($this->tw_units as $unit => $data)
                {
                    $regexpr .= '\s*(?P<'.$unit.'>[0-9]*)';
                }

                // search for 'em
                if (!preg_match_all('#'.$regexpr.'#i', $this->input->post('own_villages'), $matches))
                {
                    $errors[] = lang('attack_wizard_village_none');
                    $step = 1;
                    break;
                }
                foreach ($matches["x"] as $k => $m)
                {
                    $villData = array();

                    $villData["x"] = $matches["x"][$k];
                    $villData["y"] = $matches["y"][$k];

                    $total = 0;

                    foreach ($this->tw_units as $unit => $data)
                    {
                        $villData[$unit] = $matches[$unit][$k];

                        $total += $villData[$unit];
                    }

                    if ($total <= 0)
                    {
                        break;
                    }
                    // check if village exists
                    $query = $this->db->get_where($this->User->selected_world.'_village', array(
                                'x' => $villData["x"],
                                'y' => $villData["y"]
                            ));

                    if ($query->num_rows() == 0)
                    {
                        break;
                    }

                    $data = $query->row_array();

                    $villData["id"] = $data["id"];
                    $villData["name"] = $data["name"];

                    $wizardData["own_villages"][] = $villData;
                }

                break;

            case 4:
                $types = array('F', 'O', 'S');
                foreach ($types as $t)
                {
                    $data = $this->input->post($t);

                    $per_target = (int) $data['per_target'];
                    $units = array();
                    $slowest = 0;

                    foreach ($this->tw_units as $unit => $unit_data)
                    {
                        $units[$unit] = (int) $data[$unit];

                        $s = $unit_data['speed'] * $this->tw_troop_speed;

                        if ($units[$unit] < 0)
                        {
                            $units[$unit] = 0;
                        }

                        if ($s > $slowest && $units[$unit] > 0)
                        {
                            $slowest = $s;
                        }
                    }

                    $wizardData["types"][$t] = array('per_target' => $per_target, 'units' => $units,
                        'slowest' => $slowest);
                }

                // here comes the big calculation...
                $longestRuntime = 0;

                foreach ($wizardData['targets'] as $t)
                {
                    $slowest = $wizardData["types"][$t['action_type']]['slowest'];

                    foreach ($wizardData['own_villages'] as $k => $s)
                    {
                        $wizardData['own_villages'][$k]['to_target'] = $slowest *
                                sqrt(pow($t['x'] - $s['x'], 2) + pow($t['y'] - $s['y'], 2));
                    }

                    uasort($wizardData['own_villages'], function($a, $b)
                            {
                                if ($a['to_target'] < $b['to_target'])
                                {
                                    return -1;
                                }
                                elseif ($a['to_target'] > $b['to_target'])
                                {
                                    return 1;
                                }
                                return 0;
                            });

                    $actions = array();


                    $count = 0;
                    $mcount = 0;
                    $type_info = $wizardData["types"][($t['action_type'] == 'S' ? 'O' : $t['action_type'])];

                    for ($i = 0; $i < $type_info['per_target'] * 2; $i++)
                    {
                        foreach ($wizardData['own_villages'] as $k => $vill)
                        {
                            if ($count >= $type_info['per_target'])
                            {
                                if ($t['action_type'] == 'S' && $mcount == 0)
                                {
                                    $count = 0;
                                    $mcount = 1;
                                    $type_info = $wizardData["types"]["S"];
                                }
                                else
                                {
                                    break;
                                }
                            }
                            $action = array();

                            foreach ($type_info['units'] as $name => $min_amount)
                            {
                                if ($vill[$name] < $min_amount)
                                {
                                    continue;
                                }

                                $wizardData['own_villages'][$k][$name] -= $min_amount;
                                $action[$name] = $min_amount;
                            }

                            if (!empty($action))
                            {
                                $count++;
                            }

                            $action['note'] = '';
                            $action['start_village_id'] = $vill["id"];
                            $action['stop_village_id'] = $t["id"];
                            if ($t['action_type'] == 'F')
                            {
                                $action['type'] = 'fake';
                            }
                            elseif ($t['action_type'] == 'S')
                            {
                                $action['type'] = 'snob';
                            }
                            else
                            {
                                $action['type'] = 'attack';
                            }
                            $action['plan_id'] = $plan_id;
                            $action['runtime'] = $vill['to_target'];

                            if ($action['runtime'] > $longestRuntime)
                            {
                                $longestRuntime = $action['runtime'];
                            }

                            $actions[] = $action;
                        }
                    }
                }

                // now figure out best arrival time
                $arrival = time() + 600 + $longestRuntime * 60;

                if (date('H', $arrival) <= 8)
                {
                    $dif = 8 - date('H', $arrival);
                    $arrival += $dif * 3600 + mt_rand(60, 120);
                }

                foreach ($actions as $k => $a)
                {
                    $actions[$k]["arrival_time"] = floor($arrival);
                    $actions[$k]["start_time"] = floor($arrival - $a["runtime"] * 60);

                    unset($actions[$k]["runtime"]);

                    $this->db->insert('attack_plans_action', $actions[$k]);
                }
                
                $this->db->where('id', $plan_id);
                $this->db->update('attack_plans', array(
                    'last_arrival' => floor($arrival)
                ));

                // redir
                redirect("tools/attack/edit/".$plan_id);

                break;
        }

        $this->session->set_userdata('attack_wizard', $wizardData);

        return array("step" => $step, "errors" => $errors);
    }

    private function tab_add($plan_id)
    {
        // check if valid type is given
        if (!in_array($this->input->post("type"), array("attack", "fake", "snob", "def")))
        {
            show_error('Invalid selectbox input!', 500);
        }

        // check coords
        $this->form_validation->set_rules('start_vill', 'lang:attack_start_vill', 'required|trim|callback_coord_check|callback_village_exists');
        $this->form_validation->set_rules('target_vill', 'lang:attack_target_vill', 'required|trim|callback_coord_check|callback_village_exists');

        $this->form_validation->set_rules('arrival', 'lang:attack_arrival', 'required|trim|callback_datetime_check');

        $this->form_validation->set_rules('note', 'lang:attack_note', 'max_length[500]');

        foreach ($this->tw_units as $unit => $data)
        {
            $this->form_validation->set_rules($unit, '<img src="'.site_url('static/image/ds/unit_'.$unit.'.png').'" alt="" />', 'required|trim|is_natural');
        }


        if (!$this->form_validation->run())
        {
            return false;
        }

        // lookup village ids
        $svill = $this->input->post('start_vill');
        $tvill = $this->input->post('target_vill');

        // dist between villages
        $dist = sqrt(pow($svill['x'] - $tvill['x'], 2) + pow($svill['y'] - $tvill['y'], 2));

        // insert into db
        $db_arr = array('plan_id' => $plan_id);

        $slowest = 0;

        foreach ($this->tw_units as $unit => $data)
        {
            $db_arr[$unit] = $this->input->post($unit);
            $s = $data['speed'] * $this->tw_troop_speed;

            if ($db_arr[$unit] > 0 && $s > $slowest)
            {
                $slowest = $s;
            }
        }

        // walktime
        $wtime = ($dist * $slowest) * 60;

        // arrival time
        $arrival = $this->input->post('arrival');

        $db_arr['start_time'] = $arrival - $wtime; // calc
        $db_arr['arrival_time'] = $arrival;

        $db_arr['note'] = $this->input->post('note');

        $db_arr['start_village_id'] = $svill['id'];
        $db_arr['stop_village_id'] = $tvill['id'];

        $db_arr['type'] = $this->input->post('type');

        // insert
        $this->db->insert('attack_plans_action', $db_arr);

        $this->db->where('id', $plan_id);
        $this->db->update('attack_plans', array(
            'last_arrival' => $arrival
        ));

        return true;
    }

    public function datetime_check($datetime)
    {
        $status = preg_match('#^([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{2,4}) \- ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})$#', $datetime, $m);

        if (!$status)
        {
            $this->form_validation->set_message('datetime_check', lang('invalid_date'));
            return FALSE;
        }

        return mktime($m[4], $m[5], $m[6], $m[2], $m[1], $m[3]);
    }

    public function village_exists($xy)
    {
        $a = explode("|", $xy);

        $q = $this->db->get_where($this->User->selected_world.'_village', array(
                    'x' => $a[0],
                    'y' => $a[1]
                ));

        if ($q->num_rows() == 0)
        {
            $this->form_validation->set_message('village_exists', lang('invalid_village'));
            return FALSE;
        }

        return $q->row_array();
    }

    public function coord_check($xy)
    {
        $status = preg_match('#^[0-9]{1,3}\|[0-9]{1,3}$#', $xy);

        if (!$status)
        {
            $this->form_validation->set_message('coord_check', lang('invalid_coords'));
            return FALSE;
        }

        return TRUE;
    }

    public function index()
    {
        $this->requireLogin();

        $this->db->from('attack_plans');
        $this->db->where('user_id', $this->User->id());
        $query = $this->db->get();

        $data['plans'] = array();

        if ($query->num_rows() > 0)
        {
            foreach ($query->result_array() as $row)
            {
                $data['plans'][] = $row;
            }
        }

        $this->load->view('attack/overview', $data);
    }

}

?>
