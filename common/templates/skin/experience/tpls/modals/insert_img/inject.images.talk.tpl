<ul id="aim-page-images" class="media-list">
    {if $aTalks}
        {include file="tpls/modals/insert_img/inject.params.tpl"}
        {foreach $aTalks as $oItem}
            <li class="media">
                <div class="media-body">
                        <h5 class="media-heading">
                            <a href="#"
                               class="aim-talk-photoset"
                               data-talk-id="{$oItem->getId()}"
                               onclick="return false;">({$oItem->getImagesCount()}) {$oItem->getTitle()}</a>
                        </h5>
                        <div class="media-text">
                            {$oItem->getText()|strip_tags|truncate:100:'...'}
                        </div>
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