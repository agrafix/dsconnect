<?php
function tw_format_duration($duration)
{
    if ($duration <= 0) {
        return "00:00:00";
    }
    
    $hours = floor($duration / 3600);
    $duration -= $hours*3600;
    
    $min = floor($duration / 60);
    $duration -= $min*60;
    
    return $hours.":".$min.":".$duration;
}

function tw_nameencode($name)
{
    $chars = array('ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß', ' ');
    $rep = array('%C3%A4','%C3%B6','%C3%BC','%C3%84','%C3%96','%C3%9C','%C3%9F', '+');
    
    return str_replace($chars, $rep, $name);
}

function tw_bbcode($text)
{
    return preg_replace_callback('#\[([a-z]*)id\]([0-9\s]*)\[/[a-z]*\]#i', 
            create_function('$m', 'return _tw_lookup_id($m[1], $m[2]);'),
            $text);
    
    return $text;
}

function _tw_lookup_id($type, $id)
{
    $CI = &get_instance();
    
    $id = trim($id);
    
    if (!in_array($type, array('ally', 'player', 'village')) 
            || !is_numeric($id)) {
        return "invalid type/id";
    }
    
    if ($type == 'player' && $id == 0)
    {
        return '<i>'.lang('general_nobody').'</i>';
    }
    elseif ($type == 'ally' && $id == 0)
    {
        return '<i>'.lang('general_no_ally').'</i>';
    }
    
    $CI->db->select('id, name'.($type == 'village' ? ', x, y' : '')
                                .($type == 'ally' ? ', tag': ''));
    $CI->db->from($CI->User->selected_world.'_'.$type);
    $CI->db->where('id', $id);
    $q = $CI->db->get();
    
    if ($q->num_rows() == 0) {
        return '<i>'.$type.' not found, id: '.$id.'</i>';
    }
    
    $row = $q->row_array();
    $q->free_result();
    
    $base_url = site_url('profile/'.$type);
    
    if ($type == 'ally') {
        return "<a href='".$base_url."/".$id."'>"
                .htmlspecialchars(urldecode($row['name']))
                ." (".htmlspecialchars(urldecode($row['tag'])).")"
                ."</a>";
    }
    elseif ($type == 'village') {
        return "<a href='".$base_url."/".$id."'>"
                .htmlspecialchars(urldecode($row['name']))
                ." (".$row["x"]."|".$row["y"].")"
                ."</a>";
    }
    else {
        return "<a href='".$base_url."/".$id."'>"
                .htmlspecialchars(urldecode($row['name']))
                ."</a>";
    }
    
}
?>
