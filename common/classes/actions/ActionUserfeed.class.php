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
 * Обрабатывает пользовательские ленты контента
 *
 * @package actions
 * @since   1.0
 */
class ActionUserfeed extends Action {
    /**
     * Текущий пользователь
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent;

    protected $sMenuHeadItemSelect = 'blog';

    protected $sMenuSubItemSelect = 'feed';

    /**
     * Инициализация
     *
     */
    public function Init() {

        // * Доступ только у авторизованных пользователей
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
        if (!$this->oUserCurrent) {
            parent::EventNotFound();
        }
        $this->SetDefaultEvent('index');

        E::ModuleViewer()->Assign('sMenuItemSelect', 'feed');
    }

    /**
     * Регистрация евентов
     *
     */
    protected function RegisterEvent() {

        $this->AddEvent('index', array('EventIndex', 'index'));
        $this->AddEventPreg('/^track$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventTrack');
        $this->AddEventPreg('/^track$/i', '/^new$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventTrackNew');
        $this->AddEvent('subscribe', 'EventSubscribe');
        $this->AddEvent('subscribeByLogin', 'EventSubscribeByLogin');
        $this->AddEvent('unsubscribe', 'EventUnSubscribe');
        $this->AddEvent('get_more', 'EventGetMore');
    }

    /**
     * Выводит ленту контента(топики) для пользователя
     *
     */
    protected function EventIndex() {

        // * Получаем топики
        $aTopics = E::ModuleUserfeed()->Read($this->oUserCurrent->getId());

        // * Вызов хуков
        E::ModuleHook()->Run('topics_list_show', array('aTopics' => $aTopics));
        E::ModuleViewer()->Assign('aTopics', $aTopics);
        if (count($aTopics)) {
            E::ModuleViewer()->Assign('iUserfeedLastId', end($aTopics)->getId());
        }
        if (count($aTopics) < Config::Get('module.userfeed.count_default')) {
            E::ModuleViewer()->Assign('bDisableGetMoreButton', TRUE);
        } else {
            E::ModuleViewer()->Assign('bDisableGetMoreButton', FALSE);
        }
        $this->SetTemplateAction('list');
    }

    /**
     * Выводит ленту контента(топики) для пользователя
     *
     */
    protected function EventTrack() {

        $this->sMenuSubItemSelect = 'track';

        // * Получаем топики
        $aResult = E::ModuleUserfeed()->Trackread($this->oUserCurrent->getId(), 1, Config::Get('module.userfeed.count_default'));
        $aTopics = $aResult['collection'];

        // * Вызов хуков
        E::ModuleHook()->Run('topics_list_show', array('aTopics' => $aTopics));

        E::ModuleViewer()->Assign('aTopics', $aTopics);
        E::ModuleViewer()->Assign('sFeedType', 'track');
        if (count($aTopics)) {
            E::ModuleViewer()->Assign('iUserfeedLastId', 1);
        }
        if ($aResult['count'] < Config::Get('module.userfeed.count_default')) {
            E::ModuleViewer()->Assign('bDisableGetMoreButton', TRUE);
        } else {
            E::ModuleViewer()->Assign('bDisableGetMoreButton', FALSE);
        }

        $this->SetTemplateAction('track');
    }

    /**
     * Выводит ленту контента(только топики содержащие новые комментарии) для пользователя
     *
     */
    protected function EventTrackNew() {

        $this->sMenuSubItemSelect = 'track_new';

        // * Получаем топики
        $aResult = E::ModuleUserfeed()->Trackread($this->oUserCurrent->getId(), 1, Config::Get('module.userfeed.count_default'), TRUE);
        $aTopics = $aResult['collection'];

        // * Вызов хуков
        E::ModuleHook()->Run('topics_list_show', array('aTopics' => $aTopics));

        E::ModuleViewer()->Assign('aTopics', $aTopics);
        E::ModuleViewer()->Assign('sFeedType', 'track_new');
        if (count($aTopics)) {
            E::ModuleViewer()->Assign('iUserfeedLastId', 1);
        }
        if ($aResult['count'] < Config::Get('module.userfeed.count_default')) {
            E::ModuleViewer()->Assign('bDisableGetMoreButton', TRUE);
        } else {
            E::ModuleViewer()->Assign('bDisableGetMoreButton', FALSE);
        }

        $this->SetTemplateAction('track');
    }

