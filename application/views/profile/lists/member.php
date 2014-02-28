<?php
/**
 * DSConnect
 * 
 * @author Alexander Thiemann <mail@agrafix.net>
 */
?>

<?php $this->load->view("basic/header"); ?>

<div style="padding: 10px;">
    <h2><?=lang('profile_member_list_for');?> 
        <a href="<?=site_url('profile/ally/'.$ally['id']);?>">
            <?=htmlspecialchars(urldecode($ally['name']));?>
            <?='('.htmlspecialchars(urldecode($ally['tag'])).')';?>
        </a>
    </h2>
    
    <br />
    
    <table>
        <tr>
            <th><?=lang('profile_player');?></th>
            <th><?=lang('profile_points');?></th>
        </tr>
        
        <?php foreach($members as $m): ?>
        <tr>
            <td>
                <a href="<?=site_url('profile/player/'.$m['id']);?>">
                    <?=htmlspecialchars(urldecode($m['name']));?>
                </a>
            </td>
            
            <td><?=number_format($m['points'], 0, ',', '.');?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <?=$pagination;?>
</div>

<?php $this->load->view("basic/footer"); ?>