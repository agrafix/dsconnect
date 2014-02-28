<?php
class Map extends MY_Controller {
    
    public function __construct()
    {
        parent::__construct();
        
        if (!$this->User->is_loggedin()) {
            redirect('usercp/login');
        }
    }
    
    public function index()
    {
        redirect('tools/map/show');
    }
    
    public function show($cx=500, $cy=500, $zoom=1)
    {
        $data['cx'] = 500;
        $data['cy'] = 500;
        $data['zoom'] = 1;
        
        if (is_numeric($cx) && $cx >= 0 && $cx <= 1000) {
            $data['cx'] = $cx;
        }
        if (is_numeric($cy) && $cy >= 0 && $cy <= 1000) {
            $data['cy'] = $cy;
        }
        
        if (is_numeric($zoom) && $zoom >= 1 && $zoom <= 20) {
            $data['zoom'] = $zoom;
        }
        
        $this->lang->load('profile', $this->selected_lang);
        $this->lang->load('map', $this->selected_lang);
        $this->load->view('tools/map', $data);
    }
    
    public function dataAPI()
    {
        ini_set("memory_limit", "256M");
        
        $tribe = array();
        $village = array();
        
        if ($this->input->post("ally")) {
            foreach ($this->input->post("ally") as $a)
            {
                $q = $this->db->query("SELECT 
                    p.id
                FROM
                    ".$this->User->selected_world."_ally as a,
                    ".$this->User->selected_world."_player as p
                WHERE
                    p.ally = a.id AND a.id = ?", array($a['id']));
                
                foreach ($q->result_array() as $row)
                {
                    $tribe[$row["id"]] = $a["color"];
                }
            }
        }
        
        if ($this->input->post("player")) {
            foreach ($this->input->post("player") as $p)
            {
                $tribe[$p["id"]] = $p["color"];
            }
        }
        
        if ($this->input->post("village")) {
            foreach ($this->input->post("village") as $v)
            {
                $village[$v["id"]] = $v["color"];
            }
        }
        
        $q = $this->db->query('SELECT
                v.x, v.y, v.tribe, v.id
            FROM
                 '.$this->User->selected_world.'_village as v
            WHERE
              ? <= v.x AND v.x <= ? AND 
              ? <= v.y AND v.y <= ?', array(
                  $this->input->post('x_min'),
                  $this->input->post('x_max'),
                  $this->input->post('y_min'),
                  $this->input->post('y_max')
              ));
        
        $villages = array();
        
        foreach ($q->result_array() as $row)
        {
            $row['color'] = '#933030'; // normal color
            
            if (isset($village[$row['id']])) {
                $row['color'] = $village[$row['id']];
            }
            elseif ($row['tribe'] == 0) {
                $row['color'] = 'rgba(155, 155, 155, 0.4)';
            }
            elseif (isset($tribe[$row['tribe']])) {
                $row['color'] = $tribe[$row['tribe']];
            }

            $villages[] = $row;
        }
        
        $this->output->set_content_type("application/json")
                     ->set_output(json_encode($villages));
    }
    
}
?>
