{extends file="themes/$sSkinTheme/layouts/default_light.tpl"}

{block name="layout_vars"}
    {$noSidebar=true}
{/block}

{block name="layout_content"}
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('#reminder-form').bind('submit', function () {
                ls.user.reminder('reminder-form');
                return false;
            });
            $('#reminder-form-submit').attr('disabled', false);
        });
    </script>
    <div class="text-center page-header">
        <h3>{$aLang.password_reminder}</h3>
    </div>
    <form action="{router page='login'}reminder/" method="POST" id="reminder-form">
        <div class="form-group">
            <label for="reminder-mail">{$aLang.password_reminder_email}</label>
            <input type="text" name="mail" id="reminder-mail" class="form-control"/>

            <p class="help-block">
                <small class="text-danger validate-error-hide validate-error-reminder"></small>
            </p>
        </div>

        <p>
            {$aLang.user_password_reminder_note}
        </p>

        <a class="btn btn-default" href="{router page='login'}">{$aLang.user_login_submit}</a>
        <a class="btn btn-default" href="{router page='registration'}">{$aLang.user_registration}</a>
        <button type="submit" name="submit_reminder" class="btn btn-success" id="reminder-form-submit" disabled="disabled">
            {$aLang.user_password_reminder}
        </button>
    </form>
{/block}