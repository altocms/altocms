{if $oType}
    {foreach from=$oType->getFields() item=oField}
        {include file="forms/form_field_`$oField->getFieldType()`.tpl" oField=$oField}
    {/foreach}
{/if}