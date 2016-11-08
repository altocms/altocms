 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="themes/$sSkinTheme/layouts/default_light.tpl"}

{block name="layout_vars"}
    {$noSidebar=true}
{/block}

{block name="layout_content"}

    <div class="text-center page-header">
        <h3>{$aLang.user_authorization}</h3>
    </div>

    {hook run='login_begin'}

    <div class="row">
        <div class="col-xs-20 col-xs-offset-2 col-sm-12 col-sm-offset-6  col-md-10 col-md-offset-7  col-lg-10 col-lg-offset-7">
            <form id="login-form" action="{router page='login'}" method="post" class="js-form-login">
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

                <div class="checkbox pal0">
                    <label>
                        <input type="checkbox" name="remember" checked> {$aLang.user_login_remember}
                    </label>
                </div>

                {hook run='form_login_end'}

                <br/>
                <br/>

                <input type="submit"
                       id="login-form-submit"
                       name="submit_login"
                       class="btn btn-blue btn-big corner-no js-form-login-submit"
                       value="{$aLang.user_login_submit_on_site}">
                <a class="btn btn-light btn-big corner-no" href="{router page='registration'}">{$aLang.user_registration}</a>
                <a class="btn btn-light btn-big corner-no" href="{router page='login'}reminder/">{$aLang.user_password_reminder}</a>

            </form>
        </div>
    </div>




    {if Config::Get('general.reg.invite')}
        <br/>
        <br/>
        <br/>
<div class="row">
    <div class="col-xs-20 col-xs-offset-2 col-sm-12 col-sm-offset-6  col-md-10 col-md-offset-7  col-lg-10 col-lg-offset-7">
        <form action="{router page='registration'}invite/" method="POST">
            <div class="text-center page-header">
                <h3>{$aLang.registration_invite}</h3>
            </div>

            <div class="form-group">
                <div class="input-group">
                    <label for="input-login" class="input-group-addon"><i class="fa fa-paw"></i></label>
                    <input type="text" placeholder="{$aLang.registration_invite_code}" name="login" class="invite_code form-control" required>
                </div>
            </div>


            <input type="submit" name="submit_invite" value="{$aLang.registration_invite_check}"
                   class="btn btn-blue btn-big corner-no"/>
        </form>
    </div>
</div>

    {/if}

    {hook run='login_end'}

{/block}
