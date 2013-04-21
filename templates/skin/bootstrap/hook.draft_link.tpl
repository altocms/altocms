{if !$oTopic->getPublish() AND Config::Get('module.topic.draft_link')}
    <br/>
    <div class="topic-link">
        {$aLang.topic_draft_link}:
        <a href="{$oTopic->getDraftUrl()}">
            {$oTopic->getDraftUrl()}
        </a>
    </div>
{/if}