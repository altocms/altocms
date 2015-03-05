{if $oTopic}
    <h4>{$oTopic->getTitle()}</h4>
{/if}
<div id="aim-page-images">
    {if $aResources}
        {include file="tpls/modals/insert_img/inject.params.tpl"}

        <div class="row">
        {$i=1}
            {foreach $aResources as $oItem}
                <div class="col-md-4">
                    <a href="#" data-url="{$oItem->GetImgUrl()}"  onclick="return false;">
                        <img src="{$oItem->GetImgUrl('131fit')}" class="img-thumbnail" alt="image"/>
                    </a>
                </div>
                {if $i mod 3 == 0}</div><div class="row">{/if}{$i=$i+1}
            {/foreach}
        </div>

        <div class="clearfix" id="aim-images-nav" style="display: none;">
            <br/>
            <button id="images-next-page" class="refresh-tree btn pull-right btn-primary btn-sm" disabled >
                {$aLang.next_page}
            </button>

            <button id="images-prev-page" class="btn btn-primary btn-sm pull-right" disabled >
                {$aLang.prev_page}
            </button>
        </div>

    {else}
        {$aLang.select_category}
    {/if}
</div>