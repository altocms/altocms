 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{$oBlog=$oTopic->getBlog()}
{$oUser=$oTopic->getUser()}
{$oVote=$oTopic->getVote()}
{$oFavourite=$oTopic->getFavourite()}
{$oContentType=$oTopic->getContentType()}

<!-- Блок топика -->
<div class="panel panel-default topic raised topic-type_{$oTopic->getType()} js-topic">

    <div class="panel-body">
        {block name="topic_header"}
            <h2 class="topic-title accent">
                {$oTopic->getTitle()|escape:'html'}

                {if $oTopic->getPublish() == 0}
                    &nbsp;<span class="fa fa-file-text-o" title="{$aLang.topic_unpublish}"></span>
                {/if}

                {if $oTopic->getType() == 'link'}
                    &nbsp;<span class="fa fa-globe" title="{$aLang.topic_link}"></span>
                {/if}
            </h2>

            <div class="topic-info">
                <span class="topic-blog">
                    <a class="link link-lead link-blue" href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a>
                </span>
                <span class="topic-date-block">
                    <span class="topic-date">{$oTopic->getDate()|date_format:'d.m.Y'}</span>
                    <span class="topic-time">{$oTopic->getDate()|date_format:"H:i"}</span>
                </span>
                {if !$bPreview}
                    <span class="pull-right topic-top-controls">
                        {if E::IsAdmin() OR E::UserId()==$oTopic->getUserId() OR E::UserId()==$oBlog->getOwnerId() OR $oBlog->getUserIsAdministrator() OR $oBlog->getUserIsModerator()}
                            <a href="{router page='content'}edit/{$oTopic->getId()}/" title="{$aLang.topic_edit}" class="small link link-lead link-dark link-clear">
                                <i class="fa fa-pencil"></i>
                                {*&nbsp;{$aLang.topic_edit}*}
                            </a>
                            {if E::IsAdmin() OR $oBlog->getUserIsAdministrator() OR $oBlog->getOwnerId()==E::UserId()}
                                &nbsp;<a href="#" class="small link link-lead link-clear link-red-blue" title="{$aLang.topic_delete}"
                                   onclick="ls.topic.remove('{$oTopic->getId()}', '{$oTopic->getTitle()}'); return false;">
                                    <i class="fa fa-times"></i>
                                {*&nbsp;{$aLang.topic_delete}*}
                                </a>
                            {/if}
                        {/if}
                    </span>
                {/if}
            </div>
        {/block}


        {block name="topic_content"}
            <div class="topic-text">
                {hook run='topic_content_begin' topic=$oTopic bTopicList=false}

                {$oTopic->getText()}

                {hook run='topic_content_end' topic=$oTopic bTopicList=false}
            </div>
        {/block}

        {if $oTopic->isShowPhotoset()}
            {include file="fields/field.photoset-show.tpl"}
        {/if}

        {if $oContentType AND $oContentType->isAllow('poll') AND $oTopic->getQuestionAnswers()}
            {include file="fields/field.poll-show.tpl"}
        {/if}

        {if $oContentType AND $oContentType->isAllow('link') AND $oTopic->getSourceLink()}
            {include file="fields/field.link-show.tpl"}
        {/if}

        {if $oContentType}
            {foreach from=$oContentType->getFields() item=oField}
                {include file="fields/customs/field.custom.`$oField->getFieldType()`-show.tpl" oField=$oField}
            {/foreach}
        {/if}


        {include file="fields/field.tags-show.tpl"}

    </div>



    {if !$bPreview}
        <div class="bg-warning topic-share" id="topic_share_{$oTopic->getId()}">
            {hookb run="topic_share" topic=$oTopic bTopicList=false}
                <div class="yashare-auto-init"
                     data-yashareTitle="{$oTopic->getTitle()|escape:'html'}"
                     data-yashareDescription=""
                     data-yashareLink="{$oTopic->getUrl()}"
                     data-yashareL10n="{Config::Get('lang.current')}"
                     data-yashareTheme="counter"
                     data-yashareType="small"
                     {if $oTopic->getPreviewImageUrl()}data-yashareImage="{$oTopic->getPreviewImageUrl()}"{/if}
                     data-yashareQuickServices="yaru,vkontakte,facebook,twitter,odnoklassniki,moimir,lj,gplus"></div>
            {/hookb}
        </div>
    {/if}

    {block name="topic_footer"}
        {if !$bPreview}
        <div class="topic-footer">
            <ul>
                <li class="topic-user js-popover-user-{$oUser->getId()}">
                    <img src="{$oUser->getAvatarUrl('small')}" alt="{$oUser->getDisplayName()}"/>
                    <a class="userlogo link link-dual link-lead link-clear" href="{$oUser->getProfileUrl()}">
                        {$oUser->getDisplayName()}
                    </a>
                </li>
                <li class="topic-favourite">
                    <a class="link link-dark link-lead link-clear {if E::IsUser() AND $oTopic->getIsFavourite()}active{/if}"
                       onclick="return ls.favourite.toggle({$oTopic->getId()},this,'topic');"
                       href="#">
                        {if $oTopic->getIsFavourite()}<i class="fa fa-star"></i>{else}<i class="fa fa-star-o"></i>{/if}
                        <span class="favourite-count" id="fav_count_topic_{$oTopic->getId()}">{$oTopic->getCountFavourite()}</span>
                    </a>
                </li>
                <li class="topic-info-share">
                    <a class="link link-dark link-lead link-clear" href="#"
                       title="{$aLang.topic_share}"
                       onclick="$('#topic_share_' + '{$oTopic->getId()}').slideToggle(); return false;">
                        <i class="fa fa-share-alt"></i>&nbsp;
                    </a>
                </li>

                <li class="topic-comments">
                    <a href="{$oTopic->getUrl()}#comments" title="{$aLang.topic_comment_read}" class="link link-dark link-lead link-clear">
                        <i class="fa fa-comment"></i>
                        <span>{$oTopic->getCountComment()}</span>
                        {if $oTopic->getCountCommentNew()}<span class="green">+{$oTopic->getCountCommentNew()}</span>{/if}
                    </a>
                </li>

                {if Config::Get('module.topic.draft_link') AND !$bPreview AND !$oTopic->getPublish()}
                    <li>
                        <a href="#" class="link link-dark link-lead link-clear"
                           onclick="prompt('{$aLang.topic_draft_link}', '{$oTopic->getDraftUrl()}'); return false;">
                            <i class="fa fa-link"></i>
                        </a>
                    </li>
                {/if}

                {hook run='topic_show_info' topic=$oTopic bTopicList=false oVote=$oVote}
            </ul>
            {/if}

        </div>
    {/block}
</div> <!-- /.topic -->

{hook run='topic_show_end' topic=$oTopic bTopicList=false}
