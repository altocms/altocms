<!doctype html>
<html>

<head>
    {hook run='html_head_begin'}

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>{$sHtmlTitle}</title>

    <meta name="description" content="{$sHtmlDescription}">
    <meta name="keywords" content="{$sHtmlKeywords}">

    {$aHtmlHeadFiles.css}

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{cfg name='path.static.skin'}/lib/bootstrap/img/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{cfg name='path.static.skin'}/lib/bootstrap/img/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{cfg name='path.static.skin'}/lib/bootstrap/img/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="{cfg name='path.static.skin'}/lib/bootstrap/img/apple-touch-icon-57-precomposed.png">
    <link href="{cfg name='path.static.skin'}/images/favicon.png?v1" rel="shortcut icon" />
    <link rel="search" type="application/opensearchdescription+xml" href="{router page='search'}opensearch/" title="{cfg name='view.name'}" />

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
        var LIVESTREET_SECURITY_KEY = '{$LIVESTREET_SECURITY_KEY}';
        var SESSION_ID = '{$_sPhpSessionId}';
        var BLOG_USE_TINYMCE = '{cfg name="view.tinymce"}';

        var TINYMCE_LANG = 'en';
        {if $oConfig->GetValue('lang.current') == 'russian'}
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
        ls.registry.set('comment_max_tree', {json var=$oConfig->Get('module.comment.max_tree')});
        ls.registry.set('block_stream_show_tip', {json var=$oConfig->Get('block.stream.show_tip')});
    </script>

    {if {cfg name='view.top_panel.type'} == 'fixed' && {cfg name='view.main_menu.type'} == 'fixed'}
        <script type="text/javascript">
            $(window).ready(function(){
                comment_scroll_top_all();
            });
        </script>
    {/if}

    {if {cfg name='view.top_panel.type'} == 'fixed' && {cfg name='view.main_menu.type'} != 'fixed'}
        <script type="text/javascript">
            $(window).ready(function(){
                comment_scroll_top_one();
            });
        </script>
    {/if}

    {if {cfg name='view.top_panel.type'} != 'fixed' && {cfg name='view.main_menu.type'} == 'fixed'}
        <script type="text/javascript">
            $(window).ready(function(){
                comment_scroll_top_one();
            });
        </script>
    {/if}

    {if {cfg name='view.top_panel.type'} == 'fixed'}
        <script type="text/javascript" src="{cfg name="path.static.skin"}/themes/default/js/application.js"></script>
    {/if}

    {if {cfg name='view.main_menu.type'} != 'fixed'}
        <style type="text/css">
            #navtop {
                position: absolute;
            }
            #navmain {
                top: 0;
            }
        </style>
    {/if}


    {hook run='html_head_end'}
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

{add_block group='toolbar' name='toolbar_admin.tpl' priority=100}
{add_block group='toolbar' name='toolbar_scrollup.tpl' priority=-100}




<body class="{$body_classes} width-{cfg name='view.grid.type'}">
    {hook run='body_begin'}


    {if $oUserCurrent}
        {include file='window_write.tpl'}
        {include file='window_favourite_form_tags.tpl'}
    {else}
        {include file='window_login.tpl'}
    {/if}

    {include file='header_top_top.tpl'}

    <div id="container" class="{hook run='container_class'} container">
        {include file='header_top.tpl'}
        {include file='nav.tpl'}

        <div id="wrapper" class="{hook run='wrapper_class'}">
            <div class="row-fluid">
                {if !$noSidebar && $sidebarPosition == 'left'}
                    {include file='sidebar.tpl'}
                {/if}

                <div id="content" role="main"
                    class="{if $noSidebar}content-full-width{/if}
                           {if $sidebarPosition == 'left'}content-right{/if}
                           {if $noSidebarRespon} respon-content{/if}
                           {if $sAction=='profile' || $sAction=='settings' || $sAction=='talk'}span9{else}span8{/if}
                           "
                    {if $sMenuItemSelect=='profile'}itemscope itemtype="http://data-vocabulary.org/Person"{/if}>
                    {include file='nav_content.tpl'}
                    {include file='system_message.tpl'}

                    {hook run='content_begin'}