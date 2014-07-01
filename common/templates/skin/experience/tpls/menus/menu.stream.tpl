 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<ul>
    {if E::IsUser()}
       <li class="{if $sMenuItemSelect=='user'}active{/if}"><a class="link link-light-gray link-lead link-clear  {if $sMenuItemSelect=='user'}active{/if}" href="{router page='stream'}user/">{$aLang.stream_menu_user}</a></li>
    {/if}
    <li style="float: none" class="{if $sMenuItemSelect=='user'}active{/if}"><a class="link link-light-gray link-lead link-clear   {if $sMenuItemSelect=='all'}active{/if}" href="{router page='stream'}all/">{$aLang.stream_menu_all}</a></li>

    {hook run='menu_stream_item'}
</ul>
