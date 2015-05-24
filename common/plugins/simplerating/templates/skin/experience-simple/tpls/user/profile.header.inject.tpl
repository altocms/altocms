<div class="col-lg-3 user-power-container">
    <div>
        <div class="user-power-header">
            {$aLang.user_skill}
        </div>
        <div class="user-power" id="user_skill_{$oUserProfile->getId()}">
            {$oUserProfile->getSkill()|number_format:{Config::Get('view.skill_length')}}
        </div>
    </div>
</div>
<div class="col-lg-4 user-rating-container">
    <div class="user-rating-header">
        {$aLang.user_rating}
    </div>
    {$sClasses = ''}
    {if $oUserProfile->getRating()>=0}
        {$sClasses = "$sClasses vote-count-positive "}
    {else}
        {$sClasses = "$sClasses vote-count-negative "}
    {/if}
    {if $oVote AND ($oVote->getDirection()>0)}
        {$sClasses = "$sClasses voted voted-up "}
    {elseif $oVote AND ($oVote->getDirection()<0)}
        {$sClasses = "$sClasses voted voted-down "}
    {elseif $oVote}
        {$sClasses = "$sClasses voted "}
    {/if}
    <div class="user-rating vote js-vote {$sClasses}"  data-target-type="user" data-target-id="{$oUserProfile->getId()}">
        {if C::Get('plugin.simplerating.user.dislike')}
        <a href="#" class="{$sVoteClass} vote-down js-vote-down link link-gray link-clear"><i class="fa fa-thumbs-o-down"></i></a>
        {/if}
        <span class="vote-total js-vote-rating {$sClasses}">{if $oUserProfile->getRating() > 0}+{/if}{$oUserProfile->getRating()|number_format:{Config::Get('view.rating_length')}}</span>
        <a href="#" class="{$sVoteClass} vote-up js-vote-up link link link-gray link-clear"><i class="fa fa-thumbs-o-up"></i></a>
    </div>
</div>