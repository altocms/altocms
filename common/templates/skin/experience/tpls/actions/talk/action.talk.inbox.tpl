 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
    {$noShowSystemMessage=false}
{/block}

{block name="layout_content"}

    {include file='menus/menu.talk.tpl'}

    {if $aTalks}

        <form action="{router page='talk'}" method="post" id="form_talks_list">
            <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
            <input type="hidden" name="submit_talk_unread" id="form_talks_list_submit_unread" value=""/>
            <input type="hidden" name="submit_talk_read" id="form_talks_list_submit_read" value=""/>
            <input type="hidden" name="submit_talk_del" id="form_talks_list_submit_del" value=""/>

            <div class="row">
                <div class="col-lg-24 user-toggle-publication-block">
                    <a class="btn btn-light-gray " href="#" onclick="return ls.talk.makeReadTalks();">{$aLang.talk_inbox_make_read}</a>
                    <a class="btn btn-light-gray " href="#" onclick="return ls.talk.makeUnreadTalks();">{$aLang.talk_inbox_make_un_read}</a>
                    <a class="btn btn-light-gray " href="#" onclick="return ls.talk.removeTalks();">{$aLang.talk_inbox_delete}</a>
                </div>
            </div>


            <div class="panel panel-default panel-table raised">
                <div class="panel-body">

                    <table class="table table-talk">
                <thead>
                <tr>
                    <script>
                        $(function(){
                            $('#ch-controls').on('ifChecked', function(e) {
                                $('.form_talks_checkbox').iCheck('check');
                            }).on('ifUnchecked', function(e) {
                                $('.form_talks_checkbox').iCheck('uncheck');
                            })
                        })
                    </script>
                    <th class="cell-checkbox"><input type="checkbox" name="" id="ch-controls" class="input-checkbox"
                                                     onclick="

                                                     ls.tools.checkAll('form_talks_checkbox', this, true);">
                    </th>
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
                        <td class="cell-checkbox">
                            <input type="checkbox" name="talk_select[{$oTalk->getId()}]" class="form_talks_checkbox input-checkbox"/>
                        </td>
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
                                    {if !$oTalkUser@first}, {/if}
                                    <span class="nowrap">
                                        <img src="{$oUser->getAvatarUrl('mini')}" {$oUser->getAvatarImageSizeAttr('mini')} alt="{$oUser->getDisplayName()}"/>&nbsp;
                                    <a href="{$oUser->getProfileUrl()}" class="user {if $oTalkUser->getUserActive()!=$TALK_USER_ACTIVE}inactive{/if}">{$oUser->getDisplayName()}</a>
                                    </span>
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

                            {strip}
                                <a href="{router page='talk'}read/{$oTalk->getId()}/" class="js-title-talk link link-lead link-clear"
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
                                <span class="text text-muted small">({$oTalk->getCountComment()}{if $oTalkUserAuthor->getCommentCountNew()}
                                    <span class="text text-info small">+{$oTalkUserAuthor->getCommentCountNew()}</span>{/if})</span>
                            {/if}
                            {if E::UserId()==$oTalk->getUserIdLast()}
                                <span class="text-success small"><i class="fa fa-chevron-right"></i></span>
                            {else}
                                <span class="text-danger small"><i class="fa fa-chevron-left"></i></span>
                            {/if}
                            <p class="text-muted text small">{$oTalk->getTextLast()|strip_tags|truncate:200:'...'|escape:'html'}</p>
                        </td>
                        <td class="text-muted cell-date tac text small">{date_format date=$oTalk->getDateLast() format="j F Y, H:i"}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>

                </div>
            </div>
        </form>
    {else}
        <div class="bg-warning">{$aLang.talk_inbox_empty}</div>
    {/if}

    {include file='commons/common.pagination.tpl' aPaging=$aPaging}

{/block}
