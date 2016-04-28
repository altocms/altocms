{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="themes/$sSkinTheme/layouts/default_light.tpl"}

{block name="layout_content"}

    <script type="text/javascript">
        jQuery(document).ready(function($){
            $('#reactivation-form-submit').attr('disabled',false);
        });
    </script>

    <div class="text-center page-header">
        <h3>{$aLang.reactivation}</h3>
    </div>

    <div class="row">
        <div class="col-xs-20 col-xs-offset-2 col-sm-12 col-sm-offset-6  col-md-10 col-md-offset-7  col-lg-10 col-lg-offset-7">
            <form action="{R::GetLink("login")}reactivation/" method="POST" class="js-form-reactivation">
                <div class="form-group">

                    <div class="form-group has-feedback">
                        <div class="input-group">
                            <label for="reactivation-mail" class="input-group-addon">
                                <i class="fa fa-envelope"></i>
                            </label>
                            <input placeholder="{$aLang.password_reminder_email}" type="text" name="mail" id="reactivation-mail" class="form-control js-focus-in" required/>
                        </div>
                        <small class="text-danger validate-error-hide validate-error-reminder"></small>
                    </div>

                </div>

                <br/>
                <br/>

                <button type="submit" name="submit_reactivation" class="btn btn-blue btn-normal corner-no" id="reactivation-form-submit" disabled="disabled">{$aLang.reactivation_submit}</button>
            </form>
        </div>
    </div>

{/block}
