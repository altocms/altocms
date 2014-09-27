{if $oField}
    {$sVal = $oTopic->getField($oField->getFieldId())}
    {if $sVal}
        <p><b>{$oField->getFieldName()}</b>:
            {$sVal->getValue()}
        </p>
    {/if}
{/if}