<td class="small text-info cell-skill">
    {$oUserList->getSkill()|number_format:{Config::Get('view.skill_length')}}
</td>
<td class="small cell-rating{if $oUserList->getRating() < 0} text-danger negative{else} text-success{/if}">
    {$oUserList->getRating()|number_format:{Config::Get('view.rating_length')}}
</td>