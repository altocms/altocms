{if $oField}
    {$sVal = $oTopic->getFieldLink($oField->getFieldId(), true)}
    {if $sVal}
        <p>
            <strong>{$oField->getFieldName()}</strong>:
            <a href="{$sVal|escape:'html'}" rel="nofollow">{$sVal|escape:'html'}</a>
        </p>
    {/if}
{/if}