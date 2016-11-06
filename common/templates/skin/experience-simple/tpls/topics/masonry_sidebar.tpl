 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}


    {$oBlog=$oTopic->getBlog()}
    {$oUser=$oTopic->getUser()}
    {$oVote=$oTopic->getVote()}
    {$oFavourite=$oTopic->getFavourite()}
    {$oContentType=$oTopic->getContentType()}

    {if Config::Get('view.masonry') && !Config::Get('view.masonry_sidebar') && in_array(Router::GetAction(), Config::Get('view.masonry_sidebar_pages'))}{$isMasonry = true}{/if}

    {if Config::Get('view.masonry') && $isMasonry}
        {if $bLead==1}
            <div class="masonry-item col-xs-24 col-sm-24 col-md-16 col-lg-12">
        {else}
            <div class="masonry-item col-xs-24 col-sm-12 col-md-8 col-lg-6">
        {/if}
    {elseif Config::Get('view.masonry') }
        {if $bLead==1}
            <div class="masonry-item col-xs-24 col-sm-24 col-md-24 col-lg-24">
        {else}
            <div class="masonry-item col-xs-24 col-sm-24 col-md-12 col-lg-12">
        {/if}
    {/if}

    {if !$bPreview}
        <span class="pull-right masonry-controls">
            {if E::IsAdmin() OR E::UserId()==$oTopic->getUserId() OR E::UserId()==$oBlog->getOwnerId() OR $oBlog->getUserIsAdministrator() OR $oBlog->getUserIsModerator()}
                <a href="{router page='content'}edit/{$oTopic->getId()}/" title="{$aLang.topic_edit}" class="small link link-lead link-dark link-clear">
                    <i class="fa fa-pencil"></i>
                </a>
                {if E::IsAdmin() OR $oBlog->getUserIsAdministrator() OR $oBlog->getOwnerId()==E::UserId()}
                &nbsp;<a href="#" class="small link link-lead link-clear link-red-blue" title="{$aLang.topic_delete}"
                         onclick="ls.topic.remove('{$oTopic->getId()}', '{$oTopic->getTitle()}'); return false;">
                    <i class="fa fa-times"></i>
                </a>
            {/if}
            {/if}
        </span>
    {/if}


    <!-- Блок топика -->
    <div class="panel panel-default topic masonry masonry-lead flat topic-type_{$oTopic->getType()} js-topic">
        {$iMainPhotoId = $oTopic->getPhotosetMainPhotoId()}
        {if $iMainPhotoId}
            {$aPhotos = $oTopic->getPhotosetPhotos()}
            {foreach $aPhotos as $oPhoto}
                {if $oPhoto->getId() == $iMainPhotoId}
                    <img src="{$oPhoto->getUrl('x460')}" alt="{$oPhoto->getDescription()}" class="" />
                    {continue}
                {/if}
            {/foreach}
        {/if}


        <div class="panel-body">
            <div class="topic-info">
                <span class="topic-blog">
                    <a class="link link-lead link-blue" href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a>
                </span>
            </div>

            <h6 class="topic-title accent">
                <a class="link link-header link-lead" href="{$oTopic->getUrl()}">
                    {$oTopic->getTitle()|escape:'html'}</a>
            </h6>

            <div class="topic-text">
                {$oTopic->getTextShort()}
            </div>

        </div>
        <div class="topic-footer">
            <ul>
                <li class="topic-user js-popover-{$oUser->getId()}">
                    <img src="{$oUser->getAvatarUrl('mini')}" {$oUser->getAvatarImageSizeAttr('mini')} alt="{$oUser->getDisplayName()}"/>
                    <a class="userlogo link link-dual link-lead link-clear" href="{$oUser->getProfileUrl()}">
                        {$oUser->getDisplayName()}
                    </a>
                </li>
                <li class="topic-favourite">
                    <a class="link link-dark link-lead link-clear {if E::IsUser() AND $oTopic->getIsFavourite()}active{/if}"
                       onclick="return ls.favourite.toggle({$oTopic->getId()},this,'topic');"
                       href="#">
                        <i class="fa fa-star"></i>
                        <span class="favourite-count" id="fav_count_topic_{$oTopic->getId()}">{$oTopic->getCountFavourite()}</span>
                    </a>
                </li>

                {hook run='topic_show_info' topic=$oTopic bTopicList=false bSidebar=true oVote=$oVote}
            </ul>
        </div>

    </div>
</div>


