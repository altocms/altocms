 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if $oField}
    {$iFieldId=$oField->getFieldId()}
    <div class="info-container"><i class="fa fa-info-circle pull-right js-title-topic" data-original-title="{$oField->getFieldDescription()}"></i></div>
    <div class="form-group">
        <label for="properties-{$iFieldId}">{$oField->getFieldName()}</label>
        <textarea class="js-editor-wysiwyg js-editor-markitup form-control" name="fields[{$iFieldId}]"
                  id="properties-{$iFieldId}"
                  rows="5">{if $_aRequest.fields.$iFieldId}{$_aRequest.fields.$iFieldId}{elseif $oTopic AND $oTopic->getExtraField($iFieldId)}{$oTopic->getExtraField($iFieldId)}{/if}</textarea>
    </div>
{/if}

