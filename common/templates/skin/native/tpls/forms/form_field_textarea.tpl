{if $oField}
    {assign var=field_id value=$oField->getFieldId()}
    <p><label for="properties-{$oField->getFieldId()}">{$oField->getFieldName()}:</label>

        <textarea class="mce-editor markitup-editor input-width-full" name="fields[{$oField->getFieldId()}]"
                  id="properties-{$oField->getFieldId()}"
                  rows="5">{if $_aRequest.fields.$field_id}{$_aRequest.fields.$field_id}{elseif $oTopic && $oTopic->getExtraField($oField->getFieldId())}{$oTopic->getExtraField($oField->getFieldId())}{/if}</textarea>
        <span class="note">{$oField->getFieldDescription()}</span>
    </p>
{/if}