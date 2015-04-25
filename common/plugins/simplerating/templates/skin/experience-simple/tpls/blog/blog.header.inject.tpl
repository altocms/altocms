<div class="col-lg-4 user-rating-container">
    <h4 class="user-rating-header">
        Рейтинг
    </h4>
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
    <div class="user-rating vote js-vote {$sClasses}"  data-target-type="blog" data-target-id="{$oBlog->getId()}">
        {if C::Get('plugin.simplerating.blog.dislike')}
        <a href="#" class="{$sVoteClass} vote-down link link-gray link-clear js-vote-down"><i class="fa fa-thumbs-o-down"></i></a>
        {/if}
                            <span class="vote-total {$sClasses} js-vote-rating" title="{$aLang.blog_vote_count}: {$oBlog->getCountVote()}">
                                {if $oBlog->getRating() > 0}+{/if}{$oBlog->getRating()}
                            </span>
        <a href="#" class="{$sVoteClass} vote-up link link link-gray link-clear js-vote-up"><i class="fa fa-thumbs-o-up"></i></a>
    </div>
</div>