<div id="aim-page-images">
    {if $aResources}
        {include file="tpls/modals/insert_img/inject.params.tpl"}
            {foreach $aResources as $oItem}
                <div class="col-md-12">
                    <div class="thumbnail">
                        <a href="#" data-url="{$oItem->GetImgUrl()}" onclick="return false;">
                            <img src="{$oItem->GetImgUrl('200fit')}" alt="image"/>
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