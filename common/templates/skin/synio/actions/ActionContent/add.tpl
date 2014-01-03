{if $aParams[0]=='add'}
    {include file='header.tpl' menu_content='create'}
{else}
    {include file='header.tpl'}
    <h2 class="page-header">{$aLang.topic_topic_edit}: <b>{$_aRequest.topic_title}</b></h2>
{/if}

{include file='inc.editor.tpl'}

<script type="text/javascript">
    jQuery(function ($) {
        if (window.swfobject.getFlashPlayerVersion()) {
            ls.photoset.initSwfUpload({
                post_params: {
                    'topic_id': {json var=$_aRequest.topic_id}
                }
            });
        }
    });
</script>

<form id="photoset-upload-form" method="POST" enctype="multipart/form-data" onsubmit="return false;"
      class="modal modal-image-upload">
    <header class="modal-header">
        <h3>{$aLang.uploadimg}</h3>
        <a href="#" class="close jqmClose"></a>
    </header>

    <div id="topic-photo-upload-input" class="topic-photo-upload-input modal-content">
        <label for="photoset-upload-file">{$aLang.topic_photoset_choose_image}:</label>
        <input type="file" id="photoset-upload-file" name="Filedata"/><br><br>

        <button type="submit" class="button button-primary"  onclick="ls.photoset.upload();">
            {$aLang.topic_photoset_upload_choose}
        </button>
        <button type="submit" class="button"  onclick="ls.photoset.closeForm();">
            {$aLang.topic_photoset_upload_close}
        </button>

        <input type="hidden" name="is_iframe" value="true"/>
        <input type="hidden" name="topic_id" value="{$_aRequest.topic_id}"/>
    </div>
</form>

{hook run='add_topic_topic_begin'}

