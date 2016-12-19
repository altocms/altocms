{if !E::IsUser()}
    <div class="modal fade in" id="modal-auth">
        <div class="modal-dialog">
            <div class="modal-content">

                <header class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">{$aLang.user_authorization}</h4>
                </header>

                <div class="modal-body">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#" data-toggle="tab" data-target=".js-pane-login" class="js-tab-login">{$aLang.user_login_submit}</a></li>
                        {if !Config::Get('general.reg.invite')}
                            <li><a href="#" data-toggle="tab" data-target=".js-pane-registration" class="js-tab-registration">{$aLang.registration}</a></li>
                        {else}
                            <li><a href="{router page='registration'}">{$aLang.registration}</a></li>
                        {/if}
                        <li><a href="#" data-toggle="tab" data-target=".js-pane-reminder" class="js-tab-reminder">{$aLang.password_reminder}</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active js-pane-login">
                            {hook run='pane_login_begin'}

                            <form action="{router page='login'}" method="post" class="js-form-login">
                                {hook run='form_login_begin'}

                                <div class="form-group">
                                    <label for="input-login">{$aLang.user_login}</label>
                                    <input type="text" name="login" id="input-login" class="form-control js-focus-in" required>
                                </div>

                                <div class="form-group">
                                    <label for="input-password">{$aLang.user_password}</label>
                                    <input type="password" name="password" id="input-password" class="form-control" required>

                                    <p class="help-block">
                                        <small class="text-danger validate-error-hide validate-error-login"></small>
                                    </p>
                                </div>

                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember" checked> {$aLang.user_login_remember}
                                    </label>
                                </div>

                                {hook run='form_login_end'}

                                <input type="hidden" name="return-path" value="{$PATH_WEB_CURRENT|escape:'html'}">
                                <button type="submit" name="submit_login" class="btn btn-success js-form-login-submit"
                                        disabled="disabled">{$aLang.user_login_submit}</button>

                            </form>

                            {hook run='pane_login_end'}
                        </div>

                        {if !Config::Get('general.reg.invite')}
                        <div class="tab-pane js-pane-registration">
                            {hook run='pane_registration_begin' isPopup=true}
                            <form action="{router page='registration'}" method="post" class="js-form-registration">
                                {hook run='form_registration_begin' isPopup=true}

                                <div class="form-group">
                                    <label for="input-registration-login">{$aLang.registration_login}</label>
                                    <span class="glyphicon glyphicon-question-sign text-muted js-tip-help"
                                          title="{$aLang.registration_login_notice}"></span>
                                    <span class="glyphicon glyphicon-ok text-success validate-ok-field-login"
                                          style="display: none"></span>
                                    <input type="text" name="login" id="input-registration-login"
                                           value="{$_aRequest.login}" class="form-control js-ajax-validate js-focus-in" required/>

                                    <p class="help-block">
                                        <small class="text-danger validate-error-hide validate-error-field-login"></small>
                                    </p>
                                </div>

                                <div class="form-group">
                                    <label for="input-registration-mail">{$aLang.registration_mail}</label>
                                    <span class="glyphicon glyphicon-question-sign text-muted js-tip-help" title="{$aLang.registration_mail_notice}"></span>
                                    <span class="glyphicon glyphicon-ok text-success validate-ok-field-mail" style="display: none"></span>
                                    <input type="text" name="mail" id="input-registration-mail" value="{$_aRequest.mail}" class="form-control js-ajax-validate" required/>

                                    <p class="help-block">
                                        <small class="text-danger validate-error-hide validate-error-field-mail"></small>
                                    </p>
                                </div>

                                <div class="form-group">
                                    <label for="input-registration-password">{$aLang.registration_password}</label>
                                    <span class="glyphicon glyphicon-question-sign text-muted js-tip-help" title="{$aLang.registration_password_notice}"></span>
                                    <span class="glyphicon glyphicon-ok text-success validate-ok-field-password" style="display: none"></span>
                                    <input type="password" name="password" id="input-registration-password" value="" class="form-control js-ajax-validate" required/>

                                    <p class="help-block">
                                        <small class="text-danger validate-error-hide validate-error-field-password"></small>
                                    </p>
                                </div>

                                <div class="form-group">
                                    <label for="input-registration-password-confirm">{$aLang.registration_password_retry}</label>
                                    <span class="glyphicon glyphicon-ok text-success validate-ok-field-password_confirm" style="display: none"></span>
                                    <input type="password" value="" id="input-registration-password-confirm" name="password_confirm" class="form-control js-ajax-validate" required/>

                                    <p class="help-block">
                                        <small class="text-danger validate-error-hide validate-error-field-password_confirm"></small>
                                    </p>
                                </div>

                                {hook run="registration_captcha" type="registration"}

                                {hook run='form_registration_end' isPopup=true}

                                <input type="hidden" name="return-path" value="{$PATH_WEB_CURRENT|escape:'html'}">
                                <button type="submit" name="submit_register" class="btn btn-success js-form-registration-submit"
                                        disabled="disabled">{$aLang.registration_submit}</button>

                            </form>
                            {hook run='pane_registration_end' isPopup=true}
                        </div>
                        {/if}

                        <div class="tab-pane js-pane-reminder">
                            {hook run='pane_reminder_begin' isPopup=true}
                            <form action="{router page='login'}reminder/" method="POST" class="js-form-reminder">
                                {hook run='form_reminder_begin' isPopup=true}
                                <div class="form-group">
                                    <label for="input-reminder-mail">{$aLang.password_reminder_email}</label>
                                    <input type="text" name="mail" id="input-reminder-mail" class="form-control js-focus-in" required/>

                                    <p class="help-block">
                                        <small class="text-danger validate-error-hide validate-error-reminder"></small>
                                    </p>
                                </div>

                                {hook run='form_reminder_end' isPopup=true}

                                <button type="submit" name="submit_reminder" class="btn btn-success js-form-reminder-submit"
                                        disabled="disabled">{$aLang.password_reminder_submit}</button>

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
