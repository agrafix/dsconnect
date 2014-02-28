<?php
/**
 * DSConnect
 * 
 * @author Alexander Thiemann <mail@agrafix.net>
 */
?>

<?php $this->load->view("basic/header"); ?>

<div style="padding:10px;">
    <h2><?=lang('messageboard_no_access_world');?> <?=$this->User->selected_world;?></h2>
    <br />
    <?=ui_error(lang('messageboard_no_account'));?>
</div>

<?php $this->load->view("basic/footer"); ?>