{extends file='_index.tpl'}

{block name="content-body"}

<form action="" method="post" target="_blank">
    {foreach $aInfoData as $sSectionKey=>$aSection}
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                        <label>
                            <input type="checkbox" checked="checked" id="adm_report_{$sSectionKey}"
                                   name="adm_report_{$sSectionKey}"/>
                            {$aLang.action.admin.button_checkin}
                        </label>

                    <h3 class="panel-title">{$aSection.label}</h3>
                </div>
                <div class="panel-body">
                    {foreach $aSection.data as $sKey=>$aItem}
                        {if ($aItem.label)}
                            {$aItem.label}:
                        {/if}
                        <span class="adm_info_value">{$aItem.value}</span> {if ($aItem['.html'])}{$aItem['.html']}{/if}
                        <br/>
                    {/foreach}
                </div>
            </div>
        </div>
    {/foreach}

    <div class="col-md-12 b-form-actions" style="border-top: 1px solid #ddd; margin-left: 0;">
        <div class="form-group">
            <label class="col-sm-2 control-label">{$aLang.action.admin.button_report}</label>

            <div class="col-sm-10">
                <label>
                    <input type="input-radio" name="report" id="reportTxt" value="TXT" checked="checked">
                    TXT
                </label>
                <label>
                    <input type="input-radio" name="report" id="reportXml" value="XML">
                    XML
                </label>
            </div>
        </div>

        <div class="panel-footer clearfix">
                    <input type="submit" id="butAdmReport" value="{$aLang.action.admin.button_report}"
                           class="btn btn-primary pull-right"/>
        </div>
        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
    </div>
</form>

{/block}