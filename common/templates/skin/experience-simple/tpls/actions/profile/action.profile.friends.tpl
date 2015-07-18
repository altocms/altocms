 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="_profile.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
    {$oSession=$oUserProfile->getSession()}
    {$oVote=$oUserProfile->getVote()}
    {$oGeoTarget=$oUserProfile->getGeoTarget()}
{/block}

{block name="layout_profile_content"}

<div class="profile-content">
    {include file='menus/menu.people-top.tpl'}

    {include file='commons/common.user_list.tpl' aUsersList=$aFriends}
</div>

{/block}
