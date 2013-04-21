{extends file='_index.tpl'}

{block name="content-body"}

<div class="content-error">
{if $aMsgError[0].title}
    <h2 class="page-header">{$aLang.error}: <span>{$aMsgError[0].title}</span></h2>
{/if}

    <p>{$aMsgError[0].msg}</p>
    <br/>
    <br/>

    <p><a href="javascript:history.go(-1);">{$aLang.site_history_back}</a><br/>
        <a href="{Config::Get('path.root.url')}">{$aLang.site_go_main}</a></p>
</div>

{/block}