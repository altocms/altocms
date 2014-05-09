{$oUser=$oComment->getUser()}
{$oVote=$oComment->getVote()}

{$sCommentClass = ""}
{if $oComment->isBad()}
    {$sCommentClass = "$sCommentClass comment-bad"}
{/if}
{if $oComment->getDelete()}
    {$sCommentClass = "$sCommentClass comment-deleted"}
{elseif E::UserId()==$oComment->getUserId()}
    {$sCommentClass = "$sCommentClass comment-self"}
{elseif $sDateReadLast <= $oComment->getDate()}
    {$sCommentClass = "$sCommentClass comment-new"}
{/if}
<section id="comment_id_{$oComment->getId()}" class="comment {$sCommentClass}">
    {if !$oComment->getDelete() OR $bOneComment OR E::IsAdmin() OR $oComment->isDeletable()}
        <a name="comment{$oComment->getId()}"></a>
        <a href="{$oUser->getProfileUrl()}" class="comment-avatar js-popup-user-{$oUser->getId()}">
            <img src="{$oUser->getAvatarUrl(64)}" alt="{$oUser->getDisplayName()}"/>
        </a>
        <ul class="list-unstyled small comment-info">
            <li class="comment-info-author">
                <a href="{$oUser->getProfileUrl()}"
                   {if $iAuthorId == $oUser->getId()}title="{$sAuthorNotice}"
                   class="comment-topic-author"{/if}>{$oUser->getDisplayName()}</a>
            </li>
            <li class="comment-info-date">
                <a href="{if Config::Get('module.comment.nested_per_page')}{router page='comments'}{else}#comment{/if}{$oComment->getId()}"
                   class="link-dotted" title="{$aLang.comment_url_notice}">
                    <time datetime="{date_format date=$oComment->getDate() format='c'}">{date_format date=$oComment->getDate() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}</time>
                </a>
            </li>

            {if $oComment->getPid()}
                <li class="goto-comment-parent"><a href="#"
                                                   onclick="ls.comments.goToParentComment({$oComment->getId()},{$oComment->getPid()}); return false;"
                                                   title="{$aLang.comment_goto_parent}">↑</a></li>
            {/if}
            <li class="goto-comment-child"><a href="#" title="{$aLang.comment_goto_child}">↓</a></li>

            {if $oComment->getTargetType() != 'talk'}
                {$sVoteClass = ""}
                {if $oComment->getRating() > 0}
                    {$sVoteClass = " vote-count-positive"}
                {elseif $oComment->getRating() < 0}
                    {$sVoteClass = " vote-count-negative"}
                {/if}
                {if $oVote}
                    {$sVoteClass = " voted"}
                    {if $oVote->getDirection() > 0}
                        {$sVoteClass = " voted-up"}
                    {else}
                        {$sVoteClass = " voted-down"}
                    {/if}
                {/if}
                <li class="vote js-vote {$sVoteClass}" data-target-type="comment" data-target-id="{$oComment->getId()}">
                    <div class="vote-up js-vote-up"><span class="glyphicon glyphicon-plus-sign"></span></div>

                    <span class="vote-count js-vote-rating">{if $oComment->getRating() > 0}+{/if}{$oComment->getRating()}</span>

                    <div class="vote-down js-vote-down"><span class="glyphicon glyphicon-minus-sign"></span></div>
                </li>
            {/if}

            {if E::IsUser() AND !$bNoCommentFavourites}
                <li class="comment-favourite">
                    <a href="#" onclick="return ls.favourite.toggle({$oComment->getId()},this,'comment');"
                       class="favourite {if $oComment->getIsFavourite()}active{/if}"><span
                                class="glyphicon glyphicon-star"></span></a>
                    <span class="text-muted favourite-count"
                          id="fav_count_comment_{$oComment->getId()}">{if $oComment->getCountFavourite() > 0}{$oComment->getCountFavourite()}{/if}</span>
                </li>
            {/if}

            <li class="text-muted comment-updated"
                    {if !$oComment->getCommentDateEdit()}style="display: none;" {/if}>
                {$aLang.comment_updated}:
                <time datetime="{date_format date=$oComment->getCommentDateEdit() format='c'}">
                    {date_format date=$oComment->getCommentDateEdit() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}
                </time>
            </li>

        </ul>
        <div id="comment_content_id_{$oComment->getId()}" class="comment-content text">
            {$oComment->getText()}
        </div>
        {if E::IsUser()}
            <ul class="list-unstyled list-inline small comment-actions">
                {if !$oComment->getDelete() AND $bAllowToComment}
                    <li><a href="#" onclick="ls.comments.reply({$oComment->getId()}); return false;"
                           class="reply-link link-dotted">{$aLang.comment_answer}</a></li>
                {/if}

                {if $oComment->isDeletable()}
                    {if !$oComment->getDelete()}
                        <li><a href="#" class="comment-delete link-dotted"
                               onclick="ls.comments.toggle(this,{$oComment->getId()}); return false;">{$aLang.comment_delete}</a>
                        </li>
                    {/if}

                    {if $oComment->getDelete()}
                        <li><a href="#" class="comment-repair link-dotted"
                               onclick="ls.comments.toggle(this,{$oComment->getId()}); return false;">{$aLang.comment_repair}</a>
                        </li>
                    {/if}
                {/if}

                {if $oComment->isEditable()}
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
            </ul>
        {/if}
    {else}
        <span class="text-muted">{$aLang.comment_was_deleted}</span>
    {/if}
</section>
