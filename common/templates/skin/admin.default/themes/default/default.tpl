<!doctype html>

<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="ru"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="ru"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="ru"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="ru"> <!--<![endif]-->

<head>
{hook run='html_head_begin'}

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <title>{$sHtmlTitle}</title>

    <meta name="description" content="{$sHtmlDescription}">
    <meta name="keywords" content="{$sHtmlKeywords}">

    <meta name="viewport" content="width=device-width,initial-scale=1">

{$aHtmlHeadFiles.css}

    <link href='http://fonts.googleapis.com/css?family=PT+Sans:400,700&subset=latin,cyrillic' rel='stylesheet'
          type='text/css'>

    <link href="{cfg name='path.static.skin'}assets/img/favicon.ico?v0.9" rel="shortcut icon"/>
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
        var DIR_WEB_ROOT = '{Config::Get("path.root.web")}';
        var DIR_STATIC_SKIN = '{Config::Get("path.static.skin")}';
        var DIR_ROOT_ENGINE_LIB = '{Config::Get("path.root.engine_lib")}';
        var ALTO_SECURITY_KEY = '{$ALTO_SECURITY_KEY}';
        var LIVESTREET_SECURITY_KEY = '{$ALTO_SECURITY_KEY}';
        var SESSION_ID = '{$_sPhpSessionId}';
        var WYSIWYG = {if Config::Get("view.wysiwyg")}true{else}false{/if};

        var l10n = {
            'date_format': '{Config::Get("l10n.date_format")}',
            'week_start': {cfg name="l10n.week_start" default=0}
        };

        var TINYMCE_LANG = 'en';
        {if Config::Get('lang.current') == 'russian'}
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
        //ls.registry.set('comment_max_tree', '{Config::Get("module.comment.max_tree")}');
    </script>

{hook run='html_head_end'}
</head>

<body class="{$body_classes}">
{hook run='body_begin'}

<header id="header" class="b-view-header">
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container">
                <div class="nav-collapse nav logo">
                    <a href="{router page=admin}">
                        <img src="{Config::Get("path.static.skin")}assets/img/admlogo.png" alt="{$sAdminTitle}"/>
                    </a>
                </div>
                <a class="brand" href="{router page=admin}">
                {$sAdminTitle}
                </a>

                <div class="nav-collapse">
                    <ul class="nav">
                        <li class="divider-vertical"></li>
                        <li><a href="{cfg name='path.root.web'}" target="_blank">{$aLang.action.admin.goto_site}</a></li>
                    {hook run='main_menu'}
                    </ul>
                </div>

                <ul class="nav nav-collapse pull-right">
                    <li>
                        <a href="{$oUserCurrent->getUserUrl()}" class="username">
                            <img src="{$oUserCurrent->getAvatarUrl(24)}" alt="avatar" class="avatar"/>
                        {$oUserCurrent->getLogin()}
                        </a>
                    </li>
                    <li class="divider-vertical"></li>
                    <li>
                        <a href="{router page='talk'}">
                            <i class="icon-envelope"></i>
                        {$aLang.user_privat_messages}
                        {if $iUserCurrentCountTalkNew}
                            <span class="badge badge-important badge-up">{$iUserCurrentCountTalkNew}</span>
                        {/if}
                        </a>
                    </li>
                    <li class="divider-vertical"></li>
                    <li>
                        <a href="{router page='login'}exit/?security_ls_key={$ALTO_SECURITY_KEY}">
                            <i class="icon-off"></i>
                        {$aLang.exit}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div id="sidebar" class="b-sidebar">
    <div class="b-sidebar-top">
        <!--Action: [{$sAction}], Event: [{$sEvent}]--><br/>
        <span id="window-width"></span>
    </div>
{block name="sidebar"}{/block}
</div>

<div id="content" class="b-content">{block name="content"}

    <div id="content-header" class="b-content-header">
        <h1 class="b-content-header-title">{$sPageTitle}</h1>
    </div>
    <div id="breadcrumb" class="b-content-breadcrumb">
        <a href="#" ><i class="icon-asterisk"></i> {$aLang.action.admin.title}</a>
        <a href="#" class="current">{$sPageTitle}</a>
    </div>

    <div class="container-fluid">
        {block name="sysmessage"}{/block}

        <div class="row-fluid">
            <div class="span12">
            {block name="content-bar"}
            {/block}
            </div>

            <div class="span12">
            {block name="content-body"}
            {/block}
            </div>
        </div>

    </div>
{/block}

</div>

{hook run='body_end'}

</body>
</html>