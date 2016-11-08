<!DOCTYPE html>

<!--[if lt IE 7]>
<html class="no-js ie6 oldie" lang="{Config::Get('i18n.lang')}" dir="{Config::Get('i18n.dir')}"> <![endif]-->
<!--[if IE 7]>
<html class="no-js ie7 oldie" lang="{Config::Get('i18n.lang')}" dir="{Config::Get('i18n.dir')}"> <![endif]-->
<!--[if IE 8]>
<html class="no-js ie8 oldie" lang="{Config::Get('i18n.lang')}" dir="{Config::Get('i18n.dir')}"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="{Config::Get('i18n.lang')}" dir="{Config::Get('i18n.dir')}"> <!--<![endif]-->

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
        <link href='//fonts.googleapis.com/css?family=Roboto:300,400,500,700&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    {/if}

    <link href="{asset file="img/favicon.png" theme=true}?v1" rel="shortcut icon"/> 

    <link rel="search" type="application/opensearchdescription+xml" href="{router page='search'}opensearch/"
          title="{Config::Get('view.name')}"/>

    {if $bRefreshToHome}
        <meta HTTP-EQUIV="Refresh" CONTENT="3; URL={Config::Get('path.root.url')}/">
    {/if}

    {hook run="html_head_tags"}

    <script type="text/javascript">
        var DIR_WEB_ROOT = '{Config::Get('path.root.url')}';
        var DIR_STATIC_SKIN = '{Config::Get('path.static.skin')}';
        var DIR_ROOT_ENGINE_LIB = '{Config::Get('path.root.engine_lib')}';
        var LIVESTREET_SECURITY_KEY = '{$ALTO_SECURITY_KEY}';
        var SESSION_ID = '{$_sPhpSessionId}';
        var BLOG_USE_TINYMCE = '{Config::Get('view.tinymce')}';

        var tinyMCE = tinymce = false;
        var TINYMCE_LANG = {if E::ModuleLang()->GetLang() == 'ru'}'ru'{else}'en'{/if};

        var aRouter = [];
        {strip}{foreach from=$aRouter key=sPage item=sPath} aRouter['{$sPage}'] = '{$sPath}'; {/foreach}{/strip}
    </script>

    {$aHtmlHeadFiles.js}

    <script type="text/javascript">
        ls.lang.load({json var = $aLangJs});
        ls.registry.set('comment_max_tree', {json var=Config::Get('module.comment.max_tree')});
        ls.registry.set('widget_stream_show_tip', {json var=Config::Get('block.stream.show_tip')});
    </script>

    {hook run='html_head_end'}
</head>

{if E::IsUser()}
    {$body_classes=$body_classes|cat:' alto-user-role-user'}

    {if $oUserCurrent->isAdministrator()}
        {$body_classes=$body_classes|cat:' alto-user-role-admin'}
    {/if}
    {if $oUserCurrent->isModerator()}
        {$body_classes=$body_classes|cat:' alto-user-role-moderator'}
    {/if}
{else}
    {$body_classes=$body_classes|cat:' alto-user-role-guest'}
{/if}

{if !E::IsAdmin()}
    {$body_classes=$body_classes|cat:' alto-user-role-not-admin'}
{/if}


<body class="{$body_classes} light">
{hook run='body_begin'}

<div class="container">

    <hgroup class="site-info">
        <h1 class="text-center site-name"><a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a></h1>
        <h5 class="text-center site-description">{Config::Get('view.description')}</h5>
    </hgroup>

    {include file='commons/common.messages.tpl'}

    <div class="light-form">
        {block name="layout_content"}
            {hook run='content_begin'}

            {hook run='content_end'}
        {/block}
    </div><!-- /light-form -->
</div><!-- /container -->

{hook run='body_end'}

</body>
</html>
