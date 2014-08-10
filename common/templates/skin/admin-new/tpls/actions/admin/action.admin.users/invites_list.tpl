{extends file='_index.tpl'}

{block name="content-bar"}
<div class="col-md-12">
    <a href="{router page='admin'}users-invites/new/" class="btn btn-primary disabled pull-right"><i class="glyphicon glyphicon-plus"></i></a>
  <ul class="nav nav-pills atlass">
    <li class="{if $sMode=='all'}active{/if}">
        <a href="{router page='admin'}users-invites/all/">
            {$aLang.action.admin.invites_all} <span class="label label-primary">{$aCounts.all}</span>
        </a>
    </li>
    <li class="{if $sMode=='used'}active{/if}">
        <a href="{router page='admin'}users-invites/used/">
            {$aLang.action.admin.invites_used} <span class="label label-primary">{$aCounts.used}</span>
        </a>
	</li>
    <li class="{if $sMode=='unused'}active{/if}">
        <a href="{router page='admin'}users-invites/unused/">
            {$aLang.action.admin.invites_unused} <span class="label label-primary">{$aCounts.unused}</span>
        </a>
	</li>
  </ul>
</div>
{/block}

{block name="content-body"}
<div class="col-md-12">

    <div class="panel panel-default">
        <div class="panel-body no-padding">
<form method="post" action="" id="admin_form_invite_list">
    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}" />

{if $aInvites}

<div class="table table-striped-responsive"><table class="table table-striped table-condensed invites-list">
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
            <a href="{router page='admin'}users/profile/{$aInvite.from_login}/" class="link">{$aInvite.from_login}</a>
        </td>
        <td class="center;">{$aInvite.invite_date_add}</td>
        <td>
            {if $aInvite.to_login}
            <a href="{router page='admin'}users/profile/{$aInvite.to_login}/" class="link">{$aInvite.to_login}</a>
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
</table></div>
<div style="display:none;">
        <input type="hidden" name="invite_sort" id="invite_sort" />
        <input type="hidden" name="invite_order" id="invite_order" />
</div>

{else}
    {$oLang->user_empty}
{/if}
</form>
</div>
    </div>
</div>

    {include file='inc.paging.tpl'}

{/block}