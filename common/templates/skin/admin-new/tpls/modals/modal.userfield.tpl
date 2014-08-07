<div class="modal fade in" id="modal-userfield">
    <div class="modal-dialog">
        <div class="modal-content">

            <header class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h3 class="modal-title">{$aLang.action.admin.user_field_admin_title_add}</h3>
            </header>

            <form class="modal-body">
                <p><label for="user_fields_form_type">{$aLang.action.admin.userfield_form_type}:</label>
                    <select id="user_fields_form_type" class="input-text input-wide">
                        <option value=""></option>
                        {foreach from=$aUserFieldTypes item=sFieldType}
                            <option value="{$sFieldType}">{$sFieldType}</option>
                        {/foreach}
                    </select></p>

                <p><label for="user_fields_form_name">{$aLang.action.admin.userfield_form_name}:</label>
                    <input type="text" id="user_fields_form_name" class="input-text input-wide"/></p>

                <p><label for="user_fields_form_title">{$aLang.action.admin.userfield_form_title}:</label>
                    <input type="text" id="user_fields_form_title" class="input-text input-wide"/></p>

                <p><label for="user_fields_form_pattern">{$aLang.action.admin.userfield_form_pattern}:</label>
                    <input type="text" id="user_fields_form_pattern" class="input-text input-wide"/></p>

                <input type="hidden" id="user_fields_form_action"/>
                <input type="hidden" id="user_fields_form_id"/>

                <button type="button" onclick="ls.userfield.applyForm(); return false;"
                        class="btn btn-primary">{$aLang.action.admin.user_field_add}</button>
            </form>
        </div>
    </div>
</div>
