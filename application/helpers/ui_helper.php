<?php
/**
 * JQuery-UI/FamFamFam Icons CodeIgniter Helper
 * 
 * @author Alexander Thiemann <mail@agrafix.net>
 */

if (!function_exists('ui_init')) {
    function ui_init()
    {
        $script = '/*jquery state changes*/';
        $script .= '$(function() {';
        $script .= '$(".ui-state-default").hover(';
        $script .= 'function() { $(this).addClass("ui-state-hover"); },';
        $script .= 'function() { $(this).removeClass("ui-state-hover"); }';
        $script .= ');';
        $script .= '})';
        
        return '<script type="text/javascript">'.$script.'</script>'."\n";
    }
}

if (!function_exists('ui_msgbox')) {
    function ui_msgbox($text, $id='', $style='') 
    {
        $code = '<div'.($id != '' ? ' id = "'.$id.'"' : '').' class="ui-widget" style="margin-bottom:10px;'.$style.'">';
        $code .= '<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0.7em;"> ';
        $code .= '<p>';
        $code .= '<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>';
        $code .= ' '.$text;
        $code .= '</p>';
        $code .= '</div>';
        $code .= '</div>';
        
        return $code;
    }
}

if (!function_exists('ui_error')) {
    function ui_error($text, $id='', $style='') 
    {
        $code = '<div'.($id != '' ? ' id = "'.$id.'"' : '').' class="ui-widget" style="margin-bottom:10px;'.$style.'">';
        $code .= '<div class="ui-state-error ui-corner-all" style="padding: 0.7em;">';
        $code .= '<p>';
        $code .= '<span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>';
        $code .= ' '.$text;
        $code .= '</p>';
        $code .= '</div>';
        $code .= '</div>';
        
        return $code;
    }
}

if (!function_exists('ui_button')) {
    function ui_button($element_id) {
        return '<script type="text/javascript">$("#'.$element_id.'").button();</script>';
    }
}

if (!function_exists('ui_ficon')) {
    function ui_ficon($name, $desc='', $ui_icon=false) {
        return '<img src="'.site_url("static/image/icon/".$name.".png").'" '.
               'alt="'.($desc == '' ? $name : $desc).'" '.
               'title="'.($desc == '' ? $name : $desc).'" class="'.($ui_icon ? 'ui-icon' : '').'" />';
    }
}

if (!function_exists('ui_iconbutton')) {
    function ui_iconbutton($name, $desc, $url) {
        return '<a href="' . $url . '" class="ui-state-default ui-corner-all ui-link-text">' .
               ui_ficon($name, $desc, true).'</a>';
    }
}

if (!function_exists('ui_loadicon')) {
    function ui_loadicon($id='loadimg', $style='display:none;') {
        return '<img id="'.$id.'" src="'.site_url("static/image/ajax-loader-small.gif").'" alt="Loading..." style="'.$style.'" />';
    }
}

if (!function_exists('ui_ajax_post_form')) {
    function ui_ajax_post_form($formId, $loadiconId='-1', $onComplete='')
    {
        return '<script type="text/javascript">
        $(function() {
            $("#'.$formId.'").submit(function(event) {
                event.preventDefault();
                
                '.($loadiconId == '-1' ? '' : '$("#'.$loadiconId.'").fadeIn();').'
                    
                var data = $("input, textarea", this).serializeArray();
                    
                $.post($(this).attr("action"), data, function(resp) {
                    '.($loadiconId == '-1' ? '' : '$("#'.$loadiconId.'").fadeOut();').'
                        
                    '.$onComplete.'
                }, "json");
            });
        });
        </script>';
    }
}
?>
