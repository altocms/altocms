 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if $oField}
    {$sVal = $oTopic->getField($oField->getFieldId())}
    {if $sVal}
	<p><b>{$oField->getFieldName()}</b>:
		<time datetime="{date_format date=$oTopic->getField($oField->getFieldId())->getValue() format='c'}" title="{date_format date=$oTopic->getField($oField->getFieldId())->getValue() format='j F Y'}">
			{date_format date=$oTopic->getField($oField->getFieldId())->getValue() format="j F Y"}
		</time>
	</p>
    {/if}
{/if}