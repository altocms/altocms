{if $oField}
    {$oFile=$oTopic->getFieldFile($oField->getFieldId())}
    {if $oFile}
        <p>
            <strong>{$oField->getFieldName()}</strong>:
            <a href="{R::GetLink("download")}file/{$oTopic->getTopicId()}/{$oField->getFieldId()}/?security_ls_key={$ALTO_SECURITY_KEY}">{$oFile->getFileName()|escape:'html'}</a>
            {$oFile->getSizeFormat()}
            ({$aLang.content_count_downloads}: {$oFile->getFileDownloads()})
        </p>
    {/if}
{/if}