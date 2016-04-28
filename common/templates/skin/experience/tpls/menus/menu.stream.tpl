 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<ul>
    {if E::IsUser()}
       <li class="{if $sMenuItemSelect=='follow'}active{/if}">
           <a class="link link-light-gray link-lead link-clear  {if $sMenuItemSelect=='follow'}active{/if}" href="{R::GetLink("stream")}follow/">{$aLang.stream_menu_follow}</a>
       </li>
    {/if}
    <li style="float: none" class="{if $sMenuItemSelect=='user'}active{/if}">
        <a class="link link-light-gray link-lead link-clear   {if $sMenuItemSelect=='all'}active{/if}" href="{R::GetLink("stream")}all/">{$aLang.stream_menu_all}</a>
    </li>

    {hook run='menu_stream_item'}
</ul>
