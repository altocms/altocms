{$sVoteClass = ""}
{if $oVote OR E::UserId()==$oTopic->getUserId() OR strtotime($oTopic->getDateAdd())<$smarty.now-Config::Get('acl.vote.topic.limit_time')}
    {if $oTopic->getRating() > 0}
        {$sVoteClass = "$sVoteClass vote-count-positive"}
    {elseif $oTopic->getRating() < 0}
        {$sVoteClass = "$sVoteClass vote-count-negative"}
    {/if}
{/if}
{if $oVote}
    {$sVoteClass = "$sVoteClass voted"}
    {if $oVote->getDirection() > 0}
        {$sVoteClass = "$sVoteClass voted-up"}
    {elseif $oVote->getDirection() < 0}
        {$sVoteClass = "$sVoteClass voted-down"}
    {/if}
{/if}
{if $oTopic->isVoteInfoShow()}
    {$bVoteInfoShow=true}
{/if}
<li class="pull-right vote js-vote {$sVoteClass}" data-target-type="topic" data-target-id="{$oTopic->getId()}">
    <div class="vote-up js-vote-up"><span class="glyphicon glyphicon-plus-sign"></span></div>
    <div class="vote-count js-vote-rating {if $bVoteInfoShow}js-infobox-vote-topic{/if}"
         title="{$aLang.topic_vote_count}: {$oTopic->getCountVote()}">
        {if $bVoteInfoShow}
            {if $oTopic->getRating() > 0}+{/if}{$oTopic->getRating()}
        {else}
            <a href="#" onclick="return ls.vote.vote('topic',{$oTopic->getId()},0,this);">?</a>
        {/if}
    </div>
    {if C::Get('plugin.simplerating.topic.dislike')}
        <div class="vote-down js-vote-down"><span class="glyphicon glyphicon-minus-sign"></span></div>
    {/if}
    {if $bVoteInfoShow}
        <div id="vote-info-topic-{$oTopic->getId()}" style="display: none;">
            <ul class="list-unstyled vote-topic-info">
                <li>
                    <span class="glyphicon glyphicon-thumbs-up"></span>{$oTopic->getCountVoteUp()}
                </li>
                {if C::Get('plugin.simplerating.topic.dislike')}
                    <li>
                        <span class="glyphicon glyphicon-thumbs-down"></span>{$oTopic->getCountVoteDown()}
                    </li>
                {/if}
                <li>
                    <span class="glyphicon glyphicon-eye-open"></span>{$oTopic->getCountVoteAbstain()}
                </li>
                {hook run='topic_show_vote_stats' topic=$oTopic}
            </ul>
        </div>
    {/if}
</li>