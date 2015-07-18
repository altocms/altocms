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

<div id="aim-page-images">
    {if $aResources}
            {foreach $aResources as $oItem}
                <div class="col-md-12">
                    <div class="thumbnail">
                        <a href="{$oItem->GetImgUrl()}" rel="prettyPhoto[pp_gal]" onclick="return false;">
                            <img class="transition8" src="{$oItem->GetImgUrl('350fit')}" alt="image"/>
                        </a>

                        <div class="caption text-center">
                            {$aLang["aim_target_type_{$oItem->getTargetType()}"]}
                        </div>
                    </div>
                </div>
            {/foreach}
    {else}
        {$aLang.select_category}
    {/if}
</div>