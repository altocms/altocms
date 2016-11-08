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
 * Экшен обработки регистрации
 *
 * @package actions
 * @since   1.0
 */
class ActionRegistration extends Action {
    /**
     * Инициализация
     *
     */
    public function Init() {
        //  Проверяем аторизован ли юзер
        if (E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('registration_is_authorization'), E::ModuleLang()->Get('attention')
            );
            return R::Action('error');
        }
        //  Если включены инвайты то перенаправляем на страницу регистрации по инвайтам
        if (!E::ModuleUser()->IsAuthorization() && Config::Get('general.reg.invite')
            && !in_array(R::GetActionEvent(), array('invite', 'activate', 'confirm'))
            && !$this->CheckInviteRegister()
        ) {
            return R::Action('registration', 'invite');
        }
        $this->SetDefaultEvent('index');
        //  Устанавливаем title страницы
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('registration'));
    }

    /**
     * Регистрируем евенты
     *
     */
    protected function RegisterEvent() {

        $this->AddEvent('index', 'EventIndex');
        $this->AddEvent('confirm', 'EventConfirm');
        $this->AddEvent('activate', 'EventActivate');
        $this->AddEvent('invite', 'EventInvite');

        $this->AddEvent('ajax-validate-fields', 'EventAjaxValidateFields');
        $this->AddEvent('ajax-registration', 'EventAjaxRegistration');
    }


    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */

    /**
     * Ajax валидация формы регистрации
     */
    protected function EventAjaxValidateFields() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');

        // * Создаем объект пользователя и устанавливаем сценарий валидации
        /** @var ModuleUser_EntityUser $oUser */
        $oUser = E::GetEntity('ModuleUser_EntityUser');
        $oUser->_setValidateScenario('registration');
        //  Пробегаем по переданным полям/значениям и валидируем их каждое в отдельности
        $aFields = F::GetRequest('fields');
        if (is_array($aFields)) {
            foreach ($aFields as $aField) {
                if (isset($aField['field']) && isset($aField['value'])) {
                    E::ModuleHook()->Run('registration_validate_field', array('aField' => &$aField, 'oUser' => &$oUser));

                    $sField = $aField['field'];
                    $sValue = $aField['value'];
                    //  Список полей для валидации
                    switch ($sField) {
                        case 'login':
                            $oUser->setLogin($sValue);
                            break;
                        case 'mail':
                            $oUser->setMail($sValue);
                            break;
                        case 'captcha':
                            $oUser->setCaptcha($sValue);
                            break;
                        case 'password':
                            $oUser->setPassword($sValue);
                            if (isset($aField['params']['login'])) {
                                $oUser->setLogin($aField['params']['login']);
                            }
                            break;
                        case 'password_confirm':
                            $oUser->setPasswordConfirm($sValue);
                            $oUser->setPassword(
                                isset($aField['params']['password']) ? $aField['params']['password'] : null
                            );
                            break;
                        default:
                            continue;
                            break;
                    }
                    //  Валидируем поле
                    $oUser->_Validate(array($sField), false);
                }
            }
        }
        //  Возникли ошибки?
        if ($oUser->_hasValidateErrors()) {
            //  Получаем ошибки
            E::ModuleViewer()->AssignAjax('aErrors', $oUser->_getValidateErrors());
        }
    }

    /**
     * Обработка Ajax регистрации
     */
    protected function EventAjaxRegistration() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');

        E::ModuleSecurity()->ValidateSendForm();

        // * Создаем объект пользователя и устанавливаем сценарий валидации
        /** @var ModuleUser_EntityUser $oUser */
        $oUser = E::GetEntity('ModuleUser_EntityUser');
        $oUser->_setValidateScenario('registration');

        // * Заполняем поля (данные)
        $oUser->setLogin($this->GetPost('login'));
        $oUser->setMail($this->GetPost('mail'));
        $oUser->setPassword($this->GetPost('password'));
        $oUser->setPasswordConfirm($this->GetPost('password_confirm'));
        $oUser->setCaptcha($this->GetPost('captcha'));
        $oUser->setDateRegister(F::Now());
        $oUser->setIpRegister(F::GetUserIp());

        // * Если используется активация, то генерим код активации
        if (Config::Get('general.reg.activation')) {
            $oUser->setActivate(0);
            $oUser->setActivationKey(F::RandomStr());
        } else {
            $oUser->setActivate(1);
            $oUser->setActivationKey(null);
        }
        E::ModuleHook()->Run('registration_validate_before', array('oUser' => $oUser));

        // * Запускаем валидацию
        if ($oUser->_Validate()) {
            // Сбросим капчу // issue#342.
            E::ModuleSession()->Drop(E::ModuleCaptcha()->GetKeyName());

            E::ModuleHook()->Run('registration_validate_after', array('oUser' => $oUser));
            $oUser->setPassword($oUser->getPassword(), true);
            if ($this->_addUser($oUser)) {
                E::ModuleHook()->Run('registration_after', array('oUser' => $oUser));

                // * Подписываем пользователя на дефолтные события в ленте активности
                E::ModuleStream()->SwitchUserEventDefaultTypes($oUser->getId());

                // * Если юзер зарегистрировался по приглашению то обновляем инвайт
                if (Config::Get('general.reg.invite') && ($oInvite = E::ModuleUser()->GetInviteByCode($this->GetInviteRegister()))) {
                    $oInvite->setUserToId($oUser->getId());
                    $oInvite->setDateUsed(F::Now());
                    $oInvite->setUsed(1);
                    E::ModuleUser()->UpdateInvite($oInvite);
                }

                // * Если стоит регистрация с активацией то проводим её
                if (Config::Get('general.reg.activation')) {
                    // * Отправляем на мыло письмо о подтверждении регистрации
                    E::ModuleNotify()->SendRegistrationActivate($oUser, F::GetRequestStr('password'));
                    E::ModuleViewer()->AssignAjax('sUrlRedirect', R::GetPath('registration') . 'confirm/');
                } else {
                    E::ModuleNotify()->SendRegistration($oUser, F::GetRequestStr('password'));
                    $oUser = E::ModuleUser()->GetUserById($oUser->getId());

                    // * Сразу авторизуем
                    E::ModuleUser()->Authorization($oUser, false);
                    $this->DropInviteRegister();

                    // * Определяем URL для редиректа после авторизации
                    $sUrl = Config::Get('module.user.redirect_after_registration');
                    if (F::GetRequestStr('return-path')) {
                        $sUrl = F::GetRequestStr('return-path');
                    }
                    E::ModuleViewer()->AssignAjax('sUrlRedirect', $sUrl ? $sUrl : Config::Get('path.root.url'));
                    E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('registration_ok'));
                }
            } else {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
                return;
            }
        } else {
            // * Получаем ошибки
            E::ModuleViewer()->AssignAjax('aErrors', $oUser->_getValidateErrors());
        }
    }

    /**
     * Add new user
     *
     * @param ModuleUser_EntityUser $oUser
     *
     * @return bool|ModuleUser_EntityUser
     */
    protected function _addUser($oUser) {

        return E::ModuleUser()->Add($oUser);
    }

    /**
     * Показывает страничку регистрации
     * Просто вывод шаблона
     */
    protected function EventIndex() {

    }

    /**
     * Обрабатывает активацию аккаунта
     */
    protected function EventActivate() {

        $bError = false;

        // * Проверяет передан ли код активации
        $sActivateKey = $this->GetParam(0);
        if (!F::CheckVal($sActivateKey, 'md5')) {
            $bError = true;
        }

        // * Проверяет верный ли код активации
        if (!($oUser = E::ModuleUser()->GetUserByActivationKey($sActivateKey))) {
            $bError = true;
        }

        // * User is already activated
        if ($oUser && $oUser->isActivated()) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('registration_activate_error_reactivate'), E::ModuleLang()->Get('error')
            );
            return R::Action('error');
        }

        // * Если что то не то
        if ($bError) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('registration_activate_error_code'), E::ModuleLang()->Get('error')
            );
            return R::Action('error');
        }

        // * Активируем
        if ($this->_activateUser($oUser)) {
            $this->DropInviteRegister();
            E::ModuleViewer()->Assign('bRefreshToHome', true);
            E::ModuleUser()->Authorization($oUser, false);
            return;
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
            return R::Action('error');
        }
    }

    /**
     * Activate user
     *
     * @param ModuleUser_EntityUser $oUser
     *
     * @return bool
     */
    protected function _activateUser($oUser) {

        return E::ModuleUser()->Activate($oUser);
    }

    /**
     * Обработка кода приглашения при включеном режиме инвайтов
     *
     */
    protected function EventInvite() {

        if (!Config::Get('general.reg.invite')) {
            return parent::EventNotFound();
        }
        //  Обработка отправки формы с кодом приглашения
        if (F::isPost('submit_invite')) {
            //  проверяем код приглашения на валидность
            if ($this->CheckInviteRegister()) {
                $sInviteCode = $this->GetInviteRegister();
            } else {
                $sInviteCode = trim(F::GetRequestStr('invite_code'));
            }
            $oInvite = E::ModuleUser()->GetInviteByCode($sInviteCode);
            if ($oInvite) {
                if (!$this->CheckInviteRegister()) {
                    E::ModuleSession()->Set('invite_code', $oInvite->getCode());
                }
                return R::Action('registration');
            } else {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('registration_invite_code_error'), E::ModuleLang()->Get('error'));
            }
        }
    }

    /**
     * Пытается ли юзер зарегистрироваться с помощью кода приглашения
     *
     * @return bool
     */
    protected function CheckInviteRegister() {

        if (E::ModuleSession()->Get('invite_code')) {
            return true;
        }
        return false;
    }

    /**
     * Вожвращает код приглашения из сессии
     *
     * @return string
     */
    protected function GetInviteRegister() {

        return E::ModuleSession()->Get('invite_code');
    }

    /**
     * Удаляет код приглашения из сессии
     */
    protected function DropInviteRegister() {

        if (Config::Get('general.reg.invite')) {
            E::ModuleSession()->Drop('invite_code');
        }
    }

    /**
     * Просто выводит шаблон для подтверждения регистрации
     *
     */
    protected function EventConfirm() {
    }

}

// EOF