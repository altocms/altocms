{if $oTopic}
    <h5>{$oTopic->getTitle()}</h5>
{/if}
<div id="aim-page-images">
    {if $aResources}

        <script>

            //noinspection JSUnresolvedFunction
            $(function () {
                function setPrettyPhoto() {
                    $("a[rel^='prettyPhoto']").prettyPhoto({
                        social_tools:'',
                        show_title: false,
                        slideshow: true,
                        deeplinking: false
                    });
                }

                setPrettyPhoto();

            })
        </script>
        <div class="masonry-container">
            {foreach $aResources as $oItem}
                <div class="masonry-item col-md-8">
                    <a href="{$oItem->GetImgUrl()}" rel="prettyPhoto[pp_gal]" onclick="return false;">
                        <img src="{$oItem->GetImgUrl('250x250')}" class="img-thumbnail" alt="image"/>
                    </a>
                </div>
            {/foreach}
        </div>

        <div class="clearfix" id="aim-images-nav" style="display: none;">
            <br/>
            <button id="images-next-page" class="refresh-tree btn pull-right btn-default btn-sm" disabled >
                {$aLang.next_page}
            </button>

            <button id="images-prev-page" class="btn btn-default btn-sm pull-right" disabled >
                {$aLang.prev_page}
            </button>
        </div>

    {else}
        {$aLang.select_category}
    {/if}
</div>