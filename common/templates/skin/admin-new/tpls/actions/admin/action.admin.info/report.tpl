{extends file='_index.tpl'}
{block name="content-body"}
<form action="" method="post" target="_blank" class="uniform">
  {foreach $aInfoData as $sSectionKey=>$aSection}
  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading">
        <div class="tools pull-right">
          <label>
          <input type="checkbox" checked="checked" id="adm_report_{$sSectionKey}"
            name="adm_report_{$sSectionKey}"/>
          {$aLang.action.admin.button_checkin}
          </label>
        </div>
        <h3 class="panel-title">{$aSection.label}</h3>
      </div>
      <div class="panel-body">
        {foreach $aSection.data as $sKey=>$aItem}
        {if ($aItem.label)}
        <span class="col-md-4">{$aItem.label}:</span>
        {/if}
        <span class="col-md-8">{$aItem.value}</span>
        {if ($aItem['.html'])}{$aItem['.html']}{/if}
        <br/>
        {/foreach}
      </div>
    </div>
  </div>
  {/foreach}
  <div class="col-md-12">
    <div class="panel panel-default noborder">
      <div class="panel-body">
        <div class="panel-heading"></div>
        <div class="form-group">
          <label class="col-sm-2 control-label">{$aLang.action.admin.button_report}</label>
          <div class="col-sm-10">
            <label class="col-md-12">
            <input class="input-radio" type="radio" name="report" id="reportTxt" value="TXT" checked="checked">
            TXT
            </label>
            <label class="col-md-12">
            <input class="input-radio" type="radio" name="report" id="reportXml" value="XML">
            XML
            </label>
          </div>
        </div>
      </div>
      <div class="panel-footer clearfix">
        <input type="submit" id="butAdmReport" value="{$aLang.action.admin.button_report}" class="btn btn-primary pull-right"/>
      </div>
    </div>
  </div>
</form>
{/block}