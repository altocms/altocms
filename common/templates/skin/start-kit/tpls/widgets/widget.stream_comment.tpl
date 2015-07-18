{if $aComments}
    <ul class="list-unstyled item-list">
        {foreach $aComments as $oComment}
            {$oUser=$oComment->getUser()}
            {$oTopic=$oComment->getTarget()}
            {$oBlog=$oTopic->getBlog()}
            <li class="js-title-comment" title="{$oComment->getText()|strip_tags|trim|truncate:100:'...'|escape:'html'}">
                <p>
                    <a href="{$oUser->getProfileUrl()}" class="author">{$oUser->getDisplayName()}</a>
                    <time datetime="{date_format date=$oComment->getDate() format='c'}" class="text-muted">
                        · {date_format date=$oComment->getDate() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}
                    </time>
                </p>
                <a href="{if Config::Get('module.comment.nested_per_page')}{router page='comments'}{else}{$oTopic->getUrl()}#comment{/if}{$oComment->getId()}"
                   class="stream-topic">{$oTopic->getTitle()|escape:'html'}</a>
                <span class="stream-topic text-danger">{$oTopic->getCountComment()}</span>
            </li>
        {/foreach}
    </ul>
{else}
    {$aLang.widget_stream_comments_no}
{/if}

<footer class="small text-muted">
    <a href="{router page='comments'}">{$aLang.widget_stream_comments_all}</a> ·
    <a href="{router page='rss'}allcomments/">RSS</a>
</footer>
