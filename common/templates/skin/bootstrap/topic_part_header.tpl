{assign var="oBlog" value=$oTopic->getBlog()}
{assign var="oUser" value=$oTopic->getUser()}
{assign var="oVote" value=$oTopic->getVote()}


<article class="topic topic-type-{$oTopic->getType()} js-topic">
	<header class="topic-header">

        <div class="published">
            <time datetime="{date_format date=$oTopic->getDateAdd() format='c'}" title="{date_format date=$oTopic->getDateAdd() format='j F Y, H:i'}">
                {date_format date=$oTopic->getDateAdd() format="j F Y, H:i"}
            </time>
        </div>

		<h1 class="topic-title word-wrap">

        {if $oUserCurrent and ($oUserCurrent->getId()==$oTopic->getUserId() or $oUserCurrent->isAdministrator() or $oBlog->getUserIsAdministrator() or $oBlog->getUserIsModerator() or $oBlog->getOwnerId()==$oUserCurrent->getId())}
            <div class="actions">
            {if $oUserCurrent and ($oUserCurrent->getId()==$oTopic->getUserId() or $oUserCurrent->isAdministrator() or $oBlog->getUserIsAdministrator() or $oBlog->getUserIsModerator() or $oBlog->getOwnerId()==$oUserCurrent->getId())}
                <a href="{$oTopic->getUrlEdit()}" title="{$aLang.topic_edit}" class="actions-edit"><i class="icon-edit"></i></a>
            {/if}
            {if $oUserCurrent and ($oUserCurrent->isAdministrator() or $oBlog->getUserIsAdministrator() or $oBlog->getOwnerId()==$oUserCurrent->getId())}
                <a href="{router page='content'}delete/{$oTopic->getId()}/?security_ls_key={$ALTO_SECURITY_KEY}" title="{$aLang.topic_delete}" onclick="return confirm('{$aLang.topic_delete_confirm}');" class="actions-delete"><i class="icon-remove"></i></a>
            {/if}
            </div>
        {/if}

			{if $bTopicList}
				<a href="{$oTopic->getUrl()}">{$oTopic->getTitle()|escape:'html'}</a>
			{else}
				{$oTopic->getTitle()|escape:'html'}
			{/if}
		</h1>
		
		<div class="topic-info">
			<a href="{$oBlog->getUrlFull()}" class="topic-blog"><i class="icon-folder-open"></i>{$oBlog->getTitle()|escape:'html'}</a>
		</div>

        {if $oTopic->getType() == 'link'}
            <div class="topic-url">
                <a href="{router page='link'}go/{$oTopic->getId()}/" title="{$aLang.topic_link_count_jump}: {$oTopic->getLinkCountJump()}">{$oTopic->getLinkUrl()}</a>
            </div>
        {/if}
	</header>