{if $oField}
    {$oTopicField = $oTopic->getField($oField->getFieldId())}
    <p>
        <strong>{$oField->getFieldName()}</strong>:
        {if $oTopicField}
            <time datetime="{date_format date=$oTopicField->getValue() format='c'}" title="{date_format date=$oTopicField->getValue() format='j F Y'}">
                {date_format date=$oTopicField->getValue() format="j F Y"}
            </time>
        {/if}
    </p>
{/if}