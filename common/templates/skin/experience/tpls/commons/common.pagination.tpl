 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if $aPaging AND $aPaging.iCountPage>1}
    <div class="paginator">
        <ul class="js-pagination">
            {if $aPaging.iCurrentPage>1}
                <li class="first">
                    <a href="{$aPaging.sBaseUrl}/{$aPaging.sGetParams}"
                       class="link link-light-gray link-lead link-clear"
                       title="{$aLang.paging_first}">
                        <span class="visible-xs hidden-md hidden-sm hidden-lg"><i class="fa fa-chevron-left"></i><i class="fa fa-chevron-left"></i></span>
                        <span class="hidden-xs visible-md visible-sm visible-lg">{$aLang.paging_first}</span>
                    </a>
                </li>
            {/if}


            {if $aPaging.iPrevPage}
                <li class="prev"><a href="{$aPaging.sBaseUrl}/page{$aPaging.iPrevPage}/{$aPaging.sGetParams}"
                                    class="link link-light-gray link-lead link-clear js-paging-prev-page"
                                    title="{$aLang.paging_previos}"><i class="fa fa-chevron-left"></i></a></li>
            {else}
                <li class="disabled prev"><span><i class="fa fa-chevron-left"></i></span></li>
            {/if}

            {foreach $aPaging.aPagesLeft as $iPage}
                <li><a class="link link-light-gray link-lead link-clear" href="{$aPaging.sBaseUrl}/page{$iPage}/{$aPaging.sGetParams}">{$iPage}</a></li>
            {/foreach}

            <li class="active link link-light-gray link-lead link-clear"><span>{$aPaging.iCurrentPage}</span></li>

            {foreach $aPaging.aPagesRight as $iPage}
                <li><a class="link link-light-gray link-lead link-clear" href="{$aPaging.sBaseUrl}/page{$iPage}/{$aPaging.sGetParams}">{$iPage}</a></li>
            {/foreach}

            {if $aPaging.iNextPage}
                <li class="next"><a  class="link link-light-gray link-lead link-clear js-paging-next-page"
                                     href="{$aPaging.sBaseUrl}/page{$aPaging.iNextPage}/{$aPaging.sGetParams}"
                                     title="{$aLang.paging_next}"><i class="fa fa-chevron-right"></i></a></li>
            {else}
                <li class="disabled next"><span><i class="fa fa-chevron-right"></i></span></li>
            {/if}

            {if $aPaging.iCurrentPage<$aPaging.iCountPage}
                <li class="last"><a  class="link link-light-gray link-lead link-clear"  href="{$aPaging.sBaseUrl}/page{$aPaging.iCountPage}/{$aPaging.sGetParams}"
                                     title="{$aLang.paging_last}">

                    <span class="visible-xs hidden-md hidden-sm hidden-lg"><i class="fa fa-chevron-right"></i><i class="fa fa-chevron-right"></i></span>
                    <span class="hidden-xs visible-md visible-sm visible-lg">{$aLang.paging_last}</span>
                </a></li>{/if}

        </ul>
    </div>

{/if}
