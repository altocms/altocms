 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<!DOCTYPE html>

{block name="layout_vars"}{/block}

<!--[if lt IE 7]>
<html class="no-js ie6 oldie" lang="{Config::Get('i18n.lang')}" dir="{Config::Get('i18n.dir')}"> <![endif]-->
<!--[if IE 7]>
<html class="no-js ie7 oldie" lang="{Config::Get('i18n.lang')}" dir="{Config::Get('i18n.dir')}"> <![endif]-->
<!--[if IE 8]>
<html class="no-js ie8 oldie" lang="{Config::Get('i18n.lang')}" dir="{Config::Get('i18n.dir')}"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="{Config::Get('i18n.lang')}" dir="{Config::Get('i18n.dir')}"> <!--<![endif]-->

<head>
    {block name="layout_head"}
        {hook run='layout_head_begin'}

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{$sHtmlTitle}</title>

        <meta name="description" content="{$sHtmlDescription}">
        <meta name="keywords" content="{$sHtmlKeywords}">

        {$aHtmlHeadFiles.css}

        {*<link href='//fonts.googleapis.com/css?family=Roboto:300,400,500,700&subset=latin,cyrillic' rel='stylesheet' type='text/css'>*}
        {*<link href='//fonts.googleapis.com/css?family=Roboto+Condensed:300,400,500,700&subset=latin,cyrillic' rel='stylesheet' type='text/css'>*}


        <link href="{asset file="images/favicon.ico" theme=true}?v1" rel="shortcut icon"/>
        <link rel="search" type="application/opensearchdescription+xml" href="{router page='search'}opensearch/" title="{Config::Get('view.name')}"/>

        {if $aHtmlRssAlternate}
            <link rel="alternate" type="application/rss+xml" href="{$aHtmlRssAlternate.url}" title="{$aHtmlRssAlternate.title}">
        {/if}

        {if $sHtmlCanonical}
            <link rel="canonical" href="{$sHtmlCanonical}"/>
        {/if}

        {if $bRefreshToHome}
            <meta HTTP-EQUIV="Refresh" CONTENT="3; URL={Config::Get('path.root.url')}/">
        {/if}

        {hook run="html_head_tags"}

        <script type="text/javascript">
            var DIR_WEB_ROOT        = '{Config::Get('path.root.url')}';
            var DIR_STATIC_SKIN     = '{Config::Get('path.static.skin')}';
            var DIR_ROOT_ENGINE_LIB = '{Config::Get('path.root.engine_lib')}';
            var ALTO_SECURITY_KEY   = '{$ALTO_SECURITY_KEY}';
            var SESSION_ID          = '{$_sPhpSessionId}';


            var tinymce = false;
            var TINYMCE_LANG = {if Config::Get('lang.current') == 'ru'}'ru'{else}'en'{/if};

            var aRouter = [];
            {foreach from=$aRouter key=sPage item=sPath}
                aRouter['{$sPage}'] = '{$sPath}';
            {/foreach}

            {$SWF_DIR_NAME=E::ViewerAsset_AssetFileHashDir("{Config::Get('path.root.dir')}common/templates/frontend/libs/vendor/jquery.fileapi/FileAPI/")}
            window.FileAPI = {
                debug: false, // debug mode
                media: true,
                staticPath: "{F::File_GetAssetUrl()}{$SWF_DIR_NAME}" // path to *.swf
            };

        </script>

        {$aHtmlHeadFiles.js}

        <script type="text/javascript">
            ls.lang.load({json var = $aLangJs});
            ls.registry.set('comment_max_tree', {json var=Config::Get('module.comment.max_tree')});
            ls.registry.set('block_stream_show_tip', {json var=Config::Get('block.stream.show_tip')});
        </script>

        {*<!--[if lt IE 9]>*}
        {*<script src="{asset file='js/respond.min.js'}"></script>*}
        {*<![endif]-->*}

        <script src="{asset file='js/theme.js' theme=true}"></script>

        {if E::IsUser()}
            {$body_classes=$body_classes|cat:' alto-user-role-user'}

            {if E::IsAdmin()}
                {$body_classes=$body_classes|cat:' alto-user-role-admin'}
            {/if}
        {else}
            {$body_classes=$body_classes|cat:' alto-user-role-guest'}
        {/if}

        {if !E::IsAdmin()}
            {$body_classes=$body_classes|cat:' alto-user-role-not-admin'}
        {/if}

        {hook run='layout_head_end'}
    {/block}
