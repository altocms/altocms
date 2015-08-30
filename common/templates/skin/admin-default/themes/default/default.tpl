<!DOCTYPE html>
{block name="layout_vars"}{/block}
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="ru"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="ru"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="ru"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="ru"> <!--<![endif]-->

<head>
{block name="layout_head"}
{hook run='layout_head_begin'}

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <title>{$sHtmlTitle}</title>

    <meta name="description" content="{$sHtmlDescription}">
    <meta name="keywords" content="{$sHtmlKeywords}">

    <meta name="viewport" content="width=600"/>

    {$aHtmlHeadFiles.css}

    <link href="{Config::Get('path.static.skin')}assets/img/favicon.ico?v1" rel="shortcut icon">
    <link rel="search" type="application/opensearchdescription+xml" href="{router page='search'}opensearch/"
          title="{Config::Get('view.name')}"/>

{if $aHtmlRssAlternate}
    <link rel="alternate" type="application/rss+xml" href="{$aHtmlRssAlternate.url}" title="{$aHtmlRssAlternate.title}">
{/if}

{if $sHtmlCanonical}
    <link rel="canonical" href="{$sHtmlCanonical}"/>
{/if}

{if $bRefreshToHome}
    <meta HTTP-EQUIV="Refresh" CONTENT="3; URL={Config::Get('path.root.url')}">
{/if}


    <script type="text/javascript">
        var DIR_WEB_ROOT = '{Config::Get("path.root.web")}';
        var DIR_STATIC_SKIN = '{Config::Get("path.static.skin")}';
        var DIR_ROOT_ENGINE_LIB = '{Config::Get("path.root.engine_lib")}';
        var ALTO_SECURITY_KEY = '{$ALTO_SECURITY_KEY}';
        var SESSION_ID = '{$_sPhpSessionId}';
        var WYSIWYG = {if Config::Get("view.wysiwyg")}true{else}false{/if};

        var l10n = {
            'date_format': '{Config::Get("l10n.date_format")}',
            'week_start': {cfg name="l10n.week_start" default=0}
        };

        var tinyMCE = tinymce = false;
        var TINYMCE_LANG = {if Config::Get('lang.current') == 'ru'}'ru'{else}'en'{/if};

        var aRouter = [];
        {strip}{foreach from=$aRouter key=sPage item=sPath} aRouter['{$sPage}'] = '{$sPath}'; {/foreach}{/strip}
    </script>

	<style>
	@font-face {
		font-family:'Icons Halflings';
		src:url('{asset file="assets/css/fonts/icons-halflings-regular.eot"}');
		src:url('{asset file="assets/css/fonts/icons-halflings-regular.eot?#iefix"}') format('embedded-opentype'),
		url('{asset file="assets/css/fonts/icons-halflings-regular.woff"}') format('woff'),
		url('{asset file="assets/css/fonts/icons-halflings-regular.ttf"}') format('truetype'),
		url('{asset file="assets/css/fonts/icons-halflings-regular.svg#icons-halflingsregular"}') format('svg');
	}
	@font-face {
	font-family: 'Simple-Line-Icons';
	src:url('{asset file="assets/css/simpleline/fonts/Simple-Line-Icons.eot"}');
	src:url('{asset file="assets/css/simpleline/fonts/Simple-Line-Icons.eot?#iefix"}') format('embedded-opentype'),
		url('{asset file="assets/css/simpleline/fonts/Simple-Line-Icons.woff"}') format('woff'),
		url('{asset file="assets/css/simpleline/fonts/Simple-Line-Icons.ttf"}') format('truetype'),
		url('{asset file="assets/css/simpleline/fonts/Simple-Line-Icons.svg#Simple-Line-Icons"}') format('svg');
	font-weight: normal;
	font-style: normal;
}
	</style>

	<link href='//fonts.googleapis.com/css?family=Cuprum:400,400italic,700,700italic&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
	<link href='//fonts.googleapis.com/css?family=Ubuntu:400,700,400italic,700italic&subset=latin,cyrillic' rel='stylesheet' type='text/css'>

{$aHtmlHeadFiles.js}
    <script>
        (function ($) {
            $(function () {
                $('input, select').styler();
            });
        })(jQuery);
    </script>
    <script type="text/javascript">
        ls.lang.load({json var = $aLangJs});
        //ls.registry.set('comment_max_tree', '{Config::Get("module.comment.max_tree")}');
    </script>

{hook run='layout_head_end'}
{/block}
</head>

<body class="{$body_classes}">
{block name="layout_body"}
{hook run='layout_body_begin'}

    {include file="modals/modal.empty.tpl"}

<!-- NAVBAR -->
<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-header">
        <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>

    <nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">

    <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                <img src="{$oUserCurrent->getAvatarUrl(24)}" alt="avatar" class="avatar"/>
                {$oUserCurrent->getDisplayName()}
            </a>

            <ul class="dropdown-menu">
                <li><a href="{$oUserCurrent->getUserUrl()}"><i class="icon icon-user"></i> {$aLang.user_menu_profile}
                    </a></li>
                <li><a href="/settings/profile/"><i class="icon icon-settings"></i> {$aLang.settings_menu}</a></li>
                <li><a href="{router page='login'}exit/?security_key={$ALTO_SECURITY_KEY}"><i class="icon icon-lock"></i> {$aLang.exit}</a></li>
            </ul>
        </li>
    </ul>

    <ul class="nav navbar-nav navbar-right">
        <!-- li>
          <a href="#" data-toggle="dropdown">
            <i class="icon icon-send"></i> Онлайн <span class="badge badge-success">178</span>
          </a>
        </li -->

        <li>
            <a href="{router page='talk'}">
                <i class="icon icon-envelope"></i>
                {$aLang.user_privat_messages}
                {if $iUserCurrentCountTalkNew}
                    <span class="badge badge-important">{$iUserCurrentCountTalkNew}</span>
                {/if}
            </a>
        </li>

        <li>
            <a href="{router page='feed'}track/">
                <i class="icon icon-bell"></i> {$aLang.subscribe_menu} {if $iUserCurrentCountTrack}<span
                        class="badge badge-important">{$iUserCurrentCountTrack}</span>{/if}
            </a>
        </li>

    </ul>

    <ul class="nav navbar-nav navbar-right goto-site">
        <li>
            <a href="/" target="_blank"><i class="icon icon-pointer"></i> {$aLang.action.admin.goto_site}</a></li>
        <li>
    </ul>
</nav>
</div>

<div class="container">
<!-- SIDEBAR -->
<div id="sidebar" class="b-sidebar">
{block name="sidebar"}{/block}
    <div class="b-sidebar-top">
        <!--Action: [{$sAction}], Event: [{$sEvent}]-->
        <span id="window-width"></span>
    </div>
</div>

<!-- CONTENT -->
<div id="content" class="b-content">

{block name="content"}
    <div id="sticknote" class="b-sticknote">wait...</div>
    <div id="content-header" class="b-content-header">
        <h1 class="b-content-header-title">{$sPageTitle}</h1>
    </div>
    <div id="breadcrumb" class="b-content-breadcrumb">
        <a href="#" ><i class="icon icon-magic-wand"></i> {$aLang.action.admin.title}</a>
        <a href="#" class="current">{$sPageTitle}</a>
        {if isset($sPageSubMenu)}
            <a href="#" class="current">{$sPageSubMenu}</a>
        {/if}
    </div>

    {block name="sysmessage"}{/block}

    {block name="content-bar"}
    {/block}

    {block name="content-body"}
    {/block}
{/block}

</div>

</div>

{hook run='layout_body_end'}
{/block}
</body>
</html>