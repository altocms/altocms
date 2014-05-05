<div class="panel panel-default">
    <div class="panel-heading">
        <h5 class="panel-title">
            <a data-toggle="collapse" href="#topic-field-poll">
                {$aLang.topic_field_poll_add}
            </a>
        </h5>
    </div>
    <div id="topic-field-poll" class="panel-collapse collapse {if $_aRequest.topic_field_question}in{/if}">
        <div class="panel-body form-group topic-poll-add js-poll-edit">
            <label>{$aLang.topic_question_create_question}:</label>
            <input type="text" value="{$_aRequest.topic_field_question}" name="topic_field_question"
                   class="form-control" {if $bEditDisabled}disabled{/if} />
            <br/>
            <label>{$aLang.topic_question_create_answers}</label>
            <ul class="list-unstyled topic-poll-add-list js-poll-list">
                {if count($_aRequest.topic_field_answers)>=2}
                    {foreach $_aRequest.topic_field_answers as $i=>$sAnswer}
                        <li class="topic-poll-add-item js-poll-item">
                            <input type="text" value="{$sAnswer}" name="topic_field_answers[]"
                                   class="form-control" {if $bEditDisabled}disabled{/if} />
                            {if !$bEditDisabled AND $i>1}
                                <a href="#" class="glyphicon glyphicon-remove btn-remove" title="{$aLang.delete}" onclick="return ls.poll.removeItem(this);"></a>
                            {/if}
                        </li>
                    {/foreach}
                {else}
                    <li class="topic-poll-add-item js-poll-edit-item">
                        <input type="text" value="" name="topic_field_answers[]"
                               class="form-control" {if $bEditDisabled}disabled{/if} />
                        <a href="#" class="glyphicon glyphicon-remove" title="{$aLang.delete}"></a>
                    </li>
                    <li class="topic-poll-add-item js-poll-edit-item">
                        <input type="text" value="" name="topic_field_answers[]"
                               class="form-control" {if $bEditDisabled}disabled{/if} />
                        <a href="#" class="glyphicon glyphicon-remove" title="{$aLang.delete}"></a>
                    </li>
                {/if}
            </ul>

            {if !$bEditDisabled}
                <a href="#" class="link-dotted js-poll-add-button">{$aLang.topic_question_create_answers_add}</a>
            {/if}
        </div>
    </div>
</div>

