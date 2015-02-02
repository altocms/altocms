{$sClasses = ''}
{if $oBlog->getRating() > 0}
    {$sClasses = "$sClasses vote-count-positive"}
{elseif $oBlog->getRating() < 0}
    {$sClasses = "$sClasses vote-count-negative"}
{/if}
{if $oVote}
    {$sClasses = "$sClasses voted"}
    {if $oVote->getDirection()>0}
        {$sClasses = "$sClasses voted-up"}
    {elseif $oVote->getDirection()<0}
        {$sClasses = "$sClasses voted-down"}
    {/if}
{/if}
<div class="small vote js-vote {$sClasses}" data-target-type="blog" data-target-id="{$oBlog->getId()}">
    <div class="text-muted vote-label">{$aLang.blog_rating}</div>
    <a href="#" class="vote-up js-vote-up"><span class="glyphicon glyphicon-plus-sign"></span></a>

    <div class="vote-count count js-vote-rating" title="{$aLang.blog_vote_count}: {$oBlog->getCountVote()}">
        {if $oBlog->getRating() > 0}+{/if}{$oBlog->getRating()}
    </div>
    {if C::Get('plugin.simplerating.blog.dislike')}
        <a href="#" class="vote-down js-vote-down"><span class="glyphicon glyphicon-minus-sign"></span></a>
    {/if}
</div>