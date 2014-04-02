{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="#" class="btn btn-primary tip-top" onclick="ls.userfield.addUserfieldDialog(); return false;"
           title="{$aLang.action.admin.user_field_add}"><i class="icon icon-plus"></i></a>
    </div>
{/block}

{block name="layout_body" prepend}
    {include file="modals/modal.userfield.tpl"}
{/block}

{block name="content-body"}

<div class="span12">

    <div class="b-wbox">
        <div class="b-wbox-content nopadding">
            <table class="table userfields-list">
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
                               class="icon icon-note"></a>
                            <a href="#" onclick="return ls.userfield.deleteUserfield('{$oField->getId()}')"
                               title="{$aLang.action.admin.user_field_delete}" class="icon icon-trash"></a>
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
                           class="icon icon-note"></a>
                        <a href="#" onclick="return ls.userfield.deleteUserfield('ID')"
                           title="{$aLang.action.admin.user_field_delete}" class="icon icon-trash"></a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{/block}
