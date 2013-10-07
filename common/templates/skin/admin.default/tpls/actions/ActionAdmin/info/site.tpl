{extends file='_index.tpl'}

{block name="content-body"}

<form action="" method="post" target="_blank">
    {foreach $aInfoData as $sSectionKey=>$aSection}
        <div class="span6">
            <div class="b-wbox">
                <div class="b-wbox-header">
                        <label class="checkbox">
                            <input type="checkbox" checked="checked" id="adm_report_{$sSectionKey}"
                                   name="adm_report_{$sSectionKey}"/>
                            {$aLang.action.admin.button_checkin}
                        </label>

                    <h3 class="b-wbox-header-title">{$aSection.label}</h3>
                </div>
                <div class="b-wbox-content">
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

    <div class="span12 b-form-actions" style="border-top: 1px solid #FFFFFF; margin-left: 0; box-shadow: 0 -1px 0px rgba(32,32,32,0.5)">
        <div class="control-group">
            <label class="control-label">{$aLang.action.admin.button_report}</label>

            <div class="controls">
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

        <div class="navbar navbar-inner">
                    <input type="submit" id="butAdmReport" value="{$aLang.action.admin.button_report}"
                           class="btn btn-primary pull-right"/>
        </div>
        <input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}"/>
    </div>
</form>

{/block}