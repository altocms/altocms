{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if $oTopic AND E::Topic_IsAllowTopicType($oTopic->getType())}
    {$sTopicTemplateName=$oTopic->getTopicTypeTemplate('edit')}
{elseif $oContentType AND E::Topic_IsAllowTopicType($oContentType->getContentUrl())}
    {$sTopicTemplateName=$oContentType->getTemplate('edit')}
{else}
    {$sTopicTemplateName='topic.type_default-edit.tpl'}
{/if}
{include file="topics/$sTopicTemplateName"}
