{assign var="noSidebar" value=true}
{include file='header.tpl'}
    <div class="panel panel-default panel-search flat">
        <div class="panel-body">
            <h2 class="panel-header">
                {$aLang.plugin.estheme.admin_title}
            </h2>
        </div>
    </div>


    <div class="container">
        <div class="row">
            {if $sCSSDownloadPath}
                <h4>{$aLang.plugin.estheme.path} <a href="{$sCSSDownloadPath}" target="_blank">{$sCSSDownloadPath}</a></h4>
            {/if}
            <form method="post" action="" enctype="multipart/form-data" id="tools-estheme" class="form-vertical uniform">
                <input type="hidden" name="security_ls_key" value="{$LIVESTREET_SECURITY_KEY}"/>

                <div data-palette="{asset file="assets/images/HueSaturation.png" skin="admin-default" plugin="estheme"}"
                     class="js-estheme-panel">
                    {$sPartsPath="{$sTemplatePathEstheme}tpls/actions/ActionEstheme/parts/"}
                    {include
                    file="{$sTemplatePathEstheme}../admin-default/tpls/actions/admin/action.admin.tools/estheme.form.tpl"
                    sPartsPath=$sPartsPath}
                </div>

                <br/><br/><br/><br/>

                <input type="submit" class="btn btn-default" name="submit_estheme" value="{$aLang.plugin.estheme.save}"/>
                <input type="submit" class="btn btn-primary" name="cancel" value="{$aLang.plugin.estheme.cancel}"/>

            </form>
        </div>
    </div>


{include file='footer.tpl'}