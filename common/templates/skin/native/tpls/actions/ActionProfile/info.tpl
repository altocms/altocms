{**
 * Профиль пользователя с информацией о нем
 *
 * @styles css/profile.css
 * @styles css/tables.css
 *}

{extends file='[layouts]layout.user.tpl'}

{block name='layout_options' append}
    {$oSession = $oUserProfile->getSession()}
    {$oGeoTarget = $oUserProfile->getGeoTarget()}
{/block}

{block name='layout_user_page_title'}{$aLang.user_menu_profile_whois}{/block}

{block name='layout_content'}
    {include file='nav.user.info.tpl'}

    {hook run='user_info_begin' oUserProfile=$oUserProfile}

    {$aUserFieldValues = $oUserProfile->getUserFieldValues(true,array(''))}
    <div class="profile-wrapper">

        <!-- О себе -->
        {if $oUserProfile->getProfileAbout()}
            <h2 class="header-table">{$aLang.profile_about}</h2>
            <p>{$oUserProfile->getProfileAbout()}</p>
        {/if}
        <!-- /О себе -->

        {hook run='user_info_about_after' oUserProfile=$oUserProfile}

        <!-- Личные данные -->
        {if $oUserProfile->getProfileSex()!='other' || $oUserProfile->getProfileBirthday() || $oGeoTarget || count($aUserFieldValues)}
            <h2 class="header-table">{$aLang.profile_privat}</h2>
            <ul class="profile-contacts">
                {if $oUserProfile->getProfileSex()!='other'}
                    <li>
                        <em>{$aLang.profile_sex}</em>
						<span>
							{if $oUserProfile->getProfileSex()=='man'}
                                {$aLang.profile_sex_man}
                            {else}
                                {$aLang.profile_sex_woman}
                            {/if}
						</span>
                    </li>
                {/if}

                {if $oUserProfile->getProfileBirthday()}
                    <li>
                        <em>{$aLang.profile_birthday}</em>
						<span>
							{date_format date=$oUserProfile->getProfileBirthday() format="j F Y" notz=true}
						</span>
                    </li>
                {/if}

                {if $oGeoTarget}
                    <li>
                        <em>{$aLang.profile_place}</em>
						<span itemprop="address" itemscope itemtype="http://data-vocabulary.org/Address">
							{if $oGeoTarget->getCountryId()}
                                <a href="{router page='people'}country/{$oGeoTarget->getCountryId()}/"
                                   itemprop="country-name">{$oUserProfile->getProfileCountry()|escape}</a>{if $oGeoTarget->getCityId()},{/if}
                            {/if}

                            {if $oGeoTarget->getCityId()}
                                <a href="{router page='people'}city/{$oGeoTarget->getCityId()}/"
                                   itemprop="locality">{$oUserProfile->getProfileCity()|escape}</a>
                            {/if}
						</span>
                    </li>
                {/if}

                {if $aUserFieldValues}
                    {foreach $aUserFieldValues as $oField}
                        <li>
                            <em>{$oField->getTitle()|escape}</em>
                            <span>$oField->getValue(true,true)}</span>
                        </li>
                    {/foreach}
                {/if}

                {hook run='profile_whois_privat_item' oUserProfile=$oUserProfile}
            </ul>
        {/if}
        <!-- /Личные данные -->

        {hook run='profile_whois_item_after_privat' oUserProfile=$oUserProfile}

        {if $aBlogsOwner}
            <h2 class="header-table">{$aLang.profile_blogs_self}</h2>
            <p>
                {foreach $aBlogsOwner as $oBlog}
                    <a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape}</a>{if ! $oBlog@last}, {/if}
                {/foreach}
            </p>
        {/if}

        {if $aBlogAdministrators}
            <h2 class="header-table">{$aLang.profile_blogs_administration}</h2>
            <p>
                {foreach $aBlogAdministrators as $oBlogUser}
                    {$oBlog = $oBlogUser->getBlog()}
                    <a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape}</a>{if ! $oBlogUser@last}, {/if}
                {/foreach}
            </p>
        {/if}

        {if $aBlogModerators}
            <h2 class="header-table">{$aLang.profile_blogs_moderation}</h2>
            <p>
                {foreach $aBlogModerators as $oBlogUser}
                    {$oBlog = $oBlogUser->getBlog()}
                    <a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape}</a>{if ! $oBlogUser@last}, {/if}
                {/foreach}
            </p>
        {/if}

        {if $aBlogUsers}
            <h2 class="header-table">{$aLang.profile_blogs_join}</h2>
            <p>
                {foreach $aBlogUsers as $oBlogUser}
                    {$oBlog = $oBlogUser->getBlog()}
                    <a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape}</a>{if ! $oBlogUser@last}, {/if}
                {/foreach}
            </p>
        {/if}

        <!-- АКТИВНОСТЬ -->
        <h2 class="header-table">{$aLang.profile_activity}</h2>
        <ul class="profile-contacts">
            {if Config::Get('general.reg.invite') and $oUserInviteFrom}
                <li>
                    <em>{$aLang.profile_invite_from}</em>
                    <span><a href="{$oUserInviteFrom->getProfileUrl()}">{$oUserInviteFrom->getLogin()}</a></span>
                </li>
            {/if}

            {if Config::Get('general.reg.invite') and $aUsersInvite}
                <li>
                    <em>{$aLang.profile_invite_to}</em>
                    {foreach $aUsersInvite as $oUserInvite}
                        <span><a href="{$oUserInvite->getProfileUrl()}">{$oUserInvite->getLogin()}</a></span>
                        <br/>
                    {/foreach}
                </li>
            {/if}

            <li>
                <em>{$aLang.profile_date_registration}</em>
                <span>{date_format date=$oUserProfile->getDateRegister()}</span>
            </li>
            {if $oSession}
                <li>
                    <em>{$aLang.profile_date_last}</em>
                    <span>{date_format date=$oSession->getDateLast()}</span>
                </li>
            {/if}
        </ul>
        <!-- /АКТИВНОСТЬ -->


        {$aUserFieldContactValues = $oUserProfile->getUserFieldValues(true,array('contact'))}
        {if $aUserFieldContactValues}
            <h2 class="header-table">{$aLang.profile_contacts}</h2>
            <ul class="profile-contacts">
                {foreach $aUserFieldContactValues as $oField}
                    <li><em>{$oField->getTitle()|escape}</em><span>{$oField->getValue(true,true)}</span></li>
                {/foreach}
            </ul>
        {/if}

        {$aUserFieldContactValues = $oUserProfile->getUserFieldValues(true,array('social'))}
        {if $aUserFieldContactValues}
            <h2 class="header-table">{$aLang.profile_social}</h2>
            <ul class="profile-contacts">
                {foreach $aUserFieldContactValues as $oField}
                    <li><em>{$oField->getTitle()|escape:'html'}</em><span>{$oField->getValue(true,true)}</span></li>
                {/foreach}
            </ul>
            </p>
        {/if}

        {hook run='profile_whois_item' oUserProfile=$oUserProfile}

    </div>
    {if $aUsersFriend}
        <h2 class="header-table mb-15"><a href="{$oUserProfile->getProfileUrl()}friends/">{$aLang.profile_friends}</a>
            ({$iCountFriendsUser})</h2>
        {include file='user_list_avatar.tpl' aUsersList=$aUsersFriend}
    {/if}

    {hook run='profile_whois_item_end' oUserProfile=$oUserProfile}

    {hook run='user_info_end' oUserProfile=$oUserProfile}
{/block}