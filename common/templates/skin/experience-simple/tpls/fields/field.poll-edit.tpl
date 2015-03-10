 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<script>
//    $(function(){
//        ls.poll.init();
//    })
</script>
<div class="add-ask">
    <div class="clearfix toggle-block">
        <a href="#"
           onclick="$(this).toggleClass('active').parent().next().slideToggle(100); return false;"
           class="pull-right toggle-link link link-lead link-blue"><i class="fa fa-plus-circle"></i>{$aLang.topic_field_poll_add}</a>
    </div>
    <div style="display: none;">
        <div class="form-group js-poll-edit">
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-question-circle"></i></span>
                <input type="text"
                       placeholder="{$aLang.topic_question_create_question}"
                       value="{$_aRequest.topic_field_question}"
                       name="topic_field_question"
                       class="form-control" {if $bEditDisabled}disabled{/if} />
            </div>


            <ul class="list-unstyled topic-poll-add-list js-poll-list answers">
                {if count($_aRequest.topic_field_answers)>=2}
                    {foreach $_aRequest.topic_field_answers as $i=>$sAnswer}
                        <li class="topic-poll-add-item js-poll-item">
                            <div class="form-group has-feedback">
                                <div class="input-group">
                                    <span class="input-group-addon">{$aLang.variant}</span>
                                    <input type="text" value="{$sAnswer}" name="topic_field_answers[]" class="form-control" {if $bEditDisabled}disabled{/if} />
                                    {if !$bEditDisabled AND $i>1}
                                        <a href="#" class="link link-lead link-blue form-control-feedback"
                                           title="{$aLang.delete}" onclick="return ls.poll.removeItem(this);"><i class="fa fa-minus"></i></a>
                                    {/if}
                                </div>
                            </div>
                        </li>
                    {/foreach}
                {else}
                    <li class="topic-poll-add-item js-poll-item">
                        <div class="form-group has-feedback">
                            <div class="input-group">
                                <span class="input-group-addon">{$aLang.variant}</span>
                                <input type="text" value="{$sAnswer}" name="topic_field_answers[]" class="form-control" {if $bEditDisabled}disabled{/if} />
                                {if !$bEditDisabled AND $i>1}
                                    <a href="#" class="link link-lead link-blue form-control-feedback"
                                       title="{$aLang.delete}" onclick="return ls.poll.removeItem(this);"><i class="fa fa-minus"></i></a>
                                {/if}
                            </div>
                        </div>
                    </li>
                    <li class="topic-poll-add-item js-poll-item">
                        <div class="form-group has-feedback">
                            <div class="input-group">
                                <span class="input-group-addon">{$aLang.variant}</span>
                                <input type="text" value="{$sAnswer}" name="topic_field_answers[]" class="form-control" {if $bEditDisabled}disabled{/if} />
                                {if !$bEditDisabled AND $i>1}
                                    <a href="#" class="link link-lead link-blue form-control-feedback"
                                       title="{$aLang.delete}" onclick="return ls.poll.removeItem(this);"><i class="fa fa-minus"></i></a>
                                {/if}
                            </div>
                        </div>
                    </li>
                {/if}
            </ul>
            {if !$bEditDisabled}
                <a href="#"
                   onclick="return false;"
                   class="toggle-link link link-lead link-blue js-poll-add-button">
                    <i class="fa fa-plus"></i>{$aLang.topic_question_create_answers_add}
                </a>
            {/if}
        </div>
    </div>
</div>



