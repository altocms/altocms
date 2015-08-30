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

	{$aHtmlHeadFiles.css}
	
	{if {Config::Get('view.theme')} == 'light'}
		<link href='//fonts.googleapis.com/css?family=PT+Sans+Narrow:400,700&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
	{/if}
	
	<link href="{Config::Get('path.static.skin')}/images/favicon.ico?v1" rel="shortcut icon" />
	<link rel="search" type="application/opensearchdescription+xml" href="{router page='search'}opensearch/" title="{Config::Get('view.name')}" />

	
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

		var tinyMCE = tinymce = false;
		var TINYMCE_LANG = {if Config::Get('lang.current') == 'ru'}'ru'{else}'en'{/if};

		var aRouter = [];
		{strip}{foreach from=$aRouter key=sPage item=sPath} aRouter['{$sPage}'] = '{$sPath}'; {/foreach}{/strip}
	</script>
	
	
	{$aHtmlHeadFiles.js}

	
	<script type="text/javascript">
		ls.lang.load({json var = $aLangJs});
		ls.registry.set('comment_max_tree',{json var=$oConfig->Get('module.comment.max_tree')});
		ls.registry.set('block_stream_show_tip',{json var=$oConfig->Get('block.stream.show_tip')});
	</script>
	
	
	{hook run='html_head_end'}
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


<body class="{$body_classes} light">
	{hook run='body_begin'}
	
	<div class="container">
	
		<hgroup class="site-info">
			<h1 class="text-center site-name"><a href="{Config::Get('path.root.web')}">{Config::Get('view.name')}</a></h1>
			<h5 class="text-center site-description">{Config::Get('view.description')}</h5>
		</hgroup>

		<div class="light-form">
