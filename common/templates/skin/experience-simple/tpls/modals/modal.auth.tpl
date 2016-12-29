 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if !E::IsUser()}
    <div class="modal fade in" id="modal-auth">
        <div class="modal-dialog">
            <div class="modal-content">

                <header class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">{$aLang.user_authorization}</h4>
                </header>

                <div class="modal-body">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#" data-toggle="tab" data-target=".js-pane-login" class="js-tab-login">{$aLang.user_login_submit}</a></li>
                        {if !Config::Get('general.reg.invite')}
                            <li><a href="#" data-toggle="tab" data-target=".js-pane-registration" class="js-tab-registration link link-lead link-blue link-clear">{$aLang.registration}</a></li>
                        {else}
                            <li><a class="link link-lead link-blue link-clear" href="{router page='registration'}">{$aLang.registration}</a></li>
                        {/if}
                        <li>
                            <a href="#" data-toggle="tab" data-target=".js-pane-reminder" class="js-tab-reminder link link-lead link-blue link-clear">{$aLang.password_reminder}</a>
                        </li>
                    </ul>

                    <br/>

                    {* ВХОД НА САЙТ *}
                    <div class="tab-content">
                        <div class="tab-pane active js-pane-login">
                            {hook run='pane_login_begin'}

                            <form action="{router page='login'}" method="post" class="js-form-login">
                                {hook run='form_login_begin'}

                                <div class="form-group">
                                    <div class="input-group">
                                        <label for="input-login" class="input-group-addon"><i class="fa fa-user"></i></label>
                                        <input type="text" placeholder="{$aLang.user_login}" name="login" id="input-login" class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-group">
                                        <label for="input-password" class="input-group-addon"><i class="fa fa-lock"></i></label>
                                        <input type="password" placeholder="{$aLang.user_password}" name="password" id="input-password" class="form-control" required>
                                    </div>
                                    <small class="text-danger validate-error-hide validate-error-login"></small>
                                </div>

                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember" checked> {$aLang.user_login_remember}
                                    </label>
                                </div>

                                {hook run='form_login_end'}

                                <input type="hidden" name="return-path" value="{$PATH_WEB_CURRENT|escape:'html'}">
                                <input type="submit" name="submit_login"
                                       class="btn btn-blue btn-normal corner-no js-form-login-submit"
                                       value="{$aLang.user_login_submit_on_site}"
                                       disabled="disabled">

                            </form>

                            {hook run='pane_login_end'}
                        </div>

                        {if !Config::Get('general.reg.invite')}
                        <div class="tab-pane js-pane-registration">
                            {hook run='pane_registration_begin' isPopup=true}
                            <form action="{router page='registration'}" method="post" class="js-form-registration">

                                {hook run='form_registration_begin' isPopup=true}

                                <script>
                                    $(function(){
                                        $('.js-tip-help').tooltip({
                                            placement: 'left',
                                            container: '#modal-auth',
                                            trigger: 'click'
                                        });
                                    })
                                </script>

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
                                        <span class="fa fa-question-circle js-tip-help form-control-feedback form-control-feedback-help" title="{$aLang.registration_registration_mail_short}"></span>
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
                                        <span class="fa fa-question-circle js-tip-help form-control-feedback form-control-feedback-help" title="{$aLang.registration_password_notice}"></span>
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
                                <input type="submit" name="submit_register" class="btn btn-blue btn-normal corner-no js-form-registration-submit"
                                       value="{$aLang.registration_submit}"
                                        disabled="disabled">

                            </form>
                            {hook run='pane_registration_end' isPopup=true}
                        </div>
                        {/if}

                        <div class="tab-pane js-pane-reminder">
                            {hook run='pane_reminder_begin' isPopup=true}
                            <form action="{router page='login'}reminder/" method="POST" class="js-form-reminder">
                                {hook run='form_reminder_begin' isPopup=true}

                                <div class="form-group has-feedback">
                                    <div class="input-group">
                                        <label for="input-reminder-mail" class="input-group-addon">
                                            <i class="fa fa-envelope"></i>
                                        </label>
                                        <input placeholder="{$aLang.password_reminder_email}" type="text" name="mail" id="input-reminder-mail" class="form-control js-focus-in" required/>
                                        <span class="fa fa-question-circle js-tip-help form-control-feedback form-control-feedback-help" title="{$aLang.registration_remind_notice}"></span>
                                    </div>
                                    <small class="text-danger validate-error-hide validate-error-reminder"></small>
                                </div>

                                {hook run='form_reminder_end' isPopup=true}

                                <input type="submit" name="submit_reminder" class="btn btn-blue btn-normal corner-no  js-form-reminder-submit"
                                       value="{$aLang.password_reminder_submit}"
                                        disabled="disabled">

                            </form>
                            {hook run='pane_reminder_end' isPopup=true}
                        </div>
                    </div>

                </div><!-- /.modal-body -->

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <script type="text/javascript">
        jQuery(function ($) {
            var modalAuth = $('#modal-auth');
            var selectFirstInput = function() {
                modalAuth.find('.tab-pane.active input[type=text]:first:visible').focus();
                if (modalAuth.data('first') != 1) {
                    $('.captcha-image').attr('src', "{router page='captcha'}?n="+Math.random());
                    modalAuth.attr('data-first', 1);
                }
            };
            var ajaxValidate = function(target) {
                var field = $(target),
                    fieldName = field.attr('name'),
                    fieldValue = field.val(),
                    form = field.parents('form').first(),
                    params = { };

                if (fieldName == 'password_confirm') {
                    params['password'] = $('#input-registration-password').val();
                }
                if (fieldName == 'password') {
                    var passwordConfirm = $('#input-registration-password-confirm').val();
                    params['password'] = $('#input-registration-password').val();
                    if (passwordConfirm) {
                        ls.user.validateRegistrationField(form,  'password_confirm', passwordConfirm, { password: fieldValue });
                    }
                }
                ls.user.validateRegistrationField(form, fieldName, fieldValue, params);
            };
            modalAuth.on('shown.bs.modal', selectFirstInput);
            modalAuth.find('[data-toggle=tab]').on('shown.bs.tab', selectFirstInput);

            // --- //
            $('.js-form-login-submit').prop('disabled', false);

            // --- //
            $('.js-form-registration input.js-ajax-validate').blur(function (e) {
                ajaxValidate(e.target);
            });
            $('.js-form-registration-submit').prop('disabled', false);

            // -- //
            $('.js-form-reminder-submit').prop('disabled', false);
        });
    </script>

{/if}
