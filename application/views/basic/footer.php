        
    </div>
    
    <div id="footnote">
        &copy; 2012 by <a href="http://www.agrafix.net">www.agrafix.net</a>
        - <a href="<?=site_url('home/imprint');?>">Impressum &amp; Disclaimer</a> <br />
        Powered by PHP5, CodeIgniter, jQuery, jQuery-UI and FamFamFam Icons
    </div>
    
    <?php if($this->User->is_loggedin()): ?>
    <div style="position:fixed;width:120px;height:600px;right:5px;top:40px;">
        <script type="text/javascript" src="http://www.sponsorads.de/script.php?s=199678"></script>
    </div>
    <script type="text/javascript" src="http://www.sponsorads.de/script.php?s=199679"></script>
    <?php endif; ?>
    
    <script type="text/javascript">

      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-28133338-1']);
      _gaq.push(['_trackPageview']);

      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();

    </script>
    
    </body>
</html>