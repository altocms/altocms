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
    <li class="pull-right topic-rating end js-vote {$sVoteClass}" data-target-type="comment" data-target-id="{$oComment->getId()}">
        {if C::Get('plugin.rating.comment.dislike')}
        <a href="#" class="{$sVoteClass} vote-down link link-gray link-clear js-vote-down"><i class="fa fa-thumbs-o-down"></i></a>
        {/if}
        <span class="vote-total {$sVoteClass} js-vote-rating">{if $oComment->getRating() > 0}+{/if}{$oComment->getRating()}</span>
        <a href="#" class="{$sVoteClass} vote-up link link link-gray link-clear js-vote-up"><i class="fa fa-thumbs-o-up"></i></a>
    </li>
{/if}