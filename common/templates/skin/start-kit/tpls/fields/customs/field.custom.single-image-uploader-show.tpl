{if $oField}
    {$sVal = $oTopic->getSingleImage($oField->getFieldId(), '180fit')}
    {if $sVal}
        <p>
            <img src="{$sVal}" alt="image"/>
        </p>
    {/if}
{/if}