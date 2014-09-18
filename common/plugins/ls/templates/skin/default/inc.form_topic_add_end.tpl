<div class="topic-additional-fields">
	{if $oContentType->isAllow('photoset')}

		<div class="page-header">
            <a href="#" class="link-dotted pointer" onclick="$('.topic-photo-upload').slideToggle();return false;">{$aLang.topic_toggle_images}</a>
        </div>
		<div class="topic-photo-upload" {if !count($aPhotos)}style="display:none;"{/if}>
			<h4>{$aLang.topic_photoset_upload_title}</h4>

			<div class="topic-photo-upload-rules">
				{$nMaxSixe=Config::Get('module.topic.photoset.photo_max_size')}
				{$nMaxCount=Config::Get('module.topic.photoset.count_photos_max')}
				{$aLang.topic_photoset_upload_rules|ls_lang:"SIZE%%$nMaxSixe":"COUNT%%$nMaxCount"}
			</div>

			<input type="hidden" name="topic_main_photo" id="topic_main_photo" value="{$_aRequest.topic_main_photo}" />

			<ul id="swfu_images" class="b-topic-multiupload">
				{if count($aPhotos)}
					{foreach from=$aPhotos item=oPhoto}
						{if $_aRequest.topic_main_photo && $_aRequest.topic_main_photo == $oPhoto->getId()}
							{assign var=bIsMainPhoto value=true}
						{/if}

						<li id="photo_{$oPhoto->getId()}" {if $bIsMainPhoto}class="marked-as-preview"{/if}>
							<img src="{$oPhoto->getWebPath('100crop')}" alt="image" />
							<textarea onBlur="ls.photoset.setPreviewDescription({$oPhoto->getId()}, this.value)">{$oPhoto->getDescription()}</textarea><br />
							<a href="javascript:ls.photoset.deletePhoto('{$oPhoto->getId()}')" class="image-delete">{$aLang.topic_photoset_photo_delete}</a>
							<span id="photo_preview_state_{$oPhoto->getId()}" class="photo-preview-state">
								{if $bIsMainPhoto}
									{$aLang.topic_photoset_is_preview}
								{else}
									<a href="javascript:ls.photoset.setPreview('{$oPhoto->getId()}')" class="mark-as-preview">{$aLang.topic_photoset_mark_as_preview}</a>
								{/if}
							</span>
						</li>

						{assign var=bIsMainPhoto value=false}
					{/foreach}
				{/if}
			</ul>

			<a href="javascript:ls.photoset.showForm()" id="photoset-start-upload">{$aLang.topic_photoset_upload_choose}</a>
		</div>
	{/if}

	{if $oContentType->isAllow('question')}
		<div class="page-header">
            <a href="#" class="link-dotted pointer" onclick="$('#topic-poll').slideToggle();return false;">{$aLang.topic_toggle_poll}</a>
        </div>

		<div class="poll-create" id="topic-poll" {if !$_aRequest.question_title}style="display:none;"{/if}>
			<label>{$aLang.topic_question_create_question}:</label>
			<input type="text" value="{$_aRequest.question_title}" name="question_title" class="input-text input-width-300" {if $bEditDisabled}disabled{/if} />
			<label>{$aLang.topic_question_create_answers}:</label>
			<ul class="question-list" id="question_list">
				{if count($_aRequest.answer)>=2}
					{foreach from=$_aRequest.answer item=sAnswer key=i}
						<li>
							<input type="text" value="{$sAnswer}" name="answer[]" class="input-text input-width-300" {if $bEditDisabled}disabled{/if} />
							{if !$bEditDisabled and $i>1} <a href="#" class="icon-synio-remove" onClick="return ls.poll.removeAnswer(this);"></a>{/if}
						</li>
					{/foreach}
				{else}
					<li><input type="text" value="" name="answer[]" class="input-text input-width-300" {if $bEditDisabled}disabled{/if} /></li>
					<li><input type="text" value="" name="answer[]" class="input-text input-width-300" {if $bEditDisabled}disabled{/if} /></li>
				{/if}
			</ul>

			{if !$bEditDisabled}
				<a href="#" onClick="ls.poll.addAnswer(); return false;" class="link-dotted">{$aLang.topic_question_create_answers_add}</a>
			{/if}
		</div>
	{/if}


	{if $oContentType->isAllow('link')}
		<div class="page-header">
            <a href="#" class="link-dotted pointer" onclick="$('#topic-link').slideToggle();return false;">{$aLang.topic_toggle_link}</a>
        </div>

		<div class="poll-create" id="topic-link" {if !$_aRequest.topic_link_url}style="display:none;"{/if}>
			<p><label for="topic_link_url">{$aLang.topic_link_create_url}:</label>
				<input type="text" id="topic_link_url" name="topic_link_url" value="{$_aRequest.topic_link_url}" class="input-text input-width-full" />
				<small class="note">{$aLang.topic_link_create_url_notice}</small></p>
		</div><br/>
	{/if}
</div>
