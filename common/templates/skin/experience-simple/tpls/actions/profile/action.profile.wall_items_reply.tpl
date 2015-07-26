 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{foreach $aReplyWall as $oReplyWall}
    {$oReplyUser=$oReplyWall->getUser()}
    <div id="wall-reply-item-{$oReplyWall->getId()}" class="js-wall-reply-item comment wall-comment-reply wall-level-1">
        {*<a class="wall-user-logo js-popup-user-{$oReplyUser->getId()}"*}
           {*href="{$oReplyUser->getProfileUrl()}">*}
            {*<img src="{$oReplyUser->getAvatarUrl(50)}" alt="user-logo"/>*}
        {*</a>*}
        <div class="comment-tools mab6">
            <ul>
                <li class="comment-user js-popover-user-{$oReplyUser->getId()}">
                    <a href="{$oReplyUser->getProfileUrl()}" class="mal0">
                        <img src="{$oReplyUser->getAvatarUrl('small')}" alt="{$oReplyUser->getDisplayName()}"/>
                    </a>
                    <a class="userlogo link link-blue link-lead link-clear"
                       href="{$oReplyUser->getProfileUrl()}">
                        {$oReplyUser->getDisplayName()}
                        <span class="caret"></span>
                    </a>
                </li>
                <li class="comment-date-block bordered">
                    <a class="link link-blue link-lead link-clear" href="#">
                        <span class="topic-date">{$oReplyWall->getDateAdd()|date_format:'d.m.Y'}</span>
                        <span class="topic-time">{$oReplyWall->getDateAdd()|date_format:'H:i'}</span>
                    </a>
                </li>
                {if $oReplyWall->isAllowDelete()}
                    <li class="bordered">
                        <a class="link link-clear link-gray"
                           onclick="return ls.wall.remove({$oReplyWall->getId()});"
                           href="#"><i class="fa fa-trash-o"></i></a>
                    </li>
                {/if}
            </ul>
        </div>

        <div class="wall-content">
            <div class="wall-text">
                {$oReplyWall->getText()}
            </div>
        </div>
    </div>
{/foreach}
