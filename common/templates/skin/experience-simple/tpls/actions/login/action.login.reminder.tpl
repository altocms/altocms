 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="themes/$sSkinTheme/layouts/default_light.tpl"}

{block name="layout_vars"}
    {$noSidebar=true}
{/block}

{block name="layout_content"}
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
//            $('#reminder-form').bind('submit', function () {
//                ls.user.reminder('reminder-form');
//                return false;
//            });
            $('#reminder-form-submit').attr('disabled', false);
            $('#reminder-form [data-toggle="tooltip"]').tooltip({
                placement: 'left',
                container: '#reminder-form',
                trigger: 'click'
            });
        });
    </script>
    <div class="text-center page-header">
        <h3>{$aLang.password_reminder}</h3>
    </div>
    <div class="row">
    <div class="col-xs-20 col-xs-offset-2 col-sm-12 col-sm-offset-6  col-md-10 col-md-offset-7  col-lg-10 col-lg-offset-7">

        <form action="{router page='login'}reminder/" method="POST" id="reminder-form" class="js-form-registration js-form-reminder">
            {hook run='form_reminder_begin' isPopup=true}

            <div class="form-group has-feedback">
                <div class="input-group">
                    <label for="input-reminder-mail" class="input-group-addon">
                        <i class="fa fa-envelope"></i>
                    </label>
                    <input placeholder="{$aLang.password_reminder_email}" type="text" name="mail" id="input-reminder-mail" class="form-control js-focus-in" required/>
                    <span data-toggle="tooltip" class="fa fa-question-circle js-tip-help form-control-feedback form-control-feedback-help" title="{$aLang.registration_remind_notice}"></span>
                </div>
                <small class="text-danger validate-error-hide validate-error-reminder"></small>
            </div>

            {hook run='form_reminder_end' isPopup=true}

            <br/>
            <br/>

            <a class="btn btn-light btn-normal corner-no" href="{router page='login'}">{$aLang.user_login_submit}</a>
            <a class="btn btn-light btn-normal corner-no" href="{router page='registration'}">{$aLang.user_registration}</a>
            <button type="submit" name="submit_reminder" class="btn btn-blue btn-normal corner-no" id="reminder-form-submit" disabled="disabled">
                {$aLang.user_password_reminder}
            </button>
        </form>
    </div>
    </div>
{/block}