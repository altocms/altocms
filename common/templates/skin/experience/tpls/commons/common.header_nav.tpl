 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<!-- ГЛАВНОЕ МЕНЮ САЙТА -->
<div class="menu-level-1-container">
    <div class="container">

        <!-- КНОПКА СКРЫТОГО МЕНЮ -->
        <ul class="menu-level-1 right menu-hidden-container-box">
            <li class="btn dropdown right menu-hidden-container">
                <a data-toggle="dropdown" href="#" class="menu-hidden-trigger">
                    <i class="fa fa-chevron-circle-down"></i>
                </a>
                <!-- контейнер скрытых элементов -->
                <ul class="menu-hidden dropdown-menu"></ul>
            </li>
        </ul>

        <!-- МЕНЮ САЙТА -->
        <ul class="menu-level-1 main-menu">

            {if $menu}
                {if in_array($menu,$aMenuContainers)}{$aMenuFetch.$menu}{else}{include file="menus/menu.$menu.tpl"}{/if}
            {/if}

            {hook run='menu_blog'}

            {if E::IsUser()}
                <!-- МЕНЮ ПОЛЬЗОВАТЕЛЯ -->
                <li class="btn dropdown right" data-hidden-class="btn">
                    <a class="userlogo" data-toggle="dropdown" href="{E::User()->getProfileUrl()}">
                        <img src="{E::User()->getAvatarUrl(24)}" alt="{E::User()->getDisplayName()}" class="user"/>
                        {E::User()->getDisplayName()}&nbsp;<i class="caret"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{E::User()->getProfileUrl()}">{$aLang.user_menu_profile}</a></li>
                        <li>
                            <a href="{router page='talk'}" id="new_messages"  title="{if $iUserCurrentCountTalkNew}{$aLang.user_privat_messages_new}{/if}">
                                {$aLang.user_privat_messages}{if $iUserCurrentCountTalkNew} <span class="new-messages">+{$iUserCurrentCountTalkNew}</span>{/if}
                            </a>
                        </li>
                        <li>
                            <a href="{E::User()->getProfileUrl()}wall/">{$aLang.user_menu_profile_wall}</a>
                        </li>
                        <li>
                            <a href="{E::User()->getProfileUrl()}created/topics/">{$aLang.user_menu_publication}</a>
                        </li>
                        <li>
                            <a href="{E::User()->getProfileUrl()}favourites/topics/">{$aLang.user_menu_profile_favourites}</a>
                        </li>
                        <li>
                            <a href="{router page='settings'}profile/">{$aLang.user_settings}</a>
                        </li>
                        {hook run='userbar_item'}
                        <li class="divider"></li>
                        <li>
                            <a href="{router page='login'}exit/?security_key={$ALTO_SECURITY_KEY}">{$aLang.exit}</a>
                        </li>
                    </ul>
                </li>

                <!-- ПОЧТОВЫЕ СООБЩЕНИЯ -->
                <li class="btn right inbox" data-hidden-class="btn right">
                {if $iUserCurrentCountTalkNew}
                    <a href="{router page='talk'}" class="messages">
                        <i class="fa fa-envelope-o"></i>
                        +&nbsp;{$iUserCurrentCountTalkNew}
                        {*<em>&nbsp;{$iUserCurrentCountTalkNew|declension:$aLang.personal_messages:'russian'}</em>*}
                    </a>
                {/if}
                </li>

                <!-- КНОПКА СОЗДАТЬ -->
                <li class="btn create right" data-hidden-class="btn right" data-toggle="modal" data-target="#modal-write">
                    <a href="#" onclick="return false;">{$aLang.block_create}</a>
                </li>
            {else}
                {hook run='userbar_item'}
                <li  class="btn right" data-hidden-class="btn right">
                    <a href="#" onclick="return false;" class="js-modal-auth-registration">{$aLang.registration_submit}</a>
                </li>
                <li class="btn right" data-hidden-class="btn right">
                    <a href="#" onclick="return false;" class="js-modal-auth-login">{$aLang.user_login_submit}</a>
                </li>
            {/if}

        </ul>
        <!-- главное меню сайта -->
    </div>
    <!-- div.menu-level-1-container" -->

</div>