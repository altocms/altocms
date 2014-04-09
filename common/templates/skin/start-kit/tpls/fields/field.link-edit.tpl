<div class="panel panel-default">
    <div class="panel-heading">
        <h5 class="panel-title">
            <a data-toggle="collapse" href="#topic-field-link">
                {$aLang.topic_field_link_add}
            </a>
        </h5>
    </div>
    <div id="topic-field-link" class="panel-collapse collapse {if $_aRequest.topic_field_link}in{/if}">
        <div class="panel-body form-group">
            <label for="topic-field-link-input">{$aLang.topic_field_link_label}:</label>
            <input type="text" id="topic-field-link-input" name="topic_field_link" value="{$_aRequest.topic_field_link}"
                   class="input-text form-control"/>

            <p class="help-block">
                <small class="note">{$aLang.topic_field_link_notice}</small>
            </p>
        </div>
    </div>
</div>

