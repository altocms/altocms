 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if $oField}
    {$iFieldId=$oField->getFieldId()}
    <div class="form-group">
        <div class="input-group">
            <label class="input-group-addon" for="fields-{$iFieldId}">{$oField->getFieldName()}</label>
            <select name="fields[{$iFieldId}]" id="properties-{$iFieldId}" class="form-control">
                {foreach from=$oField->getSelectVal() item=sValue}
                    <option value="{$sValue}" {if $_aRequest.fields.$iFieldId==$sValue OR ($oTopicEdit && $oTopicEdit->getExtraValueCck($iFieldId))}selected{/if}>
                        {$sValue}
                    </option>
                {/foreach}
            </select>
        </div>
        <small class="control-notice">{$oField->getFieldDescription()}</small>
    </div>
{/if}