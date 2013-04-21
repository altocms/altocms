{extends file='actions/ActionAdmin/users/users_profile.tpl'}

{block name="content-body-main"}
    {assign var="oSession" value=$oUserProfile->getSession()}
    {assign var="oVote" value=$oUserProfile->getVote()}

<div class="profile-user -box">

<h4 class="title">{$oLang->profile_privat}</h4>
<table class="table">
    {if $oUserProfile->getProfileSex()!='other'}
        <tr>
            <td class="adm_var">{$oLang->profile_sex}:</td>
            <td>
                {if $oUserProfile->getProfileSex()=='man'}
                {$oLang->profile_sex_man}
                {else}
                {$oLang->profile_sex_woman}
                {/if}
            </td>
        </tr>
    {/if}

    <tr>
        <td class="adm_var">{$oLang->profile_birthday}:</td>
        <td>{$oUserProfile->getProfileBirthday()}</td>
    </tr>

    <tr>
        <td class="adm_var">{$oLang->profile_place}:</td>
        <td>
            {if $oUserProfile->getProfileCountry()}
                <a href="{router page='people'}country/{$oUserProfile->getProfileCountry()|escape:'html'}/">{$oUserProfile->getProfileCountry()|escape:'html'}</a>{if $oUserProfile->getProfileCity()}
                ,{/if}
            {/if}
            {if $oUserProfile->getProfileCity()}
                <a href="{router page='people'}city/{$oUserProfile->getProfileCity()|escape:'html'}/">{$oUserProfile->getProfileCity()|escape:'html'}</a>
            {/if}
        </td>
    </tr>

    <tr>
        <td class="adm_var" id="user_profile_about_label">{$oLang->profile_about}:</td>
        <td class="adm_field">
            <button class="btn btn-mini"
                    onclick="admin.user.editField('user_profile_about', this, 'text'); return false;">
                <i class="icon-edit"></i>
            </button>
            <span id="user_profile_about_view"
                  class="adm_field_value">{$oUserProfile->getProfileAbout()|escape:'html'}</span>
        </td>
    </tr>

    <tr>
        <td class="adm_var" id="user_profile_site_label">{$oLang->profile_site}:</td>
        <td class="adm_field">
            <button class="btn btn-mini"
                    onclick="admin.user.editField('user_profile_site', this, 'url'); return false;">
                <i class="icon-edit"></i>
            </button>
            <span id="user_profile_site_view" class="adm_field_value">
                <a href="{$oUserProfile->getProfileSite(true)|escape:'hex'}">
                    {$oUserProfile->getProfileSite(true)|escape:'html'}
                </a>
            </span>

        </td>
    </tr>

    <tr>
        <td class="adm_var" id="user_profile_email_label">{$oLang->settings_profile_mail}:</td>
        <td class="adm_field">
            <button class="btn btn-mini"
                    onclick="admin.user.editField('user_profile_email', this, 'email'); return false;">
                <i class="icon-edit"></i>
            </button>

            <span id="user_profile_email_view">
                <a href="mailto:{$oUserProfile->getMail()|escape:'hex'}">
                    {$oUserProfile->getMail()|escape:'html'}
                </a>
            </span>
        </td>
    </tr>

</table>
<input type="hidden" id="user_id" value="{$oUserProfile->getId()}"/>

    <div class="navbar navbar-inner fix-on-bottom">
                <button type="submit" id="edit_submit" name="edit_submit"
                        class="btn btn-primary pull-right" onclick="admin.user.submitData();" disabled="disabled">
                    {$aLang.action.admin.save}
                </button>
    </div>

