{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="{router page='admin'}site-plugins/add/" class="btn btn-primary tip-top"
           title="{$aLang.action.admin.plugin_load}"><i class="icon icon-plus"></i></a>
    </div>
    <div class="btn-group">
        <a class="btn btn-default {if $sMode=='all' || $sMode==''}active{/if}" href="{router page='admin'}site-plugins/list/all/">
            {$aLang.action.admin.all_plugins}
        </a>
        <a class="btn btn-default {if $sMode=='active'}active{/if}" href="{router page='admin'}site-plugins/list/active/">
            {$aLang.action.admin.active_plugins}
        </a>
        <a class="btn btn-default {if $sMode=='inactive'}active{/if}" href="{router page='admin'}site-plugins/list/inactive/">
            {$aLang.action.admin.inactive_plugins}
        </a>
    </div>
{/block}

{block name="content-body"}
    <form action="{router page='admin'}site-plugins/" method="post" id="form_plugins_list" class="uniform">
        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>

        <div class="b-wbox">
            <div class="b-wbox-content nopadding">

                <table class="table plugins-list">
                    <thead>
                    <tr>
                        <th>
                            <input type="checkbox" name="" onclick="admin.selectAllRows(this);"/>
                        </th>
                        <th class="name">{$aLang.action.admin.plugin_name}</th>
                        <th class="version">{$aLang.action.admin.plugin_version}</th>
                        <th class="author">{$aLang.action.admin.plugin_author}</th>
                        <th class="action">{$aLang.action.admin.plugin_action}</th>
                        <th class="">{$aLang.action.admin.menu_settings}</th>
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
                                     onclick="admin.plugin.turn('{$oPlugin->GetId()}', '{!$oPlugin->isActive()}'); return false;">
                                    <input type="checkbox" {if $oPlugin->isActive()}checked{/if}
                                           name="b-switch-{$oPlugin->GetId()}">
                                    <label><i></i></label>
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
                </table>
                <!-- <br/> {$aLang.action.admin.plugin_priority_notice} -->
                <!-- <input type="submit" name="submit_plugins_save" value="{$aLang.adm_save}" onclick="adminPluginSave();" /> -->
            </div>
        </div>

        <input type="hidden" name="plugin_action" value="">

        <div class="navbar navbar-inner">
            <button type="submit" name="submit_plugins_del" class="btn btn-danger pull-right"
                    onclick="admin.confirmDelete(); return false;">
                {$aLang.action.admin.plugin_submit_delete}
            </button>
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
{/block}