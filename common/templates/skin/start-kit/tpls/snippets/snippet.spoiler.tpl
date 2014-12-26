{strip}
<div class="spoiler">
    <div class="spoiler-title">
        {if $aParams.title}{$aParams.title}{/if}
        <div class="spoiler-slider">
            {$aLang.spoiler_toggle_show}
        </div>
    </div>
    <div class="spoiler-text">
        {$aParams.snippet_text}
    </div>
</div>
{/strip}