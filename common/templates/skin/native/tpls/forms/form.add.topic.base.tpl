{**
 * Базовая форма создания топика
 *
 * @styles css/topic.css
 *}

{extends file='[layouts]layout.base.tpl'}

{block name='layout_options'}
    {if $aParams[0] == 'add'}
        {$sNav = 'create'}
    {/if}
{/block}

{block name='layout_page_title'}
    {if $aParams[0] == 'add'}
        {$aLang.topic_create}
    {else}
        {$aLang.topic_topic_edit}
    {/if}
{/block}

{block name='layout_content'}
    {block name='add_topic_options'}{/block}

{* Подключение редактора *}
    {include file='forms/editor.init.tpl'}

    {hook run="add_topic_`$sTopicType`_begin"}
    {include file='modals/modal.photoset_add_photo.tpl'}
    {block name='add_topic_header_after'}{/block}
    <form action="" method="POST" enctype="multipart/form-data" id="form-topic-add" class="js-form-validate">
    {hook run="form_add_topic_`$sTopicType`_begin"}
    {block name='add_topic_form_begin'}{/block}


    {* Выбор блога *}
    {if $bPersonalBlog}
        {$aBlogs[] = [
            'value' => 0,
            'text' => $aLang.topic_create_blog_personal
        ]}
    {/if}

    {foreach $aBlogsAllow as $oBlog}
        {$aBlogs[] = [
            'value' => $oBlog->getId(),
            'text' => $oBlog->getTitle()
        ]}
    {/foreach}

    {include file='forms/form.field.select.tpl'
        sFieldName          = 'blog_id'
        sFieldLabel         = $aLang.topic_create_blog
        sFieldNote          = $aLang.topic_create_blog_notice
        sFieldClasses       = 'width-full js-topic-add-title'
        aFieldItems         = $aBlogs
        sFieldSelectedValue = $_aRequest.blog_id
    }


    {* Заголовок топика *}
    {include file='forms/form.field.text.tpl'
        sFieldName  = 'topic_title'
        sFieldRules = 'required="true" rangelength="[2,200]"'
        sFieldNote  = $aLang.topic_create_title_notice
        sFieldLabel = $aLang.topic_create_title
    }

    {if $aParams[0] != 'add' AND E::IsAdmin()}
        <p><label for="topic_url">{$aLang.topic_create_url}:</label>
            <span class="b-topic-url-demo">{$_aRequest.topic_url_before}</span><span
                    class="b-topic_url_demo-edit">{$_aRequest.topic_url}</span>{if $_aRequest.topic_url  AND E::IsAdmin()}
            <input
            type="text" id="topic_url" name="topic_url" value="{$_aRequest.topic_url}"
            class="input-text input-width-300" style="display: none;"/>{/if}<span
                    class="b-topic_url_demo">{$_aRequest.topic_url_after}</span>
            {if $aParams[0] != 'add' AND E::IsAdmin() AND $_aRequest.topic_url}
                <button class="btn js-tip-help" title="{$aLang.topic_create_url_edit}"
                        onclick="ls.topic.editUrl(this); return false;"><i class="icon-edit"></i></button>
            {/if}
            <button class="btn js-tip-help" title="{$aLang.topic_create_url_short}"
                    onclick="ls.topic.shortUrl('{$_aRequest.topic_url_short}'); return false;"><i
                        class="icon-share-alt"></i></button>
            <small class="note"></small>
        </p>
    {/if}


    {block name='add_topic_form_text_before'}{/block}

    {* Текст топика *}
    {include file='forms/form.field.textarea.tpl'
        sFieldName    = 'topic_text'
        sFieldRules   = 'required="true" rangelength="[2,'|cat:Config::Get('module.topic.max_length')|cat:']"'
        sFieldLabel   = $aLang.topic_create_text
        sFieldClasses = 'width-full js-editor'
    }

    {* Если визуальный редактор отключен выводим справку по разметке для обычного редактора *}
    {if !Config::Get('view.wysiwyg')}
        {include file='forms/editor.help.tpl' sTagsTargetId='topic_text'}
    {/if}

    {if $oContentType->isAllow('photoset')}
        <div class="fieldset-toggle-photoset">
            <a class="fieldset-title link-dotted pointer" onclick="$('.fieldset-photoset').slideToggle();return false;">
                {$aLang.topic_toggle_images}
            </a>
            <script type="text/javascript">
                jQuery(function ($) {
                    /*
                    if (window.swfobject.getFlashPlayerVersion()) {
                        ls.photoset.initSwfUpload({
                            post_params: { 'topic_id': {json var=$_aRequest.topic_id} }
                        });
                    }
                    */
                });
            </script>
            <div class="fieldset-body fieldset-photoset" {if !count($aPhotos)}style="xdisplay:none;"{/if}>
                <div class="fieldset-note">
                    {$nMaxSixe=Config::Get('module.topic.photoset.photo_max_size')}
                    {$nMaxCount=Config::Get('module.topic.photoset.count_photos_max')}
                    {$aLang.topic_photoset_upload_rules|ls_lang:"SIZE%%$nMaxSixe":"COUNT%%$nMaxCount"}
                </div>

                <ul class="fieldset-photoset-images" id="swfu_images">
                    {if count($aPhotos)}
                        {foreach $aPhotos as $oPhoto}
                            {if $_aRequest.topic_main_photo AND $_aRequest.topic_main_photo == $oPhoto->getId()}
                                {$bIsMainPhoto = true}
                            {/if}
                            <li id="photo_{$oPhoto->getId()}"
                                class="fieldset-photoset-images-item {if $bIsMainPhoto}marked-as-preview{/if}">
                                <img src="{$oPhoto->getWebPath('100crop')}" alt="image"/>
                                <textarea onBlur="ls.photoset.setPreviewDescription({$oPhoto->getId()}, this.value)"
                                          class="width-full">{$oPhoto->getDescription()}</textarea><br/>
                                <a href="javascript:ls.photoset.deletePhoto({$oPhoto->getId()})" class="link-dotted ">{$aLang.topic_photoset_photo_delete}</a>
                            <span id="photo_preview_state_{$oPhoto->getId()}" class="photo-preview-state">
                            {if $bIsMainPhoto}
                                {$aLang.topic_photoset_is_preview}
                            {else}
                                <a href="javascript:ls.photoset.setPreview({$oPhoto->getId()})"
                                   class="link-dotted mark-as-preview">{$aLang.topic_photoset_mark_as_preview}</a>
                            {/if}
                            </span>
                            </li>
                            {$bIsMainPhoto = false}
                        {/foreach}
                    {/if}
                </ul>

                {include file='forms/form.field.hidden.tpl' sFieldName='topic_main_photo' value=$_aRequest.topic_main_photo}

                <label class="form-input-file btn-primary">
                    <span id="js-photoset-image-upload-flash">{$aLang.topic_photoset_upload_choose}</span>
                    <input type="file" name="Filedata" id="js-photoset-image-upload" data-topic-id="{$_aRequest.topic_id}">
                </label>
            </div>
        </div>
    {/if}

    {if $oContentType->isAllow('question')}
        <div class="fieldset-toggle-question">
            <a class="fieldset-title link-dotted pointer" onclick="$('.js-poll-add').slideToggle();return false;">
                {$aLang.topic_toggle_poll}
            </a>
            <div class="fieldset-body fieldset-poll js-poll-add" {if !$_aRequest.question_title}style="display:none;"{/if}>
                <label>{$aLang.topic_question_create_question}:</label>
                <input type="text" value="{$_aRequest.question_title}" name="question_title"
                       class="input-text input-width-300" {if $bEditDisabled}disabled{/if} />
                <label>{$aLang.topic_question_create_answers}</label>

                <ul class="poll-add-list js-poll-add-list">
                    {if count($_aRequest.answer) >= 2}
                        {foreach $_aRequest.answer as $sAnswer}
                            <li class="poll-add-item js-poll-add-item">
                                <input type="text" value="{$sAnswer}" name="answer[]"
                                       class="poll-add-item-input js-poll-add-item-input"
                                       {if $bEditDisabled}disabled{/if} />

                                {if ! $bEditDisabled and $sAnswer@key > 1}
                                    <i class="icon-remove poll-add-item-remove js-poll-add-item-remove"
                                       title="{$aLang.topic_question_create_answers_delete}"></i>
                                {/if}
                            </li>
                        {/foreach}
                    {else}
                        <li class="poll-add-item js-poll-add-item"><input type="text" name="answer[]"
                                                                          class="poll-add-item-input js-poll-add-item-input"
                                                                          {if $bEditDisabled}disabled{/if} /></li>
                        <li class="poll-add-item js-poll-add-item"><input type="text" name="answer[]"
                                                                          class="poll-add-item-input js-poll-add-item-input"
                                                                          {if $bEditDisabled}disabled{/if} /></li>
                    {/if}
                </ul>

                {if ! $bEditDisabled}
                    <button type="button" class="btn-primary js-poll-add-button"
                            title="[Ctrl + Enter]">{$aLang.topic_question_create_answers_add}</button>
                {/if}
            </div>
        </div>
    {/if}

    {if $oContentType->isAllow('link')}
        <div class="fieldset-toggle-link">
            <a class="fieldset-title link-dotted pointer" onclick="$('#topic-link').slideToggle();return false;">{$aLang.topic_toggle_link}</a>
            <div class="fieldset-body fieldset-link" id="topic-link" {if !$_aRequest.topic_link_url}style="display:none;"{/if}>
                <p><label for="topic_link_url">{$aLang.topic_link_create_url}:</label>
                    <input type="text" id="topic_link_url" name="topic_link_url" value="{$_aRequest.topic_link_url}"
                           class="input-text input-width-full"/>
                    <small class="note">{$aLang.topic_link_create_url_notice}</small>
                </p>
            </div>
        </div>
    {/if}

    {block name='add_topic_form_text_after'}{/block}


    {* Теги *}
    {include file='forms/form.field.text.tpl'
        sFieldName    = 'topic_tags'
        sFieldRules   = 'required="false" rangetags="[1,15]"'
        sFieldNote    = $aLang.topic_create_tags_notice
        sFieldLabel   = $aLang.topic_create_tags
        sFieldClasses = 'width-full autocomplete-tags-sep'}

    {* Запретить комментарии *}
    {include file='forms/form.field.checkbox.tpl'
        sFieldName  = 'topic_forbid_comment'
        sFieldNote  = $aLang.topic_create_forbid_comment_notice
        sFieldLabel = $aLang.topic_create_forbid_comment}

    {* Принудительный вывод топиков на главную (доступно только админам) *}
    {if E::IsAdmin()}
        {include file='forms/form.field.checkbox.tpl'
        sFieldName  = 'topic_publish_index'
        sFieldNote  = $aLang.topic_create_publish_index_notice
        sFieldLabel = $aLang.topic_create_publish_index}
    {/if}


    {block name='add_topic_form_end'}{/block}
    {hook run="form_add_topic_`$sTopicType`_end"}


    {* Скрытые поля *}
    {include file='forms/form.field.hidden.tpl' sFieldName='topic_type' value=$sTopicType}
    {include file='forms/form.field.hidden.security_key.tpl'}


    {* Кнопки *}
    {if $aParams[0] == 'add' or ($oTopicEdit and $oTopicEdit->getPublish() == 0)}
        {$sSubmitInputText = $aLang.topic_create_submit_publish}
    {else}
        {$sSubmitInputText = $aLang.topic_create_submit_update}
    {/if}

    {include file='forms/form.field.button.tpl'
        sFieldName    = 'submit_topic_publish'
        sFieldClasses = 'btn-primary fl-r'
        sFieldText    = $sSubmitInputText}
    {include file='forms/form.field.button.tpl' sFieldType='button' sFieldClasses='js-topic-preview-text-button' sFieldText=$aLang.topic_create_submit_preview}
    {include file='forms/form.field.button.tpl' sFieldName='submit_topic_save' sFieldText=$aLang.topic_create_submit_save}
    </form>
{* Блок с превью текста *}
    <div class="topic-preview" style="display: none;" id="topic-text-preview"></div>
    {block name='add_topic_end'}{/block}
    {hook run="add_topic_`$sTopicType`_end"}

    {include file='modals/modal.topic.image_upload.tpl'}
{/block}
