{**
 * Блок с кнопкой добавления блога
 *
 * @styles css/blocks.css
 *}

{extends file='blocks/block.aside.base.tpl'}

{block name='block_type'}blog-add{/block}

{block name='block_options'}
	{if ! $oUserCurrent}
		{$bBlockNotShow = true}
	{/if}
{/block}

{block name='block_content'}
	<p>{$aLang.topic_add_title}</p>

	<a href="{router page='content'}topic/add/" class="button button-primary button-large" data-type="modal-toggle" data-option-target="modal-write">{$aLang.topic_add}</a>
{/block}