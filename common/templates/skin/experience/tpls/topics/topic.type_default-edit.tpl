 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{include file='modals/modal.upload_photoset.tpl'}
{include file='commons/common.editor.tpl' sTargetType='topic'}

<!-- Блок создания -->
<div class="panel panel-default content-write raised">
    <div class="panel-body">

        {hook run='add_topic_begin'}

        <form action="" method="POST" enctype="multipart/form-data" id="form-topic-add" class="topic-edit">
            {hook run='form_add_topic_begin'}

            <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
            <input type="hidden" id="topic_id"  name="topic_id" value="{$_aRequest.topic_id}"/>

            {* ВЫБОР БЛОГА *}
            <div class="form-group">
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        ls.blog.loadInfo($('#blog_id').val());
                    });
                </script>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-comment-o"></i></span>
                    <select name="blog_id" id="blog_id" onChange="ls.blog.loadInfo(jQuery(this).val());" class="form-control">
                        {if $bPersonalBlog}
                            <option value="0">{$aLang.topic_create_blog_personal}</option>
                        {/if}
                        {foreach $aBlogsAllow as $oBlog}
                            <option value="{$oBlog->getId()}"
                                    {if $_aRequest.blog_id==$oBlog->getId()}selected{/if}>{$oBlog->getTitle()|escape:'html'}</option>
                        {/foreach}
                    </select>
                </div>
                <small class="control-notice">{$aLang.topic_create_blog_notice}</small>
            </div>

            {* ЗАГОЛОВОК *}
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-header"></i></span>
                    <input type="text" id="topic_title" name="topic_title" value="{$_aRequest.topic_title}" class="form-control"/>
                </div>
                <small class="control-notice">{$aLang.topic_create_title_notice}</small>
            </div>

            {* ПОЛУЧЕНИЕ КОРОТКОЙ ССЫЛКИ НА ТОПИК *}
            {if $sMode != 'add' AND E::IsAdmin()}
                {if $aEditTopicUrl.input == ''}
                    <div class="form-group has-feedback"">
                        <div class="input-group">
                            <span class="input-group-addon">{$aLang.topic_create_url}</span>
                            <input type="text" id="topic_url" name="topic_url" value="{$aEditTopicUrl.before}{$aEditTopicUrl.input}{$aEditTopicUrl.after}" class="form-control" readonly/>
                            <a href="#" class="link link-lead link-dark form-control-feedback"
                               title="{$aLang.topic_create_url_short}" onclick="ls.topic.shortUrl('{$_aRequest.topic_url_short}'); return false;"><i class="fa fa-share"></i></a>
                        </div>
                    </div>
                {else}
    {*<script>*}
        {*$(function(){*}
            {*function resizeInput() {*}
                {*$(this).attr('size', $(this).val().length-2);*}
            {*}*}

            {*$('.auto-input')*}
                    {*.keyup(resizeInput)*}
                    {*.each(resizeInput);*}
        {*})*}
    {*</script>*}
                    <div class="form-group has-feedback"">
                        <div class="input-group">
                            <span class="input-group-addon">{$aLang.topic_create_url}</span>
                            <table class="form-control">
                                <tr>
                                    <td>{$aEditTopicUrl.before}</td>
                                    <td class="input-container"><input class="auto-input" type="text" id="topic_url" name="topic_url" value="{$aEditTopicUrl.input}"  /></td>
                                    <td>{$aEditTopicUrl.after}</td>
                                </tr>
                            </table>

                            <a href="#" class="link link-lead link-dark form-control-feedback"
                               title="{$aLang.topic_create_url_short}" onclick="ls.topic.shortUrl('{$_aRequest.topic_url_short}'); return false;"><i class="fa fa-share"></i></a>
                        </div>

                    </div>
                {/if}
            {/if}

            {* РЕДАКТОР *}
            <div class="form-group">
                <textarea name="topic_text" id="topic_text" rows="20"
                          class="form-control js-editor-wysiwyg js-editor-markitup">{$_aRequest.topic_text}</textarea>
            </div>
            {if !Config::Get('view.wysiwyg')}
                <div class="row">
                    <div class="col-xs-6">
                        <a class="link link-lead link-blue control-twice" href="#"
                           onclick="$('.tags-about').slideToggle(100);
                              $(this).toggleClass('active');
                              return false;">{$aLang.topic_create_text_notice}</a>
                    </div>
                </div>
            {/if}

            {* ПОЯСНЕНИЯ К РЕДАКТОРУ *}
            {if !Config::Get('view.wysiwyg')}
                {include file='fields/field.tags_help.tpl' sTagsTargetId="topic_text"}
            {/if}

            {* ТЕГИ *}
            {include file="fields/field.tags-edit.tpl"}

            {* ССЫЛКА *}
            {if $oContentType->isAllow('link')}
                {include file="fields/field.link-edit.tpl"}
            {/if}

            {* ОПРОС *}
            {if $oContentType->isAllow('poll')}
                {include file="fields/field.poll-edit.tpl"}
            {/if}

            {* ФОТОСЕТ *}
            {if $oContentType->isAllow('photoset')}
                {include file="fields/field.photoset-edit.tpl" sFormId='#form-topic-add'}
            {/if}

            {if $oContentType}
                {foreach from=$oContentType->getFields() item=oField}
                    {include file="fields/customs/field.custom.`$oField->getFieldType()`-edit.tpl" oField=$oField}
                {/foreach}
            {/if}

            <br/><br/>

            <div class="form-group checkbox">
                <div class="input-group">
                    <label for="public_topic">
                        <input class="mal0" type="checkbox" id="topic_forbid_comment" name="topic_forbid_comment" value="1"
                               {if $_aRequest.topic_forbid_comment==1}checked{/if} />
                        {$aLang.topic_create_forbid_comment}
                    </label>
                </div>
                <small class="control-notice">{$aLang.topic_create_forbid_comment_notice}</small>
            </div>


            {if E::IsAdmin()}
                <div class="form-group checkbox">
                    <div class="input-group">
                        <label for="public_topic">
                            <input class="mal0"  type="checkbox" id="topic_publish_index" name="topic_publish_index" value="1"
                                   {if $_aRequest.topic_publish_index==1}checked{/if} />
                            {$aLang.topic_create_publish_index}
                        </label>
                    </div>
                    <small class="control-notice">{$aLang.topic_create_publish_index_notice}</small>
                </div>
            {/if}

            <input type="hidden" name="topic_type" value="topic"/>

            {hook run='form_add_topic_end'}

            <br/><br/>

            <button type="submit" name="submit_topic_publish" class="btn btn-blue btn-big corner-no pull-right">
                {if $oTopic AND $oTopic->getPublish()}
                    {$aLang.topic_create_submit_publish_update}
                {else}
                    {$aLang.topic_create_submit_publish}
                {/if}
            </button>
            <button type="submit" name="submit_preview" class="btn btn-light btn-big corner-no js-topic-preview-text-button">
                {$aLang.topic_create_submit_preview}
            </button>
            <button type="submit" name="submit_topic_draft" class="btn btn-light btn-big corner-no">
                {if $oTopic AND $oTopic->getPublish()}
                    {$aLang.topic_create_submit_publish_draft}
                {else}
                    {$aLang.topic_create_submit_draft}
                {/if}
            </button>
        </form>

    </div>
</div>

<!-- Блок создания -->
<div style="display: none;" class="panel panel-default content-write raised js-topic-preview-place">
    <div class="panel-body">
        <div class="topic-preview"></div>
    </div>
</div>

{hook run='topic_edit_end,add_topic_end'}

