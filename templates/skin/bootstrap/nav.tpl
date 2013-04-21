{*For desktop*}
<div class="visible-desktop"><div id="navmain-fantom"></div></div>

<div id="navmain" class="navbar navbar-inverse subnav visible-desktop">
    <div class="navbar-inner">
        <div class="container">
            <div>
                <ul class="nav">
                    {*<li {if $sMenuHeadItemSelect=='blog'}class="active"{/if}><a href="{cfg name='path.root.web'}">{$aLang.blog_menu_all}</a></li>*}
                    {if count($aContentTypes)>1}
						{foreach from=$aContentTypes item=oType}
							<li {if $sMenuHeadItemSelect=='filter' && $sEvent==$oType->getContentUrl()}class="active"{/if}><a href="{router page='filter'}{$oType->getContentUrl()}/">{$oType->getContentTitleDecl()|escape:'html'}</a> <i></i></li>
						{/foreach}
					{/if}
					<li {if $sMenuHeadItemSelect=='blogs'}class="active"{/if}><a href="{router page='blogs'}">{$aLang.blogs}</a></li>
                    <li {if $sMenuHeadItemSelect=='people'}class="active"{/if}><a href="{router page='people'}">{$aLang.people}</a></li>
                    <li {if $sMenuHeadItemSelect=='stream'}class="active"{/if}><a href="{router page='stream'}">{$aLang.stream_menu}</a></li>

                    {hook run='main_menu_item'}
                </ul>


                <form class="search-menu navbar-form pull-right" action="{router page='search'}topics/">
                    <div class="input-append">
                        <input type="text" class="span2" placeholder="{$aLang.search}" name="q">
                        <input type="submit" value="{$aLang.search_submit}" class="btn btn-primary span">
                    </div>
                </form>

                {hook run='main_menu'}
            </div><!-- /.nav-collapse -->
        </div>
    </div><!-- /navbar-inner -->
</div>

{*For tablet & phone*}
<div class="navbar navbar-inverse subnav hidden-desktop">
    <div class="navbar-inner">
        <div class="container">
            <a class="btn btn-navbar" data-toggle="collapse" data-target="#navmain-list">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a class="brand" data-toggle="collapse" data-target="#navmain-list">Главное меню</a>
            <div id="navmain-list" class="nav-collapse collapse">
                <ul class="nav">
                    {*<li {if $sMenuHeadItemSelect=='blog'}class="active"{/if}><a href="{cfg name='path.root.web'}">{$aLang.topic_title}</a></li>*}
                    <li {if $sMenuHeadItemSelect=='blogs'}class="active"{/if}><a href="{router page='blogs'}">{$aLang.blogs}</a></li>
                    <li {if $sMenuHeadItemSelect=='people'}class="active"{/if}><a href="{router page='people'}">{$aLang.people}</a></li>
                    <li {if $sMenuHeadItemSelect=='stream'}class="active"{/if}><a href="{router page='stream'}">{$aLang.stream_menu}</a></li>

                {hook run='main_menu_item'}
                </ul>

            {hook run='main_menu'}
            </div><!-- /.nav-collapse -->
        </div>
    </div><!-- /navbar-inner -->
</div>

<form class="search-block hidden-desktop" action="{router page='search'}topics/">
    <div class="input-append">
        <input type="text" placeholder="{$aLang.search}">
        <input type="submit" value="{$aLang.search_submit}" class="btn btn-primary">
    </div>
</form>