{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if $oField}
    {$oTopicField = $oTopic->getField($oField->getFieldId())}
    {if $oTopicField}
        <p>
            <strong>{$oField->getFieldName()}</strong>:
            <time datetime="{date_format date=$oTopicField->getValue() format='c'}"
                  title="{date_format date=$oTopicField->getValue() format='j F Y'}">
                {date_format date=$oTopicField->getValue() format="j F Y"}
            </time>
        </p>
    {/if}
{/if}