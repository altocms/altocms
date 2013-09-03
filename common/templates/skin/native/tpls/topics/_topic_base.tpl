{**
 * Базовый шаблон топика
 *
 * @styles assets/css/topic.css
 * @scripts <framework>/js/livestreet/topic.js
 *}

{block name='topic_options'}{/block}

{$oBlog = $oTopic->getBlog()}
{$oUser = $oTopic->getUser()}
{$oVote = $oTopic->getVote()}
{$oFavourite = $oTopic->getFavourite()}
{$oType = $oTopic->getContentType()}

<article class="topic topic-type-{$oTopic->getType()} js-topic {if ! $bTopicList}topic-single{/if} {block name='topic_class'}{/block}" id="{block name='topic_id'}{/block}" {block name='topic_attributes'}{/block}>

	<div class="topic-author">
		<a href="{$oUser->getProfileUrl()}"><img src="{$oUser->getAvatarUrl(48)}" alt="avatar" class="avatar" /></a>
		<a rel="author" href="{$oUser->getProfileUrl()}">{$oUser->getLogin()}</a>
		<br />
		<time datetime="{date_format date=$oTopic->getDateAdd() format='c'}" title="{date_format date=$oTopic->getDateAdd() format='j F Y, H:i'}">
			{date_format date=$oTopic->getDateAdd() format="j F Y, H:i"}
		</time>
	</div>

	{**
	 * Хидер
	 *}
	{block name='topic_header'}
		<header class="topic-header">
			<div class="blog-title">
				<a href="{$oBlog->getUrlFull()}" class="topic-blog">{$oBlog->getTitle()|escape:'html'}</a>
			</div>

			{* Заголовок *}
			<h1 class="topic-title word-wrap">
				{if $oTopic->getPublish() == 0}   
					<i class="icon-file" title="{$aLang.topic_unpublish}"></i>
				{/if}

				{block name='topic_icon'}{/block}

				{if $bTopicList}
					<a href="{$oTopic->getUrl()}">{$oTopic->getTitle()|escape:'html'}</a>
				{else}
					{$oTopic->getTitle()|escape:'html'}
				{/if}
			</h1>

			{* Информация *}
			<div class="topic-info">
				{* Управление *}
				{if $oTopic->getIsAllowAction()}
					<ul class="actions">
						{if $oTopic->getIsAllowEdit()}
							<li><a href="{$oTopic->getUrlEdit()}" title="{$aLang.topic_edit}" class="actions-edit">{$aLang.topic_edit}</a></li>
						{/if}

						{if $oTopic->getIsAllowDelete()}
							<li>
								<a href="{router page='topic'}delete/{$oTopic->getId()}/?security_ls_key={$ALTO_SECURITY_KEY}"
								   title="{$aLang.topic_delete}" 
								   onclick="return confirm('{$aLang.topic_delete_confirm}');" 
								   class="actions-delete">{$aLang.topic_delete}</a>
							</li>
						{/if}
					</ul>
				{/if}
			</div> 
		</header>
	{/block}


	{if $oType->isAllow('question') && $oTopic->getQuestionAnswers()}
		<div class="poll js-poll" data-poll-id="{$oTopic->getId()}">
			{if ! $oTopic->getUserQuestionIsVote()}
				<ul class="poll-list js-poll-list">
					{foreach $oTopic->getQuestionAnswers() as $iItemId => $aAnswer}
						<li class="poll-item js-poll-item"><label><input type="radio" name="poll-{$oTopic->getId()}" value="{$iItemId}" class="js-poll-item-option" /> {$aAnswer.text|escape}</label></li>
					{/foreach}
				</ul>

				<button type="submit" class="button button-primary js-poll-button-vote">{$aLang.topic_question_vote}</button>
				<button type="submit" class="button js-poll-button-abstain">{$aLang.topic_question_abstain}</button>
			{else}
				{include file='topics/poll_result.tpl'}
			{/if}
		</div>
	{/if}

	{if $oType->isAllow('photoset') && $iPhotosCount}
		{$oMainPhoto = $oTopic->getPhotosetMainPhoto()}

		{if $oMainPhoto}
			<div class="topic-preview-image">
				<div class="topic-preview-image-inner js-topic-preview-loader loading" onclick="window.location='{$oTopic->getUrl()}'">
					<div class="topic-preview-image-count" id="photoset-photo-count-{$oTopic->getId()}"><i class="icon-camera icon-white"></i> {$oTopic->getPhotosetCount()}</div>

					{if $oMainPhoto->getDescription()}
						<div class="topic-preview-image-desc" id="photoset-photo-desc-{$oTopic->getId()}">{$oMainPhoto->getDescription()}</div>
					{/if}

					<img class="js-topic-preview-image" src="{$oMainPhoto->getWebPath(1000)}" alt="Topic preview" />
				</div>
			</div>
		{/if}
	{/if}

	{block name='topic_header_after'}{/block}

	{**
	 * Текст
	 *}
	{block name='topic_content'}
		<div class="topic-content text">
			{hook run='topic_content_begin' topic=$oTopic bTopicList=$bTopicList}

			{block name='topic_content_text'}{$oTopic->getText()}{/block}

			{hook run='topic_content_end' topic=$oTopic bTopicList=$bTopicList}
		</div>
	{/block}

	{if $oType->isAllow('photoset') && !$bTopicList && $iPhotosCount}
		<div class="photoset photoset-type-default">
			<h2 class="photoset-title">{$oTopic->getPhotosetCount()} {$oTopic->getPhotosetCount()|declension:$aLang.topic_photoset_count_images}</h2>

			<ul class="photoset-images" id="topic-photo-images">
				{$aPhotos = $oTopic->getPhotosetPhotos(0, Config::Get('module.topic.photoset.per_page'))}

				{if $aPhotos}
					{foreach $aPhotos as $oPhoto}
						<li>
							<a class="js-photoset-type-default-image"
							   href="{$oPhoto->getWebPath(1000)}"
							   rel="[photoset]"  title="{$oPhoto->getDescription()}">

							   <img src="{$oPhoto->getWebPath('50crop')}" alt="{$oPhoto->getDescription()}" /></a>
						</li>

						{$iLastPhotoId = $oPhoto->getId()}
					{/foreach}
				{/if}

				<script type="text/javascript">
					ls.photoset.idLast='{$iLastPhotoId}';
				</script>
			</ul>

			{if count($aPhotos) < $oTopic->getPhotosetCount()}
				<a href="javascript:ls.photoset.getMore({$oTopic->getId()})" id="topic-photo-more" class="get-more">{$aLang.topic_photoset_show_more} &darr;</a>
			{/if}
		</div>
	{/if}

	{if $oType->isAllow('link') && $oTopic->getLinkUrl()}
		<div class="topic-url">
			<a href="{router page='link'}go/{$oTopic->getId()}/" title="{$aLang.topic_link_count_jump}: {$oTopic->getLinkCountJump()}">{$oTopic->getLinkUrl()}</a>
		</div>
	{/if}
	
	{block name='topic_content_after'}{/block}


	{**
	 * Футер
	 *}
	{block name='topic_footer'}

		<footer class="topic-footer">
			{block name='topic_footer_begin'}{/block}

			{if !$bTopicList}
			{* Теги *}
			<ul class="topic-tags js-favourite-insert-after-form js-favourite-tags-topic-{$oTopic->getId()}">
				<li>{$aLang.topic_tags}:</li>

				{strip}
					{foreach $oTopic->getTagsArray() as $sTag}
						<li><a rel="tag" href="{router page='tag'}{$sTag|escape:'url'}/">{$sTag|escape}</a>{if !$sTag@last}, {/if}</li>
					{foreachelse}
						<li>{$aLang.topic_tags_empty}</li>
					{/foreach}

					{if $oUserCurrent}
						{if $oFavourite}
							{foreach $oFavourite->getTagsArray() as $sTag}
								<li class="topic-tags-user js-favourite-tag-user">,
                                    <a rel="tag" href="{$oUserCurrent->getProfileUrl()}favourites/topics/tag/{$sTag|escape:'url'}/">{$sTag|escape}</a>
                                </li>
							{/foreach}
						{/if}

						<li class="topic-tags-edit js-favourite-tag-edit" {if !$oFavourite}style="display:none;"{/if}>
							<a href="#" onclick="return ls.favourite.showEditTags({$oTopic->getId()},'topic',this);" class="link-dotted">{$aLang.favourite_form_tags_button_show}</a>
						</li>
					{/if}
				{/strip}
			</ul>

			{* Информация *}
			<ul class="topic-info">
				{* Голосование *}
				{if $oVote || ($oUserCurrent && $oTopic->getUserId() == $oUserCurrent->getId()) || strtotime($oTopic->getDateAdd()) < $smarty.now-Config::Get('acl.vote.topic.limit_time')}
					{$bShowVoteInfo = true}
				{/if}

				<li data-type="tooltip-toggle"
					data-param-i-topic-id="{$oTopic->getId()}"
					data-option-url="{router page='ajax'}vote/get/info/"
					data-vote-type="topic"
					data-vote-id="{$oTopic->getId()}"
					class="vote vote-topic js-vote 
							{if $oVote || ($oUserCurrent && $oTopic->getUserId() == $oUserCurrent->getId()) || strtotime($oTopic->getDateAdd()) < $smarty.now-Config::Get('acl.vote.topic.limit_time')}
								{if $oTopic->getRating() > 0}
									vote-count-positive
								{elseif $oTopic->getRating() < 0}
									vote-count-negative
								{/if}
							{/if}

							{if $oVote} 
								voted
								{if $oVote->getDirection() > 0}
									voted-up
								{elseif $oVote->getDirection() < 0}
									voted-down
								{/if}
							{/if}

							{if $bShowVoteInfo}js-tooltip-vote-topic{/if}">
					<div class="vote-item vote-down js-vote-down"><i></i></div>
					<div class="vote-item vote-count js-vote-rating">
						{if $bShowVoteInfo}
							{if $oTopic->getRating() > 0}+{/if}{$oTopic->getRating()}
						{else} 
							<a href="#" class="js-vote-abstain"></a> 
						{/if}
					</div>
					<div class="vote-item vote-up js-vote-up"><i></i></div>

				</li>

				{* Избранное *}
				<li class="topic-info-favourite">
					<div onclick="return ls.favourite.toggle({$oTopic->getId()},this,'topic');" 
						 class="favourite {if $oUserCurrent && $oTopic->getIsFavourite()}active{/if}" 
						 title="{if $oTopic->getIsFavourite()}{$aLang.talk_favourite_del}{else}{$aLang.talk_favourite_add}{/if}"></div>
					<span class="favourite-count" id="fav_count_topic_{$oTopic->getId()}" {if ! $oTopic->getCountFavourite()}style="display: none"{/if}>{$oTopic->getCountFavourite()}</span>
				</li>

				{* Поделиться *}
				<!-- <li class="topic-info-share"><a href="#" class="icon-share js-popover-default" title="{$aLang.topic_share}" data-type="popover-toggle" data-option-target="topic_share_{$oTopic->getId()}"></a></li> -->
				
				{* Ссылка на комментарии *}
				{if $bTopicList}
					<li class="topic-info-comments">
						<a href="{$oTopic->getUrl()}#comments" title="{$aLang.topic_comment_read}">{$oTopic->getCountComment()} {$oTopic->getCountComment()|declension:$aLang.comment_declension:'russian'}</a>
						{if $oTopic->getCountCommentNew()}<span>+{$oTopic->getCountCommentNew()}</span>{/if}
					</li>
				{/if}

				{block name='topic_footer_info_end'}{/block}
				{hook run='topic_show_info' topic=$oTopic}
			</ul>

			{* Всплывающий блок появляющийся при нажатии на кнопку Поделиться *}
					{hookb run="topic_share" topic=$oTopic bTopicList=$bTopicList}
						<div class="yashare-auto-init" data-yashareTitle="{$oTopic->getTitle()|escape:'html'}" data-yashareLink="{$oTopic->getUrl()}" data-yashareL10n="ru" data-yashareType="button" data-yashareQuickServices="yaru,vkontakte,facebook,twitter,odnoklassniki,moimir,lj,gplus"></div>
					{/hookb}
			{/if} <!-- /if bTopicList не показываем в списке теги и пр.-->

			{if ! $bTopicList}
				{hook run='topic_show_end' topic=$oTopic}
			{/if}

			{block name='topic_footer_end'}{/block}
		</footer>
	{/block}

	{block name='topic_footer_after'}{/block}
</article>

{block name='topic_topic_after'}{/block}