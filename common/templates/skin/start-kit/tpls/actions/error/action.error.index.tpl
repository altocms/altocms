{extends file="themes/$sSkinTheme/layouts/default_light.tpl"}

{block name="layout_vars"}
    {$noShowSystemMessage=true}
{/block}

{block name="layout_content"}

    {if $aMsgError[0].title}
        <div class="text-center page-header">
            <h3>{$aLang.error}: <span>{$aMsgError[0].title}</span></h3>
        </div>
    {/if}
    <p>{$aMsgError[0].msg}</p>
    <p><a href="javascript:history.go(-1);">{$aLang.site_history_back}</a>, <a
                href="{Config::Get('path.root.url')}">{$aLang.site_go_main}</a></p>
{/block}
