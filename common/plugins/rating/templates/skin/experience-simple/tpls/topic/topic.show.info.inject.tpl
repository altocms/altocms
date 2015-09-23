{$all=$oTopic->getCountVoteAbstain()+$oTopic->getCountVoteUp()+$oTopic->getCountVoteDown()}
{$up2=0}
{$down2=0}
{if $all==0}
    {$up=120}
    {$down=120}
{else}
    {if $oTopic->getCountVoteUp()==0}
        {$up=0}
    {else}
        {$up=$oTopic->getCountVoteUp()*360/$all}
        {if $up > 180}
            {$up2=$up-180}
            {$up=180}
        {/if}
    {/if}
    {if $oTopic->getCountVoteDown()==0}
        {$down=0}
    {else}
        {$down=$oTopic->getCountVoteDown()*360/$all}
        {if $down > 180}
            {$down2=$down-180}
            {$down=180}
        {/if}
    {/if}
{/if}
<style>
    .graph-positive_{$oTopic->getId()} {
        -webkit-transform:rotate(0deg);
        -moz-transform:rotate(0deg);
        -o-transform:rotate(0deg);
        transform:rotate(0deg);
    }
    .graph-positive_{$oTopic->getId()} > div {
        -webkit-transform:rotate({$up}deg);
        -moz-transform:rotate({$up}deg);
        -o-transform:rotate({$up}deg);
        transform:rotate({$up}deg);
    }
    .graph-positive_2_{$oTopic->getId()} {
        -webkit-transform:rotate({$up}deg);
        -moz-transform:rotate({$up}deg);
        -o-transform:rotate({$up}deg);
        transform:rotate({$up}deg);
    }
    .graph-positive_2_{$oTopic->getId()} > div {
        -webkit-transform:rotate({$up2}deg);
        -moz-transform:rotate({$up2}deg);
        -o-transform:rotate({$up2}deg);
        transform:rotate({$up2}deg);
    }

    .graph-negative_{$oTopic->getId()} {
        -webkit-transform:rotate(-{$down}deg);
        -moz-transform:rotate(-{$down}deg);
        -o-transform:rotate(-{$down}deg);
        transform:rotate(-{$down}deg);
    }
    .graph-negative_{$oTopic->getId()} > div {
        -webkit-transform:rotate({$down}deg);
        -moz-transform:rotate({$down}deg);
        -o-transform:rotate({$down}deg);
        transform:rotate({$down}deg);
    }


    .graph-negative_2_{$oTopic->getId()} {
        -webkit-transform:rotate(-{$down+$down2}deg);
        -moz-transform:rotate(-{$down+$down2}deg);
        -o-transform:rotate(-{$down+$down2}deg);
        transform:rotate(-{$down+$down2}deg);
    }
    .graph-negative_2_{$oTopic->getId()} > div {
        -webkit-transform:rotate({$down2}deg);
        -moz-transform:rotate({$down2}deg);
        -o-transform:rotate({$down2}deg);
        transform:rotate({$down2}deg);
    }
</style>
{if $bSideBar}
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

    <li class="topic-rating js-vote marr0" data-target-type="topic" data-target-id="{$oTopic->getId()}">
        {if C::Get('plugin.rating.topic.dislike')}
        <a href="#" onclick="return false;" class="vote-down link link-gray link-clear js-vote-down"><i class="fa fa-thumbs-o-down"></i></a>
        {/if}
        {if $bVoteInfoShow}
            <span class="vote-total js-vote-rating {$sVoteClass}">{if $oTopic->getRating() > 0}+{/if}{$oTopic->getRating()}</span>
        {else}
            <a href="#" class="vote-down link link-gray link-clear" onclick="return ls.vote.vote('topic',{$oTopic->getId()},0,this);">?</a>
        {/if}
        <a href="#" onclick="return false;" class="vote-up link link link-gray link-clear js-vote-up"><i class="fa fa-thumbs-o-up"></i></a>

        {if $bVoteInfoShow}
            <div id="vote-info-topic-{$oTopic->getId()}" style="display: none;">
                <ul class="list-unstyled vote-topic-info">
                    <li><span class="glyphicon glyphicon-thumbs-up"></span>{$oTopic->getCountVoteUp()}</li>
                    {if C::Get('plugin.rating.topic.dislike')}
                        <li><span class="glyphicon glyphicon-thumbs-down"></span>{$oTopic->getCountVoteDown()}
                    {/if}
                    </li>
                    <li><span class="glyphicon glyphicon-eye-open"></span>{$oTopic->getCountVoteAbstain()}
                    </li>
                    {hook run='topic_show_vote_stats' topic=$oTopic}
                </ul>
            </div>
        {/if}
    </li>
{else}

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

        <li class="topic-rating js-vote" data-target-type="topic" data-target-id="{$oTopic->getId()}">
        {if C::Get('plugin.rating.topic.dislike')}
            <a href="#" onclick="return false;" class="{$sVoteClass} vote-down link link-gray link-clear js-vote-down"><i class="fa fa-thumbs-o-down"></i></a>
        {/if}
                            <span class="vote-popover vote-total js-vote-rating {$sVoteClass} {if $oTopic->getRating() >= 0}green{else}red{/if}"
                                    {if Config::Get('view.show_rating') || $bVoteInfoShow}
                                data-placement="top"
                                data-trigger="click"
                                data-content='
                                        <div class="graph-container">
                                            <div class="graph-background"></div>
                                            <div class="graph-holder">{$all}</div>
                                            <div class="graph graph-positive graph-positive_{$oTopic->getId()}"><div></div></div>
                                            <div class="graph graph-negative graph-negative_{$oTopic->getId()}"><div></div></div>
                                            <div class="graph graph-positive graph-positive_2_{$oTopic->getId()}"><div></div></div>
                                            <div class="graph graph-negative graph-negative_2_{$oTopic->getId()}"><div></div></div>
                                        </div>
                                                                                                                                                                                                                    </div>
                                        <div id="vote-info-topic-{$oTopic->getId()}">
                                            <ul class="vote-topic-info list-unstyled mal0 clearfix">
                                                <li class="positive">{if $oTopic->getCountVoteUp()>0}+{/if}{$oTopic->getCountVoteUp()}</li>
                                                {if C::Get('plugin.rating.topic.dislike')}
                                                    <li class="negative">{if $oTopic->getCountVoteDown()>0}-{/if}{$oTopic->getCountVoteDown()}</li>
                                                {/if}
                                                <li class="abstain">{$oTopic->getCountVoteAbstain()}</li>
                                                {hook run='topic_show_vote_stats' topic=$oTopic bTopicList=true}
                                            </ul>
                                        </div>'
                                data-html="true"
                                    {/if}>
                                {if $bVoteInfoShow}
                                    {if $oTopic->getRating() > 0}+{/if}{$oTopic->getRating()}
                                {else}
                                    <i class=" vote-down link link-gray js-vote-rating link-clear"
                                       onclick="return ls.vote.vote('topic',{$oTopic->getId()},0,this);">...</i>
                                {/if}
                                </span>
            <a href="#" onclick="return false;" class="{$sVoteClass} vote-up link link link-gray link-clear js-vote-up"><i class="fa fa-thumbs-o-up"></i></a>
        </li>

{/if}