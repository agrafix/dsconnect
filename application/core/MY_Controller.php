<?php
class MY_Controller extends CI_Controller {
    
    protected $selected_lang = 'german';
    
    public function __construct()
    {
        parent::__construct();
        
        $this->selectLanguage();
        
        $this->lang->load('general', $this->selected_lang);
        
        $this->load->model('User_model', 'User');
        if ($this->User->is_loggedin()) {
            $this->load->model('Gameaccount_model', 'Game_Account');
        }
        
        $this->User->lng = $this->selected_lang;
        
        if ($this->input->get('world') &&
                in_array($this->input->get('world'), $this->config->item('tw_worlds'))) {
            $this->User->select_world($this->input->get('world'));
        }
    }
    
    private function selectLanguage() 
    {
        $allowed_langs = array('german', 'english');
        
        if ($this->input->cookie('lang') != false
                && in_array($this->input->cookie('lang'), $allowed_langs)) {
            $this->selected_lang = $this->input->cookie('lang');
            
        }
        
        if ($this->input->get('lang') != false
                && in_array($this->input->get('lang'), $allowed_langs)) {
            $this->selected_lang = $this->input->get('lang');
            
            $this->input->set_cookie('lang', $this->selected_lang, 3600 * 24 * 30);
        }
    }
    
    protected function requireLogin()
    {
        if (!$this->User->is_loggedin())
        {
            redirect('usercp/login');
        }
    }
}
?>
