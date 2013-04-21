{if !$noShowSystemMessage}
    {if $aMsgError}
        {foreach from=$aMsgError item=aMsg}
        <div class="system-message-error">
            <a href="#" class="e-close"></a>
            {if $aMsg.title!=''}
                <strong>{$aMsg.title}</strong>:
            {/if}
            {$aMsg.msg}
        </div>
        {/foreach}
    {/if}

    {if $aMsgNotice}
        {foreach from=$aMsgNotice item=aMsg}
        <div class="system-message-notice">
            <a href="#" class="e-close"></a>
            {if $aMsg.title!=''}
                <strong>{$aMsg.title}</strong>:
            {/if}
            {$aMsg.msg}
        </div>
        {/foreach}
    {/if}

<script>
    jQuery(function () {
        $('[class^=system-message-] .e-close').each(function () {
            $(this).click(function () {
                $(this).parents('[class^=system-message-]').slideUp();
            });
        });
    });
</script>
{/if}