 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if E::Topic_IsAllowTopicType($oTopic->getType())}
    {$sTopicTemplateName=$oTopic->getTopicTypeTemplate('show')}
{else}
    {$sTopicTemplateName='topic.type_default-show.tpl'}
{/if}
{include file="topics/$sTopicTemplateName" bTopicList=true}
