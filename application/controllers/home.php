<?php
class Home extends MY_Controller {
    
    public function __construct()
    {
        parent::__construct();
        
        if ($this->User->is_loggedin()) {
            $this->load->model('Posts_model', 'Posts');
        }
    }
    
    public function store_post() {
        if (!$this->User->is_loggedin() || !$this->Game_Account->is_connected()) {
            set_status_header('403', 'Login and connected Account required.');
            return;
        }
        
        $this->output->set_content_type('application/json');

        $type = ($this->input->post("type") ? $this->input->post("type") : 'player');
        $type_id = ($this->input->post("type_id") ? $this->input->post("type_id") : $this->Game_Account->id);
        
        if (!in_array($type, array("player", "ally", "village")) || 
                !is_numeric($type_id)) {
            set_status_header('403', 'Invalid type/type_id');
            return;
        }
        
        // lookup name
        $q = $this->db->query('SELECT name FROM '.$this->User->selected_world.'_'.$type.' WHERE id = ?', array($type_id));
        if ($q->num_rows() == 0) {
            $this->output->set_output(json_encode(array("error" => true)));
            return;
        }
        $row = $q->row();
        $type_name = urldecode($row->name);
        
        // visibility settings
        $visible_everyone = ($this->input->post('visible') == 'all' ? 1 : 0);
        $visible_ally_id = ($this->input->post('visible') == 'ally' ? $this->Game_Account->ally_id : -1);
        $visible_player_id = ($this->input->post('visible') == 'me' ? $this->Game_Account->id : -1);
        
        // post
        if (!$this->input->post('post') 
                || trim($this->input->post('post')) == '') {
            $this->output->set_output(json_encode(array("error" => true)));
            return;
        }
        $post = $this->input->post('post');
        
        // store post
        $postID = $this->Posts->make_post($this->User->id(), $post, $this->User->selected_world, 
                              $type_id, $type_name, $type,
                              $visible_everyone, $visible_ally_id, $visible_player_id);
        
        // make threadbit
        $data['id'] = $postID;
        $data['post'] = $post;
        $data['time'] = time();
        $data['type'] = $type;
        $data['type_name'] = $type_name;
        $data['type_id'] = $type_id;
        $data['user_id'] = $this->User->id();
        $data['comment_count'] = 0;
        
        $bit = $this->load->view("messageboard/threadbit", $data, true);
        
        // post ok
        $this->output->set_output(json_encode(array("error" => false, "bit" => $bit)));
    }
    
    public function imprint() {
        $this->load->view('legal/imprint');
    }
    
    public function index() {
        if (!$this->User->is_loggedin()) {
            $this->lang->load("intro", $this->selected_lang);
            $this->load->view("intro/main");
        }
        elseif (!$this->Game_Account->is_connected())
        {
            $this->lang->load("messageboard", $this->selected_lang);
            $this->load->view("messageboard/no_account");
        }
        else {
            $this->lang->load("messageboard", $this->selected_lang);
            $data["posts"] = $this->Posts->get_visible_posts('all', -1, true);
            $this->load->view("messageboard", $data);
        }
    }
}
?>
