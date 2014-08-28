{extends file='_index.tpl'}
{block name="content-body"}
<div class="col-md-12">
  <a href="{router page='admin'}site-plugins/add/" class="btn btn-primary pull right"
    title="{$aLang.action.admin.plugin_load}"><i class="glyphicon glyphicon-plus"></i></a>
  <ul class="nav nav-pills">
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
<div class="col-md-12">
  <form action="{router page='admin'}plugins/" method="post" id="form_plugins_list">
    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
    <div class="panel panel-default">
      <div class="panel-body">
      </div>
    </div>
  </form>
</div>
{/block}