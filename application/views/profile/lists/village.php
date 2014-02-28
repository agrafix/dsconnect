<?php
/**
 * DSConnect
 * 
 * @author Alexander Thiemann <mail@agrafix.net>
 */
?>

<?php $this->load->view("basic/header"); ?>

<div style="padding: 10px;">
    <h2><?=lang('profile_village_list_for');?> 
        <a href="<?=site_url('profile/'.($type == 'player-villages' ? 'player' : 'ally').'/'.$player['id']);?>">
            <?=htmlspecialchars(urldecode($player['name']));?>
            <?=($type != 'player-villages' ? '('.htmlspecialchars(urldecode($player['tag'])).')' : ''); ?>
        </a>
    </h2>
    
    <br />
    
    <table>
        <tr>
            <th><?=lang('profile_village');?></th>
            <th><?=lang('profile_points');?></th>
        </tr>
        
        <?php foreach($villages as $v): ?>
        <tr>
            <td>
                <a href="<?=site_url('profile/village/'.$v['id']);?>">
                    <?=htmlspecialchars(urldecode($v['name']));?> (<?=$v['x'];?>|<?=$v['y'];?>)
                </a>
            </td>
            
            <td><?=number_format($v['points'], 0, ',', '.');?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <?=$pagination;?>
</div>

<?php $this->load->view("basic/footer"); ?>