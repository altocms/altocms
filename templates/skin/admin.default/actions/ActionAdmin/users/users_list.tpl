{extends file='actions/ActionAdmin/users/users.tpl'}

{block name="content-body-main"}
{$bShowIpColumns=false}
<div class="b-wbox">
    <div class="b-wbox-content nopadding">
        <table class="table table-condensed b-users-list uniform">
            <thead>
            <tr>
                <th>
                    <input type="checkbox" id="id_0" onclick="admin.selectAllUsers(this);"/>
                </th>
                <th>
                    ID
                </th>
                <th>
                    {$aLang.user}
                </th>
                <th>
                    {$aLang.action.admin.users_date_reg}
                </th>
                {if $bShowIpColumns}
                <th>
                    {$aLang.action.admin.users_ip_reg}
                </th>
                {/if}
                <th>
                    E-mail
                </th>
                {if $oConfig->GetValue('general.reg.activation')}
                    <th>
                        {$aLang.action.admin.users_activated}
                    </th>
                {/if}
                <th>
                    {$aLang.action.admin.users_last_activity}
                </th>
                {if $bShowIpColumns}
                <th>
                    Last IP
                </th>
                {/if}
                <th>{if $sMode!='admins'}{$aLang.action.admin.users_banned}{else}&nbsp;{/if}</th>
            </tr>
            </thead>

            <tbody>
                {foreach $aUsers as $oUser}
                    {if $oConfig->GetValue('general.reg.activation') AND !$oUser->getDateActivate()}
                        {assign var=classIcon value='icon-gray'}
                    {elseif $oUser->isAdministrator()}
                        {assign var=classIcon value='icon-green'}
                    {elseif $oUser->isBanned()}
                        {assign var=classIcon value='icon-red'}
                    {else}
                        {assign var=classIcon value=''}
                    {/if}
                    {assign var="oSession" value=$oUser->getSession()}
                    {if $oSession}{$sLastIp=$oSession->getIpLast()}{else}{$sLastIp=""}{/if}
                <tr class="selectable">
                    <td class="check-row">
                        {if $oUserCurrent->GetId()!=$oUser->getId()}
                            <input type="checkbox" id="login_{$oUser->GetLogin()}" onclick="admin.user.select()"/>
                        {else}
                            &nbsp;
                        {/if}
                    </td>
                    <td class="number"> {$oUser->getId()} &nbsp;</td>
                    <td>
                        <i class="icon-user {$classIcon}"></i>
                        <a href="{router page='admin'}users/profile/{$oUser->getId()}/"
                           {if $oUserCurrent->GetId()==$oUser->getId()}style="font-weight:bold;"{/if}
                           class="link">{$oUser->getLogin()}</a>
                        <div class="pull-right">
                        <i class="icon-globe {if $oUser->IsOnline()}icon-green{else}icon-gray{/if}" data-toggle="popover" data-popover="#user-win-iplist"
                           onclick="admin.user.setIpInfo('{$oUser->getIpRegister()}', '{$sLastIp}')"></i>
                            {if $oUser->IsOnline()}<div class="status-on">on</div>{else}<div class="status-off">off</div>{/if}
                        </div>
                    </td>
                    <td class="center">{$oUser->getDateRegister()}</td>
                    {if $bShowIpColumns}
                    <td class="center ip-split">
                        {$oUser->getIpRegister()}
                    </td>
                    {/if}
                    <td>{$oUser->getUserMail()}</td>
                    {if $oConfig->GetValue('general.reg.activation')}
                        <td>&nbsp;
                            {if $oUser->getDateActivate()}{$oUser->getDateActivate()}
                                {else}<a
                                    href="{router page='admin'}users/activate/{$oUser->getLogin()}/?security_ls_key={$ALTO_SECURITY_KEY}">{$aLang.action.admin.users_activate}</a>{/if}
                        </td>
                    {/if}
                    <td class="center">
                        {if $oSession}{$oSession->getDateLast()}{/if}
                    </td>
                    {if $bShowIpColumns}
                    <td class="center ip-split">
                        {$sLastIp}
                    </td>
                    {/if}
                    {if $sMode=='admins'}
                        <td class="center">
                            {if $oUser->GetLogin()!='admin'}
                                <a href="#" onclick="admin.user.unsetAdmin('{$oUser->GetLogin()}')"
                                   class="link tip-top" title="{$aLang.action.admin.exclude}"><i class="icon-remove-sign"></i></a>&nbsp;
                            {/if}
                        </td>
                        {else}
                        <td class="center">{if $oUser->isBanned()}{if $oUser->getBanLine()}{$oUser->getBanLine()}{else}
                            unlim{/if}{/if}</td>
                    {/if}
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>

{include file='inc.paging.tpl'}

<div id="user-win-iplist" class="popover right out">
    <div class="arrow"></div>
    <div class="popover-title">
        <a href="#" class="close" data-dismiss="popover">&times;</a>
        {$aLang.action.admin.user_ip_addresses}
    </div>
    <div class="popover-content">
        <table class="table-condensed no-border">
            <tr><td>{$aLang.action.admin.users_ip_reg}:</td><td class="ip-split-reg"> </td></tr>
            <tr><td>{$aLang.action.admin.users_ip_last}:</td><td class="ip-split-last"> </td></tr>
        </table>
    </div>
</div>

    <form action="" method="post" id="user-do-command">
        <input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}"/>
        <input type="hidden" name="adm_user_cmd" value=""/>
        <input type="hidden" name="users_list" value=""/>
        <input type="hidden" name="return_url" value="{$PATH_WEB_CURRENT|escape:'html'}"/>
    </form>

<script>
    var admin = admin || { };
    admin.user = admin.user || { };
    admin.user.setIpInfo = function(ip1, ip2) {
        $('#user-win-iplist').find('.ip-split-reg').text(ip1);
        $('#user-win-iplist').find('.ip-split-last').text(ip2);
    };

    admin.selectAllUsers = function (element) {
        if ($(element).prop('checked')) {
            $('tr.selectable td.checkbox input[type=checkbox]').prop('checked', true);
            $('tr.selectable').addClass('info');
        } else {
            $('tr.selectable td.checkbox input[type=checkbox]').prop('checked', false);
            $('tr.selectable').removeClass('info');
        }
        admin.user.select();
    }

    admin.user.select = function (list) {
        //console.log(list);
        if (admin.isEmpty(list)) list = [];
        else if (typeof list == 'string') list = [list];

        $('tr.selectable td.check-row input[type=checkbox]:checked').each(function () {
            var id = $(this).prop('id');
            if (id.indexOf('login_') === 0) {
                list.push(id.substr(6, 255));
            }
            $(this).parents('tr.selectable').addClass('info');
        });

        var view = '';
        $.each(list, function (index, item) {
            if (view) view += ', ';
            view += '<span class="popup-user">' + item + '</span>';
        });
        $('form .users_list').val(list.join(', '));
        $('form .users_list_view').html(view);
    }

    admin.user.unsetAdmin = function(login) {
        var form = $('#user-do-command');
        if (form.length) {
            form.find('[name=adm_user_cmd]').val('adm_user_unsetadmin');
            form.find('[name=users_list]').val(login);
            form.submit();
        }
    }
</script>

{/block}
