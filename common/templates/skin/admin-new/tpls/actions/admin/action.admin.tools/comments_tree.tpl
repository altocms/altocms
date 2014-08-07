{extends file='_index.tpl'}
{block name="content-body"}
<div class="col-md-12">
  <div class="panel panel-default">
    <div class="panel-body">
    <div class="callout callout-warning">
      {$sMessage}
    </div>
    </div>
    {if $bActionEnable}
    <form action="" method="post">
      <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
      <div class="panel-footer clearfix">
        <input type="submit" name="comments_tree_submit" value="{$aLang.action.admin.execute}"
          class="btn btn-primary pull-right"/>
      </div>
    </form>
    {/if}
    {/block}
  </div>
</div>