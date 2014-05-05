<div id="topic_question_area_{$oTopic->getId()}" class="poll js-poll" data-poll-id="{$oTopic->getId()}">
    <h4>{$oTopic->getQuestionTitle()}</h4>
    {if !$oTopic->getUserQuestionIsVote()}
        <ul class="list-unstyled poll-vote">
            {foreach $oTopic->getQuestionAnswers() as $key=>$aAnswer}
                <li class="radio js-poll-item">
                    <label>
                        <input type="radio" name="topic_answer_{$oTopic->getId()}" value="{$key}" />
                        {$aAnswer.text|escape:'html'}
                    </label>
                </li>
            {/foreach}
        </ul>
        <button type="submit" onclick="return ls.poll.vote(this,  -1);" class="btn btn-default">
            {$aLang.topic_question_abstain}
        </button>
        <button type="submit" onclick="return ls.poll.vote(this);" class="btn btn-success">
            {$aLang.topic_question_vote}
        </button>
    {else}
        <ul class="list-unstyled js-poll-result">
            {foreach $oTopic->getQuestionAnswers() as $key=>$aAnswer}
                <li class="{if $oTopic->getQuestionAnswerMax()==$aAnswer.count}most{/if} js-poll-result-item"
                        data-poll-item-pos="{$key}" data-poll-item-count="{$aAnswer.count}">
                    {$aAnswer.text|escape:'html'} <span class="text-muted">({$aAnswer.count})</span>
                    <span class="pull-right text-muted">{$oTopic->getQuestionAnswerPercent($key)}%</span>
                    <div class="progress">
                        <div class="progress-bar {if $oTopic->getQuestionAnswerMax()==$aAnswer.count}progress-bar-success{else}progress-bar-info{/if}" style="width: {$oTopic->getQuestionAnswerPercent($key)}%;"></div>
                    </div>
                </li>
            {/foreach}
        </ul>

        <button type="submit" class="btn btn-default btn-sm" title="{$aLang.topic_question_vote_result_sort}" onclick="return ls.poll.toggleSort(this);">
            <span class="glyphicon glyphicon-align-left"></span>
        </button>

        <span class="text-muted pull-right poll-total poll-total-result">
            <small>{$aLang.topic_question_vote_result}: {$oTopic->getQuestionCountVote()} | {$aLang.topic_question_abstain_result}: {$oTopic->getQuestionCountVoteAbstain()}</small>
        </span>
    {/if}
</div>
