 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if $oField}
    {$sVal = $oTopic->getField($oField->getFieldId())}
    {if $sVal}
	<p><b>{$oField->getFieldName()}</b>:
		{$sVal->getValue()}
	</p>
    {/if}
{/if}