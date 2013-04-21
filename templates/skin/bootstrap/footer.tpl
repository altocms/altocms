                {hook run='content_end'}
            </div> <!-- /content -->


            {if !$noSidebar && $sidebarPosition != 'left'}
                {include file='sidebar.tpl'}
            {/if}
        </div>
	</div> <!-- /wrapper -->

	
	<footer id="footer">
		<div class="copyright">
			{hook run='copyright'}
		</div>

        {$aLang.foot_develops}
		
		{hook run='footer_end'}
	</footer>

</div> <!-- /container -->

{include file='toolbar.tpl'}

{hook run='body_end'}

<!-- GA -->
<script type="text/javascript">

    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-37460186-1']);
    _gaq.push(['_setDomainName', 'wstandart.ru']);
    _gaq.push(['_setAllowLinker', true]);
    _gaq.push(['_trackPageview']);

    (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();

</script>
<!-- /GA -->

</body>
</html>