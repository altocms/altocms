{if $oField}
    {$iFieldId=$oField->getFieldId()}
    <div class="form-group">

        <label for="properties-{$iFieldId}">{$oField->getFieldName()}</label>

        <input class="date-picker" readonly="readonly" name="fields[{$iFieldId}]"
               id="properties-{$iFieldId}"
               value="{if $_aRequest.fields.$iFieldId}{$_aRequest.fields.$iFieldId}{/if}"/>

        <p class="help-block">
            <small class="note">{$oField->getFieldDescription()}</small>
        </p>
    </div>
{/if}