 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{foreach $aWall as $oWall}
    {$oWallUser=$oWall->getUser()}
    {$aReplyWall=$oWall->getLastReplyWall()}
    <div id="wall-item-{$oWall->getId()}" class="js-wall-item comment-wrapper wall-block pab6 mab6">
        <div class="comment-tools mab6">
            <ul>
                <li class="comment-user js-popover-user-{$oWallUser->getId()}">
                    <a href="{$oWallUser->getProfileUrl()}" class="mal0">
                        <img src="{$oWallUser->getAvatarUrl('small')}" alt="{$oWallUser->getDisplayName()}"/>
                    </a>
                    <a class="userlogo link link-blue link-lead link-clear"
                       href="{$oWallUser->getProfileUrl()}">
                        {$oWallUser->getDisplayName()}
                        <span class="caret"></span>
                    </a>
                </li>
                <li class="comment-date-block bordered">
                    <a class="link link-blue link-lead link-clear" href="#">
                        <span class="topic-date">{$oWall->getDateAdd()|date_format:'d.m.Y'}</span>
                        <span class="topic-time">{$oWall->getDateAdd()|date_format:'H:i'}</span>
                    </a>
                </li>
                {if $oWall->isAllowDelete()}
                <li class="bordered">
                    <a class="link link-clear link-gray"
                       onclick="return ls.wall.remove({$oWall->getId()});"
                       href="#"><i class="fa fa-trash-o"></i></a>
                </li>
                {/if}
            </ul>
        </div>

        <div class="wall-content mab6">
            <div class="wall-text mab6">
                {$oWall->getText()}
            </div>
            <div class="wall-footer">
                <a class="link link-lead link-blue link-clear  comment-reply"
                   onclick="return ls.wall.toggleReply({$oWall->getId()});"
                   href="#">{$aLang.wall_action_reply}</a>


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
            <form class="wall-submit wall-submit-reply pab6 pat6" style="display: none">
                   <textarea rows="4" id="wall-reply-text-{$oWall->getId()}" class="form-control js-wall-reply-text"
                              placeholder="{$aLang.wall_reply_placeholder}"
                              onclick="return ls.wall.expandReply({$oWall->getId()});"></textarea>
                   <button type="button"
                        onclick="ls.wall.addReply(jQuery('#wall-reply-text-' + '{$oWall->getId()}').val(), {$oWall->getId()});"
                        class="btn btn-default corner-no mat4 js-button-wall-submit pull-right">{$aLang.wall_reply_submit}</button>
            </form>
        {/if}
    </div>
{/foreach}
