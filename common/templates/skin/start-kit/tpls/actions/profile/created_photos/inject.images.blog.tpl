<script type="text/javascript">
    (function($){
        "use strict";
        function setPrettyPhoto() {
            $("a[rel^='prettyPhoto']").prettyPhoto({
                social_tools:'',
                show_title: false,
                slideshow:true,
                deeplinking: false
            });
        }

        setPrettyPhoto();

    }(jQuery));
</script>

<div id="aim-page-images" class="media-list row">
    {if $aResources}
    {foreach $aResources as $oItem}
        <div class="media col-md-12 mat0 mab12">
            <a href="{$oItem->GetImgUrl()}" class="pull-left hover-look150" rel="prettyPhoto[pp_gal]" onclick="return false;">
                <img class="bor100 transition8"  src="{$oItem->GetImgUrl('200x200crop')}" alt="image"/>
            </a>

            <div class="media-body">
                {if isset($aBlogs[$oItem->getTargetId()])}
                {$oBlog=$aBlogs[$oItem->getTargetId()]}
                    <h6 class="media-heading">
                        <a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()}</a>
                    </h6>
                    <div class="media-text text-muted">
                        <em>{$oBlog->getDescription()|strip_tags|truncate:1300:'...'}</em>
                    </div>
                {/if}
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
        <div class="bg-warning image-loading text-center">{$aLang.select_category}</div>
    {/if}
</div>