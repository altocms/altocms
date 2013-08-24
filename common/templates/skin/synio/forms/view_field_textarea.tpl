{if $oField}
	<p><b>{$oField->getFieldName()}</b>:
		{$oTopic->getField($oField->getFieldId())->getValue()}
	</p>
{/if}