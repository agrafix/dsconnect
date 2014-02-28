<?php
/**
 * DSConnect
 * 
 * @author Alexander Thiemann <mail@agrafix.net>
 */
?>

<?php $this->load->view("basic/header"); ?>

<div style="padding: 10px;">
    
    <h2>
        <?=lang('profile_'.$type);?>: 
        <?=htmlspecialchars(urldecode($file['name']));?> 
        <?php if ($type == 'ally') { echo '('.htmlspecialchars(urldecode($file['tag'])).')'; } ?>
    </h2>
    
    <br />
    
    <button id="followBtn">
        <span>
        <?php
        if (empty($follow)) {
            echo ui_ficon('user_add').' '.lang('profile_follow');
        }
        else {
            echo ui_ficon('tick').' '.lang('profile_you_follow');
        }
        ?>
        </span>
    </button>
    
    <script type="text/javascript">
    var followState = <?=(empty($follow) ? 'false' : 'true');?>;
    
    $(function() {
       $('#followBtn')
       .css('width', '200')
       .button()
       .mouseover(function() {
           if (followState) {
               $("span", this).html('<?=ui_ficon('cancel');?> <?=lang('profile_unfollow');?>');
           }
       })
       .mouseout(function() {
           if (followState) {
               $("span", this).html('<?=ui_ficon('tick');?> <?=lang('profile_you_follow');?>');
           }
       })
       .click(function() {
           $.post('<?=site_url('profile/follow_state');?>', {
               'id': <?=$id;?>,
               'type': '<?=$type;?>',
               "<?=$this->security->get_csrf_token_name();?>": "<?=$this->security->get_csrf_hash();?>",
               'state': (followState ? 'unfollow' : 'follow')
           },
           function(data) {
               followState = data.status;
               
               if (!followState) {
                   $("span", $('#followBtn')).html('<?=ui_ficon('user_add');?> <?=lang('profile_follow');?>');
               }
               else {
                   $("span", $('#followBtn')).html('<?=ui_ficon('tick');?> <?=lang('profile_you_follow');?>');
               }
           },
           "json");
       });
    });
    </script>
    
    <div style="float:right;width:350px;">
        <div class="ui-widget" style="margin-bottom:10px;"><div class="ui-state-highlight ui-corner-all" style="padding: 0.7em;">
                    
        <p style="font-weight:bold;"><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span> <?=lang('profile_information');?></p>
        
        <table style="width:100%;">
            
            <?php if ($type == 'village'): ?>
            <tr>
                <th style="width:100px;"><?=lang('profile_owner');?></th>
                <td>
                    <?php if (empty($player_info)): ?>
                        <i><?=lang('profile_village_left');?></i>
                    <?php else: ?>
                    <a href="<?=site_url('profile/player/'.$player_info['id']);?>">
                        <?=htmlspecialchars(urldecode($player_info['name']));?>
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><?=lang('profile_position');?></th>
                <td><?=$file['x']."|".$file['y'];?></td>
            </tr>
            <tr>
                <th><?=lang('profile_points');?></th>
                <td><?=number_format($file['points'], 0, ',', '.');?></td>
            </tr>
            
            <?php else: ?>
            <tr>
                <th style="width:100px;"><?=lang('profile_rank');?></th>
                <td><?=$file['rank'];?>.</td>
            </tr>
            <?php if ($type == 'ally'): ?>
             <tr>
                <th><?=lang('profile_ally_points');?></th>
                <td><?=number_format($file['points'], 0, ',', '.');?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th><?=lang('profile_points');?></th>
                <td><?=number_format($file[($type=='ally' ? 'all_' : '').'points'], 0, ',', '.');?></td>
            </tr>
            <tr>
                <th><?=lang('profile_villages');?></th>
                <td><?=number_format($file['villages'], 0, ',', '.');?></td>
            </tr>
            <?php if ($type == 'player'): ?>
            <tr>
                <th><?=lang('profile_ally');?></th>
                <td>
                    <?php if (empty($ally_info)): ?>
                    <i>-</i>
                    <?php else: ?>
                    <a href="<?=site_url("profile/ally/".$ally_info['id']);?>">
                    <?=htmlspecialchars(urldecode($ally_info['name']));?> 
                    (<?=htmlspecialchars(urldecode($ally_info['tag']));?>) 
                    <?php endif; ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <a href="<?=site_url('profile/lists/player-villages/'.$id);?>">
                        &raquo; <?=lang('profile_village_list');?>
                    </a>
                </td>
            </tr>
            <?php elseif ($type == 'ally'): ?>
            <tr>
                <th><?=lang('profile_players');?></th>
                <td><?=$file['members'];?></td>
            </tr>
            <tr>
                <td colspan="2">
                    <a href="<?=site_url('profile/lists/ally-members/'.$id);?>">
                        &raquo; <?=lang('profile_member_list');?>
                    </a>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <a href="<?=site_url('profile/lists/ally-villages/'.$id);?>">
                        &raquo; <?=lang('profile_village_list');?>
                    </a>
                </td>
            </tr>
            <?php endif; ?>
            <?php endif; ?>
            
            <tr>
                <th colspan="2">Position auf der Karte</th>
            </tr>
            <tr>
                <td colspan="2" style="text-align:center;">
                    <canvas id="minimap" width="300" height="300">
                        Dein Browser unterstützt diese Karte leider nicht. 
                        Probiere es mit Google Chrome oder Firefox! ;-)
                    </canvas>
                    <br />
                    <a href="<?=site_url('tools/map/show/'.$map['center']['x'].'/'.$map['center']['y'].'/'.$map['scale']);?>">
                            
                        <?=ui_ficon('map_magnify');?> 
                        <?=lang('profile_view_big_map'); ?>
                    </a>
                </td>
            </tr>
        </table>
        
            </div></div>
    </div>
    
    <script type="text/javascript">
    $(function() {
       TWUtils.msgBoard_since = '<?=time();?>';
                    window.setInterval(function() {
                      TWUtils.updateMsgBoard('msgboard', '<?=$type;?>', <?=$id;?>, false, 
                    "<?=$this->security->get_csrf_token_name();?>", "<?=$this->security->get_csrf_hash();?>");
                     
                    }, 60000);
                    
       var map = new TW_CanvasMap(300, 300, 'minimap'); 
       map.setScale(<?=$map['scale'];?>);
       map.setCenter(<?=$map['center']['x'];?>, <?=$map['center']['y'];?>);
       map.mark<?=ucfirst($type);?>(<?=$id;?>, "#FFFF54");
       map.render('<?=site_url('tools/map/dataAPI');?>', 
                  "<?=$this->security->get_csrf_token_name();?>", 
                  "<?=$this->security->get_csrf_hash();?>");
                  
                  
    });
    </script>
    
    <h3><?=lang('profile_posts');?></h3>
    <div id="msgboard">
                <?php foreach($posts as $post): ?>
                <?php $this->load->view('messageboard/threadbit', $post); ?>
                <?php endforeach; ?>
        
        <?php if (count($posts) == 0) { echo "<i>".lang('general_none')."</i>"; } ?>
    </div>
    
    <br style="clear:both;" />
</div>

<?php $this->load->view("basic/footer"); ?>