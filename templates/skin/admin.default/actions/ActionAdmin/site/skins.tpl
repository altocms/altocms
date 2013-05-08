{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group span6">
        <a href="{router page="admin"}{$sEvent}/all/" class="btn {if $sMode=='all'}active{/if}">All</a>
        <a href="{router page="admin"}{$sEvent}/adm/" class="btn {if $sMode=='adm'}active{/if}">Admin</a>
        <a href="{router page="admin"}{$sEvent}/site/" class="btn {if $sMode=='site'}active{/if}">Site</a>
    </div>
{/block}

{block name="content-body"}

    {if $oActiveSkin}
        <div class="span6">

            <div class="b-wbox">
                <div class="b-wbox-header">
                    <span class="icon tip-top" title="Skin for Site"><i class="icon-globe"></i></span>

                    <h3 class="b-wbox-header-title">
                        {$oActiveSkin->GetName()}
                        {if $oActiveSkin->GetVersion()}v.{$oActiveSkin->GetVersion()}{/if}
                        - {$aLang.action.admin.active_skin}
                    </h3>
                </div>
                <div class="b-wbox-content -box">
                    {if $oActiveSkin->GetPreviewUrl()}
                        <img src="{$oActiveSkin->GetPreviewUrl()}" class="b-skin-screenshot" alt=""/>
                    {else}
                        <div class="b-skin-screenshot"></div>
                    {/if}
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
        <div class="span6">
            <div class="b-wbox">
                <div class="b-wbox-header">
                    <span class="icon"><i class="icon-chevron-left"></i></span>

                    <div class="b-wbox-header-title">{$aLang.action.admin.skin_settings} {$oActiveSkin->GetName()}</div>
                </div>
                <div class="b-wbox-content -box nopadding">
                    <form class="form-horizontal uniform" action="" method="post">
                        <input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}"/>
                        <input type="hidden" name="return_url" value="{$PATH_WEB_CURRENT|escape:'html'}"/>
                        {$aThemes=$oActiveSkin->GetThemes()}
                        {if $aThemes}
                            <div class="control-group">
                                <label class="control-label">{$aLang.action.admin.skin_themes}</label>

                                <div class="controls">
                                    {foreach $aThemes as $aTheme}
                                        <label>
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
                        <div class="form-actions">
                            <button class="btn btn-primary pull-right">{$aLang.action.admin.save}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    {/if}
    <div class="span12">
        <div class="b-wbox">
            <div class="b-wbox-header">
                <div class="b-wbox-header-title">{$aLang.action.admin.available_skins}</div>
            </div>
        </div>
    </div>
    <div class="row-fluid">
        {foreach $aSkins as $oSkin}
            <div class="span6">
                <div class="b-wbox">
                    <div class="b-wbox-header">
                        {if $oSkin->GetType() == 'adminpanel'}
                            <span class="icon tip-top" title="Skin for Adminpanel"><i class="icon-asterisk"></i></span>
                        {else}
                            <span class="icon tip-top" title="Skin for Site"><i class="icon-globe"></i></span>
                        {/if}

                        <h3 class="b-wbox-header-title">
                            {$oSkin->GetName()}
                            {if $oSkin->GetVersion()}v.{$oSkin->GetVersion()}{/if}
                        </h3>

                        <div class="buttons">
                            {if $oSkin->GetType() == 'adminpanel'}
                                <button class="btn btn-primary btn-mini disabled"><i class="icon-ok"></i></button>
                            {else}
                                <button class="btn btn-primary btn-mini tip-top skin_select"
                                        title="{$aLang.action.admin.activate}" id="skin-{$oSkin->GetId()}">
                                    <i class="icon-ok"></i></button>
                            {/if}
                        </div>
                    </div>
                    <div class="b-wbox-content -box">
                        {if $oSkin->GetPreviewUrl()}
                            <img src="{$oSkin->GetPreviewUrl()}" class="b-skin-screenshot" alt=""/>
                        {else}
                            <div class="b-skin-screenshot"></div>
                        {/if}
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
        {/foreach}
    </div>
    <form action="" method="post" id="form-skin-select">
        <input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}"/>
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