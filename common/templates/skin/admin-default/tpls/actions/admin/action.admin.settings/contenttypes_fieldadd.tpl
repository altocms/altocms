{extends file='_index.tpl'}

{block name="content-body"}

<div class="col-md-12">

{literal}
<script>

function selectfield(f){
	$('#select_inputval').css({'display':'none'});
	$('#daoobj_select').css({'display':'none'});
	//для типа выпадающий список
	if(f=='select'){
		$('#select_inputval').css({'display':'block'});
	}
	//для ДАОсвязей
	if(f=='daoobj'){
		$('#daoobj_select').css({'display':'block'});
	}
	return false;
}
</script>
{/literal}


<form action="" method="post" id="popup-login-form" class="form-horizontal">
	<input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}" />
		{*<input type="hidden" name="topic_type" value="{$oContentType->getContentId()}"/>*}

    <div class="panel panel-default">
        <div class="panel-heading">
            {if $sEvent=='settings-contenttypes-fieldadd'}
                <div class="panel-title">
                    {$aLang.action.admin.contenttypes_add_field_title}
                    ({$aLang.action.admin.contenttypes_for} "{$oContentType->getContentTitle()|escape:'html'}")
                </div>
            {elseif $sEvent=='settings-contenttypes-fieldedit'}
                <div class="panel-title">
                    {$aLang.action.admin.contenttypes_edit_field_title}: {$oField->getFieldName()|escape:'html'}
                    ({$aLang.action.admin.contenttypes_for} "{$oContentType->getContentTitle()|escape:'html'}")
                </div>
            {/if}
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label for="field_type" class="col-sm-2 control-label">
                    {$aLang.action.admin.contenttypes_type}:
                </label>

                <div class="col-sm-10">
                    <select name="field_type" id="field_type" onChange="selectfield(jQuery(this).val());" class="input-text form-control input-width-300" {if $sEvent=='fieldedit'}disabled{/if}>
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
                        {hook run='admin_content_add_field_list'}
					</select>
                </div>
            </div>

            <div class="form-group">
                <label for="field_name" class="col-sm-2 control-label">
                    {$aLang.action.admin.contenttypes_name}:
                </label>

                <div class="col-sm-10">
                    <input type="text" name="field_name" value="{$_aRequest.field_name}" class="input-text form-control">
                </div>
            </div>

            <div class="form-group">
                <label for="field_description" class="col-sm-2 control-label">
                    {$aLang.action.admin.contenttypes_description}:
                </label>

                <div class="col-sm-10">
                    <input type="text" name="field_description" value="{$_aRequest.field_description}" class="input-text form-control">
                </div>
            </div>

            <div class="form-group" {if !$_aRequest.field_type || $_aRequest.field_type!='select'}style="display:none;"{/if} id="select_inputval">
                <label for="field_description" class="col-sm-2 control-label">
                    {$aLang.action.admin.contenttypes_values}:
                </label>

                <div class="col-sm-10">
                    <textarea name="field_values" id="field_values" class="input-text form-control" rows="5">{$_aRequest.field_values}</textarea>
                </div>
            </div>

            <div {if !$_aRequest.field_type || $_aRequest.field_type!='daoobj'}style="display:none;"{/if} id="daoobj_select">
                {if $aPluginActive.dao}
                    {include file="`$aTemplatePathPlugin.dao`inject.topiccck.tpl"}
                {else}
                    {$aLang.action.admin.contenttypes_buydao}
                {/if}
            </div>

            <div class="panel-footer clearfix">
                <button type="submit"  name="submit_field" class="btn btn-primary pull-right" id="popup-field-submit">
                    {$aLang.action.admin.contenttypes_submit}
                </button>
            </div>
        </div>
    </div>

	</form>


</div>

{/block}