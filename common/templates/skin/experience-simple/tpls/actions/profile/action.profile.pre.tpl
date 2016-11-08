{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<div class="panel panel-default user-info flat">
<div class="panel-body">

    {hook run='profile_header_begin'}

    <div class="row user-info-block">
        <div class="col-lg-17">
            <img src="{$oUserProfile->getAvatarUrl('big')}" {$oUserProfile->getAvatarImageSizeAttr('big')} alt="{$oUserProfile->getDisplayName()}" class="user-logo" itemprop="photo"/>
            <div class="user-name">
                <div class="user-login-block">
                    <span class="user-login {if !$oUserProfile->getProfileName()}no-user-name{/if}" >{$oUserProfile->getLogin()}</span><br/>
                </div>
                <div class="user-full-name {if !$oUserProfile->getProfileName()}no-user-name{/if}">
                    {if !$oUserProfile->getProfileName()}{$aLang.no_name}{else}{$oUserProfile->getProfileName()|escape:'html'}{/if}

                </div>
            </div>
        </div>
        {hook run='profile_header' oUserProfile=$oUserProfile oVote=$oVote}
    </div>

    <div style="display: none;" class="user-more-block">
        {if $oUserProfile->getProfileAbout()}
            <div class="bg-warning user-about">
                <h5>{$aLang.profile_about}</h5>
                <p>
                    {$oUserProfile->getProfileAbout()}
                </p>
            </div>
        {/if}

        <div class="row user-more">
            <div class="col-md-14">
                <table class="table">
                    <thead>
                    <tr>
                        <th colspan="2"><h5>{$aLang.profile_privat}</h5></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>{$aLang.profile_sex}:</td>
                        <td>
                            {if $oUserProfile->getProfileSex()=='man'}
                                {$aLang.profile_sex_man}
                            {elseif $oUserProfile->getProfileSex()=='woman'}
                                {$aLang.profile_sex_woman}
                            {else}
                                {$aLang.profile_sex_other}
                            {/if}
                        </td>
                    </tr>
                    {if $oUserProfile->getProfileBirthday()}
                        <tr>
                            <td>{$aLang.profile_birthday}:</td>
                            <td>{date_format date=$oUserProfile->getProfileBirthday() format="j F Y"}</td>
                        </tr>
                    {/if}
                    {if $oGeoTarget}
                        <tr>
                            <td>{$aLang.profile_place}</td>
                            <td>
                                {if $oGeoTarget->getCountryId()}
                                    <a href="{router page='people'}country/{$oGeoTarget->getCountryId()}/"
                                       class="link link-blue link-lead">{$oUserProfile->getProfileCountry()|escape:'html'}</a>{if $oGeoTarget->getCityId()},{/if}
                                {/if}

                                {if $oGeoTarget->getCityId()}
                                    <a href="{router page='people'}city/{$oGeoTarget->getCityId()}/"
                                       class="link link-blue link-lead">{$oUserProfile->getProfileCity()|escape:'html'}</a>
                                {/if}
                            </td>
                        </tr>
                    {/if}
                    </tbody>
                </table>
            </div>
            <div class="col-md-10">
                <table class="table">
                    <thead>
                    <tr>
                        <th colspan="2"><h5>{$aLang.profile_contacts}</h5></th>
                    </tr>
                    </thead>
                    <tbody>
                    {$aUserFieldContactValues=$oUserProfile->getUserFieldValues(true, array('contact'))}
                    {if $aUserFieldContactValues}
                        {foreach $aUserFieldContactValues as $oField}
                            <tr>
                                <td><i class="fa fa-{$oField->getName()}"></i>{$oField->getTitle()|escape:'html'}:</td>
                                <td>{$oField->getValue(true,true)}</td>
                            </tr>
                        {/foreach}
                    {else}
                        <tr>
                            <td>
                                <div class="empty-info">
                                    {$aLang.no_profile_contacts}
                                </div>
                            </td>
                        </tr>
                    {/if}
                    </tbody>
                </table>

                <table class="table">
                    <thead>
                    <tr>
                        <th colspan="2"><h5>{$aLang.profile_social}</h5></th>
                    </tr>
                    </thead>
                    <tbody>
                    {$aUserFieldContactValues=$oUserProfile->getUserFieldValues(true, array('social'))}
                    {if $aUserFieldContactValues}
                        {foreach $aUserFieldContactValues as $oField}
                            <tr>
                                <td><i class="fa fa-{$oField->getName()}"></i>{$oField->getTitle()|escape:'html'}</td>
                                <td><a class="link link-blue link-lead" href="#">{$oField->getValue(true,true)}</a></td>
                            </tr>
                        {/foreach}
                    {else}
                        <tr>
                            <td>
                                <div class="empty-info">
                                    {$aLang.no_profile_accounts}
                                </div>
                            </td>
                        </tr>
                    {/if}
                    </tbody>
                </table>

                {hook run='profile_whois_item' oUserProfile=$oUserProfile}
            </div>
        </div>
    </div>

    {hook run='profile_header_end'}

</div>
<div class="panel-footer dual-footer clearfix">
    <ul class="pull-left">
        {if $oUserCurrent && $oUserProfile->getId() != $oUserCurrent->getId()}
            <li>
                <a href="{router page='talk'}add/?talk_users={$oUserProfile->getLogin()}"
                   class="link link-light-gray link-clear link-lead"><i class="fa fa-envelope-o"></i>&nbsp;{$aLang.send_message}</a>
            </li>
            {include file='actions/profile/action.profile.friend_item.tpl' oUserFriend=$oUserProfile->getUserFriend()}
            <li>
                <script type="text/javascript">
                    jQuery(function ($) {
                        ls.lang.load({lang_load name="profile_user_unfollow,profile_user_follow"});
                    });
                </script>
                <a href="#"
                   onclick="ls.user.followToggle(this, {$oUserProfile->getId()}); return false;"
                   class="link link-light-gray link-clear link-lead {if $oUserProfile->isFollow()}followed{/if}"><i class="fa fa-star-o"></i>
                    {if $oUserProfile->isFollow()}{$aLang.profile_user_unfollow}{else}{$aLang.profile_user_follow}{/if}
                </a>
            </li>
        {else}
            <li></li>
        {/if}
    </ul>
    <ul class="pull-right">
        <li class="user-info-show-button pa0">
            <a href="#"
               onclick="
                       var $b = $('.user-more-block');
                       $b.slideToggle('fast');
                       var t = $('#whois_toggle_button');
                       if ($.trim(t.text()) == $.trim('{$aLang.hide_info}')) {
                       t.text('{$aLang.show_info}').prev().toggleClass('fa-rotate-180');
                       } else {
                       t.text('{$aLang.hide_info}').prev().toggleClass('fa-rotate-180');
                       }

                       return false;"
               class="btn btn-gray">
                <i class="fa fa-bars"></i>&nbsp; <span id="whois_toggle_button" data-state="off">{$aLang.show_info}</span>
            </a>
        </li>
    </ul>
</div>
</div>
