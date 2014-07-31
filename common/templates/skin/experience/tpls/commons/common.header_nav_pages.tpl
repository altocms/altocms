 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<!-- МЕНЮ ВТОРОГО УРОВНЯ -->
<div class="menu-level-2-container">
    <div class="container">

        <ul class="menu-level-2 main">

<!-- Логотип -->
<li class="menu-level-2-logo">
    <a class="logo" href="{Config::Get('path.root.url')}">
        {if Config::Get('view.header.logo.file')}
            {$sFile = Config::Get('view.header.logo.file')}
            <img src="{asset file="images/$sFile" theme=true}" alt="{Config::Get('view.name')}" class="logo-img" />
        {elseif Config::Get('view.header.logo.url')}
            <img src="{Config::Get('view.header.logo.url')}" alt="{Config::Get('view.name')}" class="logo-img" />
        {/if}
        {if Config::Get('view.header.logo.name')}
            <span class="logo-name" >{Config::Get('view.header.logo.name')}</span>
        {/if}
    </a>
    <a href="#" onclick="return false;" class="bars">
        <i class="fa fa-bars"></i>
    </a>
</li>

            {hook run='header_top_begin'}

            {include file="menus/menu.main.tpl"}

            <li class="right last search">
                <form action="{router page='search'}topics/" class="form">
                    <label>
                        <input placeholder="{$aLang.search|mb_strtolower}" type="text" maxlength="255" name="q"/>
                    </label>
                </form>
            </li>

            {hook run='header_top_end'}

        </ul>

        {hook run='main_menu'}

        <!-- СКРЫТОЕ МЕНЮ -->
        <ul class="menu-level-2 menu-level-2-hidden">

        </ul>

    </div>
</div>