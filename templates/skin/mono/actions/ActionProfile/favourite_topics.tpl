{extends file="_index.tpl"}

{block name="content"}

{include file='actions/ActionProfile/profile_top.tpl'}
{include file='menu.profile_favourite.tpl'}

{if $oUserCurrent and $oUserCurrent->getId()==$oUserProfile->getId()}
	{$aParams.user=$oUserProfile}
	{widget name="tagsFavouriteTopic" params=$aParams}
{/if}

{include file='topic_list.tpl'}

{/block}