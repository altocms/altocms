<div class="modal modal-write" id="modal_write">
    <header class="modal-header">
        <button type="button" class="close jqmClose" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>{$aLang.block_create}</h3>
    </header>

	{strip}
	<div class="modal-body">
        <ul class="unstyled inline write-list">
            {if $iUserCurrentCountTopicDraft}
                <li class="write-item-type-draft">
                    <a href="{router page='content'}saved/" class="write-item-image"></a>
                    <a href="{router page='content'}saved/" class="write-item-link">{$iUserCurrentCountTopicDraft} {$iUserCurrentCountTopicDraft|declension:$aLang.draft_declension:'russian'}</a>
                </li>
            {/if}
            {foreach from=$aContentTypes item=oContentType}
                <li class="write-item-type-topic">
                    <a href="{router page='content'}{$oContentType->getContentUrl()}/add/" class="write-item-image"></a>
                    <a href="{router page='content'}{$oContentType->getContentUrl()}/add/" class="write-item-link">{$oContentType->getContentTitle()|escape:'html'}</a>
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
            {hook run='write_item' isPopup=true from="modal"}
        </ul>
	</div>
	{/strip}
</div>
	