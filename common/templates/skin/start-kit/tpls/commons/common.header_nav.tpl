<nav id="nav" class="navbar clearfix">
    <div class="container">
        <div class="row">

            <div class="col-sm-8 col-lg-8">
                {if $menu}
                    {if in_array($menu,$aMenuContainers)}{$aMenuFetch.$menu}{else}{include file="menus/menu.$menu.tpl"}{/if}
                {/if}
            </div>

            <div class="col-sm-4 col-lg-4 hidden-xs">
                {if E::IsUser()}
                    <button class="btn btn-success btn-write pull-right" data-toggle="modal" data-target="#modal-write">
                        {$aLang.block_create}
                    </button>
                {/if}

                <form action="{R::GetLink("search")}topics/" class="navbar-search pull-right visible-lg">
                    <input type="text" placeholder="{$aLang.search}" maxlength="255" name="q" class="form-control">
                </form>
            </div>

        </div>
    </div>
</nav>
