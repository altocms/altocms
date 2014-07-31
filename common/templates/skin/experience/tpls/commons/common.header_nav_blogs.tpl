 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<!-- МЕНЮ ТРЕТЬЕГО УРОВНЯ -->
<div class="menu-level-3-container">
    <div class="container">

        <ul class="menu-level-3">
            {$sItemSelect=Config::Get('view.menu.blogs.select')}
            {foreach Config::Get('view.menu.blogs.items') as $sKey=>$aMenuItem}
                {strip}
                    <li {if $sItemSelect==$sKey}class="active"{/if}>
                        <a href="{$aMenuItem.url}">
                            {$aMenuItem.text}
                        </a>
                    </li>
                {/strip}
            {/foreach}
        </ul>

    </div>
</div>