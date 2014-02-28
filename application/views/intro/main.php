<?php
/**
 * DSConnect
 * 
 * @author Alexander Thiemann <mail@agrafix.net>
 */
?>

<?php $this->load->view("basic/header"); ?>

<div id="intro_div">
    <h1><?=lang('intro_welcome');?></h1>
    
    <h2><?=lang('intro_what_is');?></h2>
    
    <p><?=lang('intro_what_is_text');?></p>
    
    <a href="<?=site_url('usercp/signup');?>" id="signupBtn"><?=lang('intro_signup_now');?></a>
    <?=ui_button('signupBtn');?>
</div>

<?php $this->load->view("basic/footer"); ?>