{include file='modals/modal.upload_photoset.tpl'}
{include file='commons/common.editor.tpl' sTargetType='topic'}

{if $sMode!='add'}
    <div class="page-header">
        <div class=" header">{$aLang.topic_topic_edit}</div>
    </div>
{/if}

{hook run='add_topic_begin'}

<form action="" method="POST" enctype="multipart/form-data" id="form-topic-add" class="wrapper-content">
    {hook run='form_add_topic_begin'}

    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
    <input type="hidden" id="topic_id"  name="topic_id" value="{$_aRequest.topic_id}"/>

    <div class="form-group">
        <label for="blog_id">{$aLang.topic_create_blog}</label>
        <select name="blog_id" id="blog_id" onChange="ls.blog.loadInfo(jQuery(this).val());" class="form-control">
            {if $bPersonalBlog}
                <option value="0">{$aLang.topic_create_blog_personal}</option>
            {/if}
            {foreach $aBlogsAllow as $oBlog}
                <option value="{$oBlog->getId()}"
                        {if $_aRequest.blog_id==$oBlog->getId()}selected{/if}>{$oBlog->getTitle()|escape:'html'}</option>
            {/foreach}
            {if !$bPersonalBlog && sizeof($aBlogsAllow) <= 1}{* 1 — потому что в $aBlogsAllow всегда есть персональный блог, если они включены *}
                <option value="" disabled="disabled">{$aLang.topic_create_blog_type_not_in_any_blog}</option>
            {/if}
        </select>

        <p class="help-block">
            <small>{$aLang.topic_create_blog_notice}</small>
        </p>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            ls.blog.loadInfo($('#blog_id').val());
        });
    </script>

    <div class="form-group">
        <label for="topic_title">{$aLang.topic_create_title}</label>
        <input type="text" id="topic_title" name="topic_title" value="{$_aRequest.topic_title}"
               class="form-control"/>

        <p class="help-block">
            <small>{$aLang.topic_create_title_notice}</small>
        </p>
    </div>

    {if $sMode != 'add' AND E::IsAdmin()}
        <div class="form-group">
            {strip}
            <label for="topic_url">{$aLang.topic_create_url}:</label>
            <span class="b-topic-url-demo">{$aEditTopicUrl.before}</span>
            <span class="b-topic_url_demo-edit">{$aEditTopicUrl.input}</span>
            {if $_aRequest.topic_url_input AND E::IsAdmin()}
                <input type="text" id="topic_url" name="topic_url" value="{$_aRequest.topic_url_input}"
                       class="input-text input-width-300" style="display: none;"/>
            {/if}
            <span class="b-topic_url_demo">{$aEditTopicUrl.after}</span>
            {/strip}
            {if $sMode != 'add' AND $_aRequest.topic_url_input AND E::IsAdmin()}
                <button class="btn btn-default js-tip-help" title="{$aLang.topic_create_url_edit}"
                        onclick="ls.topic.editUrl(this); return false;"><i class="glyphicon glyphicon-edit"></i></button>
            {/if}
            <button class="btn btn-default js-tip-help" title="{$aLang.topic_create_url_short}"
                    onclick="ls.topic.shortUrl('{$_aRequest.topic_url_short}'); return false;"><i
                        class="glyphicon glyphicon-share"></i></button>
            <small class="note"></small>
        </div>
    {/if}

    <div class="form-group">
        <label for="topic_text">{$aLang.topic_create_text}</label>
        <textarea name="topic_text" id="topic_text" rows="20"
                  class="form-control js-editor-wysiwyg js-editor-markitup">{$_aRequest.topic_text}</textarea>

        {if !Config::Get('view.wysiwyg')}
            {include file='fields/field.tags_help.tpl' sTagsTargetId="topic_text"}
        {/if}
    </div>

    {if $oContentType->isAllow('link')}
        {include file="fields/field.link-edit.tpl"}
    {/if}

    {if $oContentType->isAllow('poll')}
        {include file="fields/field.poll-edit.tpl"}
    {/if}

    {if $oContentType->isAllow('photoset')}
        {include file="fields/field.photoset-edit.tpl" sFormId='#form-topic-add'}
    {/if}

    {if $oContentType}
        {foreach from=$oContentType->getFields() item=oField}
            {include file="fields/customs/field.custom.`$oField->getFieldType()`-edit.tpl" oField=$oField}
        {/foreach}
    {/if}

    {include file="fields/field.tags-edit.tpl"}

    <div class="checkbox">
        <label>
            <input type="checkbox" id="topic_forbid_comment" name="topic_forbid_comment" value="1"
                   {if $_aRequest.topic_forbid_comment==1}checked{/if} />
            {$aLang.topic_create_forbid_comment}
        </label>

        <p class="help-block">
            <small>{$aLang.topic_create_forbid_comment_notice}</small>
        </p>
    </div>

    {if E::IsAdmin()}
        <div class="checkbox">
            <label>
                <input type="checkbox" id="topic_publish_index" name="topic_publish_index" value="1"
                       {if $_aRequest.topic_publish_index==1}checked{/if} />
                {$aLang.topic_create_publish_index}
            </label>

            <p class="help-block">
                <small>{$aLang.topic_create_publish_index_notice}</small>
            </p>
        </div>
    {/if}

    <input type="hidden" name="topic_type" value="{$oContentType->getContentUrl()}" />

    {hook run='form_add_topic_end'}

    <button type="submit" name="submit_topic_publish" class="btn btn-success pull-right">
        {if $oTopic AND $oTopic->getPublish()}
            {$aLang.topic_create_submit_publish_update}
        {else}
            {$aLang.topic_create_submit_publish}
        {/if}
    </button>
    <button type="submit" name="submit_preview" class="btn btn-default js-topic-preview-text-button">
        {$aLang.topic_create_submit_preview}
    </button>
    <button type="submit" name="submit_topic_draft" class="btn btn-default">
        {if $oTopic AND $oTopic->getPublish()}
            {$aLang.topic_create_submit_publish_draft}
        {else}
            {$aLang.topic_create_submit_draft}
        {/if}
    </button>
</form>
<div class="topic-preview js-topic-preview-place" style="display: none;"></div>

{hook run='topic_edit_end,add_topic_end'}

