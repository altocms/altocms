<ul class="nav nav-pills">
    {if E::IsUser()}
        <li {if $sMenuItemSelect=='follow'}class="active"{/if}>
            <a href="{R::GetLink("stream")}follow/">{$aLang.stream_menu_follow}</a>
        </li>
    {/if}
    <li {if $sMenuItemSelect=='all'}class="active"{/if}>
        <a href="{R::GetLink("stream")}all/">{$aLang.stream_menu_all}</a>
    </li>

    {hook run='menu_stream_item'}
</ul>

{hook run='menu_stream'}
