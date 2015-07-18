{extends file='./_users.tpl'}

{block name="content-body-main"}
{$bShowIpColumns=false}
<div class="b-wbox">
    <div class="b-wbox-content nopadding">
        <table class="table table-condensed b-users-list uniform">
            <thead>
            <tr>
                <th>
                    <input type="checkbox" id="id_0" onchange="admin.user.selectAll(this);"/>
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
                {if Config::Get('general.reg.activation')}
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
                    {if Config::Get('general.reg.activation') AND !$oUser->getDateActivate()}
                        {assign var=classIcon value='icon-gray'}
                    {elseif $oUser->isAdministrator()}
                        {assign var=classIcon value='icon-green-inverse'}
                    {elseif $oUser->isBanned()}
                        {assign var=classIcon value='icon-red'}
                    {else}
                        {assign var=classIcon value=''}
                    {/if}
                    {assign var="oSession" value=$oUser->getSession()}
                    {if $oSession}{$sLastIp=$oSession->getIpLast()}{else}{$sLastIp=""}{/if}
                <tr class="selectable">
                    <td class="check-row">
                        {if E::UserId()!=$oUser->getId()}
                            <input type="checkbox" id="id_{$oUser->getId()}"
                                   data-user-id="{$oUser->getId()}"
                                   data-user-login="{$oUser->getLogin()}"
                                   {if E::UserId()!=$oUser->getId()}onchange="admin.user.select()"{/if}/>
                        {else}
                            &nbsp;
                        {/if}
                    </td>
                    <td class="number"> {$oUser->getId()} &nbsp;</td>
                    <td>
                        <a href="{router page='admin'}users-list/profile/{$oUser->getId()}/"
                           {if $oUserCurrent->GetId()==$oUser->getId()}style="font-weight:bold;"{/if}
                           class="link">
                            <i class="icon icon-user {$classIcon}"></i>
                            {$oUser->getDisplayName()}
                        </a>
                        <div class="pull-right">
                        <i class="icon icon-globe {if $oUser->IsOnline()}icon-green{else}icon-gray{/if}" data-toggle="popover" data-popover="#user-win-iplist"
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
                    {if Config::Get('general.reg.activation')}
                        <td class="center">&nbsp;
                            {if $oUser->getDateActivate()}
                                {$oUser->getDateActivate()}
                            {else}
                                <a href="#" onclick="admin.user.activate('{$oUser->GetLogin()}')">
                                    {$aLang.action.admin.users_activate}
                                </a>
                            {/if}
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
                                   class="link tip-top" title="{$aLang.action.admin.exclude}"><i class="icon icon-close"></i></a>&nbsp;
                            {/if}
                        </td>
                    {elseif $sMode=='moderators'}
                        <td class="center">
                            {if $oUser->GetLogin()!='admin'}
                                <a href="#" onclick="admin.user.unsetModerator('{$oUser->GetLogin()}')"
                                   class="link tip-top" title="{$aLang.action.admin.exclude_moderator}"><i class="icon icon-close"></i></a>&nbsp;
                            {/if}
                        </td>
                    {else}
                        <td class="center">
                            {if $oUser->isBanned()}
                                {if $oUser->getBanLine()}{$oUser->getBanLine()}{else}unlim{/if}
                            {/if}
                        </td>
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
        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
        <input type="hidden" name="adm_user_cmd" value=""/>
        <input type="hidden" name="users_list" value=""/>
        <input type="hidden" name="return_url" value="{$PATH_WEB_CURRENT|escape:'html'}"/>
    </form>

{/block}
