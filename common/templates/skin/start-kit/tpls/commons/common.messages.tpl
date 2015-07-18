{if !$noShowSystemMessage}
    {if $aMsgError}
        <div class="alert alert-danger alert-message">
            <span class="alert-message-sign glyphicon glyphicon-exclamation-sign"></span>
            <ul class="list-unstyled alert-message-list">
                {foreach $aMsgError as $aMsg}
                    <li>
                        {if $aMsg.title!=''}
                            <strong>{$aMsg.title}</strong>:
                        {/if}
                        {$aMsg.msg}
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}


    {if $aMsgNotice}
        <div class="alert alert-success alert-message">
            <span class="alert-message-sign glyphicon glyphicon-info-sign"></span>
            <ul class="list-unstyled alert-message-list">
                {foreach $aMsgNotice as $aMsg}
                    <li>
                        {if $aMsg.title!=''}
                            <strong>{$aMsg.title}</strong>:
                        {/if}
                        {$aMsg.msg}
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}
{/if}
