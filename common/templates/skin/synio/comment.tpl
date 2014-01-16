{assign var="oUser" value=$oComment->getUser()}
{assign var="oVote" value=$oComment->getVote()}


<section id="comment_id_{$oComment->getId()}" class="comment {if $oComment->isBad()}comment-bad{/if}
    {if $oComment->getDelete()}
        comment-deleted
    {elseif $oUserCurrent AND $oComment->getUserId() == $oUserCurrent->getId()}
        comment-self
    {elseif $sDateReadLast <= $oComment->getDate()}
        comment-new
    {/if}">

    {if !$oComment->getDelete() OR $bOneComment OR E::IsAdmin()}
        <a name="comment{$oComment->getId()}"></a>
        <div class="folding"></div>
        <a href="{$oUser->getProfileUrl()}"><img src="{$oUser->getAvatarUrl(48)}" alt="avatar" class="comment-avatar"/></a>
        <div id="comment_content_id_{$oComment->getId()}" class="comment-content">
            <div class=" text">
                {$oComment->getText()}
            </div>

            <div id="comment_updated_id_{$oComment->getId()}" class="comment-updated"
                 {if !$oComment->getCommentDateEdit()}style="display:none;"{/if}>
                {$aLang.comment_updated}:
                <time datetime="{date_format date=$oComment->getCommentDateEdit() format='c'}">
                    {date_format date=$oComment->getCommentDateEdit() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}
                </time>
            </div>
        </div>
        <ul class="comment-info">
            <li class="comment-author {if $iAuthorId == $oUser->getId()}comment-topic-author{/if}"
                title="{if $iAuthorId == $oUser->getId() and $sAuthorNotice}{$sAuthorNotice}{/if}">
                <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
            </li>
            <li class="comment-date">
                <time datetime="{date_format date=$oComment->getDate() format='c'}"
                      title="{date_format date=$oComment->getDate() format="j F Y, H:i"}">
                    {date_format date=$oComment->getDate() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}
                </time>
            </li>

            {if $oComment->getTargetType() != 'talk'}
                <li id="vote_area_comment_{$oComment->getId()}" class="vote
                    {if $oComment->getRating() > 0}vote-count-positive{elseif $oComment->getRating() < 0}vote-count-negative{/if}
                    {if ($oComment->isVoteExpired() AND !$oVote) OR (E::UserId() == $oUser->getId())}
                        vote-expired
                    {/if}
                    {if $oVote}
                        voted
                        {if $oVote->getDirection() > 0}voted-up{else}voted-down{/if}
                    {/if}">
                    <span class="vote-count"
                          id="vote_total_comment_{$oComment->getId()}">{if $oComment->getRating() > 0}+{/if}{$oComment->getRating()}</span>

                    <div class="vote-down" onclick="return ls.vote.vote({$oComment->getId()},this,-1,'comment');"></div>
                    <div class="vote-up" onclick="return ls.vote.vote({$oComment->getId()},this,1,'comment');"></div>
                </li>
            {/if}


            {if E::IsUser() AND !$bNoCommentFavourites}
                <li class="comment-favourite">
                    <div onclick="return ls.favourite.toggle({$oComment->getId()},this,'comment');"
                         class="favourite {if $oComment->getIsFavourite()}active{/if}"></div>
                    <span class="favourite-count"
                          id="fav_count_comment_{$oComment->getId()}">{if $oComment->getCountFavourite() > 0}{$oComment->getCountFavourite()}{/if}</span>
                </li>
            {/if}
            <li class="comment-link">
                <a href="{if Config::Get('module.comment.nested_per_page')}{router page='comments'}{else}#comment{/if}{$oComment->getId()}"
                   title="{$aLang.comment_url_notice}">
                    <i class="icon-synio-link"></i>
                </a>
            </li>

            {if $oComment->getPid()}
                <li class="goto goto-comment-parent">
                    <a href="#" onclick="ls.comments.goToParentComment('{$oComment->getId()}','{$oComment->getPid()}'); return false;" title="{$aLang.comment_goto_parent}">↑</a>
                </li>
            {/if}
            <li class="goto goto-comment-child"><a href="#" title="{$aLang.comment_goto_child}">↓</a></li>

            {if E::User()}
                {if !$oComment->getDelete() AND $bAllowNewComment}
                    <li class="comment-reply">
                        <a href="#" onclick="ls.comments.newComment('{$oComment->getId()}'); return false;" class="reply-link link-dotted">{$aLang.comment_answer}</a>
                    </li>
                {/if}

                {if $oComment->isDeletable()}
                    {if !$oComment->getDelete()}
                        <li class="comment-delete">
                            <a href="#" class="comment-delete link-dotted" onclick="ls.comments.toggle(this,'{$oComment->getId()}'); return false;">{$aLang.comment_delete}</a>
                        </li>
                    {else}
                        <li class="comment-repair">
                            <a href="#" class="comment-repair link-dotted" onclick="ls.comments.toggle(this,'{$oComment->getId()}'); return false;">{$aLang.comment_repair}</a>
                        </li>
                    {/if}
                {/if}

                {if $oComment->isEditable() AND ($oComment->getEditTime() OR E::IsAdmin())}
                    <li class="comment-edit">
                        <a href="#"
                           class="link-dotted"
                           onclick="ls.comments.editComment('{$oComment->getId()}', '{$oComment->getTargetType()}', '{$oComment->getTargetId()}'); return false;">
                            {$aLang.comment_edit}
                            {if Config::Get('module.comment.edit.rest_time') AND $oComment->getEditTime()}
                            (<span class="comment-edit-time-rest">{$oComment->getEditTime(true)}</span>)
                            <span class="comment-edit-time-remainder" style="display: none;">{$oComment->getEditTime()}</span>
                            {/if}
                        </a>
                    </li>
                {/if}

                {hook run='comment_action' comment=$oComment}
            {/if}
        </ul>
    {else}
        {$aLang.comment_was_delete}
    {/if}
</section>