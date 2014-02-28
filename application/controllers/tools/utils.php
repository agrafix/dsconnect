<?php
class Utils extends MY_Controller {
    
    public function __construct()
    {
        parent::__construct();
        
        if (!$this->User->is_loggedin()) {
            show_error('Login first.', 403);
        }
    }
    
    public function coords_to_id()
    {
        $cx = $this->input->post("x");
        $cy = $this->input->post("y");
        
        if (is_numeric($cx) && $cx >= 0 && $cx <= 1000
                && is_numeric($cy) && $cy >= 0 && $cy <= 1000) {
            
            $query = $this->db->query('SELECT 
                v.id, v.name
            FROM
                '.$this->User->selected_world.'_village as v
            WHERE
                v.x = ? AND v.y = ?', array($cx, $cy));
            
            $this->output->set_content_type('application/json');
            
            if ($query->num_rows() == 0) {
                $this->output->set_output(json_encode(array('error' => true)));
            }
            else {
                $r = $query->row();
                $this->output->set_output(json_encode(array('error' => false, 'id' => $r->id, 
                    'name' => urldecode($r->name))));
               
            } 
            
            return;
            
        }
        
        show_error('Invalid Input' , 500);
        return;
    }
    
    public function msgboard()
    {
        $type = $this->input->post("type");
        $type_id = $this->input->post("typeID");
        $only_follow = $this->input->post("only_follow");
        $since = $this->input->post("since");
        
        if (!is_numeric($since) || $since < 0 || $since > time()
             || !is_numeric($type_id) 
             || !in_array($type, array('all', 'ally', 'village', 'player'))) {
            show_404();
            return;
        }
        
        $this->load->model('Posts_model', 'Posts');
        
        $posts = $this->Posts->get_visible_posts($type, $type_id, $only_follow, $since);
        
        $json_posts = array();
        $ct = 0;
        
        foreach ($posts as $p)
        {
            $ct++;
            $json_posts[] = $this->load->view('messageboard/threadbit', $p, true);
        }
        
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(array(
                         'since' => ($ct == 0 ? 0 : time()),
                         'posts' => $json_posts,
                         'error' => false,
                         'count' => $ct
                     )));
    }
    
    public function autocomplete($type)
    {
        if (!in_array($type, array('player', 'ally'))
                || !$this->input->get('term'))
        {
            show_404();
            return;
        }
        
        $term = $this->input->get('term');
        
        $this->db->select('id, name');
        $this->db->from($this->User->selected_world.'_'.$type);
        $this->db->like('name', $term, 'after');
        $this->db->limit(20);
        
        $query = $this->db->get();
        
        $o = array();
        
        foreach ($query->result_array() as $r) {
            $o[] = array('id' => $r['id'], 'value' => urldecode($r['name']), 
                'label' => urldecode($r['name']));
        }
        
        $this->output->set_content_type('application/json')
                ->set_output(json_encode($o));
    }
    
}
?>
