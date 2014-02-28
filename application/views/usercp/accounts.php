<?php
/**
 * DSConnect
 * 
 * @author Alexander Thiemann <mail@agrafix.net>
 */
?>

<?php $this->load->view("basic/header"); ?>

<div style="padding:10px;">
    <h2><?=lang('general_accounts');?></h2>
    
    <?php if ($error_id != 0): ?>
    <?=ui_error(lang('accounts_error_'.$error_id));?>
    <?php endif; ?>
    
    <?=ui_msgbox(lang('accounts_text'));?>
    
    <h3><?=lang('accounts_connected_accounts');?> <?=ui_loadicon();?></h3>
    
    <table>
        <tr>
            <th><?=lang('general_name');?></th>
            <th><?=lang('general_world');?></th>
            <th><?=lang('general_remove');?></th>
        </tr>
        
        <?php foreach($accounts as $account): ?>
        <tr id="account_link_<?=$account['linkid'];?>">
            <td>
                <?=anchor('profile/player/'.$account['ds_id'].'?world='.$account['world'], htmlspecialchars(urldecode($account['name'])));?>
            </td>
            <td><?=$account['world'];?></td>
            <td style='text-align:center;'>
                <span class="account_unlink" title="<?=$account['linkid'];?>">
                    <?=ui_ficon('link_break', lang('general_remove'));?>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <script type="text/javascript">
    $(function() {
       $(".account_unlink").button().click(function() {
           var id = $(this).attr('title');
           
           $('#loadimg').fadeIn();
  
           $.post("<?=site_url('usercp/account_unlink');?>", 
                  {
                      'id': id, 
                      "<?=$this->security->get_csrf_token_name();?>": "<?=$this->security->get_csrf_hash();?>"
                  },
                  function(data) {
                      $('#loadimg').fadeOut();
                      
                      if (!data.error)
                      {
                          $('#account_link_' + data.id).remove();
                      }
                  }, "json");
       }); 
    });
    </script>
    
    <?php if(empty($accounts)): ?>
    <p><i>keine</i></p>
    <?php endif; ?>
    
    <h3><?=lang('accounts_connect_account');?></h3>
    
    <?=ui_error(lang('accounts_connect_new_text'));?>
    
    <p><?=lang('general_current_world');?>: <?=$this->User->selected_world;?> (<?=anchor('usercp/change_world', lang('general_change'));?>)</p>
    
    <a id="link_add" href="<?=Tw_import::get_tw_base_host($this->User->selected_world);?>external_auth.php?sid=<?=$this->User->selected_world;?>|<?=$this->User->api_hash;?>&client=dsconn">
        <?=ui_ficon('link_add', lang('accounts_connect_new_account'));?> 
        <?=lang('accounts_connect_new_account');?>
    </a>
    <?=ui_button("link_add");?>
</div>

<?php $this->load->view("basic/footer"); ?>