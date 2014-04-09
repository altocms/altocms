<div id="topic_question_area_{$oTopic->getId()}" class="poll">
    {$oTopic->getQuestionTitle()}
    {if !$oTopic->getUserQuestionIsVote()}
        <ul class="list-unstyled poll-vote">
            {foreach $oTopic->getQuestionAnswers() as $key=>$aAnswer}
                <li class="radio"><label><input type="radio" id="topic_answer_{$oTopic->getId()}_{$key}"
                                                name="topic_answer_{$oTopic->getId()}" value="{$key}"
                                                onchange="jQuery('#topic_answer_'+'{$oTopic->getId()}_value').val(jQuery(this).val());"/>{$aAnswer.text|escape:'html'}
                    </label></li>
            {/foreach}
        </ul>
        <button type="submit"
                onclick="ls.poll.vote({$oTopic->getId()},jQuery('#topic_answer_'+'{$oTopic->getId()}_value').val());"
                class="btn btn-success">{$aLang.topic_question_vote}</button>
        <button type="submit" onclick="ls.poll.vote({$oTopic->getId()},-1)"
                class="btn btn-default">{$aLang.topic_question_abstain}</button>
        <input type="hidden" id="topic_answer_{$oTopic->getId()}_value" value="-1"/>
    {else}
        <ul class="list-unstyled poll-result" id="poll-result-original-{$oTopic->getId()}">
            {foreach $oTopic->getQuestionAnswers() as $key=>$aAnswer}
                <li {if $oTopic->getQuestionAnswerMax()==$aAnswer.count}class="most"{/if}>
                    {$aAnswer.text|escape:'html'} <span class="text-muted">({$aAnswer.count})</span>
                    <span class="pull-right text-muted">{$oTopic->getQuestionAnswerPercent($key)}%</span>
                    <div class="progress">
                        <div class="progress-bar {if $oTopic->getQuestionAnswerMax()==$aAnswer.count}progress-bar-success{else}progress-bar-info{/if}" style="width: {$oTopic->getQuestionAnswerPercent($key)}%;"></div>
                    </div>
                </li>
            {/foreach}
        </ul>


        <ul class="list-unstyled poll-result" id="poll-result-sort-{$oTopic->getId()}" style="display: none;">
            {foreach from=$oTopic->getQuestionAnswers(true) key=key item=aAnswer}
                <li {if $oTopic->getQuestionAnswerMax()==$aAnswer.count}class="most"{/if}>
                    {$aAnswer.text|escape:'html'} <span class="text-muted">({$aAnswer.count})</span>
                    <span class="pull-right text-muted">{$oTopic->getQuestionAnswerPercent($key)}%</span>
                    <div class="progress">
                        <div class="progress-bar {if $oTopic->getQuestionAnswerMax()==$aAnswer.count}progress-bar-success{else}progress-bar-info{/if}" style="width: {$oTopic->getQuestionAnswerPercent($key)}%;"></div>
                    </div>
                </li>
            {/foreach}
        </ul>

        <button type="submit" class="btn btn-default btn-sm" title="{$aLang.topic_question_vote_result_sort}" onclick="return ls.poll.switchResult(this, {$oTopic->getId()});"><span class="glyphicon glyphicon-align-left"></span></button>

        <span class="text-muted pull-right poll-total poll-total-result"><small>{$aLang.topic_question_vote_result}: {$oTopic->getQuestionCountVote()} | {$aLang.topic_question_abstain_result}: {$oTopic->getQuestionCountVoteAbstain()}</small></span>
    {/if}
</div>
