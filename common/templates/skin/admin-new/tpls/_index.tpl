{extends file='[themes]default/default.tpl'}
{block name="sidebar"}
<aside class="sidebar" id="sidebar">
<div class="panel panel-default">
<div class="panel-body no-padding">

<!-- /.search form -->
<!-- sidebar menu: : style can be found in sidebar.less -->
<ul class="sidebar-menu" id="nav-accordion">
   {hook run='admin_menu_top'}
   <li class="dcjq-parent-li {if $sEvent=='' OR $sEvent=='info-dashboard' || $sEvent=='info-report' || $sEvent=='info-phpinfo'}active{/if}">
      <a href="#">
      <i class="glyphicon glyphicon-th-large"></i><span>{$aLang.action.admin.menu_info}</span><i class="glyphicon glyphicon-chevron-right pull-right"></i>
      </a>
      <ul class="sub">
         <li class="{if $sEvent=='' OR $sEvent=='info-dashboard'}active{/if}">
            <a href="{router page='admin'}info-dashboard/">{$aLang.action.admin.menu_info_dashboard}</a>
         </li>
         <li class="{if $sEvent=='info-report'}active{/if}">
            <a href="{router page='admin'}info-report/">{$aLang.action.admin.menu_info_report}</a>
         </li>
         <li class="{if $sEvent=='info-phpinfo'}active{/if}">
            <a href="{router page='admin'}info-phpinfo/">{$aLang.action.admin.menu_info_phpinfo}</a>
         </li>
         {hook run='admin_menu_info'}
      </ul>
   </li>
   <li class="dcjq-parent-li {if $sEvent=='content-pages' || $sEvent=='content-blogs' || $sEvent=='content-topics' || $sEvent=='content-comments' || $sEvent=='content-mresources'}active{/if}">
      <a href="#">
      <i class="glyphicon glyphicon-briefcase"></i><span>{$aLang.action.admin.menu_content}</span><i class="glyphicon glyphicon-chevron-right pull-right"></i>
      </a>
      <ul class="sub">
         <li class="{if $sEvent=='content-pages'}active{/if}">
            <a href="{router page="admin"}content-pages/">{$aLang.action.admin.menu_content_pages}</a>
         </li>
         <li class="{if $sEvent=='content-blogs'}active{/if}">
            <a href="{router page="admin"}content-blogs/">{$aLang.action.admin.menu_content_blogs}</a>
         </li>
         <li class="{if $sEvent=='content-topics'}active{/if}">
            <a href="{router page="admin"}content-topics/">{$aLang.action.admin.menu_content_topics}</a>
         </li>
         <li class="{if $sEvent=='content-comments'}active{/if}">
            <a href="{router page="admin"}content-comments/">{$aLang.action.admin.menu_content_comments}</a>
         </li>
         <li class="{if $sEvent=='content-mresources'}active{/if}">
            <a href="{router page="admin"}content-mresources/">{$aLang.action.admin.menu_content_mresources}</a>
         </li>
         {hook run='admin_menu_content'}
      </ul>
   </li>
   <li class="dcjq-parent-li {if $sEvent=='users-list' || $sEvent=='users-banlist' || $sEvent=='users-invites'}active{/if}">
      <a href="#">
      <i class="glyphicon glyphicon-user"></i><span>{$aLang.action.admin.menu_users}</span><i class="glyphicon glyphicon-chevron-right pull-right"></i>
      </a>
      <ul class="sub">
         <li class="{if $sEvent=='users-list'}active{/if}">
            <a href="{router page="admin"}users-list/">{$aLang.action.admin.menu_users_list}</a>
         </li>
         <li class="{if $sEvent=='users-banlist'}active{/if}">
            <a href="{router page="admin"}users-banlist/">{$aLang.action.admin.menu_users_banlist}</a>
         </li>
         <li class="{if $sEvent=='users-invites'}active{/if}">
            <a href="{router page="admin"}users-invites/">{$aLang.action.admin.menu_users_invites}</a>
         </li>
         {hook run='admin_menu_users'}
      </ul>
   </li>
   <li class="dcjq-parent-li {if $sEvent=='settings-site' || $sEvent=='settings-lang' || $sEvent=='settings-blogtypes' || $sEvent=='settings-contenttypes' || $sEvent=='settings-userrights' || $sEvent=='settings-userrights' || $sEvent=='settings-userfields'}active{/if}">
      <a href="#">
      <i class="glyphicon glyphicon-cog"></i><span>{$aLang.action.admin.menu_settings}</span><i class="glyphicon glyphicon-chevron-right pull-right"></i>
      </a>
      <ul class="sub">
         <li class="{if $sEvent=='settings-site'}active{/if}">
            <a href="{router page="admin"}settings-site/">{$aLang.action.admin.menu_settings_site}</a>
         </li>
         <li class="{if $sEvent=='settings-lang'}active{/if}">
            <a href="{router page="admin"}settings-lang/">{$aLang.action.admin.menu_settings_lang}</a>
         </li>
         <li class="{if $sEvent=='settings-blogtypes'}active{/if}">
            <a href="{router page="admin"}settings-blogtypes/">{$aLang.action.admin.menu_settings_blogtypes}</a>
         </li>
         <li class="{if $sEvent=='settings-contenttypes'}active{/if}">
            <a href="{router page="admin"}settings-contenttypes/">{$aLang.action.admin.menu_settings_contenttypes}</a>
         </li>
         <li class="{if $sEvent=='settings-userrights'}active{/if}">
            <a href="{router page="admin"}settings-userrights/">{$aLang.action.admin.menu_settings_userrights}</a>
         </li>
         <li class="{if $sEvent=='settings-userfields'}active{/if}">
            <a href="{router page="admin"}settings-userfields/">{$aLang.action.admin.menu_settings_userfields}</a>
         </li>
         {hook run='admin_menu_settings'}
      </ul>
   </li>
   <li class="dcjq-parent-li {if $sEvent=='site-skins' || $sEvent=='site-widgets' || $sEvent=='site-plugins'}active{/if}">
      <a href="#">
      <i class="glyphicon glyphicon-globe"></i><span>{$aLang.action.admin.menu_site}</span><i class="glyphicon glyphicon-chevron-right pull-right"></i>
      </a>
      <ul class="sub">
         <li class="{if $sEvent=='site-skins'}active{/if}">
            <a href="{router page="admin"}site-skins/">{$aLang.action.admin.menu_site_skins}</a>
         </li>
         <li class="{if $sEvent=='site-widgets'}active{/if}">
            <a href="{router page="admin"}site-widgets/">{$aLang.action.admin.menu_widgets}</a>
         </li>
         <li class="{if $sEvent=='site-plugins'}active{/if}">
            <a href="{router page="admin"}site-plugins/">{$aLang.action.admin.menu_plugins}</a>
         </li>
         {hook run='admin_menu_site'}
      </ul>
   </li>
   <li class="dcjq-parent-li {if $sEvent=='logs-error' || $sEvent=='logs-sqlerror' || $sEvent=='logs-sqllog'}active{/if}">
      <a href="#">
      <i class="glyphicon glyphicon-book"></i><span>{$aLang.action.admin.menu_logs}</span><i class="glyphicon glyphicon-chevron-right pull-right"></i>
      </a>
      <ul class="sub">
         <li class="{if $sEvent=='logs-error'}active{/if}">
            <a href="{router page="admin"}logs-error/">{$aLang.action.admin.menu_logs_error}</a>
         </li>
         <li class="{if $sEvent=='logs-sqlerror'}active{/if}">
            <a href="{router page="admin"}logs-sqlerror/">{$aLang.action.admin.menu_logs_sqlerror}</a>
         </li>
         <li class="{if $sEvent=='logs-sqllog'}active{/if}">
            <a href="{router page="admin"}logs-sqllog/">{$aLang.action.admin.menu_logs_sqllog}</a>
         </li>
         {hook run='admin_menu_logs'}
      </ul>
   </li>
   <li class="dcjq-parent-li {if $sEvent=='tools-reset' || $sEvent=='tools-commentstree' || $sEvent=='tools-recalcfavourites' || $sEvent=='tools-recalcvotes' || $sEvent=='tools-recalctopics' || $sEvent=='tools-recalcblograting' || $sEvent=='tools-checkdb'}active{/if}">
      <a href="#">
      <i class="glyphicon glyphicon-wrench"></i><span>{$aLang.action.admin.menu_tools}</span><i class="glyphicon glyphicon-chevron-right pull-right"></i>
      </a>
      <ul class="sub">
         <li class="{if $sEvent=='tools-reset'}active{/if}">
            <a href="{router page="admin"}tools-reset/">{$aLang.action.admin.menu_tools_reset}</a>
         </li>
         {if Config::Get('module.comment.use_nested')}
         <li class="{if $sEvent=='tools-commentstree'}active{/if}">
            <a href="{router page="admin"}tools-commentstree/">{$aLang.action.admin.menu_tools_commentstree}</a>
         </li>
         {/if}
         <li class="{if $sEvent=='tools-recalcfavourites'}active{/if}">
            <a href="{router page="admin"}tools-recalcfavourites/">{$aLang.action.admin.menu_tools_recalcfavourites}</a>
         </li>
         <li class="{if $sEvent=='tools-recalcvotes'}active{/if}">
            <a href="{router page="admin"}tools-recalcvotes/">{$aLang.action.admin.menu_tools_recalcvotes}</a>
         </li>
         <li class="{if $sEvent=='tools-recalctopics'}active{/if}">
            <a href="{router page="admin"}tools-recalctopics/">{$aLang.action.admin.menu_tools_recalctopics}</a>
         </li>
         <li class="{if $sEvent=='tools-recalcblograting'}active{/if}">
            <a href="{router page="admin"}tools-recalcblograting/">{$aLang.action.admin.menu_tools_recalcblograting}</a>
         </li>
         <li class="{if $sEvent=='tools-checkdb'}active{/if}">
            <a href="{router page="admin"}tools-checkdb/">{$aLang.action.admin.menu_tools_checkdb}</a>
         </li>
         {hook run='admin_menu_items_end'}
      </ul>
   </li>
</ul>

</div>
</div>
</aside> <!-- /sidebar -->
{/block}