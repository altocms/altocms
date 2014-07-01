 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if E::IsUser()}
<div class="toolbar-button toolbar-user toolbar-menu-popover">
    <div id="hidden-toolbar-user-content" style="display: none;">
        <ul class="toolbar-menu">
            <li><a href="{E::User()->getProfileUrl()}">
                    <span><i class="fa fa-user"></i></span><span>{$aLang.user_menu_profile}</span>
                </a></li>
            <li>
                <a href="{router page='talk'}">
                    <span><i class="fa fa-envelope"></i></span><span>{$aLang.user_privat_messages}</span>
                </a>
            </li>
            <li>
                <a href="{E::User()->getProfileUrl()}favourites/topics/">
                    <span><i class="fa fa-star"></i></span><span>{$aLang.user_menu_profile_favourites}</span>
                </a>
            </li>
            <li>
                <a href="{router page='settings'}profile/">
                    <span><i class="fa fa-cog"></i></span><span>{$aLang.user_settings}</span>
                </a>
            </li>
            <li class="divider"></li>
            <li>
                <a href="{router page='login'}exit/?security_key={$ALTO_SECURITY_KEY}">
                    <span><i class="fa fa-sign-out"></i></span><span>{$aLang.exit}</span>
                </a>
            </li>
        </ul>
    </div>
    <a href="#"
       onclick="return false;"
       data-toggle="popover"
       class="toolbar-exit-button link link-light-gray"><span class="fa fa-user"></span></a>
</div>
{/if}


