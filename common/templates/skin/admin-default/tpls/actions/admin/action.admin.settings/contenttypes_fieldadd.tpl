{extends file='_index.tpl'}

{block name="content-body"}

<div class="span12">

{* Поведение функции admin.onSelectField можно расширить с помощью
        хука admin_content_add_field_properties, добавив следующий код, например:

{literal}
<script>
$(function () {
    $('#field_type').off().on('change', function () {
        var selected = $(this).val();

        /* наш код */
        /*
        var myDiv = $('#my-type-id');

        myDiv.show();
        if ('mytype' == selected) {
            myDiv.show();
        }
        */

        return admin.onSelectField.apply(this);
    });
});
</script>
{/literal}
*}
{literal}
<script>
var admin = admin || {};
admin.onSelectField = function () {
    var selected = $(this).val(),
        daoobj = $('#daoobj_select'),
        inputval = $('#select_inputval')
    ;
    inputval.css({'display': 'none'});
    daoobj.css({'display': 'none'});

    //для типа выпадающий список
    if (selected == 'select') {
        inputval.css({'display': 'block'});
    }
    //для ДАОсвязей
    if (selected == 'daoobj') {
        daoobj.css({'display': 'block'});
    }

    return false;
};

$(function () {
    $('#field_type').on('change', admin.onSelectField);
});
</script>
{/literal}


<form action="" method="post" id="popup-login-form" class="form-horizontal uniform">
    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}" />
        {*<input type="hidden" name="topic_type" value="{$oContentType->getContentId()}"/>*}

    <div class="b-wbox">
        <div class="b-wbox-header">
            {if $sEvent=='settings-contenttypes-fieldadd'}
                <div class="b-wbox-header-title">
                    {$aLang.action.admin.contenttypes_add_field_title}
                    ({$aLang.action.admin.contenttypes_for} "{$oContentType->getContentTitle()|escape:'html'}")
                </div>
            {elseif $sEvent=='settings-contenttypes-fieldedit'}
                <div class="b-wbox-header-title">
                    {$aLang.action.admin.contenttypes_edit_field_title}: {$oField->getFieldName()|escape:'html'}
                    ({$aLang.action.admin.contenttypes_for} "{$oContentType->getContentTitle()|escape:'html'}")
                </div>
            {/if}
        </div>
        <div class="b-wbox-content nopadding">
            <div class="control-group">
                <label for="field_type" class="control-label">
                    {$aLang.action.admin.contenttypes_type}:
                </label>

                <div class="controls">
                    <select name="field_type" id="field_type" class="input-text input-width-300" {if $sEvent=='fieldedit'}disabled{/if}>
                        <option value="input" {if $_aRequest.field_type=='input'}selected{/if} title="{$aLang.action.admin.contenttypes_field_input_notice}">
                            {$aLang.action.admin.contenttypes_field_input}</option>
                        <option value="textarea" {if $_aRequest.field_type=='textarea'}selected{/if} title="{$aLang.action.admin.contenttypes_field_textarea_notice}">
                            {$aLang.action.admin.contenttypes_field_textarea}</option>
                        <option value="select" {if $_aRequest.field_type=='select'}selected{/if} title="{$aLang.action.admin.contenttypes_field_select_notice}">
                            {$aLang.action.admin.contenttypes_field_select}</option>
                        <option value="date" {if $_aRequest.field_type=='date'}selected{/if} title="{$aLang.action.admin.contenttypes_field_date_notice}">
                            {$aLang.action.admin.contenttypes_field_date}</option>
                        <option value="link" {if $_aRequest.field_type=='link'}selected{/if} title="{$aLang.action.admin.contenttypes_field_link_notice}">
                            {$aLang.action.admin.contenttypes_field_link}</option>
                        <option value="file" {if $_aRequest.field_type=='file'}selected{/if} title="{$aLang.action.admin.contenttypes_field_file_notice}">
                            {$aLang.action.admin.contenttypes_field_file}</option>
                        <option value="single-image-uploader" {if $_aRequest.field_type=='single-image-uploader'}selected{/if} title="{$aLang.action.admin.contenttypes_field_image_notice}">
                            {$aLang.action.admin.contenttypes_field_image}</option>
                        {hook run='admin_content_add_field_list'}
                    </select>
                </div>
            </div>

            <div class="control-group">
                <label for="field_name" class="control-label">
                    {$aLang.action.admin.contenttypes_name}:
                </label>

                <div class="controls">
                    <input type="text" name="field_name" value="{$_aRequest.field_name}" class="input-text">
                </div>
            </div>

            <div class="control-group">
                <label for="field_description" class="control-label">
                    {$aLang.action.admin.contenttypes_description}:
                </label>

                <div class="controls">
                    <input type="text" name="field_description" value="{$_aRequest.field_description}" class="input-text">
                </div>
            </div>

            <div class="control-group" {if !$_aRequest.field_type || $_aRequest.field_type!='select'}style="display:none;"{/if} id="select_inputval">
                <label for="field_description" class="control-label">
                    {$aLang.action.admin.contenttypes_values}:
                </label>

                <div class="controls">
                    <textarea name="field_values" id="field_values" class="input-text" rows="5">{$_aRequest.field_values}</textarea>
                </div>
            </div>

            <div {if !$_aRequest.field_type || $_aRequest.field_type!='daoobj'}style="display:none;"{/if} id="daoobj_select">
                {if $aPluginActive.dao}
                    {include file="`$aTemplatePathPlugin.dao`inject.topiccck.tpl"}
                {else}
                    {$aLang.action.admin.contenttypes_buydao}
                {/if}
            </div>

            {hook run='admin_content_add_field_properties'}

            <div class="form-actions">
                <button type="submit"  name="submit_field" class="btn btn-primary" id="popup-field-submit">
                    {$aLang.action.admin.contenttypes_submit}
                </button>
            </div>
        </div>
    </div>

    </form>


</div>

{/block}
