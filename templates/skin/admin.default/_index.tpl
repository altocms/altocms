{extends file='themes/default/default.tpl'}

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
<ul class="b-sidebar-menu">
    {hook run='admin_menu_top'}
    <li class="nav-header"><i class="icon-info-sign"></i>{$aLang.action.admin.menu_info}</li>
    <li class="nav-menu_dashboard {if $sEvent=='' OR $sEvent=='dashboard'}active{/if}">
        <a href="{router page='admin'}dashboard/">{$aLang.action.admin.menu_info_dashboard}</a>
    </li>
    <li class="nav-menu_report {if $sEvent=='report'}active{/if}">
        <a href="{router page='admin'}report/">{$aLang.action.admin.menu_info_report}</a>
    </li>
    <li class="nav-menu_phpinfo {if $sEvent=='phpinfo'}active{/if}">
        <a href="{router page='admin'}phpinfo/">{$aLang.action.admin.menu_info_phpinfo}</a>
    </li>
    {hook run='admin_menu_info'}

    <li class="nav-header"><i class="icon-file"></i>{$aLang.action.admin.menu_content}</li>
    <li class="nav-menu_pages {if $sEvent=='pages'}active{/if}">
        <a href="{router page="admin"}pages/">{$aLang.action.admin.menu_content_pages}</a>
    </li>
    <li class="nav-menu_blogs {if $sEvent=='blogs'}active{/if}">
        <a href="{router page="admin"}blogs/">{$aLang.action.admin.menu_content_blogs}</a>
    </li>
    <li class="nav-menu_topics {if $sEvent=='topics'}active{/if}">
        <a href="{router page="admin"}topics/">{$aLang.action.admin.menu_content_topics}</a>
    </li>
    <li class="nav-menu_comments {if $sEvent=='comments'}active{/if}">
        <a href="{router page="admin"}comments/">{$aLang.action.admin.menu_content_comments}</a>
    </li>
    {hook run='admin_menu_content'}

    <li class="nav-header"><i class="icon-user"></i>{$aLang.action.admin.menu_users}</li>
    <li class="nav-menu_users {if $sEvent=='users'}active{/if}">
        <a href="{router page="admin"}users/">{$aLang.action.admin.menu_users_list}</a>
    </li>
    <li class="nav-menu_banlist {if $sEvent=='banlist'}active{/if}">
        <a href="{router page="admin"}banlist/">{$aLang.action.admin.menu_users_banlist}</a>
    </li>
    <!-- li class="nav-menu_invites {if $sEvent=='invites'}active{/if}">
        <a href="{router page="admin"}invites/">{$aLang.action.admin.menu_users_invites}</a>
    </li -->
    {hook run='admin_menu_users'}

    <li class="nav-header"><i class="icon-cog"></i>{$aLang.action.admin.menu_settings}</li>
    <li class="nav-menu_settings {if $sEvent=='config'}active{/if}">
        <a href="{router page="admin"}config/">{$aLang.action.admin.menu_settings_site}</a>
    </li>
    <li class="nav-menu_lang {if $sEvent=='lang'}active{/if}">
        <a href="{router page="admin"}lang/">{$aLang.action.admin.menu_settings_lang}</a>
    </li>
    <li class="nav-menu_blogtypes {if $sEvent=='blogtypes'}active{/if}">
        <a href="{router page="admin"}blogtypes/">{$aLang.action.admin.menu_settings_blogtypes}</a>
    </li>
    <li class="nav-menu_content {if $sEvent=='content'}active{/if}">
        <a href="{router page="admin"}content/">{$aLang.action.admin.menu_settings_content}</a>
    </li>
    <li class="nav-menu_userrights {if $sEvent=='userrights'}active{/if}">
        <a href="{router page="admin"}userrights/">{$aLang.action.admin.menu_settings_userrights}</a>
    </li>
    <li class="nav-menu_userfields {if $sEvent=='userfields'}active{/if}">
        <a href="{router page="admin"}userfields/">{$aLang.action.admin.menu_settings_userfields}</a>
    </li>
    {hook run='admin_menu_settings'}

    <li class="nav-header"><i class="icon-th"></i>{$aLang.action.admin.menu_site}</li>
    <li class="nav-menu_skins {if $sEvent=='skins'}active{/if}">
        <a href="{router page="admin"}skins/">{$aLang.action.admin.menu_site_skins}</a>
    </li>
    <li class="nav-menu_widgets {if $sEvent=='widgets'}active{/if}">
        <a href="{router page="admin"}widgets/">{$aLang.action.admin.menu_widgets}</a>
    </li>
    <li class="nav-menu_plugins {if $sEvent=='plugins'}active{/if}">
        <a href="{router page="admin"}plugins/">{$aLang.action.admin.menu_plugins}</a>
    </li>
    {hook run='admin_menu_site'}

    <li class="nav-header"><i class="icon-list-alt"></i>{$aLang.action.admin.menu_logs}</li>
    <li class="nav-menu_logs_errors {if $sEvent=='logs' and $sMode=='errors'}active{/if}">
        <a href="{router page="admin"}logs/errors/">{$aLang.action.admin.menu_logs_error}</a>
    </li>
    <li class="nav-menu_logs_sqlerrors {if $sEvent=='logs' and $sMode=='sqlerrors'}active{/if}">
        <a href="{router page="admin"}logs/sqlerrors/">{$aLang.action.admin.menu_logs_sql_error}</a>
    </li>
    <li class="nav-menu_logs_sql {if $sEvent=='logs' and $sMode=='sql'}active{/if}">
        <a href="{router page="admin"}logs/sql/">{$aLang.action.admin.menu_logs_sql_log}</a>
    </li>
    {hook run='admin_menu_logs'}

    <li class="nav-header"><i class="icon-wrench"></i>{$aLang.action.admin.menu_tools}</li>
    <li class="nav-menu_reset {if $sEvent=='reset'}active{/if}">
        <a href="{router page="admin"}reset/">{$aLang.action.admin.menu_tools_reset}</a>
    </li>
    {if Config::Get('module.comment.use_nested')}
    <li class="nav-menu_commentstree {if $sEvent=='commentstree'}active{/if}">
        <a href="{router page="admin"}commentstree/">{$aLang.action.admin.menu_tools_commentstree}</a>
    </li>
    {/if}
    <li class="nav-menu_recalcfavourites {if $sEvent=='recalcfavourites'}active{/if}">
        <a href="{router page="admin"}recalcfavourites/">{$aLang.action.admin.menu_tools_recalcfavourites}</a>
    </li>
    <li class="nav-menu_recalcvotes {if $sEvent=='recalcvotes'}active{/if}">
        <a href="{router page="admin"}recalcvotes/">{$aLang.action.admin.menu_tools_recalcvotes}</a>
    </li>
    <li class="nav-menu_recalctopics {if $sEvent=='recalctopics'}active{/if}">
        <a href="{router page="admin"}recalctopics/">{$aLang.action.admin.menu_tools_recalctopics}</a>
    </li>
    <li class="nav-menu_recalcblograting {if $sEvent=='recalcblograting'}active{/if}">
        <a href="{router page="admin"}recalcblograting/">{$aLang.action.admin.menu_tools_recalcblograting}</a>
    </li>
    <li class="nav-menu_checkdb {if $sEvent=='checkdb'}active{/if}">
        <a href="{router page="admin"}checkdb/">{$aLang.action.admin.menu_tools_checkdb}</a>
    </li>

    {hook run='admin_menu_items_end'}

</ul>

{/block}
