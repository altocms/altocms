<td rowspan="3" class="hidden-xxs rating-value last-td {if $oUserList->getSkill() < 0}red{/if}">
    {$oUserList->getSkill()|number_format:{Config::Get('view.skill_length')}}
</td>
<td rowspan="3" class="hidden-xxs rating-value last-td {if $oUserList->getRating() < 0}red{/if}">
    {$oUserList->getRating()|number_format:{Config::Get('view.rating_length')}}
</td>