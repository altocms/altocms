{if $aPaging AND $aPaging.iCountPage>1}
    <div class="paging">
        <ul class="pagination js-pagination">
            {if $aPaging.iPrevPage}
                <li class="prev"><a href="{$aPaging.sBaseUrl}/page{$aPaging.iPrevPage}/{$aPaging.sGetParams}"
                                    class="js-paging-prev-page" title="{$aLang.paging_previos}">&laquo;</a></li>
            {else}
                <li class="disabled prev"><span>&laquo;</span></li>
            {/if}

            {if $aPaging.iCurrentPage>1}
                <li><a href="{$aPaging.sBaseUrl}/{$aPaging.sGetParams}"
                       title="{$aLang.paging_first}">{$aLang.paging_first}</a></li>{/if}

            {foreach $aPaging.aPagesLeft as $iPage}
                <li><a href="{$aPaging.sBaseUrl}/page{$iPage}/{$aPaging.sGetParams}">{$iPage}</a></li>
            {/foreach}

            <li class="active"><span>{$aPaging.iCurrentPage}</span></li>

            {foreach $aPaging.aPagesRight as $iPage}
                <li><a href="{$aPaging.sBaseUrl}/page{$iPage}/{$aPaging.sGetParams}">{$iPage}</a></li>
            {/foreach}

            {if $aPaging.iCurrentPage<$aPaging.iCountPage}
                <li><a href="{$aPaging.sBaseUrl}/page{$aPaging.iCountPage}/{$aPaging.sGetParams}"
                       title="{$aLang.paging_last}">{$aLang.paging_last}</a></li>{/if}

            {if $aPaging.iNextPage}
                <li class="pull-right next"><a href="{$aPaging.sBaseUrl}/page{$aPaging.iNextPage}/{$aPaging.sGetParams}"
                                               class="js-paging-next-page" title="{$aLang.paging_next}">&raquo;</a></li>
            {else}
                <li class="pull-right disabled next"><span>&raquo;</span></li>
            {/if}
        </ul>
    </div>
{/if}
