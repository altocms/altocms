<!DOCTYPE html>
{block name="layout_vars"}{/block}
<!--[if lt IE 7]> 
<html class="no-js ie6 oldie" lang="ru">
  <![endif]-->
  <!--[if IE 7]>    
  <html class="no-js ie7 oldie" lang="ru">
    <![endif]-->
    <!--[if IE 8]>    
    <html class="no-js ie8 oldie" lang="ru">
      <![endif]-->
      <!--[if gt IE 8]><!-->
<html class="no-js" lang="ru">
      <!--<![endif]-->

<head>
  {block name="layout_head"}
  {hook run='layout_head_begin'}
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
  <title>{$sHtmlTitle}</title>
  <meta name="description" content="{$sHtmlDescription}">
  <meta name="keywords" content="{$sHtmlKeywords}">
  <link href="{Config::Get('path.static.skin')}assets/img/favicon.png?v1.3" rel="shortcut icon">
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
  {$aHtmlHeadFiles.css}
  {$aHtmlHeadFiles.js}
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
    
    var tinymce = false;
    var TINYMCE_LANG = {if Config::Get('lang.current') == 'ru'}'ru'{else}'en'{/if};
    
    var aRouter = new Array();
    {foreach from=$aRouter key=sPage item=sPath}
    aRouter['{$sPage}'] = '{$sPath}';
    {/foreach}
  </script>

  <script type="text/javascript">
    ls.lang.load({json var = $aLangJs});
    //ls.registry.set('comment_max_tree', '{Config::Get("module.comment.max_tree")}');
  </script>
  {hook run='layout_head_end'}
  {/block}

  <link rel='stylesheet' type='text/css' href='{cfg name='path.static.skin'}/assets/css/fonts/ionicons/css/ionicons.min.css' />
  <link href='http://fonts.googleapis.com/css?family=Exo+2:500,500italic,600,600italic,700,700italic&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
</head>

<body class="{$body_classes}">

{block name="layout_body"}
{hook run='layout_body_begin'}

  <header class="navbar navbar-static-top" role="banner">
  <div class="container">
    <div class="navbar-header">
      <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a href="../" class="navbar-brand"></a>
    </div>
    <nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
      <form class="navbar-form navbar-left">
               <input type="text" class="form-control col-lg-8" placeholder="Найти" value="Найти">
            </form>
      <ul class="nav navbar-nav">
        <li>
          <a href="http://altocms.ru/community/"><i class="ion ion-android-social"></i> Сообщество</a>
        </li>
        <li>
          <a href="http://altocms.ru/addons/"><i class="ion ion-android-developer"></i> Модули</a>
        </li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li>
            <a href="{router page='talk'}">
                <i class="ion ion-android-mail"></i>
                {$aLang.user_privat_messages}
                {if $iUserCurrentCountTalkNew}
                    <span class="badge badge-important">{$iUserCurrentCountTalkNew}</span>
                {/if}
            </a>
        </li>
        <li>
            <a href="/" target="_blank"><i class="ion ion-android-location"></i> {$aLang.action.admin.goto_site}</a></li>
        <li>
    </ul>
    </nav>
  </div>
  <div class="line"></div>
</header>
<div id="wrapper">
   {include file="modals/modal.empty.tpl"}
   {block name="sidebar"}{/block}
   <!-- Mobile Header -->
   <div id="content">
    <div class="breadcrumbs">
        <ul class="breadcrumb">
            <li>
                <a href="#">{$aLang.action.admin.title}</a>
            </li>
            <li>
                <a href="#">{$sPageTitle}</a>
            </li>
        </ul>
        <h1 class="page-title"><i class="ion ion-ios7-redo"></i> {$sPageTitle}</h1>
    </div>
      {block name="content"}
      <section class="content-header">
      </section>
      <div class="row">
         {include file="sysmessage.tpl"}
         {block name="sysmessage"}{/block}
         {block name="content-bar"}
         {/block}
         {block name="content-body"}
         {/block}
      </div>
      {/block}
      {/block}
   </div>
</div>

<div id="footer">{hook run='layout_body_end'}</div>

</body>
</html>