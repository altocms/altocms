{extends file='_index.tpl'}

{block name="content-bar"}
<div class="col-md-12 mb15">
    <a href="#" class="btn btn-primary" onclick="ls.userfield.addUserfieldDialog(); return false;" title="{$aLang.action.admin.user_field_add}"><i class="ion-plus-round"></i></a>
</div>
{/block}

{block name="layout_body" prepend}
    {include file="modals/modal.userfield.tpl"}
{/block}

{block name="content-body"}

<div class="col-md-12">

    <div class="panel panel-default">
        <div class="panel-body no-padding">
            <div class="table table-striped-responsive"><table class="table table-striped">
                <thead>
                <tr>
                    <th>{$aLang.action.admin.userfield_form_name}</th>
                    <th>{$aLang.action.admin.userfield_form_title}</th>
                    <th>{$aLang.action.admin.userfield_form_type}</th>
                    <th>{$aLang.action1.admin.userfield_form_pattern}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody id="user_field_list">
                {foreach from=$aUserFields item=oField}
                    <tr id="userfield_{$oField->getId()}">
                        <td class="userfield_admin_name">{$oField->getName()|escape:"html"}</td>
                        <td class="userfield_admin_title">{$oField->getTitle()|escape:"html"}</td>
                        <td class="userfield_admin_type">{$oField->getType()|escape:"html"}</td>
                        <td class="userfield_admin_pattern">{$oField->getPattern()|escape:"html"}</td>

                        <td class="userfield-actions">
                            <a href="#" onclick="return ls.userfield.updateUserfieldDialog('{$oField->getId()}')"
                               title="{$aLang.action.admin.user_field_update}"
                               class="ion-ios7-compose"></a>
                            <a href="#" onclick="return ls.userfield.deleteUserfield('{$oField->getId()}')"
                               title="{$aLang.action.admin.user_field_delete}" class="ion-ios7-trash"></a>
                        </td>
                    </tr>
                {/foreach}
                <tr id="userfield_ID" style="display: none;">
                    <td class="userfield_admin_name"></td>
                    <td class="userfield_admin_title"></td>
                    <td class="userfield_admin_type"></td>
                    <td class="userfield_admin_pattern"></td>

                    <td class="userfield-actions">
                        <a href="#" onclick="return ls.userfield.updateUserfieldDialog('ID')"
                           title="{$aLang.action.admin.user_field_update}"
                           class="ion-ios7-compose"></a>
                        <a href="#" onclick="return ls.userfield.deleteUserfield('ID')"
                           title="{$aLang.action.admin.user_field_delete}" class="ion-ios7-trash"></a>
                    </td>
                </tr>
                </tbody>
            </table></div>
        </div>
    </div>
</div>

{/block}
