{if $bUsersUseOrder}
    <th class="cell-skill">
        <small>
            <a href="{$sUsersRootPage}?order=user_skill&order_way={if $sUsersOrder=='user_skill'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}"
               {if $sUsersOrder=='user_skill'}class="{$sUsersOrderWay}"{/if}><span>{$aLang.user_skill}</span></a>
        </small>
    </th>
    <th class="cell-rating">
        <small>
            <a href="{$sUsersRootPage}?order=user_rating&order_way={if $sUsersOrder=='user_rating'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}"
               {if $sUsersOrder=='user_rating'}class="{$sUsersOrderWay}"{/if}><span>{$aLang.user_rating}</span></a>
        </small>
    </th>
{else}
    <th class="cell-skill">
        <small>{$aLang.user_skill}</small>
    </th>
    <th class="cell-rating">
        <small>{$aLang.user_rating}</small>
    </th>
{/if}