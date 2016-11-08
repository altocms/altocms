{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="{router page='admin'}site-scripts/add/" class="btn btn-primary tip-top"
           title="{$aLang.action.admin.scripts_add}"><i class="icon icon-plus"></i></a>
    </div>
    <div class="btn-group">
        <a class="btn btn-default {if $sMode=='all' || $sMode==''}active{/if}" href="{router page='admin'}site-scripts/list/all/">
            {$aLang.action.admin.scripts_sub_all}
        </a>
        <a class="btn btn-default {if $sMode=='active'}active{/if}" href="{router page='admin'}site-scripts/list/active/">
            {$aLang.action.admin.scripts_sub_active}
        </a>
        <a class="btn btn-default {if $sMode=='inactive'}active{/if}" href="{router page='admin'}site-scripts/list/inactive/">
            {$aLang.action.admin.scripts_sub_inactive}
        </a>
    </div>
{/block}

{block name="content-body"}
    <form action="{router page='admin'}site-scripts/" method="post" id="form_scripts_list" class="uniform">
        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>

        <div class="b-wbox">
            <div class="b-wbox-content nopadding">

                <table class="table scripts-list">
                    <thead>
                    <tr>
                        <th>
                            <input type="checkbox" name="" onclick="admin.selectAllRows(this);"/>
                        </th>
                        <th>ID</th>
                        <th class="name">{$aLang.action.admin.script_edit_name}</th>
                        <th>{$aLang.action.admin.script_edit_description}</th>
                        <th>{$aLang.action.admin.script_edit_place}</th>
                        <th>{$aLang.action.admin.script_edit_code}</th>
                        <th colspan="2"></th>
                    </tr>
                    </thead>

                    <tbody>
                    {foreach $aScripts as $sScriptId => $aScript}
                        <tr id="script-{$sScriptId}"
                            class="{if !$aScript.disable}success{else}inactive{/if} selectable">
                            <td class="check-row">
                                <input type="checkbox" name="script_sel[]" value="{$sScriptId}"  class="form_scripts_checkbox"/>
                            </td>
                            <td class="name">
                                {$aScript.id}
                            </td>
                            <td class="name">
                                {$aScript.name|escape:'html'}
                            </td>
                            <td class="name">
                                {$aScript.description|escape:'html'}
                            </td>
                            <td class="name">
                                {$aScript.place}
                            </td>
                            <td class="source">
                                <a href="#" onclick="admin.showCode(this, '{$sScriptId}'); return false;">{$aLang.action.admin.script_edit_show_code}</a>
                                <pre class="script-source" style="display: none;">{$aScript.code|escape:'html'}</pre>
                            </td>
                            <td class="action">
                                <div class="b-switch"
                                     onclick="admin.script.turn('{$sScriptId}', '{if $aScript.disable}activate{/if}'); return false;">
                                    <input type="checkbox" {if !$aScript.disable}checked{/if}
                                           name="b-switch-{$sScriptId}">
                                    <label><i></i></label>
                                </div>
                            </td>
                            <td class="center">
                                <a href="{router page='admin'}site-scripts/edit/{$sScriptId}/"
                                   title="{$aLang.action.admin.scripts_edit}">
                                    <i class="icon icon-note"></i></a>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>

        <input type="hidden" name="script_action" value="">

        <div class="navbar navbar-inner">
            <button type="submit" name="submit_scripts_del" class="btn btn-danger pull-right"
                    onclick="admin.confirmDelete(); return false;">
                {$aLang.action.admin.script_submit_delete}
            </button>
        </div>
    </form>

    <script>
        var admin = admin || { };

        admin.showCode = function(button, id) {
            $('#script-' + id + ' .script-source').show();
        };

        admin.confirmDelete = function () {
            if ($('.form_scripts_checkbox:checked').length) {
                ls.modal.confirm({
                    title: '{$aLang.action.admin.script_submit_delete}',
                    message: '{$aLang.action.admin.script_delete_confirm}',
                    onConfirm: function () {
                        $('#form_scripts_list [name=script_action]').val('delete');
                        $('#form_scripts_list').submit();
                    }
                });
            } else {
                ls.modal.alert({
                    content: '{$aLang.action.admin.script_need_select_for_delete}'
                });
            }

        }
    </script>
{/block}
