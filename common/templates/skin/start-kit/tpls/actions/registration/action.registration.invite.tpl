{extends file="themes/$sSkinTheme/layouts/default_light.tpl"}

{block name="layout_vars"}
    {$noSidebar=true}
{/block}

{block name="layout_content"}
    <div class="text-center page-header">
        <h3>{$aLang.registration_invite}</h3>
    </div>
    <form action="{router page='registration'}invite/" method="POST">
        <div class="form-group">
            <label>{$aLang.registration_invite_code}</label>
            <input type="text" name="invite_code" class="form-control"/>
        </div>

        <input type="submit" name="submit_invite" value="{$aLang.registration_invite_check}" class="btn btn-success"/>
    </form>
{/block}
