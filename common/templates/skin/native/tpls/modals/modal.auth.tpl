{**
 * Модальное окно с формами входа, регистрации и напоминанием пароля
 *
 * @styles css/modals.css
 *}

{extends file='modals/_modal_base.tpl'}

{block name='modal_id'}modal-login{/block}
{block name='modal_class'}modal-login js-modal-default{/block}
{block name='modal_title'}{$aLang.user_authorization}{/block}

{block name='modal_content'}
    <ul class="nav nav-pills" data-type="tabs">
        <li><a href="#tab-pane-login" data-toggle="pill">{$aLang.user_login_submit}</a></li>
        <li><a href="#tab-pane-registration" data-toggle="pill">{$aLang.registration}</a></li>
        <li><a href="#tab-pane-reminder" data-toggle="pill">{$aLang.password_reminder}</a></li>
    </ul>
    <div data-type="tab-panes tab-content">
        <div class="tab-pane" id="tab-pane-login" data-type="tab-pane">
            {include file='forms/form.auth.login.tpl' isModal=true}
        </div>

        <div class="tab-pane" id="tab-pane-registration" data-type="tab-pane">
            {if ! Config::Get('general.reg.invite')}
                {include file='forms/form.auth.signup.tpl' isModal=true}
            {else}
                {include file='forms/form.auth.invite.tpl' isModal=true}
            {/if}
        </div>

        <div class="tab-pane" id="tab-pane-reminder" data-type="tab-pane">
            {include file='forms/form.auth.recovery.tpl' isModal=true}
        </div>
    </div>
    <script>
        $('#modal-login').on('shown.bs.modal', function () {
            $(this).find('.tab-pane.active input[type=text]:first').focus();
        });
        $('#modal-login [data-toggle=pill]').on('shown.bs.tab', function () {
            var target = $(this).attr('href');
            if (target) {
                $(target).find('form input[type=text]:first').focus();
            }
        })
    </script>
{/block}

{block name='modal_footer'}{/block}