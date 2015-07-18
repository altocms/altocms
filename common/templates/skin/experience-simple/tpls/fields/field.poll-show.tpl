 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<div id="topic_question_area_{$oTopic->getId()}" class="poll js-poll" data-poll-id="{$oTopic->getId()}">
    <h4 class="accent">
        {$oTopic->getQuestionTitle()}
        <button type="submit" class="btn btn-light pull-right" title="{$aLang.topic_question_vote_result_sort}" onclick="return ls.poll.toggleSort(this);">
            <span class="fa fa-sort"></span>
        </button>
        <br/>
        <br/>
    </h4>
    {if !$oTopic->getUserQuestionIsVote()}

            {foreach $oTopic->getQuestionAnswers() as $key=>$aAnswer}
                    <div class="radio js-poll-item">
                        <input id="radio-{$key}" type="radio" name="topic_answer_{$oTopic->getId()}" value="{$key}" />
                        <label for="radio-{$key}">{$aAnswer.text|escape:'html'}</label>
                    </div>

            {/foreach}
        <button type="submit" onclick="return ls.poll.vote(this,  -1);" class="btn btn-light btn-normal corner-no">
            {$aLang.topic_question_abstain}
        </button>
        <button type="submit" onclick="return ls.poll.vote(this);" class="btn btn-blue btn-normal corner-no">
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



        <div class="tar">
            <span class="text-muted poll-total poll-total-result">
                <small>{$aLang.topic_question_vote_result}: {$oTopic->getQuestionCountVote()} | {$aLang.topic_question_abstain_result}: {$oTopic->getQuestionCountVoteAbstain()}</small>
            </span>
        </div>
    {/if}
</div>
