 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

    {if $aMsgError}
        <div class="bg-danger tags-about system-msg">
            <ul class="list-unstyled mab0">
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
        <div class="bg-warning tags-about system-msg">
            <ul class="list-unstyled mab0">
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

