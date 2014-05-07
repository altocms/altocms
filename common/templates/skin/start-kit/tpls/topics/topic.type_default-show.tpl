{$oBlog=$oTopic->getBlog()}
{$oUser=$oTopic->getUser()}
{$oVote=$oTopic->getVote()}
{$oFavourite=$oTopic->getFavourite()}
{$oContentType=$oTopic->getContentType()}

<article class="topic topic-type_{$oTopic->getType()} js-topic">
    {block name="topic_header"}
        <header class="topic-header">
            <h1 class="topic-header-title">
                {$oTopic->getTitle()|escape:'html'}

                {if $oTopic->getPublish() == 0}
                    <span class="glyphicon glyphicon-file text-muted" title="{$aLang.topic_unpublish}"></span>
                {/if}

                {if $oTopic->getType() == 'link'}
                    <span class="glyphicon glyphicon-globe text-muted" title="{$aLang.topic_link}"></span>
                {/if}
            </h1>

            <div class="topic-header-info">
                <a href="{$oBlog->getUrlFull()}" class="topic-blog">{$oBlog->getTitle()|escape:'html'}</a>

                <time datetime="{date_format date=$oTopic->getDateAdd() format='c'}"
                      title="{date_format date=$oTopic->getDateAdd() format='j F Y, H:i'}" class="topic-info-date">
                    {date_format date=$oTopic->getDateAdd() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}
                </time>

                {if !$bPreview}
                {if E::IsAdmin() OR E::UserId()==$oTopic->getUserId() OR E::UserId()==$oBlog->getOwnerId() OR $oBlog->getUserIsAdministrator() OR $oBlog->getUserIsModerator()}
                    <ul class="list-unstyled list-inline small pull-right actions">
                        <li><span class="glyphicon glyphicon-cog actions-tool"></span></li>
                        <li>
                            <a href="{router page='content'}edit/{$oTopic->getId()}/" title="{$aLang.topic_edit}" class="actions-edit">
                                {$aLang.topic_edit}
                            </a>
                        </li>

                        {if E::IsAdmin() OR $oBlog->getUserIsAdministrator() OR $oBlog->getOwnerId()==E::UserId()}
                            <li>
                                <a href="#" class="actions-delete" title="{$aLang.topic_delete}"
                                   onclick="ls.topic.remove('{$oTopic->getId()}', '{$oTopic->getTitle()}'); return false;">
                                    {$aLang.topic_delete}
                                </a>
                            </li>
                        {/if}
                    </ul>
                {/if}
                {/if}
            </div>
        </header>
    {/block}

    {block name="topic_content"}
        <div class="topic-content text">
            {hook run='topic_content_begin' topic=$oTopic bTopicList=false}

            {$oTopic->getText()}

            {hook run='topic_content_end' topic=$oTopic bTopicList=false}
        </div>
    {/block}

    {if $oContentType->isAllow('link') AND $oTopic->getLinkUrl()}
        {include file="fields/field.link-show.tpl"}
    {/if}

    {if $oContentType->isAllow('poll') AND $oTopic->getQuestionAnswers()}
        {include file="fields/field.poll-show.tpl"}
    {/if}

    {if $oContentType->isAllow('photoset') AND $oTopic->getPhotosetCount()}
        {include file="fields/field.photoset-show.tpl"}
    {/if}

    {if $oContentType}
        {foreach from=$oContentType->getFields() item=oField}
            {include file="fields/customs/field.custom.`$oField->getFieldType()`-show.tpl" oField=$oField}
        {/foreach}
    {/if}

    {block name="topic_footer"}
        <footer class="topic-footer">
            {include file="fields/field.tags-show.tpl"}

            {if !$bPreview}
            <div class="topic-share" id="topic_share_{$oTopic->getId()}">
                {hookb run="topic_share" topic=$oTopic bTopicList=false}
                    <div class="yashare-auto-init" data-yashareTitle="{$oTopic->getTitle()|escape:'html'}"
                         data-yashareLink="{$oTopic->getUrl()}" data-yashareL10n="ru" data-yashareType="none"
                         data-yashareQuickServices="yaru,vkontakte,facebook,twitter,odnoklassniki,moimir,lj,gplus"></div>
                {/hookb}
            </div>
            {/if}

            {if !$bPreview}
            <ul class="list-unstyled list-inline small topic-footer-info">
                <li class="topic-info-author">
                    <a href="{$oUser->getProfileUrl()}" class="avatar js-popup-{$oUser->getId()}">
                        <img src="{$oUser->getAvatarUrl(24)}" alt="{$oUser->getDisplayName()}" />
                    </a>
                    <a rel="author" href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                </li>
                <li class="topic-info-favourite">
                    <a href="#" onclick="return ls.favourite.toggle({$oTopic->getId()},this,'topic');"
                       class="favourite {if E::IsUser() AND $oTopic->getIsFavourite()}active{/if}"><span
                                class="glyphicon glyphicon-star"></span></a>
                    <span class="text-muted favourite-count"
                          id="fav_count_topic_{$oTopic->getId()}">{$oTopic->getCountFavourite()}</span>
                </li>
                <li class="topic-info-share"><a href="#" class="glyphicon glyphicon-share-alt"
                                                title="{$aLang.topic_share}"
                                                onclick="jQuery('#topic_share_' + '{$oTopic->getId()}').slideToggle(); return false;"></a>
                </li>

                {$sVoteClass = ""}
                {if $oVote OR E::UserId()==$oTopic->getUserId() OR strtotime($oTopic->getDateAdd())<$smarty.now-Config::Get('acl.vote.topic.limit_time')}
                    {if $oTopic->getRating() > 0}
                        {$sVoteClass = "$sVoteClass vote-count-positive"}
                    {elseif $oTopic->getRating() < 0}
                        {$sVoteClass = "$sVoteClass vote-count-negative"}
                    {/if}
                {/if}
                {if $oVote}
                    {$sVoteClass = "$sVoteClass voted"}
                    {if $oVote->getDirection() > 0}
                        {$sVoteClass = "$sVoteClass voted-up"}
                    {elseif $oVote->getDirection() < 0}
                        {$sVoteClass = "$sVoteClass voted-down"}
                    {/if}
                {/if}
                {if $oTopic->isVoteInfoShow()}
                    {$bVoteInfoShow=true}
                {/if}
                <li class="pull-right vote js-vote {$sVoteClass}" data-target-type="topic" data-target-id="{$oTopic->getId()}">
                    <div class="vote-up js-vote-up"><span class="glyphicon glyphicon-plus-sign"></span></div>
                    <div class="vote-count js-vote-rating {if $bVoteInfoShow}js-infobox-vote-topic{/if}"
                         title="{$aLang.topic_vote_count}: {$oTopic->getCountVote()}">
                        {if $bVoteInfoShow}
                            {if $oTopic->getRating() > 0}+{/if}{$oTopic->getRating()}
                        {else}
                            <a href="#" onclick="return ls.vote.vote({$oTopic->getId()},this,0,'topic');">?</a>
                        {/if}
                    </div>
                    <div class="vote-down js-vote-down"><span class="glyphicon glyphicon-minus-sign"></span></div>
                    {if $bVoteInfoShow}
                        <div id="vote-info-topic-{$oTopic->getId()}" style="display: none;">
                            <ul class="list-unstyled vote-topic-info">
                                <li><span class="glyphicon glyphicon-thumbs-up"></span>{$oTopic->getCountVoteUp()}</li>
                                <li><span class="glyphicon glyphicon-thumbs-down"></span>{$oTopic->getCountVoteDown()}
                                </li>
                                <li><span class="glyphicon glyphicon-eye-open"></span>{$oTopic->getCountVoteAbstain()}
                                </li>
                                {hook run='topic_show_vote_stats' topic=$oTopic}
                            </ul>
                        </div>
                    {/if}
                </li>

                {hook run='topic_show_info' topic=$oTopic}
            </ul>
            {/if}

            {hook run='topic_show_end' topic=$oTopic}
        </footer>
    {/block}
</article> <!-- /.topic -->
