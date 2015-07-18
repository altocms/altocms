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
                {menu id='user' class='nav navbar-nav navbar-right' hideul=true}
            {else}
                {menu id='login' class='nav navbar-nav navbar-right' hideul=true}
            {/if}

        </ul>
        <!-- главное меню сайта -->
    </div>
    <!-- div.menu-level-1-container" -->

</div>