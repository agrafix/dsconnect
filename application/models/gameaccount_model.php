<?php
class Gameaccount_model extends CI_Model {
    
    public $id;
    public $name;
    public $ally_id;
    public $villages;
    public $points;
    public $rank;
    
    private $ga_loaded = false;
    
    public function __construct()
    {
        parent::__construct();
        
        // search for game-account
        $q = $this->db->query('SELECT 
               * 
        FROM
            linked_accounts as l, '.$this->User->selected_world.'_player as p
        WHERE
            l.user_id = ? AND 
            l.ds_id = p.id', array($this->User->id()));
        
        if ($q->num_rows() != 0) {
            $r = $q->row();
            
            $this->id = $r->id;
            $this->name = urldecode($r->name);
            $this->ally_id = $r->ally;
            $this->villages = $r->villages;
            $this->points = $r->points;
            $this->rank = $r->rank;
            
            $this->ga_loaded = true;
        }
    }
    
    public function getAllConnected() {
        $q = $this->db->query('SELECT 
               l.world as world, l.id as linkid, l.ds_id as ds_id
        FROM
            linked_accounts as l
        WHERE
            l.user_id = ?', array($this->User->id()));
        
        $accounts = array();
        
        foreach ($q->result_array() as $row) {
            $q2 = $this->db->query('SELECT name FROM '.$row['world'].'_player WHERE 
                id = '.$row['ds_id']);
            
            $player = $q2->row();
            
            $accounts[] = array('linkid' => $row['linkid'], 'world' => $row['world'], 
                'ds_id' => $row['ds_id'], 'name' => $player->name);
            
            $q2->free_result();
        }
        
        return $accounts;
    }
    
    public function is_connected() {
        return $this->ga_loaded;
    }
}
?>
