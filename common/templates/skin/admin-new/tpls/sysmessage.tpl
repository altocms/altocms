{block name="sysmessage"}
{if !$noShowSystemMessage AND ($aMsgError OR $aMsgNotice)}
{if $aMsgError}
{foreach from=$aMsgError item=aMsg}
<div class="col-md-12">
<div class="pad margin no-print">
  <div class="alert alert-danger" style="">
    <i class="fa fa-info"></i>
    {if $aMsg.title!=''}<b>{$aMsg.title}:</b>{/if} {$aMsg.msg}
  </div>
</div>
</div>
{/foreach}
{/if}
{if $aMsgNotice}
{foreach from=$aMsgNotice item=aMsg}
<div class="col-md-12">
<div class="pad margin no-print">
  <div class="alert alert-success" style="">
    <i class="fa fa-info"></i>
    {if $aMsg.title!=''}<b>{$aMsg.title}:</b>{/if} {$aMsg.msg}
  </div>
</div>
</div>
{/foreach}
{/if}
{/if}
{/block}