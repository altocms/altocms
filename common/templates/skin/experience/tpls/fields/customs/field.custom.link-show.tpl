 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if $oField}
    {$sVal = $oTopic->getFieldLink($oField->getFieldId(), true)}
    {if $sVal}
        <p><b>{$oField->getFieldName()}</b>:
            <a href="{$sVal|escape:'html'}" rel="nofollow">{$sVal|escape:'html'}</a>
        </p>
    {/if}
{/if}