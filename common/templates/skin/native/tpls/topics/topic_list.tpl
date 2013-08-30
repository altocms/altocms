{**
 * Список топиков
 *}

{if $aTopics}
	{add_block group='toolbar' name='toolbar/toolbar.topic.tpl' iCountTopic=count($aTopics)}

	{foreach $aTopics as $oTopic}
		{if E::Topic_IsAllowTopicType($oTopic->getType())}
			{include file="topics/topic.topic.tpl" bTopicList=true}
		{/if}
	{/foreach}

	{include file='pagination.tpl' aPaging=$aPaging}
{else}
	{$aLang.blog_no_topic}
{/if}