<?php
class Posts_model extends CI_Model {
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function make_post($user_id, $post, $world, 
                              $type_id='-1', $type_name='', $type='player',
                              $visible_everyone=0, $visible_ally_id=-1, $visible_player_id=-1,
                              $time=-1)
    {
        $this->db->insert('posts', array(
           'user_id' => $user_id,
            'post' => $post,
            'world' => $world,
            'visible_world' => $world,
            'type_id' => $type_id,
            'type_name' => $type_name,
            'type' => $type,
            'visible_everyone' => $visible_everyone,
            'visible_ally_id' => $visible_ally_id,
            'visible_player_id' => $visible_player_id,
            'time' => ($time == -1 ? time() : $time)
        ));
        
        return $this->db->insert_id();
    }
    
    public function get_visible_posts($type='all', $type_id=-1, $only_follow=false, $since=0, $sel_id=-1) 
    {
        $w = $this->User->selected_world;
        $pid = $this->Game_Account->id;
        $aid = $this->Game_Account->ally_id;
        
        if (!$this->Game_Account->is_connected()) {
            $pid = -1;
            $aid = -1;
        }
        
        $query = $this->db->query("
        SELECT      
            p.time, p.post, p.type_name, p.type, p.type_id, p.user_id, p.id, 
            COUNT(c.id) AS comment_count
        FROM
            posts AS p
        LEFT JOIN
            comments AS c ON (c.post_id = p.id)
        ".($only_follow ? "
        INNER JOIN
            follow_state AS f ON (f.user_id = ".$this->User->id()." AND f.type = p.type AND f.type_id = p.type_id AND f.world = '".$w."')
        " : '')."    
        WHERE
            (
                (p.visible_world = '".$w."' AND p.visible_ally_id = -1 AND p.visible_player_id = -1)
               OR
                (p.visible_everyone = 1)
               OR
                (p.visible_world = '".$w."' AND p.visible_ally_id = '".$aid."')
               OR
                (p.visible_world = '".$w."' AND p.visible_player_id = '".$pid."')
               OR
                (p.user_id = '".$this->User->id()."')
            )
                
                
            ".($type != 'all' ? ' AND p.type = "'.$type.'" ' : '')."
            ".($type_id != -1 ? ' AND p.type_id = "'.$type_id.'" ' : '')."
            ".($sel_id != -1 ? ' AND p.id = "'.$sel_id.'" ' : '')."
            ".($since != 0 ? ' AND p.time > "'.$since.'" ': '')." 

        GROUP BY p.id
        
        ORDER BY p.time DESC

        LIMIT 50
        ");
        
        $array = array();
        
        foreach ($query->result_array() as $row) {
            $array[] = $row;
        }
        
        return $array;
    }
    
}
?>
