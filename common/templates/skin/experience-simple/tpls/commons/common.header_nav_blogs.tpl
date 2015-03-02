 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<!-- МЕНЮ ТРЕТЬЕГО УРОВНЯ -->
<div class="menu-level-3-container">
    <div class="container">

        {*{menu id='blog_list' class='menu-level-3'}*}
        {if $menu}
            {if in_array($menu,$aMenuContainers)}{$aMenuFetch.$menu}{else}{include file="menus/menu.$menu.tpl"}{/if}
        {/if}

    </div>
</div>

