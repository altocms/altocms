{extends file="themes/$sSkinTheme/layouts/default_light.tpl"}

{block name="layout_vars"}
    {$noSidebar=true}
{/block}

{block name="layout_content"}
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('#reactivation-form-submit').attr('disabled', false);
        });
    </script>

    <div class="text-center page-header">
        <h3>{$aLang.reactivation}</h3>
    </div>

    <form action="{router page='login'}reactivation/" method="POST" class="js-form-reactivation">
        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
        <div class="form-group">
            <label for="reactivation-mail">{$aLang.password_reminder_email}</label>
            <input type="text" name="mail" id="reactivation-mail" class="form-control"/>

            <p class="help-block">
                <small class="text-danger validate-error-hide validate-error-reactivation"></small>
            </p>
        </div>

        <button type="submit" name="submit_reactivation" class="btn btn-success" id="reactivation-form-submit"
                disabled="disabled">{$aLang.reactivation_submit}</button>
    </form>
{/block}