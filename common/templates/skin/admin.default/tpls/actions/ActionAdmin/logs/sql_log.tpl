{extends file='_index.tpl'}

{block name="content-body"}

<div class="span12">

    <div class="b-wbox">
        <div class="b-wbox-header">
        </div>
        <div class="b-wbox-content">
            {if $aLogs}
                {foreach $aLogs as $aRec}
                    <div class="b-log-date">{$aRec.date} </div>
                    <div class="b-log-text">{$aRec.text|escape:'html'}</div>
                    <div class="b-log-result">
                        <div class="b-log-result-time">{$aRec.time}</div>
                        <div class="b-log-result-text">{$aRec.result|escape:'html'}</div>
                    </div>
                {/foreach}
            {else}
                <pre>No data</pre>
            {/if}
        </div>
    </div>

    <form action="" method="post">
        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>

        <div class="navbar navbar-inner">
                <button type="submit" name="submit_logs_del" class="btn btn-danger pull-right {if !$aLogs}disabled{/if}">
                    {$aLang.action.admin.delete}
                </button>
        </div>
    </form>

</div>

{/block}