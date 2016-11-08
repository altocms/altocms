<td class="hidden-xs rating-value last-td {if $oUserList->getSkill() < 0}red{/if}">
    {$oUserList->getSkill()|number_format:{Config::Get('view.skill_length')}}
</td>
<td class="hidden-xs rating-value last-td {if $oUserList->getRating() < 0}red{/if}">
    {$oUserList->getRating()|number_format:{Config::Get('view.rating_length')}}
</td>