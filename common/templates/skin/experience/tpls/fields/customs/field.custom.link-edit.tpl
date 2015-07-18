 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if $oField}
    {$iFieldId=$oField->getFieldId()}
    <div class="form-group">
        <div class="input-group">
            <label class="input-group-addon" for="fields-{$iFieldId}">{$oField->getFieldName()}</label>
            <input class="form-control" name="fields[{$iFieldId}]" id="fields-{$iFieldId}"
                   value="{if $_aRequest.fields.$iFieldId}{$_aRequest.fields.$iFieldId}{/if}"/>
        </div>
        <small class="control-notice">{$oField->getFieldDescription()}</small>
    </div>
{/if}

