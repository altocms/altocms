{extends file='_index.tpl'}
{block name="content-body"}
<div class="col-md-12">
  <div class="panel panel-default noborder">
    <div class="panel-body">
      {if $aLogs}
      {foreach $aLogs as $aRec}
      <div class="alert alert-warning alert-dismissable">
          <i class="fa fa-warning"></i>
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <b>{$aRec.date}:</b>{$aRec.text}
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