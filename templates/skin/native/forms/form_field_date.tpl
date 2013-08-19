{if $oField}
	{assign var=field_id value=$oField->getFieldId()}

	<p><label for="properties-{$oField->getFieldId()}">{$oField->getFieldName()}:</label>

	<input class="input-text date-picker" readonly="readonly" name="fields[{$oField->getFieldId()}]" id="properties-{$oField->getFieldId()}" value="{if $_aRequest.fields.$field_id}{$_aRequest.fields.$field_id}{/if}"/>
	<span class="note">{$oField->getFieldDescription()}</span>
	</p>
{/if}