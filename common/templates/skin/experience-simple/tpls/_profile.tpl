 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
    {$oSession=$oUserProfile->getSession()}
    {$oVote=$oUserProfile->getVote()}
    {$oGeoTarget=$oUserProfile->getGeoTarget()}
{/block}

{block name="layout_content"}
    {block name="layout_profile_header"}

        {include file="actions/profile/action.profile.pre.tpl"}

        {block name="layout_profile_submenu"}{/block}

        {block name="layout_profile_content"}{/block}

    {/block}

{/block}
