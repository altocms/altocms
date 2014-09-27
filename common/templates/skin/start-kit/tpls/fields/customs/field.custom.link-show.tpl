{if $oField}111
    {$sVal = $oTopic->getFieldLink($oField->getFieldId(), true)}222
    {if $sVal}333
        <p><b>{$oField->getFieldName()}</b>:
            <a href="{$sVal|escape:'html'}" rel="nofollow">{$sVal|escape:'html'}</a>
        </p>
    {/if}
{/if}