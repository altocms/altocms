{extends file="_index.tpl"}

{block name="layout_vars"}
    {$noShowSystemMessage=false}
{/block}

{block name="layout_content"}

    {include file='menus/menu.talk.tpl'}

    {if $aTalks}
        {include file='actions/talk/action.talk.filter.tpl'}
        <form action="{router page='talk'}" method="post" id="form_talks_list">
            <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
            <input type="hidden" name="submit_talk_read" id="form_talks_list_submit_read" value=""/>
            <input type="hidden" name="submit_talk_del" id="form_talks_list_submit_del" value=""/>

            <button type="submit" onclick="ls.talk.makeReadTalks()"
                    class="btn btn-default">{$aLang.talk_inbox_make_read}</button>
            <button type="submit" onclick="return ls.talk.removeTalks();"
                    class="btn btn-default">{$aLang.talk_inbox_delete}</button>
            <br/><br/>

            <table class="table table-talk">
                <thead>
                <tr>
                    <th class="cell-checkbox"><input type="checkbox" name="" class="input-checkbox"
                                                     onclick="ls.tools.checkAll('form_talks_checkbox', this, true);">
                    </th>
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
                        <td class="cell-checkbox"><input type="checkbox" name="talk_select[{$oTalk->getId()}]"
                                                         class="form_talks_checkbox input-checkbox"/></td>
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
                                    {if !$oTalkUser@first}, {/if}
                                    <a href="{$oUser->getProfileUrl()}" class="user {if $oTalkUser->getUserActive()!=$TALK_USER_ACTIVE}inactive{/if}">{$oUser->getDisplayName()}</a>
                                {/foreach}
                            {/strip}
                        </td>
                        <td class="cell-favourite">
                            <a href="#" onclick="return ls.favourite.toggle({$oTalk->getId()},this,'talk');"
                               class="muted favourite {if $oTalk->getIsFavourite()}active{/if}"><span
                                        class="glyphicon glyphicon-star"></span></a>
                        </td>
                        <td>
                            {strip}
                                <a href="{router page='talk'}read/{$oTalk->getId()}/" class="js-title-talk"
                                   title="{$oTalk->getTextLast()|strip_tags|truncate:100:'...'|escape:'html'}">
                                    {if $oTalkUserAuthor->getCommentCountNew() OR !$oTalkUserAuthor->getDateLast()}
                                        <strong>{$oTalk->getTitle()|escape:'html'}</strong>
                                    {else}
                                        {$oTalk->getTitle()|escape:'html'}
                                    {/if}
                                </a>
                            {/strip}
                            &nbsp;
                            {if $oTalk->getCountComment()}
                                <span class="text-muted">({$oTalk->getCountComment()}{if $oTalkUserAuthor->getCommentCountNew()}
                                    <span class="text-info">+{$oTalkUserAuthor->getCommentCountNew()}</span>{/if})</span>
                            {/if}
                            {if E::UserId()==$oTalk->getUserIdLast()}
                                <span class="text-success">&rarr;</span>
                            {else}
                                <span class="text-danger">&larr;</span>
                            {/if}
                            <p class="small text-muted">{$oTalk->getTextLast()|strip_tags|truncate:200:'...'|escape:'html'}</p>
                        </td>
                        <td class="small text-muted cell-date ta-r">{date_format date=$oTalk->getDate() format="j F Y, H:i"}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </form>
    {else}
        <div class="alert alert-info notice-empty">{$aLang.talk_inbox_empty}</div>
    {/if}

    {include file='commons/common.pagination.tpl' aPaging=$aPaging}

{/block}
