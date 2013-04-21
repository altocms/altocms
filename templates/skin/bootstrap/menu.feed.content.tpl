<ul class="nav nav-pills mb-30">
	<li {if $sMenuSubItemSelect=='feed'}class="active"{/if}><a href="{router page='feed'}">{$aLang.subscribe_menu}</a></li>
	<li {if $sMenuSubItemSelect=='track'}class="active"{/if}><a href="{router page='feed'}track/">{$aLang.subscribe_tracking_menu}{if $iUserCurrentCountTrack} +{$iUserCurrentCountTrack}{/if}</a></li>
</ul>