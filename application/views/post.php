<?php
/**
 * DSConnect
 * 
 * @author Alexander Thiemann <mail@agrafix.net>
 */
?>

<?php $this->load->view("basic/header"); ?>

<div style="padding: 10px;">
    <?php
    $this->load->view('messageboard/threadbit', $post);
    ?>
    
    <div id="commentboard">
    <?php
    foreach ($comments as $comment) {
        $this->load->view('messageboard/commentbit', $comment);
    }
    ?>
    </div>
    <?=$pagination;?>
    
    <?php
    if (!$this->Game_Account->is_connected()):
        echo ui_error(lang('messageboard_no_account'));
    else:
    ?>
    <h2><?=lang('post_make_comment');?></h2>
    <?=form_open('post/show/'.$id.'/comment', 'id="comment_form"');?>
    <?=lang('post_your_name');?> <b><?=htmlspecialchars($this->Game_Account->name);?></b>
    <br />
    <?=lang('post_your_comment', 'comment');?>
    <br />
    <?=form_textarea('comment', '', 'id="comment"');?>
    <br />
    <button id="commentBtn">
        <?=lang('general_save');?>
        <?=ui_loadicon();?>
    </button>
    <?=  ui_button("commentBtn");?>
    <?=form_close();?>
    <?php
    endif;
    ?>
</div>

<?=ui_ajax_post_form('comment_form', 'loadimg', '
    var nD = $("<div>").html(resp.bit);
    
    $("#commentboard").prepend(
        nD
    );
    
    nD.effect("highlight");
    
    $("#comment").val("");
');?> 

<?php $this->load->view("basic/footer"); ?>