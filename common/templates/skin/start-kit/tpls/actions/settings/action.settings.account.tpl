{extends file="_index.tpl"}

{block name="layout_content"}

{include file='menus/menu.settings.tpl'}

{hook run='settings_account_begin'}

<form method="post" enctype="multipart/form-data" class="wrapper-content">
    {hook run='form_settings_account_begin'}

    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}">

    <fieldset>
        <legend>{$aLang.settings_account}</legend>

        <div class="row">
            <div class="col-sm-6 col-lg-6">

                <div class="form-group">
                    <label for="mail">{$aLang.settings_profile_mail}</label>
                    <input type="email" name="mail" id="mail" value="{E::User()->getMail()|escape:'html'}"
                           class="form-control" required/>

                    <p class="help-block">
                        <small>{$aLang.settings_profile_mail_notice}</small>
                    </p>
                </div>

            </div>
        </div>
    </fieldset>
    <br />

    <fieldset>
        <legend>{$aLang.settings_account_password}</legend>

        <div class="row">
            <div class="col-sm-6 col-lg-6">
                <p class="help-block">{$aLang.settings_account_password_notice}</p>

                <div class="form-group">
                    <label for="password_now">{$aLang.settings_profile_password_current}</label>
                    <input type="password" name="password_now" id="password_now" value="" class="form-control"/>
                </div>

                <div class="form-group">
                    <label for="password">{$aLang.settings_profile_password_new}</label>
                    <input type="password" id="password" name="password" value="" class="form-control"/>
                </div>

                <div class="form-group">
                    <label for="password_confirm">{$aLang.settings_profile_password_confirm}</label>
                    <input type="password" id="password_confirm" name="password_confirm" value="" class="form-control"/>
                </div>
            </div>
        </div>
    </fieldset>
    <br />

    {hook run='form_settings_account_end'}

    <button type="submit" name="submit_account_edit" class="btn btn-success" />{$aLang.settings_profile_submit}</button>
</form>

{hook run='settings_account_end'}

{/block}
