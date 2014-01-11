{extends file='[themes]default/default.tpl'}

{block name="sysmessage"}
    {if !$noShowSystemMessage AND ($aMsgError OR $aMsgNotice)}
    <div class="row-fluid" style="height: 1px;">
        <div class="span12">
            {if $aMsgError}
                {foreach from=$aMsgError item=aMsg}
                    <div class="b-sysmessage_alert alert alert-error">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        {if $aMsg.title!=''}
                            <h4 class="alert-heading">{$aMsg.title}:</h4>
                        {/if}
                        {$aMsg.msg}
                    </div>
                {/foreach}
            {/if}

            {if $aMsgNotice}
                {foreach from=$aMsgNotice item=aMsg}
                    <div class="b-sysmessage_success alert alert-success">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        {if $aMsg.title!=''}
                            <h4 class="alert-heading">{$aMsg.title}:</h4>
                        {/if}
                        {$aMsg.msg}
                    </div>
                {/foreach}
            {/if}
        </div>
    </div>
    {/if}
{/block}

{block name="sidebar"}
<a class="logo" href="{router page="admin"}"><span></span></a>

<ul class="menu-left">
    {hook run='admin_menu_top'}
<li class="menu-header">

	<a data-toggle="collapse" href="#MenuInfo">
		<i class="icon icon-info-sign"></i>{$aLang.action.admin.menu_info}
	</a>
							
<ul id="MenuInfo" style="{if $sEvent=='' OR $sEvent=='info-dashboard' || $sEvent=='info-report' || $sEvent=='info-phpinfo'}height: auto;{else}height: 0px;{/if}">
    <li class="menu-item_dashboard {if $sEvent=='' OR $sEvent=='info-dashboard'}active{/if}">
        <a href="{router page='admin'}info-dashboard/">{$aLang.action.admin.menu_info_dashboard}</a>
    </li>
    <li class="menu-item_report {if $sEvent=='info-report'}active{/if}">
        <a href="{router page='admin'}info-report/">{$aLang.action.admin.menu_info_report}</a>
    </li>
    <li class="menu-item_phpinfo {if $sEvent=='info-phpinfo'}active{/if}">
        <a href="{router page='admin'}info-phpinfo/">{$aLang.action.admin.menu_info_phpinfo}</a>
    </li>
    {hook run='admin_menu_info'}
</ul>

</li>

<li class="menu-header">

	<a data-toggle="collapse" href="#MenuContent">
		<i class="icon icon-file"></i>{$aLang.action.admin.menu_content}
	</a>
		
<ul id="MenuContent" style="{if $sEvent=='content-pages' || $sEvent=='content-blogs' || $sEvent=='content-topics' || $sEvent=='content-comments' || $sEvent=='content-mresources'}height: auto;{else}height: 0px;{/if}">
    <li class="menu-item_pages {if $sEvent=='content-pages'}active{/if}">
        <a href="{router page="admin"}content-pages/">{$aLang.action.admin.menu_content_pages}</a>
    </li>
    <li class="menu-item_blogs {if $sEvent=='content-blogs'}active{/if}">
        <a href="{router page="admin"}content-blogs/">{$aLang.action.admin.menu_content_blogs}</a>
    </li>
    <li class="menu-item_topics {if $sEvent=='content-topics'}active{/if}">
        <a href="{router page="admin"}content-topics/">{$aLang.action.admin.menu_content_topics}</a>
    </li>
    <li class="menu-item_comments {if $sEvent=='content-comments'}active{/if}">
        <a href="{router page="admin"}content-comments/">{$aLang.action.admin.menu_content_comments}</a>
    </li>
    <li class="menu-item_mresources {if $sEvent=='content-mresources'}active{/if}">
        <a href="{router page="admin"}content-mresources/">{$aLang.action.admin.menu_content_mresources}</a>
    </li>
    {hook run='admin_menu_content'}
</ul>

</li>

