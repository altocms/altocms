{extends file="_profile.tpl"}

{block name="layout_vars"}
    {$noSidebar=true}
    {$noShowSystemMessage=true}
{/block}

{block name="layout_profile_content"}

<div class="content-error">
    <p>{$sText}</p>
    <br />
</div>

{/block}
