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
            <div class="btn-group">
                <a class="btn btn-warning btn-mini dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    {if $oUserCurrent and ($oUserCurrent->getId()==$oTopic->getUserId() or $oUserCurrent->isAdministrator() or $oBlog->getUserIsAdministrator() or $oBlog->getUserIsModerator() or $oBlog->getOwnerId()==$oUserCurrent->getId())}
                        <li><a href="{$oTopic->getUrlEdit()}" title="{$aLang.topic_edit}" class="actions-edit"><i class="icon-edit"></i>{$aLang.topic_edit}</a></li>
                    {/if}
                    {if $oUserCurrent and ($oUserCurrent->isAdministrator() or $oBlog->getUserIsAdministrator() or $oBlog->getOwnerId()==$oUserCurrent->getId())}
                        <li><a href="{router page='content'}delete/{$oTopic->getId()}/?security_ls_key={$LIVESTREET_SECURITY_KEY}" title="{$aLang.topic_delete}" onclick="return confirm('{$aLang.topic_delete_confirm}');" class="actions-delete"><i class="icon-remove"></i>{$aLang.topic_delete}</a></li>
                    {/if}
                </ul>
            </div>
            {/if}
			
			{if $bTopicList}
				<a href="{$oTopic->getUrl()}">{$oTopic->getTitle()|escape:'html'}</a>
			{else}
				{$oTopic->getTitle()|escape:'html'}
			{/if}
		</h1>
		
		<div class="topic-info">
			<a href="{$oBlog->getUrlFull()}" class="topic-blog"><i class="icon-briefcase"></i>{$oBlog->getTitle()|escape:'html'}</a>
		</div>

        {if $oTopic->getType() == 'link'}
            <div class="topic-url">
                <a href="{router page='link'}go/{$oTopic->getId()}/" title="{$aLang.topic_link_count_jump}: {$oTopic->getLinkCountJump()}">{$oTopic->getLinkUrl()}</a>
            </div>
        {/if}
	</header>