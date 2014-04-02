{extends file='_index.tpl'}

{block name="content-body"}
<div class="span12">

    <form method="post" action="" class="uniform">
        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>

        <div class="b-wbox">
            <div class="b-wbox-header">
                <div class="b-wbox-header-title">
                    {$aLang.action.admin.menu_reset_cache}
                </div>
            </div>
            <div class="b-wbox-content">
                <div class="offset1">
                    <div class="control">
                        <label class="checkbox">
                            <input type="checkbox" id="adm_cache_clear_data" name="adm_cache_clear_data"
                                   {if $aSettings.adm_cache_clear_data}checked{/if}/>
                            {$aLang.action.admin.cache_clear_data}</label>
                        <span class="help-block">{$aLang.action.admin.cache_clear_data_notice}</span>
                    </div>

                    <div class="control">
                        <label class="checkbox">
                            <input type="checkbox" id="adm_cache_clear_assets" name="adm_cache_clear_assets"
                                   {if $aSettings.adm_cache_clear_assets}checked{/if}/>
                            {$aLang.action.admin.cache_clear_assets}</label>
                        <span class="help-block">{$aLang.action.admin.cache_clear_assets_notice}</span>
                    </div>

                    <div class="control">
                        <label class="checkbox">
                            <input type="checkbox" id="adm_cache_clear_smarty" name="adm_cache_clear_smarty"
                                   {if $aSettings.adm_cache_clear_smarty}checked{/if}/>
                            {$aLang.action.admin.cache_clear_smarty}</label>
                        <span class="help-block">{$aLang.action.admin.cache_clear_smarty_notice}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="b-wbox">
            <div class="b-wbox-header">
                <div class="b-wbox-header-title">
                    {$aLang.action.admin.menu_reset_config}
                </div>
            </div>
            <div class="b-wbox-content">

                <div class="offset1">
                    <div class="control">
                        <label class="checkbox">
                            <input type="checkbox" id="adm_reset_config_data" name="adm_reset_config_data"
                                   {if $aSettings.adm_cache_clear_data}adm_reset_config_data{/if}/>
                            {$aLang.action.admin.reset_config_data}</label>
                        <span class="help-block">{$aLang.action.admin.reset_config_data_notice}</span>
                    </div>

                </div>
            </div>
        </div>

        <div class="navbar navbar-inner">
                    <input type="submit" name="adm_reset_submit" value="{$aLang.action.admin.execute}"
                           class="btn btn-primary pull-right"/>
        </div>

    </form>

</div>

{/block}