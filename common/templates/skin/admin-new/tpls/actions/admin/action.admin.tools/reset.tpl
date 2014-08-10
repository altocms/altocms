{extends file='_index.tpl'}
{block name="content-body"}
<div class="col-md-12">
  <form method="post" action="" class="form-horizontal">
    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
    <div class="panel panel-default">
      <div class="panel-heading">
        <div class="panel-title">
          {$aLang.action.admin.menu_reset_cache}
        </div>
      </div>
      <div class="panel-body">
        <div class="form-group">
          <label for="adm_cache_clear_data" class="col-sm-4 control-label">{$aLang.action.admin.cache_clear_data}</label>
          <div class="col-sm-8">
            <input type="checkbox" id="adm_cache_clear_data" name="adm_cache_clear_data"
            {if $aSettings.adm_cache_clear_data}checked{/if}/>
            <span class="help-block">{$aLang.action.admin.cache_clear_data_notice}</span>
          </div>
          <label for="adm_cache_clear_assets" class="col-sm-4 control-label">{$aLang.action.admin.cache_clear_assets}</label>
          <div class="col-sm-8">
            <input type="checkbox" id="adm_cache_clear_assets" name="adm_cache_clear_assets"
            {if $aSettings.adm_cache_clear_assets}checked{/if}/>
            <span class="help-block">{$aLang.action.admin.cache_clear_assets_notice}</span>
          </div>
            <label for="adm_cache_clear_smarty" class="col-sm-4 control-label">{$aLang.action.admin.cache_clear_smarty}</label>
          <div class="col-sm-8">
            <input type="checkbox" id="adm_cache_clear_smarty" name="adm_cache_clear_smarty"
            {if $aSettings.adm_cache_clear_smarty}checked{/if}/>
            <span class="help-block">{$aLang.action.admin.cache_clear_smarty_notice}</span>
          </div>
        </div>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">
        <div class="panel-title">
          {$aLang.action.admin.menu_reset_config}
        </div>
      </div>
      <div class="panel-body">
        <div class="form-group">
            <label for="adm_reset_config_data" class="col-sm-4 control-label">{$aLang.action.admin.reset_config_data}</label>
          <div class="col-sm-8">
            <input type="checkbox" id="adm_reset_config_data" name="adm_reset_config_data"
            {if $aSettings.adm_cache_clear_data}adm_reset_config_data{/if}/>
            <span class="help-block">{$aLang.action.admin.reset_config_data_notice}</span>
          </div>
        </div>
      </div>
      <div class="panel-footer clearfix">
        <input type="submit" name="adm_reset_submit" value="{$aLang.action.admin.execute}"
          class="btn btn-primary pull-right"/>
      </div>
    </div>
  </form>
</div>
{/block}