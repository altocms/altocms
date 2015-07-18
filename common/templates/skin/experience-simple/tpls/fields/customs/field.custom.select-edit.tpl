 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if $oField}
    {$iFieldId=$oField->getFieldId()}
    <div class="info-container"><i class="fa fa-info-circle pull-right js-title-topic" data-original-title="{$oField->getFieldDescription()}"></i></div>
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
    </div>
{/if}