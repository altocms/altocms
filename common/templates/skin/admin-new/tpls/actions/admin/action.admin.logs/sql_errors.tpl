{extends file='_index.tpl'}
{block name="content-body"}
<div class="col-md-12">
  <div class="panel panel-default noborder">
    <div class="panel-body">
      {if $aLogs}
      {foreach $aLogs as $aRec}
      <div class="b-log-date">{$aRec.date} </div>
      <div class="b-log-text">{$aRec.text}</div>
      <div class="b-log-result">
        <div class="b-log-result-time">{$aRec.time}</div>
        <div class="b-log-result-text">{$aRec.sql|escape:'html'}</div>
      </div>
      {/foreach}
      {else}
      <pre>No data</pre>
      {/if}
    </div>
    <form action="" method="post">
      <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
      <div class="panel-footer clearfix">
        <button type="submit" name="submit_logs_del" class="btn btn-danger pull-right {if !$aLogs}disabled{/if}">
        {$aLang.action.admin.delete}
        </button>
      </div>
    </form>
  </div>
</div>
{/block}