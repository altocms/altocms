 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if $aComments}
    <script>
        $(function(){
            $('.js-title-comment').tooltip();
            $.altoPopoverReset('.js-widget-stream-content');
        })
    </script>
    {foreach $aComments as $oComment}
        {$oUser=$oComment->getUser()}
        {$oTopic=$oComment->getTarget()}
        {$oBlog=$oTopic->getBlog()}
        <!-- Комментарий -->
        <div id="js-title-comment-{$oComment->getId()}" class="feed-comment js-title-comment"
             data-placement="left"
             data-container="body"
             data-original-title="{$oComment->getText()|strip_tags|trim|truncate:100:'...'|escape:'html'}">
            <ul>
                <li data-alto-role="popover"
                    data-api="user/{$oUser->getId()}/info"
                    class="user-block">
                    <img src="{$oUser->getAvatarUrl('small')}" alt="{$oUser->getDisplayName()}" class="user-avatar"/>
                    <a class="userlogo link link-dual link-lead link-clear" href="{$oUser->getProfileUrl()}">
                        {$oUser->getDisplayName()}
                    </a>
                </li>
                <li class="date-block">
                    <span class="date">{$oComment->getDate()|date_format:'d.m.Y'}</span>
                    <span class="time">{$oComment->getDate()|date_format:'H:i'}</span>
                </li>
            </ul>
            <div class="feed-comment-text">
                <a href="{if Config::Get('module.comment.nested_per_page')}{router page='comments'}{else}{$oTopic->getUrl()}#comment{/if}{$oComment->getId()}"
                   class="stream-topic link">{$oTopic->getTitle()|escape:'html'}</a>
                <span class="text-muted"> - <i class="fa fa-comments-o"></i>{$oTopic->getCountComment()}</span>
            </div>
        </div>
    {/foreach}
{else}
    <div class="bg-warning">
        {$aLang.widget_stream_comments_no}
    </div>
{/if}


