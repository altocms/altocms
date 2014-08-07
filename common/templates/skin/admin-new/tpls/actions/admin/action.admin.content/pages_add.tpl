{extends file='_index.tpl'}
{block name="content-bar"}
<div class="col-md-12">
  <a href="{router page='admin'}content-pages/" class="btn btn-primary mb15"><i class="ion-plus-round"></i></a>
</div>
{/block}
{block name="content-body"}
<div class="col-md-12">
  {include file='inc.editor.tpl' sImgToLoad='page_text'}
  <div class="panel panel-default">
    <div class="panel-heading">
      <div class="panel-title">
        {if $sMode=='add'}
        {$aLang.action.admin.pages_new}
        {else}
        {$aLang.action.admin.pages_edit}: {$_aRequest.page_title}
        {/if}
      </div>
    </div>
    <div class="panel-body">
      <form action="" method="POST" class="form-horizontal" enctype="multipart/form-data">
        {hook run='plugin_page_form_add_begin'}
        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
        <input type="hidden" name="page_id" value="{$_aRequest.page_id}">
        <div class="form-group">
          <label for="page_pid" class="col-sm-2 control-label">{$aLang.action.admin.pages_create_parent_page}</label>
          <div class="col-sm-10">
            <select name="page_pid" id="page_pid" class="form-control">
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
        <div class="form-group">
          <label for="page_title" class="col-sm-2 control-label">{$aLang.action.admin.pages_create_title}:</label>
          <div class="col-sm-10">
            <input type="text" id="page_title" class="form-control" name="page_title"
              value="{$_aRequest.page_title}" />
          </div>
        </div>
        <div class="form-group">
          <label for="page_url" class="col-sm-2 control-label">{$aLang.action.admin.pages_create_url}:</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" id="page_url" name="page_url"
              value="{$_aRequest.page_url}" />
          </div>
        </div>
        <div class="form-group">
          <label for="page_text" class="col-sm-2 control-label">{$aLang.action.admin.pages_create_text}:</label>
          <div class="col-sm-10">
            <textarea id="editor1" name="editor1" rows="20" name="page_text" id="page_text" class="textarea" placeholder="Place some text here" style="width: 100%; height: 200px; font-size: 14px; border-radius: 4px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;">{$_aRequest.page_text}</textarea>
          </div>
        </div>
        <div class="form-group">
          <label for="page_seo_keywords" class="col-sm-2 control-label">{$aLang.action.admin.pages_create_seo_keywords}
          :</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" id="page_seo_keywords"
              name="page_seo_keywords"
              value="{$_aRequest.page_seo_keywords}" />
            <span class="help-block">{$aLang.action.admin.pages_create_seo_keywords_notice}</span>
          </div>
        </div>
        <div class="form-group">
          <label for="page_seo_description"
            class="col-sm-2 control-label">{$aLang.action.admin.pages_create_seo_description}:</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" id="page_seo_description"
              name="page_seo_description"
              value="{$_aRequest.page_seo_description}" />
            <span class="help-block">{$aLang.action.admin.pages_create_seo_description_notice}</span>
          </div>
        </div>
        <div class="form-group">
          <label for="page_sort" class="col-sm-2 control-label">{$aLang.action.admin.pages_create_sort}:</label>
          <div class="col-sm-10">
            <input type="text" id="page_sort" class="form-control" name="page_sort"
              value="{$_aRequest.page_sort}" />
            <span class="help-block">{$aLang.action.admin.pages_create_sort_notice}</span>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-2 control-label"></label>
          <div class="col-sm-10">
            <label class="col-sm-12">
            <input type="checkbox" id="page_auto_br" name="page_auto_br" value="1"
              class="input-checkbox"
              {if $_aRequest.page_auto_br==1}checked{/if}/> {$aLang.action.admin.pages_create_auto_br}
            </label>
            <label class="col-sm-12">
            <input type="checkbox" id="page_main" name="page_main" value="1" class="input-checkbox"
              {if $_aRequest.page_main==1}checked{/if} /> {$aLang.action.admin.pages_create_main}
            </label>
            <label class="col-sm-12">
            <input type="checkbox" id="page_active" name="page_active" value="1" class="input-checkbox"
              {if $_aRequest.page_active==1}checked{/if} /> {$aLang.action.admin.pages_create_active}
            </label>
          </div>
        </div>
      </form>
    </div>
    {hook run='plugin_page_form_add_end'}
    <div class="panel-footer clearfix">
      <button type="submit" class="btn btn-primary pull-right"
        name="submit_page_save">{$aLang.action.admin.pages_create_submit_save}</button>
    </div>
  </div>
</div>
{/block}