{**
 * Навгиация создания топика
 *}

{if $sMenuItemSelect == 'topic'}
	<ul class="nav nav-pills mb-30">
		{foreach from=$aContentTypes item=oType}
            {if $oType->isAccessible()}
                <li {if $sEvent==$oType->getContentUrl()}class="active"{/if}><a href="{router page='content'}{$oType->getContentUrl()}/add/">{$oType->getContentTitle()|escape:'html'}</a></li>
            {/if}
        {/foreach}
		
		{hook run='menu_create_topic_item'}

		{if $iUserCurrentCountTopicDraft}
			<li class="{if $sMenuSubItemSelect == 'drafts'}active{/if}"><a href="{router page='topic'}drafts/">{$aLang.topic_menu_drafts}<span class="block-count">{$iUserCurrentCountTopicDraft}</span></a></li>
		{/if}
	</ul>
{/if}

{hook run='menu_create' sMenuItemSelect=$sMenuItemSelect sMenuSubItemSelect=$sMenuSubItemSelect}