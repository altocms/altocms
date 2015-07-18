{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<div class="toolbar-button toolbar-search toolbar-menu-popover">
    <div id="hidden-toolbar-search-content" style="display: none;">
        <form action="{router page='search'}topics/" class="form toolbar-menu">
            <div class="form-group">
                <div class="input-group">
                    <input class="form-control" placeholder="{$aLang.search|mb_strtolower}..." type="text" maxlength="255" name="q"/>
                </div>
            </div>
        </form>
    </div>
    <a href="#"
       onclick="return false;"
       data-toggle="popover"
       class="toolbar-exit-button link link-light-gray"><span class="fa fa-search"></span></a>
</div>
