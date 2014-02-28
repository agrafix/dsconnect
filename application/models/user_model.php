<?php
class User_model extends CI_Model {
    
    public $username;
    public $password;
    public $email;
    public $api_hash;
    
    public $lng;
    
    public $selected_world = '';
    
    // CHANGE THIS!
    private static $salt = 'SOME_STATIC_SALT';
    
    private $is_loggedin = false;
    
    public function __construct()
    {
        parent::__construct();
        
        $worlds = $this->config->item('tw_worlds');
        
        if (!$this->session->userdata('world')
                || !in_array($this->session->userdata('world'), $worlds)) {
            $this->session->set_userdata('world', $worlds[0]);
        }
        
        $this->selected_world = $this->session->userdata('world');
    }
    
    public function select_world($w)
    {
        $this->session->set_userdata('world', $w);
        
        $this->selected_world = $w;
    }
    
    public function is_loggedin() {
        if ($this->is_loggedin) {
            return true;
        }
        
        if ($this->session->userdata('login') == true) {
            // check if user still exists and load his info
            $q = $this->db->query("SELECT * FROM users WHERE id = ? LIMIT 1", array($this->session->userdata('userid')));
            
            if ($q->num_rows() == 0) {
                $this->session->set_userdata('login', false);
                return false;
            }
            
            $info = $q->row();
            
            $this->username = $info->username;
            $this->password = $info->password;
            $this->email = $info->email;
            $this->api_hash = $info->api_hash;
            $this->is_loggedin = true;
            
            return true;
        }
        
        return false;
    }
    
    public function id() {
        if (!$this->is_loggedin()) {
            return -1;
        }
        
        return $this->session->userdata('userid');
    }
    
    public function logoff() {
        $this->session->set_userdata('login', false);
        $this->session->set_userdata('userid', '-1');
    }
    
    public function login()
    {
        if ($this->input->post("username") == false && $this->input->post("password") == false) {
            return false;
        }
        
        $this->username = $this->input->post("username");
        $this->password = md5(md5($this->input->post("password")).self::$salt);
        
        $query = $this->db->query("SELECT * FROM users WHERE username = ? AND password = ? LIMIT 1", 
                array($this->username, $this->password));
        
        if ($query->num_rows() == 0) {
            return false;
        }
        
        $row = $query->row();
        
        $this->email = $row->email;
        
        $this->session->set_userdata('username', $this->username);
        $this->session->set_userdata('userid', $row->id);
        $this->session->set_userdata('login', true);
        
        return true;
    }
    
    public function change_pass($userid, $new_pass)
    {
        $this->password = md5(md5($new_pass).self::$salt);
        
        $this->db->where('id', $userid);
        $this->db->update('users', array(
            'password' => $this->password
        ));
    }
    
    public function signup()
    {
        $this->username = $this->input->post("username");
        $this->password = md5(md5($this->input->post("password")).self::$salt);
        $this->email = $this->input->post("email");
        $this->api_hash = sha1($this->email.microtime(true).md5(mt_rand(1000, 5000)));
        
        $this->db->insert('users', array(
            'username' => $this->username,
            'password' => $this->password,
            'email' => $this->email,
            'api_hash' => $this->api_hash
        ));
        
        return true;
    }
}
