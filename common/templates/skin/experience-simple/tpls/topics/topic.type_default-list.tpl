{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if Config::Get('view.masonry') == true}
    {include file="topics/masonry_sidebar.tpl"}
{else}
{$oBlog=$oTopic->getBlog()}
{$oUser=$oTopic->getUser()}
{$oVote=$oTopic->getVote()}
{$oFavourite=$oTopic->getFavourite()}
{$oContentType=$oTopic->getContentType()}

<!-- Блок топика -->
<div class="panel panel-default topic flat topic-type_{$oTopic->getType()} js-topic">

    <div class="panel-body">
        {block name="topic_header"}
            <h2 class="topic-title accent">
                <a class="link link-lead link-clear link-dark" href="{$oTopic->getUrl()}">{$oTopic->getTitle()|escape:'html'}</a>

                {if $oTopic->getPublish() == 0}
                    &nbsp;<span class="fa fa-file-text-o" title="{$aLang.topic_unpublish}"></span>
                {/if}

                {if $oTopic->getType() == 'link'}
                    &nbsp;<span class="fa fa-globe" title="{$aLang.topic_link}"></span>
                {/if}
            </h2>

            <div class="topic-info">
                <ul>
                    <li data-alto-role="popover"
                        data-api="user/{$oUser->getId()}/info"
                        class="topic-user">
                        <img src="{$oUser->getAvatarUrl('small')}" {$oUser->getAvatarImageSizeAttr('small')} alt="{$oUser->getDisplayName()}"/>
                        <a class="userlogo link link-dual link-lead link-clear" href="{$oUser->getProfileUrl()}">
                            {$oUser->getDisplayName()}
                        </a>
                    </li>
                    <li class="topic-blog">
                        <a class="link link-lead link-blue"
                           href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a>
                    </li>
                    <li class="topic-date-block">
                        <span class="topic-date">{$oTopic->getDate()|date_format:'d.m.Y'}</span>
                        <span class="topic-time">{$oTopic->getDate()|date_format:"H:i"}</span>
                    </li>
                </ul>

            </div>
        {/block}


        {block name="topic_content"}
            <div class="topic-text">
                {hook run='topic_content_begin' topic=$oTopic bTopicList=true}

                {$oTopic->getTextShort()}

                {hook run='topic_content_end' topic=$oTopic bTopicList=true}
            </div>
        {/block}

        {block name="topic_fields"}
            {*{include file="fields/field.tags-show.tpl"}*}
        {/block}

    </div>


    {block name="topic_footer"}
        {if !$bPreview}
        <div class="topic-footer">
            <ul>

                {hook run='topic_show_info' topic=$oTopic bTopicList=true oVote=$oVote}

                <li class="topic-favourite">
                    <a class="link link-dark link-lead link-clear {if E::IsUser() AND $oTopic->getIsFavourite()}active{/if}"
                       onclick="return ls.favourite.toggle({$oTopic->getId()},this,'topic');"
                       href="#">
                        {if $oTopic->getIsFavourite()}<i class="fa fa-star"></i>{else}<i class="fa fa-star-o"></i>{/if}
                        <span class="favourite-count" id="fav_count_topic_{$oTopic->getId()}">{$oTopic->getCountFavourite()}</span>
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

                <li class="pull-right read-more">
                    <a href="{$oTopic->getUrl()}#cut" title="{$aLang.topic_read_more}" class="btn btn-gray hidden-xxs">
                        {if $oTopic->getCutText()}
                            {$oTopic->getCutText()}
                        {else}
                            {$aLang.topic_read_more}
                        {/if}
                    </a>
                    <a href="{$oTopic->getUrl()}#cut" title="{$aLang.topic_read_more}" class="fa-btn btn btn-gray visible-xxs">
                        <i class="fa fa-chevron-right"></i>
                    </a>
                </li>

                {if !$bPreview}
                    <li class="pull-right topic-controls">
                        {if E::IsAdmin() OR E::UserId()==$oTopic->getUserId() OR E::UserId()==$oBlog->getOwnerId() OR $oBlog->getUserIsAdministrator() OR $oBlog->getUserIsModerator()}
                            <a href="{router page='content'}edit/{$oTopic->getId()}/" title="{$aLang.topic_edit}" class="small link link-lead link-dark link-clear">
                                <i class="fa fa-pencil"></i>
                                {*&nbsp;{$aLang.topic_edit}*}
                            </a>
                            {if E::IsAdmin() OR $oBlog->getUserIsAdministrator() OR $oBlog->getOwnerId()==E::UserId()}
                                &nbsp;&nbsp;<a href="#" class="small link link-lead link-clear link-red-blue" title="{$aLang.topic_delete}"
                                         onclick="ls.topic.remove('{$oTopic->getId()}', '{$oTopic->getTitle()}'); return false;">
                                <i class="fa fa-trash-o"></i>
                                {*&nbsp;{$aLang.topic_delete}*}
                            </a>
                            {/if}
                        {/if}
                    </li>
                {/if}
            </ul>
        </div>
        {/if}
    {/block}
</div> <!-- /.topic -->
{/if}

{hook run='topic_list_end,topic_show_end' topic=$oTopic bTopicList=true}
