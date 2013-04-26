{*For desktop*}
<div class="visible-desktop"><div id="navmain-fantom"></div></div>

<div id="navmain" class="navbar subnav visible-desktop">
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

            {if $oUserCurrent}
                <div class="writes btn-group pull-right">
                    <a href="{router page='content'}/topic/add/" class="btn btn-primary pull-left write">
                        <i class="icon-plus-sign icon-white"></i>
                        {$aLang.block_create}
                    </a>
                    <a class="btn btn-primary pull-left write dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu pull-right">
                        {foreach from=$aContentTypes item=oType}
                            <li><a href="{router page='content'}{$oType->getContentUrl()}/add/">{$oType->getContentTitle()|escape:'html'}</a></li>
                        {/foreach}
                        <li><a href="{router page='talk'}add/">{$aLang.block_create_talk}</a></li>
                        <li><a href="{router page='blog'}add" class="write-item-link">{$aLang.block_create_blog}</a></li>
                        <li class="divider"></li>
                        <li><a href="{router page='content'}saved/" class="write-item-link">{$aLang.topic_menu_saved} {if $iUserCurrentCountTopicDraft}({$iUserCurrentCountTopicDraft}){/if}</a></li>
                    </ul>
                </div>
            {/if}

                {hook run='main_menu'}
            </div><!-- /.nav-collapse -->
        </div>
    </div><!-- /navbar-inner -->
</div>

{*For tablet & phone*}
<div class="navbar subnav hidden-desktop">
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