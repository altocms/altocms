<div class="user-info-default-container">
    <img src="{$oUser->GetPhotoUrl('112x112crop')}" alt="{$oUser->getLogin()}"/>
    <div class="user-info-default-top">
        <ul>
            {if (C::Get('rating.enabled'))}
                <li class="user-info-default-rating"><i class="fa fa-bar-chart-o"></i><span class="{if $oUser->getRating()>=0}positive{else}negative{/if}">{if $oUser->getRating()>0}+{/if}{$oUser->getRating()}</span></a></li>
            {/if}
            <li class="user-info-default-rss"><a href="{router page='rss'}personal_blog/{$oUser->getLogin()}/"><i class="fa fa-rss"></i></a></li>
            <li class="user-info-default-username"><a href="{$oUser->getProfileUrl()}">{$oUser->getLogin()}</a></li>
            <li class="user-info-default-display">
                <a href="{$oUser->getProfileUrl()}">
                    {if !$oUser->getProfileName()}{$aLang.no_name}{else}{$oUser->getProfileName()|escape:'html'}{/if}
                </a>
            </li>
        </ul>
    </div>
    <div class="user-info-default-bottom">
        <ul>
            {if E::User() && $oUser->getId() != E::User()->getId()}
                <li>
                    <a href="{router page='talk'}add/?talk_users={$oUser->getLogin()}"
                       class="link link-light-gray link-clear link-lead"><i class="fa fa-envelope-o"></i>&nbsp;{E::ModuleLang()->Get('send_message')}</a>
                </li>
                {*{include file='actions/profile/action.profile.friend_item.tpl' oUserFriend=$oUser->getUserFriend() oUserProfile=$oUser aLang=E::ModuleLang()->GetLangMsg()}*}
                <li>
                    <script type="text/javascript">
                        jQuery(function ($) {
                            ls.lang.load({lang_load name="profile_user_unfollow,profile_user_follow"});
                        });
                    </script>
                    <a href="#"
                       onclick="ls.user.followToggle(this, {$oUser->getId()}); return false;"
                       class="link link-light-gray link-clear link-lead {if $oUser->isFollow()}followed{/if}"><i class="fa fa-star-o"></i>
                        {if $oUser->isFollow()}{E::ModuleLang()->Get('profile_user_unfollow')}{else}{E::ModuleLang()->Get('profile_user_follow')}{/if}
                    </a>
                </li>
            {else}
                <li></li>
            {/if}
        </ul>
    </div>
</div>

