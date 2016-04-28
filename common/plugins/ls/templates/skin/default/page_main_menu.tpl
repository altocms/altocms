{foreach from=$aPagesMain item=oPage}
	<li {if $sAction=='page' and $sEvent==$oPage->getUrl()}class="active"{/if}><a href="{R::GetLink("page")}{$oPage->getUrlFull()}/" >{$oPage->getTitle()}</a><i></i></li>
{/foreach}	