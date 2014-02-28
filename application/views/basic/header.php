<!DOCTYPE html>
<html>
    <head>
        <title>DSConnect - <?=lang('general_subtitle');?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        
        <meta name="google-site-verification" content="i-VarGGGo9wmrPBPuRp2poWb2pAFA_aC__iDBF_XVIw" />
        <meta name="msvalidate.01" content="C67B6113DD7CC64F2A7349E261D57439" />
        
        <link rel="shortcut icon" href="<?=site_url('favicon.ico');?>" type="image/x-icon" />
        
        <link rel="stylesheet" type="text/css" href="<?=site_url('static/style/connect.css');?>?v03" />
        <link rel="stylesheet" type="text/css" href="<?=site_url('static/style/humanity/jquery-ui.css');?>" />
        
        <script type="text/javascript" src="<?=site_url('static/script/jquery-1.6.2.min.js');?>"></script>
        <script type="text/javascript" src="<?=site_url('static/script/jquery-ui-1.8.16.min.js');?>"></script>
        <script type="text/javascript" src="<?=site_url('static/script/twutils.js');?>?v02"></script>
        <script type="text/javascript" src="<?=site_url('static/script/canvasmap.js');?>?v04"></script>
        
        <?=ui_init();?>
        <script type="text/javascript">
        TWUtils.controller = '<?=site_url('tools/utils');?>';
        TWUtils.siteTitle = 'DSConnect - <?=lang('general_subtitle');?>';
        TWUtils.selectedWorld = '<?=$this->User->selected_world;?>';
        TWUtils.dropdown_icon_path = '<?=site_url('static/image/flags');?>';
        
        TWUtils.dropdown_langs.push({'id': 'de', 'desc': 'Deutsch', 'internal': 'german'});
        TWUtils.dropdown_langs.push({'id': 'us', 'desc': 'English', 'internal': 'english'});
        
        $(function() {
            $('#topbar .title').click(function() {
                top.location.href = "<?=site_url("home");?>";
            })
        });
        </script>
    </head>
    <body>
        <div id="topbar">
            <span class="title">DSConnect</span>
            
            
            <?php if($this->User->is_loggedin()): ?>
                <a href="<?=site_url('usercp/quit');?>"><?=lang('general_logout');?> <?=ui_ficon('door_out');?></a>
                <a href="<?=site_url('usercp/change_world');?>">
                    (<?=ui_ficon('world', lang('general_world'));?> <span id='sel_world'><?=$this->User->selected_world;?></span>) 
                    <?=lang('general_change_world');?>
                </a>
                
                <a href="<?=site_url('usercp/accounts');?>"><?=ui_ficon('user_go');?> <?=lang('general_accounts');?></a>
                
                <a href="<?=site_url('home');?>"><?=lang('general_home');?></a>
                
            <?php else: ?>
                <a href="<?=site_url('usercp/login');?>"><?=lang('general_login');?> <?=ui_ficon('door_in');?></a>
                <a href="<?=site_url('usercp/signup');?>"><?=lang('general_signup');?></a>
                
                <a href="<?=site_url('home');?>"><?=lang('general_home');?></a>
            <?php endif; ?>
                
            <a href="#" id="changeLanguageBtn">
                <img src="<?=site_url('static/image/flags/'.($this->User->lng == 'german' ? 'de': 'us').'.png');?>" 
                     alt="<?=$this->User->lng;?>" />
            </a>
                
            <br style="clear:both; visability:hidden;" />
        </div>
        
        <div id="container">