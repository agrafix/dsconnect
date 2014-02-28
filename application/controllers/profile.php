<?php
class Profile extends MY_Controller {
    
    public function __construct()
    {
        parent::__construct();
        
        if (!$this->User->is_loggedin()) {
            redirect('usercp/login');
        }
        
        $this->load->model('Posts_model', 'Posts');
        $this->lang->load('profile', $this->selected_lang);
    }
    
    public function follow_state()
    {
        $type_id = $this->input->post("id");
        $type = $this->input->post("type");
        $new_state = $this->input->post("state");
        
        if (!is_numeric($type_id) 
                || !in_array($type, array("player", "village", "ally"))) {
            show_error('Invalid input data', 500);
            return;
        }
        
        $s = false;
        
        if ($new_state == 'follow')
        {
            $this->db->insert('follow_state', array(
                'user_id' => $this->User->id(),
                'type' => $type,
                'type_id' => $type_id,
                'world' => $this->User->selected_world
            ));
            
            $s = true;
        }
        else
        {
            $this->db->delete('follow_state', array(
                'user_id' => $this->User->id(),
                'type' => $type,
                'type_id' => $type_id,
                'world' => $this->User->selected_world
            ));
        }
        
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(array('status' => $s)));
    }
    
    public function lists($type='none', $id=0, $page=1)
    {
        if (!in_array($type, array('ally-members', 'ally-villages', 'player-villages'))
                || !is_numeric($id)) {
            show_404();
            return;
        }
        
        $total_rows = 0;
        $output_rows = array();
        
        $data['type'] = $type;
        
        if ($type == 'player-villages') {
            $query = $this->db->query('SELECT 
                COUNT(v.id) as amount
            FROM
                '.$this->User->selected_world.'_village AS v,
                '.$this->User->selected_world.'_player AS p
            WHERE 
                p.id = ? AND v.tribe = p.id', array($id));
            
            $row = $query->row();
            
            $total_rows = $row->amount;  
            
            // info
            $query = $this->db->query('SELECT * FROM '.$this->User->selected_world.'_player 
                WHERE id = ?', array($id));
            
            if ($query->num_rows() > 0) {
                $data['player'] = $query->row_array();
            }
            else {
                show_404();
                return;
            }
        }
        elseif ($type == 'ally-villages') {
            $query = $this->db->query('SELECT 
                COUNT(v.id) as amount
            FROM
                '.$this->User->selected_world.'_village AS v,
                '.$this->User->selected_world.'_player AS p,
                '.$this->User->selected_world.'_ally AS a  
            WHERE 
                a.id = ? AND v.tribe = p.id AND p.ally = a.id', array($id));
            
            $row = $query->row();
            
            $total_rows = $row->amount; 
            
            // info
            $query = $this->db->query('SELECT * FROM '.$this->User->selected_world.'_ally 
                WHERE id = ?', array($id));
            
            if ($query->num_rows() > 0) {
                $data['ally'] = $query->row_array();
            }
            else {
                show_404();
                return;
            }
        }
        else { // ally-members
            $query = $this->db->query('SELECT 
                COUNT(p.id) as amount
            FROM
                '.$this->User->selected_world.'_player AS p,
                '.$this->User->selected_world.'_ally AS a
            WHERE 
                a.id = ? AND p.ally = a.id', array($id));
            
            $row = $query->row();
            
            $total_rows = $row->amount;
            
            // info
            $query = $this->db->query('SELECT * FROM '.$this->User->selected_world.'_ally 
                WHERE id = ?', array($id));
            
            if ($query->num_rows() > 0) {
                $data['ally'] = $query->row_array();
            }
            else {
                show_404();
                return;
            }
        }
        
        $this->load->library('pagination');
        
        $config['base_url'] = site_url('profile/lists/'.$type.'/'.$id.'/');
        $config['uri_segment'] = 5;
        $config['total_rows'] = $total_rows;
        $config['per_page'] = 50;
        $config['use_page_numbers'] = true;
        $config['cur_page'] = $page;
        
        $this->pagination->initialize($config);
        
        $data['pagination'] = $this->pagination->create_links();
        
        if ($type == 'player-villages') {           
            $data['villages'] = array();
            
            $query = $this->db->query('SELECT 
                v.name, v.id, v.x, v.y, v.points
             FROM
                '.$this->User->selected_world.'_village AS v,
                '.$this->User->selected_world.'_player AS p
            WHERE 
                p.id = ? AND v.tribe = p.id
            ORDER BY 
                v.points DESC
            LIMIT
                '.(50 * ($page-1)).',50', array($id));
            
            foreach ($query->result_array() as $v) {
                $data['villages'][] = $v;
            }
            
            $this->load->view("profile/lists/village", $data);
        }
        
        elseif ($type == 'ally-villages')
        {
            $data['villages'] = array();
            
            $query = $this->db->query('SELECT 
                v.name, v.id, v.x, v.y, v.points
             FROM
                '.$this->User->selected_world.'_village AS v,
                '.$this->User->selected_world.'_player AS p,
                '.$this->User->selected_world.'_ally AS a    
            WHERE 
                a.id = ? AND v.tribe = p.id AND p.ally = a.id
            ORDER BY 
                v.points DESC
            LIMIT
                '.(50 * ($page-1)).',50', array($id));
            
            foreach ($query->result_array() as $v) {
                $data['villages'][] = $v;
            }
            
            $data['player'] = $data['ally'];
            
            $this->load->view("profile/lists/village", $data);
        }
        else {
            $data['members'] = array();
            
            $query = $this->db->query('SELECT 
                p.name, p.id, p.points
             FROM
                '.$this->User->selected_world.'_player AS p,
                '.$this->User->selected_world.'_ally AS a    
            WHERE 
                a.id = ? AND p.ally = a.id
            ORDER BY 
                p.points DESC
            LIMIT
                '.(50 * ($page-1)).',50', array($id));
            
            foreach ($query->result_array() as $v) {
                $data['members'][] = $v;
            }
            
            $this->load->view("profile/lists/member", $data);
        }
    }
    
    public function player($id) 
    {
        $this->show('player', $id);
    }
    
    public function ally($id)
    {
        $this->show('ally', $id);
    }
    
    public function village($id)
    {
        $this->show('village', $id);
    }
    
    private function show($type='player', $id=0)
    {
        $q = $this->db->query('SELECT * FROM '.$this->User->selected_world.'_'.$type.' 
            WHERE id = ? LIMIT 1', array($id));
        
        if ($q->num_rows() == 0) {
            show_404();
            return;
        }
        
        $data['type'] = $type;
        $data['id'] = $id;
        
        $data['file'] = $q->row_array();
        
        $data['posts'] = $this->Posts->get_visible_posts($type, $id);
        
        if ($type == 'player') {
            $q = $this->db->query('SELECT * FROM '.$this->User->selected_world.'_ally 
                WHERE id = '.$data['file']['ally']);
            
            if ($q->num_rows() == 0) {
                $data['ally_info'] = array();
            }else {
                $data['ally_info'] = $q->row_array();
            }
        }
        elseif ($type == 'village') {
            $q = $this->db->query('SELECT * FROM '.$this->User->selected_world.'_player 
                WHERE id = '.$data['file']['tribe']);
            
            if ($q->num_rows() == 0) {
                $data['player_info'] = array();
            }else {
                $data['player_info'] = $q->row_array();
            }
        }
        
        // calculate map data
        $cx = 0;
        $cy = 0;
        
        $scale = 10;
        $displayRange = ceil((300 / $scale) / 2) + 1;
        
        $vills = array();
        
        if ($type == 'player') {
            $scale = 5;
            $displayRange = ceil((300 / $scale) / 2) + 1;
        
            $q = $this->db->query('SELECT 
 AVG(v.x) as x, AVG(v.y) AS y
FROM
 '.$this->User->selected_world.'_village as v,
 '.$this->User->selected_world.'_player as p
WHERE
 p.id = ? AND v.tribe = p.id', array($id));
            
            $d = $q->row();
            $cx = $d->x;
            $cy = $d->y;
        }
        elseif ($type == 'ally') {
            $scale = 3;
            $displayRange = ceil((300 / $scale) / 2) + 1;
            
            $q = $this->db->query('SELECT 
 AVG(v.x) as x, AVG(v.y) AS y
FROM
 '.$this->User->selected_world.'_village as v,
 '.$this->User->selected_world.'_player as p,
 '.$this->User->selected_world.'_ally as a
WHERE
 a.id = ? AND v.tribe = p.id AND p.ally = a.id', array($id));
            
            $d = $q->row();
            $cx = $d->x;
            $cy = $d->y;
        }
        else {
            $cx = $data['file']['x'];
            $cy = $data['file']['y'];
        }
        
        $data['map']['center'] = array("x" => (int)$cx, "y" => (int)$cy);
        $data['map']['scale'] = $scale;
        
        // check follow state
        $q = $this->db->query("SELECT * FROM follow_state WHERE user_id = "
                .$this->User->id()." AND type = '".$type."' AND type_id = ?", array($id));
        if ($q->num_rows() == 0) {
            $data['follow'] = array();
        }
        else {
            $data['follow'] = $q->row_array();
        }
        
        $this->load->view('profile/generic', $data);
    }
}
?>