</head>


<body class="{$body_classes}">
    {block name="layout_body"}

        {hook run='layout_body_begin'}

        {* Модальные окна *}
        {if E::IsUser()}
            {include file='modals/modal.write.tpl'}
            {include file='modals/modal.favourite_tags.tpl'}
        {else}
            {include file='modals/modal.auth.tpl'}
        {/if}
        {include file='modals/modal.empty.tpl'}

        {* Строим менюшки *}
        {include file='commons/common.header_nav_pages.tpl'}
        {include file='commons/common.header_nav.tpl'}
        {if Config::Get('view.header.banner')}
            {wgroup group="topbanner"}
        {/if}
        {*{include file='commons/common.header_nav_blogs.tpl'}*}


        {block name="main_container"}
        {* А вот и основной контент *}
        <div class="container content {hook run='container_class'}">
            <div class="row">

                <!-- САЙДБАР ЛЕВЫЙ-->
                {if !$noSidebar AND $sidebarPosition == 'left'}
                    {include file='commons/common.sidebar.tpl'}
                {/if}

                <!-- КОНТЕНТ-->
                <div id="content-container" class="{if $noSidebar}col-sm-24{else}col-sm-17{/if}">
                    {include file='menus/menu.content.tpl'}

                    {block name="layout_pre_content"}

                    {/block}
                    {include file='commons/common.messages.tpl'}
                    {block name="layout_content"}
                        {hook run='content_begin'}

                        {hook run='content_end'}
                    {/block}
                </div>

                <!-- САЙДБАР ПРАВЫЙ-->
                {if !$noSidebar AND $sidebarPosition != 'left'}
                    {include file='commons/common.sidebar.tpl'}
                {/if}

            </div>
        </div>
        {/block}

        <!-- ПОДВАЛ -->
        <div class="footer-container">
            <div class="container">

                <div class="row footer">

                    <!-- Три  колонки ссылок -->
                    <div id="footer-main" class="col-xs-16">

                        <div class="row">
                            <div class="col-sm-8">
                                <h4>{$aLang.footer_menu_navigate_title}</h4>
                                {menu id='footer_site_menu'}
                            </div>

                            <div class="col-sm-8">
                                <h4>{$aLang.footer_menu_navigate_info}</h4>
                                {menu id='footer_info'}
                            </div>

                            <div class="col-sm-8">

                            </div>
                        </div>

                        {hook run='footer_end'}

                    </div>


                    <!-- Соцсети и счетчики -->
                    <div id="footer-other" class="col-xs-8">
                        <ul class="social-icons pull-right">
                            <li><a class="link link-dark link-lead link-clear" href="#"><i class="fa fa-facebook-square"></i></a></li>
                            <li><a class="link link-dark link-lead link-clear" href="#"><i class="fa fa-instagram"></i></a></li>
                            <li><a class="link link-dark link-lead link-clear" href="#"><i class="fa fa-github-alt"></i></a></li>
                            <li><a class="link link-dark link-lead link-clear" href="#"><i class="fa fa-vk"></i></a></li>
                            <li><a class="link link-dark link-lead link-clear" href="#"><i class="fa fa-skype"></i></a></li>
                        </ul>
                        {*<img src="{asset file="images/counter.png" theme=true}" alt="counter" class="pull-right counter"/>*}
                    </div>

                </div>

            </div>
        </div>

        <!-- Второй подвал -->
        <div class="footer-2-container">
            <div class="container">

                <div class="left-copyright pull-left">
                    <img src="{asset file="images/alto-logo.png" theme=true}" alt=""/>
                    {hook run='copyright'}
                </div>

                <div class="right-copyright pull-right">
                    <span>Desight by</span>
                    <a href="http://creatime.org" class="link link-blue link-lead link-clear" title="design studio creatime.org">
                        <img src="{asset file="images/creatime-logo.png" theme=true}" alt="студия дизайна creatime.org">
                    </a>
                </div>

            </div>
        </div>

{include file='commons/common.toolbar.tpl'}

{hook run='layout_body_end'}
{/block}
    <div class="device-xs visible-xs"></div>
    <div class="device-sm visible-sm"></div>
    <div class="device-md visible-md"></div>
    <div class="device-lg visible-lg"></div>
</body>
</html>
