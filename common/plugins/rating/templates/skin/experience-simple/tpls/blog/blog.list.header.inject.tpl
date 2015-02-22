{if $bBlogsUseOrder}
    <th style="max-width: 120px;">
        <a href="{$sBlogsRootPage}?order=blog_rating&order_way={if $sBlogOrder=='blog_rating'}{$sBlogOrderWayNext}{else}{$sBlogOrderWay}{/if}"
           class="link {if $sBlogOrder=='blog_rating'}{$sBlogOrderWay}{/if}">{$aLang.blogs_rating}</a>
    </th>
{else}
    <th style="max-width: 120px;">{$aLang.blogs_rating}</th>
{/if}