{if $oComment->getTargetType() != 'talk'}
    {$oVote=$oComment->getVote()}
    {$sVoteClass = ""}
    {if $oComment->getRating() > 0}
        {$sVoteClass = " vote-count-positive"}
    {elseif $oComment->getRating() < 0}
        {$sVoteClass = " vote-count-negative"}
    {/if}
    {if $oVote}
        {$sVoteClass = " voted"}
        {if $oVote->getDirection() > 0}
            {$sVoteClass = " voted-up"}
        {else}
            {$sVoteClass = " voted-down"}
        {/if}
    {/if}
    <li class="vote js-vote {$sVoteClass}" data-target-type="comment" data-target-id="{$oComment->getId()}">
        <div class="vote-up js-vote-up"><span class="glyphicon glyphicon-plus-sign"></span></div>

        <span class="vote-count js-vote-rating">{if $oComment->getRating() > 0}+{/if}{$oComment->getRating()}</span>
        {if C::Get('plugin.simplerating.comment.dislike')}
            <div class="vote-down js-vote-down"><span class="glyphicon glyphicon-minus-sign"></span></div>
        {/if}
    </li>
{/if}