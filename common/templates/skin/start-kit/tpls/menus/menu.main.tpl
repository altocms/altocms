<ul class="nav navbar-nav">
    <!-- li {if $sMenuHeadItemSelect=='blog'}class="active"{/if}><a href="{Config::Get('path.root.url')}">{$aLang.topic_title}</a></li -->
    {foreach Config::Get('view.menu.main.items') as $sKey=>$aMenuItem}
        {strip}
            <li {if $sMenuHeadItemSelect==$sKey}class="active"{/if}><a href="{$aMenuItem.url}">
                    {if $aMenuItem.text}
                        {$aMenuItem.text}
                    {elseif {$aMenuItem.lang}}
                        {$aLang[$aMenuItem.lang]}
                    {else}
                        {$sKey}
                    {/if}
                </a></li>
        {/strip}
    {/foreach}

    {hook run='main_menu_item'}
</ul>

{hook run='main_menu'}
