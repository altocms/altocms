{extends file='_index.tpl'}
{block name="content-bar"}
<div class="col-md-12">
  <a href="{router page='admin'}site-plugins/add/" class="btn btn-primary active pull-right"
    title="{$aLang.action.admin.plugin_load}"><i class="glyphicon glyphicon-plus"></i></a>
  <ul class="nav nav-pills">
    </li>
    <li class="{if $sMode=='all' || $sMode==''}active{/if}">
      <a href="{router page='admin'}site-plugins/list/all/">
      {$aLang.action.admin.all_plugins}
      </a>
    </li>
    <li class="{if $sMode=='active'}active{/if}">
      <a href="{router page='admin'}site-plugins/list/active/">
      {$aLang.action.admin.active_plugins}
      </a>
    </li>
    <li class="{if $sMode=='inactive'}active{/if}">
      <a href="{router page='admin'}site-plugins/list/inactive/">
      {$aLang.action.admin.inactive_plugins}
      </a>
    </li>
  </ul>
</div>
{/block}
{block name="content-body"}
<div class="col-md-12">
  <form action="" method="post" id="form_plugins_add" enctype="multipart/form-data" class="uniform">
    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
    <div class="panel panel-default">
      <div class="panel-body">
        <input type="file" id="plugin_arc" name="plugin_arc"/>
      </div>
    <div class="panel-footer clearfix">
      <button type="submit" name="submit_plugins_del" class="btn btn-primary pull-right">
      {$aLang.action.admin.plugin_load}
      </button>
    </div>
    </div>
  </form>
</div>
{/block}