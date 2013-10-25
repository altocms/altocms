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
<ul class="b-sidebar-menu">
    {hook run='admin_menu_top'}
    <li class="nav-header"><i class="icon-info-sign"></i>{$aLang.action.admin.menu_info}</li>
    <li class="nav-menu_dashboard {if $sEvent=='' OR $sEvent=='info-dashboard'}active{/if}">
        <a href="{router page='admin'}info-dashboard/">{$aLang.action.admin.menu_info_dashboard}</a>
    </li>
    <li class="nav-menu_report {if $sEvent=='info-report'}active{/if}">
        <a href="{router page='admin'}info-report/">{$aLang.action.admin.menu_info_report}</a>
    </li>
    <li class="nav-menu_phpinfo {if $sEvent=='info-phpinfo'}active{/if}">
        <a href="{router page='admin'}info-phpinfo/">{$aLang.action.admin.menu_info_phpinfo}</a>
    </li>
    {hook run='admin_menu_info'}

    <li class="nav-header"><i class="icon-file"></i>{$aLang.action.admin.menu_content}</li>
    <li class="nav-menu_pages {if $sEvent=='content-pages'}active{/if}">
        <a href="{router page="admin"}content-pages/">{$aLang.action.admin.menu_content_pages}</a>
    </li>
    <li class="nav-menu_blogs {if $sEvent=='content-blogs'}active{/if}">
        <a href="{router page="admin"}content-blogs/">{$aLang.action.admin.menu_content_blogs}</a>
    </li>
    <li class="nav-menu_topics {if $sEvent=='content-topics'}active{/if}">
        <a href="{router page="admin"}content-topics/">{$aLang.action.admin.menu_content_topics}</a>
    </li>
    <li class="nav-menu_comments {if $sEvent=='content-comments'}active{/if}">
        <a href="{router page="admin"}content-comments/">{$aLang.action.admin.menu_content_comments}</a>
    </li>
    <li class="nav-menu_mresources {if $sEvent=='content-mresources'}active{/if}">
        <a href="{router page="admin"}content-mresources/">{$aLang.action.admin.menu_content_mresources}</a>
    </li>
    {hook run='admin_menu_content'}

    <li class="nav-header"><i class="icon-user"></i>{$aLang.action.admin.menu_users}</li>
    <li class="nav-menu_users {if $sEvent=='users-list'}active{/if}">
        <a href="{router page="admin"}users-list/">{$aLang.action.admin.menu_users_list}</a>
    </li>
    <li class="nav-menu_banlist {if $sEvent=='users-banlist'}active{/if}">
        <a href="{router page="admin"}users-banlist/">{$aLang.action.admin.menu_users_banlist}</a>
    </li>
    <!-- li class="nav-menu_invites {if $sEvent=='invites'}active{/if}">
        <a href="{router page="admin"}users-invites/">{$aLang.action.admin.menu_users_invites}</a>
    </li -->
    {hook run='admin_menu_users'}

    <li class="nav-header"><i class="icon-cog"></i>{$aLang.action.admin.menu_settings}</li>
    <li class="nav-menu_settings {if $sEvent=='settings-config'}active{/if}">
        <a href="{router page="admin"}settings-site/">{$aLang.action.admin.menu_settings_site}</a>
    </li>
    <li class="nav-menu_lang {if $sEvent=='lang'}active{/if}">
        <a href="{router page="admin"}settings-lang/">{$aLang.action.admin.menu_settings_lang}</a>
    </li>
    <li class="nav-menu_blogtypes {if $sEvent=='settings-blogtypes'}active{/if}">
        <a href="{router page="admin"}settings-blogtypes/">{$aLang.action.admin.menu_settings_blogtypes}</a>
    </li>
    <li class="nav-menu_content {if $sEvent=='settings-contenttypes'}active{/if}">
        <a href="{router page="admin"}settings-contenttypes/">{$aLang.action.admin.menu_settings_contenttypes}</a>
    </li>
    <li class="nav-menu_userrights {if $sEvent=='settings-userrights'}active{/if}">
        <a href="{router page="admin"}settings-userrights/">{$aLang.action.admin.menu_settings_userrights}</a>
    </li>
    <li class="nav-menu_userfields {if $sEvent=='settings-userfields'}active{/if}">
        <a href="{router page="admin"}settings-userfields/">{$aLang.action.admin.menu_settings_userfields}</a>
    </li>
    {hook run='admin_menu_settings'}

    <li class="nav-header"><i class="icon-th"></i>{$aLang.action.admin.menu_site}</li>
    <li class="nav-menu_skins {if $sEvent=='site-skins'}active{/if}">
        <a href="{router page="admin"}site-skins/">{$aLang.action.admin.menu_site_skins}</a>
    </li>
    <li class="nav-menu_widgets {if $sEvent=='site-widgets'}active{/if}">
        <a href="{router page="admin"}site-widgets/">{$aLang.action.admin.menu_widgets}</a>
    </li>
    <li class="nav-menu_plugins {if $sEvent=='site-plugins'}active{/if}">
        <a href="{router page="admin"}site-plugins/">{$aLang.action.admin.menu_plugins}</a>
    </li>
    {hook run='admin_menu_site'}

    <li class="nav-header"><i class="icon-list-alt"></i>{$aLang.action.admin.menu_logs}</li>
    <li class="nav-menu_logs_errors {if $sEvent=='logs-error' and $sMode=='errors'}active{/if}">
        <a href="{router page="admin"}logs-error/">{$aLang.action.admin.menu_logs_error}</a>
    </li>
    <li class="nav-menu_logs_sqlerrors {if $sEvent=='logs-sqlerror'}active{/if}">
        <a href="{router page="admin"}logs-sqlerror/">{$aLang.action.admin.menu_logs_sqlerror}</a>
    </li>
    <li class="nav-menu_logs_sql {if $sEvent=='logs-sqllog'}active{/if}">
        <a href="{router page="admin"}logs-sqllog/">{$aLang.action.admin.menu_logs_sqllog}</a>
    </li>
    {hook run='admin_menu_logs'}

    <li class="nav-header"><i class="icon-wrench"></i>{$aLang.action.admin.menu_tools}</li>
    <li class="nav-menu_reset {if $sEvent=='tools-reset'}active{/if}">
        <a href="{router page="admin"}tools-reset/">{$aLang.action.admin.menu_tools_reset}</a>
    </li>
    {if Config::Get('module.comment.use_nested')}
    <li class="nav-menu_commentstree {if $sEvent=='tools-commentstree'}active{/if}">
        <a href="{router page="admin"}tools-commentstree/">{$aLang.action.admin.menu_tools_commentstree}</a>
    </li>
    {/if}
    <li class="nav-menu_recalcfavourites {if $sEvent=='tools-recalcfavourites'}active{/if}">
        <a href="{router page="admin"}tools-recalcfavourites/">{$aLang.action.admin.menu_tools_recalcfavourites}</a>
    </li>
    <li class="nav-menu_recalcvotes {if $sEvent=='tools-recalcvotes'}active{/if}">
        <a href="{router page="admin"}tools-recalcvotes/">{$aLang.action.admin.menu_tools_recalcvotes}</a>
    </li>
    <li class="nav-menu_recalctopics {if $sEvent=='tools-recalctopics'}active{/if}">
        <a href="{router page="admin"}tools-recalctopics/">{$aLang.action.admin.menu_tools_recalctopics}</a>
    </li>
    <li class="nav-menu_recalcblograting {if $sEvent=='tools-recalcblograting'}active{/if}">
        <a href="{router page="admin"}tools-recalcblograting/">{$aLang.action.admin.menu_tools_recalcblograting}</a>
    </li>
    <li class="nav-menu_checkdb {if $sEvent=='tools-checkdb'}active{/if}">
        <a href="{router page="admin"}tools-checkdb/">{$aLang.action.admin.menu_tools_checkdb}</a>
    </li>

    {hook run='admin_menu_items_end'}

</ul>

{/block}
