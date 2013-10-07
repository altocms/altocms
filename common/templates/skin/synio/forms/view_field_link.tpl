{if $oField}
	<p><b>{$oField->getFieldName()}</b>:
		<a href="{$oTopic->getLink($oField->getFieldId(),true)|escape:'html'}" rel="nofollow">{$oTopic->getLink($oField->getFieldId())|escape:'html'}</a>
	</p>
{/if}