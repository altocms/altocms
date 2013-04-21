<div class="b-modal_write" id="modal_write">
	<header class="b-modal-header">
		<h3>{$aLang.block_create}</h3>
		<a href="#" class="b-modal-close jqmClose"></a>
	</header>
	
	{strip}
	<div class="b-modal-content">
        <ul class="write-list">
            {if $iUserCurrentCountTopicDraft}
                <li class="write-item-type-draft">
                    <a href="{router page='content'}saved/" class="write-item-image"></a>
                    <a href="{router page='content'}saved/" class="write-item-link">{$iUserCurrentCountTopicDraft} {$iUserCurrentCountTopicDraft|declension:$aLang.draft_declension:'russian'}</a>
                </li>
            {/if}
            {foreach from=$aContentTypes item=oType}
                <li class="write-item-type-topic">
                    <a href="{router page='content'}{$oType->getContentUrl()}/add/" class="write-item-image"></a>
                    <a href="{router page='content'}{$oType->getContentUrl()}/add/" class="write-item-link">{$oType->getContentTitle()|escape:'html'}</a>
                </li>
            {/foreach}
            <li class="write-item-type-blog">
                <a href="{router page='blog'}add" class="write-item-image"></a>
                <a href="{router page='blog'}add" class="write-item-link">{$aLang.block_create_blog}</a>
            </li>
            <li class="write-item-type-message">
                <a href="{router page='talk'}add" class="write-item-image"></a>
                <a href="{router page='talk'}add" class="write-item-link">{$aLang.block_create_talk}</a>
            </li>
            {hook run='write_item' isPopup=true}
        </ul>
	</div>
	{/strip}
</div>
	