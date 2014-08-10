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
      <link href='http://fonts.googleapis.com/css?family=PT+Sans:400,700,400italic,700italic&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
   </head>
   <body class="{$body_classes}">
      {block name="layout_body"}
      {hook run='layout_body_begin'}
      <header class="navbar navbar-inverse navbar-fixed-top" role="navigation">
         <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
               <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse">
               <span class="sr-only">Toggle navigation</span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
               </button>
               <a class="navbar-brand" href="#"><span class="glyphicon glyphicon-heart-empty"></span> AltoCMS</a>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="navbar-collapse">
               <ul class="nav navbar-nav navbar-right">
                  <form method="get" action="{router page='search'}topics/" class="navbar-form navbar-left" role="search">
                      <div class="form-group">
                        <input type="text" name="q" class="form-control" placeholder="{$aLang.search_submit}..." value="{$aLang.search_submit}...">
                      </div>
                  </form>
                  <li><a href="{router page='index'}" target="_blank">{$aLang.action.admin.goto_site}</a></li>
                  <li>
                  <li class="active"><a href="#">Админ-панель</a></li>
                  <li><a href="http://altocms.ru/community/">Сообщество</a></li>
                  <li class="dropdown">
                     <a href="#" class="dropdown-toggle" data-toggle="dropdown">Дополнения <span class="caret"></span></a>
                     <ul class="dropdown-menu" role="menu">
                        <li><a href="http://altocms.ru/addons/">Все</a></li>
                        <li><a href="http://altocms.ru/addons/list/extensions">Плагины</a></li>
                        <li><a href="http://altocms.ru/addons/list/templates">Шаблоны</a></li>
                     </ul>
                  </li>
                  <li class="dropdown">
                     <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                     <div class="pull-left"><img src="{$oUserCurrent->getAvatarUrl(24)}" alt="avatar" class="avatar"/></div>
                     {$oUserCurrent->getDisplayName()}
                     <span class="caret"></span>
                     </a>
                     <ul class="dropdown-menu" role="menu">
                        <li><a href="{$oUserCurrent->getUserUrl()}">{$aLang.user_menu_profile}
                           </a>
                        </li>
                        <li><a href="{router page='settings'}profile/">{$aLang.settings_menu}</a></li>
                        <li><a href="{router page='talk'}">
                           {$aLang.user_privat_messages}
                           {if $iUserCurrentCountTalkNew}
                           <span class="badge pull-right">{$iUserCurrentCountTalkNew}</span>
                           {/if}
                           </a>
                        </li>
                        <li><a href="{router page='login'}exit/?security_key={$ALTO_SECURITY_KEY}">{$aLang.exit}</a></li>
                     </ul>
                  </li>
               </ul>
            </div>
            <!-- /.navbar-collapse -->
         </div>
         <!-- /.container-fluid -->
      </header>
      <div class="container">
         {include file="modals/modal.empty.tpl"}
         {block name="sidebar"}{/block}
         <!-- Mobile Header -->
         <div class="content">
            <div class="breadcrumbs">
               <ul class="breadcrumb">
                  <li>
                     <a href="#">{$aLang.action.admin.title}</a>
                  </li>
                  <li>
                     <a href="#">{$sPageTitle}</a>
                  </li>
               </ul>
               <h1 class="page-title">{$sPageTitle}</h1>
            </div>
            {block name="content"}
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
            {hook run='layout_body_end'}
         </div>
      </div>
   </body>
</html>