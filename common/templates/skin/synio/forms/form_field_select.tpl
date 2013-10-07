{if $oField}
	{assign var=field_id value=$oField->getFieldId()}

	<p><label for="properties-{$oField->getFieldId()}">{$oField->getFieldName()}:</label>
	<select name="fields[{$oField->getFieldId()}]" id="properties-{$oField->getFieldId()}" class="input-text input-width-300">
		{foreach from=$oField->getSelectVal() item=sValue}
		<option value="{$sValue}" {if $_aRequest.fields.$field_id==$sValue || ($oTopicEdit && $oTopicEdit->getExtraValueCck($oField->getFieldId()))}selected{/if}>{$sValue}</option>
		{/foreach}
	</select>
	<span class="note">{$oField->getFieldDescription()}</span>
	</p>
{/if}