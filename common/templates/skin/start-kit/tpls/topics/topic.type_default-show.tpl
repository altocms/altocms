{$oBlog=$oTopic->getBlog()}
{$oUser=$oTopic->getUser()}
{$oVote=$oTopic->getVote()}
{$oFavourite=$oTopic->getFavourite()}
{$oContentType=$oTopic->getContentType()}

<article class="topic topic-type_{$oTopic->getType()} js-topic">
    {block name="topic_header"}
        <header class="topic-header">
            {if !$bPreview}
            {if E::IsUser() AND ($oTopic->CanEditedBy(E::User()) OR $oTopic->CanDeletedBy(E::User()))}
                <ul class="list-unstyled list-inline small pull-right actions">
                    <li><span class="glyphicon glyphicon-cog actions-tool"></span></li>
                    {if $oTopic->CanEditedBy(E::User())}
                        <li>
                            <a href="{router page='content'}edit/{$oTopic->getId()}/" title="{$aLang.topic_edit}" class="actions-edit">
                                {$aLang.topic_edit}
                            </a>
                        </li>
                    {/if}

                    {if $oTopic->CanDeletedBy(E::User())}
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
            <h2 class=" header topic-header-title">
                {$oTopic->getTitle()|escape:'html'}

                {if $oTopic->getPublish() == 0}
                    <span class="glyphicon glyphicon-file text-muted" title="{$aLang.topic_unpublish}"></span>
                {/if}

                {if $oTopic->getType() == 'link'}
                    <span class="glyphicon glyphicon-globe text-muted" title="{$aLang.topic_link}"></span>
                {/if}
            </h2>

            <div class="topic-header-info">
                <a href="{$oBlog->getUrlFull()}" class="topic-blog">{$oBlog->getTitle()|escape:'html'}</a>

                <time datetime="{date_format date=$oTopic->getDate() format='c'}"
                      title="{date_format date=$oTopic->getDate() format='j F Y, H:i'}" class="topic-info-date">
                    {date_format date=$oTopic->getDate() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}
                </time>

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

    {block name="topic_fields"}
        {if $oContentType AND $oContentType->isAllow('link') AND $oTopic->getSourceLink()}
            {include file="fields/field.link-show.tpl"}
        {/if}

        {if $oContentType AND $oContentType->isAllow('poll') AND $oTopic->getQuestionAnswers()}
            {include file="fields/field.poll-show.tpl"}
        {/if}

        {if $oTopic->isShowPhotoset()}
            {include file="fields/field.photoset-show.tpl"}
        {/if}

        {if $oContentType}
            {foreach from=$oContentType->getFields() item=oField}
                {include file="fields/customs/field.custom.`$oField->getFieldType()`-show.tpl" oField=$oField}
            {/foreach}
        {/if}
    {/block}

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
                <li class="topic-info-author js-popover-user-{$oUser->getId()}">
                    <a href="{$oUser->getProfileUrl()}" class="avatar">
                        <img src="{$oUser->getAvatarUrl('mini')}" {$oUser->getAvatarImageSizeAttr('mini')} alt="{$oUser->getDisplayName()}" />
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

                {hook run='topic_show_info' topic=$oTopic}
            </ul>
            {/if}

            {if Config::Get('module.topic.draft_link') AND !$bPreview AND !$oTopic->getPublish()}
                <div class="topic-link">
                    {$aLang.topic_draft_link}:<br/>
                    <a href="{$oTopic->getDraftUrl()}">
                        {$oTopic->getDraftUrl()}
                    </a>
                </div>
            {/if}

        </footer>
    {/block}
</article> <!-- /.topic -->

{hook run='topic_show_end' topic=$oTopic bTopicList=false}
