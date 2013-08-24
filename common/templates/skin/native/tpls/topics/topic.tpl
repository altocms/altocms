{**
 * Топик
 *}

{if $LS->Topic_IsAllowTopicType($oTopic->getType())}
	{include file="topics/topic.topic.tpl"}
{/if}