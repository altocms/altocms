{extends file="themes/$sSkinTheme/layouts/default_light.tpl"}

{block name="layout_vars"}
    {$noSidebar=true}
{/block}

{block name="layout_content"}
    <div class="text-center page-header">
        <h3>{$aLang.registration_confirm_header}</h3>
        {$aLang.registration_confirm_text}<br/><br/>

        <a href="{Config::Get('path.root.url')}">{$aLang.site_go_main}</a>
    </div>
{/block}
