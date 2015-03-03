 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if E::IsUser()}
<div class="toolbar-button toolbar-user toolbar-menu-popover">
    <div id="hidden-toolbar-user-content" style="display: none;">

        <ul class="toolbar-menu">
            <li>
                <ul class="toolbar-menu-user">
                    {menu id='toolbar_user' hideul=true}
                </ul>
            </li>
            <li class="user_activity_items">
                <ul class="toolbar-menu-info">
                    {menu id='userinfo' class='' hideul=true}
                </ul>
            </li>
            {menu id='toolbar_userbar' hideul=true}
        </ul>
    </div>
    <a href="#"
       onclick="return false;"
       data-toggle="popover"
       class="toolbar-exit-button link link-light-gray"><span class="fa fa-user"></span></a>
</div>
{/if}


