{**
 * Список комментариев к записи на стене
 *}

{foreach $aReplyWall as $oReplyWall}
	{$oReplyUser = $oReplyWall->getUser()}

	<div id="wall-reply-item-{$oReplyWall->getId()}" class="js-wall-reply-item comment wall-comment-reply">
		
		
		<ul class="comment-info">
			<li>
				<a href="{$oReplyUser->getProfileUrl()}"><img src="{$oReplyUser->getAvatarUrl(24)}" alt="avatar" class="avatar" /></a>
			</li>
			<li class="comment-author"><a href="{$oReplyUser->getProfileUrl()}">{$oReplyUser->getLogin()}</a></li>
			<li class="comment-date">
				<time datetime="{date_format date=$oReplyWall->getDateAdd() format='c'}">
					{date_format date=$oReplyWall->getDateAdd() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}
				</time>
			</li>
			
			{if $oReplyWall->isAllowDelete()}
				<li><a href="#" onclick="return ls.wall.remove({$oReplyWall->getId()});" class="link-dotted">{$aLang.wall_action_delete}</a></li>
			{/if}
		</ul>
		
		<div class="comment-content text">
			{$oReplyWall->getText()}
		</div>
	</div>
{/foreach}