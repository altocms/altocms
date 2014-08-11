{if $oField}
    {$iFieldId=$oField->getFieldId()}
    <div class="form-group">
        {if $_aRequest.fields.$iFieldId}
            <label for="topic_delete_file_{$iFieldId}">
                <input type="checkbox" id="topic_delete_file_{$iFieldId}"
                       name="topic_delete_file_{$iFieldId}"
                       value="on"> &mdash; {$aLang.content_delete_file}
                ({$_aRequest.fields.$iFieldId.file_name|escape:'html'})
            </label>
        {else}
            <label for="topic_upload_file">
                {$oField->getFieldName()}{if $_aRequest.fields.$iFieldId} ({$aLang.content_file_replace}){/if}
            </label><br/>
            <span class="btn btn-default btn-file">
            {$aLang.uploadimg_file}
                <input type="file" name="fields_{$iFieldId}" id="fields-{$iFieldId}">
        </span>

            <p class="help-block">
                <small class="note">{$oField->getFieldDescription()}</small>
            </p>
        {/if}
    </div>
{/if}