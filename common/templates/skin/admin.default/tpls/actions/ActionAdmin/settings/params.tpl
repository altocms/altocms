{extends file='./config.tpl'}

{block name="content-body-formcontent"}

{foreach $aFields as $aItem}
    {if $aItem.type=='section'}
        <div class="b-wbox-header">
            <div class="b-wbox-header-title">{$aItem.text}</div>
        </div>
    {elseif $aItem.config}
        <div class="control-group">
            <label for="{$aItem.label}" class="control-label">{$aItem.text}</label>

            <div class="controls">
                {if $aItem.type=='checkbox'}
                    <label>
                        <input type="{$aItem.type}" name="{$aItem.config}" value="1"
                               {if ($aItem.value)}checked{/if} />
                        {if ($aItem.help)}<span class="help-inline">{$aItem.help}</span>{/if}
                    </label>
                {elseif $aItem.type=='select'}
                    <select name="{$aItem.config}">
                        {foreach from=$aItem.options item=sOption}
                            <option value="{$sOption}" {if $sOption==$aItem.value}selected{/if}>
                                {$sOption}
                            </option>
                        {/foreach}
                    </select>
                {else}
                    {if $aItem.suffix}
                        <div class="input-append">
                            <input type="text" name="{$aItem.config}" value="{$aItem.value}" />
                            <span class="add-on">{$aItem.suffix}</span>
                        </div>
                    {else}
                        <input type="{$aItem.type}" name="{$aItem.config}" value="{$aItem.value}"/>
                    {/if}
                {/if}
            </div>
        </div>
    {/if}
{/foreach}

{/block}