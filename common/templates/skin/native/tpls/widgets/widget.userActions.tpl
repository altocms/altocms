{**
 * Меню пользователя ("Добавить в друзья", "Написать письмо" и т.д.)
 *
 * @styles css/widgets.css
 *}

{extends file='./_aside.base.tpl'}

{block name='block_options'}
{/block}

{block name='block_type'}profile-actions{/block}

{block name='block_content'}
    {if ! $oUserCurrent or ( $oUserCurrent and $oUserCurrent->getId() == $oUserProfile->getId() )}
        {* $bBlockNotShow = true *}
    {else}
        <script type="text/javascript">
            jQuery(function ($) {
                ls.lang.load({lang_load name="profile_user_unfollow,profile_user_follow"});
            });
        </script>
        <ul class="profile-actions" id="profile_actions">
            {include file='actions/ActionProfile/friend_item.tpl' oUserFriend=$oUserProfile->getUserFriend()}

            <li><a href="{router page='talk'}add/?talk_users={$oUserProfile->getLogin()}"><i
                            class="icon-native-profile-new-talk"></i>{$aLang.user_write_prvmsg}</a></li>
            <li>
                <a href="#" onclick="ls.user.followToggle(this, {$oUserProfile->getId()}); return false;"
                   class="follow-link {if $oUserProfile->isFollow()}followed{/if}">
                    {if $oUserProfile->isFollow()}{$aLang.profile_user_unfollow}{else}{$aLang.profile_user_follow}{/if}
                </a>
            </li>
        </ul>
    {/if}
{/block}
