{if $oField}
	{assign var=field_id value=$oField->getFieldId()}
	<p>
		{if $_aRequest.fields.$field_id}
			<label for="topic_delete_file_{$field_id}"><input type="checkbox" id="topic_delete_file_{$field_id}" name="topic_delete_file_{$field_id}" value="on"> &mdash; {$aLang.content_delete_file} ({$_aRequest.fields.$field_id.file_name|escape:'html'}) </label>
		{/if}
		<label for="topic_upload_file">{$oField->getFieldName()}{if $_aRequest.fields.$field_id} ({$aLang.content_file_replace}){/if}:</label>
		<input class="input-200" type="file" name="fields_{$oField->getFieldId()}" id="fields-{$oField->getFieldId()}"><br />
		<span class="note">{$oField->getFieldDescription()}</span>
	</p>
{/if}