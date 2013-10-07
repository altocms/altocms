<ul class="nav nav-pills mb-30">
	<li {if $sMenuSubItemSelect=='feed'}class="active"{/if}><a href="{router page='feed'}">{$aLang.subscribe_menu}</a></li>
	<li {if $sMenuSubItemSelect=='track'}class="active"{/if}><a href="{router page='feed'}track/">{$aLang.subscribe_tracking_menu}</a></li>
    {if $iUserCurrentCountTrack}<li {if $sMenuSubItemSelect=='track_new'}class="active"{/if}><a href="{router page='feed'}track/new/">{$aLang.subscribe_tracking_menu_new} +{$iUserCurrentCountTrack}</a></li>{/if}
</ul>