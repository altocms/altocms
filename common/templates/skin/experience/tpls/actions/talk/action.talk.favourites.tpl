 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
{/block}

{block name="layout_content"}

    {include file='menus/menu.talk.tpl'}

    {if $aTalks}
<div class="panel panel-default panel-table raised">
    <div class="panel-body">

        <table class="table table-talk">

            <thead>
            <tr>
                <th class="cell-recipients">
                    {$aLang.talk_inbox_target}
                </th>
                <th class="cell-favourite"></th>
                <th class="cell-title">
                    {$aLang.talk_inbox_title}
                </th>
                <th class="cell-date ta-r">
                    {$aLang.talk_inbox_date}
                </th>
            </tr>
            </thead>

            <tbody>
            {foreach $aTalks as $oTalk}
                {$oTalkUserAuthor=$oTalk->getTalkUser()}
                <tr>
                    <td class="text-muted">
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
                           class="muted favourite {if $oTalk->getIsFavourite()}active{/if}">
                            {if $oTalk->getIsFavourite()}<i class="fa fa-star"></i>{else}<i class="fa fa-star-o"></i>{/if}
                        </a>
                    </td>
                    <td>
                        {if $oTalkUserAuthor->getCommentCountNew() OR !$oTalkUserAuthor->getDateLast()}
                            <a class="link link-lead link-clear" href="{router page='talk'}read/{$oTalk->getId()}/"><strong>{$oTalk->getTitle()|escape:'html'}</strong></a>
                        {else}
                            <a class="link link-lead link-clear" href="{router page='talk'}read/{$oTalk->getId()}/">{$oTalk->getTitle()|escape:'html'}</a>
                        {/if}
                        &nbsp;
                        {if $oTalk->getCountComment()}
                            <span class="text text-info small">({$oTalk->getCountComment()}{if $oTalkUserAuthor->getCommentCountNew()}
                                <span class="text text-info small">+{$oTalkUserAuthor->getCommentCountNew()}</span>{/if})</span>
                        {/if}
                        <p class="text text-muted small">{$oTalk->getTextLast()|strip_tags|truncate:200:'...'|escape:'html'}</p>
                    </td>
                    <td class="text-muted cell-date tac text small">{date_format date=$oTalk->getDateLast()}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
        </div>
    </div>
    {else}
        <div class="bg-warning">{$aLang.talk_favourite_empty}</div>
    {/if}

    {include file='commons/common.pagination.tpl' aPaging=$aPaging}

{/block}
