{extends file='actions/ActionAdmin/users/banlist.tpl'}

{block name="content-body-table"}

<table class="table table-condensed ban-ips-list">
    <thead>
    <tr>
        <th>ID</th>
        <th>{$oLang->user}</th>
        <th>{$aLang.action.admin.users_ip_reg}</th>
        <th>Last IP</th>
        <th>{$aLang.action.admin.ban_upto}</th>
        <th>{$aLang.action.admin.ban_comment}</th>
        <th>&nbsp;</th>
    </tr>
    </thead>

    <tbody>
    {if $aUserList}
        {foreach $aUserList as $oUser}
            {assign var="oSession" value=$oUser->getSession()}
        <tr>
            <td class="number"> {$oUser->getId()} &nbsp;</td>
            <td {if $oUserCurrent->GetId()==$oUser->getId()}style="font-weight:bold;"{/if}>
                <i class="icon-user icon-red"></i>
                <a href="{router page='admin'}users-list/profile/{$oUser->getLogin()}/"
                   class="link">{$oUser->getLogin()}</a></td>
            <td class="center ip-split">
                {$oUser->getIpRegister()}
            </td>
            <td class="center ip-split">
            {if $oSession}{$oSession->getIpLast()}{/if}
            </td>
            <td class="center">{if $oUser->getBanLine()}{$oUser->getBanLine()}{else}unlim{/if}</td>
            <td>{$oUser->getBanComment()}</td>
            <td class="center">
                <a href="#" onclick="admin.user.unsetBan('{$oUser->getId()}', 'user'); return false;"
                   class="btn btn-mini tip-top" title="{$aLang.action.admin.exclude}"><i class="icon-thumbs-up"></i></a>
            </td>
        </tr>
        {/foreach}
        {else}
        <tr>
            <td colspan="7">{$aLang.action.admin.no_data}</td>
        </tr>
        {/if}
    </tbody>
</table>

{/block}