{extends file="_index.tpl"}

{block name="layout_content"}

    {include file='menus/menu.talk.tpl'}

    {if $aTalks}
        <table class="table table-talk">
            <thead>
            <tr>
                <th class="cell-recipients">
                    <small>{$aLang.talk_inbox_target}</small>
                </th>
                <th class="cell-favourite"></th>
                <th class="cell-title">
                    <small>{$aLang.talk_inbox_title}</small>
                </th>
                <th class="cell-date ta-r">
                    <small>{$aLang.talk_inbox_date}</small>
                </th>
            </tr>
            </thead>

            <tbody>
            {foreach $aTalks as $oTalk}
                {$oTalkUserAuthor=$oTalk->getTalkUser()}
                <tr>
                    <td class="small text-muted">
                        {strip}
                            {$aTalkUserOther=[]}
                            {foreach $oTalk->getTalkUsers() as $oTalkUser}
                                {if $oTalkUser->getUserId()!=E::UserId()}
                                    {$aTalkUserOther[]=$oTalkUser}
                                {/if}
                            {/foreach}
                            {foreach $aTalkUserOther as $oTalkUser}
                                {$oUser=$oTalkUser->getUser()}
                                {if !$oTalkUser@first}, {/if}<a href="{$oUser->getProfileUrl()}"
                                                                class="user {if $oTalkUser->getUserActive()!=$TALK_USER_ACTIVE}inactive{/if}">{$oUser->getDisplayName()}</a>
                            {/foreach}
                        {/strip}
                    </td>
                    <td class="cell-favourite">
                        <a href="#" onclick="return ls.favourite.toggle({$oTalk->getId()},this,'talk');"
                           class="favourite {if $oTalk->getIsFavourite()}active{/if}"><span
                                    class="glyphicon glyphicon-star"></span></a>
                    </td>
                    <td>
                        {if $oTalkUserAuthor->getCommentCountNew() OR !$oTalkUserAuthor->getDateLast()}
                            <a href="{router page='talk'}read/{$oTalk->getId()}/"><strong>{$oTalk->getTitle()|escape:'html'}</strong></a>
                        {else}
                            <a href="{router page='talk'}read/{$oTalk->getId()}/">{$oTalk->getTitle()|escape:'html'}</a>
                        {/if}
                        &nbsp;
                        {if $oTalk->getCountComment()}
                            <span class="text-muted">({$oTalk->getCountComment()}{if $oTalkUserAuthor->getCommentCountNew()}
                                <span class="text-info">+{$oTalkUserAuthor->getCommentCountNew()}</span>{/if})</span>
                        {/if}
                        <p class="small text-muted">{$oTalk->getTextLast()|strip_tags|truncate:200:'...'|escape:'html'}</p>
                    </td>
                    <td class="small text-muted cell-date ta-r">{date_format date=$oTalk->getDateLast()}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    {else}
        <div class="alert alert-info notice-empty">{$aLang.talk_favourite_empty}</div>
    {/if}

    {include file='commons/common.pagination.tpl' aPaging=$aPaging}

{/block}
