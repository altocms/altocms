{foreach $aReplyWall as $oReplyWall}
    {$oReplyUser=$oReplyWall->getUser()}
    <div id="wall-reply-item-{$oReplyWall->getId()}" class="js-wall-reply-item comment wall-comment-reply">
        <a href="{$oReplyUser->getProfileUrl()}" class="comment-avatar js-popover-user-{$oReplyUser->getId()}">
            <img src="{$oReplyUser->getAvatarUrl('medium')}" {$oReplyUser->getAvatarImageSizeAttr('medium')} alt="{$oReplyUser->getDisplayName()}" />
        </a>

        <ul class="list-unstyled list-inline small comment-info">
            <li class="comment-info-author"><a href="{$oReplyUser->getProfileUrl()}">{$oReplyUser->getDisplayName()}</a></li>
            <li class="comment-info-date">
                <time datetime="{date_format date=$oReplyWall->getDateAdd() format='c'}">{date_format date=$oReplyWall->getDateAdd() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}</time>
            </li>
        </ul>

        <div class="comment-content text">
            {$oReplyWall->getText()}
        </div>

        {if $oReplyWall->isAllowDelete()}
            <ul class="list-unstyled list-inline small comment-actions">
                <li><a href="#" onclick="return ls.wall.remove({$oReplyWall->getId()});"
                       class="link-dotted comment-delete">{$aLang.wall_action_delete}</a></li>
            </ul>
        {/if}
    </div>
{/foreach}
