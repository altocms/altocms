<header id="header" role="banner">

    {hook run='header_top_begin'}

    <nav class="navbar navbar-inverse navbar-{Config::Get('view.header.top')}-top">
        <div class="container">

            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <hgroup class="site-info">
                    {strip}
                        <h1 class="site-name"><a class="navbar-brand" href="{Config::Get('path.root.url')}">
                        {if Config::Get('view.header.logo')}
                                    <img src="{asset file=Config::Get('view.header.logo')}"
                                         alt="{Config::Get('view.name')}" class="navbar-brand-logo">
                        {/if}
                        {if Config::Get('view.header.name')}
                            {Config::Get('view.header.name')}
                        {/if}
                        </a></h1>
                    {/strip}
                </hgroup>
            </div>

            {hook run='userbar_nav'}

            <div class="collapse navbar-collapse navbar-ex1-collapse">
                {include file="menus/menu.main.tpl"}
                    {if E::IsUser()}
                    {menu id='user' class='nav navbar-nav navbar-right'}
                    {else}
                    {menu id='login' class='nav navbar-nav navbar-right'}
                    {/if}
            </div>

        </div>
    </nav>

    {hook run='header_top_end'}

</header>
