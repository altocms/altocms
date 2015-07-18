<li id="vote_area_comment_{$oComment->getId()}"
    class="pull-right vote
                    {if $oComment->getRating() > 0}
                        vote-count-positive
                    {elseif $oComment->getRating() < 0 && C::Get('plugin.rating.comment.dislike')}
                        vote-count-negative
                    {/if}">
    <span class="vote-count" id="vote_total_comment_{$oComment->getId()}">{$oComment->getRating()}</span>
</li>