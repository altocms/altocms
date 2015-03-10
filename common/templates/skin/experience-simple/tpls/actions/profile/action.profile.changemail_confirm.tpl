 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="_profile.tpl"}

{block name="layout_vars"}
    {$noSidebar=true}
    {$noShowSystemMessage=true}
    {$oSession=$oUserProfile->getSession()}
    {$oVote=$oUserProfile->getVote()}
    {$oGeoTarget=$oUserProfile->getGeoTarget()}
{/block}

{block name="layout_profile_content"}

    <div class="bg-warning">
        <p>{$sText}</p>
    </div>

{/block}
