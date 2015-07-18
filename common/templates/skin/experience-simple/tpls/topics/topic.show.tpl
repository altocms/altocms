 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if E::Topic_IsAllowTopicType($oTopic->getType())}
    {$sTopicTemplateName=$oTopic->getTopicTypeTemplate('show')}
{else}
    {$sTopicTemplateName='topic.type_default-show.tpl'}
{/if}
{include file="topics/$sTopicTemplateName" bTopicList=true}
