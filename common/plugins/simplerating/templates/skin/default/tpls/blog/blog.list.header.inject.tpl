{if $bBlogsUseOrder}
    <th class="cell-rating align-center">
        <small>
            <a href="{$sBlogsRootPage}?order=blog_rating&order_way={if $sBlogOrder=='blog_rating'}{$sBlogOrderWayNext}{else}{$sBlogOrderWay}{/if}"
               {if $sBlogOrder=='blog_rating'}class="{$sBlogOrderWay}"{/if}><span>{$aLang.blogs_rating}</span></a>
        </small>
    </th>
{else}
    <th class="cell-rating align-center">
        <small>{$aLang.blogs_rating}</small>
    </th>
{/if}