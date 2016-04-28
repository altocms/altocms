{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<script>
    $(function(){
        $('.dropdown-content-menu a').addClass('hvr-leftline-reveal');
    })
</script>

<nav class="navbar navbar-default navbar-content" role="navigation">
    <div class="container">
        <div id="navbar-content">
            <ul class="main nav navbar-nav">
                {*<li class="{if $sMenuSubItemSelect=='good'}active{/if}">*}
                    {*<a href="{R::GetLink("index")}">{$aLang.menu_homepage}</a>*}
                {*</li>*}
                {if $menu}
                    {if in_array($menu,$aMenuContainers)}{$aMenuFetch.$menu}{else}{include file="menus/menu.$menu.tpl"}{/if}
                {/if}
                <li class="dropdown right menu-hidden-container hidden">
                    <a data-toggle="dropdown" href="#" class="menu-hidden-trigger">
                        {$aLang.more}<span class="caret"></span>
                    </a>
                    <!-- контейнер скрытых элементов -->
                    <ul class="header-menu-hidden dropdown-menu animated fadeIn dropdown-content-menu"></ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
