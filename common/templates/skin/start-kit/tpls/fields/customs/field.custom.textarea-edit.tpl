{if $oField}
    {$iFieldId=$oField->getFieldId()}
    <div class="form-group">
        <label for="properties-{$iFieldId}">{$oField->getFieldName()}</label>

        <textarea class="js-editor-wysiwyg js-editor-markitup form-control" name="fields[{$iFieldId}]"
                  id="properties-{$iFieldId}"
                  rows="5">{if $_aRequest.fields.$iFieldId}{$_aRequest.fields.$iFieldId}{elseif $oTopic AND $oTopic->getExtraField($iFieldId)}{$oTopic->getExtraField($iFieldId)}{/if}</textarea>

        <p class="help-block">
            <small class="note">{$oField->getFieldDescription()}</small>
        </p>
    </div>
{/if}