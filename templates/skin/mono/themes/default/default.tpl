<!DOCTYPE html>
{block name="vars"}{/block}
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="ru"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="ru"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="ru"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="ru"> <!--<![endif]-->

<head>
{block name="html_head"}
{hook run='html_head_begin'}

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>{$sHtmlTitle}</title>

    <meta name="description" content="{$sHtmlDescription}">
    <meta name="keywords" content="{$sHtmlKeywords}">

    {foreach $aHtmlHeadTags as $sTag}
        {$sTag}
    {/foreach}

    {$aHtmlHeadFiles.css}

    <link href="{cfg name='path.static.skin'}/images/favicon.ico?v1" rel="shortcut icon"/>
    <link rel="search" type="application/opensearchdescription+xml" href="{router page='search'}opensearch/"
          title="{cfg name='view.name'}"/>

{if $aHtmlRssAlternate}
    <link rel="alternate" type="application/rss+xml" href="{$aHtmlRssAlternate.url}" title="{$aHtmlRssAlternate.title}">
{/if}

{if $sHtmlCanonical}
    <link rel="canonical" href="{$sHtmlCanonical}"/>
{/if}

{if $bRefreshToHome}
    <meta HTTP-EQUIV="Refresh" CONTENT="3; URL={cfg name='path.root.web'}">
{/if}


    <script type="text/javascript">
        var DIR_WEB_ROOT = '{cfg name="path.root.web"}';
        var DIR_STATIC_SKIN = '{cfg name="path.static.skin"}';
        var DIR_ROOT_ENGINE_LIB = '{cfg name="path.root.engine_lib"}';
        var LIVESTREET_SECURITY_KEY = '{$ALTO_SECURITY_KEY}';
        var SESSION_ID = '{$_sPhpSessionId}';
        var BLOG_USE_TINYMCE = '{cfg name="view.tinymce"}';

        var TINYMCE_LANG = 'en';
        {if Config::Get('lang.current') == 'russian' || Config::Get('lang.current') == 'ru'}
        TINYMCE_LANG = 'ru';
        {/if}

        var aRouter = new Array();
        {foreach from=$aRouter key=sPage item=sPath}
        aRouter['{$sPage}'] = '{$sPath}';
        {/foreach}
    </script>


{$aHtmlHeadFiles.js}


    <script type="text/javascript">
        var tinyMCE = false;
        ls.lang.load({json var = $aLangJs});
        ls.registry.set('comment_max_tree', {json var=Config::Get('module.comment.max_tree')});
        ls.registry.set('block_stream_show_tip', {json var=Config::Get('block.stream.show_tip')});
    </script>


{if {cfg name='view.grid.type'} == 'fluid'}
    <style>
        .i-container {
            min-width: {cfg name='view.grid.fluid_min_width'}px;
            max-width: {cfg name='view.grid.fluid_max_width'}px;
        }
    </style>
    {else}
    <style>
        .i-container {
            width: {cfg name='view.grid.fixed_width'}px;
        }
    </style>
{/if}


{hook run='html_head_end'}
{/block}
</head>



{if $oUserCurrent}
    {assign var=body_classes value=$body_classes|cat:' ls-user-role-user'}

    {if $oUserCurrent->isAdministrator()}
        {assign var=body_classes value=$body_classes|cat:' ls-user-role-admin'}
    {/if}
    {else}
    {assign var=body_classes value=$body_classes|cat:' ls-user-role-guest'}
{/if}

{if !$oUserCurrent or ($oUserCurrent and !$oUserCurrent->isAdministrator())}
    {assign var=body_classes value=$body_classes|cat:' ls-user-role-not-admin'}
{/if}

<body class="{$body_classes} width-{cfg name='view.grid.type'}">
{hook run='body_begin'}


{if $oUserCurrent}
    {include file='window_write.tpl'}
    {include file='window_favourite_form_tags.tpl'}
{else}
    {include file='window_login.tpl'}
{/if}


<div id="container" class="i-container {hook run='container_class'}">
    {include file='header_top.tpl'}
    {include file='nav.tpl'}

    {include file='system_message.tpl'}

    <div id="wrapper" class="{hook run='wrapper_class'}">
    {if !$noSidebar && $sidebarPosition == 'left'}
        {include file='sidebar.tpl'}
    {/if}

        <div id="content-wrapper" class="{if $sidebarPosition == 'left'}content-right{/if}">
            <div id="content" role="main"
                 class="{if $noSidebar}content-full-width{/if}"
                {if $sMenuItemSelect=='profile'}itemscope itemtype="http://data-vocabulary.org/Person"{/if}>

            {block name="content_menu"}
            {if $menu}
                {if in_array($menu,$aMenuContainers)}{$aMenuFetch.$menu}{else}{include file="menu.$menu.tpl"}{/if}
            {/if}
            {/block}

            {block name="content"}
            {hook run='content_begin'}
            {hook run='content_end'}
            {/block}

            </div>
            <!-- /content -->
        </div>
        <!-- /content-wrapper -->


    {if !$noSidebar && $sidebarPosition != 'left'}
        {include file='sidebar.tpl'}
    {/if}
    </div>
    <!-- /wrapper -->


    <footer id="footer" class="b-footer i-container">
        <div class="b-footer-copyright">
        {hook run='copyright'}
        </div>

        Skin based on templates by <a href="http://deniart.ru">deniart</a>

    {hook run='footer_end'}
    </footer>

</div>
<!-- /container -->

{include file='toolbar.tpl'}

{hook run='body_end'}

</body>
</html>