 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{foreach $aWall as $oWall}
    {$oWallUser=$oWall->getUser()}
    {$aReplyWall=$oWall->getLastReplyWall()}
    <div id="wall-item-{$oWall->getId()}" class="js-wall-item comment-wrapper">
        <div class="comment">
            <a class="wall-user-logo js-popover-user-{$oWallUser->getId()}" href="{$oWallUser->getProfileUrl()}">
                <img src="{$oWallUser->getAvatarUrl('medium')}" {$oWallUser->getAvatarImageSizeAttr('medium')} alt="user-logo"/>
            </a>

            <div class="wall-content">
                <div class="wall-header">
                    <a href="{$oWallUser->getProfileUrl()}" class="link link-dual link-lead wall-username">{$oWallUser->getDisplayName()}</a>
                    <span class="wall-date">{$oWall->getDateAdd()|date_format:'d.m.Y'}, {$oWall->getDateAdd()|date_format:'H:i'}</span>
                </div>
                <div class="wall-text">
                    {$oWall->getText()}
                </div>
                <div class="wall-footer">
                    <a class="link link-lead link-blue link-clear"
                       onclick="return ls.wall.toggleReply({$oWall->getId()});"
                       href="#">{$aLang.wall_action_reply}</a>
                    {if $oWall->isAllowDelete()}
                        <a class="link link-lead link-red-blue link-clear"
                           onclick="return ls.wall.remove({$oWall->getId()});"
                           href="#">{$aLang.wall_action_delete}</a>
                    {/if}
                </div>
            </div>
        </div>

        <div id="wall-reply-container-{$oWall->getId()}" class="comment-wrapper">
            {if count($aReplyWall) < $oWall->getCountReply()}
                <a href="#" onclick="return ls.wall.loadReplyNext({$oWall->getId()});"
                   id="wall-reply-button-next-{$oWall->getId()}" class="link link-lead link-blue link-clear pull-right">
                                    <span class="wall-more-inner">{$aLang.wall_load_reply_more} <span
                                                id="wall-reply-count-next-{$oWall->getId()}">{$oWall->getCountReply()}</span> {$oWall->getCountReply()|declension:$aLang.comment_declension:$sLang}</span>
                </a>
            {/if}
            {if $aReplyWall}
                {include file='actions/profile/action.profile.wall_items_reply.tpl'}
            {/if}
        </div>

        {if E::IsUser()}
            <form class="wall-submit wall-submit-reply pab24" style="display: none">
                   <textarea rows="4" id="wall-reply-text-{$oWall->getId()}" class="form-control js-wall-reply-text"
                              placeholder="{$aLang.wall_reply_placeholder}"
                              onclick="return ls.wall.expandReply({$oWall->getId()});"></textarea>
                   <button type="button"
                        onclick="ls.wall.addReply(jQuery('#wall-reply-text-' + '{$oWall->getId()}').val(), {$oWall->getId()});"
                        class="btn btn-blue btn-big corner-no mat4 js-button-wall-submit">{$aLang.wall_reply_submit}</button>
            </form>
        {/if}
    </div>
{/foreach}
