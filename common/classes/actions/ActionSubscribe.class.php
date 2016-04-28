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
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
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
        $oSubscribe = E::ModuleSubscribe()->GetSubscribeByKey($this->getParam(0));
        if ($oSubscribe && $oSubscribe->getStatus() == 1) {
            /**
             * Отписываем пользователя
             */
            $oSubscribe->setStatus(0);
            $oSubscribe->setDateRemove(F::Now());
            E::ModuleSubscribe()->UpdateSubscribe($oSubscribe);

            E::ModuleMessage()->AddNotice(E::ModuleLang()->get('subscribe_change_ok'), null, true);
        }
        /**
         * Получаем URL для редиректа
         */
        if ((!$sUrl = E::ModuleSubscribe()->GetUrlTarget($oSubscribe->getTargetType(), $oSubscribe->getTargetId()))) {
            $sUrl = R::GetLink('index');
        }
        R::Location($sUrl);
    }

    /**
     * Изменение состояния подписки
     */
    protected function EventAjaxSubscribeToggle() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        E::ModuleViewer()->SetResponseAjax('json');
        /**
         * Получаем емайл подписки и проверяем его на валидность
         */
        $sMail = F::GetRequestStr('mail');
        if ($this->oUserCurrent) {
            $sMail = $this->oUserCurrent->getMail();
        }
        if (!F::CheckVal($sMail, 'mail')) {
            E::ModuleMessage()->AddError(E::ModuleLang()->get('registration_mail_error'), E::ModuleLang()->get('error'));
            return;
        }
        /**
         * Получаем тип объекта подписки
         */
        $sTargetType = F::GetRequestStr('target_type');
        if (!E::ModuleSubscribe()->IsAllowTargetType($sTargetType)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->get('system_error'), E::ModuleLang()->get('error'));
            return;
        }
        $sTargetId = F::GetRequestStr('target_id') ? F::GetRequestStr('target_id') : null;
        $iValue = F::GetRequest('value') ? 1 : 0;

        $oSubscribe = null;
        /**
         * Есть ли доступ к подписке гостям?
         */
        if (!$this->oUserCurrent && !E::ModuleSubscribe()->IsAllowTargetForGuest($sTargetType)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->get('need_authorization'), E::ModuleLang()->get('error'));
            return;
        }
        /**
         * Проверка объекта подписки
         */
        if (!E::ModuleSubscribe()->CheckTarget($sTargetType, $sTargetId, $iValue)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->get('system_error'), E::ModuleLang()->get('error'));
            return;
        }
        /**
         * Если подписка еще не существовала, то создаем её
         */
        if ($oSubscribe = E::ModuleSubscribe()->AddSubscribeSimple(
            $sTargetType, $sTargetId, $sMail, $this->oUserCurrent ? $this->oUserCurrent->getId() : null
        )
        ) {
            $oSubscribe->setStatus($iValue);
            E::ModuleSubscribe()->UpdateSubscribe($oSubscribe);
            E::ModuleMessage()->AddNotice(E::ModuleLang()->get('subscribe_change_ok'), E::ModuleLang()->get('attention'));
            return;
        }
        E::ModuleMessage()->AddError(E::ModuleLang()->get('system_error'), E::ModuleLang()->get('error'));
        return;
    }

    /**
     * Изменение состояния подписки
     */
    protected function EventAjaxTrackToggle() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        E::ModuleViewer()->SetResponseAjax('json');

        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddError(E::ModuleLang()->get('need_authorization'), E::ModuleLang()->get('error'));
            return;
        }
        /**
         * Получаем тип объекта подписки
         */
        $sTargetType = F::GetRequestStr('target_type');
        if (!E::ModuleSubscribe()->IsAllowTargetType($sTargetType)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->get('system_error'), E::ModuleLang()->get('error'));
            return;
        }
        $sTargetId = F::GetRequestStr('target_id') ? F::GetRequestStr('target_id') : null;
        $iValue = F::GetRequest('value') ? 1 : 0;

        $oTrack = null;
        /**
         * Проверка объекта подписки
         */
        if (!E::ModuleSubscribe()->CheckTarget($sTargetType, $sTargetId, $iValue)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->get('system_error'), E::ModuleLang()->get('error'));
            return;
        }
        /**
         * Если подписка еще не существовала, то создаем её
         */
        if ($oTrack = E::ModuleSubscribe()->AddTrackSimple($sTargetType, $sTargetId, $this->oUserCurrent->getId())) {
            $oTrack->setStatus($iValue);
            E::ModuleSubscribe()->UpdateTrack($oTrack);
            E::ModuleMessage()->AddNotice(E::ModuleLang()->get('subscribe_change_ok'), E::ModuleLang()->get('attention'));
            return;
        }
        E::ModuleMessage()->AddError(E::ModuleLang()->get('system_error'), E::ModuleLang()->get('error'));
        return;
    }
}

// EOF