{extends file="_index.tpl"}

{block name="layout_content"}
    <div class="action-header">
        {include file='menus/menu.talk.tpl'}
    </div>
    {$oUser=$oTalk->getUser()}

    <article class="topic topic-type-talk">
        <header class="topic-header">
            <h1 class="topic-header-title">{$oTalk->getTitle()|escape:'html'}</h1>

            <div class="topic-header-info">
                <ul class="list-unstyled list-inline small pull-right actions">
                    <li><span class="glyphicon glyphicon-cog actions-tool"></span></li>
                    <li class="delete"><a href="#"
                                          onclick="return ls.talk.removeMessage('{$oTalk->getId()}');"
                                          class="actions-delete">{$aLang.delete}</a></li>
                </ul>
            </div>
        </header>

        <div class="topic-content text">
            {$oTalk->getText()}
        </div>

        {include file='actions/talk/action.talk.speakers.tpl'}

        <footer class="small topic-footer">
            <ul class="text-muted list-unstyled list-inline topic-footer-info">
                <li class="topic-info-author">
                    <a href="{$oUser->getProfileUrl()}"><img src="{$oUser->getAvatarUrl(24)}" alt="avatar" class="avatar"/></a>
                    <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                </li>
                <li class="topic-info-date">
                    <time datetime="{date_format date=$oTalk->getDate() format='c'}" pubdate class="text-muted">
                        {date_format date=$oTalk->getDate() format="j F Y, H:i"}
                    </time>
                </li>
                <li class="topic-info-favourite"><a href="#"
                                                    onclick="return ls.favourite.toggle({$oTalk->getId()},this,'talk');"
                                                    class="favourite {if $oTalk->getIsFavourite()}active{/if}"><span
                                class="glyphicon glyphicon-star"></span></a></li>
                {hook run='talk_read_info_item' talk=$oTalk}
            </ul>
        </footer>
    </article>

    {$oTalkUser=$oTalk->getTalkUser()}

    {if !$bNoComments}
        {include
        file='comments/comment.tree.tpl'
        iTargetId=$oTalk->getId()
        sTargetType='talk'
        iCountComment=$oTalk->getCountComment()
        sDateReadLast=$oTalkUser->getDateLast()
        sNoticeCommentAdd=$aLang.topic_comment_add
        bNoCommentFavourites=true
        }
    {/if}

{/block}
