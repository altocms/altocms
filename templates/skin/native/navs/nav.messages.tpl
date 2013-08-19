{**
 * Навигация на странице личных сообщений
 *}
 
<div class="page-header">
	{$aLang.talk_menu_inbox}
	
	<a href="{router page='talk'}add/" class="button button-small">{$aLang.talk_menu_inbox_create}</a>
	
	<a class="accordion-header link-dotted" onclick="jQuery('#block_talk_search_content').toggle(); return false;">{$aLang.talk_filter_title}</a>
	
	<div class="accordion">
		<form action="{router page='talk'}" method="GET" name="talk_filter_form" id="block_talk_search_content" class="accordion-content" {if $_aRequest.submit_talk_filter}style="display:block;"{/if}>
			<div class="trigger"><i></i></div>
			
			<p><label for="talk_filter_sender">{$aLang.talk_filter_label_sender}:</label>
			<input type="text" id="talk_filter_sender" name="sender" value="{$_aRequest.sender}" class="input-text input-width-full" />
			<small class="note">{$aLang.talk_filter_notice_sender}</small></p>

			<p><label for="talk_filter_keyword">{$aLang.talk_filter_label_keyword}:</label>
			<input type="text" id="talk_filter_keyword" name="keyword" value="{$_aRequest.keyword}" class="input-text input-width-full" />
			<small class="note">{$aLang.talk_filter_notice_keyword}</small></p>

			<p><label for="talk_filter_keyword_text">{$aLang.talk_filter_label_keyword_text}:</label>
				<input type="text" id="talk_filter_keyword_text" name="keyword_text" value="{$_aRequest.keyword_text}" class="input-text input-width-full" />
				<small class="note">{$aLang.talk_filter_notice_keyword}</small></p>

			<p><label for="talk_filter_start">{$aLang.talk_filter_label_date}:</label>
			<input type="text" id="talk_filter_start" name="start" value="{$_aRequest.start}" style="width: 43%" class="input-text date-picker" readonly="readonly" /> &mdash;
			<input type="text" id="talk_filter_end" name="end" value="{$_aRequest.end}" style="width: 43%" class="input-text date-picker" readonly="readonly" /></p>

			<p><label for="talk_filter_favourite"><input type="checkbox" {if $_aRequest.favourite}checked {/if} class="input-checkbox" name="favourite" value="1" id="talk_filter_favourite" />
			{$aLang.talk_filter_label_favourite}</label></p>

			<input type="submit" name="submit_talk_filter" value="{$aLang.talk_filter_submit}" class="button button-primary" />
			<input type="submit" name="" value="{$aLang.talk_filter_submit_clear}" class="button" onclick="return ls.talk.clearFilter();" />
		</form>
	</div> 
</div>

<ul class="nav nav-folding nomargin">
	
	<li class="talk-checkbox"><input type="checkbox" name="" class="input-checkbox" onclick="ls.tools.checkAll('form_talks_checkbox', this, true);"></li>

	<li {if $sMenuSubItemSelect=='inbox'}class="active"{/if}><a href="{router page='talk'}">{$aLang.talk_menu_inbox}</a></li>
	{if $iUserCurrentCountTalkNew}
		<li {if $sMenuSubItemSelect=='new'}class="active"{/if}><a href="{router page='talk'}inbox/new/">{$aLang.talk_menu_inbox_new} <span class="block-count">{$iUserCurrentCountTalkNew}</span></a></li>
	{/if}
	<li {if $sMenuSubItemSelect=='favourites'}class="active"{/if}><a href="{router page='talk'}favourites/">{$aLang.talk_menu_inbox_favourites}{if $iCountTalkFavourite} <span class="block-count">{$iCountTalkFavourite}</span>{/if}</a></li>
	<li {if $sMenuSubItemSelect=='blacklist'}class="active"{/if}><a href="{router page='talk'}blacklist/">{$aLang.talk_menu_inbox_blacklist}</a></li>

	{hook run='menu_talk_talk_item'}	
</ul>

{hook run='menu_talk'}