{assign var="oUser" value=$oComment->getUser()}
{assign var="oVote" value=$oComment->getVote()}


<section id="comment_id_{$oComment->getId()}" class="comment
														{if $oComment->isBad()}
															comment-bad
														{/if}

														{if $oComment->getDelete()}
															comment-deleted
														{elseif $oUserCurrent and $oComment->getUserId() == $oUserCurrent->getId()} 
															comment-self
														{elseif $sDateReadLast <= $oComment->getDate()} 
															comment-new
														{/if}">
    {if !$oComment->getDelete() or $bOneComment or ($oUserCurrent and $oUserCurrent->isAdministrator())}
        <a id="comment{$oComment->getId()}"></a>
        <a href="{$oUser->getUserWebPath()}"
           class="avatar {if $iAuthorId == $oUser->getId()}comment-topic-author{else}comment-topic{/if}">
            <img src="{$oUser->getProfileAvatarPath(48)}" alt="{$oUser->getLogin()}"
                 title="{$oUser->getLogin()}{if $iAuthorId == $oUser->getId()} - {$sAuthorNotice}{/if}"
                 class="comment-avatar"/>
        </a>
        <div class="wraps">
            <div class="box">
                <ul class="comment-info">
                    <li class="comment-date">
                        <a href="{if $oConfig->GetValue('module.comment.nested_per_page')}{router page='comments'}{else}#comment{/if}{$oComment->getId()}"
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
                        <li id="vote_area_comment_{$oComment->getId()}" class="btn-group vote
                                                                                {if $oComment->getRating() > 0}
                                                                                    vote-count-positive
                                                                                {elseif $oComment->getRating() < 0}
                                                                                    vote-count-negative
                                                                                {/if}

                                                                                {if $oVote}
                                                                                    voted

                                                                                    {if $oVote->getDirection() > 0}
                                                                                        voted-up
                                                                                    {else}
                                                                                        voted-down
                                                                                    {/if}
                                                                                {/if}">
                            {if {cfg name='view.vote_comments.type'} == 'plus_minus'}
                                <div class="vote-up btn btn-mini"
                                     onclick="return ls.vote.vote({$oComment->getId()},this,1,'comment');"><i
                                            class="icon-thumbs-up"></i></div>
                                <span class="vote-count btn btn-mini"
                                      id="vote_total_comment_{$oComment->getId()}">{if $oComment->getRating() > 0}+{/if}{$oComment->getRating()}</span>
                                <div class="vote-down btn btn-mini"
                                     onclick="return ls.vote.vote({$oComment->getId()},this,-1,'comment');"><i
                                            class="icon-thumbs-down"></i></div>
                            {elseif {cfg name='view.vote_comments.type'} == 'minus_plus'}
                                <div class="vote-down btn btn-mini"
                                     onclick="return ls.vote.vote({$oComment->getId()},this,-1,'comment');"><i
                                            class="icon-thumbs-down"></i></div>
                                <span class="vote-count btn btn-mini"
                                      id="vote_total_comment_{$oComment->getId()}">{if $oComment->getRating() > 0}+{/if}{$oComment->getRating()}</span>
                                <div class="vote-up btn btn-mini"
                                     onclick="return ls.vote.vote({$oComment->getId()},this,1,'comment');"><i
                                            class="icon-thumbs-up"></i></div>
                            {/if}
                        </li>
                    {/if}


                    {if $oUserCurrent and !$bNoCommentFavourites}
                        <li class="comment-favourite">
                            <div onclick="return ls.favourite.toggle({$oComment->getId()},this,'comment');"
                                 class="favourite {if $oComment->getIsFavourite()}active{/if}"></div>
                            <span class="favourite-count"
                                  id="fav_count_comment_{$oComment->getId()}">{if $oComment->getCountFavourite() > 0}{$oComment->getCountFavourite()}{/if}</span>
                        </li>
                    {/if}
                </ul>


                <div id="comment_content_id_{$oComment->getId()}" class="comment-content text">
                    {$oComment->getText()}
                </div>


                <div id="comment_updated_id_{$oComment->getId()}" class="comment-updated"
                     {if !$oComment->getCommentDateEdit()}style="display:none;"{/if}>
                    {$aLang.comment_updated}:
                    <time datetime="{date_format date=$oComment->getCommentDateEdit() format='c'}">{date_format date=$oComment->getCommentDateEdit() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}</time>
                </div>


                {if $oUserCurrent}
                    <ul class="comment-actions">
                        {if !$oComment->getDelete() and !$bAllowNewComment}
                            <li class="comment-reply"><a href="#"
                                                         onclick="ls.comments.toggleCommentForm({$oComment->getId()}); return false;"
                                                         class="reply-link">{$aLang.comment_answer}</a></li>
                        {/if}

                        {if !$oComment->getDelete() and $oUserCurrent and $oUserCurrent->isAdministrator()}
                            <li class="comment-delete"><a href="#" class="comment-delete red"
                                                          onclick="ls.comments.toggle(this,{$oComment->getId()}); return false;">{$aLang.comment_delete}</a>
                            </li>
                        {/if}

                        {if $oComment->getDelete() and $oUserCurrent and $oUserCurrent->isAdministrator()}
                            <li class="comment-repair"><a href="#" class="comment-repair green"
                                                          onclick="ls.comments.toggle(this,{$oComment->getId()}); return false;">{$aLang.comment_repair}</a>
                            </li>
                        {/if}

                        {if $oComment->isEditable() AND $oComment->getEditTime()}
                            <li class="comment-edit">
                                <a href="#"
                                   class="link-dotted"
                                   onclick="ls.comments.editComment('{$oComment->getId()}', '{$oComment->getTargetType()}', '{$oComment->getTargetId()}'); return false;">
                                    {$aLang.comment_edit}
                                    <!--
                                    (<span class="comment-edit-time-rest">{$oComment->getEditTime()}</span>)
                                    <span class="comment-edit-time-remainder" style="display: none;">{$oComment->getEditTime()}</span>
                                    -->
                                </a></li>
                        {/if}

                        {hook run='comment_action' comment=$oComment}
                    </ul>
                {/if}
            </div>
        </div>
    {else}
        {$aLang.comment_was_delete}
    {/if}
</section>