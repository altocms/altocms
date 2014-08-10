{extends file='_index.tpl'}
{block name="content-bar"}
<div class="col-md-12">
  <ul class="nav nav-pills atlass">
    <li class="{if $sMode=='all'}active{/if}">
      <a href="{router page="admin"}{$sEvent}/all/">All</a>
    </li>
    <li class="{if $sMode=='adm'}active{/if}">
      <a href="{router page="admin"}{$sEvent}/adm/">Admin</a>
    </li>
    <li class="{if $sMode=='site'}active{/if}">
      <a href="{router page="admin"}{$sEvent}/site/">Site</a>
    </li>
  </ul>
</div>
{/block}
{block name="content-body"}
  {if $oActiveSkin}
<div class="col-md-6">
  <div class="panel panel-default">
    <div class="panel-heading">
      <div class="tools pull-right">
        <button class="btn btn-primary btn-xs" title data-original-title="Skin for Site"><i class="glyphicon glyphicon-ok"></i></button>
      </div>
      <h3 class="panel-title">
        {$oActiveSkin->GetName()}
        {if $oActiveSkin->GetVersion()}v.{$oActiveSkin->GetVersion()}{/if}
        - {$aLang.action.admin.active_skin}
      </h3>
    </div>
    <div class="panel-body clearfix">
      <div class="col-xs-7">
        {if $oActiveSkin->GetPreviewUrl()}
        <img src="{$oActiveSkin->GetPreviewUrl()}" class="img-responsive b-skin-screenshot" alt=""/>
        {else}
        <div class="b-skin-screenshot"></div>
        {/if}
      </div>
      <div class="col-xs-5">
        <dl>
          <dt>Author:</dt>
          <dd>{$oActiveSkin->GetAuthor()|escape:'html'}</dd>
          <dt>Description:</dt>
          <dd>{$oActiveSkin->GetDescription()|escape:'html'}</dd>
          {if $oActiveSkin->GetHomePage()}
          <dt>Homepage:</dt>
          <dd>{$oActiveSkin->GetHomepage()}</dd>
          {/if}
          {$aThemes=$oActiveSkin->GetThemes()}
          {if $aThemes}
          <dt>{$aLang.action.admin.skin_themes}:</dt>
          <dd>
            {foreach $aThemes as $aTheme}
            {if $aTheme.color}<span class="b-skin-theme-color"
              style="background: {$aTheme.color};">
            &nbsp;</span>{/if}{$aTheme.name}{if !$aTheme@last},{/if}
            {/foreach}
          </dd>
          {/if}
        </dl>
      </div>
    </div>
  </div>
</div>
{$aThemes=$oActiveSkin->GetThemes()}
{if $aThemes}
<div class="col-md-6">
  <div class="panel panel-default">
    <div class="panel-heading">
    <div class="pull-right tools">
      <button class="btn btn-primary btn-xs" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="glyphicon glyphicon-minus"></i></button>
      <button class="btn btn-primary btn-xs" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="glyphicon glyphicon-remove"></i></button>
    </div>
      <h3 class="panel-title">{$aLang.action.admin.skin_settings} {$oActiveSkin->GetName()}</h3>
    </div>
    <div class="panel-body">
      <form class="form-horizontal" action="" method="post">
        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
        <input type="hidden" name="return_url" value="{$PATH_WEB_CURRENT|escape:'html'}"/>
        <input type="hidden" name="skin" value="{$oActiveSkin->GetId()}"/>
        {if $aThemes}
        <div class="form-group">
          <label class="col-sm-2 control-label">{$aLang.action.admin.skin_themes}</label>
          <div class="col-sm-10">
            {foreach $aThemes as $aTheme}
            <label class="col-sm-12">
            <input type="radio" class="input-checkbox"
              name="theme_activate" value="{$aTheme.code}"
              {if $sSiteTheme==$aTheme.code}checked{/if} >
            {if $aTheme.color}<span class="b-skin-theme-color"
              style="background: {$aTheme.color};">
            &nbsp;</span>{/if}{$aTheme.name}
            </label>
            {/foreach}
          </div>
        </div>
        {/if}
      </form>
    </div>
        <div class="panel-footer clearfix">
          <button class="btn btn-primary pull-right">{$aLang.action.admin.save}</button>
        </div>
  </div>
</div>
<div class="col-md-12"></div>
{/if}
{/if}
{foreach $aSkins as $oSkin}
<div class="col-md-6">
  <div class="panel panel-default">
    <div class="panel-heading">
      <div class="tools pull-right">
        {if $oSkin->GetType() == 'adminpanel'}
        <button class="btn btn-primary btn-xs disabled"><i class="glyphicon glyphicon-cog"></i></button>
        {else}
        <button class="btn btn-primary btn-xs skin_select"
          title data-original-title="{$aLang.action.admin.activate}" id="skin-{$oSkin->GetId()}">
        <i class="glyphicon glyphicon-ok"></i></button>
        {/if}
        {if $oSkin->GetType() == 'adminpanel'}
        {/if}
      </div>
      <h3 class="panel-title">
        {$oSkin->GetName()}
        {if $oSkin->GetVersion()}v.{$oSkin->GetVersion()}{/if}
      </h3>
    </div>
    <div class="panel-body clearfix">
      <div class="col-xs-7">
        {if $oSkin->GetPreviewUrl()}
        <img src="{$oSkin->GetPreviewUrl()}" class="img-responsive b-skin-screenshot" alt=""/>
        {else}
        <div class="b-skin-screenshot"></div>
        {/if}
      </div>
      <div class="col-xs-5">
        <dl>
          <dt>Author:</dt>
          <dd>{$oSkin->GetAuthor()|escape:'html'}
          <dd>
          <dt>Description:</dt>
          <dd>{$oSkin->GetDescription()|escape:'html'}</dd>
          {if $oSkin->GetHomePage()}
          <dt>Homepage:</dt>
          <dd>{$oSkin->GetHomepage()}</dd>
          {/if}
        </dl>
      </div>
    </div>
  </div>
</div>
{/foreach}
<form action="" method="post" id="form-skin-select">
  <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
  <input type="hidden" name="return_url" value="{$PATH_WEB_CURRENT|escape:'html'}"/>
  <input type="hidden" name="skin_activate" value=""/>
</form>
<script>
  $(function () {
      $('button[class*=skin_select]').click(function () {
          var f = $('#form-skin-select');
          var skin = $(this).prop('id').substr(5);
          f.find('[name=skin_activate]').val(skin);
          f.submit();
      });
  })
</script>
{/block}