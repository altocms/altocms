{extends file='_index.tpl'}
{block name="content-bar"}
<div class="col-md-12 mb15">
    <a href="#" class="btn btn-primary disabled"><i class="glyphicon glyphicon-plus"></i></a>
</div>
{/block}
{block name="content-body"}
<div class="col-md-12">
  <form method="POST" enctype="multipart/form-data" class="form-horizontal">
    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
    <div class="panel panel-default">
      <div class="panel-heading">
        <div class="panel-title">{$aLang.action.admin.userrights_blogs_title}</div>
      </div>
      <div class="panel-body">
        <div class="form-group">
          <label class="col-sm-2 control-label">
          {$aLang.blog_user_administrators}:
          </label>
          <div class="col-sm-10">
            <label class="col-sm-12">
            <input type="checkbox" name="userrights_administrator[control_users]" value="1"
            {if $_aRequest.userrights_administrator.control_users}checked{/if}/>
            {$aLang.action.admin.userrights_blogs_control_users}
            </label>
            <label class="col-sm-12">
            <input type="checkbox" name="userrights_administrator[edit_blog]" value="1"
            {if $_aRequest.userrights_administrator.edit_blog}checked{/if}/>
            {$aLang.action.admin.userrights_blogs_edit_blog}
            </label>
            <label class="col-sm-12">
            <input type="checkbox" name="userrights_administrator[edit_content]" value="1"
            {if $_aRequest.userrights_administrator.edit_content}checked{/if}/>
            {$aLang.action.admin.userrights_blogs_edit_content}
            </label>
            <label class="col-sm-12">
            <input type="checkbox" name="userrights_administrator[delete_content]" value="1"
            {if $_aRequest.userrights_administrator.delete_content}checked{/if}/>
            {$aLang.action.admin.userrights_blogs_delete_content}
            </label>
            <label class="col-sm-12">
            <input type="checkbox" name="userrights_administrator[edit_comment]" value="1"
            {if $_aRequest.userrights_administrator.edit_comment}checked{/if}/>
            {$aLang.action.admin.userrights_blogs_edit_comment}
            </label>
            <label class="col-sm-12">
            <input type="checkbox" name="userrights_administrator[delete_comment]" value="1"
            {if $_aRequest.userrights_administrator.delete_comment}checked{/if}/>
            {$aLang.action.admin.userrights_blogs_delete_comment}
            </label>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label">
          {$aLang.blog_user_moderators}:
          </label>
          <div class="col-sm-10">
            <label class="col-sm-12">
            <input type="checkbox" name="userrights_moderator[control_users]" value="1"
            {if $_aRequest.userrights_moderator.control_users}checked{/if}/>
            {$aLang.action.admin.userrights_blogs_control_users}
            </label>
            <label class="col-sm-12">
            <input type="checkbox" name="userrights_moderator[edit_blog]" value="1"
            {if $_aRequest.userrights_moderator.edit_blog}checked{/if}/>
            {$aLang.action.admin.userrights_blogs_edit_blog}
            </label>
            <label class="col-sm-12">
            <input type="checkbox" name="userrights_moderator[edit_content]" value="1"
            {if $_aRequest.userrights_moderator.edit_content}checked{/if}/>
            {$aLang.action.admin.userrights_blogs_edit_content}
            </label>
            <label class="col-sm-12">
            <input type="checkbox" name="userrights_moderator[delete_content]" value="1"
            {if $_aRequest.userrights_moderator.delete_content}checked{/if}/>
            {$aLang.action.admin.userrights_blogs_delete_content}
            </label>
            <label class="col-sm-12">
            <input type="checkbox" name="userrights_moderator[edit_comment]" value="1"
            {if $_aRequest.userrights_moderator.edit_comment}checked{/if}/>
            {$aLang.action.admin.userrights_blogs_edit_comment}
            </label>
            <label class="col-sm-12">
            <input type="checkbox" name="userrights_moderator[delete_comment]" value="1"
            {if $_aRequest.userrights_moderator.delete_comment}checked{/if}/>
            {$aLang.action.admin.userrights_blogs_delete_comment}
            </label>
          </div>
        </div>
      </div>
      <div class="panel-footer clearfix">
        <button type="submit" class="btn btn-primary pull-right"
          name="submit_type_add">{$aLang.action.admin.save}</button>
        {if $sEvent=='add'}
        <p><span class="help-block">{$aLang.action.admin.content_afteradd}</span></p>
        {/if}
      </div>
    </div>
  </form>
</div>
{/block}