 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
{/block}

{block name="layout_content"}

    <div class="panel panel-default panel-table flat">

        <div class="panel-body">

            {hook run='settings_account_begin'}

            <form method="post" enctype="multipart/form-data" class="wrapper-content">
                {hook run='form_settings_account_begin'}
                {***************************************************************************************}

                <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}">

                <h2 class="panel-header">
                    {*<i class="fa fa-envelope-o"></i>&nbsp;*}
                    {$aLang.settings_account}
                </h2>

                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                        <input type="email" placeholder="{$aLang.settings_profile_mail}" name="mail" id="mail" value="{E::User()->getMail()|escape:'html'}"
                               class="form-control" required/>
                    </div>
                    <small class="control-notice">{$aLang.settings_profile_mail_notice}</small>
                </div>



                <fieldset class="settings_account_password">
                    <legend>{$aLang.settings_account_password}</legend>
                    <p class="text-muted">{$aLang.settings_account_password_notice}</p>

                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">{$aLang.settings_profile_password_current}</span>
                            <input type="password" name="password_now" id="password_now" value="" class="form-control"/>
                        </div>
                    </div>


                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">{$aLang.settings_profile_password_new}</span>
                            <input type="password" id="password" name="password" value="" class="form-control"/>
                        </div>
                    </div>


                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">{$aLang.settings_profile_password_confirm}</span>
                            <input type="password" id="password_confirm" name="password_confirm" value="" class="form-control"/>
                        </div>
                    </div>
                </fieldset>
                <br />



                {***************************************************************************************}
                {hook run='form_settings_account_end'}
                <button type="submit" name="submit_account_edit" class="btn btn-blue btn-normal corner-no">
                    {$aLang.settings_profile_submit}
                </button>
            </form>

            {hook run='settings_account_end'}

        </div>

        <div class="panel-footer">
            {include file='menus/menu.settings.tpl'}
        </div>
    </div>

{/block}
