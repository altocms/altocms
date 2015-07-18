{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if $oField}
    {$oTopicField = $oTopic->getField($oField->getFieldId())}
    {if $oTopicField}
        <p>
            <strong>{$oField->getFieldName()}</strong>:
            {$oTopicField->getValue()}
        </p>
    {/if}
{/if}