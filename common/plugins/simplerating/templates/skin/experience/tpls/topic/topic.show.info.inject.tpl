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

    <li class="pull-right topic-rating js-vote end marr0" data-target-type="topic" data-target-id="{$oTopic->getId()}">
        {if C::Get('plugin.simplerating.topic.dislike')}
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
                    {if C::Get('plugin.simplerating.topic.dislike')}
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
{if $bTopicList}
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

    <li class="pull-right topic-rating js-vote end" data-target-type="topic" data-target-id="{$oTopic->getId()}">
    {if C::Get('plugin.simplerating.topic.dislike')}
        <a href="#" onclick="return false;" class="{$sVoteClass} vote-down link link-gray link-clear js-vote-down"><i class="fa fa-thumbs-o-down"></i></a>
    {/if}
                        <span class="vote-tooltip vote-total js-vote-rating {$sVoteClass} {if $oTopic->getRating() >= 0}green{else}red{/if}"
                                {if Config::Get('view.show_rating') || $bVoteInfoShow}
                            data-placement="top"
                            data-original-title='
                                    <div id="vote-info-topic-{$oTopic->getId()}">
                                        <ul class="vote-topic-info list-unstyled mal0">
                                            <li><i class="fa fa-thumbs-o-up"></i><span>{$oTopic->getCountVoteUp()}</span>
                                            {if C::Get('plugin.simplerating.topic.dislike')}
                                                <li><i class="fa fa-thumbs-o-down"></i><span>{$oTopic->getCountVoteDown()}</span>
                                            {/if}
                                            <li><i class="fa fa-eye"></i><span>{$oTopic->getCountVoteAbstain()}</span>
                                            {hook run='topic_show_vote_stats' topic=$oTopic bTopicList=true}
                                        </ul>
                                    </div>'
                            data-html="true"
                                {/if}>
                            {if $bVoteInfoShow}
                                {if $oTopic->getRating() > 0}+{/if}{$oTopic->getRating()}
                            {else}
                                <i class="vote-tooltip vote-down link link-gray js-vote-rating link-clear"
                                   onclick="return ls.vote.vote('topic',{$oTopic->getId()},0,this);">&nbsp;?&nbsp;</i>
                            {/if}
                            </span>
        <a href="#" onclick="return false;" class="{$sVoteClass} vote-up link link link-gray link-clear js-vote-up"><i class="fa fa-thumbs-o-up"></i></a>
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
    {$sVoteClassOwner = ""}
    {if ($oUserCurrent && $oTopic->getUserId() == $oUserCurrent->getId())}{$sVoteClassOwner = "gray"}{/if}
    <li class="pull-right topic-rating js-vote end" data-target-type="topic" data-target-id="{$oTopic->getId()}">
    {if C::Get('plugin.simplerating.topic.dislike')}
        <a href="#" onclick="return false;" class="{$sVoteClass} {$sVoteClassOwner} vote-down link link-gray link-clear js-vote-down"><i class="fa fa-thumbs-o-down"></i></a>
    {/if}
                        <span class="vote-tooltip vote-total js-vote-rating {$sVoteClass} {if $oTopic->getRating() >= 0}green{else}red{/if}"
                                {if Config::Get('view.show_rating') || $bVoteInfoShow}
                            data-placement="top"
                            data-original-title='
                                    <div id="vote-info-topic-{$oTopic->getId()}">
                                        <ul class="vote-topic-info list-unstyled mal0">
                                            <li><i class="fa fa-thumbs-o-up"></i><span>{$oTopic->getCountVoteUp()}</span>
                                            {if C::Get('plugin.simplerating.topic.dislike')}
                                                <li><i class="fa fa-thumbs-o-down"></i><span>{$oTopic->getCountVoteDown()}</span>
                                            {/if}
                                            <li><i class="fa fa-eye"></i><span>{$oTopic->getCountVoteAbstain()}</span>
                                            {hook run='topic_show_vote_stats' topic=$oTopic bTopicList=false}
                                        </ul>
                                    </div>'
                            data-html="true"
                                {/if}>
                            {if $bVoteInfoShow}
                                {if $oTopic->getRating() > 0}+{/if}{$oTopic->getRating()}
                            {else}
                                <i class="vote-tooltip vote-down link link-gray js-vote-rating link-clear"
                                   onclick="return ls.vote.vote({$oTopic->getId()},this,0,'topic');">&nbsp;?&nbsp;</i>
                            {/if}
                            </span>
        <a href="#" onclick="return false;" class="{$sVoteClass} {$sVoteClassOwner} vote-up link link link-gray link-clear js-vote-up"><i class="fa fa-thumbs-o-up"></i></a>

    </li>
{/if}
{/if}