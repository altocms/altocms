 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="themes/$sSkinTheme/layouts/default_light.tpl"}

{block name="layout_vars"}
    {$noShowSystemMessage=true}
{/block}

{block name="layout_pre_content"}
    {if $aMsgError[0].title}
        <div class="panel panel-default panel-search flat">
            <div class="panel-body">
                <h2 class="panel-header">
                    {$aLang.error}: {$aMsgError[0].title}
                </h2>
            </div>
        </div>
    {/if}
{/block}

{block name="layout_content"}
<div class="row">
    <div class="col-xs-20 col-xs-offset-2 col-sm-12 col-sm-offset-6  col-md-12 col-md-offset-6  col-lg-12 col-lg-offset-6">
        <br/><br/><br/>
    {foreach $aMsgError as $sMsg}
        <div class="bg-warning">{$sMsg.msg}</div>
    {/foreach}
    <hr>
    <p>
        <a class="link link-lead link-clear" href="javascript:history.go(-1);"><i class="fa fa-repeat"></i>&nbsp;{$aLang.site_history_back}</a>,
        <a class="link link-lead link-clear" href="{Config::Get('path.root.url')}">{$aLang.site_go_main}</a>
    </p>

    </div>
</div>
{/block}
