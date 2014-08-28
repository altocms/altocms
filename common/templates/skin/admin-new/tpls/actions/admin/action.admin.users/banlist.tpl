{extends file='_index.tpl'}
{block name="content-bar"}
<div class="col-md-12">
  <a href="#" class="btn btn-primary pull-right disabled"><i class="glyphicon glyphicon-plus"></i></a>
  <ul class="nav nav-pills">
    <li class="{if $sMode=='ids'}active{/if}">
      <a href="{router page='admin'}users-banlist/ids/">
      {$aLang.action.admin.banlist_ids}
      </a>
    </li>
    <li class="{if $sMode=='ips'}active{/if}">
      <a href="{router page='admin'}users-banlist/ips/">
      {$aLang.action.admin.banlist_ips}
      </a>
    </li>
  </ul>
</div>
{/block}
{block name="content-body"}
<div class="col-md-9">
  {block name="content-body-main"}
  <div class="panel panel-default">
    <div class="panel-body no-padding">
      {block name="content-body-table"}
      {/block}
    </div>
  </div>
  {/block}
</div>
<div class="col-md-3">
  {block name="content-body-sidebar"}
  <div class="panel panel-default">
    <div class="panel-heading">
      <button class="btn-block btn left btn-primary" data-target="#admin_user_ban" data-toggle="collapse"
        data-parent="#user-comands-switch">
      {if $aFilter}<i class="icon icon-filter icon-green pull-right"></i>{/if}
      <i class="icon icon-ban"></i>
      {$aLang.action.admin.banlist_add}
      </button>
    </div>
    <div class="panel-heading no-padding noborder">
    <div class="collapse collapse-save" id="admin_user_ban">
      <form method="post" action="" class="well-well-small">
        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
        <div class="form-group {if $sUserFilterLogin}success{/if}">
          <label for="user_login">{$aLang.action.admin.user_login}</label>
          <div class="form-group">
            <input type="text" name="user_login" id="user_login" value="{$sUserFilterLogin}" class="form-control wide js-autocomplete-users"/>
          </div>
        </div>
        <div class="form-group {if $sUserFilterIp}success{/if}">
          <label for="user_ban_ip1">{$aLang.action.admin.user_ip}</label>
          <input type="text" name="user_ban_ip1" id="user_ban_ip1"
            value="{$aUserFilterIp.0}"
            maxlength="3"
            class="form-control ip-part" placeholder="*"/>
          <input type="text" name="user_ban_ip2" id="user_ban_ip2"
            value="{$aUserFilterIp.1}"
            maxlength="3"
            class="form-control ip-part" placeholder="*"/>
          <input type="text" name="user_ban_ip3" id="user_ban_ip3"
            value="{$aUserFilterIp.2}"
            maxlength="3"
            class="form-control ip-part" placeholder="*"/>
          <input type="text" name="user_ban_ip4" id="user_ban_ip4"
            value="{$aUserFilterIp.3}"
            maxlength="3"
            class="form-control ip-part" placeholder="*"/>
          <span class="help-block">{$aLang.action.admin.user_filter_ip_notice}</span>
        </div>
        <label>{$aLang.action.admin.ban_period}</label>
        <label class="radio">
        <input type="radio" name="ban_period" value="days"/>
        {$aLang.action.admin.ban_for}
        <input type="text" name="ban_days" id="ban_days"
          class="form-control num1"/> {$aLang.action.admin.ban_days}
        </label>
        <label class="radio">
        <input type="radio" name="ban_period" value="unlim" checked />
        {$aLang.action.admin.ban_unlim}
        </label>
        <label for="ban_comment">{$aLang.action.admin.ban_comment}</label>
        <input type="text" name="ban_comment" id="ban_comment" maxlength="255" class="form-control wide"/>
        <input type="hidden" name="user_list_sort" id="user_list_sort" value="{$sUserListSort}"/>
        <input type="hidden" name="user_list_order" id="user_list_order" value="{$sUserListOrder}"/>
        <input type="hidden" name="return-path" value="{Router::Url('url')}"/>
        <input type="hidden" name="adm_user_cmd" value="adm_ban_user"/>
      </form>
    </div>
    </div>
        <div class="panel-footer clearfix">
          <button type="submit" name="adm_action_submit" class="btn btn-danger">
          {$aLang.action.admin.users_ban}
          </button>
        </div>
  </div>
  {/block}
</div>
<form action="" method="post" id="ban-do-command">
  <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
  <input type="hidden" name="adm_user_cmd" value=""/>
  <input type="hidden" name="bans_list" value=""/>
  <input type="hidden" name="return_url" value="{$PATH_WEB_CURRENT|escape:'html'}"/>
</form>
<script>
  var admin = admin || { };
  admin.user = admin.user || { };
  admin.user.unsetBan = function(id, mode) {
      var form = $('#ban-do-command');
      if (form.length) {
          form.find('[name=adm_user_cmd]').val('adm_unsetban_' + mode);
          form.find('[name=bans_list]').val(id);
          form.submit();
      }
  }
</script>
{/block}