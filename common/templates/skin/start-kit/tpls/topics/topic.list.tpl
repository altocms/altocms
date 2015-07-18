{if count($aTopics)>0}
    {wgroup_add group='toolbar' name='toolbar_topic.tpl' iCountTopic=count($aTopics)}

    {foreach $aTopics as $oTopic}
        {if E::Topic_IsAllowTopicType($oTopic->getType())}
            {$sTopicTemplateName=$oTopic->getTopicTypeTemplate('list')}
            {include file="topics/$sTopicTemplateName" bTopicList=true}
        {/if}
    {/foreach}

    {include file='commons/common.pagination.tpl' aPaging=$aPaging}
{else}
    <div class="alert alert-info">
        {$aLang.blog_no_topic}
    </div>
{/if}
