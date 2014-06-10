{if $oField}
    {$oFile=$oTopic->getFile($oField->getFieldId())}
    {if $oFile}
        <p>
            <b>{$oField->getFieldName()}</b>:
            {$oFile=$oTopic->getFile($oField->getFieldId())}

            <a href="{router page="download"}file/{$oTopic->getTopicId()}/{$oField->getFieldId()}/?security_ls_key={$ALTO_SECURITY_KEY}">{$oFile->getFileName()|escape:'html'}</a>
            {$oFile->getSizeFormat()}
            ({$aLang.content_count_downloads}: {$oFile->getFileDownloads()})
        </p>
    {/if}
{/if}