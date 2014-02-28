<?php
/**
 * DSConnect
 * 
 * @author Alexander Thiemann <mail@agrafix.net>
 */
?>

<?php $this->load->view("basic/header"); ?>

<div style="padding:10px;">
    <h2><?= lang('general_search'); ?></h2>

    <?= ui_msgbox(lang('search_info')); ?>

    <?= form_open(''); ?>
    <input class="ui-widget ui-state-default ui-corner-all" style="width:400px;" type="text" value="" name="search" />
    <button id="new_search">
        <?= ui_ficon('magnifier', lang('general_search')); ?> <?= lang('general_search'); ?>
        <?= ui_loadicon(); ?>
    </button>
    <?= form_close(); ?>
    
    <div id="search_results" style="margin-top:30px;display:none;">
        <h2><?=lang('search_results');?></h2>
        
        <div id="search_result_display">
            <h3><a href="#"><?=lang('profile_villages');?></a></h3>
            <div>
                <ul id="search_result_village">

                </ul>
            </div>
            
            <h3><a href="#"><?=lang('profile_players');?></a></h3>
            <div>
                <ul id="search_result_player">
                    
                </ul>
            </div>
            
            <h3><a href="#"><?=lang('profile_allys');?></a></h3>
            <div>
                <ul id="search_result_ally">
                    
                </ul>
            </div>
        </div>

    </div>
</div>

<script type="text/javascript">
    $(function() {
        $('#search_result_display').accordion({
                    autoHeight: false
                });
        
        $('#new_search')
        .button();
        
        $('form').submit(function (event) {
            event.preventDefault(); 
            
            $('#loadimg').fadeIn();
            $('#search_results').fadeOut();
            
            var data = $('input', this).serializeArray();
                       
            $.post('<?= site_url('search/run'); ?>', data, function(json) {
                $('#loadimg').fadeOut();
                           
                if (json.error) {
                    alert('Es ist ein Fehler aufgetreten.');
                    return;
                }
                
                var player_r = $('#search_result_player');
                var ally_r = $('#search_result_ally');
                var village_r = $('#search_result_village');
                
                player_r.empty(); ally_r.empty(); village_r.empty();
                
                for(var i in json.players)
                {
                    var p = json.players[i];
                    
                    var a = $('<a>')
                                .attr('href', '<?=site_url('profile/player/');?>/' + p.id)
                                .text(p.name);
                    player_r.append($('<li>').append(a));
                }
                
                for(var i in json.allys)
                {
                    var p = json.allys[i];
                    
                    var a = $('<a>')
                                .attr('href', '<?=site_url('profile/ally/');?>/' + p.id)
                                .text(p.name + " (" + p.tag + ")");
                    ally_r.append($('<li>').append(a));
                }
                
                for(var i in json.villages)
                {
                    var p = json.villages[i];
                    
                    var a = $('<a>')
                                .attr('href', '<?=site_url('profile/village/');?>/' + p.id)
                                .text(p.name + ' (' + p.x + '|' + p.y + ')');
                                
                                
                    village_r.append($('<li>').append(a));
                }
                
                $('#search_results').fadeIn();
                
                
            }, "json");
        });
    });
</script>

<?php $this->load->view("basic/footer"); ?>