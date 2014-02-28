<?php
/**
 * DSConnect
 * 
 * @author Alexander Thiemann <mail@agrafix.net>
 */
?>

<?php $this->load->view("basic/header"); ?>

<div style="padding: 10px;" id="int_container">
    <h2><?=lang('attack_plan');?>: <?=htmlspecialchars($plan['desc']);?></h2>    
    
    <br />
    <a id="backBtn" href="<?=site_url('tools/attack');?>">&laquo; <?=lang('attack_back_to_overview');?></a>
    <br />
    <br />
    
    <div id="attack-tabs">
        <ul>
            <li><a href="#tab-overview"><?=ui_ficon('table');?> <?=lang('attack_tab_overview');?></a></li>
            <li><a href="#tab-add"><?=ui_ficon('add');?> <?=lang('attack_tab_add');?></a></li>
            <li><a href="#tab-wizard"><?=ui_ficon('wand');?> <?=lang('attack_tab_wizard');?></a></li>
            <li><a href="#tab-map"><?=ui_ficon('map');?> <?=lang('attack_tab_map');?></a></li>
        </ul>
        
        <div id="tab-overview">
            
            <table>
                <tr>
                    <th><?=lang('attack_type');?></th>
                    <th><?=lang('attack_start_vill');?></th>
                    <th><?=lang('attack_target_vill');?></th>
                    <th><?=lang('attack_start_time');?></th>
                    <th><?=lang('attack_arrival');?></th>
                    <th><?=lang('attack_send_in');?></th>
                    <th></th>
                </tr>
                
                <?php
                foreach($actions as $action):
                ?>
                <tr class="hover_info">
                    <td>
                        <img src="
                        <?php
                        switch($action['type']) {
                            case "attack":
                                echo site_url('static/image/ds/unit_axe.png');
                                break;
                            case "snob":
                                echo site_url('static/image/ds/unit_snob.png');
                                break;
                            case "def":
                                echo site_url('static/image/ds/unit_sword.png');
                                break;
                            case "fake":
                                echo site_url('static/image/ds/unit_ram.png');
                                break;
                        }
                        ?>" alt="<?=lang('attack_type_'.$action['type']);?>"
                        title="<?=lang('attack_type_'.$action['type']);?>" />
                    </td>
                    <td>
                        <a href="<?=site_url('profile/village/'.$action['start_village_id']);?>">
                            <?=htmlspecialchars(urldecode($action['start_vname']));?>
                        </a>
                    </td>
                    <td>
                        <a href="<?=site_url('profile/village/'.$action['stop_village_id']);?>">
                            <?=htmlspecialchars(urldecode($action['stop_vname']));?>
                        </a>
                    </td>
                    <td>
                        <?=date(lang('general_basic_date_no_year'), $action['start_time']);?>
                    </td>
                    <td>
                        <?=date(lang('general_basic_date_no_year'), $action['arrival_time']);?>
                    </td>
                    <td style="text-align:right;" class="countdown">
                        <?=tw_format_duration($action['start_time']-time());?>
                    </td>
                    <td>
                        <a href="<?=site_url('tools/attack/edit/'.$plan['id'].'/delete_action/'.$action['id']);?>">
                            <?=ui_ficon('bin');?>
                        </a>
                        
                        <a href="<?=Tw_import::get_tw_host($this->User->selected_world);?>/game.php?village=<?=$action['start_village_id'];?>&screen=place&target=<?=$action['stop_village_id'];?>" target="_blank">
                            <?=ui_ficon('application_go');?>
                        </a>
                        
                        <div class="tooltip_data" style="display:none;">
                            <h4><?=lang('attack_type_'.$action['type']);?></h4>
                            <br />
                            
                            <h4><?=lang('attack_start_vill');?>: <?=htmlspecialchars(urldecode($action['start_vname']));?></h4>
                            <p><b><?=lang('attack_village_owner');?>:</b> <?=($action['start_pname'] != null ? 
                                    htmlspecialchars(urldecode($action['start_pname'])) : 
                                    lang('attack_vill_left'));?></p>
                            
                            <h4><?=lang('attack_target_vill');?>: <?=htmlspecialchars(urldecode($action['stop_vname']));?></h4>
                            <p><b><?=lang('attack_village_owner');?>:</b> <?=($action['stop_pname'] != null ? 
                                    htmlspecialchars(urldecode($action['stop_pname'])) : 
                                    lang('attack_vill_left'));?></p>
                            
                            <h4><?=lang('attack_note');?></h4>
                            <p><?=nl2br(htmlspecialchars($action['note']));?></p>
                            
                            <h4><?=lang('attack_units');?></h4>
                            <table>
                            <tr>
                                <?php
                                foreach($units as $name => $data):
                                ?>
                                <th>    
                                    <img src="<?=site_url('static/image/ds/unit_'.$name.'.png');?>" alt="<?=$name;?>" />
                                </th>
                                <?php
                                endforeach;
                                ?>
                            </tr>
                            <tr>
                                <?php
                                foreach($units as $name => $data):
                                ?>
                                <td>    
                                    <?=$action[$name];?>
                                </td>
                                <?php
                                endforeach;
                                ?>
                            </tr>
                        </table>
                        </div>
                    </td>
                </tr>
                <?php
                endforeach;
                ?>
            </table>
            
        </div>
        
        <div id="tab-add">
            <?=validation_errors('<div class="ui-widget" style="margin-bottom:10px;"><div class="ui-state-error ui-corner-all" style="padding: 0.7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>', '</p></div></div>');?>
            
            <?=form_open();?>
            
            <?=form_hidden('tab', 'add');?>
            
            <h3><?=lang('attack_type', 'add_type');?></h3>
            <?=form_dropdown('type', array(
                'attack' => lang('attack_type_attack'),
                'fake' => lang('attack_type_fake'),
                'snob' => lang('attack_type_snob'),
                'def' => lang('attack_type_def')
            ), array(), "id='add_type'");?>
            
            <h3><?=lang('attack_start_vill');?></h3>
            <input type="text" value="500|500" name="start_vill" />
            
            <h3><?=lang('attack_target_vill');?></h3>
            <input type="text" value="500|501" name="target_vill" />
            
            <h3><?=lang('attack_units');?></h3>
            
            <table>
                <tr>
                    <th></th>
                    <?php
                    foreach($units as $name => $data):
                    ?>
                    <th>    
                        <img src="<?=site_url('static/image/ds/unit_'.$name.'.png');?>" alt="<?=$name;?>" />
                    </th>
                    <?php
                    endforeach;
                    ?>
                </tr>
                <tr>
                    <th>
                        <img src="<?=site_url('static/image/ds/speed.png');?>"
                             alt="<?=lang('attack_unit_speed');?>" 
                             title="<?=lang('attack_unit_speed');?>" />
                    </th>
                    <?php
                    foreach($units as $name => $data):
                    ?>
                    <td title="<?=lang('attack_unit_speed');?>: <?=lang('attack_unit_speed_unit');?>">    
                        <?=round($data['speed']*$unit_speed, 2);?> 
                        <span style="font-size:6pt;"><?=lang('attack_unit_speed_unit');?></span>
                    </td>
                    <?php
                    endforeach;
                    ?>
                </tr>
                <tr>
                    <th></th>
                    <?php
                    foreach($units as $name => $data):
                    ?>
                    <td>    
                        <input type="text" size="5" name="<?=$name;?>" value="0" />
                    </td>
                    <?php
                    endforeach;
                    ?>
                </tr>
            </table>
            
            <?=ui_msgbox(lang('attack_units_note'));?>
            
            <h3><?=lang('attack_arrival');?></h3>
            
            <input type="text" name="arrival" value="<?=date('d.m.Y - H:i:s', $plan['last_arrival']);?>" />
            
            <h3><?=lang('attack_note');?></h3>
            
            <?=form_textarea('note');?>
            
            <h3></h3>
            
            <button id="addBtn">
                <?=ui_ficon('add'); ?>
                <?=lang('attack_tab_add');?>
            </button>
            
            <?=ui_button('addBtn');?>
            
            <?=form_close();?>
        </div>
        
        <div id="tab-wizard">
            <?php if ($wiz_step == 1): ?>
            <?=ui_msgbox(lang('attack_wizard_info'));?>
            <?php endif; ?>
            
            <?=ui_msgbox(lang('attack_wizard_step'.$wiz_step));?>
            
            <?php
            if (!empty($wiz_errors)) {
                foreach ($wiz_errors as $e) {
                    echo ui_error($e);
                }
            }
            ?>
            
            <?=form_open();?>
            
            <?=form_hidden('tab', 'wizard');?>
            <?=form_hidden('step', $wiz_step+1);?>
            
            <?php
            if ($wiz_step == 1):
            ?>
            
            <?=form_textarea('villages', 
                    htmlspecialchars($this->input->post("villages") ? 
                                     $this->input->post("villages") : ''));?> 
            
            <?php
            elseif ($wiz_step == 2):
            ?>
            
            <?=form_textarea('own_villages', 
                    htmlspecialchars($this->input->post("own_villages") ? 
                                     $this->input->post("own_villages") : ''));?>
            
            
            <?php
            elseif ($wiz_step == 3):
                $types = array('F', 'O', 'S');
                foreach($types as $t):
            ?>
            
            <h3><?=lang('attack_wizard_troop_config');?> <?=lang('attack_wizard_troop_config_'.$t);?></h3>
            
            <?=sprintf(lang('attack_wizard_per_target'),lang('attack_wizard_troop_config_'.$t));?>: 
                <input type="text" value="<?php
                if ($t == 'O') { echo 3; }
                elseif ($t == 'S') { echo 4; }
                else { echo 7; }
                ?>" size="5" name="<?=$t."[per_target]";?>" />x
            
            <table>
                <tr>
                    <th></th>
                    <?php
                    foreach($units as $name => $data):
                    ?>
                    <th>    
                        <img src="<?=site_url('static/image/ds/unit_'.$name.'.png');?>" alt="<?=$name;?>" />
                    </th>
                    <?php
                    endforeach;
                    ?>
                </tr>
                <tr>
                    <th>
                        <img src="<?=site_url('static/image/ds/speed.png');?>"
                             alt="<?=lang('attack_unit_speed');?>" 
                             title="<?=lang('attack_unit_speed');?>" />
                    </th>
                    <?php
                    foreach($units as $name => $data):
                    ?>
                    <td title="<?=lang('attack_unit_speed');?>: <?=lang('attack_unit_speed_unit');?>">    
                        <?=round($data['speed']*$unit_speed, 2);?> 
                        <span style="font-size:6pt;"><?=lang('attack_unit_speed_unit');?></span>
                    </td>
                    <?php
                    endforeach;
                    ?>
                </tr>
                <tr>
                    <th></th>
                    <?php
                    foreach($units as $name => $data):
                    ?>
                    <td>    
                        <input type="text" size="5" name="<?=$t."[".$name."]";?>" value="0" />
                    </td>
                    <?php
                    endforeach;
                    ?>
                </tr>
            </table>
            
            
            <?php
            endforeach;
            ?>
            <?php
            endif;
            ?>
            
            <br /> <br />
            
            <button id="nextBtn"><?=lang('attack_wizard_next');?> &gt;</button>
            
            <?=form_close();?>
        </div>
        
        <div id="tab-map">
            <canvas id="attack_map" width="600" height="600"></canvas>
        </div>
    </div>
