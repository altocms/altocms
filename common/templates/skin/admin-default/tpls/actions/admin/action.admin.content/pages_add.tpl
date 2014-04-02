{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="{router page='admin'}content-pages/" class="btn btn-default"><i class="icon icon-action-undo"></i></a>
    </div>
{/block}

{block name="content-body"}

<div class="span12">

    {include file='inc.editor.tpl' sImgToLoad='page_text'}

    <div class="b-wbox">
        <div class="b-wbox-header">
            <div class="b-wbox-header-title">
                {if $sMode=='add'}
                    {$aLang.action.admin.pages_new}
                {else}
                    {$aLang.action.admin.pages_edit}: {$_aRequest.page_title}
                {/if}
            </div>
        </div>
        <div class="b-wbox-content nopadding">
            <form action="" method="POST" class="form-horizontal uniform" enctype="multipart/form-data">
                {hook run='plugin_page_form_add_begin'}
                <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
                <input type="hidden" name="page_id" value="{$_aRequest.page_id}">

                <div class="control-group">
                    <label for="page_pid" class="control-label">{$aLang.action.admin.pages_create_parent_page}</label>

                    <div class="controls">
                        <select name="page_pid" id="page_pid" class="">
                            <option value="0"></option>
                            {foreach from=$aPages item=oPage}
                                {if $_aRequest.page_id != $oPage->getId()}
                                    <option style="margin-left: {$oPage->getLevel()*20}px;" value="{$oPage->getId()}"
                                            {if $_aRequest.page_pid==$oPage->getId()}selected{/if}>{$oPage->getTitle()}
                                        (/{$oPage->getUrlFull()}/)
                                    </option>
                                {/if}
                            {/foreach}
                        </select>
                    </div>
                </div>


                <div class="control-group">
                    <label for="page_title" class="control-label">{$aLang.action.admin.pages_create_title}:</label>

                    <div class="controls">
                        <input type="text" id="page_title" class="input-text" name="page_title"
                               value="{$_aRequest.page_title}" />
                    </div>
                </div>


                <div class="control-group">
                    <label for="page_url" class="control-label">{$aLang.action.admin.pages_create_url}:</label>

                    <div class="controls">
                        <input type="text" class="input-text" id="page_url" name="page_url"
                               value="{$_aRequest.page_url}" />
                    </div>
                </div>


                <div class="control-group">
                    <label for="page_text" class="control-label">{$aLang.action.admin.pages_create_text}:</label>

                    <div class="controls">
                        <textarea name="page_text" id="page_text" rows="20"
                                  class="js-editor-markitup">{$_aRequest.page_text}</textarea>
                    </div>
                </div>

                <div class="control-group">
                    <label for="page_seo_keywords" class="control-label">{$aLang.action.admin.pages_create_seo_keywords}
                        :</label>

                    <div class="controls">
                        <input type="text" class="input-text" id="page_seo_keywords"
                               name="page_seo_keywords"
                               value="{$_aRequest.page_seo_keywords}" />
                        <span class="help-block">{$aLang.action.admin.pages_create_seo_keywords_notice}</span>
                    </div>
                </div>

                <div class="control-group">
                    <label for="page_seo_description"
                           class="control-label">{$aLang.action.admin.pages_create_seo_description}:</label>

                    <div class="controls">
                        <input type="text" class="input-text" id="page_seo_description"
                               name="page_seo_description"
                               value="{$_aRequest.page_seo_description}" />
                        <span class="help-block">{$aLang.action.admin.pages_create_seo_description_notice}</span>
                    </div>
                </div>

                <div class="control-group">
                    <label for="page_sort" class="control-label">{$aLang.action.admin.pages_create_sort}:</label>

                    <div class="controls">
                        <input type="text" id="page_sort" class="input-text" name="page_sort"
                               value="{$_aRequest.page_sort}" />
                        <span class="help-block">{$aLang.action.admin.pages_create_sort_notice}</span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label"></label>

                    <div class="controls">
                        <label>
                            <input type="checkbox" id="page_auto_br" name="page_auto_br" value="1"
                                   class="input-checkbox"
                                   {if $_aRequest.page_auto_br==1}checked{/if}/> {$aLang.action.admin.pages_create_auto_br}
                        </label>
                        <label>
                            <input type="checkbox" id="page_main" name="page_main" value="1" class="input-checkbox"
                                   {if $_aRequest.page_main==1}checked{/if} /> {$aLang.action.admin.pages_create_main}
                        </label>
                        <label>
                            <input type="checkbox" id="page_active" name="page_active" value="1" class="input-checkbox"
                                   {if $_aRequest.page_active==1}checked{/if} /> {$aLang.action.admin.pages_create_active}
                        </label>
                    </div>
                </div>

                {hook run='plugin_page_form_add_end'}
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"
                            name="submit_page_save">{$aLang.action.admin.pages_create_submit_save}</button>
                </div>

            </form>
        </div>
    </div>

</div>


{/block}