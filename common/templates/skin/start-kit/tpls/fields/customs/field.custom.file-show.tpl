{if $oField}
    {$oFile=$oTopic->getFieldFile($oField->getFieldId())}
    {if $oFile}
        <p>
            <b>{$oField->getFieldName()}</b>:
            <a href="{router page="download"}file/{$oTopic->getTopicId()}/{$oField->getFieldId()}/?security_ls_key={$ALTO_SECURITY_KEY}">{$oFile->getFileName()|escape:'html'}</a>
            {$oFile->getSizeFormat()}
            ({$aLang.content_count_downloads}: {$oFile->getFileDownloads()})
        </p>
    {/if}
{/if}