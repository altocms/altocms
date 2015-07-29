 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{include file='modals/modal.upload_photoset.tpl'}
{include file='commons/common.editor.tpl' sTargetType='topic'}

<!-- Блок создания -->
<div class="panel panel-default content-write flat">
    <div class="panel-body">

        {$sMenuType=C::Get('view.content_type_menu')}
        {if $sMenuType=='collapsed'}
            <script>
                $(function() {
                    $('.js-content-type-menu-container').altoCollapsedMenu({
                        collapse: '.right-placed-menu',
                        hidden: '.topic-menu-hidden',
                        widthCorrect: 20,
                        other: []
                    });
                })
            </script>
        {/if}



        <div class="panel-header-container">
            <div class="col-md-{if $sMenuType=='select'}12{else}6{/if}">
                <div class="panel-header">
                    {if $sMode == 'add'}
                        {$aLang.topic_add}
                    {else}
                        {$aLang.blog_edit}
                    {/if}
                </div>
            </div>
            <div class="js-content-type-menu-container col-md-{if $sMenuType=='select'}12{else}18{/if}">
                {if $sMenuType=='select'}
                    <div class="form-group">
                        <select name="blog_id" id="blog_id" onchange="location = this.options[this.selectedIndex].value;" class="form-control">
                        {foreach from=$aContentTypes item=oContentTypeItem}
                            {if $oContentTypeItem->isAccessible()}
                                <option value="{router page='content'}{$oContentTypeItem->getContentUrl()}/add/" {if Router::GetActionEvent() == {$oContentTypeItem->getContentUrl()}} class="active" {/if}>
                                    {$oContentTypeItem->getContentTitle()|escape:'html'}
                                </option>
                            {/if}
                        {/foreach}
                            <option value="{router page='blog'}add">
                                {$aLang.block_create_blog}
                            </option>
                            <option value="{router page='talk'}add">
                                {$aLang.block_create_talk}
                            </option>
                            {hook run='write_item' isPopup=true}
                            {if $iUserCurrentCountTopicDraft}
                                <option value="{router page='content'}drafts/" {if Router::GetActionEvent() == 'drafts'} class="active" {/if}>
                                    {$iUserCurrentCountTopicDraft} {$iUserCurrentCountTopicDraft|declension:$aLang.draft_declension:$sLang}
                                </option>
                            {/if}
                        </select>
                    </div>
                {else}
                    <ul class="right-placed-menu pull-right">
                        {foreach from=$aContentTypes item=oContentTypeItem}
                            {if $oContentTypeItem->isAccessible()}
                                <li {if Router::GetActionEvent() == {$oContentTypeItem->getContentUrl()}} class="active" {/if}>
                                    <a href="{router page='content'}{$oContentTypeItem->getContentUrl()}/add/">
                                        {$oContentTypeItem->getContentTitle()|escape:'html'}
                                    </a>
                                </li>
                            {/if}
                        {/foreach}
                        <li>
                            <a href="{router page='blog'}add">
                                {$aLang.block_create_blog}
                            </a>
                        </li>
                        <li>
                            <a href="{router page='talk'}add">
                                {$aLang.block_create_talk}
                            </a>
                        </li>
                        {hook run='write_item' isPopup=true}
                        {if $iUserCurrentCountTopicDraft}
                            <li {if Router::GetActionEvent() == 'drafts'} class="active" {/if}>
                                <a href="{router page='content'}drafts/"
                                   class="write-item-link">
                                    {$iUserCurrentCountTopicDraft} {$iUserCurrentCountTopicDraft|declension:$aLang.draft_declension:$sLang}
                                </a>
                            </li>
                        {/if}
                        {if $sMenuType=='collapsed'}
                            <li class="dropdown right menu-hidden-container hidden">
                                <a data-toggle="dropdown" href="#" class="menu-hidden-trigger">
                                    {$aLang.more}<span class="caret"></span>
                                </a>
                                <!-- контейнер скрытых элементов -->
                                <ul class="topic-menu-hidden dropdown-menu animated fadeIn dropdown-content-menu"></ul>
                            </li>
                        {/if}
                    </ul>
                {/if}
            </div>
        </div>

        {hook run='add_topic_begin'}

        <form action="" method="POST" enctype="multipart/form-data" id="form-topic-add" class="topic-edit">
            {hook run='form_add_topic_begin'}

            <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
            <input type="hidden" id="topic_id"  name="topic_id" value="{$_aRequest.topic_id}"/>

            {* ВЫБОР БЛОГА *}
            <div class="info-container"><i class="fa fa-info-circle pull-right js-title-topic" data-original-title="{$aLang.topic_create_blog_notice}"></i></div>
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
                            {if $oBlog->getType() == 'personal'}{continue}{/if}
                            <option value="{$oBlog->getId()}"
                                    {if $_aRequest.blog_id==$oBlog->getId()}selected{/if}>{$oBlog->getTitle()|escape:'html'}</option>
                        {/foreach}
                        {if !$bPersonalBlog && sizeof($aBlogsAllow) <= 1}{* 1 — потому что в $aBlogsAllow всегда есть персональный блог, если они включены *}
                            <option value="" disabled="disabled">{$aLang.topic_create_blog_type_not_in_any_blog}</option>
                        {/if}
                    </select>
                </div>

            </div>

            {* ЗАГОЛОВОК *}
            <div class="info-container"><i class="fa fa-info-circle pull-right js-title-topic" data-original-title="{$aLang.topic_create_title_notice}"></i></div>
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-header"></i></span>
                    <input type="text" id="topic_title" name="topic_title" value="{$_aRequest.topic_title}" class="form-control"/>
                </div>
            </div>

            {* ПОЛУЧЕНИЕ КОРОТКОЙ ССЫЛКИ НА ТОПИК *}
            {if $sMode != 'add' AND E::IsAdmin()}
                {if $aEditTopicUrl.input == ''}
                    <div class="form-group has-feedback">
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
                    <div class="form-group has-feedback">
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
                {* ПОЯСНЕНИЯ К РЕДАКТОРУ *}
                {if !Config::Get('view.wysiwyg')}
                    <div class="clearfix">
                        <a class="link link-lead link-blue control-twice pull-right" href="#"
                           onclick="$('.tags-about').slideToggle(100);
                                    $(this).toggleClass('active');
                                    return false;">{$aLang.topic_create_text_notice}</a>
                    </div>
                    {include file='fields/field.tags_help.tpl' sTagsTargetId="topic_text"}
                {/if}
            </div>

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
                    <label>
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
                        <label>
                            <input class="mal0"  type="checkbox" id="topic_publish_index" name="topic_publish_index" value="1"
                                   {if $_aRequest.topic_publish_index==1}checked{/if} />
                            {$aLang.topic_create_publish_index}
                        </label>
                    </div>
                    <small class="control-notice">{$aLang.topic_create_publish_index_notice}</small>
                </div>
            {/if}

            <input type="hidden" name="topic_type" value="{$oContentType->getContentUrl()}" />

            {hook run='form_add_topic_end'}

            <br/><br/>

            <button type="submit" name="submit_topic_publish" class="btn btn-blue btn-normal corner-no pull-right">
                {if $oTopic AND $oTopic->getPublish()}
                    {$aLang.topic_create_submit_publish_update}
                {else}
                    {$aLang.topic_create_submit_publish}
                {/if}
            </button>
            <button type="submit" name="submit_preview" class="btn btn-light btn-normal corner-no js-topic-preview-text-button">
                {$aLang.topic_create_submit_preview}
            </button>
            <button type="submit" name="submit_topic_draft" class="btn btn-light btn-normal corner-no">
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
<div style="display: none;" class="panel panel-default content-write flat js-topic-preview-place">
    <div class="panel-body">
        <div class="topic-preview"></div>
    </div>
</div>

{hook run='topic_edit_end,add_topic_end'}

