<?php $this->load->view("basic/header"); ?>

<div id="loginbox">
    <?=ui_msgbox(lang('general_please_login'));?>
    <?php
    if (validation_errors() != '') {
        echo ui_error(validation_errors());
    }
    ?>
    <?php
    if (isset($login_error) && $login_error == true) {
        echo ui_error(lang('general_login_failed'));
    }
    ?>
    
    <?=form_open('usercp/login');?>
    <?=lang('general_username', 'username');?>: 
        <?=form_input(array('id' => 'username', 'name' => 'username'));?> <br />
    <?=lang('general_password', 'password');?>: 
        <?=form_password(array('id' => 'password', 'name' => 'password'));?> <br />
    <?=form_submit('login', lang('general_login'), 'id=loginbtn');?>
    <?=ui_button('loginbtn');?> <a href="<?=site_url('usercp/password');?>">&raquo; <?=lang('general_forgot_password');?></a>
    <?=form_close();?>
</div>

<?php $this->load->view("basic/footer"); ?>