{foreach $aPagesMain as $oPage}
    <li {if $sAction=='page' AND $sEvent==$oPage->getUrl()}class="active"{/if}>
        <a href="{router page='page'}{$oPage->getUrlFull()}/" >{$oPage->getTitle()}</a>
    </li>
{/foreach}