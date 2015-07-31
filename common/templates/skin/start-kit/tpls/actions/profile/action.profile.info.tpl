{extends file="_profile.tpl"}

{block name="layout_vars" append}
    {$sMenuItemSelect="profile"}
{/block}

{block name="layout_profile_content"}

{if $oUserProfile->getProfileAbout()}
    <div class="well profile-info-about">
        <h3>{$aLang.profile_about}</h3>
        {$oUserProfile->getProfileAbout()}
    </div>
{/if}

<div class="profile-content">

<div class="row">

    <div class="col-lg-6">
        {$aUserFieldValues=$oUserProfile->getUserFieldValues(true)}
        {if $oUserProfile->getProfileSex()!='other' OR $oUserProfile->getProfileBirthday() OR $oGeoTarget OR $oUserProfile->getProfileAbout() OR count($aUserFieldValues)}
            <h4>{$aLang.profile_privat}</h4>
            <table class="table table-profile-info">
                {if $oUserProfile->getProfileSex()!='other'}
                    <tr>
                        <td class="text-muted cell-label">{$aLang.profile_sex}:</td>
                        <td>
                            {if $oUserProfile->getProfileSex()=='man'}
                                {$aLang.profile_sex_man}
                            {else}
                                {$aLang.profile_sex_woman}
                            {/if}
                        </td>
                    </tr>
                {/if}


                {if $oUserProfile->getProfileBirthday()}
                    <tr>
                        <td class="text-muted cell-label">{$aLang.profile_birthday}:</td>
                        <td>{date_format date=$oUserProfile->getProfileBirthday() format="j F Y"}</td>
                    </tr>
                {/if}


                {if $oGeoTarget}
                    <tr>
                        <td class="text-muted cell-label">{$aLang.profile_place}:</td>
                        <td itemprop="address" itemscope itemtype="http://data-vocabulary.org/Address">
                            {if $oGeoTarget->getCountryId()}
                                <a href="{router page='people'}country/{$oGeoTarget->getCountryId()}/"
                                   itemprop="country-name">{$oUserProfile->getProfileCountry()|escape:'html'}</a>{if $oGeoTarget->getCityId()},{/if}
                            {/if}

                            {if $oGeoTarget->getCityId()}
                                <a href="{router page='people'}city/{$oGeoTarget->getCityId()}/"
                                   itemprop="locality">{$oUserProfile->getProfileCity()|escape:'html'}</a>
                            {/if}
                        </td>
                    </tr>
                {/if}

                {hook run='profile_whois_privat_item' oUserProfile=$oUserProfile}
            </table>
        {/if}

        {hook run='profile_whois_item_after_privat' oUserProfile=$oUserProfile}
    </div>

    <div class="col-lg-6">
        {$aUserFieldContactValues=$oUserProfile->getUserFieldValues(true, array('contact'))}
        {if $aUserFieldContactValues}
            <h4>{$aLang.profile_contacts}</h4>
            <table class="table table-profile-info">
                {foreach $aUserFieldContactValues as $oField}
                    <tr>
                        <td class="text-muted cell-label">
                            <span class="icon-contact icon-contact-{$oField->getName()}"></span>
                            {$oField->getTitle()|escape:'html'}:
                        </td>
                        <td>{$oField->getValue(true,true)}</td>
                    </tr>
                {/foreach}
            </table>
        {/if}

        {$aUserFieldContactValues=$oUserProfile->getUserFieldValues(true, array('social'))}
        {if $aUserFieldContactValues}
            <h4>{$aLang.profile_social}</h4>
            <table class="table table-profile-info">
                {foreach $aUserFieldContactValues as $oField}
                    <tr>
                        <td class="text-muted cell-label">
                            <span class="icon-contact icon-contact-{$oField->getName()}"></span>
                            {$oField->getTitle()|escape:'html'}:
                        </td>
                        <td>{$oField->getValue(true,true)}</td>
                    </tr>
                {/foreach}
            </table>
        {/if}
    </div>

</div>

{hook run='profile_whois_item' oUserProfile=$oUserProfile}

<h4>{$aLang.profile_activity}</h4>

<table class="table table-profile-info">

    {if Config::Get('general.reg.invite') AND $oUserInviteFrom}
        <tr>
            <td class="text-muted cell-label">{$aLang.profile_invite_from}:</td>
            <td>
                <a href="{$oUserInviteFrom->getProfileUrl()}">{$oUserInviteFrom->getDisplayName()}</a>&nbsp;
            </td>
        </tr>
    {/if}

    {if Config::Get('general.reg.invite') AND $aUsersInvite}
        <tr>
            <td class="text-muted cell-label">{$aLang.profile_invite_to}:</td>
            <td>
                {foreach $aUsersInvite as $oUserInvite}
                    <a href="{$oUserInvite->getProfileUrl()}">{$oUserInvite->getDisplayName()}</a>
                    &nbsp;
                {/foreach}
            </td>
        </tr>
    {/if}

    {if $aBlogsOwner}
        <tr>
            <td class="text-muted cell-label">{$aLang.profile_blogs_self}:</td>
            <td>
                {foreach $aBlogsOwner as $oBlog}
                    <a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a>{if !$oBlog@last}, {/if}
                {/foreach}
            </td>
        </tr>
    {/if}

    {if $aBlogAdministrators}
        <tr>
            <td class="text-muted cell-label">{$aLang.profile_blogs_administration}:</td>
            <td>
                {foreach $aBlogAdministrators as $oBlogUser}
                    {$oBlog=$oBlogUser->getBlog()}
                    <a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a>{if !$oBlogUser@last}, {/if}
                {/foreach}
            </td>
        </tr>
    {/if}

    {if $aBlogModerators}
        <tr>
            <td class="text-muted cell-label">{$aLang.profile_blogs_moderation}:</td>
            <td>
                {foreach $aBlogModerators as $oBlogUser}
                    {$oBlog=$oBlogUser->getBlog()}
                    <a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a>{if !$oBlogUser@last}, {/if}
                {/foreach}
            </td>
        </tr>
    {/if}

    {if $aBlogUsers}
        <tr>
            <td class="text-muted cell-label">{$aLang.profile_blogs_join}:</td>
            <td>
                {foreach $aBlogUsers as $oBlogUser}
                    {$oBlog=$oBlogUser->getBlog()}
                    <a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a>{if !$oBlogUser@last}, {/if}
                {/foreach}
            </td>
        </tr>
    {/if}

    {hook run='profile_whois_activity_item' oUserProfile=$oUserProfile}

    <tr>
        <td class="text-muted cell-label">{$aLang.profile_date_registration}:</td>
        <td>{date_format date=$oUserProfile->getDateRegister()}</td>
    </tr>

    {if $oSession}
        <tr>
            <td class="text-muted cell-label">{$aLang.profile_date_last}:</td>
            <td>{date_format date=$oSession->getDateLast()}</td>
        </tr>
    {/if}
</table>

{if $aUsersFriend}
    <h4><a href="{$oUserProfile->getProfileUrl()}friends/" class="user-friends">{$aLang.profile_friends}</a>
        <span class="text-muted">({$iCountFriendsUser})<span></h4>
    {include file='commons/common.user_list_avatar.tpl' aUsersList=$aUsersFriend}
{/if}

{hook run='profile_whois_item_end' oUserProfile=$oUserProfile}

</div>
{/block}
