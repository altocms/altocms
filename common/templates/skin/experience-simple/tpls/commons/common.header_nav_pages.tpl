 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

 <script>
     $(function(){
         $('.dropdown-user-menu a').not('.user_activity_items a').addClass('hvr-leftline-reveal');
     })
 </script>

 <nav class="navbar navbar-default navbar-main {if C::Get('view.fix_menu')}fixed{/if}" role="navigation">
    <div class="container">

        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-main">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <h1>
            <a class="logo navbar-brand" href="{Config::Get('path.root.url')}">
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
            </h1>
        </div>



        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="navbar-main">
            <ul class="main nav navbar-nav nav-content">
                {hook run='header_top_begin'}
                <li class="search visible-xs hidden-sm hidden-md hidden-lg">
                    <form action="{router page='search'}topics/" class="form">
                        <input placeholder="{$aLang.search|mb_strtolower}" class="form-control" type="text" maxlength="255" name="q"/>
                    </form>
                </li>
                {menu id='main' class='nav navbar-nav' hideul=true}
                {hook run='header_top_end'}
                <li class="dropdown right menu-hidden-container hidden">
                    <a data-toggle="dropdown" href="#" class="menu-hidden-trigger">
                        {$aLang.more}<span class="caret"></span>
                    </a>
                    <!-- контейнер скрытых элементов -->
                    <ul class="menu-hidden dropdown-menu animated fadeIn dropdown-content-menu"></ul>
                </li>
            </ul>


            <ul class="nav navbar-nav navbar-right navbar-user">
                <li class="dropdown">

                    {hook run='menu_blog'}

                    {if E::IsUser()}
                        {menu id='user' class='nav navbar-nav navbar-right' hideul=true}
                    {else}
                        {menu id='login' class='nav navbar-nav navbar-right' hideul=true}
                    {/if}

                </li>
            </ul>

        </div>

        {hook run='main_menu'}

    </div>
</nav>