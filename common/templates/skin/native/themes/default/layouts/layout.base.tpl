<!doctype html>

{block name='layout_options'}{/block}

<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="ru"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="ru"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="ru"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="ru"> <!--<![endif]-->

<head>
	{hook run='html_head_begin'}
	{block name='layout_head_begin'}{/block}

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<meta name="description" content="{block name='layout_description'}{$sHtmlDescription}{/block}">
	<meta name="keywords" content="{block name='layout_keywords'}{$sHtmlKeywords}{/block}">

	<title>{block name='layout_title'}{$sHtmlTitle}{/block}</title>

	{**
	 * Стили
	 * CSS файлы подключаются в конфиге шаблона (ваш_шаблон/settings/config.php)
	 *}
	{$aHtmlHeadFiles.css}

	<link href="{cfg name='path.static.assets'}/images/favicons/favicon.svg?v1" rel="shortcut icon" />
	<link rel="search" type="application/opensearchdescription+xml" href="{router page='search'}opensearch/" title="{cfg name='view.name'}" />

	{**
	 * RSS
	 *}
	{if $aHtmlRssAlternate}
		<link rel="alternate" type="application/rss+xml" href="{$aHtmlRssAlternate.url}" title="{$aHtmlRssAlternate.title}">
	{/if}

	{if $sHtmlCanonical}
		<link rel="canonical" href="{$sHtmlCanonical}" />
	{/if}


	<script>
		var DIR_WEB_ROOT 			= '{cfg name="path.root.web"}',
			DIR_STATIC_SKIN 		= '{cfg name="path.static.skin"}',
			DIR_STATIC_FRAMEWORK 	= '{cfg name="path.static.framework"}',
			DIR_ENGINE_LIBS	 		= '{cfg name="path.root.engine_lib"}',
			LIVESTREET_SECURITY_KEY = '{$ALTO_SECURITY_KEY}',
			SESSION_ID				= '{$_sPhpSessionId}',
			SESSION_NAME			= '{$_sPhpSessionName}',
			LANGUAGE				= '{$oConfig->GetValue('lang.current')}',
			WYSIWYG					= {if $oConfig->GetValue('view.wysiwyg')}true{else}false{/if};

		var aRouter = [];
		{foreach $aRouter as $sPage => $sPath}
			aRouter['{$sPage}'] = '{$sPath}';
		{/foreach}
	</script>

	{**
	 * JavaScript файлы
	 * JS файлы подключаются в конфиге шаблона (ваш_шаблон/settings/config.php)
	 *}
	{$aHtmlHeadFiles.js}

	<script>
		ls.lang.load({json var = $aLangJs});
		ls.lang.load({lang_load name="blog, talk_favourite_add, talk_favourite_del, topic_question_create_answers_error_max"});

		ls.registry.set('comment_max_tree', {json var=$oConfig->Get('module.comment.max_tree')});
		ls.registry.set('block_stream_show_tip', {json var=$oConfig->Get('block.stream.show_tip')});
	</script>

	{**
	 * Тип сетки сайта
	 *}
	{if {cfg name='view.grid.type'} == 'fluid'}
		<style>
			#container {
				min-width: {cfg name='view.grid.fluid_min_width'}px;
				max-width: {cfg name='view.grid.fluid_max_width'}px;
			}
		</style>
	{else}
		<style>
			#container { width: {cfg name='view.grid.fixed_width'}px; } {* *}
		</style>
	{/if}

	{block name='layout_head_end'}{/block}
	{hook run='html_head_end'}
</head>


{**
 * Вспомогательные классы
 *
 * ls-user-role-guest        Посетитель - гость
 * ls-user-role-user         Залогиненый пользователь - обычный пользователь
 * ls-user-role-admin        Залогиненый пользователь - админ
 * ls-user-role-not-admin    Залогиненый пользователь - не админ
 * ls-template-*             Класс с названием активного шаблона
 *}
{if E::IsUser()}
	{$sBodyClasses = $sBodyClasses|cat:' ls-user-role-user'}

	{if E::IsAdmin()}
		{$sBodyClasses = $sBodyClasses|cat:' ls-user-role-admin'}
	{/if}
{else}
	{$sBodyClasses = $sBodyClasses|cat:' ls-user-role-guest'}
{/if}

{if !E::IsAdmin()}
	{$sBodyClasses = $sBodyClasses|cat:' ls-user-role-not-admin'}
{/if}

{$sBodyClasses = $sBodyClasses|cat:' ls-template-'|cat:{cfg name="view.skin"}}


