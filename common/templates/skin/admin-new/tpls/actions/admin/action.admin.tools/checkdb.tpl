{extends file="_index.tpl"}
{block name="content-body"}

<div class="col-md-6">
  <div class="panel panel-default">
    <div class="panel-body">
		{$aLang.action.admin.checkdb_deleted_blogs}
    </div>
    <div class="panel-footer clearfix">
    <a class="btn btn-primary pull-right" href="{router page="admin"}tools-checkdb/blogs/">{$aLang.action.admin.execute}</a>
  	</div>
  </div>
</div>

<div class="col-md-6">
  <div class="panel panel-default">
    <div class="panel-body">
		{$aLang.action.admin.checkdb_deleted_topics}
    </div>
    <div class="panel-footer clearfix">
    <a class="btn btn-primary pull-right" href="{router page="admin"}tools-checkdb/topics/">{$aLang.action.admin.execute}</a>
  	</div>
  </div>
</div>

{hook run='admin_action_db_item'}

{/block}