{extends file='actions/ActionAdmin/users/banlist.tpl'}

{block name="content-body-table"}

<table class="table table-condensed ban-ips-list">
    <thead>
    <tr>
        <th>&nbsp;</th>
        <th>IP</th>
        <th>{$aLang.action.admin.users_banned}</th>
        <th>{$aLang.action.admin.ban_upto}</th>
        <th>{$aLang.action.admin.ban_comment}</th>
        <th>&nbsp;</th>
    </tr>
    </thead>

    <tbody>
    {if $aIpsList}
    {foreach $aIpsList as $aIp}
    <tr>
        <td class="number">{$aIp.id}</td>
        <td class="center">{$aIp.ip1} - {$aIp.ip2}</td>
        <td class="center">{$aIp.bandate}</td>
        <td class="center">{if $aIp.banunlim}unlim{else}{$aIp.banline}{/if}</td>
        <td class="center">{$aIp.bancomment}</td>
        <td class="center">
            <a href="#" onclick="admin.user.unsetBan('{$aIp.id}', 'ip'); return false;"
               class="btn btn-mini tip-top" title="{$aLang.action.admin.exclude}"><i class="icon icon-thumbs-up"></i></a>
        </td>
    </tr>
    {/foreach}
    {else}
        <tr>
            <td colspan="6">{$aLang.action.admin.no_data}</td>
        </tr>
    {/if}
    </tbody>
</table>

{/block}