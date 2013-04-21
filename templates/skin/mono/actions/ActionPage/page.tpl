{extends file="_index.tpl"}

{block name="vars"}
    {$noSidebar=true}
{/block}

{block name="content"}

<div class="topic">
	<div class="topic-content text">
		{if $oConfig->GetValue('view.tinymce')}
			{$oPage->getText()}
		{else}
			{if $oPage->getAutoBr()}
				{$oPage->getText()|nl2br}
			{else}
				{$oPage->getText()}
			{/if}
		{/if}
	</div>
</div>

{/block}