{if $aResources}
    <script>

        //noinspection JSUnresolvedFunction
        $(function () {
            var $masonryContainer = $('.masonry-container');

            $masonryContainer.imagesLoaded( function() {
                $masonryContainer.masonry({
                    itemSelector        : '.masonry-item',
                    columnWidth         : '.col-md-4',
                    transitionDuration  : 0
                });
            });

        })
    </script>
    <div class="masonry-container">
        {foreach $aResources as $oItem}
            <div class="masonry-item col-md-4">
                <a href="#" data-url="{$oItem->GetImgUrl()}"  onclick="return false;">
                    <img src="{$oItem->GetImgUrl('131fit')}" class="img-thumbnail" alt="image"/>
                </a>
            </div>
        {/foreach}
    </div>
{else}
    {$aLang.select_category}
{/if}