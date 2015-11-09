<!doctype html>

<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="ru"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="ru"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="ru"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="ru"> <!--<![endif]-->

<head>
	{hook run='html_head_begin'}
	
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<title>{$sHtmlTitle}</title>
	
	<meta name="description" content="{$sHtmlDescription}">
	<meta name="keywords" content="{$sHtmlKeywords}">
	
	{if $oTopic}
		<meta property="og:title" content="{$oTopic->getTitle()|escape:'html'}"/>
		<meta property="og:url" content="{$oTopic->getUrl()}"/>
		{if $oTopic->getPreviewImageWebPath()}
			<meta property="og:image" content="{$oTopic->getPreviewImageWebPath('700crop')}"/>
		{/if}
		<meta property="og:description" content="{$sHtmlDescription}"/>
		<meta property="og:site_name" content="{Config::Get('view.name')}"/>
		<meta property="og:type" content="article"/>
		<meta name="twitter:card" content="summary">
	{/if}

	{$aHtmlHeadFiles.css}
	
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800&subset=latin,cyrillic' rel='stylesheet' type='text/css'>

	<link href="{Config::Get('path.static.skin')}/images/favicon.ico?v1" rel="shortcut icon" />
	<link rel="search" type="application/opensearchdescription+xml" href="{router page='search'}opensearch/" title="{Config::Get('view.name')}" />


	{if $aHtmlRssAlternate}
		<link rel="alternate" type="application/rss+xml" href="{$aHtmlRssAlternate.url}" title="{$aHtmlRssAlternate.title}">
	{/if}

	{if $sHtmlCanonical}
		<link rel="canonical" href="{$sHtmlCanonical}" />
	{/if}
	
	{if $bRefreshToHome}
		<meta  HTTP-EQUIV="Refresh" CONTENT="3; URL={Config::Get('path.root.web')}/">
	{/if}
	
	
	<script type="text/javascript">
		var DIR_WEB_ROOT 			= '{Config::Get('path.root.web')}';
		var DIR_STATIC_SKIN 		= '{Config::Get('path.static.skin')}';
		var DIR_ROOT_ENGINE_LIB 	= '{Config::Get('path.root.engine_lib')}';
		var LIVESTREET_SECURITY_KEY = '{$ALTO_SECURITY_KEY}';
		var SESSION_ID				= '{$_sPhpSessionId}';
		var BLOG_USE_TINYMCE		= '{Config::Get('view.tinymce')}';
		
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
		ls.registry.set('comment_max_tree',{json var=$oConfig->Get('module.comment.max_tree')});
		ls.registry.set('block_stream_show_tip',{json var=$oConfig->Get('block.stream.show_tip')});
	</script>
	
	
	{hook run='html_head_end'}
	
	<!--[if lt IE 9]>
		<script src="{Config::Get('path.static.skin')}/js/html5shiv.js"></script>
		<script src="{Config::Get('path.static.skin')}/js/respond.min.js"></script>
	<![endif]-->
	
	<!--[if IE 7]>
		<link rel="stylesheet" href="{Config::Get('path.static.skin')}/themes/default/icons/css/fontello-ie7.css">
	<![endif]-->
	<script>
		function toggleCodes(on) {
			var obj = document.getElementById('icons');
			if (on) {
				obj.className += ' codesOn';
			} else {
				obj.className = obj.className.replace(' codesOn', '');
			}
		}
	</script>
</head>



{if E::IsUser()}
	{assign var=body_classes value=$body_classes|cat:' ls-user-role-user'}
	
	{if $oUserCurrent->isAdministrator()}
		{assign var=body_classes value=$body_classes|cat:' ls-user-role-admin'}
	{/if}
{else}
	{assign var=body_classes value=$body_classes|cat:' ls-user-role-guest'}
{/if}

{if !$oUserCurrent OR ($oUserCurrent AND !$oUserCurrent->isAdministrator())}
	{assign var=body_classes value=$body_classes|cat:' ls-user-role-not-admin'}
{/if}

{add_block group='toolbar' name='toolbar_admin.tpl' priority=100}
{add_block group='toolbar' name='toolbar_scrollup.tpl' priority=-100}


<body class="{$body_classes}">
	{hook run='body_begin'}
		
	{if E::IsUser()}
		{include file='window_write.tpl'}
		{include file='window_favourite_form_tags.tpl'}
	{else}
		{include file='window_login.tpl'}
	{/if}
	
	
	{include file='header_top.tpl'}
	{include file='nav.tpl'}
	
	<section id="container" class="{hook run='container_class'}">
		<div id="wrapper" class="container {hook run='wrapper_class'}">
			<div class="row">
			
				{if !$noSidebar AND $sidebarPosition == 'left'}
					{include file='sidebar.tpl'}
				{/if} 
	
				<div id="content" role="main" 
					class="{if $noSidebar}col-md-12 col-lg-12{else}col-md-8 col-lg-8{/if} content{if $sidebarPosition == 'left'} content-right{/if}"
					{if $sMenuItemSelect=='profile'}itemscope itemtype="http://data-vocabulary.org/Person"{/if}>
					
					<div class="content-inner action-{$sAction}{if $sEvent} event-{$sEvent}{/if}{if $aParams[0]} params-{$aParams[0]}{/if}">
						{include file='nav_content.tpl'}
						{include file='system_message.tpl'}
						
						{hook run='content_begin'}
						