    /**
     * Подгрузка ленты топиков (замена постраничности)
     *
     */
    protected function EventGetMore() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');

        // * Проверяем последний просмотренный ID топика
        $iFromId = intval(F::GetRequestStr('last_id'));
        if (!$iFromId) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));

            return;
        }

        // * Получаем топики
        $sTrackType = F::GetRequestStr('type', FALSE);
        if ($sTrackType) {
            $aResult = E::ModuleUserfeed()->Trackread($this->oUserCurrent->getId(), ++$iFromId, Config::Get('module.userfeed.count_default'), ($sTrackType == 'track_new' ? TRUE : FALSE));
            $aTopics = $aResult['collection'];
        } else {
            $aTopics = E::ModuleUserfeed()->Read($this->oUserCurrent->getId(), NULL, $iFromId);
        }

        // * Вызов хуков
        E::ModuleHook()->Run('topics_list_show', array('aTopics' => $aTopics));

        // * Загружаем данные в ajax ответ
        $oViewer = E::ModuleViewer()->GetLocalViewer();
        $oViewer->Assign('aTopics', $aTopics);
        E::ModuleViewer()->AssignAjax('result', $oViewer->Fetch('topics/topic.list.tpl'));
        E::ModuleViewer()->AssignAjax('topics_count', count($aTopics));

        if (count($aTopics)) {
            if ($sTrackType) {
                E::ModuleViewer()->AssignAjax('iUserfeedLastId', $iFromId);
            } else {
                E::ModuleViewer()->AssignAjax('iUserfeedLastId', end($aTopics)->getId());
            }
        }
    }

    /**
     * Подписка на контент блога или пользователя
     *
     */
    protected function EventSubscribe() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');

        // * Проверяем наличие ID блога или пользователя
        if (!F::GetRequest('id')) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
        }
        $sType = F::GetRequestStr('type');
        $iType = null;

        // * Определяем тип подписки
        switch ($sType) {
            case 'blog':
            case 'blogs':
                $iType = ModuleUserfeed::SUBSCRIBE_TYPE_BLOG;

                // * Проверяем существование блога
                if (!E::ModuleBlog()->GetBlogById(F::GetRequestStr('id'))) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                    return;
                }
                break;
            case 'user':
            case 'users':
                $iType = ModuleUserfeed::SUBSCRIBE_TYPE_USER;

                // * Проверяем существование пользователя
                if (!E::ModuleUser()->GetUserById(F::GetRequestStr('id'))) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                    return;
                }
                if ($this->oUserCurrent->getId() == F::GetRequestStr('id')) {
                    E::ModuleMessage()->AddError(
                        E::ModuleLang()->Get('userfeed_error_subscribe_to_yourself'), E::ModuleLang()->Get('error')
                    );
                    return;
                }
                break;
            default:
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                return;
        }

        // * Подписываем
        E::ModuleUserfeed()->SubscribeUser($this->oUserCurrent->getId(), $iType, F::GetRequestStr('id'));
        E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('userfeed_subscribes_updated'), E::ModuleLang()->Get('attention'));
    }

    /**
     * Подписка на пользвователя по логину
     *
     */
    protected function EventSubscribeByLogin() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');

        // * Передан ли логин
        $sUserLogin = $this->GetPost('login');
        if (!$sUserLogin) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Проверяем существование прользователя
        $oUser = E::ModuleUser()->GetUserByLogin($sUserLogin);
        if (!$oUser) {
            E::ModuleMessage()->AddError(
                E::ModuleLang()->Get('user_not_found', array('login' => htmlspecialchars(F::GetRequestStr('login')))),
                E::ModuleLang()->Get('error')
            );
            return;
        }

        // * Не даем подписаться на самого себя
        if ($this->oUserCurrent->getId() == $oUser->getId()) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('userfeed_error_subscribe_to_yourself'), E::ModuleLang()->Get('error'));
            return;
        }

        $aData = E::ModuleUserfeed()->GetUserSubscribes($this->oUserCurrent->getId(), ModuleUserfeed::SUBSCRIBE_TYPE_USER, $oUser->getId());
        if (isset($aData['user'][$oUser->getId()])) {
            // Already subscribed
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('userfeed_subscribes_already_subscribed'), E::ModuleLang()->Get('error'));
        } else {
            // * Подписываем
            E::ModuleUserfeed()->SubscribeUser($this->oUserCurrent->getId(), ModuleUserfeed::SUBSCRIBE_TYPE_USER, $oUser->getId());

            // * Загружаем данные ajax ответ
            E::ModuleViewer()->AssignAjax('uid', $oUser->getId());
            E::ModuleViewer()->AssignAjax('user_id', $oUser->getId());
            E::ModuleViewer()->AssignAjax('user_login', $oUser->getLogin());
            E::ModuleViewer()->AssignAjax('user_name', $oUser->getDisplayName());
            E::ModuleViewer()->AssignAjax('user_web_path', $oUser->getUserWebPath());
            E::ModuleViewer()->AssignAjax('user_profile_url', $oUser->getProfileUrl());
            E::ModuleViewer()->AssignAjax('user_avatar', $oUser->getAvatarUrl(24));
            E::ModuleViewer()->AssignAjax('lang_error_msg', E::ModuleLang()->Get('userfeed_subscribes_already_subscribed'));
            E::ModuleViewer()->AssignAjax('lang_error_title', E::ModuleLang()->Get('error'));
            E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('userfeed_subscribes_updated'), E::ModuleLang()->Get('attention'));
        }

    }

    /**
     * Отписка от блога или пользователя
     *
     */
    protected function EventUnsubscribe() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');
        if (!F::GetRequest('id')) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        $sType = F::GetRequestStr('type');
        $iType = null;

        // * Определяем от чего отписываемся
        switch ($sType) {
            case 'blogs':
            case 'blog':
                $iType = ModuleUserfeed::SUBSCRIBE_TYPE_BLOG;
                break;
            case 'users':
            case 'user':
                $iType = ModuleUserfeed::SUBSCRIBE_TYPE_USER;
                break;
            default:
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                return;
        }

        // * Отписываем пользователя
        E::ModuleUserfeed()->UnsubscribeUser($this->oUserCurrent->getId(), $iType, F::GetRequestStr('id'));
        E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('userfeed_subscribes_updated'), E::ModuleLang()->Get('attention'));
    }

    /**
     * При завершении экшена загружаем в шаблон необходимые переменные
     *
     */
    public function EventShutdown() {

        E::ModuleViewer()->Assign('sMenuHeadItemSelect', $this->sMenuHeadItemSelect);
        E::ModuleViewer()->Assign('sMenuSubItemSelect', $this->sMenuSubItemSelect);
        /**
         * Подсчитываем новые топики
         */
        $iCountTopicsCollectiveNew=E::ModuleTopic()->GetCountTopicsCollectiveNew();
        $iCountTopicsPersonalNew=E::ModuleTopic()->GetCountTopicsPersonalNew();
        $iCountTopicsNew=$iCountTopicsCollectiveNew+$iCountTopicsPersonalNew;
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('iCountTopicsCollectiveNew',$iCountTopicsCollectiveNew);
        E::ModuleViewer()->Assign('iCountTopicsPersonalNew',$iCountTopicsPersonalNew);
        E::ModuleViewer()->Assign('iCountTopicsNew',$iCountTopicsNew);
    }
}

// EOF