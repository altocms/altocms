<script type="text/javascript">
    (function($){
        "use strict";
        function setPrettyPhoto() {
            $("a[rel^='prettyPhoto']").prettyPhoto({
                social_tools:'',
                show_title: false,
                slideshow:5000,
                deeplinking: false
            });
        }

        setPrettyPhoto();

    }(jQuery));
</script>

<div id="aim-page-images" class="media-list row">
    {if $aTopics}
        {foreach $aTopics as $oItem}
            <div class="media col-md-{if count($aTopics)==1}24{else}12{/if} mat0 mab12">
                {$sImagePath=$oItem->getPhotosetMainPhotoUrl(true)}
                {if $sImagePath}
                <a href="{$sImagePath}" class="pull-left hover-look150" rel="prettyPhoto[pp_gal]" onclick="return false;">
                    <img class="bor100 transition8" src="{$oItem->getPhotosetMainPhotoUrl(true, '100x100crop')}" alt="image"/>
                </a>
                {/if}
                <div class="media-body">
                        <h6 class="media-heading">
                            <a href="#"
                               class="aim-topic-photoset"
                               data-topic-id="{$oItem->getId()}"
                               onclick="return false;"><strong>({$oItem->getImagesCount()})</strong> {$oItem->getTitle()}</a>
                        </h6>
                        <div class="media-text text-muted">
                            {if count($aTopics)==1}{$g=300}{else}{$g=70}{/if}
                            <em>{$oItem->getTextShort()|strip_tags|truncate:$g:'...'}</em>
                        </div>
                </div>
            </div>
        {/foreach}

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
        {if $pre}
        <script>
            $(function(){
                $('.image-loading').pulse({
                    backgroundColor : '#FFFFFF'
                }, {
                    duration : 3250,
                    pulses   : 5,
                    interval : 800
                });
            })
        </script>
        {*<div class="bg-warning image-loading">{$aLang.topic_photoset_upload_title}</div>*}
        <div class="bg-warning image-loading text-center"><i class="fa fa-5x fa-spin fa-circle-o-notch"></i></div>
        {else}
        <div class="bg-warning image-loading text-center">
            {$aLang.insertimg_not_found}
        </div>
        {/if}
    {/if}
</div>