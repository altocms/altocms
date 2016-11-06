 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if $aTopics}
    <script>
        $(function(){
            $('.js-title-comment').tooltip();
            jQuery('.comment-info [data-alto-role="popover"]')
                    .altoPopover(false);
        })
    </script>
        {foreach $aTopics as $oTopic}
            {$oUser=$oTopic->getUser()}
            {$oBlog=$oTopic->getBlog()}

            <div id="js-title-comment-{$oTopic->getId()}" class="feed-topic js-title-comment"
                 data-placement="left"
                 data-container="body"
                 data-original-title="{$oTopic->getText()|strip_tags|trim|truncate:100:'...'|escape:'html'}">
                <ul class="comment-info">
                    <li data-alto-role="popover"
                        data-api="user/{$oUser->getId()}/info"
                        class="user-block">
                        <img src="{$oUser->getAvatarUrl('small')}" {$oUser->getAvatarImageSizeAttr('small')} alt="{$oUser->getDisplayName()}"/>
                        <a class="userlogo link link-dual link-lead link-clear mal0" href="{$oUser->getProfileUrl()}">
                            {$oUser->getDisplayName()}
                        </a>
                    </li>
                    <li class="date-block">
                        <span class="date">{$oTopic->getDate()|date_format:'d.m.y'}</span>
                        <span class="time">{$oTopic->getDate()|date_format:'H:i'}</span>
                    </li>
                </ul>
                <div class="feed-topic-text">
                    <a href="{$oBlog->getUrlFull()}" class="stream-topic blog-name">{$oBlog->getTitle()|escape:'html'}</a>,&nbsp;
                    <a href="{$oTopic->getUrl()}" class="stream-topic">{$oTopic->getTitle()|escape:'html'}</a>
                    <span class="stream-topic"> - <i class="fa fa-comments-o"></i>{$oTopic->getCountComment()}</span>
                </div>
            </div>
        {/foreach}

{else}
<div class="bg-warning">{$aLang.widget_stream_topics_no}</div>
{/if}

{*<footer class="small text-muted">*}
    {*<a href="{router page='index'}newall/">{$aLang.widget_stream_topics_all}</a> ·*}
    {*<a href="{router page='rss'}new/">RSS</a>*}
{*</footer>*}




