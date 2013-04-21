<nav id="nav">
    <ul class="b-nav-main">
        <li {if $sMenuHeadItemSelect=='blog'}class="active"{/if}>
            <a href="{cfg name='path.root.web'}">{$aLang.blog_menu_all}</a>
        </li>
        {if count($aContentTypes)>1}
            {foreach from=$aContentTypes item=oType}
            <li {if $sMenuHeadItemSelect=='filter' && $sEvent==$oType->getContentUrl()}class="active"{/if}>
                <a href="{router page='filter'}{$oType->getContentUrl()}/">{$oType->getContentTitleDecl()|escape:'html'}</a>
            </li>
            {/foreach}
        {/if}
        <li {if $sMenuHeadItemSelect=='blogs'}class="active"{/if}>
            <a href="{router page='blogs'}">{$aLang.blogs}</a>
        </li>
        <li {if $sMenuHeadItemSelect=='people'}class="active"{/if}>
            <a href="{router page='people'}">{$aLang.people}</a>
        </li>
        <li {if $sMenuHeadItemSelect=='stream'}class="active"{/if}>
            <a href="{router page='stream'}">{$aLang.stream_menu}</a>
        </li>
    {hook run='main_menu_item'}
    </ul>
{hook run='main_menu'}
</nav>