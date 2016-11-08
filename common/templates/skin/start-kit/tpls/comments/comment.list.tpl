<div class="comments comment-list">
    {foreach $aComments as $oComment}
        {$oUser=$oComment->getUser()}
        {$oTopic=$oComment->getTarget()}

        {if $oUser AND $oTopic}
        {$oBlog=$oTopic->getBlog()}

        <div class="small text-muted comment-path">
            <a href="{$oBlog->getUrlFull()}" class="blog-name">{$oBlog->getTitle()|escape:'html'}</a> &rarr;
            <a href="{$oTopic->getUrl()}" class="comment-path-topic">{$oTopic->getTitle()|escape:'html'}</a>
            <a href="{$oTopic->getUrl()}#comments" class="comment-path-comments">({$oTopic->getCountComment()})</a>
        </div>

        <section class="comment">
            <a href="{$oUser->getProfileUrl()}" class="comment-avatar js-popover-user-{$oUser->getId()}">
                <img src="{$oUser->getAvatarUrl('medium')}" {$oUser->getAvatarImageSizeAttr('medium')}  alt="{$oUser->getDisplayName()}"  />
            </a>

            <ul class="list-unstyled list-inline small comment-info">
                <li class="comment-info-author"><a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a></li>
                <li class="comment-info-date">
                    <a href="{if Config::Get('module.comment.nested_per_page')}{router page='comments'}{else}{$oTopic->getUrl()}#comment{/if}{$oComment->getId()}" class="link-dotted" title="{$aLang.comment_url_notice}">
                        <time datetime="{date_format date=$oComment->getDate() format='c'}">{date_format date=$oComment->getDate() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}</time>
                    </a>
                </li>
                {if E::IsUser() AND !$bNoCommentFavourites}
                    <li class="comment-favourite">
                        <div onclick="return ls.favourite.toggle({$oComment->getId()},this,'comment');" class="favourite {if $oComment->getIsFavourite()}active{/if}"><span class="glyphicon glyphicon-star"></span></div>
                        <span class="text-muted favourite-count" id="fav_count_comment_{$oComment->getId()}">{if $oComment->getCountFavourite() > 0}{$oComment->getCountFavourite()}{/if}</span>
                    </li>
                {/if}
                {hook run="comment_list_info" oComment=$oComment}
            </ul>

            <div class="comment-content text">
                {if $oComment->isBad()}
                    {$oComment->getText()}
                {else}
                    {$oComment->getText()}
                {/if}
            </div>
        </section>
        {/if}
    {/foreach}
</div>

{include file='commons/common.pagination.tpl' aPaging=$aPaging}
