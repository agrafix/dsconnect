<?php
/**
 * DSConnect
 * 
 * @author Alexander Thiemann <mail@agrafix.net>
 */
?>

<?php $this->load->view("basic/header"); ?>

<div id="loginbox">
    <?php
    if ($finish_msg != ''):
        echo ui_msgbox($finish_msg);
    else:
    ?>
    <?=  ui_msgbox(lang('password_recover'));?>
    
    <?=validation_errors('<div class="ui-widget" style="margin-bottom:10px;"><div class="ui-state-error ui-corner-all" style="padding: 0.7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>', '</p></div></div>');?>
    
    <?=form_open('usercp/password');?>
    <?=lang('general_username', 'username');?>: 
        <?=form_input(array('id' => 'username', 'name' => 'username', 'value' => set_value('username')));?> <br />
        
    <?=lang('general_email', 'email');?>: 
        <?=form_input(array('id' => 'email', 'name' => 'email', 'value' => set_value('email')));?> <br />
    
        <label>&nbsp;</label>
        <img src="<?=site_url("usercp/password_captcha");?>?h=<?=md5(microtime(true));?>" alt="img" />
        
    <?=lang('password_captcha', 'captcha');?>: 
        <?=form_input(array('id' => 'captcha', 'name' => 'captcha'));?> <br />
    
    <?=form_submit('recover', lang('password_ok'), 'id=recoverbtn');?>
    <?=ui_button('recoverbtn');?>
        
    <?=form_close();?>
     <?php
     endif;
     ?>
</div>

<?php $this->load->view("basic/footer"); ?>