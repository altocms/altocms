 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if $oField}
    {$iFieldId=$oField->getFieldId()}
    <div class="form-group">
        <label for="properties-{$iFieldId}">{$oField->getFieldName()}</label>
        <textarea class="js-editor-wysiwyg js-editor-markitup form-control" name="fields[{$iFieldId}]"
                  id="properties-{$iFieldId}"
                  rows="5">{if $_aRequest.fields.$iFieldId}{$_aRequest.fields.$iFieldId}{elseif $oTopic AND $oTopic->getExtraField($iFieldId)}{$oTopic->getExtraField($iFieldId)}{/if}</textarea>
        <small class="control-notice">{$oField->getFieldDescription()}</small>
    </div>
{/if}

