{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
{/block}

{block name="layout_content"}
    {$oUserOwner=$oBlog->getOwner()}
    {$oVote=$oBlog->getVote()}

<script type="text/javascript">
    jQuery(function ($) {
        ls.lang.load({lang_load name="blog_fold_info,blog_expand_info"});
    });
</script>

{include_once file='modals/modal.blog_delete.tpl'}

<div class="blog">
    <header class="blog-header">
        {$oBlogType=$oBlog->getBlogType()}
        <img src="{$oBlog->getAvatarUrl('big')}" {$oBlog->getAvatarImageSizeAttr('big')} class="avatar"/>

        <h1 class=" header">
            {$oBlog->getTitle()|escape:'html'}
            {if $oBlogType}
                {if $oBlogType->IsHidden()}
                    <span title="{$aLang.blog_closed}" class="glyphicon glyphicon-eye-close"></span>
                {elseif $oBlogType->IsPrivate()}
                    <span title="{$aLang.blog_closed}" class="glyphicon glyphicon-lock"></span>
                {/if}
            {/if}
        </h1>

        {hook run="blog_header" oBlog=$oBlog}

        {if E::IsUser() AND ($oBlog->CanEditedBy(E::User()) OR $oBlog->CanAdminBy(E::User()) OR $oBlog->CanDeletedBy(E::User()))}
            <ul class="small list-unstyled list-inline pull-right actions">
                <li><span class="glyphicon glyphicon-cog actions-tool"></span></li>
                {if $oBlog->CanEditedBy(E::User())}
                <li>
                    <a href="{router page='blog'}edit/{$oBlog->getId()}/" title="{$aLang.blog_menu_edit}"
                       class="actions-edit">{$aLang.blog_menu_edit}</a>
                </li>
                {/if}
                {if $oBlog->CanAdminBy(E::User())}
                <li>
                    <a href="{router page='blog'}admin/{$oBlog->getId()}/" title="{$aLang.blog_menu_admin}"
                       class="actions-edit">{$aLang.blog_menu_admin}</a>
                </li>
                {/if}
                {if $oBlog->CanDeletedBy(E::User())}
                <li>
                    <a href="#" title="{$aLang.blog_menu_delete}" class="actions-delete js-modal-blog_delete">{$aLang.blog_menu_delete}</a>
                </li>
                {/if}
            </ul>
        {/if}
    </header>

    <div class="blog-mini" id="blog-mini">
        <div class="row">
            <div class="col-sm-6 col-lg-6 small text-muted">
                <span id="blog_user_count_{$oBlog->getId()}">{$iCountBlogUsers}</span> {$iCountBlogUsers|declension:$aLang.reader_declension:$sLang}
                ,
                {$oBlog->getCountTopic()} {$oBlog->getCountTopic()|declension:$aLang.topic_declension:$sLang}
            </div>
            <div class="col-sm-6 col-lg-6 blog-mini-header">
                <a href="#" class="small link-dotted"
                   onclick="ls.blog.toggleInfo(); return false;">{$aLang.blog_expand_info}</a>
                <a href="{router page='rss'}blog/{$oBlog->getUrl()}/" class="small">RSS</a>
                {if E::UserId()!=$oBlog->getOwnerId()}
                    <button type="submit" class="btn btn-success btn-sm{if $oBlog->getUserIsJoin()} active{/if}"
                            id="button-blog-join-first-{$oBlog->getId()}"
                            data-button-additional="button-blog-join-second-{$oBlog->getId()}" data-only-text="1"
                            onclick="ls.blog.toggleJoin(this, {$oBlog->getId()}); return false;">{if $oBlog->getUserIsJoin()}{$aLang.blog_leave}{else}{$aLang.blog_join}{/if}</button>
                {/if}
            </div>
        </div>
    </div>

    <div class="blog-more-content" id="blog-more-content" style="display: none;">
        <div class="blog-content">
            <p class="blog-description">{$oBlog->getDescription()}</p>
        </div>

        <footer class="small blog-footer">
            {hook run='blog_info_begin' oBlog=$oBlog}

            <div class="row">
                <div class="col-lg-6">
                    <dl class="dl-horizontal blog-info">
                        <dt>{$aLang.infobox_blog_create}</dt>
                        <dd>{date_format date=$oBlog->getDateAdd() format="j F Y"}</dd>

                        <dt>{$aLang.infobox_blog_topics}</dt>
                        <dd>{$oBlog->getCountTopic()}</dd>

                        <dt><a href="{$oBlog->getUrlFull()}users/">{$aLang.infobox_blog_users}</a></dt>
                        <dd>{$iCountBlogUsers}</dd>

                        {hook run="blog_stat" oBlog=$oBlog}
                    </dl>
                </div>

                <div class="col-lg-6">
                    <strong>{$aLang.blog_user_administrators} ({$iCountBlogAdministrators}):</strong><br/>
                    <span class="avatar">
                        <a href="{$oUserOwner->getProfileUrl()}">
                            <img src="{$oUserOwner->getAvatarUrl('mini')}" {$oUserOwner->getAvatarImageSizeAttr('mini')}  alt="{$oUserOwner->getDisplayName()}"/>
                        </a>
                        <a href="{$oUserOwner->getProfileUrl()}">{$oUserOwner->getDisplayName()}</a>
                    </span>
                    {if $aBlogAdministrators}
                    {foreach $aBlogAdministrators as $oBlogUser}
                        {$oUser=$oBlogUser->getUser()}
                        <span class="user-avatar">
                            <a href="{$oUser->getProfileUrl()}"><img src="{$oUser->getAvatarUrl('mini')}" {$oUser->getAvatarImageSizeAttr('mini')}  alt="{$oUser->getDisplayName()}"/></a>
                            <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                        </span>
                    {/foreach}
                    {/if}<br/><br/>

                    <strong>{$aLang.blog_user_moderators} ({$iCountBlogModerators}):</strong><br/>
                    {if $aBlogModerators}
                        {foreach $aBlogModerators as $oBlogUser}
                            {$oUser=$oBlogUser->getUser()}
                            <span class="user-avatar">
                                <a href="{$oUser->getProfileUrl()}"><img src="{$oUser->getAvatarUrl('mini')}" {$oUser->getAvatarImageSizeAttr('mini')}  alt="{$oUser->getDisplayName()}"/></a>
                                <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                            </span>
                        {/foreach}
                    {else}
                        <span class="text-muted">{$aLang.blog_user_moderators_empty}</span>
                    {/if}
                </div>
            </div>

            {hook run='blog_info_end' oBlog=$oBlog}
        </footer>
    </div>
</div>

{hook run='blog_info' oBlog=$oBlog}

<div class="row nav-filter-wrapper">
    <div class="col-lg-12">
        <div class="blog-nav">
            <ul class="nav nav-pills pull-left">
                <li {if $sMenuSubItemSelect=='good'}class="active"{/if}><a href="{$sMenuSubBlogUrl}">{$aLang.blog_menu_collective_good}</a></li>

                <li {if $sMenuSubItemSelect=='new'}class="active"{/if}><a href="{$sMenuSubBlogUrl}newall/">{$aLang.blog_menu_collective_new}{if $iCountTopicsBlogNew>0} +{$iCountTopicsBlogNew}{/if}</a></li>

                <li class="dropdown{if $sMenuSubItemSelect=='discussed'} active{/if}">
                    <a href="{$sMenuSubBlogUrl}discussed/" class="dropdown-toggle" data-toggle="dropdown">
                        {$aLang.blog_menu_collective_discussed}
                        <b class="caret"></b>
                    </a>

                    <ul class="dropdown-menu">
                        <li {if $sMenuSubItemSelect=='discussed' & $sPeriodSelectCurrent=='1'}class="active"{/if}><a
                                    href="{$sMenuSubBlogUrl}discussed/?period=1">{$aLang.blog_menu_top_period_24h}</a>
                        </li>
                        <li {if $sMenuSubItemSelect=='discussed' & $sPeriodSelectCurrent=='7'}class="active"{/if}><a
                                    href="{$sMenuSubBlogUrl}discussed/?period=7">{$aLang.blog_menu_top_period_7d}</a>
                        </li>
                        <li {if $sMenuSubItemSelect=='discussed' & $sPeriodSelectCurrent=='30'}class="active"{/if}><a
                                    href="{$sMenuSubBlogUrl}discussed/?period=30">{$aLang.blog_menu_top_period_30d}</a>
                        </li>
                        <li {if $sMenuSubItemSelect=='discussed' & $sPeriodSelectCurrent=='all'}class="active"{/if}><a
                                    href="{$sMenuSubBlogUrl}discussed/?period=all">{$aLang.blog_menu_top_period_all}</a>
                        </li>
                    </ul>
                </li>

                {if C::Get('rating.enabled')}
                <li class="dropdown{if $sMenuSubItemSelect=='top'} active{/if}">
                    <a href="{$sMenuSubBlogUrl}top/" class="dropdown-toggle" data-toggle="dropdown">
                        {$aLang.blog_menu_collective_top}
                        <b class="caret"></b>
                    </a>

                    <ul class="dropdown-menu">
                        <li {if $sMenuSubItemSelect=='top' & $sPeriodSelectCurrent=='1'}class="active"{/if}><a
                                    href="{$sMenuSubBlogUrl}top/?period=1">{$aLang.blog_menu_top_period_24h}</a></li>
                        <li {if $sMenuSubItemSelect=='top' & $sPeriodSelectCurrent=='7'}class="active"{/if}><a
                                    href="{$sMenuSubBlogUrl}top/?period=7">{$aLang.blog_menu_top_period_7d}</a></li>
                        <li {if $sMenuSubItemSelect=='top' & $sPeriodSelectCurrent=='30'}class="active"{/if}><a
                                    href="{$sMenuSubBlogUrl}top/?period=30">{$aLang.blog_menu_top_period_30d}</a></li>
                        <li {if $sMenuSubItemSelect=='top' & $sPeriodSelectCurrent=='all'}class="active"{/if}><a
                                    href="{$sMenuSubBlogUrl}top/?period=all">{$aLang.blog_menu_top_period_all}</a></li>
                    </ul>
                </li>
                {/if}

                {hook run='menu_blog_blog_item'}
            </ul>
        </div>
    </div>
</div>

    {if $bCloseBlog}
        <div class="alert alert-danger">
            {$aLang.blog_close_show}
        </div>
    {else}
        {include file='topics/topic.list.tpl'}
    {/if}

{/block}
