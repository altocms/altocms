 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<!-- МЕНЮ ПЕРВОГО УРОВНЯ -->
<li class="btn active" data-hidden-class="btn">
    <a href="{Config::Get('path.root.url')}">{$aLang.menu_homepage}</a>
</li>
{*<li class="btn {if $sMenuSubItemSelect=='good'} active{/if}" data-hidden-class="btn">*}
    {*<a href="{Config::Get('path.root.url')}">{$aLang.blog_menu_all_good}</a>*}
{*</li>*}
<li class="btn {if $sMenuSubItemSelect=='new'} active{/if}" data-hidden-class="btn">
    {if $iCountTopicsNew>0}
        <a href="{router page='index'}newall/" title="{$aLang.blog_menu_top_period_24h}">
            {$aLang.blog_menu_all_new} +{$iCountTopicsNew}
        </a>
    {else}
        <a href="{router page='index'}newall/" title="{$aLang.blog_menu_top_period_all}">
            {$aLang.blog_menu_all_new}
        </a>
    {/if}
</li>

<li class="btn final {if $sMenuItemSelect=='feed'} active{/if}" data-hidden-class="btn final">
    <a href="{router page='feed'}">{$aLang.userfeed_title}</a>
</li>

<!-- ОБСУЖДАЕМЫЕ -->
<li class="btn dropdown active" data-hidden-class="btn">
    <a class="{if $sMenuSubItemSelect=='discussed'} active{/if}" data-toggle="dropdown" href="{router page='index'}discussed/">
        {$aLang.blog_menu_all_discussed}&nbsp;<i class="caret"></i>
    </a>
    <ul class="dropdown-menu">
        <li  class="labeled {if $sMenuSubItemSelect=='discussed' & $sPeriodSelectCurrent=='1'}active{/if}">
            <a href="{router page='index'}discussed/?period=1">{$aLang.blog_menu_top_period_24h}</a>
        </li>
        <li class="labeled {if $sMenuSubItemSelect=='discussed' & $sPeriodSelectCurrent=='7'}active{/if}">
            <a href="{router page='index'}discussed/?period=7">{$aLang.blog_menu_top_period_7d}</a>
        </li>
        <li class="labeled {if $sMenuSubItemSelect=='discussed' & $sPeriodSelectCurrent=='30'}active{/if}">
            <a href="{router page='index'}discussed/?period=30">{$aLang.blog_menu_top_period_30d}</a>
        </li>
        <li class="labeled {if $sMenuSubItemSelect=='discussed' & $sPeriodSelectCurrent=='all'}active{/if}">
            <a href="{router page='index'}discussed/?period=all">{$aLang.blog_menu_top_period_all}</a>
        </li>
    </ul>
</li>

<!-- ТОП -->
<li class="btn dropdown" data-hidden-class="btn">
    <a class="{if $sMenuSubItemSelect=='top'} active{/if}" data-toggle="dropdown" href="#">
        {$aLang.blog_menu_all_top}&nbsp;<i class="caret"></i>
    </a>
    <ul class="dropdown-menu">
        <li {if $sMenuSubItemSelect=='top' & $sPeriodSelectCurrent=='1'}class="active"{/if}>
            <a href="{router page='index'}top/?period=1">{$aLang.blog_menu_top_period_24h}</a>
        </li>
        <li {if $sMenuSubItemSelect=='top' & $sPeriodSelectCurrent=='7'}class="active"{/if}>
            <a href="{router page='index'}top/?period=7">{$aLang.blog_menu_top_period_7d}</a>
        </li>
        <li {if $sMenuSubItemSelect=='top' & $sPeriodSelectCurrent=='30'}class="active"{/if}>
            <a href="{router page='index'}top/?period=30">{$aLang.blog_menu_top_period_30d}</a>
        </li>
        <li {if $sMenuSubItemSelect=='top' & $sPeriodSelectCurrent=='all'}class="active"{/if}>
            <a href="{router page='index'}top/?period=all">{$aLang.blog_menu_top_period_all}</a>
        </li>
    </ul>
</li>

{hook run='menu_blog_index_item'}

