{if !$noShowSystemMessage}
    {if $aMsgError}
        <div class="alert alert-danger">
            <ul class="list-unstyled">
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
        <div class="alert alert-success">
            <ul class="list-unstyled">
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
