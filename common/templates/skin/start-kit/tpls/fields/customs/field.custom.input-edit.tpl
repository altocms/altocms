{if $oField}
    {$iFieldId=$oField->getFieldId()}
    <div class="form-group">

        <label for="fields-{$iFieldId}">{$oField->getFieldName()}</label>

        <input class="form-control" name="fields[{$iFieldId}]" id="fields-{$iFieldId}"
               value="{if $_aRequest.fields.$iFieldId}{$_aRequest.fields.$iFieldId}{/if}"/>

        <p class="help-block">
            <small class="note">{$oField->getFieldDescription()}</small>
        </p>
    </div>
{/if}