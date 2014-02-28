<?php
/**
 * DSConnect
 * 
 * @author Alexander Thiemann <mail@agrafix.net>
 */
?>

<?php $this->load->view("basic/header"); ?>

<div style="padding: 10px;">
    <h2><?=lang('attack_name');?></h2>
    
    <h3><?=lang('attack_create_plan');?></h3>
    <?=form_open('tools/attack/create');?>
    <?=lang('attack_desc', 'desc');?>: 
    <?=form_input('desc', '', 'id="desc" style="width:200px;"');?>
    <button id="createBtn">
        <?=ui_ficon('table_add');?>
    </button>
    
    <?=ui_button('createBtn');?>
    <?=form_close();?>
    
    
    <h3><?=lang('attack_own_plans');?></h3>
    <table>
        <tr>
            <th><?=lang('attack_id');?></th>
            <th><?=lang('attack_desc');?></th>
            <th><?=lang('attack_created');?></th>
            <th></th>
        </tr>
        
        <?php foreach($plans as $plan): ?>
        <tr>
            <td><?=$plan['id'];?></td>
            <td>
                <a href="<?=site_url('tools/attack/edit/'.$plan['id']);?>">
                    <?=htmlspecialchars($plan['desc']);?>
                </a>
            </td>
            <td><?=date('d.m.Y', $plan['created_at']);?></td>
            <td>
                <a href="<?=site_url('tools/attack/delete/'.$plan['id']);?>">
                    <?=ui_ficon('bin');?>
                </a>
            </td>
        </tr>
        <? endforeach;?>
    </table>
</div>

<?php $this->load->view("basic/footer"); ?>