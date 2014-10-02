{if $oTopic AND E::Topic_IsAllowTopicType($oTopic->getType())}
    {$sTopicTemplateName=$oTopic->getTopicTypeTemplate('edit')}
{elseif $oContentType AND E::Topic_IsAllowTopicType($oContentType->getContentUrl())}
    {$sTopicTemplateName=$oContentType->getTemplate('edit')}
{else}
    {$sTopicTemplateName='topic.type_default-edit.tpl'}
{/if}
{include file="topics/$sTopicTemplateName"}
