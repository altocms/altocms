{extends file="_index.tpl"}

{block name="layout_vars"}
    {$sMainMenuItem='tools'}
{/block}

{block name="content-bar"}

{/block}

{block name="content-body"}

    <div class="span12">
    <div class="b-wbox">
    <div class="b-wbox-header">
        <h3 class="b-wbox-header-title">
            {$aLang.plugin.estheme.admin_title}
        </h3>
    </div>
    <div class="b-wbox-content">
    <div class="b-wbox-content">

    <form method="post" action="" enctype="multipart/form-data" id="tools-estheme" class="form-vertical uniform">
    <input type="hidden" name="security_ls_key" value="{$LIVESTREET_SECURITY_KEY}"/>

    <div data-palette="{asset file="assets/images/HueSaturation.png" skin="admin-default" plugin="estheme"}"
            class="js-estheme-panel">
        {$sPartsPath="{$sTemplatePathEstheme}../admin-default/tpls/actions/admin/action.admin.tools/parts/"}
        {include file="{$sTemplatePathEstheme}../admin-default/tpls/actions/admin/action.admin.tools/estheme.form.tpl" sPartsPath=$sPartsPath}
    </div>

    <br/><br/><br/><br/>

    <input type="submit" name="submit_estheme" value="{$aLang.plugin.estheme.save}"/>
    <input type="submit" name="cancel" value="{$aLang.plugin.estheme.cancel}"/>

    </form>
    </div>
    </div>
    </div>
    </div>
{/block}