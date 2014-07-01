 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

    {foreach Config::Get('view.header.menu.items') as $sKey=>$aMenuItem}
        {strip}
            <li {if $sMenuHeadItemSelect==$sKey}class="active"{/if}>
                <a href="{$aMenuItem.url}">
                    <i class="{$aMenuItem.icon_class}"></i>
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
