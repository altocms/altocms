 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<div class="topic-url">
    <p><b>{$aLang.topic_link_create_url}:</b>
        <a href="{router page='content'}go/{$oTopic->getId()}/"
           title="{$aLang.topic_link_count_jump}: {$oTopic->getSourceLinkCountJump()}">{$oTopic->getSourceLink()}</a>
        <span class="small muted">({$oTopic->getSourceLinkCountJump()} {$oTopic->getSourceLinkCountJump()|declension:$aLang.link_jump_declesion:$sLang})</span>
    </p>
</div>
