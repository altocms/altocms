{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="#" class="btn btn-primary tip-top" onclick="ls.userfield.showAddForm(); return false;"
           title="{$aLang.action.admin.user_field_add}"><i class="icon icon-plus-sign"></i></a>
    </div>
{/block}

{block name="content-body"}

<div class="span12">
    <div class="b-modal" id="userfield_form">
        <header class="b-modal-header">
            <button type="button" class="b-modal-close" data-type="modal-close" data-dismiss="b-modal" aria-hidden="true">&times;</button>
            <h3 class="b-modal-title">{$aLang.action.admin.user_field_admin_title_add}</h3>
        </header>

        <form class="b-modal-content uniform">
            <p><label for="user_fields_form_type">{$aLang.action.admin.userfield_form_type}:</label>
                <select id="user_fields_form_type" class="input-text input-width-full">
                    <option value=""></option>
                    {foreach from=$aUserFieldTypes item=sFieldType}
                        <option value="{$sFieldType}">{$sFieldType}</option>
                    {/foreach}
                </select></p>

            <p><label for="user_fields_form_name">{$aLang.action.admin.userfield_form_name}:</label>
                <input type="text" id="user_fields_form_name" class="input-text input-width-full"/></p>

            <p><label for="user_fields_form_title">{$aLang.action.admin.userfield_form_title}:</label>
                <input type="text" id="user_fields_form_title" class="input-text input-width-full"/></p>

            <p><label for="user_fields_form_pattern">{$aLang.action.admin.userfield_form_pattern}:</label>
                <input type="text" id="user_fields_form_pattern" class="input-text input-width-full"/></p>

            <input type="hidden" id="user_fields_form_action"/>
            <input type="hidden" id="user_fields_form_id"/>

            <button type="button" onclick="ls.userfield.applyForm(); return false;"
                    class="btn btn-primary">{$aLang.action.admin.user_field_add}</button>
        </form>
    </div>

    <div class="b-wbox">
        <div class="b-wbox-content nopadding">
            <table class="table userfields-list" id="user_field_list">
                <thead>
                <tr>
                    <th>{$aLang.action.admin.userfield_form_name}</th>
                    <th>{$aLang.action.admin.userfield_form_title}</th>
                    <th>{$aLang.action.admin.userfield_form_type}</th>
                    <th>{$aLang.action1.admin.userfield_form_pattern}</th>
                    <th></th>
                </tr>
                </thead>
                {foreach from=$aUserFields item=oField}
                    <tr id="field_{$oField->getId()}">
                        <td class="userfield_admin_name">{$oField->getName()|escape:"html"}</td>
                        <td class="userfield_admin_title">{$oField->getTitle()|escape:"html"}</td>
                        <td class="userfield_admin_type">{$oField->getType()|escape:"html"}</td>
                        <td class="userfield_admin_pattern">{$oField->getPattern()|escape:"html"}</td>

                        <td class="userfield-actions">
                            <a href="javascript:ls.userfield.showEditForm('{$oField->getId()}')"
                               title="{$aLang.action.admin.user_field_update}"
                               class="icon icon-edit"></a>
                            <a href="javascript:ls.userfield.deleteUserfield('{$oField->getId()}')"
                               title="{$aLang.action.admin.user_field_delete}" class="icon icon-remove"></a>
                        </td>
                    </tr>
                {/foreach}
            </table>
        </div>
    </div>
</div>

{/block}
