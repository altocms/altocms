 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<div class="row">
    <div class="col-lg-12 user-toggle-publication-block">

        <a class="btn btn-light-gray {if $sMenuItemSelect=='all'}active{/if}" href="{R::GetLink("people")}">{$aLang.people_menu_users_all}</a>
        <a class="btn btn-light-gray {if $sMenuItemSelect=='online'}active{/if}" href="{R::GetLink("people")}online/">{$aLang.people_menu_users_online}</a>
        <a class="btn btn-light-gray {if $sMenuItemSelect=='new'}active{/if}" href="{R::GetLink("people")}new/">{$aLang.people_menu_users_new}</a>

        {hook run='menu_people_people_item'}
    </div>
    {hook run='menu_people'}
</div>