{extends file='_index.tpl'}

{block name="content"}


<link rel="stylesheet" type="text/css" href="{$aTemplateWebPathPlugin.page|cat:'css/style.css'}" media="all" />


<div>
	<h2 class="page-header">{$aLang.module.page.admin}</h2>
	
	
	{if $aParams.0=='new'}
		<h3 class="page-sub-header">{$aLang.module.page.create}</h3>
		{include file=$aTemplatePathPlugin.page|cat:'actions/ActionPage/add.tpl'}
	{elseif $aParams.0=='edit'}
		<h3 class="page-sub-header">{$aLang.module.page.edit} «{$oPageEdit->getTitle()}»</h3>
		{include file=$aTemplatePathPlugin.page|cat:'actions/ActionPage/add.tpl'}
	{else}
		<a href="{router page='admin'}page/new/" class="page-new">{$aLang.module.page.new}</a><br /><br />
	{/if}


	<table cellspacing="0" class="table">
		<thead>
			<tr>
				<th width="180px">{$aLang.module.page.admin_title}</th>
				<th align="center" >{$aLang.module.page.admin_url}</th>
				<th align="center" width="50px">{$aLang.module.page.admin_active}</th>
				<th align="center" width="70px">{$aLang.module.page.admin_main}</th>
				<th align="center" width="80px">{$aLang.module.page.admin_action}</th>
			</tr>
		</thead>
		
		<tbody>
			{foreach from=$aPages item=oPage name=el2} 	
				<tr>
					<td>
						<img src="{$aTemplateWebPathPlugin.page|cat:'images/'}{if $oPage->getLevel()==0}folder{else}document{/if}.gif" alt="" title="" border="0" style="margin-left: {$oPage->getLevel()*20}px;"/>
						<a href="{router page='page'}{$oPage->getUrlFull()}/">{$oPage->getTitle()}</a>
					</td>
					<td>
						/{$oPage->getUrlFull()}/
					</td>   
					<td align="center">
						{if $oPage->getActive()}
							{$aLang.module.page.admin_active_yes}
						{else}
							{$aLang.module.page.admin_active_no}
						{/if}
					</td>
					<td align="center">
						{if $oPage->getMain()}
							{$aLang.module.page.admin_active_yes}
						{else}
							{$aLang.module.page.admin_active_no}
						{/if}
					</td>
					<td align="center">  
						<a href="{router page='admin'}page/edit/{$oPage->getId()}/"><img src="{$aTemplateWebPathPlugin.page|cat:'images/edit.png'}" alt="{$aLang.module.page.admin_action_edit}" title="{$aLang.module.page.admin_action_edit}" /></a>
						<a href="{router page='admin'}page/delete/{$oPage->getId()}/?security_ls_key={$ALTO_SECURITY_KEY}" onclick="return confirm('«{$oPage->getTitle()}»: {$aLang.module.page.admin_action_delete_confirm}');"><img src="{$aTemplateWebPathPlugin.page|cat:'images/delete.png'}" alt="{$aLang.module.page.admin_action_delete}" title="{$aLang.module.page.admin_action_delete}" /></a>
						<a href="{router page='admin'}page/sort/{$oPage->getId()}/?security_ls_key={$ALTO_SECURITY_KEY}"><img src="{$aTemplateWebPathPlugin.page|cat:'images/up.png'}" alt="{$aLang.module.page.admin_sort_up}" title="{$aLang.module.page.admin_sort_up} ({$oPage->getSort()})" /></a>
						<a href="{router page='admin'}page/sort/{$oPage->getId()}/down/?security_ls_key={$ALTO_SECURITY_KEY}"><img src="{$aTemplateWebPathPlugin.page|cat:'images/down.png'}" alt="{$aLang.module.page.admin_sort_down}" title="{$aLang.module.page.admin_sort_down} ({$oPage->getSort()})" /></a>
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>


{/block}