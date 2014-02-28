<?php
class Usercp extends MY_Controller {
    
    public function __construct()
    {
        parent::__construct();
        
        $this->lang->load("form_validation", $this->selected_lang);
        $this->load->library('form_validation');
    }
    
    public function password_captcha()
    {
        //$this->output->set_content_type('image/png');
        
        $im = imagecreate(100, 30);
        $c_bg = imagecolorallocate($im, 241, 235, 221);
        $c_txt = imagecolorallocate($im, 0, 0, 0);
        
        $number = "".($this->session->userdata("captcha") == false ? 'ERROR' : $this->session->userdata("captcha"))."";
        
        for ($i=0;$i<5;$i++) {
            $angle = 15-mt_rand(0, 30);
            
            imagettftext($im, 20, $angle, 10 + $i*15, 20, $c_txt, BASEPATH."../application/third_party/scriptin.ttf", $number{$i});
        }
        
        header("Content-Type: image/png");
        imagepng($im);
        imagedestroy($im);
    }
    
    public function check_captcha($text)
    {
        if (!$this->session->userdata("captcha") || $text != $this->session->userdata("captcha")) {
            $this->form_validation->set_message('check_captcha', lang('password_invalid_captcha'));
            return false;
        }
        
        return true;
    }
    
    public function password_reset($userid=0, $hash="")
    {
        // dont allow access if user is logged in
        if ($this->User->is_loggedin()) {
            redirect('home');
            return;
        }
        
        $this->lang->load("password", $this->selected_lang);
        
        $q = $this->db->get_where('users', array('id' => $userid));
        
        $data['finish_msg'] = lang('password_not_found');
        
        if ($q->num_rows() != 0) {
            $usr = $q->row_array();
            
            $h = md5($usr['id'].$usr['email'].$usr['password']);
            
            if ($h == $hash) {
                $new_password = substr(md5(microtime(true)."asdf"), 10, 10);
                
                $this->User->change_pass($usr['id'], $new_password);
                
                $data['finish_msg'] = sprintf(lang('password_done'), $usr['email']);
                
                mail($usr['email'], '[DSConnect] '.lang('password_reset'), sprintf(lang('password_ok_msg'), 
                        $usr['username'], $new_password), 'From: DSConnect <mail@agrafix.net>'."\r\n");
            }
        }
        
        $this->load->view('usercp/password', $data);
    }
    
    public function password()
    {
        // dont allow access if user is logged in
        if ($this->User->is_loggedin()) {
            redirect('home');
            return;
        }
        $this->lang->load("password", $this->selected_lang);
        
        $this->form_validation->set_rules('username', 'lang:general_username', 
                                          'trim|required|min_length[5]|max_length[25]|alpha_dash');
        
        $this->form_validation->set_rules('email', 'lang:general_email',
                                          'trim|required|valid_email');
        
        $this->form_validation->set_rules('captcha', 'lang:password_captcha',
                                          'trim|required|callback_check_captcha');
        
        $data['finish_msg'] = '';
        
        if ($this->form_validation->run()) {
            // now do the lookup
            $q = $this->db->get_where('users', array('username' => $this->input->post("username"),
                                                'email' => $this->input->post("email")));
            
            if ($q->num_rows() == 0) {
                $data['finish_msg'] = lang('password_not_found');
            }
            else {
                $usr = $q->row_array();
                
                $resetHash = md5($usr['id'].$usr['email'].$usr['password']);
                $url = site_url('usercp/password_reset/'.$usr['id'].'/'.$resetHash);
                
                mail($usr['email'], '[DSConnect] '.lang('password_reset'), sprintf(lang('password_msg'), 
                        $usr['username'], $url), 'From: DSConnect <mail@agrafix.net>'."\r\n");
                
                $data['finish_msg'] = sprintf(lang('password_found'), $usr['email']);
            }
        }
        
        $this->session->set_userdata("captcha", mt_rand(10000, 99999));
        $this->load->view('usercp/password', $data);
    }
    
    public function account_unlink() 
    {
        // dont allow access if user is not logged in
        if (!$this->User->is_loggedin()) {
            set_status_header('403', 'Login first.');
            return;
        }
        
        $id = $this->input->post("id");
        
        $this->db->query("DELETE FROM linked_accounts WHERE id = ? AND user_id = ?", 
                array($id, $this->User->id()));
        
        if ($this->db->affected_rows() == 0) {
            $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array('error' => true)));
            return;
        }
        
        $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array('error' => false, 'id' => $id)));
    }
    
    public function accounts($error='', $error_id=0) {
        // dont allow access if user is not logged in
        if (!$this->User->is_loggedin()) {
            redirect("home", "location");
            return;
        }
        
        $data['error_id'] = $error_id;
        
        $this->lang->load("accounts", $this->selected_lang);
        
        $this->load->library('tw_import');
        
        $data['accounts'] = $this->Game_Account->getAllConnected();
        
        $this->load->view("usercp/accounts", $data);
    }
    
    public function quit() {
        $this->User->logoff();
        
        if ($this->User->is_loggedin()) {
            redirect("home", "location");
            return;
        }
    }
    
    public function change_world() {
        // dont allow access if user is not logged in
        if (!$this->User->is_loggedin()) {
            redirect("usercp/login", "location");
            return;
        }
        
        $this->load->view("usercp/change_world");
    }
    
    public function store_world() {
        // dont allow access if user is not logged in
        if (!$this->User->is_loggedin()) {
            set_status_header('403', 'Login first.');
            return;
        }
        
        $error = false;
        $world = '';
        
        if (!in_array($this->input->post("world"), $this->config->item("tw_worlds"))) {
            $error = true;
        }
        else {
            $this->User->select_world($this->input->post("world"));
            $world = $this->input->post("world");
        }
        
        $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array('error' => $error, 'world' => $world)));
    }
    
    public function login() {

        // dont allow access if user is logged in
        if ($this->User->is_loggedin()) {
            redirect("home", "location");
            return;
        }
        
        $this->form_validation->set_rules('username', 'lang:general_username', 
                                          'trim|required|min_length[5]|max_length[25]|alpha_dash');
        
        $this->form_validation->set_rules('password', 'lang:general_password', 
                                          'trim|required|min_length[6]|max_length[30]');
        
        if ($this->form_validation->run())
        {
            if ($this->User->login()) {
                redirect("home", "location");
            }
            else {
                $this->load->view("usercp/login", array("login_error" => true));
            }
        }
        else
        {
            $this->load->view("usercp/login");
        }
    }
    
    public function signup() {
        // dont allow access if user is logged in
        if ($this->User->is_loggedin()) {
            redirect("home", "location");
            return;
        }
        
        $this->lang->load("signup", $this->selected_lang);
        
        $this->form_validation->set_rules('username', 'lang:general_username', 
                                          'trim|required|min_length[5]|max_length[25]|alpha_dash|is_unique[users.username]');
        
        $this->form_validation->set_rules('password', 'lang:general_password', 
                                          'trim|required|min_length[6]|max_length[30]|matches[password2]');
        
        $this->form_validation->set_rules('password2', lang('general_password')." ".lang('signup_repeat'), 
                                          'trim|required');
        
        $this->form_validation->set_rules('email', 'lang:general_email',
                                          'trim|required|valid_email|is_unique[users.email]');
        
        if ($this->form_validation->run())
        {
            // ok
            $this->User->signup();
            
            $this->load->view("usercp/signup_ok");
        }
        else
        {
            $this->load->view("usercp/signup");
        }
    }
    
}
?>
