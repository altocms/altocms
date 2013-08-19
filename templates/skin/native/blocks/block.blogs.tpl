{**
 * Блок со списоком блогов
 *
 * @styles css/blocks.css
 *}

{extends file='blocks/block.aside.base.tpl'}

{block name='block_title'}{$aLang.block_blogs}{/block}
{block name='block_type'}blogs{/block}

{block name='block_content'}
	<div id="js-tab-pane-blogs">
		{$sBlogsTop}
	</div>
{/block}