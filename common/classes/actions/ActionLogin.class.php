<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

/**
 * Authorization and password reovery
 *
 * @package actions
 * @since   1.0
 */
class ActionLogin extends Action {
    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->SetDefaultEvent('index');

        // Отключаем отображение статистики выполнения
        R::SetIsShowStats(false);
    }

    /**
     * Регистрируем евенты
     *
     */
    protected function RegisterEvent() {

        $this->AddEvent('index', 'EventLogin');
        $this->AddEvent('exit', 'EventExit');
        $this->AddEvent('reminder', 'EventReminder');
        $this->AddEvent('reactivation', 'EventReactivation');

        $this->AddEvent('ajax-login', 'EventAjaxLogin');
        $this->AddEvent('ajax-reminder', 'EventAjaxReminder');
        $this->AddEvent('ajax-reactivation', 'EventAjaxReactivation');
    }

    /**
     * Ajax авторизация
     */
    protected function EventAjaxLogin() {

        // Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');

        // Проверяем передачу логина пароля через POST
        $sUserLogin = trim($this->GetPost('login'));
        $sUserPassword = $this->GetPost('password');
        $bRemember = $this->GetPost('remember', false) ? true : false;
        $sUrlRedirect = F::GetRequestStr('return-path');

        if (!$sUserLogin || !trim($sUserPassword)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('user_login_bad'));
            return;
        }

        $iError = null;
        // Seek user by mail or by login
        $aUserAuthData = array(
            'login' => $sUserLogin,
            'email' => $sUserLogin,
            'password' => $sUserPassword,
            'error' => &$iError,
        );
        /** @var ModuleUser_EntityUser $oUser */
        $oUser = E::ModuleUser()->GetUserAuthorization($aUserAuthData);
        if ($oUser) {
            if ($iError) {
                switch($iError) {
                    case ModuleUser::USER_AUTH_ERR_NOT_ACTIVATED:
                        $sErrorMessage = E::ModuleLang()->Get(
                            'user_not_activated',
                            array('reactivation_path' => R::GetPath('login') . 'reactivation')
                        );
                        break;
                    case ModuleUser::USER_AUTH_ERR_IP_BANNED:
                        $sErrorMessage = E::ModuleLang()->Get('user_ip_banned');
                        break;
                    case ModuleUser::USER_AUTH_ERR_BANNED_DATE:
                        $sErrorMessage = E::ModuleLang()->Get('user_banned_before', array('date' => $oUser->GetBanLine()));
                        break;
                    case ModuleUser::USER_AUTH_ERR_BANNED_UNLIM:
                        $sErrorMessage = E::ModuleLang()->Get('user_banned_unlim');
                        break;
                    default:
                        $sErrorMessage = E::ModuleLang()->Get('user_login_bad');
                }
                E::ModuleMessage()->AddErrorSingle($sErrorMessage);
                return;
            } else {
                // Авторизуем
                E::ModuleUser()->Authorization($oUser, $bRemember);

                // Определяем редирект
                //$sUrl = Config::Get('module.user.redirect_after_login');
                if (!$sUrlRedirect) {
                    $sUrlRedirect = C::Get('path.root.url');
                }

                E::ModuleViewer()->AssignAjax('sUrlRedirect', $sUrlRedirect);
                return;
            }
        }

        E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('user_login_bad'));
    }

    /**
     * Повторный запрос активации
     */
    protected function EventReactivation() {

        if (E::ModuleUser()->GetUserCurrent()) {
            R::Location(Config::Get('path.root.url') . '/');
        }

        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('reactivation'));
    }

    /**
     *  Ajax повторной активации
     */
    protected function EventAjaxReactivation() {

        E::ModuleViewer()->SetResponseAjax('json');

        /** @var ModuleUser_EntityUser $oUser */
        if ((F::CheckVal(F::GetRequestStr('mail'), 'mail') && $oUser = E::ModuleUser()->GetUserByMail(F::GetRequestStr('mail')))) {
            if ($oUser->getActivate()) {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('registration_activate_error_reactivate'));
                return;
            } else {
                $oUser->setActivationKey(F::RandomStr());
                if (E::ModuleUser()->Update($oUser)) {
                    E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('reactivation_send_link'));
                    E::ModuleNotify()->SendReactivationCode($oUser);
                    return;
                }
            }
        }

        E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('password_reminder_bad_email'));
    }

    /**
     * Обрабатываем процесс залогинивания
     * По факту только отображение шаблона, дальше вступает в дело Ajax
     *
     */
    protected function EventLogin() {

        // Если уже авторизирован
        if (E::ModuleUser()->GetUserCurrent()) {
            R::Location(Config::Get('path.root.url') . '/');
        }
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('login'));
    }

    /**
     * Обрабатываем процесс разлогинивания
     *
     */
    protected function EventExit() {

        E::ModuleSecurity()->ValidateSendForm();
        E::ModuleUser()->Logout();

        $iShowTime = Config::Val('module.user.logout.show_exit', 3);
        $sRedirect = Config::Get('module.user.logout.redirect');
        if (!$sRedirect) {
            if (isset($_SERVER['HTTP_REFERER']) && F::File_IsLocalUrl($_SERVER['HTTP_REFERER'])) {
                $sRedirect = $_SERVER['HTTP_REFERER'];
            }
        }

        /**
         * issue #104, {@see https://github.com/altocms/altocms/issues/104}
         * Установим в lgp (last_good_page) хэш имени страницы с постфиксом "logout". Такая
         * кука будет означать, что на этой странице пользователь вышел с сайта. Время 60 -
         * заранее достаточное время, что бы произошел редирект на страницу HTTP_REFERER. Если
         * же эта страница выпадет в 404 то в экшене ActionError уйдем на главную, поскольку
         * эта страница недоступна стала после выхода с сайта, а до этого была вполне ничего.
         */

        if ($iShowTime) {
            $sUrl = F::RealUrl($sRedirect);
            $sReferrer = Config::Get('path.root.web'). R::GetAction() . "/" . R::GetActionEvent() .'/?security_key=' . F::GetRequest('security_key', '');
            E::ModuleSession()->SetCookie('lgp', md5($sReferrer . 'logout'), 60);
            E::ModuleViewer()->SetHtmlHeadTag('meta', array('http-equiv' => 'Refresh', 'Content' => $iShowTime . '; url=' . $sUrl));
        } elseif ($sRedirect) {
            // Если установлена пользовтаельская страница выхода, то считаем,
            // что она без ошибки и смело не нее редиректим, в других случаях
            // возможна 404
            if (!Config::Get('module.user.logout.redirect')) {
                E::ModuleSession()->SetCookie('lgp', md5(F::RealUrl($sRedirect) . 'logout'), 60);
            }
            R::Location($sRedirect);
            exit;
        } else {
            // E::ModuleViewer()->Assign('bRefreshToHome', true);
            // Время показа страницы выхода не задано, поэтому просто редирект
            R::Location(Config::Get('path.root.web'));
            exit;
        }
    }

    /**
     * Ajax запрос на восстановление пароля
     */
    protected function EventAjaxReminder() {

        // Устанвливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');

        $this->_eventRecovery(true);
    }

    /**
     * Обработка напоминания пароля, подтверждение смены пароля
     *
     * @return string|null
     */
    protected function EventReminder() {

        if (E::IsUser()) {
            // Для авторизованного юзера восстанавливать нечего
            Router::Location('/');
        } else {
            // Устанавливаем title страницы
            E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('password_reminder'));

            $this->_eventRecovery(false);
        }
    }

    protected function _eventRecovery($bAjax = false) {

        if ($this->IsPost()) {
            // Was POST request
            $sEmail = F::GetRequestStr('mail');

            // Пользователь с таким емайлом существует?
            if ($sEmail && (F::CheckVal($sEmail, 'mail'))) {
                if ($this->_eventRecoveryRequest($sEmail)) {
                    if (!$bAjax) {
                        E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('password_reminder_send_link'));
                    }
                    return;
                }
            }
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('password_reminder_bad_email'), E::ModuleLang()->Get('error'));
        } elseif ($sRecoveryCode = $this->GetParam(0)) {
            // Was recovery code in GET
            if (F::CheckVal($sRecoveryCode, 'md5')) {

                // Проверка кода подтверждения
                if ($this->_eventRecoverySend($sRecoveryCode)) {
                    return null;
                }
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('password_reminder_bad_code_txt'), E::ModuleLang()->Get('password_reminder_bad_code'));
                if (!$bAjax) {
                    return R::Action('error');
                }
                return;
            }
        }
    }

    protected function _eventRecoveryRequest($sMail) {

        if ($oUser = E::ModuleUser()->GetUserByMail($sMail)) {

            // Формируем и отправляем ссылку на смену пароля
            /** @var ModuleUser_EntityReminder $oReminder */
            $oReminder = E::GetEntity('User_Reminder');
            $oReminder->setCode(F::RandomStr(32));
            $oReminder->setDateAdd(F::Now());
            $oReminder->setDateExpire(date('Y-m-d H:i:s', time() + Config::Val('module.user.pass_recovery_delay', 60 * 60 * 24 * 7)));
            $oReminder->setDateUsed(null);
            $oReminder->setIsUsed(0);
            $oReminder->setUserId($oUser->getId());
            if (E::ModuleUser()->AddReminder($oReminder)) {
                E::ModuleNotify()->SendReminderCode($oUser, $oReminder);
                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('password_reminder_send_link'));
                return true;
            }
        }
        return false;
    }

    protected function _eventRecoverySend($sRecoveryCode) {

        /** @var ModuleUser_EntityReminder $oReminder */
        if ($oReminder = E::ModuleUser()->GetReminderByCode($sRecoveryCode)) {
            /** @var ModuleUser_EntityUser $oUser */
            if ($oReminder->IsValid() && $oUser = E::ModuleUser()->GetUserById($oReminder->getUserId())) {
                $sNewPassword = F::RandomStr(7);
                $oUser->setPassword($sNewPassword, true);
                if (E::ModuleUser()->Update($oUser)) {

                    // Do logout of current user
                    E::ModuleUser()->Logout();

                    // Close all sessions of this user
                    E::ModuleUser()->CloseAllSessions($oUser);

                    $oReminder->setDateUsed(F::Now());
                    $oReminder->setIsUsed(1);
                    E::ModuleUser()->UpdateReminder($oReminder);
                    E::ModuleNotify()->SendReminderPassword($oUser, $sNewPassword);
                    $this->SetTemplateAction('reminder_confirm');

                    if (($sUrl = F::GetPost('return_url')) || ($sUrl = F::GetPost('return-path'))) {
                        E::ModuleViewer()->Assign('return-path', $sUrl);
                    }
                    return true;
                }
            }
        }
        return false;
    }

}

// EOF