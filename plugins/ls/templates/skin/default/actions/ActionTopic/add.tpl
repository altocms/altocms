{if $sEvent=='add'}
	{include file='header.tpl' menu_content='create'}
{else}
	{include file='header.tpl'}
	<h2 class="page-header">{$aLang.topic_topic_edit}</h2>
{/if}

{include file='editor.tpl'}

{hook run='add_topic_topic_begin'}


<form action="" method="POST" enctype="multipart/form-data" id="form-topic-add" class="wrapper-content">
	{hook run='form_add_topic_topic_begin'}

	
	<input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}" />

	
	<p><label for="blog_id">{$aLang.topic_create_blog}</label>
	<select name="blog_id" id="blog_id" onChange="ls.blog.loadInfo(jQuery(this).val());" class="input-block-level">
		<option value="0">{$aLang.topic_create_blog_personal}</option>
		{foreach from=$aBlogsAllow item=oBlog}
			<option value="{$oBlog->getId()}" {if $_aRequest.blog_id==$oBlog->getId()}selected{/if}>{$oBlog->getTitle()|escape:'html'}</option>
		{/foreach}
	</select>
	<span class="help-block"><small>{$aLang.topic_create_blog_notice}</small></span></p>

	
	<script type="text/javascript">
		jQuery(document).ready(function($){
			ls.blog.loadInfo($('#blog_id').val());
		});
    </script>
	
	
	<p><label for="topic_title">{$aLang.topic_create_title}:</label>
	<input type="text" id="topic_title" name="topic_title" value="{$_aRequest.topic_title}" class="input-block-level" />
	<span class="help-block"><small>{$aLang.topic_create_title_notice}</small></span></p>

	
	<label for="topic_text">{$aLang.topic_create_text}:</label>
	<textarea name="topic_text" id="topic_text" rows="20" class="mce-editor markitup-editor input-block-level">{$_aRequest.topic_text}</textarea>

	{if !$oConfig->GetValue('view.tinymce')}
		{include file='tags_help.tpl' sTagsTargetId="topic_text"}
		<br />
		<br />
	{/if}
	
	<p><label for="topic_tags">{$aLang.topic_create_tags}:</label>
	<input type="text" id="topic_tags" name="topic_tags" value="{$_aRequest.topic_tags}" class="input-block-level autocomplete-tags-sep" />
	<span class="help-block"><small>{$aLang.topic_create_tags_notice}</small></span></p>

	
	<p><label class="checkbox"><input type="checkbox" id="topic_forbid_comment" name="topic_forbid_comment" class="input-checkbox" value="1" {if $_aRequest.topic_forbid_comment==1}checked{/if} />
	{$aLang.topic_create_forbid_comment}</label>
	<span class="help-block"><small>{$aLang.topic_create_forbid_comment_notice}</small></span></p>

	
	{if $oUserCurrent->isAdministrator()}
		<p><label class="checkbox"><input type="checkbox" id="topic_publish_index" name="topic_publish_index" class="input-checkbox" value="1" {if $_aRequest.topic_publish_index==1}checked{/if} />
		{$aLang.topic_create_publish_index}</label>
		<span class="help-block"><small>{$aLang.topic_create_publish_index_notice}</small></span></p>
	{/if}

	<input type="hidden" name="topic_type" value="topic" />
	
	{hook run='form_add_topic_topic_end'}
	<br />
	
	<button type="submit" name="submit_topic_publish" id="submit_topic_publish" class="btn btn-primary pull-right">{$aLang.topic_create_submit_publish}</button>
	<button type="submit" name="submit_preview" onclick="ls.topic.preview('form-topic-add','text_preview'); return false;" class="btn">{$aLang.topic_create_submit_preview}</button>
	<button type="submit" name="submit_topic_save" id="submit_topic_save" class="btn">{$aLang.topic_create_submit_save}</button>
</form>

<div class="topic-preview" style="display: none;" id="text_preview"></div>

{hook run='add_topic_topic_end'}


{include file='footer.tpl'}
