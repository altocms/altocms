{if $bUsersUseOrder}
    <th class="hidden-xs">
        <a class="link {if $sUsersOrder=='user_skill'}{$sUsersOrderWay}{/if}"
           href="{$sUsersRootPage}?order=user_skill&order_way={if $sUsersOrder=='user_skill'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}">
            {$aLang.user_skill}
        </a>
    </th>
    <th class="hidden-xs">
        <a class="link {if $sUsersOrder=='user_rating'}{$sUsersOrderWay}{/if}"
           href="{$sUsersRootPage}?order=user_rating&order_way={if $sUsersOrder=='user_rating'}{$sUsersOrderWayNext}{else}{$sUsersOrderWay}{/if}">
            {$aLang.user_rating}
        </a>
    </th>
{else}
    <th class="hidden-xs">{$aLang.user_skill}</th>
    <th class="hidden-xs">{$aLang.user_rating}</th>
{/if}