<form action="" method="POST" enctype="multipart/form-data" id="form-topic-add" class="wrapper-content">
    {hook run='form_add_topic_topic_begin'}

    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>

    <p><label for="blog_id">{$aLang.topic_create_blog}</label>
        <select name="blog_id" id="blog_id" onChange="ls.blog.loadInfo(jQuery(this).val());" class="input-width-full">
            {if $bPersonalBlog}
                <option value="0">{$aLang.topic_create_blog_personal}</option>
            {/if}
            {foreach from=$aBlogsAllow item=oBlog}
                <option value="{$oBlog->getId()}" {if $_aRequest.blog_id==$oBlog->getId()}selected{/if}>
                    {$oBlog->getTitle()|escape:'html'}
                </option>
            {/foreach}
        </select>
        <small class="note">{$aLang.topic_create_blog_notice}</small>
    </p>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            ls.blog.loadInfo($('#blog_id').val());
        });
    </script>

    <p><label for="topic_title">{$aLang.topic_create_title}:</label>
        <input type="text" id="topic_title" name="topic_title" value="{$_aRequest.topic_title}"
               class="input-text input-width-full"/>
        <small class="note">{$aLang.topic_create_title_notice}</small>
    </p>

    {if $aParams[0] != 'add' AND E::IsAdmin()}
        <p><label for="topic_url">{$aLang.topic_create_url}:</label>
            <span class="b-topic-url-demo">{$aEditTopicUrl.before}</span><span
                    class="b-topic_url_demo-edit">{$aEditTopicUrl.input}</span>
            {if $_aRequest.topic_url_input AND E::IsAdmin()}
                <input type="text" id="topic_url" name="topic_url" value="{$_aRequest.topic_url_input}"
                       class="input-text input-width-300" style="display: none;"/>
            {/if}
            <span class="b-topic_url_demo">{$aEditTopicUrl.after}</span>
            {if $aParams[0] != 'add' AND E::IsAdmin()}
                <button class="button js-tip-help" title="{$aLang.topic_create_url_edit}"
                        onclick="ls.topic.editUrl(this); return false;"><i class="icon-edit"></i></button>
            {/if}
            <button class="button js-tip-help" title="{$aLang.topic_create_url_short}"
                    onclick="ls.topic.shortUrl('{$_aRequest.topic_url_short}'); return false;"><i
                        class="icon-share-alt"></i></button>
            <small class="note"></small>
        </p>
    {/if}

    <label for="topic_text">{$aLang.topic_create_text}:</label>
    <textarea name="topic_text" id="topic_text" class="js-editor-wysiwyg js-editor-markitup input-width-full"
              rows="20">{$_aRequest.topic_text}</textarea>

    {if !Config::Get('view.wysiwyg')}
        {include file='tags_help.tpl' sTagsTargetId="topic_text"}
        <br/>
        <br/>
    {/if}

    {if $oContentType->isAllow('photoset')}
        <fieldset class="topic-fieldset photoset">
            <a href="#" class="topic-fieldset-title link-dotted pointer" onclick="$('.topic-photo-upload').slideToggle();return false;">
                {$aLang.topic_toggle_images}
            </a>
            <div class="topic-fieldset-body topic-photo-upload" {if !count($aPhotos)}style="display:none;"{/if}>
                <h4>{$aLang.topic_photoset_upload_title}</h4>

                <div class="topic-photo-upload-rules">
                    {$nMaxSixe=Config::Get('module.topic.photoset.photo_max_size')}
                    {$nMaxCount=Config::Get('module.topic.photoset.count_photos_max')}
                    {$aLang.topic_photoset_upload_rules|ls_lang:"SIZE%%$nMaxSixe":"COUNT%%$nMaxCount"}
                </div>

                <input type="hidden" name="topic_main_photo" id="topic_main_photo" value="{$_aRequest.topic_main_photo}"/>

                <ul id="swfu_images">
                    {if count($aPhotos)}
                        {foreach from=$aPhotos item=oPhoto}
                            {if $_aRequest.topic_main_photo && $_aRequest.topic_main_photo == $oPhoto->getId()}
                                {assign var=bIsMainPhoto value=true}
                            {/if}
                            <li id="photo_{$oPhoto->getId()}" {if $bIsMainPhoto}class="marked-as-preview"{/if}>
                                <img src="{$oPhoto->getWebPath('100crop')}" alt="image"/>
                                <textarea
                                        onblur="ls.photoset.setPreviewDescription({$oPhoto->getId()}, this.value)">{$oPhoto->getDescription()}</textarea><br/>
                                <a href="#" onclick="ls.photoset.deletePhoto('{$oPhoto->getId()}'); return false;" class="image-delete">
                                    {$aLang.topic_photoset_photo_delete}
                                </a>
                            <span id="photo_preview_state_{$oPhoto->getId()}" class="photo-preview-state">
                            {if $bIsMainPhoto}
                                {$aLang.topic_photoset_is_preview}
                            {else}
                                <a href="#" onclick="ls.photoset.setPreview('{$oPhoto->getId()}'); return false;"
                                   class="mark-as-preview">{$aLang.topic_photoset_mark_as_preview}</a>
                            {/if}
                            </span>
                            </li>
                            {assign var=bIsMainPhoto value=false}
                        {/foreach}
                    {/if}
                </ul>

                <a href="#" onclick="ls.photoset.showForm(); return false;"
                   id="photoset-start-upload">{$aLang.topic_photoset_upload_choose}</a>
            </div>
        </fieldset>
    {/if}

    {if $oContentType->isAllow('question')}
        <fieldset class="topic-fieldset poll">
            <a href="#" class="topic-fieldset-title link-dotted pointer" onclick="$('#topic-poll').slideToggle();return false;">
                {$aLang.topic_toggle_poll}
            </a>
            <div class="topic-fieldset-body poll-create" id="topic-poll" {if !$_aRequest.question_title}style="display:none;"{/if}>
                <label>{$aLang.topic_question_create_question}:</label>
                <input type="text" value="{$_aRequest.question_title}" name="question_title"
                       class="input-text input-width-300" {if $bEditDisabled}disabled{/if} />
                <label>{$aLang.topic_question_create_answers}:</label>
                <ul class="question-list" id="question_list">
                    {if count($_aRequest.answer)>=2}
                        {foreach from=$_aRequest.answer item=sAnswer key=i}
                            <li>
                                <input type="text" value="{$sAnswer}" name="answer[]" class="input-text input-width-300"
                                       {if $bEditDisabled}disabled{/if} />
                                {if !$bEditDisabled and $i>1}
                                    <a href="#" class="icon-synio-remove" onClick="return ls.poll.removeAnswer(this);"></a>
                                {/if}
                            </li>
                        {/foreach}
                    {else}
                        <li><input type="text" value="" name="answer[]" class="input-text input-width-300"
                                   {if $bEditDisabled}disabled{/if} /></li>
                        <li><input type="text" value="" name="answer[]" class="input-text input-width-300"
                                   {if $bEditDisabled}disabled{/if} /></li>
                    {/if}
                </ul>

                {if !$bEditDisabled}
                    <a href="#" onClick="ls.poll.addAnswer(); return false;"
                       class="link-dotted">{$aLang.topic_question_create_answers_add}</a>
                {/if}
            </div>
        </fieldset>
    {/if}

    {if $oContentType->isAllow('link')}
        <fieldset class="topic-fieldset link">
            <a href="#" class="link-dotted pointer" onclick="$('#topic-link').slideToggle();return false;">
                {$aLang.topic_toggle_link}
            </a>
            <div class="topic-fieldset-body link" id="topic-link" {if !$_aRequest.topic_link_url}style="display:none;"{/if}>
                <p><label for="topic_link_url">{$aLang.topic_link_create_url}:</label>
                    <input type="text" id="topic_link_url" name="topic_link_url" value="{$_aRequest.topic_link_url}"
                           class="input-text input-width-full"/>
                    <small class="note">{$aLang.topic_link_create_url_notice}</small>
                </p>
            </div>
        </fieldset>
    {/if}

    <p><label for="topic_tags">{$aLang.topic_create_tags}:</label>
        <input type="text" id="topic_tags" name="topic_tags" value="{$_aRequest.topic_tags}"
               class="input-text input-width-full autocomplete-tags-sep"/>
        <small class="note">{$aLang.topic_create_tags_notice}</small>
    </p>

    {hook run='form_add_content'}

    <p><label><input type="checkbox" id="topic_forbid_comment" name="topic_forbid_comment" class="input-checkbox"
                     value="1" {if $_aRequest.topic_forbid_comment==1}checked{/if} />
            {$aLang.topic_create_forbid_comment}</label>
        <small class="note">{$aLang.topic_create_forbid_comment_notice}</small>
    </p>

    {if $oUserCurrent->isAdministrator()}
        <p><label><input type="checkbox" id="topic_publish_index" name="topic_publish_index" class="input-checkbox"
                         value="1" {if $_aRequest.topic_publish_index==1}checked{/if} />
                {$aLang.topic_create_publish_index}</label>
            <small class="note">{$aLang.topic_create_publish_index_notice}</small>
        </p>
    {/if}

    <input type="hidden" name="topic_type" value="{$oContentType->getContentUrl()}"/>

    {hook run='form_add_topic_topic_end'}

    <button type="submit" name="submit_topic_publish" id="submit_topic_publish"
            class="button button-primary fl-r">
        {if $oTopic AND $oTopic->getPublish()}
            {$aLang.topic_create_submit_publish_update}
        {else}
            {$aLang.topic_create_submit_publish}
        {/if}
    </button>
    <button onclick="ls.topic.preview('form-topic-add','text_preview'); return false;"
            type="submit" name="submit_preview" class="button">
        {$aLang.topic_create_submit_preview}
    </button>
    <button type="submit" name="submit_topic_save" id="submit_topic_save" class="button">
        {if $oTopic AND $oTopic->getPublish()}
            {$aLang.topic_create_submit_publish_draft}
        {else}
            {$aLang.topic_create_submit_save}
        {/if}
    </button>
</form>

<div class="topic-preview" style="display: none;" id="text_preview"></div>

{hook run='add_topic_topic_end'}

{include file='footer.tpl'}
