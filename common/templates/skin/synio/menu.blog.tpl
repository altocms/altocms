<ul class="nav nav-menu">
    <li {if $sMenuItemSelect=='index' AND $sMenuSubItemSelect=='good'}class="active"{/if}>
        <a href="{Config::Get('path.root.url')}">{$aLang.blog_menu_all}</a>
    </li>

    <li {if $sMenuSubItemSelect=='new'}class="active"{/if}>
        <a href="{router page='index'}newall/">{$aLang.blog_menu_all_new}</a>
        {*if $iCountTopicsNew>0}<a href="{router page='index'}new/" class="new" title="{$aLang.blog_menu_top_period_24h}">+{$iCountTopicsNew}</a>{/if*}
    </li>

    {if $oUserCurrent}
        <li {if $sMenuItemSelect=='feed'}class="active"{/if}>
            <a href="{router page='feed'}">{$aLang.userfeed_title}</a>
        </li>
    {/if}

    {hook run='menu_blog'}
</ul>