 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if E::IsAdmin()}
    <div class="toolbar-admin toolbar-button">
        <a href="{R::GetLink("admin")}" title="{$aLang.admin_title}" title="admin panel" target="_blank">
            <span class="fa fa-cogs"></span>
        </a>
    </div>
{/if}
