 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
{/block}

{block name="layout_content"}

<div class="panel panel-default panel-table raised">

    <div class="panel-body">

        <div class="panel-header">{$aLang.settings_menu_invite}</div>

        <div class="bg-warning">{$aLang.settings_invite_notice} "{$aLang.settings_invite_submit}"</div>

        <br/><br/>

        <p>
            {$aLang.settings_invite_available}:
            <strong>{if E::IsAdmin()}{$aLang.settings_invite_many}{else}{$iCountInviteAvailable}{/if}</strong><br/>
            {$aLang.settings_invite_used}: <strong>{$iCountInviteUsed}</strong>
        </p>

        {hook run='settings_invite_begin'}

        <form action="" method="POST" enctype="multipart/form-data" class="wrapper-content">
            <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}" />
            {hook run='form_settings_invite_begin'}

            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon">{$aLang.settings_invite_mail}</span>
                    <input type="text" name="invite_mail" id="invite_mail" class="form-control"/>
                </div>
                <small class="control-notice">{$aLang.settings_invite_mail_notice}</small>
            </div>

            {hook run='form_settings_invite_end'}
            <button type="submit" name="submit_invite" class="btn btn-big btn-blue corner-no">{$aLang.settings_invite_submit}</button>
        </form>

        {hook run='settings_invite_end'}



    </div>

    <div class="panel-footer">
        {include file='menus/menu.settings.tpl'}
    </div>
</div>








    <div class="row">
        <div class="col-sm-6 col-lg-6">



        </div>
    </div>


{/block}
