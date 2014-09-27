{if $oField}
	<p><b>{$oField->getFieldName()}</b>:
		<time datetime="{date_format date=$oTopic->getField($oField->getFieldId())->getValue() format='c'}" title="{date_format date=$oTopic->getField($oField->getFieldId())->getValue() format='j F Y'}">
			{date_format date=$oTopic->getField($oField->getFieldId())->getValue() format="j F Y"}
		</time>
	</p>
{/if}