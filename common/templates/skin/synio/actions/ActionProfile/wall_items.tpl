{foreach $aWallItems as $oWallItem}
	{assign var="oWallUser" value=$oWallItem->getUser()}
	{assign var="aReplyWall" value=$oWallItem->getLastReplyWall()}

	<div id="wall-item-{$oWallItem->getId()}" class="js-wall-item wall-item-wrapper">
		<div class="wall-item">
			<a href="{$oWallUser->getUserWebPath()}">
                <img src="{$oWallUser->getProfileAvatarPath(48)}" alt="avatar" class="avatar" />
            </a>

			<p class="info">
				<a href="{$oWallUser->getUserWebPath()}">{$oWallUser->getLogin()}</a> ·
				<time class="date" datetime="{date_format date=$oWallItem->getDateAdd() format='c'}">
                    {date_format date=$oWallItem->getDateAdd() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}
                </time>
			</p>

			<div class="wall-item-content text">
				{$oWallItem->getText()}
			</div>

			{if $oUserCurrent}
				<ul class="actions wall-item-actions">
					{if $oUserCurrent AND !$aReplyWall}
						<li>
                            <a href="#" class="link-dotted" onclick="return ls.wall.toggleReply({$oWallItem->getId()});">
                                {$aLang.wall_action_reply}
                            </a>
                        </li>
					{/if}
					{if $oWallItem->isAllowDelete()}
						<li>
                            <a href="#" onclick="return ls.wall.remove({$oWallItem->getId()});" class="link-dotted">
                                {$aLang.wall_action_delete}
                            </a>
                        </li>
					{/if}
				</ul>
			{/if}
		</div>

		{if $aReplyWall}
			<div class="wall-item-replies" id="wall-item-replies-{$oWallItem->getId()}">
				{if count($aReplyWall) < $oWallItem->getCountReply()}
					<a href="#" onclick="return ls.wall.loadReplyNext({$oWallItem->getId()});" id="wall-reply-button-next-{$oWallItem->getId()}" class="wall-more-reply">
						<span class="wall-more-inner">{$aLang.wall_load_reply_more}
                            <span id="wall-reply-count-next-{$oWallItem->getId()}">{$oWallItem->getCountReply()}</span>
                            {$oWallItem->getCountReply()|declension:$aLang.comment_declension:'russian'}
                        </span>
					</a>
				{/if}

				{if $aReplyWall}
					<div class="wall-item-container" id="wall-reply-container-{$oWallItem->getId()}">
						{include file='actions/ActionProfile/wall_items_reply.tpl'}
					</div>
				{/if}
			</div>
		{/if}

		{if $oUserCurrent}
			<form class="wall-submit wall-submit-reply" {if !$aReplyWall}style="display: none"{/if}>
				<textarea rows="4" id="wall-reply-text-{$oWallItem->getId()}" class="input-text input-width-full js-wall-reply-text" placeholder="{$aLang.wall_reply_placeholder}" onclick="return ls.wall.expandReply({$oWallItem->getId()});"></textarea>
				<button type="button" onclick="ls.wall.addReply(jQuery('#wall-reply-text-{$oWallItem->getId()}').val(), {$oWallItem->getId()});" class="button button-primary js-button-wall-submit">{$aLang.wall_reply_submit}</button>
			</form>
		{/if}
	</div>
{/foreach}