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
        Router::SetIsShowStats(false);
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
        $this->Viewer_SetResponseAjax('json');

        // Проверяем передачу логина пароля через POST
        $sUserLogin = trim($this->GetPost('login'));
        $sUserPassword = $this->GetPost('password');
        if (!$sUserLogin || !trim($sUserPassword)) {
            $this->Message_AddErrorSingle($this->Lang_Get('user_login_bad'));
            return;
        }

        // Seek user by mail or by login
        /** @var ModuleUser_EntityUser $oUser */
        if ((F::CheckVal($sUserLogin, 'mail') && $oUser = $this->User_GetUserByMail($sUserLogin)) || ($oUser = $this->User_GetUserByLogin($sUserLogin))) {
            // Не забанен ли юзер
            if ($oUser->IsBanned()) {
                if ($oUser->IsBannedByIp()) {
                    $this->Message_AddErrorSingle($this->Lang_Get('user_ip_banned'));
                    return;
                } elseif ($oUser->GetBanLine()) {
                    $this->Message_AddErrorSingle(
                        $this->Lang_Get('user_banned_before', array('date' => $oUser->GetBanLine()))
                    );
                    return;
                } else {
                    $this->Message_AddErrorSingle($this->Lang_Get('user_banned_unlim'));
                    return;
                }
            }
            // Check password
            if ($this->User_CheckPassword($oUser, $sUserPassword)) {
                if (!$oUser->getActivate()) {
                    $this->Message_AddErrorSingle(
                        $this->Lang_Get(
                            'user_not_activated',
                            array('reactivation_path' => Router::GetPath('login') . 'reactivation')
                        )
                    );
                    return;
                }
                $bRemember = F::GetRequest('remember', false) ? true : false;

                // Авторизуем
                $this->User_Authorization($oUser, $bRemember);

                // Определяем редирект
                //$sUrl = Config::Get('module.user.redirect_after_login');
                $sUrl = Config::Get('path.root.url');
                if (F::GetRequestStr('return-path')) {
                    $sUrl = F::GetRequestStr('return-path');
                }
                $this->Viewer_AssignAjax('sUrlRedirect', $sUrl ? $sUrl : Config::Get('path.root.url'));
                return;
            }
        }
        $this->Message_AddErrorSingle($this->Lang_Get('user_login_bad'));
    }

    /**
     * Повторный запрос активации
     */
    protected function EventReactivation() {

        if ($this->User_GetUserCurrent()) {
            Router::Location(Config::Get('path.root.url') . '/');
        }

        $this->Viewer_AddHtmlTitle($this->Lang_Get('reactivation'));
    }

    /**
     *  Ajax повторной активации
     */
    protected function EventAjaxReactivation() {

        $this->Viewer_SetResponseAjax('json');

        /** @var ModuleUser_EntityUser $oUser */
        if ((F::CheckVal(F::GetRequestStr('mail'), 'mail') && $oUser = $this->User_GetUserByMail(F::GetRequestStr('mail')))) {
            if ($oUser->getActivate()) {
                $this->Message_AddErrorSingle($this->Lang_Get('registration_activate_error_reactivate'));
                return;
            } else {
                $oUser->setActivateKey(F::RandomStr());
                if ($this->User_Update($oUser)) {
                    $this->Message_AddNotice($this->Lang_Get('reactivation_send_link'));
                    $this->Notify_SendReactivationCode($oUser);
                    return;
                }
            }
        }

        $this->Message_AddErrorSingle($this->Lang_Get('password_reminder_bad_email'));
    }

    /**
     * Обрабатываем процесс залогинивания
     * По факту только отображение шаблона, дальше вступает в дело Ajax
     *
     */
    protected function EventLogin() {

        // Если уже авторизирован
        if ($this->User_GetUserCurrent()) {
            Router::Location(Config::Get('path.root.url') . '/');
        }
        $this->Viewer_AddHtmlTitle($this->Lang_Get('login'));
    }

    /**
     * Обрабатываем процесс разлогинивания
     *
     */
    protected function EventExit() {

        $this->Security_ValidateSendForm();
        $this->User_Logout();

        $iShowTime = Config::Val('module.user.logout.show_exit', 3);
        $sRedirect = Config::Get('module.user.logout.redirect');
        if (!$sRedirect) {
            if (isset($_SERVER['HTTP_REFERER']) && F::File_IsLocalUrl($_SERVER['HTTP_REFERER'])) {
                $sRedirect = $_SERVER['HTTP_REFERER'];
            }
        }
        if ($iShowTime) {
            $sUrl = F::RealUrl($sRedirect);
            $this->Viewer_SetHtmlHeadTag('meta', array('http-equiv' => 'Refresh', 'Content' => $iShowTime . '; url=' . $sUrl));
        } elseif ($sRedirect) {
            Router::Location($sRedirect);
            exit;
        } else {
            $this->Viewer_Assign('bRefreshToHome', true);
        }
    }

    /**
     * Ajax запрос на восстановление пароля
     */
    protected function EventAjaxReminder() {

        // Устанвливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax('json');

        $this->_eventRecovery(true);
    }

    /**
     * Обработка напоминания пароля, подтверждение смены пароля
     *
     * @return string|null
     */
    protected function EventReminder() {

        // Устанавливаем title страницы
        $this->Viewer_AddHtmlTitle($this->Lang_Get('password_reminder'));

        $this->_eventRecovery(false);
    }

    protected function _eventRecovery($bAjax = false) {

        if ($this->IsPost()) {
            // Was POST request
            $sEmail = F::GetRequestStr('mail');

            // Пользователь с таким емайлом существует?
            if ($sEmail && (F::CheckVal($sEmail, 'mail'))) {
                if ($this->_eventRecoveryRequest($sEmail)) {
                    if (!$bAjax) {
                        $this->Message_AddNoticeSingle($this->Lang_Get('password_reminder_send_link'));
                    }
                    return;
                }
            }
            $this->Message_AddError($this->Lang_Get('password_reminder_bad_email'), $this->Lang_Get('error'));
        } elseif ($sRecoveryCode = $this->GetParam(0)) {
            // Was recovery code in GET
            if (F::CheckVal($sRecoveryCode, 'md5')) {

                // Проверка кода подтверждения
                if ($this->_eventRecoverySend($sRecoveryCode)) {
                    return null;
                }
                $this->Message_AddErrorSingle($this->Lang_Get('password_reminder_bad_code_txt'), $this->Lang_Get('password_reminder_bad_code'));
                if (!$bAjax) {
                    return Router::Action('error');
                }
                return;
            }
        }
    }

    protected function _eventRecoveryRequest($sMail) {

        if ($oUser = $this->User_GetUserByMail($sMail)) {

            // Формируем и отправляем ссылку на смену пароля
            /** @var ModuleUser_EntityReminder $oReminder */
            $oReminder = Engine::GetEntity('User_Reminder');
            $oReminder->setCode(F::RandomStr(32));
            $oReminder->setDateAdd(F::Now());
            $oReminder->setDateExpire(date('Y-m-d H:i:s', time() + Config::Val('module.user.pass_recovery_delay', 60 * 60 * 24 * 7)));
            $oReminder->setDateUsed(null);
            $oReminder->setIsUsed(0);
            $oReminder->setUserId($oUser->getId());
            if ($this->User_AddReminder($oReminder)) {
                $this->Notify_SendReminderCode($oUser, $oReminder);
                $this->Message_AddNotice($this->Lang_Get('password_reminder_send_link'));
                return true;
            }
        }
        return false;
    }

    protected function _eventRecoverySend($sRecoveryCode) {

        /** @var ModuleUser_EntityReminder $oReminder */
        if ($oReminder = $this->User_GetReminderByCode($sRecoveryCode)) {
            /** @var ModuleUser_EntityUser $oUser */
            if ($oReminder->IsValid() && $oUser = $this->User_GetUserById($oReminder->getUserId())) {
                $sNewPassword = F::RandomStr(7);
                $oUser->setPassword($sNewPassword, true);
                if ($this->User_Update($oUser)) {

                    // Do logout of current user
                    $this->User_Logout();

                    // Close all sessions of this user
                    $this->User_CloseAllSessions($oUser);

                    $oReminder->setDateUsed(F::Now());
                    $oReminder->setIsUsed(1);
                    $this->User_UpdateReminder($oReminder);
                    $this->Notify_SendReminderPassword($oUser, $sNewPassword);
                    $this->SetTemplateAction('reminder_confirm');

                    if (($sUrl = F::GetPost('return_url')) || ($sUrl = F::GetPost('return-path'))) {
                        $this->Viewer_Assign('return-path', $sUrl);
                    }
                    return true;
                }
            }
        }
        return false;
    }

}

// EOF