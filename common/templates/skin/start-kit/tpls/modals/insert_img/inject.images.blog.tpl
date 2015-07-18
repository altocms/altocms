<ul id="aim-page-images" class="media-list">
    {if $aResources}
        {include file="tpls/modals/insert_img/inject.params.tpl"}
        {foreach $aResources as $oItem}
            <li class="media">
                <a href="#" class="pull-left" data-url="{$oItem->GetImgUrl()}" onclick="return false;">
                    <img src="{$oItem->GetImgUrl('100fit')}" alt="image"/>
                </a>

                <div class="media-body">
                    {if isset($aBlogs[$oItem->getTargetId()])}
                        {$oBlog=$aBlogs[$oItem->getTargetId()]}
                        <h4 class="media-heading">{$oBlog->getTitle()}</h4>
                        <div class="media-text">
                            {$oBlog->getDescription()|strip_tags|truncate:100:'...'}
                        </div>
                    {/if}
                </div>
            </li>
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
        {$aLang.select_category}
    {/if}
</ul>