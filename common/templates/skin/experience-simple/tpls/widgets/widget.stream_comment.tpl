 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if $aComments}
    <script>
        $(function(){
            $('.js-title-comment').tooltip();
            jQuery('.comment-info [data-alto-role="popover"]')
                    .altoPopover(false);
        })
    </script>
    {foreach $aComments as $oComment}
        {$oUser=$oComment->getUser()}
        {$oTopic=$oComment->getTarget()}
        {if !$oTopic}{continue}{/if}
        {*{$oBlog=$oTopic->getBlog()}*}
        <!-- Комментарий -->
        <div id="js-title-comment-{$oComment->getId()}" class="feed-comment js-title-comment"
             data-placement="left"
             data-container="body"
             data-original-title="{$oComment->getText()|strip_tags|trim|truncate:100:'...'|escape:'html'}">
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
                    <span class="date">{$oComment->getDate()|date_format:'d.m.y'}</span>
                    <span class="time">{$oComment->getDate()|date_format:'H:i'}</span>
                </li>
            </ul>
            <div class="feed-comment-text">
                <a href="{if Config::Get('module.comment.nested_per_page')}{router page='comments'}{else}{$oTopic->getUrl()}#comment{/if}{$oComment->getId()}"
                   class="stream-topic link">{$oTopic->getTitle()|escape:'html'}</a>
                {*<span class="text-muted"> - <i class="fa fa-comments-o"></i>{$oTopic->getCountComment()}</span>*}
            </div>
        </div>
    {/foreach}
{else}
    <div class="bg-warning">
        {$aLang.widget_stream_comments_no}
    </div>
{/if}


