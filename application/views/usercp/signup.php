<?php $this->load->view("basic/header"); ?>

<div id="loginbox">
    <?=ui_msgbox(lang('signup_text'));?>
    <?php
    if (validation_errors() != '') {
        echo ui_error(validation_errors());
    }
    ?>
    
    <?=form_open('usercp/signup');?>
    <?=lang('general_username', 'username');?>: 
        <?=form_input(array('id' => 'username', 'name' => 'username', 'value' => set_value('username')));?> <br />
        
    <?=lang('general_password', 'password');?>: 
        <?=form_password(array('id' => 'password', 'name' => 'password'));?> <br />
        
    <label for="password2"><?=lang('general_password');?> <?=lang('signup_repeat');?></label>:
        <?=form_password(array('id' => 'password2', 'name' => 'password2'));?> <br />
    
    <?=lang('general_email', 'email');?>: 
        <?=form_input(array('id' => 'email', 'name' => 'email', 'value' => set_value('email')));?> <br />
        
    <?=form_submit('signup', lang('general_signup'), 'id=signupbtn');?>
    <?=ui_button('signupbtn');?>
    <?=form_close();?>
</div>

<?php $this->load->view("basic/footer"); ?>