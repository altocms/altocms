{if $oField}
    {$oTopicField = $oTopic->getField($oField->getFieldId())}
    <p>
        <strong>{$oField->getFieldName()}</strong>:
        {if $oTopicField}
            {$oTopicField->getValue()}
        {/if}
    </p>
{/if}