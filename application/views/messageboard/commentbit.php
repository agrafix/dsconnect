<div class="commentbit">
    <p>
        <a href="<?=site_url('profile/player/'.$player_id);?>">
            <?=htmlspecialchars($player_name);?>: 
        </a> 
        <span>(<?=date(lang('general_basic_date'), $time);?>)</span> <br />
        <?=htmlspecialchars($message);?>
    </p>
</div>