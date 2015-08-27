{extends file='_index.tpl'}

{block name="content-body"}

<div class="span12">

    <ul class="nav nav-tabs">
    <li class="nav-tabs-add">
        <a href="{router page='admin'}users-invites/new/"><i class="icon icon-plus"></i></a>
    </li>
    <li {if $sMode=='list' || $sMode==''}class="active"{/if}>
        <a href="{router page='admin'}users-invites/list/">All invites <span class="badge">{$iCount}</span></a>
    </li>
</ul>

{literal}
<script type="text/javascript">
admin.sort = function(sort, order) {
  $('#invite_sort').val(sort);
  $('#invite_order').val(order);
  $('#admin_form_invite_list').submit();
}
</script>
{/literal}

<form method="post" action="" id="admin_form_invite_list">
    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}" />

{if $aInvites}
{include file='inc.paging.tpl'}
<table class="table table-striped table-bordered table-condensed invites-list">
    <tr>
        <th>&nbsp;</th>
        <th>
            {if $sInviteSort=='id'}
            <a href="#" onclick="admin.sort('id', {if $sInviteOrder==1}2{else}1{/if}); return false;"><b> id </b></a>
            <b>{if $sInviteOrder==1}&darr;{else}&uarr;{/if}</b>
            {else}
            <a href="#" onclick="admin.sort('id', 1); return false;"> id </a>
            {/if}
        </th>
        <th>
            {if $sInviteSort=='code'}
            <a href="#" onclick="admin.sort('code', {if $sInviteOrder==1}2{else}1{/if}); return false;"><b> {$aLang.action.admin.invite_code}</b></a>
            <b>{if $sInviteOrder==1}&darr;{else}&uarr;{/if}</b>
            {else}
            <a href="#" onclick="admin.sort('code', 1); return false;"> {$aLang.action.admin.invite_code} </a>
            {/if}
        </th>
        <th>
            {if $sInviteSort=='user_from'}
            <a href="#" onclick="admin.sort('user_from', {if $sInviteOrder==1}2{else}1{/if}); return false;"><b> {$aLang.action.admin.invite_user_from} </b></a>
            <b>{if $sInviteOrder==1}&darr;{else}&uarr;{/if}</b>
            {else}
            <a href="#" onclick="admin.sort('user_from', 1); return false;"> {$aLang.action.admin.invite_user_from} </a>
            {/if}
        </th>
        <th>
            {if $sInviteSort=='date_add'}
            <a href="#" onclick="admin.sort('date_add', {if $sInviteOrder==1}2{else}1{/if}); return false;"><b> {$aLang.action.admin.invite_date_add} </b></a>
            <b>{if $sInviteOrder==1}&darr;{else}&uarr;{/if}</b>
            {else}
            <a href="#" onclick="admin.sort('date_add', 1); return false;"> {$aLang.action.admin.invite_date_add} </a>
            {/if}
        </th>
        <th>
            {if $sInviteSort=='user_to'}
            <a href="#" onclick="admin.sort('user_to', {if $sInviteOrder==1}2{else}1{/if}); return false;"><b> {$aLang.action.admin.invite_user_to} </b></a>
            <b>{if $sInviteOrder==1}&darr;{else}&uarr;{/if}</b>
            {else}
            <a href="#" onclick="admin.sort('user_to', 1); return false;"> {$aLang.action.admin.invite_user_to} </a>
            {/if}
        </th>
        <th>
            {if $sInviteSort=='date_used'}
            <a href="#" onclick="admin.sort('date_used', {if $sInviteOrder==1}2{else}1{/if}); return false;"><b> {$aLang.action.admin.invite_date_used} </b></a>
            <b>{if $sInviteOrder==1}&darr;{else}&uarr;{/if}</b>
            {else}
            <a href="#" onclick="admin.sort('date_used', 1); return false;"> {$aLang.action.admin.invite_date_used} </a>
            {/if}
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
            <a href="{router page='admin'}users-list/profile/{$aInvite.from_login}/" class="link">{$aInvite.from_login}</a>
        </td>
        <td class="center;">{$aInvite.invite_date_add}</td>
        <td>
            {if $aInvite.to_login}
            <a href="{router page='admin'}users-list/profile/{$aInvite.to_login}/" class="link">{$aInvite.to_login}</a>
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
    <input type="hidden" name="action" value="delete" />
    <button class="btn btn-danger" name="btn-delete" style="float: right;">Delete</button>
{include file='inc.paging.tpl'}
{else}
    {$aLang.user_empty}
{/if}
</form>

</div>

{/block}