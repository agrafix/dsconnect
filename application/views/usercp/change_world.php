<?php
/**
 * DSConnect
 * 
 * @author Alexander Thiemann <mail@agrafix.net>
 */
?>

<?php $this->load->view("basic/header"); ?>

<script type="text/javascript">
	$(function() {
                $('#world-list').accordion({
                    autoHeight: false
                });
                
		$( ".world_select" ).selectable({
                    selected: function(event, ui) {
                        $("#errorbox, #okbox").hide();
                        $("#loadimg").fadeIn();
                        
                        var w = $(".ui-selected", this).attr('title');
                        $.post("<?=site_url("usercp/store_world");?>", { 
                            "world": w,
                            "<?=$this->security->get_csrf_token_name();?>": "<?=$this->security->get_csrf_hash();?>" 
                        },
                        function(data){
                            $("#loadimg").fadeOut();
                            
                            if(data.error) {
                                $("#errorbox").slideDown();
                            }
                            else {
                                 $("#okbox").slideDown();
                                 $("#sel_world").text(data.world);
                            }
                        }, "json");
                    }
                });
	});
</script>

<div style="padding:10px">
    <h2><?=lang('general_change_world');?> <?=ui_loadicon();?></h2>
    
<?=ui_error(lang('general_change_world_error'), 'errorbox', 'display:none;');?>
<?=ui_msgbox(lang('general_change_world_ok'), 'okbox', 'display:none;');?>
<div id="world-list">
    <?php
    $prev_lang = "";
    
    foreach($this->config->item("tw_worlds") as $world):
        $curr_lang = strtoupper(substr($world, 0, 2));

        if ($curr_lang != $prev_lang) {
            
            if ($prev_lang != "") {
                echo '</ul></div>';
            }
            
            $prev_lang = $curr_lang;
            
            echo '<h3><a href="#">'.$curr_lang.'</a></h3><div>
                <ul class="world_select">';
        }
    ?>
    <li title="<?=$world;?>" class="ui-state-default <?php if($world == $this->User->selected_world) { echo 'ui-selected'; } ?>">
        <?=ui_ficon("world", lang("general_world"));?> 
        <?=$world;?>
    </li>
    <?php
    endforeach;
    ?>
</div>    
</div>

<?php $this->load->view("basic/footer"); ?>