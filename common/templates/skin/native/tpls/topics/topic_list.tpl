{**
 * Список топиков
 *}

{if $aTopics}
    {wgroup_add name='toolbar' widget='toolbar.topic.tpl' iCountTopic=count($aTopics)}

    {foreach $aTopics as $oTopic}
        {if E::Topic_IsAllowTopicType($oTopic->getType())}
            {include file="topics/topic.topic.tpl" bTopicList=true}
        {/if}
    {/foreach}

    {include file='pagination.tpl' aPaging=$aPaging}
{else}
    {$aLang.blog_no_topic}
{/if}