</div>

<script type="text/javascript">
function countdown()
{
    $('.countdown').each(function(k, el) {
        var state = $(el).text().split(":");
        
        var seconds = (state[0]*3600 + state[1]*60 + state[2]*1) - 1;
        
        if (seconds <= 0)
        {
            $(el).text('00:00:00').removeClass('countdown').addClass('countdown_finished');
            return;
        }
        
        var hours = Math.floor(seconds / 3600);
        seconds -= hours*3600;
        
        var minutes = Math.floor(seconds / 60);
        seconds -= minutes*60;
        
        var new_state = (hours < 10 ? "0" + hours : hours) 
                        + ":"
                        + (minutes < 10 ? "0" + minutes : minutes)
                        + ":"
                        + (seconds < 10 ? "0" + seconds : seconds);
                    
        $(el).text(new_state);
    });
}

$(function() {
   $('#backBtn, #nextBtn').button();
   
   var mapRendered = false
   var map = new TW_CanvasMap(600, 600, 'attack_map');
   map.setScale(4);
   map.setCenter(<?=floor($map_center['cx']);?>, <?=floor($map_center['cy']);?>);
   <?php
   $line_js = "";
   foreach($actions as $action) {
       echo 'map.markVillage('.$action['start_village_id'].', "#FFFF54");';
       echo 'map.markVillage('.$action['stop_village_id'].', "#FF00D4");';
       
       $line_js .= "map.drawArrow(".$action['start_vcoords'].", ".$action['stop_vcoords'].");";
   }
   ?>
   
   $('#attack-tabs').tabs({
       show: function(event, ui) {
            if(ui.tab.hash == '#tab-map' && !mapRendered) {
                map.render('<?=site_url('tools/map/dataAPI');?>', 
                  "<?=$this->security->get_csrf_token_name();?>", 
                  "<?=$this->security->get_csrf_hash();?>", function() { 
                      map.doneLoading(); 
                      mapRendered = true;
                      <?=$line_js;?> 
                  });
            }
       }
   }); 
   $('#attack-tabs').tabs("select", "#tab-<?=$current_tab;?>");
   
   var hoverDiv = $("<div>").addClass('tooltip').hide().css('position', 'fixed');
   $("#int_container").append(hoverDiv);
   
   $('.hover_info').bind('mouseenter', function(event) {
       hoverDiv.css('top', event.pageY-240);
       hoverDiv.css('left', event.pageX+15);
       hoverDiv.show();
       hoverDiv.html($('.tooltip_data', this).html());
   });
   
   $('.hover_info').bind('mousemove', function(event) {
       hoverDiv.css('top', event.pageY-240);
       hoverDiv.css('left', event.pageX+15);
   });
   
   $('.hover_info').bind('mouseleave', function() {
       hoverDiv.hide();
   });
   
});

window.setInterval(countdown, 1000);
</script>

<?php $this->load->view("basic/footer"); ?>