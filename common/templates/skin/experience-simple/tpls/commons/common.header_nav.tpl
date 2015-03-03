{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike   *}

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
                    {*<a href="{router page='index'}">{$aLang.menu_homepage}</a>*}
                {*</li>*}
                {if $menu}
                    {if in_array($menu,$aMenuContainers)}{$aMenuFetch.$menu}{else}{include file="menus/menu.$menu.tpl"}{/if}
                {/if}
            </ul>
        </div>
    </div>
</nav>
