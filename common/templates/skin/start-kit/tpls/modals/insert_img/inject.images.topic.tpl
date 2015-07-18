<ul id="aim-page-images" class="media-list">
    {if $aTopics}
        {include file="tpls/modals/insert_img/inject.params.tpl"}
        {foreach $aTopics as $oItem}
            <li class="media">
                {$sImagePath=$oItem->getPhotosetMainPhotoUrl(true)}
                {if $sImagePath}
                <a href="#" class="pull-left" data-url="{$sImagePath}" onclick="return false;">
                    <img src="{$oItem->getPhotosetMainPhotoUrl(true, '100fit')}" alt="image"/>
                </a>
                {/if}
                <div class="media-body">
                        <h4 class="media-heading">
                            <a href="#"
                               class="aim-topic-photoset"
                               data-topic-id="{$oItem->getId()}"
                               onclick="return false;">({$oItem->getImagesCount()}) {$oItem->getTitle()}</a>
                        </h4>
                        <div class="media-text">
                            {$oItem->getTextShort()|strip_tags|truncate:100:'...'}
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