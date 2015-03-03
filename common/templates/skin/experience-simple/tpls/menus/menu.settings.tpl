 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

    <ul>
        <li><a class="link link-light-gray link-lead link-clear {if $sMenuSubItemSelect=='profile'}active{/if}" href="{router page='settings'}profile/">{$aLang.settings_menu_profile}</a></li>
        <li><a class="link link-light-gray link-lead link-clear {if $sMenuSubItemSelect=='account'}active{/if}" href="{router page='settings'}account/">{$aLang.settings_menu_account}</a></li>
        <li>
            <a class="link link-light-gray link-lead link-clear {if $sMenuSubItemSelect=='tuning'}active{/if}" href="{router page='settings'}tuning/">
                <span class="visible-xs hidden-md hidden-sm hidden-lg">{$aLang.settings_menu_tuning_short}</span>
                <span class="hidden-xs visible-md visible-sm visible-lg">{$aLang.settings_menu_tuning}</span>

            </a>
        </li>

        {if Config::Get('general.reg.invite')}
            <li><a class="link link-light-gray link-lead link-clear {if $sMenuItemSelect=='invite'}active{/if}" href="{router page='settings'}invite/">{$aLang.settings_menu_invite}</a></li>
        {/if}

        {hook run='menu_settings_settings_item'}
    </ul>


{hook run='menu_settings'}