<li class="menu-header">
	
	<a data-toggle="collapse" href="#MenuUsers">
		<i class="icon icon-user"></i>{$aLang.action.admin.menu_users}
	</a>
	
<ul id="MenuUsers" style="{if $sEvent=='users-list' || $sEvent=='users-banlist'}height: auto;{else}height: 0px;{/if}">
    <li class="menu-item_users {if $sEvent=='users-list'}active{/if}">
        <a href="{router page="admin"}users-list/">{$aLang.action.admin.menu_users_list}</a>
    </li>
    <li class="menu-item_banlist {if $sEvent=='users-banlist'}active{/if}">
        <a href="{router page="admin"}users-banlist/">{$aLang.action.admin.menu_users_banlist}</a>
    </li>
    <!-- li class="menu-item_invites {if $sEvent=='invites'}active{/if}">
        <a href="{router page="admin"}users-invites/">{$aLang.action.admin.menu_users_invites}</a>
    </li -->
    {hook run='admin_menu_users'}
</ul>
	
</li>

<li class="menu-header">
	
	<a data-toggle="collapse" href="#MenuSettings">
		<i class="icon icon-cog"></i>{$aLang.action.admin.menu_settings}
	</a>
	
<ul id="MenuSettings" style="{if $sEvent=='settings-site' || $sEvent=='settings-lang' || $sEvent=='settings-blogtypes' || $sEvent=='settings-contenttypes' || $sEvent=='settings-userrights' || $sEvent=='settings-userrights' || $sEvent=='settings-userfields'}height: auto;{else}height: 0px;{/if}">
    <li class="menu-item_settings {if $sEvent=='settings-site'}active{/if}">
        <a href="{router page="admin"}settings-site/">{$aLang.action.admin.menu_settings_site}</a>
    </li>
    <li class="menu-item_lang {if $sEvent=='settings-lang'}active{/if}">
        <a href="{router page="admin"}settings-lang/">{$aLang.action.admin.menu_settings_lang}</a>
    </li>
    <li class="menu-item_blogtypes {if $sEvent=='settings-blogtypes'}active{/if}">
        <a href="{router page="admin"}settings-blogtypes/">{$aLang.action.admin.menu_settings_blogtypes}</a>
    </li>
    <li class="menu-item_content {if $sEvent=='settings-contenttypes'}active{/if}">
        <a href="{router page="admin"}settings-contenttypes/">{$aLang.action.admin.menu_settings_contenttypes}</a>
    </li>
    <li class="menu-item_userrights {if $sEvent=='settings-userrights'}active{/if}">
        <a href="{router page="admin"}settings-userrights/">{$aLang.action.admin.menu_settings_userrights}</a>
    </li>
    <li class="menu-item_userfields {if $sEvent=='settings-userfields'}active{/if}">
        <a href="{router page="admin"}settings-userfields/">{$aLang.action.admin.menu_settings_userfields}</a>
    </li>
    {hook run='admin_menu_settings'}
</ul>

</li>

<li class="menu-header">
	
	<a data-toggle="collapse" href="#MenuSite">
		<i class="icon icon-th"></i>{$aLang.action.admin.menu_site}
	</a>
	
<ul id="MenuSite" style="{if $sEvent=='site-skins' || $sEvent=='site-widgets' || $sEvent=='site-plugins'}height: auto;{else}height: 0px;{/if}">
    <li class="menu-item_skins {if $sEvent=='site-skins'}active{/if}">
        <a href="{router page="admin"}site-skins/">{$aLang.action.admin.menu_site_skins}</a>
    </li>
    <li class="menu-item_widgets {if $sEvent=='site-widgets'}active{/if}">
        <a href="{router page="admin"}site-widgets/">{$aLang.action.admin.menu_widgets}</a>
    </li>
    <li class="menu-item_plugins {if $sEvent=='site-plugins'}active{/if}">
        <a href="{router page="admin"}site-plugins/">{$aLang.action.admin.menu_plugins}</a>
    </li>
    {hook run='admin_menu_site'}
