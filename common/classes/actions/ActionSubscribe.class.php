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
 * Экшен обработки подписок пользователей
 *
 * @package actions
 * @since   1.0
 */
class ActionSubscribe extends Action {
    /**
     * Текущий пользователь
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent = null;

    /**
     * Инициализация
     *
     */
    public function Init() {
        $this->oUserCurrent = $this->User_GetUserCurrent();
    }

    /**
     * Регистрация евентов
     *
     */
    protected function RegisterEvent() {
        $this->AddEventPreg('/^unsubscribe$/i', '/^\w{32}$/i', 'EventUnsubscribe');
        $this->AddEvent('ajax-subscribe-toggle', 'EventAjaxSubscribeToggle');
        $this->AddEvent('ajax-track-toggle', 'EventAjaxTrackToggle');
    }


    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */


    /**
     * Отписка от подписки
     */
    protected function EventUnsubscribe() {
        /**
         * Получаем подписку по ключу
         */
        $oSubscribe = $this->Subscribe_GetSubscribeByKey($this->getParam(0));
        if ($oSubscribe && $oSubscribe->getStatus() == 1) {
            /**
             * Отписываем пользователя
             */
            $oSubscribe->setStatus(0);
            $oSubscribe->setDateRemove(F::Now());
            $this->Subscribe_UpdateSubscribe($oSubscribe);

            $this->Message_AddNotice($this->Lang_Get('subscribe_change_ok'), null, true);
        }
        /**
         * Получаем URL для редиректа
         */
        if ((!$sUrl = $this->Subscribe_GetUrlTarget($oSubscribe->getTargetType(), $oSubscribe->getTargetId()))) {
            $sUrl = Router::GetPath('index');
        }
        Router::Location($sUrl);
    }

    /**
     * Изменение состояния подписки
     */
    protected function EventAjaxSubscribeToggle() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        $this->Viewer_SetResponseAjax('json');
        /**
         * Получаем емайл подписки и проверяем его на валидность
         */
        $sMail = F::GetRequestStr('mail');
        if ($this->oUserCurrent) {
            $sMail = $this->oUserCurrent->getMail();
        }
        if (!F::CheckVal($sMail, 'mail')) {
            $this->Message_AddError($this->Lang_Get('registration_mail_error'), $this->Lang_Get('error'));
            return;
        }
        /**
         * Получаем тип объекта подписки
         */
        $sTargetType = F::GetRequestStr('target_type');
        if (!$this->Subscribe_IsAllowTargetType($sTargetType)) {
            $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
            return;
        }
        $sTargetId = F::GetRequestStr('target_id') ? F::GetRequestStr('target_id') : null;
        $iValue = F::GetRequest('value') ? 1 : 0;

        $oSubscribe = null;
        /**
         * Есть ли доступ к подписке гостям?
         */
        if (!$this->oUserCurrent && !$this->Subscribe_IsAllowTargetForGuest($sTargetType)) {
            $this->Message_AddError($this->Lang_Get('need_authorization'), $this->Lang_Get('error'));
            return;
        }
        /**
         * Проверка объекта подписки
         */
        if (!$this->Subscribe_CheckTarget($sTargetType, $sTargetId, $iValue)) {
            $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
            return;
        }
        /**
         * Если подписка еще не существовала, то создаем её
         */
        if ($oSubscribe = $this->Subscribe_AddSubscribeSimple(
            $sTargetType, $sTargetId, $sMail, $this->oUserCurrent ? $this->oUserCurrent->getId() : null
        )
        ) {
            $oSubscribe->setStatus($iValue);
            $this->Subscribe_UpdateSubscribe($oSubscribe);
            $this->Message_AddNotice($this->Lang_Get('subscribe_change_ok'), $this->Lang_Get('attention'));
            return;
        }
        $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
        return;
    }

    /**
     * Изменение состояния подписки
     */
    protected function EventAjaxTrackToggle() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        $this->Viewer_SetResponseAjax('json');

        if (!$this->oUserCurrent) {
            $this->Message_AddError($this->Lang_Get('need_authorization'), $this->Lang_Get('error'));
            return;
        }
        /**
         * Получаем тип объекта подписки
         */
        $sTargetType = F::GetRequestStr('target_type');
        if (!$this->Subscribe_IsAllowTargetType($sTargetType)) {
            $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
            return;
        }
        $sTargetId = F::GetRequestStr('target_id') ? F::GetRequestStr('target_id') : null;
        $iValue = F::GetRequest('value') ? 1 : 0;

        $oTrack = null;
        /**
         * Проверка объекта подписки
         */
        if (!$this->Subscribe_CheckTarget($sTargetType, $sTargetId, $iValue)) {
            $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
            return;
        }
        /**
         * Если подписка еще не существовала, то создаем её
         */
        if ($oTrack = $this->Subscribe_AddTrackSimple($sTargetType, $sTargetId, $this->oUserCurrent->getId())) {
            $oTrack->setStatus($iValue);
            $this->Subscribe_UpdateTrack($oTrack);
            $this->Message_AddNotice($this->Lang_Get('subscribe_change_ok'), $this->Lang_Get('attention'));
            return;
        }
        $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
        return;
    }
}

// EOF