<h4 class="title">{$oLang->profile_activity}</h4>
<table class="table">
    <tr>
        <td class="adm_var">{$oLang->profile_date_registration}:</td>
        <td>{date_format date=$oUserProfile->getDateRegister()} (ip:{$oUserProfile->getIpRegister()})</td>
    </tr>
    <tr>
        <td class="adm_var">{$oLang->profile_date_last}:</td>
        <td>
            {if $oSession}
                {date_format date=$oSession->getDateLast()} (ip:{$oSession->getIpLast()})
            {/if}
        </td>
    </tr>

    <tr>
        <td class="adm_var">{$oLang->profile_friends}:</td>
        <td class="friends">
            {if $aUsersFrend}
                {foreach from=$aUsersFrend item=oUserFrend}
                    <a href="{$oUserFrend->getUserWebPath()}">{$oUserFrend->getLogin()}</a>&nbsp;
                {/foreach}
            {/if}
        </td>
    </tr>

    <tr>
        <td class="adm_var">{$oLang->profile_friends_self}:</td>
        <td class="friends">
            {if $aUsersSelfFrend}
                {foreach from=$aUsersSelfFrend item=oUserFrend}
                    <a href="{$oUserFrend->getUserWebPath()}">{$oUserFrend->getLogin()}</a>&nbsp;
                {/foreach}
            {/if}
        </td>
    </tr>

    {if Config::Get('general.reg.invite') and $oUserInviteFrom}
        <tr>
            <td class="adm_var">{$oLang->profile_invite_from}:</td>
            <td class="friends">
                <a href="{$oUserInviteFrom->getUserWebPath()}">{$oUserInviteFrom->getLogin()}</a>&nbsp;
            </td>
        </tr>
    {/if}

    {if Config::Get('general.reg.invite') and $aUsersInvite}
        <tr>
            <td class="adm_var">{$oLang->profile_invite_to}:</td>
            <td class="friends">
                {foreach from=$aUsersInvite item=oUserInvite}
                    <a href="{$oUserInvite->getUserWebPath()}">{$oUserInvite->getLogin()}</a>&nbsp;
                {/foreach}
            </td>
        </tr>
    {/if}

    <tr>
        <td class="adm_var">{$oLang->profile_blogs_self}:</td>
        <td>
            {if $aBlogsOwner}
                {foreach from=$aBlogsOwner item=oBlog name=blog_owner}
                    <a href="{router page='blog'}{$oBlog->getUrl()}/">{$oBlog->getTitle()|escape:'html'}</a>{if !$smarty.foreach.blog_owner.last}
                    , {/if}
                {/foreach}
                {else}
                {$aLang.action.admin.word_no}
            {/if}
        </td>
    </tr>

    <tr>
        <td class="adm_var">{$oLang->profile_blogs_administration}:</td>
        <td>
            {if $aBlogsAdministration}
                {foreach from=$aBlogsAdministration item=oBlogUser name=blog_user}
                    {assign var="oBlog" value=$oBlogUser->getBlog()}
                    <a href="{$oBlog->getUrlFull()}">{$oBlog->getBlogTitle()|escape:'html'}</a>{if !$smarty.foreach.blog_user.last}
                    , {/if}
                {/foreach}
                {else}
                {$aLang.action.admin.word_no}
            {/if}
        </td>
    </tr>

    <tr>
        <td class="adm_var">{$oLang->profile_blogs_moderation}:</td>
        <td>
            {if $aBlogsModeration}
                {foreach from=$aBlogsModeration item=oBlogUser name=blog_user}
                    {assign var="oBlog" value=$oBlogUser->getBlog()}
                    <a href="{$oBlog->getUrlFull()}">{$oBlog->getBlogTitle()|escape:'html'}</a>{if !$smarty.foreach.blog_user.last}
                    , {/if}
                {/foreach}
                {else}
                {$aLang.action.admin.word_no}
            {/if}
        </td>
    </tr>

    <tr>
        <td class="adm_var">{$oLang->profile_blogs_join}:</td>
        <td>
            {if $aBlogsUser}
                {foreach from=$aBlogsUser item=oBlogUser name=blog_user}
                    {assign var="oBlog" value=$oBlogUser->getBlog()}
                    <a href="{$oBlog->getUrlFull()}">{$oBlog->getBlogTitle()|escape:'html'}</a>{if !$smarty.foreach.blog_user.last}
                    , {/if}
                {/foreach}
                {else}
                {$aLang.action.admin.word_no}
            {/if}
        </td>
    </tr>

    <tr>
        <td class="adm_var">{$aLang.action.admin.user_wrote_topics}:</td>
        <td>
            {if $oUserProfile->GetCountTopics()}{$oUserProfile->GetCountTopics()}{else}0{/if}
            {if $aLastTopicList}
                (
                {foreach from=$aLastTopicList item=oTopic name=topic_user}
                    <a href="{router page='blog'}{$oTopic->getId()}.html">{$oTopic->getTitle()|escape:'html'}</a>{if !$smarty.foreach.topic_user.last}
                    , {/if}
                {/foreach}
                )
            {/if}
        </td>
    </tr>

    <tr>
        <td class="adm_var">{$aLang.action.admin.user_wrote_comments}:</td>
        <td>
            {if $oUserProfile->GetCountComments()}{$oUserProfile->GetCountComments()}{else}0{/if}
        </td>
    </tr>

</table>
</div>
{/block}