</ul>

</li>

<li class="menu-header">
	
	<a data-toggle="collapse" href="#MenuLogs">
		<i class="icon icon-list-alt"></i>{$aLang.action.admin.menu_logs}
	</a>
	
<ul id="MenuLogs" style="{if $sEvent=='logs-error' || $sEvent=='logs-sqlerror' || $sEvent=='logs-sqllog'}height: auto;{else}height: 0px;{/if}">
    <li class="menu-item_logs_errors {if $sEvent=='logs-error'}active{/if}">
        <a href="{router page="admin"}logs-error/">{$aLang.action.admin.menu_logs_error}</a>
    </li>
    <li class="menu-item_logs_sqlerrors {if $sEvent=='logs-sqlerror'}active{/if}">
        <a href="{router page="admin"}logs-sqlerror/">{$aLang.action.admin.menu_logs_sqlerror}</a>
    </li>
    <li class="menu-item_logs_sql {if $sEvent=='logs-sqllog'}active{/if}">
        <a href="{router page="admin"}logs-sqllog/">{$aLang.action.admin.menu_logs_sqllog}</a>
    </li>
    {hook run='admin_menu_logs'}
</ul>
	
</li>

<li class="menu-header">
	
	<a data-toggle="collapse" href="#MenuTools">
		<i class="icon icon-wrench"></i>{$aLang.action.admin.menu_tools}
	</a>
	
<ul id="MenuTools" style="{if $sEvent=='tools-reset' || $sEvent=='tools-commentstree' || $sEvent=='tools-recalcfavourites' || $sEvent=='tools-recalcvotes' || $sEvent=='tools-recalctopics' || $sEvent=='tools-recalcblograting' || $sEvent=='tools-checkdb'}height: auto;{else}height: 0px;{/if}">
    <li class="menu-item_reset {if $sEvent=='tools-reset'}active{/if}">
        <a href="{router page="admin"}tools-reset/">{$aLang.action.admin.menu_tools_reset}</a>
    </li>
    {if Config::Get('module.comment.use_nested')}
    <li class="menu-item_commentstree {if $sEvent=='tools-commentstree'}active{/if}">
        <a href="{router page="admin"}tools-commentstree/">{$aLang.action.admin.menu_tools_commentstree}</a>
    </li>
    {/if}
    <li class="menu-item_recalcfavourites {if $sEvent=='tools-recalcfavourites'}active{/if}">
        <a href="{router page="admin"}tools-recalcfavourites/">{$aLang.action.admin.menu_tools_recalcfavourites}</a>
    </li>
    <li class="menu-item_recalcvotes {if $sEvent=='tools-recalcvotes'}active{/if}">
        <a href="{router page="admin"}tools-recalcvotes/">{$aLang.action.admin.menu_tools_recalcvotes}</a>
    </li>
    <li class="menu-item_recalctopics {if $sEvent=='tools-recalctopics'}active{/if}">
        <a href="{router page="admin"}tools-recalctopics/">{$aLang.action.admin.menu_tools_recalctopics}</a>
    </li>
    <li class="menu-item_recalcblograting {if $sEvent=='tools-recalcblograting'}active{/if}">
        <a href="{router page="admin"}tools-recalcblograting/">{$aLang.action.admin.menu_tools_recalcblograting}</a>
    </li>
    <li class="menu-item_checkdb {if $sEvent=='tools-checkdb'}active{/if}">
        <a href="{router page="admin"}tools-checkdb/">{$aLang.action.admin.menu_tools_checkdb}</a>
    </li>

    {hook run='admin_menu_items_end'}
</ul>

</li>

</ul>

<div class="site-search">
	<form method="get" action="{router page='search'}topics/">
		<input type="text" name="q" class="search-input" placeholder="{$aLang.search_submit}..." style="">
		<button type="submit" style=""><i class="icon icon-search"></i></button>
	</form>
</div>

{/block}
