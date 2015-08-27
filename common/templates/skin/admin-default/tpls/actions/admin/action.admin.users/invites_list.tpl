{extends file='_index.tpl'}

{block name="content-bar"}
    <script>
        $(function(){
            $('.js-admin-user-invite').click(function(){
                admin.user.inviteUserDialog();
                return false;
            });
        });
    </script>

    <div class="btn-group">
        <a href="#" class="btn btn-primary js-admin-user-invite"><i class="icon icon-plus"></i></a>
    </div>
    <div class="btn-group">
        <a class="btn btn-default {if $sMode=='all'}active{/if}" href="{router page='admin'}users-invites/all/">
            {$aLang.action.admin.invites_all} <span class="badge badge-up">{$aCounts.all}</span>
        </a>
        <a class="btn btn-default {if $sMode=='used'}active{/if}" href="{router page='admin'}users-invites/used/">
            {$aLang.action.admin.invites_used} <span class="badge badge-up">{$aCounts.used}</span>
        </a>
        <a class="btn btn-default {if $sMode=='unused'}active{/if}" href="{router page='admin'}users-invites/unused/">
            {$aLang.action.admin.invites_unused} <span class="badge badge-up">{$aCounts.unused}</span>
        </a>
    </div>
{/block}

{block name="layout_body" prepend}
    {include file="modals/modal.user_invite.tpl"}
{/block}

{block name="content-body"}
<div class="span12">

    <div class="b-wbox">
        <div class="b-wbox-content nopadding">
<form method="post" action="" id="admin_form_invite_list">
    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}" />

{if $aInvites}

<table class="table table-striped table-bordered table-condensed invites-list">
    <tr>
        <th>&nbsp;</th>
        <th>
            ID
        </th>
        <th>
            {$aLang.action.admin.invite_code}
        </th>
        <th>
            {$aLang.action.admin.invite_user_from}
        </th>
        <th>
            {$aLang.action.admin.invite_date_add}
        </th>
        <th>
            {$aLang.action.admin.invite_user_to}
        </th>
        <th>
            {$aLang.action.admin.invite_date_used}
        </th>
    </tr>

    {foreach from=$aInvites item=aInvite}
    <tr class="{$className}">
        <td>
            <input type="checkbox" name="invite_{$aInvite.invite_id}" {if $aInvite.invite_date_used}disabled="disabled"{/if}/>
        </td>
        <td> {$aInvite.invite_id} &nbsp;</td>
        <td> {$aInvite.invite_code} &nbsp;</td>
        <td>
            <a href="{router page='admin'}users-list/profile/{$aInvite.user_from_id}/" class="link">{$aInvite.from_login}</a>
        </td>
        <td class="center;">{$aInvite.invite_date_add}</td>
        <td>
            {if $aInvite.to_login}
            <a href="{router page='admin'}users-list/profile/{$aInvite.user_to_id}/" class="link">{$aInvite.to_login}</a>
            {else}
            &nbsp;
            {/if}
        </td>
        <td style="text-align:center;">
            {if $aInvite.invite_date_used}
            {$aInvite.invite_date_used}
            {else}
            &nbsp;
            {/if}
        </td>
    </tr>
    {/foreach}
</table>
<div style="display:none;">
        <input type="hidden" name="invite_sort" id="invite_sort" />
        <input type="hidden" name="invite_order" id="invite_order" />
</div>

{else}
    {$aLang.user_empty}
{/if}
</form>
</div>
    </div>
</div>

    {include file='inc.paging.tpl'}

{/block}