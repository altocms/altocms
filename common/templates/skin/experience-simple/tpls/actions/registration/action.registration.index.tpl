 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="themes/$sSkinTheme/layouts/default_light.tpl"}

{block name="layout_vars"}
    {$noSidebar=true}
{/block}

{block name="layout_content"}
    <script type="text/javascript">
        jQuery(function ($) {

            // --- //
            $('.js-form-registration input.js-ajax-validate').blur(function (e) {
                var params = { };
                var fieldName = $(e.target).attr('name');
                var fieldValue = $(e.target).val();
                var form = $(e.target).parents('form').first();

                if (fieldName == 'password_confirm') {
                    params['password'] = $('#input-registration-password').val();
                }
                if (fieldName == 'password') {
                    params['password'] = $('#input-registration-password').val();
                    if ($('#input-registration-password-confirm').val()) {
                        ls.user.validateRegistrationField(form,  'password_confirm', $('#input-registration-password-confirm').val(), { password: fieldValue });
                    }
                }
                ls.user.validateRegistrationField(form, fieldName, fieldValue, params);
            });
            $('.js-form-registration-submit').prop('disabled', false);

            $('.captcha-image').prop('src', ls.routerUrl('captcha') + '?n=' + Math.random());

            $('#registration-form [data-toggle="tooltip"]').tooltip({
                placement: 'left',
                container: '.js-form-registration',
                trigger: 'click'
            });
        });

    </script>
    <script>
        $(function(){

        })
    </script>
    <div class="text-center page-header">
        <h3>{$aLang.registration}</h3>
    </div>
    {hook run='registration_begin'}

<div class="row">
    <div class="col-xs-20 col-xs-offset-2 col-sm-12 col-sm-offset-6  col-md-10 col-md-offset-7  col-lg-10 col-lg-offset-7">

    <form action="{router page='registration'}" method="post" id="registration-form" class="js-form-registration">

        {hook run='form_registration_begin' isPopup=true}


        <div class="form-group has-feedback">
            <div class="input-group">
                <label for="input-registration-login" class="input-group-addon"><i class="fa fa-user"></i></label>
                <input placeholder="{$aLang.registration_login}" type="text" name="login" id="input-registration-login" value="{$_aRequest.login}" class="form-control js-ajax-validate js-focus-in" required/>
                <span class="fa fa-check validate-ok-field-login form-control-feedback form-control-feedback-ok" style="display: none"></span>
                <span  data-toggle="tooltip" data-placement="left" class="fa fa-question-circle js-tip-help form-control-feedback form-control-feedback-help" title="{$aLang.registration_login_notice}"></span>
            </div>
            <small class="text-danger validate-error-hide validate-error-field-login"></small>
        </div>

        <div class="form-group has-feedback">
            <div class="input-group">
                <label for="input-registration-mail"  class="input-group-addon"><i class="fa fa-envelope"></i></label>
                <input placeholder="{$aLang.registration_mail}" type="text" name="mail" id="input-registration-mail" value="{$_aRequest.mail}" class="form-control js-ajax-validate" required/>
                <span class="fa fa-check validate-ok-field-mail form-control-feedback form-control-feedback-ok" style="display: none"></span>
                <span data-toggle="tooltip"  class="fa fa-question-circle js-tip-help form-control-feedback form-control-feedback-help" title="{$aLang.registration_registration_mail_short}"></span>
            </div>
            <small class="text-danger validate-error-hide validate-error-field-mail"></small>
        </div>

        <div class="form-group has-feedback">
            <div class="input-group">
                <label for="input-registration-password" class="input-group-addon">
                    <i class="fa fa-lock"></i>
                </label>
                <input placeholder="{$aLang.registration_password}" type="password" name="password" id="input-registration-password" value="" class="form-control js-ajax-validate" required/>
                <span class="fa fa-check validate-ok-field-password form-control-feedback form-control-feedback-ok" style="display: none"></span>
                <span data-toggle="tooltip" class="fa fa-question-circle js-tip-help form-control-feedback form-control-feedback-help" title="{$aLang.registration_password_notice}"></span>
            </div>
            <small class="text-danger validate-error-hide validate-error-field-password"></small>
        </div>

        <div class="form-group has-feedback">
            <div class="input-group">
                <label for="input-registration-password-confirm" class="input-group-addon">
                    <i class="fa fa-lock"></i>
                </label>
                <input placeholder="{$aLang.registration_password_retry}" type="password" value="" id="input-registration-password-confirm" name="password_confirm" class="form-control js-ajax-validate" required/>
                <span class="fa fa-check validate-ok-field-password_confirm form-control-feedback form-control-feedback-ok" style="display: none"></span>
            </div>
            <small class="text-danger validate-error-hide validate-error-field-password_confirm"></small>
        </div>




        {hook run="registration_captcha" type="registration"}

        {hook run='form_registration_end' isPopup=true}

        <input type="hidden" name="return-path" value="{$PATH_WEB_CURRENT|escape:'html'}">

        <br/>
        <br/>

        <a class="btn btn-light btn-normal corner-no" href="{router page='login'}">{$aLang.user_login_submit}</a>
        <button type="submit" name="submit_register" id="registration-form-submit" class="btn btn-blue btn-normal corner-no js-form-registration-submit" disabled="disabled">
            {$aLang.registration_submit}
        </button>
        <a class="btn btn-light btn-normal corner-no" href="{router page='login'}reminder/">{$aLang.user_password_reminder}</a>

    </form>

    </div>
</div>

    {hook run='registration_end'}

{/block}
