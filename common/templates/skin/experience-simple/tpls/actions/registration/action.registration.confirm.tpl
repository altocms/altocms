 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

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
