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
        /**
         * Доступ только у авторизованных пользователей
         */
        $this->oUserCurrent = $this->User_getUserCurrent();
        if (!$this->oUserCurrent) {
            parent::EventNotFound();
        }
        $this->SetDefaultEvent('index');

        $this->Viewer_Assign('sMenuItemSelect', 'feed');
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
        /**
         * Получаем топики
         */
        $aTopics = $this->Userfeed_read($this->oUserCurrent->getId());
        /**
         * Вызов хуков
         */
        $this->Hook_Run('topics_list_show', array('aTopics' => $aTopics));
        $this->Viewer_Assign('aTopics', $aTopics);
        if (count($aTopics)) {
            $this->Viewer_Assign('iUserfeedLastId', end($aTopics)->getId());
        }
        if (count($aTopics) < Config::Get('module.userfeed.count_default')) {
            $this->Viewer_Assign('bDisableGetMoreButton', true);
        } else {
            $this->Viewer_Assign('bDisableGetMoreButton', false);
        }
        $this->SetTemplateAction('list');
    }

    /**
     * Выводит ленту контента(топики) для пользователя
     *
     */
    protected function EventTrack() {

        $this->sMenuSubItemSelect = 'track';
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(0, 2) ? $this->GetParamEventMatch(0, 2) : 1;
        /**
         * Получаем топики
         */
        $aResult = $this->Userfeed_trackread(
            $this->oUserCurrent->getId(), $iPage, Config::Get('module.topic.per_page')
        );

        $aTopics = $aResult['collection'];

        /**
         * Формируем постраничность
         */
        $aPaging = $this->Viewer_MakePaging(
            $aResult['count'], $iPage, Config::Get('module.topic.per_page'), Config::Get('pagination.pages.count'),
            Router::GetPath('feed') . 'track'
        );

        /**
         * Вызов хуков
         */
        $this->Hook_Run('topics_list_show', array('aTopics' => $aTopics));
        $this->Viewer_Assign('aTopics', $aTopics);
        $this->Viewer_Assign('aPaging', $aPaging);

        $this->SetTemplateAction('track');
    }

    /**
     * Выводит ленту контента(только топики содержащие новые комментарии) для пользователя
     *
     */
    protected function EventTrackNew() {

        $this->sMenuSubItemSelect = 'track_new';
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(1, 2) ? $this->GetParamEventMatch(1, 2) : 1;
        /**
         * Получаем топики
         */
        $aResult = $this->Userfeed_trackread(
            $this->oUserCurrent->getId(), $iPage, Config::Get('module.topic.per_page'), true
        );

        $aTopics = $aResult['collection'];

        /**
         * Формируем постраничность
         */
        $aPaging = $this->Viewer_MakePaging(
            $aResult['count'], $iPage, Config::Get('module.topic.per_page'), Config::Get('pagination.pages.count'),
            Router::GetPath('feed') . 'track/new'
        );

        /**
         * Вызов хуков
         */
        $this->Hook_Run('topics_list_show', array('aTopics' => $aTopics));
        $this->Viewer_Assign('aTopics', $aTopics);
        $this->Viewer_Assign('aPaging', $aPaging);

        $this->SetTemplateAction('track');
    }

    /**
     * Подгрузка ленты топиков (замена постраничности)
     *
     */
    protected function EventGetMore() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        $this->Viewer_SetResponseAjax('json');
        /**
         * Проверяем последний просмотренный ID топика
         */
        $iFromId = F::GetRequestStr('last_id');
        if (!$iFromId) {
            $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
            return;
        }
        /**
         * Получаем топики
         */
        $aTopics = $this->Userfeed_read($this->oUserCurrent->getId(), null, $iFromId);
        /**
         * Вызов хуков
         */
        $this->Hook_Run('topics_list_show', array('aTopics' => $aTopics));
        /**
         * Загружаем данные в ajax ответ
         */
        $oViewer = $this->Viewer_GetLocalViewer();
        $oViewer->Assign('aTopics', $aTopics);
        $this->Viewer_AssignAjax('result', $oViewer->Fetch('topic_list.tpl'));
        $this->Viewer_AssignAjax('topics_count', count($aTopics));

        if (count($aTopics)) {
            $this->Viewer_AssignAjax('iUserfeedLastId', end($aTopics)->getId());
        }
    }

    /**
     * Подписка на контент блога или пользователя
     *
     */
    protected function EventSubscribe() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        $this->Viewer_SetResponseAjax('json');
        /**
         * Проверяем наличие ID блога или пользователя
         */
        if (!F::GetRequest('id')) {
            $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
        }
        $sType = F::GetRequestStr('type');
        $iType = null;
        /**
         * Определяем тип подписки
         */
        switch ($sType) {
            case 'blogs':
                $iType = ModuleUserfeed::SUBSCRIBE_TYPE_BLOG;
                /**
                 * Проверяем существование блога
                 */
                if (!$this->Blog_GetBlogById(F::GetRequestStr('id'))) {
                    $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                    return;
                }
                break;
            case 'users':
                $iType = ModuleUserfeed::SUBSCRIBE_TYPE_USER;
                /**
                 * Проверяем существование пользователя
                 */
                if (!$this->User_GetUserById(F::GetRequestStr('id'))) {
                    $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                    return;
                }
                if ($this->oUserCurrent->getId() == F::GetRequestStr('id')) {
                    $this->Message_AddError(
                        $this->Lang_Get('userfeed_error_subscribe_to_yourself'), $this->Lang_Get('error')
                    );
                    return;
                }
                break;
            default:
                $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                return;
        }
        /**
         * Подписываем
         */
        $this->Userfeed_subscribeUser($this->oUserCurrent->getId(), $iType, F::GetRequestStr('id'));
        $this->Message_AddNotice($this->Lang_Get('userfeed_subscribes_updated'), $this->Lang_Get('attention'));
    }

    /**
     * Подписка на пользвователя по логину
     *
     */
    protected function EventSubscribeByLogin() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        $this->Viewer_SetResponseAjax('json');
        /**
         * Передан ли логин
         */
        $sUserLogin = getRequestPostStr('login');
        if (!$sUserLogin) {
            $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
            return;
        }
        /**
         * Проверяем существование прользователя
         */
        $oUser = $this->User_getUserByLogin($sUserLogin);
        if (!$oUser) {
            $this->Message_AddError(
                $this->Lang_Get('user_not_found', array('login' => htmlspecialchars(F::GetRequestStr('login')))),
                $this->Lang_Get('error')
            );
            return;
        }
        /**
         * Не даем подписаться на самого себя
         */
        if ($this->oUserCurrent->getId() == $oUser->getId()) {
            $this->Message_AddError($this->Lang_Get('userfeed_error_subscribe_to_yourself'), $this->Lang_Get('error'));
            return;
        }
        /**
         * Подписываем
         */
        $this->Userfeed_subscribeUser(
            $this->oUserCurrent->getId(), ModuleUserfeed::SUBSCRIBE_TYPE_USER, $oUser->getId()
        );
        /**
         * Загружаем данные ajax ответ
         */
        $this->Viewer_AssignAjax('uid', $oUser->getId());
        $this->Viewer_AssignAjax('user_login', $oUser->getLogin());
        $this->Viewer_AssignAjax('user_web_path', $oUser->getUserWebPath());
        $this->Viewer_AssignAjax('user_avatar_48', $oUser->getAvatarUrl(48));
        $this->Viewer_AssignAjax('lang_error_msg', $this->Lang_Get('userfeed_subscribes_already_subscribed'));
        $this->Viewer_AssignAjax('lang_error_title', $this->Lang_Get('error'));
        $this->Message_AddNotice($this->Lang_Get('userfeed_subscribes_updated'), $this->Lang_Get('attention'));
    }

    /**
     * Отписка от блога или пользователя
     *
     */
    protected function EventUnsubscribe() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        $this->Viewer_SetResponseAjax('json');
        if (!F::GetRequest('id')) {
            $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
            return;
        }
        $sType = F::GetRequestStr('type');
        $iType = null;
        /**
         * Определяем от чего отписываемся
         */
        switch ($sType) {
            case 'blogs':
                $iType = ModuleUserfeed::SUBSCRIBE_TYPE_BLOG;
                break;
            case 'users':
                $iType = ModuleUserfeed::SUBSCRIBE_TYPE_USER;
                break;
            default:
                $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                return;
        }
        /**
         * Отписываем пользователя
         */
        $this->Userfeed_unsubscribeUser($this->oUserCurrent->getId(), $iType, F::GetRequestStr('id'));
        $this->Message_AddNotice($this->Lang_Get('userfeed_subscribes_updated'), $this->Lang_Get('attention'));
    }

    /**
     * При завершении экшена загружаем в шаблон необходимые переменные
     *
     */
    public function EventShutdown() {

        $this->Viewer_Assign('sMenuHeadItemSelect', $this->sMenuHeadItemSelect);
        $this->Viewer_Assign('sMenuSubItemSelect', $this->sMenuSubItemSelect);
        /**
         * Подсчитываем новые топики
         */
        //$iCountTopicsCollectiveNew=$this->Topic_GetCountTopicsCollectiveNew();
        //$iCountTopicsPersonalNew=$this->Topic_GetCountTopicsPersonalNew();
        //$iCountTopicsNew=$iCountTopicsCollectiveNew+$iCountTopicsPersonalNew;
        /**
         * Загружаем переменные в шаблон
         */
        //$this->Viewer_Assign('iCountTopicsCollectiveNew',$iCountTopicsCollectiveNew);
        //$this->Viewer_Assign('iCountTopicsPersonalNew',$iCountTopicsPersonalNew);
        //$this->Viewer_Assign('iCountTopicsNew',$iCountTopicsNew);
    }
}

// EOF