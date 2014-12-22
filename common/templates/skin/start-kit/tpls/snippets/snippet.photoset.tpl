{if count($aPhotos)}
    <div class="alto-photoset" {if $sPosition=='left' || $sPosition=='right'}data-width="{$sPosition}"{/if}>{strip}
        {foreach $aPhotos as $oPhoto}
            <a href="{$oPhoto->getWebPath()}">
                <img src="{$oPhoto->getWebPath('x240')}"
                     data-rel="prettyPhoto[pp_gal_{$sPhotosetHash}]"
                     alt="{$oPhoto->getDescription()}"/>
            </a>
        {/foreach}
    {/strip}</div>
{/if}

