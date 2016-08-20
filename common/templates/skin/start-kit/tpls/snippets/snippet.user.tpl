{if $oUser}
    <a href="{$oUser->getProfileUrl()}" data-alto-role="popover" data-api="user/{$oUser->getId()}/info"><span class="glyphicon glyphicon-user"></span>&nbsp;{$oUser->getDisplayName()}</a>
{else}
    <s>&nbsp;<span class="glyphicon glyphicon-user"></span>&nbsp;{$sUserLogin}</s>
{/if}
