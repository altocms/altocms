{if $oField}
    {$iFieldId=$oField->getFieldId()}
    <div class="form-group">

        <label for="properties-{$iFieldId}">{$oField->getFieldName()}</label>

        <select name="fields[{$iFieldId}]" id="properties-{$iFieldId}" class="form-control">
            {foreach from=$oField->getSelectVal() item=sValue}
                <option value="{$sValue}" {if $_aRequest.fields.$iFieldId==$sValue OR ($oTopicEdit && $oTopicEdit->getExtraValueCck($iFieldId))}selected{/if}>
                    {$sValue}
                </option>
            {/foreach}
        </select>

        <p class="help-block">
            <small class="note">{$oField->getFieldDescription()}</small>
        </p>
    </div>
{/if}