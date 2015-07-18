 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if $oField}
    {$oFile = $oTopic->getFieldFile($oField->getFieldId())}
    {if $oFile}
        <p>
            <strong>{$oField->getFieldName()}</strong>:

            <a href="{router page="download"}file/{$oTopic->getTopicId()}/{$oField->getFieldId()}/?security_ls_key={$ALTO_SECURITY_KEY}">{$oFile->getFileName()|escape:'html'}</a>
            {$oFile->getSizeFormat()}
            <span class="small muted">, ({$aLang.content_count_downloads}: {$oFile->getFileDownloads()})</span>
        </p>
    {/if}
{/if}