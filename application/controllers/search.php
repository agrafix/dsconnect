<?php
class Search extends MY_Controller {
    
    public function __construct()
    {
        parent::__construct();
        
        if (!$this->User->is_loggedin()) {
            redirect("usercp/login");
        }
    }
    
    public function run()
    {
        if (!$this->input->post("search"))
        {
            show_error('Search string missing!');
            return;
        }
        
        $search = $this->input->post("search");
        
        // villages
        $this->db->select('id, name, x, y');
        $this->db->from($this->User->selected_world.'_village');
        $this->db->like('name', $search);
        $this->db->or_like('CONCAT(x, "|", y)', $search);
        $this->db->limit(50);
        
        $query = $this->db->get();
        
        $villages = array();
        foreach ($query->result_array() as $row) {
            $row['name'] = urldecode($row['name']);
            $villages[] = $row;
        }
        
        // players
        $this->db->select('id, name, points');
        $this->db->from($this->User->selected_world.'_player');
        $this->db->like('name', $search, 'after');
        $this->db->limit(50);
        
        $query = $this->db->get();
        
        $players = array();
        foreach ($query->result_array() as $row) {
            $row['name'] = urldecode($row['name']);
            $players[] = $row;
        }
        
        // allys
        $this->db->select('id, name, tag, members');
        $this->db->from($this->User->selected_world.'_ally');
        $this->db->like('name', $search, 'after');
        $this->db->or_like('tag', $search);
        $this->db->limit(50);
        
        $query = $this->db->get();
        
        $allys = array();
        foreach ($query->result_array() as $row) {
            $row['name'] = urldecode($row['name']);
            $row['tag'] = urldecode($row['tag']);
            $allys[] = $row;
        }
        
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(array(
                         'villages' => $villages, 
                         'players' => $players,
                         'allys' => $allys
                     )));
    }
    
    public function index()
    {
        $this->lang->load('search', $this->selected_lang);
        $this->lang->load('profile', $this->selected_lang);
        $this->load->view('search');
    }
    
}
?>
