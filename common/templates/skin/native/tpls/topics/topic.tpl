{**
 * Топик
 *}

{if E::Topic_IsAllowTopicType($oTopic->getType())}
	{include file="topics/topic.topic.tpl"}
{/if}