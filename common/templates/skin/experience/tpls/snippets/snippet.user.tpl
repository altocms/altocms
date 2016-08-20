{if $oUser}
    <a href="{$oUser->getProfileUrl()}" data-alto-role="popover" data-api="user/{$oUser->getId()}/info"><span class="fa fa-user"></span>&nbsp;{$oUser->getDisplayName()}</a>
{else}
    <s>&nbsp;<span class="fa fa-user"></span>&nbsp;{$sUserLogin}</s>
{/if}
