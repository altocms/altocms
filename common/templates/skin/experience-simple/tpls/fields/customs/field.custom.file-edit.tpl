 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if $oField}
    {$iFieldId=$oField->getFieldId()}
    <div class="info-container"><i class="fa fa-info-circle pull-right js-title-topic" data-original-title="{$oField->getFieldDescription()}"></i></div>
    <div class="form-group">
        {if $_aRequest.fields.$iFieldId}
            <label for="topic_delete_file_{$iFieldId}">
                <input type="checkbox" id="topic_delete_file_{$iFieldId}"
                       name="topic_delete_file_{$iFieldId}"
                       class="topic_delete_file"
                       value="on"> &mdash; {$aLang.content_delete_file}
                ({$_aRequest.fields.$iFieldId.file_name|escape:'html'})
            </label>
            <input type="hidden" name="fields[{$iFieldId}]" id="fields-{$iFieldId}" value="{$iFieldId}"/>
        {else}
            <div class="input-group">
                <label class="input-group-addon" for="fields-{$iFieldId}">{$oField->getFieldName()}{if $_aRequest.fields.$iFieldId} ({$aLang.content_file_replace}){/if}</label>
                <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                    <div class="form-control" data-trigger="fileinput">
                        <i class="fa fa-file fileinput-exists"></i>
                        <span class="fileinput-filename"></span>
                    </div>
                <span class="input-group-addon btn btn-default btn-file">
                    <span class="fileinput-new">{$aLang.select}</span>
                    <span class="fileinput-exists">{$aLang.change}</span>
                    <input class="form-control" type="file" name="fields_{$iFieldId}" id="fields-{$iFieldId}">
                </span>
                    <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">{$aLang.remove}</a>
                </div>
            </div>
        {/if}
    </div>
{/if}

