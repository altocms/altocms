{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="#" class="btn btn-primary disabled"><i class="icon-plus-sign"></i></a>
    </div>
    <div class="btn-group">
        <a class="btn {if $sMode=='ids'}active{/if}" href="{router page='admin'}banlist/ids/">
            {$aLang.action.admin.banlist_ids}
        </a>
        <a class="btn {if $sMode=='ips'}active{/if}" href="{router page='admin'}banlist/ips/">
            {$aLang.action.admin.banlist_ips}
        </a>
    </div>
{/block}

{block name="content-body"}

<div class="span12">
    <div class="span9">
        {block name="content-body-main"}

            <div class="b-wbox">
                <div class="b-wbox-content nopadding">
                    {block name="content-body-table"}
                    {/block}
                </div>
            </div>
        {/block}
    </div>

    <div class="span3 sidebar">
        {block name="content-body-sidebar"}

            <div class="accordion-group no-border">
                <div class="accordion-heading">
                    <button class="btn-block btn left" data-target="#admin_form_ban" data-toggle="collapse"
                            data-parent="#user-comands-switch">
                        {if $aFilter}<i class="icon-filter icon-green pull-right"></i>{/if}
                        <i class="icon-ban-circle"></i>
                        {$aLang.action.admin.banlist_add}
                    </button>
                </div>

                <div class="accordion-body collapse collapse-save" id="admin_form_ban">
                    <form method="post" action="" class="well well-small">
                        <input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}"/>

                        <div class="row control-group {if $sUserFilterLogin}success{/if}">
                            <label for="user_login">{$aLang.action.admin.user_login}</label>

                            <div class="input-prepend">
                                <span class="add-on"><i class="icon-user"></i></span><input type="text"
                                                                                            name="user_login"
                                                                                            id="user_login"
                                                                                            value="{$sUserFilterLogin}"
                                                                                            class="wide"/>
                            </div>
                        </div>

                        <div class="row control-group {if $sUserFilterIp}success{/if}">
                            <label for="user_ban_ip1">{$aLang.action.admin.user_ip}</label>
                            <input type="text" name="user_ban_ip1" id="user_ban_ip1"
                                   value="{$aUserFilterIp.0}"
                                   maxlength="3"
                                   class="ip-part" placeholder="*"/> &bull;
                            <input type="text" name="user_ban_ip2" id="user_ban_ip2"
                                   value="{$aUserFilterIp.1}"
                                   maxlength="3"
                                   class="ip-part" placeholder="*"/> &bull;
                            <input type="text" name="user_ban_ip3" id="user_ban_ip3"
                                   value="{$aUserFilterIp.2}"
                                   maxlength="3"
                                   class="ip-part" placeholder="*"/> &bull;
                            <input type="text" name="user_ban_ip4" id="user_ban_ip4"
                                   value="{$aUserFilterIp.3}"
                                   maxlength="3"
                                   class="ip-part" placeholder="*"/>
                            <span class="help-block">{$aLang.action.admin.user_filter_ip_notice}</span>
                        </div>

                        <label>{$aLang.action.admin.ban_period}</label>
                        <label class="radio">
                            <input type="radio" name="ban_period" value="days"/>
                            {$aLang.action.admin.ban_for}
                            <input type="text" name="ban_days" id="ban_days"
                                   class="num1"/> {$aLang.action.admin.ban_days}
                        </label>

                        <label class="radio">
                            <input type="radio" name="ban_period" value="unlim" checked/>
                            {$aLang.action.admin.ban_unlim}
                        </label>

                        <label for="ban_comment">{$aLang.action.admin.ban_comment}</label>
                        <input type="text" name="ban_comment" id="ban_comment" maxlength="255"/>

                        <input type="hidden" name="user_list_sort" id="user_list_sort" value="{$sUserListSort}"/>
                        <input type="hidden" name="user_list_order" id="user_list_order" value="{$sUserListOrder}"/>
                        <input type="hidden" name="return_url" value="{$sPageRef}"/>
                        <input type="hidden" name="adm_user_cmd" value="adm_user_ban"/>
                        <div class="form-actions">
                            <button type="submit" name="adm_action_submit"
                                    class="btn btn-danger">{$aLang.action.admin.users_ban}</button>
                        </div>
                    </form>
                </div>
            </div>
        {/block}
    </div>

</div>

    <form action="" method="post" id="ban-do-command">
        <input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}"/>
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
