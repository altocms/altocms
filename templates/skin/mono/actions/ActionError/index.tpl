{extends file="_index.tpl"}

{block name="content"}

{if $aMsgError[0].title}
    <h2 class="page-header">{$aLang.error}: <span>{$aMsgError[0].title}</span></h2>
{/if}

<p>{$aMsgError[0].msg}</p>
<p><a href="javascript:history.go(-1);">{$aLang.site_history_back}</a>, <a
        href="{cfg name='path.root.web'}">{$aLang.site_go_main}</a></p>

{/block}