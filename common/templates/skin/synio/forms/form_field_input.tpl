{if $oField}
	{assign var=field_id value=$oField->getFieldId()}

	<p><label for="fields-{$oField->getFieldId()}">{$oField->getFieldName()}:</label>

	<input class="input-text input-width-full" name="fields[{$oField->getFieldId()}]" id="fields-{$oField->getFieldId()}" value="{if $_aRequest.fields.$field_id}{$_aRequest.fields.$field_id}{/if}"/>
	<span class="note">{$oField->getFieldDescription()}</span>
	</p>
{/if}