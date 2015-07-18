 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<ul class="">
    <li {if $sMenuSubItemSelect=='feed'}class="active"{/if}>
        <a class="link link-light-gray link-lead link-clear {if $sMenuSubItemSelect=='feed'}active{/if}" href="{router page='feed'}">{$aLang.subscribe_menu}</a>
    </li>
    <li style="float: none;" {if $sMenuSubItemSelect=='track'}class="active"{/if}>
        <a class="link link-light-gray link-lead link-clear {if $sMenuSubItemSelect=='track'}active{/if}" href="{router page='feed'}track/">{$aLang.subscribe_tracking_menu}</a>
    </li>
    {if $iUserCurrentCountTrack}
        <li style="float: none;" {if $sMenuSubItemSelect=='track_new'}class="active"{/if}>
            <a class="link link-light-gray link-lead link-clear {if $sMenuSubItemSelect=='track_new'}active{/if}" href="{router page='feed'}track/new/">{$aLang.subscribe_tracking_menu_new} +{$iUserCurrentCountTrack}</a>
        </li>
    {/if}
</ul>