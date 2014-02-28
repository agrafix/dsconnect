<?php
class Post extends MY_Controller {
    public function __construct()
    {
        parent::__construct();
        
        if (!$this->User->is_loggedin()) {
            redirect('usercp/login');
        }
    }
    
    public function show($id=0, $func='display', $page=1)
    {
        if (!is_numeric($id)) {
            show_404();
        }
        
        $q = $this->db->query('SELECT type, type_id FROM posts WHERE id = ?', array($id));
        
        if ($q->num_rows() == 0) {
            show_404();
        }
        
        $row = $q->row_array();
        
        $this->load->model('Posts_model', 'Posts');
        $posts = $this->Posts->get_visible_posts($row['type'], $row['type_id'], false, 0, $id);
        
        if (empty($posts)) {
            show_error('No Access', 403);
        }
        
        if ($func == 'comment')
        {
            if (!$this->Game_Account->is_connected())
            {
                show_error('No connected account', 403);
            }
            
            if ($this->input->post("comment") == '' 
                    || strlen($this->input->post("comment")) > 2000)
            {
                show_error('Invalid input', 500);
            }
            
            $d = array(
                'user_id' => $this->User->id(),
                'post_id' => $id,
                'player_id' => $this->Game_Account->id,
                'player_name' => $this->Game_Account->name,
                'message' => $this->input->post("comment"),
                'time' => time()
            );
            
            $this->db->insert('comments', $d);
            
            $this->output->set_content_type('application/json')
                         ->set_output(json_encode(array(
                             'bit' => $this->load->view('messageboard/commentbit', $d, true),
                             'error' => false
                         )));
        }
        else 
        {
            $data['post'] = $posts[0];
            $data['id'] = $id;
            
            $this->load->library('pagination');
        
            $config['base_url'] = site_url('post/show/'.$id.'/display/');
            $config['uri_segment'] = 5;
            $config['total_rows'] = $posts[0]["comment_count"];
            $config['per_page'] = 5;
            $config['use_page_numbers'] = true;
            $config['cur_page'] = $page;

            $this->pagination->initialize($config);

            $data['pagination'] = $this->pagination->create_links();
            
            $data['comments'] = array();
            
            $q = $this->db->query('SELECT c.player_id, c.player_name, c.message, c.time
                FROM
                    comments AS c
                WHERE
                    c.post_id = ?
                ORDER BY 
                    c.time DESC
                LIMIT '.(5 * ($page-1)).',5', array($id));
            
            if ($q->num_rows() > 0) {
                foreach ($q->result_array() as $row) {
                    $data['comments'][] = $row;
                }
            }

            $this->lang->load('post', $this->selected_lang);
            $this->lang->load('messageboard', $this->selected_lang);
            $this->load->view('post', $data);
        }
    }
}
?>
