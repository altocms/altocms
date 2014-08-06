{extends file='./_settings.tpl'}
{block name="content-body-formcontent"}
{foreach $aFields as $aItem}
{if $aItem.type=='section'}
<div class="panel-heading" style="margin-bottom:15px;">
  <h3 class="panel-title">{$aItem.text}</h3>
</div>
{elseif $aItem.config}
<div class="panel-body" style="padding-top:0px;padding-bottom:0px;">
<div class="form-group">
  <label for="{$aItem.label}" class="col-sm-3 control-label">{$aItem.text}</label>
  <div class="col-sm-9">
    {if $aItem.type=='checkbox'}
    <label>
    <input class="form-control" type="{$aItem.type}" name="{$aItem.config}" value="1"
    {if ($aItem.value)}checked{/if} />
    {if ($aItem.help)}<span class="help-block">{$aItem.help}</span>{/if}
    </label>
    {elseif $aItem.type=='select'}
    <select class="form-control" name="{$aItem.config}">
      {foreach from=$aItem.options item=sOption}
      <option value="{$sOption}" {if $sOption==$aItem.value}selected{/if}>
        {$sOption}
      </option>
      {/foreach}
    </select>
    {else}
    {if $aItem.suffix}
    <div class="form-group">
      <input class="form-control" type="text" name="{$aItem.config}" value="{$aItem.value}" />
      <span class="form-group-addon">{$aItem.suffix}</span>
    </div>
    {else}
    <input class="form-control" type="{$aItem.type}" name="{$aItem.config}" value="{$aItem.value}"/>
    {/if}
    {/if}
  </div>
</div>
</div>
{/if}
{/foreach}
{/block}