<body class="{$sBodyClasses} layout-{cfg name='view.grid.type'} {block name='layout_body_classes'}{/block}">
	{hook run='body_begin'}

	{block name='layout_body'}
		<div id="container" class="{hook run='container_class'} {if $bNoSidebar}no-sidebar{/if}">
			{**
			 * Шапка
			 *}
			<header id="header" role="banner">
				{hook run='header_banner_begin'}

				<a href="{cfg name='path.root.web'}" title="{cfg name='view.name'}" class="logo"></a>

				<h2 class="site-description">{cfg name='view.description'}</h2>

				{* Основная навигация *}
				<nav id="nav">
					<ul class="nav nav-main">
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

					{hook run='main_menu'}
				</nav>

				{**
				 * Юзербар
				 *}
				<nav id="userbar" class="clearfix">
					{if $iUserCurrentCountTalkNew}
						<div class="messages">
							{$aLang.userbar_messages_you_have} {$iUserCurrentCountTalkNew} {$iUserCurrentCountTalkNew|declension:$aLang.userbar_messages_new_new_count_declension:'russian'} <a href="{router page='talk'}" class="new-messages" id="new_messages" title="{if $iUserCurrentCountTalkNew}{$aLang.user_privat_messages_new}{/if}">{$iUserCurrentCountTalkNew|declension:$aLang.userbar_messages_new_count_declension:'russian'}</a>
						</div>
					{/if}
					{hook run='userbar_nav'}

						<div class="menu">
							{if $oUserCurrent}
								{$aLang.userbar_hello}, <a href="{$oUserCurrent->getUserWebPath()}" class="username">{$oUserCurrent->getLogin()}</a>

								<div class="usermenu-trigger"><i></i></div>

								<div class="dropdown-usermenu">
									<a href="{$oUserCurrent->getUserWebPath()}"><img src="{$oUserCurrent->getProfileAvatarPath(24)}" alt="avatar" class="avatar" /></a>
									<a href="{$oUserCurrent->getUserWebPath()}" class="user-title">{$aLang.user_menu_profile}</a>

									<ul class="links">
										<li></li>
										<li><i class="icon-native-usermenu-favourites"></i><a href="{$oUserCurrent->getUserWebPath()}favourites/topics/">{$aLang.user_menu_profile_favourites}</a></li>
										<li><i class="icon-native-usermenu-talk"></i><a href="{router page='talk'}">{$aLang.talk_menu_inbox}</a></li>
										<li><i class="icon-native-usermenu-settings"></i><a href="{router page='settings'}">{$aLang.settings_menu}</a></li>
										{hook run='userbar_item'}
									</ul>

									<ul class="links noborder nopad">
										<li><i class="icon-native-usermenu-logout"></i><a href="{router page='login'}exit/?security_ls_key={$ALTO_SECURITY_KEY}">{$aLang.exit}</a></li>
									</ul>
								</div>
							{else}
								{hook run='userbar_item'}
								<a href="{router page='login'}" data-type="modal-toggle" data-option-target="modal-login" onclick="jQuery('[data-option-target=tab-pane-login]').tab('activate');">{$aLang.user_login_submit}</a> или
								<a href="#" data-type="modal-toggle" data-option-target="modal-login" onclick="jQuery('[data-option-target=tab-pane-registration]').tab('activate');">{$aLang.registration_submit}</a>
							{/if}
						</div>

						<form action="{router page='search'}topics/" class="search-form">
							<input type="text" placeholder="{$aLang.search}" maxlength="255" name="q" class="search-form-input width-full">
						</form>
				</nav> 

				{hook run='header_banner_end'}
			</header>

			{* Системные сообщения *}
			{include file='system_message.tpl'}

			{* Навигация *}
			{if $sNav or $sNavContent}
				<div class="nav-group">
					{if $sNav}
						{if in_array($sNav, $aMenuContainers)}
							{$aMenuFetch.$sNav}
						{else}
							{include file="nav.$sNav.tpl"}
						{/if}
					{else}
						{include file="nav.$sNavContent.content.tpl"}
					{/if}
				</div>
			{/if}
			

			{* Вспомогательный контейнер-обертка *}
			<div id="wrapper" class="{hook run='wrapper_class'}">
				{* Контент *}
				<div id="content-wrapper">
					<div id="content" role="main" {if $sMenuItemSelect == 'profile'}itemscope itemtype="http://data-vocabulary.org/Person"{/if}>

						{hook run='content_begin'}
						{block name='layout_content_begin'}{/block}

						{block name='layout_page_title' hide}
							<h2 class="page-header">{$smarty.block.child}</h2>
						{/block}
						
						{block name='layout_content'}{/block}

						{block name='layout_content_end'}{/block}
						{hook run='content_end'}
					</div>
				</div>

				{* Сайдбар *}
				{if !$bNoSidebar}
					<aside id="sidebar" role="complementary">
						{wgroup name='right'}
					</aside>
				{/if}
			</div> {* /wrapper *}

			{* Подвал *}
			<footer id="footer">
				{hook run='footer_begin'}

				{block name='layout_footer_begin'}{/block}

				<div class="copyright">
					<p>{hook run='copyright'}</p>
					<p>{$smarty.now|date_format:"%Y"}</p>
				</div>

				{block name='layout_footer_end'}{/block}

				{hook run='footer_end'}
			</footer>
		</div> {* /container *}
	{/block}

	{* Подключение модальных окон *}
	{if E::IsUser()}
		{include file='modals/modal.create.tpl'}
		{include file='modals/modal.favourite_tags.tpl'}
	{else}
		{include file='modals/modal.auth.tpl'}
	{/if}

	{* Подключение тулбара *}
    <aside class="toolbar" id="toolbar" data-type="toolbar">
	{wgroup name='toolbar'}
    </aside>

	{if E::IsAdmin()}
		<section class="admin-link">
			<a href="{router page='admin'}" title="{$aLang.admin_title}">
				<i class="icon-native-admin-link"></i>
			</a>
		</section>
	{/if}

	{hook run='body_end'}
</body>
</html>