{extends file="_profile.tpl"}

{block name="layout_vars"}
    {$menu="people"}
{/block}

{block name="layout_profile_content"}

<div class="profile-content">
    {include file='commons/common.user_list.tpl' aUsersList=$aFriends}
</div>

{/block}
