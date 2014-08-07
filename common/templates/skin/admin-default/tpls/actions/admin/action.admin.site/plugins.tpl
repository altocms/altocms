{extends file='_index.tpl'}
{block name="content-bar"}
<div class="col-md-12">
  <a href="{router page='admin'}site-plugins/add/" class="btn btn-primary pull-right"
    title="{$aLang.action.admin.plugin_load}"><i class="ion-plus-round"></i></a>
  <ul class="nav nav-tabs atlass">
    <li class="{if $sMode=='all' || $sMode==''}active{/if}">
      <a href="{router page='admin'}site-plugins/list/all/">
      {$aLang.action.admin.all_plugins}
      </a>
    </li>
    <li class="{if $sMode=='active'}active{/if}">
      <a href="{router page='admin'}site-plugins/list/active/">
      {$aLang.action.admin.active_plugins}
      </a>
    </li>
    <li class="{if $sMode=='inactive'}active{/if}">
      <a href="{router page='admin'}site-plugins/list/inactive/">
      {$aLang.action.admin.inactive_plugins}
      </a>
    </li>
  </ul>
</div>
{/block}
{block name="content-body"}
<div class="col-md-12">
  <form action="{router page='admin'}site-plugins/" method="post" id="form_plugins_list" class="uniform">
    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
    <div class="panel panel-default">
      <div class="panel-body no-padding">
        <div class="table table-striped-responsive"><table class="table table-striped">
          <thead>
            <tr>
              <th>
                <input type="checkbox" name="" onclick="admin.selectAllRows(this);"/>
              </th>
              <th class="name sorting">{$aLang.action.admin.plugin_name}</th>
              <th class="version sorting">{$aLang.action.admin.plugin_version}</th>
              <th class="author sorting">{$aLang.action.admin.plugin_author}</th>
              <th class="action sorting">{$aLang.action.admin.plugin_action}</th>
              <th class="sorting">{$aLang.action.admin.menu_settings}</th>
            </tr>
          </thead>
          <tbody>
            {foreach $aPluginList as $oPlugin}
            <tr id="plugin-{$oPlugin->GetId()}"
              class="{if $oPlugin->IsActive()}success{else}inactive{/if} selectable">
              <td class="check-row">
                <input type="checkbox" name="plugin_sel[]" value="{$oPlugin->GetId()}"
                  class="form_plugins_checkbox"/>
              </td>
              <td class="name">
                <div class="i-title">{$oPlugin->GetName()|escape:'html'}</div>
                <div class="description">
                  <b>{$oPlugin->GetId()}</b> - {$oPlugin->GetDescription()}
                </div>
                {if ($oPlugin->GetHomepage()>'')}
                <div class="url">
                  Homepage: {$oPlugin->GetHomepage()}
                </div>
                {/if}
              </td>
              <td class="version">{$oPlugin->GetVersion()|escape:'html'}</td>
              <td class="author">{$oPlugin->GetAuthor()|escape:'html'}</td>
              <td class="action">
                <div class="b-switch"
                  onchange="admin.plugin.turn('{$oPlugin->GetId()}', '{!$oPlugin->isActive()}'); return false;">
                  <input type="checkbox" {if $oPlugin->isActive()}checked{/if}
                  name="b-switch-{$oPlugin->GetId()}">
                  <label for="b-switch-{$oPlugin->GetId()}"></label>
                </div>
              </td>
              <td class="center">
                {if $oPlugin->isActive() AND $oPlugin->GetProperty('settings') != ''}
                <a href="{$oPlugin->GetProperty('settings')}">{$aLang.plugins_plugin_settings}</a>
                {/if}
              </td>
            </tr>
            {/foreach}
          </tbody>
        </table></div>
        <!-- <br/> {$aLang.action.admin.plugin_priority_notice} -->
        <!-- <input type="submit" name="submit_plugins_save" value="{$aLang.adm_save}" onclick="adminPluginSave();" /> -->
      </div>
      <input type="hidden" name="plugin_action" value="">
      <div class="panel-footer clearfix">
        <button type="submit" name="submit_plugins_del" class="btn btn-danger pull-right"
          onclick="admin.confirmDelete(); return false;">
        {$aLang.action.admin.plugin_submit_delete}
        </button>
      </div>
    </div>
  </form>
  <script>
    var admin = admin || { };
    
    admin.confirmDelete = function () {
        if ($('.form_plugins_checkbox:checked').length) {
            ls.modal.confirm({
                title: '{$aLang.action.admin.plugin_submit_delete}',
                message: '{$aLang.action.admin.plugin_delete_confirm}',
                onConfirm: function () {
                    $('#form_plugins_list [name=plugin_action]').val('delete');
                    $('#form_plugins_list').submit();
                }
            });
        } else {
            ls.modal.alert({
                content: '{$aLang.action.admin.plugin_need_select_for_delete}'
            });
        }
    
    }
  </script>
</div>
{/block}