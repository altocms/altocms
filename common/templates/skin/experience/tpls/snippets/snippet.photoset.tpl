{strip}
{$sThumbHeight = C::Get('module.topic.photoset.thumb.height')}
{if count($aPhotos)}
    <div class="alto-photoset js-topic-photoset-list {$sClass}" {if $sPosition=='left' || $sPosition=='right'}data-width="{$sPosition}"{/if}>
        {foreach $aPhotos as $oPhoto}
            <a href="{$oPhoto->getWebPath()}" title="{$oPhoto->getDescription()}">
                <img src="{$oPhoto->getWebPath("x$sThumbHeight")}"
                     class="topic-photoset-item"
                     data-rel="prettyPhoto[pp_gal_{$sPhotosetHash}]"
                     alt="{$oPhoto->getDescription()}"/>
            </a>
        {/foreach}
    </div>
{/if}
{/strip}
