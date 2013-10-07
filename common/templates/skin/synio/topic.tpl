{if E::Topic_IsAllowTopicType($oTopic->getType())}
	{assign var="sTopicTemplateName" value="topic_topic.tpl"}
	{include file=$sTopicTemplateName}
{/if}