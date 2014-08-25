{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike   *}

{if $aPagingCmt AND $aPagingCmt.iCountPage>1}
    {if $aPagingCmt.sGetParams}
        {$sGetSep='&'}
    {else}
        {$sGetSep='?'}
    {/if}
    <div class="paginator">
        <ul class="pagination">

            {if Config::Get('module.comment.nested_page_reverse')}

                {if $aPagingCmt.iCurrentPage>1}
                    <li class="first">
                        <a href="{$aPagingCmt.sGetParams}{$sGetSep}cmtpage=1"
                           class="link link-light-gray link-lead link-clear">&larr;</a>
                    </li>
                {/if}

                {foreach $aPagingCmt.aPagesLeft as $iPage}
                    <li>
                        <a href="{$aPagingCmt.sGetParams}{$sGetSep}cmtpage={$iPage}"
                           class="link link-light-gray link-lead link-clear">{$iPage}</a>
                    </li>
                {/foreach}
                <li class="active link link-light-gray link-lead link-clear"><span>{$aPagingCmt.iCurrentPage}</span>
                </li>
                {foreach $aPagingCmt.aPagesRight as $iPage}
                    <li><a href="{$aPagingCmt.sGetParams}{$sGetSep}cmtpage={$iPage}"
                           class="link link-light-gray link-lead link-clear">{$iPage}</a></li>
                {/foreach}

                {if $aPagingCmt.iCurrentPage<$aPagingCmt.iCountPage}
                    <li class="last">
                        <a href="{$aPagingCmt.sGetParams}{$sGetSep}cmtpage={$aPagingCmt.iCountPage}"
                           class="link link-light-gray link-lead link-clear">{$aLang.paging_last}</a>
                    </li>
                {/if}

            {else}

                {if $aPagingCmt.iCurrentPage<$aPagingCmt.iCountPage}
                    <li>
                        <a href="{$aPagingCmt.sGetParams}{$sGetSep}cmtpage={$aPagingCmt.iCountPage}">{$aLang.paging_last}</a>
                    </li>
                {/if}

                {foreach $aPagingCmt.aPagesRight as $iPage}
                    <li><a href="{$aPagingCmt.sGetParams}{$sGetSep}cmtpage={$iPage}"
                           class="link link-light-gray link-lead link-clear">{$iPage}</a></li>
                {/foreach}
                <li class="active link link-light-gray link-lead link-clear"><span>{$aPagingCmt.iCurrentPage}</span>
                </li>
                {foreach $aPagingCmt.aPagesLeft as $iPage}
                    <li><a href="{$aPagingCmt.sGetParams}{$sGetSep}cmtpage={$iPage}"
                           class="link link-light-gray link-lead link-clear">{$iPage}</a></li>
                {/foreach}

                {if $aPagingCmt.iCurrentPage>1}
                    <li><a href="{$aPagingCmt.sGetParams}{$sGetSep}cmtpage=1">&rarr;</a></li>
                {/if}

            {/if}
        </ul>
    </div>
{/if}
