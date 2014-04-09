{extends file="_index.tpl"}

{block name="layout_content"}

{include file='menus/menu.settings.tpl'}

<div class="alert alert-info">{$aLang.settings_invite_notice} "{$aLang.settings_invite_submit}"</div>

{hook run='settings_invite_begin'}

<form action="" method="POST" enctype="multipart/form-data" class="wrapper-content">
    {hook run='form_settings_invite_begin'}
    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}" />

    <p>
        {$aLang.settings_invite_available}:
        <strong>{if E::IsAdmin()}{$aLang.settings_invite_many}{else}{$iCountInviteAvailable}{/if}</strong><br/>
        {$aLang.settings_invite_used}: <strong>{$iCountInviteUsed}</strong>
    </p>

    <div class="row">
        <div class="col-sm-6 col-lg-6">

            <div class="form-group">
                <label for="invite_mail">{$aLang.settings_invite_mail}</label>
                <input type="text" name="invite_mail" id="invite_mail" class="form-control"/>

                <p class="help-block">
                    <small>{$aLang.settings_invite_mail_notice}</small>
                </p>
            </div>

        </div>
    </div>

    {hook run='form_settings_invite_end'}
    <br />

    <button type="submit" name="submit_invite" class="btn btn-success" />{$aLang.settings_invite_submit}</button>
</form>

{hook run='settings_invite_end'}

{/block}
