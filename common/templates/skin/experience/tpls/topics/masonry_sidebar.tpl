 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}


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
    <div class="panel panel-default topic masonry masonry-lead raised topic-type_{$oTopic->getType()} js-topic">
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
                <li class="topic-user">
                    <img src="{$oUser->getAvatarUrl(16)}" alt="{$oUser->getDisplayName()}"/>
                    <a class="userlogo link link-dual link-lead link-clear js-popup-{$oUser->getId()}" href="{$oUser->getProfileUrl()}">
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

                <li class="pull-right topic-rating js-vote end marr0" data-target-type="topic" data-target-id="{$oTopic->getId()}">
                    <a href="#" onclick="return false;" class="vote-down link link-gray link-clear js-vote-down"><i class="fa fa-thumbs-o-down"></i></a>
                    {if $bVoteInfoShow}
                        <span class="vote-total js-vote-rating {$sVoteClass}">{if $oTopic->getRating() > 0}+{/if}{$oTopic->getRating()}</span>
                    {else}
                        <a href="#" class="vote-down link link-gray link-clear" onclick="return ls.vote.vote({$oTopic->getId()},this,0,'topic');">?</a>
                    {/if}
                    <a href="#" onclick="return false;" class="vote-up link link link-gray link-clear js-vote-up"><i class="fa fa-thumbs-o-up"></i></a>

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
            </ul>
        </div>

    </div>
</div>


