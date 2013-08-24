{**
 * Обычный топик
 *
 * @styles css/topic.css
 *}

{extends file='topics/topic_base.tpl'}


{block name='topic_content_text'}
	{if $bTopicList}
		{$oTopic->getTextShort()}
		
		<br/><br/>
		
		{if $oTopic->getTextShort() != $oTopic->getText()}
			<a href="{$oTopic->getUrl()}#cut" title="{$aLang.topic_read_more}" class="button button-inline button-readmore">
				{$oTopic->getCutText()|default:$aLang.topic_read_more} &rarr;
			</a>		
		{/if}
		
		<div onclick="return ls.favourite.toggle({$oTopic->getId()},this,'topic');" 
			 class="favourite {if $oUserCurrent && $oTopic->getIsFavourite()}active{/if}" 
			 title="{if $oTopic->getIsFavourite()}{$aLang.talk_favourite_del}{else}{$aLang.talk_favourite_add}{/if}">
		</div>
		<span class="favourite-count" id="fav_count_topic_{$oTopic->getId()}" {if ! $oTopic->getCountFavourite()}style="display: none"{/if}>{$oTopic->getCountFavourite()}</span>
	{else}
		{$oTopic->getText()}
	{/if}
{/block}