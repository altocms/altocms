{extends file="themes/$sSkinTheme/layouts/default_light.tpl"}

{block name="layout_vars"}
{/block}

{block name="layout_content"}
    <div class="text-center page-header">
        <h3>{$aLang.registration_activate_ok}</h3>
        <a href="{Config::Get('path.root.url')}">{$aLang.site_go_main}</a>
    </div>
{/block}
