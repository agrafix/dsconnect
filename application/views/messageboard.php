<?php $this->load->view("basic/header"); ?>
            <div id="nav">
                <div class="navbox">
                    <h1><?=lang('messageboard_main_menu');?></h1>
                    
                    <a href="<?=site_url("home");?>">
                        <?=ui_ficon('newspaper');?> <?=lang('messageboard_desc');?>
                    </a>
                    <a href="<?=site_url("profile/player/".$this->Game_Account->id);?>">
                        <?=ui_ficon('user_orange');?> <?=lang('messageboard_your_profile');?>
                    </a>
                    <a href="<?=site_url("profile/lists/player-villages/".$this->Game_Account->id);?>">
                        <?=ui_ficon('house');?> <?=lang('messageboard_your_villages');?>
                    </a>
                    <a href="<?=site_url("profile/ally/".$this->Game_Account->ally_id);?>">
                        <?=ui_ficon('group');?> <?=lang('messageboard_your_ally');?>
                    </a>
                    <a href="<?=site_url("search");?>">
                        <?=ui_ficon('magnifier');?> <?=lang('general_search');?>
                    </a>
                </div>
                
                <div class="navbox">
                    <h1><?=lang('messageboard_tools');?></h1>
                    
                    <a href="<?=site_url("tools/attack");?>">
                        <?=ui_ficon('bomb');?> <?=lang('messageboard_attack');?>
                    </a>
                    <a href="<?=site_url("tools/map");?>">
                        <?=ui_ficon('map');?> <?=lang('messageboard_map');?>
                    </a>
                </div>
            </div>
            
            <div id="content">
                <div id="status_post">
                    <?=form_open('');?>
                    <input class="ui-widget ui-state-default ui-corner-all" type="text" value="<?=lang('messageboard_whats_happening');?>" id="new_status" name="post" />
                    <button id="new_status_btn">
                        <?=ui_ficon('newspaper_go', lang('messageboard_post'));?> <?=lang('messageboard_post');?>
                        <?=ui_loadicon();?>
                    </button>
                    <br />
                        <div id="visability">
                        <?=ui_ficon('eye', lang('messageboard_visible_for'));?>
                        <?=lang("messageboard_visible_all", 'vall');?>
                            <input type="radio" name="visible" value="all" id="vall" />
                        <?=lang("messageboard_visible_world", 'vworld');?>
                            <input type="radio" name="visible" value="world" id="vworld" checked="checked" />
                        <?=lang("messageboard_visible_ally", 'vally');?>
                            <input type="radio" name="visible" value="ally" id="vally" />
                        <?=lang("messageboard_visible_me", 'vme');?>
                            <input type="radio" name="visible" value="me" id="vme" />
                        </div>
                    
                    <?=ui_button("new_status_btn");?>
                    <?=form_close();?>
                </div>
                
                <script type="text/javascript">
                var defaultText = '<?=addslashes(lang('messageboard_whats_happening'));?>';
                
                $(function() {
                    TWUtils.msgBoard_since = '<?=time();?>';
                    window.setInterval(function() {
                      TWUtils.updateMsgBoard('msgboard', 'all', -1, true, 
                    "<?=$this->security->get_csrf_token_name();?>", "<?=$this->security->get_csrf_hash();?>");
                     
                    }, 60000);
                    
                    $('#visability').buttonset();
                    
                    $('#new_status').focus(function() {
                        if ($(this).val() == defaultText) {
                            $(this).val('');
                        } 
                    }).blur(function() {
                        if ($(this).val() == '') {
                            $(this).val(defaultText);
                        }
                    });
                    
                    $('form').submit(function(event) {
                       event.preventDefault();
                       
                       $('#loadimg').fadeIn();
                       
                       if ($('#new_status').val() == defaultText
                               || $('#new_status').val() == '') {
                               return;
                               }
                       
                       var data = $('input', this).serializeArray();
                       
                       $.post('<?=site_url('home/store_post');?>', data, function(json) {
                           $('#loadimg').fadeOut();
                           
                           if (json.error) {
                               alert('Es ist ein Fehler aufgetreten.');
                               return;
                           }
                           
                           $('#new_status').val(defaultText);
                           var newDiv = $('<div>').html(json.bit);
                           $('#msgboard').prepend(newDiv);
                       }, "json");
                    });
                })</script>
                
                <div id="msgboard">
                <?php
                if (empty($posts)) {
                    echo ui_msgbox(lang('messageboard_first_steps'));
                }
                ?>
                <?php foreach($posts as $post): ?>
                <?php $this->load->view('messageboard/threadbit', $post); ?>
                <?php endforeach; ?>
                </div>

            </div>
<?php $this->load->view("basic/footer"); ?>    