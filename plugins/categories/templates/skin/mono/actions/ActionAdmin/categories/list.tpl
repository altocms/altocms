{extends file='_index.tpl'}

{block name="content-body"}

<div class="span12">

<div class="btn-group">
	<a href="{router page='admin'}categoriesadd/" class="btn btn-primary tip-top" title="{$aLang.plugin.categories.add}"><i class="icon-plus-sign"></i></a>
</div>
{if count($aCategories)>0}

	<div class="b-wbox">
	<div class="b-wbox-content nopadding">

	<table class="table table-striped table-condensed pages-list" id="sortable">
		<thead>
			<tr>
				<th class="span4">{$aLang.plugin.categories.category_title}</th>
				<th>{$aLang.plugin.categories.category_url}</th>
				<th class="span2">{$aLang.plugin.categories.actions}</th>
			</tr>
		</thead>

		<tbody class="content">
			{foreach from=$aCategories item=oCategory}
				<tr id="{$oCategory->getCategoryId()}" class="cursor-x">
					<td class="center">
					{$oCategory->getCategoryTitle()|escape:'html'}
					</td>
					<td class="center">
						{$oCategory->getCategoryUrl()|escape:'html'}
					</td>
					<td class="center">
						<a href="{router page='admin'}categoriesedit/{$oCategory->getCategoryId()}/" >
						<i class="icon-edit tip-top" title="{$aLang.plugin.categories.edit}"></i></a>

					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
	</div>
	</div>
{/if}

</div>

{/block}