<div class="threadbit">
    <a href="<?=site_url('profile/'.$type.'/'.$type_id);?>">
        <?php 
        if ($type == 'village') {
            echo ui_ficon('house');
        }
        elseif ($type == 'player') {
            echo ui_ficon('user_orange');
        }
        else {
            echo ui_ficon('group');
        }
        ?>
        <?= htmlspecialchars($type_name); ?>
    </a>
    <p style="margin-top: 5px;"><?php
if ($user_id != -1)
{
    echo htmlspecialchars($post);
}
else
{
    echo tw_bbcode($post);
}
?></p>
    <span><a href="<?=site_url('post/show/'.$id);?>"><?=lang('general_comments');?> (<?=$comment_count;?>)</a> - 
<?= date(lang('general_post_date'), $time); ?></span>
</div>