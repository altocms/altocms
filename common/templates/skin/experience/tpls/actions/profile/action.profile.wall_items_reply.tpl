 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{foreach $aReplyWall as $oReplyWall}
    {$oReplyUser=$oReplyWall->getUser()}
    <div id="wall-reply-item-{$oReplyWall->getId()}" class="js-wall-reply-item comment wall-comment-reply wall-level-1">
        <a class="wall-user-logo js-popover-user-{$oReplyUser->getId()}" href="{$oReplyUser->getProfileUrl()}">
            <img src="{$oReplyUser->getAvatarUrl('medium')}" {$oReplyUser->getAvatarImageSizeAttr('medium')} alt="user-logo"/>
        </a>

        <div class="wall-content">
            <div class="wall-header">
                <a href="{$oReplyUser->getProfileUrl()}" class="link link-dual link-lead wall-username">{$oReplyUser->getDisplayName()}</a>
                <span class="wall-date">{$oReplyWall->getDateAdd()|date_format:'d.m.Y'}, {$oReplyWall->getDateAdd()|date_format:'H:i'}</span>
            </div>
            <div class="wall-text">
                {$oReplyWall->getText()}
            </div>
            <div class="wall-footer">
                {if $oReplyWall->isAllowDelete()}
                    <ul class="list-unstyled list-inline small comment-actions">
                        <li><a href="#" onclick="return ls.wall.remove({$oReplyWall->getId()});"
                               class="link link-lead link-red-blue link-clear comment-delete">{$aLang.wall_action_delete}</a></li>
                    </ul>
                {/if}
            </div>
        </div>
    </div>
{/foreach}
