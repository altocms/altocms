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
 * Экшен обработки профайла юзера, т.е. УРЛ вида /profile/login/
 *
 * @package actions
 * @since   1.0
 */
class ActionProfile extends Action {
    /**
     * Объект юзера чей профиль мы смотрим
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserProfile;
    /**
     * Главное меню
     *
     * @var string
     */
    protected $sMenuHeadItemSelect = 'people';
    /**
     * Субменю
     *
     * @var string
     */
    protected $sMenuSubItemSelect = '';
    /**
     * Текущий пользователь
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent;

    /**
     * Инициализация
     */
    public function Init() {

        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
    }

    /**
     * Регистрация евентов
     */
    protected function RegisterEvent() {

        $this->AddEvent('friendoffer', 'EventFriendOffer');
        $this->AddEvent('ajaxfriendadd', 'EventAjaxFriendAdd');
        $this->AddEvent('ajaxfrienddelete', 'EventAjaxFriendDelete');
        $this->AddEvent('ajaxfriendaccept', 'EventAjaxFriendAccept');
        $this->AddEvent('ajax-note-save', 'EventAjaxNoteSave');
        $this->AddEvent('ajax-note-remove', 'EventAjaxNoteRemove');

        $this->AddEventPreg('/^.+$/i', '/^(whois)?$/i', 'EventWhois');
        $this->AddEventPreg('/^.+$/i', '/^(info)?$/i', 'EventInfo');

        $this->AddEventPreg('/^.+$/i', '/^wall$/i', '/^$/i', 'EventWall');
        $this->AddEventPreg('/^.+$/i', '/^wall$/i', '/^add$/i', 'EventWallAdd');
        $this->AddEventPreg('/^.+$/i', '/^wall$/i', '/^load$/i', 'EventWallLoad');
        $this->AddEventPreg('/^.+$/i', '/^wall$/i', '/^load-reply$/i', 'EventWallLoadReply');
        $this->AddEventPreg('/^.+$/i', '/^wall$/i', '/^remove$/i', 'EventWallRemove');

        $this->AddEventPreg('/^.+$/i', '/^favourites$/i', '/^comments$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventFavouriteComments');
        $this->AddEventPreg('/^.+$/i', '/^favourites$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventFavourite');
        $this->AddEventPreg('/^.+$/i', '/^favourites$/i', '/^topics/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventFavourite');
        $this->AddEventPreg('/^.+$/i', '/^favourites$/i', '/^topics/i', '/^tag/i', '/^.+/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventFavouriteTopicsTag');

        $this->AddEventPreg('/^.+$/i', '/^created/i', '/^notes/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventCreatedNotes');
        $this->AddEventPreg('/^.+$/i', '/^created/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventCreatedTopics');
        $this->AddEventPreg('/^.+$/i', '/^created/i', '/^topics/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventCreatedTopics');
        $this->AddEventPreg('/^.+$/i', '/^created/i', '/^comments$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventCreatedComments');
        $this->AddEventPreg('/^.+$/i', '/^created/i', '/^photos$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventCreatedPhotos');

        $this->AddEventPreg('/^.+$/i', '/^friends/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventFriends');
        $this->AddEventPreg('/^.+$/i', '/^stream/i', '/^$/i', 'EventStream');

        $this->AddEventPreg('/^changemail$/i', '/^confirm-from/i', '/^\w{32}$/i', 'EventChangemailConfirmFrom');
        $this->AddEventPreg('/^changemail$/i', '/^confirm-to/i', '/^\w{32}$/i', 'EventChangemailConfirmTo');
    }

    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */

    /**
     * Проверка корректности профиля
     */
    protected function CheckUserProfile() {

        // * Проверяем есть ли такой юзер
        if (preg_match('/^(id|login)\-(.+)$/i', $this->sCurrentEvent, $aMatches)) {
            if ($aMatches[1] == 'id') {
                $this->oUserProfile = E::ModuleUser()->GetUserById($aMatches[2]);
            } else {
                $this->oUserProfile = E::ModuleUser()->GetUserByLogin($aMatches[2]);
            }
        } else {
            $this->oUserProfile = E::ModuleUser()->GetUserByLogin($this->sCurrentEvent);
        }

        return $this->oUserProfile;
    }

    /**
     * Чтение активности пользователя (stream)
     */
    protected function EventStream() {

        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }
        /**
         * Читаем события
         */
        $aEvents = E::ModuleStream()->ReadByUserId($this->oUserProfile->getId());
        E::ModuleViewer()->Assign(
            'bDisableGetMoreButton',
            E::ModuleStream()->GetCountByUserId($this->oUserProfile->getId()) < Config::Get('module.stream.count_default')
        );
        E::ModuleViewer()->Assign('aStreamEvents', $aEvents);
        if (count($aEvents)) {
            $oEvenLast = end($aEvents);
            E::ModuleViewer()->Assign('iStreamLastId', $oEvenLast->getId());
        }
        $this->SetTemplateAction('stream');
    }

    /**
     * Список друзей пользователей
     */
    protected function EventFriends() {

        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(1, 2) ? $this->GetParamEventMatch(1, 2) : 1;
        /**
         * Получаем список комментов
         */
        $aResult = E::ModuleUser()->GetUsersFriend(
            $this->oUserProfile->getId(), $iPage, Config::Get('module.user.per_page')
        );
        $aFriends = $aResult['collection'];
        /**
         * Формируем постраничность
         */
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.user.per_page'), Config::Get('pagination.pages.count'),
            $this->oUserProfile->getUserUrl() . 'friends'
        );
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aFriends', $aFriends);
        E::ModuleViewer()->AddHtmlTitle(
            E::ModuleLang()->Get('user_menu_profile_friends') . ' ' . $this->oUserProfile->getLogin()
        );

        $this->SetTemplateAction('friends');
    }

    /**
     * Список топиков пользователя
     */
    protected function EventCreatedTopics() {

        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }
        $this->sMenuSubItemSelect = 'topics';
        /**
         * Передан ли номер страницы
         */
        if ($this->GetParamEventMatch(1, 0) == 'topics') {
            $iPage = $this->GetParamEventMatch(2, 2) ? $this->GetParamEventMatch(2, 2) : 1;
        } else {
            $iPage = $this->GetParamEventMatch(1, 2) ? $this->GetParamEventMatch(1, 2) : 1;
        }
        /**
         * Получаем список топиков
         */
        $aResult = E::ModuleTopic()->GetTopicsPersonalByUser(
            $this->oUserProfile->getId(), 1, $iPage, Config::Get('module.topic.per_page')
        );
        $aTopics = $aResult['collection'];
        /**
         * Вызов хуков
         */
        E::ModuleHook()->Run('topics_list_show', array('aTopics' => $aTopics));
        /**
         * Формируем постраничность
         */
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.topic.per_page'), Config::Get('pagination.pages.count'),
            $this->oUserProfile->getUserUrl() . 'created/topics'
        );
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aTopics', $aTopics);
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_publication') . ' ' . $this->oUserProfile->getLogin());
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_publication_blog'));
        E::ModuleViewer()->SetHtmlRssAlternate(
            R::GetPath('rss') . 'personal_blog/' . $this->oUserProfile->getLogin() . '/',
            $this->oUserProfile->getLogin()
        );
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('created_topics');
    }

    /**
     * Вывод комментариев пользователя
     */
    protected function EventCreatedComments() {

        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }
        $this->sMenuSubItemSelect = 'comments';
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(2, 2) ? $this->GetParamEventMatch(2, 2) : 1;
        /**
         * Получаем список комментов
         */
        $aResult = E::ModuleComment()->GetCommentsByUserId(
            $this->oUserProfile->getId(), 'topic', $iPage, Config::Get('module.comment.per_page')
        );
        $aComments = $aResult['collection'];
        /**
         * Формируем постраничность
         */
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.comment.per_page'), Config::Get('pagination.pages.count'),
            $this->oUserProfile->getUserUrl() . 'created/comments'
        );
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aComments', $aComments);
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_publication') . ' ' . $this->oUserProfile->getLogin());
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_publication_comment'));
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('created_comments');
    }


    /**
     * Вывод фотографий пользователя пользователя.
     * В шаблоне в переменной oUserImagesInfo уже есть группы фотографий
     */
    protected function EventCreatedPhotos() {

        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }
        $this->sMenuSubItemSelect = 'photos';

        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_publication') . ' ' . $this->oUserProfile->getLogin());
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('insertimg_images'));
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('created_photos');
    }

    /**
     * Выводит список избранноего юзера
     *
     */
    protected function EventFavourite() {

        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }
        $this->sMenuSubItemSelect = 'topics';
        /**
         * Передан ли номер страницы
         */
        if ($this->GetParamEventMatch(1, 0) == 'topics') {
            $iPage = $this->GetParamEventMatch(2, 2) ? $this->GetParamEventMatch(2, 2) : 1;
        } else {
            $iPage = $this->GetParamEventMatch(1, 2) ? $this->GetParamEventMatch(1, 2) : 1;
        }
        /**
         * Получаем список избранных топиков
         */
        $aResult = E::ModuleTopic()->GetTopicsFavouriteByUserId(
            $this->oUserProfile->getId(), $iPage, Config::Get('module.topic.per_page')
        );
        $aTopics = $aResult['collection'];
        /**
         * Вызов хуков
         */
        E::ModuleHook()->Run('topics_list_show', array('aTopics' => $aTopics));
        /**
         * Формируем постраничность
         */
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.topic.per_page'), Config::Get('pagination.pages.count'),
            $this->oUserProfile->getUserUrl() . 'favourites/topics'
        );
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aTopics', $aTopics);
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_profile') . ' ' . $this->oUserProfile->getLogin());
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_profile_favourites'));
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('favourite_topics');
    }

    /**
     * Список топиков из избранного по тегу
     */
    protected function EventFavouriteTopicsTag() {

        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }

        // * Пользователь авторизован и просматривает свой профиль?
        if (!$this->oUserCurrent || $this->oUserProfile->getId() != $this->oUserCurrent->getId()) {
            return parent::EventNotFound();
        }
        $this->sMenuSubItemSelect = 'topics';
        $sTag = F::UrlDecode($this->GetParamEventMatch(3, 0), true);

        // * Передан ли номер страницы
        $iPage = $this->GetParamEventMatch(4, 2) ? $this->GetParamEventMatch(4, 2) : 1;

        // * Получаем список избранных топиков
        $aResult = E::ModuleFavourite()->GetTags(
            array('target_type' => 'topic', 'user_id' => $this->oUserProfile->getId(), 'text' => $sTag),
            array('target_id' => 'desc'), $iPage, Config::Get('module.topic.per_page')
        );
        $aTopicId = array();
        foreach ($aResult['collection'] as $oTag) {
            $aTopicId[] = $oTag->getTargetId();
        }
        $aTopics = E::ModuleTopic()->GetTopicsAdditionalData($aTopicId);

        // * Формируем постраничность
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.topic.per_page'), Config::Get('pagination.pages.count'),
            $this->oUserProfile->getUserUrl() . 'favourites/topics/tag/' . htmlspecialchars($sTag)
        );

        // * Загружаем переменные в шаблон
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aTopics', $aTopics);
        E::ModuleViewer()->Assign('sFavouriteTag', htmlspecialchars($sTag));
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_profile') . ' ' . $this->oUserProfile->getLogin());
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_profile_favourites'));

        // * Устанавливаем шаблон вывода
        $this->SetTemplateAction('favourite_topics');
    }

    /**
     * Выводит список избранноего юзера
     *
     */
    protected function EventFavouriteComments() {

        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }
        $this->sMenuSubItemSelect = 'comments';
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(2, 2) ? $this->GetParamEventMatch(2, 2) : 1;
        /**
         * Получаем список избранных комментариев
         */
        $aResult = E::ModuleComment()->GetCommentsFavouriteByUserId(
            $this->oUserProfile->getId(), $iPage, Config::Get('module.comment.per_page')
        );
        $aComments = $aResult['collection'];
        /**
         * Формируем постраничность
         */
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.comment.per_page'), Config::Get('pagination.pages.count'),
            $this->oUserProfile->getUserUrl() . 'favourites/comments'
        );
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aComments', $aComments);
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_profile') . ' ' . $this->oUserProfile->getLogin());
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_profile_favourites_comments'));
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('favourite_comments');
    }

    protected function _filterBlogs($aBlogs) {

        if (!$aBlogs || E::IsAdmin() || E::UserId() == $this->oUserProfile->getId()) {
            return $aBlogs;
        } else {
            // Blog types for guest and all users
            $aBlogTypes = E::ModuleBlog()->GetOpenBlogTypes();
            foreach ($aBlogs as $iBlogId => $oBlog) {
                if (!in_array($oBlog->getType(), $aBlogTypes)) {
                    unset($aBlogs[$iBlogId]);
                }
            }
        }
        return $aBlogs;
    }

    protected function _filterBlogUsers($aBlogUsers) {

        if (!$aBlogUsers || E::IsAdmin() || E::UserId() == $this->oUserProfile->getId()) {
            return $aBlogUsers;
        } else {
            // Blog types for guest and all users
            $aBlogTypes = E::ModuleBlog()->GetOpenBlogTypes();
            foreach ($aBlogUsers as $n => $oBlogUser) {
                if (!in_array($oBlogUser->getBlog()->getType(), $aBlogTypes)) {
                    unset($aBlogUsers[$n]);
                }
            }
        }
        return $aBlogUsers;
    }

    /**
     * Показывает инфу профиля
     *
     */
    protected function EventInfo() {

        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }
        $this->sMenuSubItemSelect = 'main';

        // * Получаем список друзей
        $aUsersFriend = E::ModuleUser()->GetUsersFriend($this->oUserProfile->getId(), 1, Config::Get('module.user.friend_on_profile'));

        // * Если активен режим инвайтов, то загружаем дополнительную информацию
        if (Config::Get('general.reg.invite')) {
            // * Получаем список тех кого пригласил юзер
            $aUsersInvite = E::ModuleUser()->GetUsersInvite($this->oUserProfile->getId());
            E::ModuleViewer()->Assign('aUsersInvite', $aUsersInvite);

            // * Получаем того юзера, кто пригласил текущего
            $oUserInviteFrom = E::ModuleUser()->GetUserInviteFrom($this->oUserProfile->getId());
            E::ModuleViewer()->Assign('oUserInviteFrom', $oUserInviteFrom);
        }
        // * Получаем список юзеров блога
        $aBlogUsers = $this->_filterBlogUsers(
            E::ModuleBlog()->GetBlogUsersByUserId($this->oUserProfile->getId(), ModuleBlog::BLOG_USER_ROLE_USER)
        );
        $aBlogModerators = $this->_filterBlogUsers(
            E::ModuleBlog()->GetBlogUsersByUserId($this->oUserProfile->getId(), ModuleBlog::BLOG_USER_ROLE_MODERATOR)
        );
        $aBlogAdministrators = $this->_filterBlogUsers(
            E::ModuleBlog()->GetBlogUsersByUserId($this->oUserProfile->getId(), ModuleBlog::BLOG_USER_ROLE_ADMINISTRATOR)
        );

        // * Получаем список блогов которые создал юзер
        $aBlogsOwner = E::ModuleBlog()->GetBlogsByOwnerId($this->oUserProfile->getId());
        $aBlogsOwner = $this->_filterBlogs($aBlogsOwner);

        // * Получаем список контактов
        $aUserFields = E::ModuleUser()->GetUserFieldsValues($this->oUserProfile->getId());

        // * Вызов хуков
        E::ModuleHook()->Run('profile_whois_show', array("oUserProfile" => $this->oUserProfile));

        // * Загружаем переменные в шаблон
        E::ModuleViewer()->Assign('aBlogUsers', $aBlogUsers);
        E::ModuleViewer()->Assign('aBlogModerators', $aBlogModerators);
        E::ModuleViewer()->Assign('aBlogAdministrators', $aBlogAdministrators);
        E::ModuleViewer()->Assign('aBlogsOwner', $aBlogsOwner);
        E::ModuleViewer()->Assign('aUsersFriend', $aUsersFriend['collection']);
        E::ModuleViewer()->Assign('aUserFields', $aUserFields);
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_profile') . ' ' . $this->oUserProfile->getLogin());
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_profile_whois'));

        // * Устанавливаем шаблон вывода
        $this->SetTemplateAction('info');
    }

    /**
     * LS-comatibility
     *
     * @return string
     */
    protected function EventWhois() {

        return $this->EventInfo();
    }

    /**
     * Отображение стены пользователя
     */
    public function EventWall() {

        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }

        // * Получаем записи стены
        $aWallItems = E::ModuleWall()->GetWall(
            array('wall_user_id' => $this->oUserProfile->getId(), 'pid' => 0), array('id' => 'desc'), 1,
            Config::Get('module.wall.per_page')
        );
        E::ModuleViewer()->Assign('aWallItems', $aWallItems['collection']);
        E::ModuleViewer()->Assign('iCountWall', $aWallItems['count']);

        // LS-compatible
        E::ModuleViewer()->Assign('aWall', $aWallItems['collection']);

        // * Устанавливаем шаблон вывода
        $this->SetTemplateAction('wall');
    }

    /**
     * Добавление записи на стену
     */
    public function EventWallAdd() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');

        // * Пользователь авторизован?
        if (!$this->oUserCurrent) {
            return parent::EventNotFound();
        }
        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }

        // * Создаем запись
        /** @var ModuleWall_EntityWall $oWall */
        $oWall = E::GetEntity('Wall');
        $oWall->_setValidateScenario('add');
        $oWall->setWallUserId($this->oUserProfile->getId());
        $oWall->setUserId($this->oUserCurrent->getId());
        $oWall->setText(F::GetRequestStr('sText'));
        $oWall->setPid(F::GetRequestStr('iPid'));

        E::ModuleHook()->Run('wall_add_validate_before', array('oWall' => $oWall));
        if ($oWall->_Validate()) {

            // * Экранируем текст и добавляем запись в БД
            $oWall->setText(E::ModuleText()->Parser($oWall->getText()));
            E::ModuleHook()->Run('wall_add_before', array('oWall' => $oWall));
            if ($this->AddWallMessage($oWall)) {
                E::ModuleHook()->Run('wall_add_after', array('oWall' => $oWall));

                // * Отправляем уведомления
                if ($oWall->getWallUserId() != $oWall->getUserId()) {
                    E::ModuleNotify()->SendWallNew($oWall, $this->oUserCurrent);
                }
                if (($oWallParent = $oWall->GetPidWall()) && ($oWallParent->getUserId() != $oWall->getUserId())) {
                    E::ModuleNotify()->SendWallReply($oWallParent, $oWall, $this->oUserCurrent);
                }

                // * Добавляем событие в ленту
                E::ModuleStream()->Write($oWall->getUserId(), 'add_wall', $oWall->getId());
            } else {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('wall_add_error'), E::ModuleLang()->Get('error'));
            }
        } else {
            E::ModuleMessage()->AddError($oWall->_getValidateError(), E::ModuleLang()->Get('error'));
        }
    }

    protected function AddWallMessage($oWall) {

        return E::ModuleWall()->AddWall($oWall);
    }

    /**
     * Удаление записи со стены
     */
    public function EventWallRemove() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        E::ModuleViewer()->SetResponseAjax('json');
        /**
         * Пользователь авторизован?
         */
        if (!$this->oUserCurrent) {
            return parent::EventNotFound();
        }
        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }
        /**
         * Получаем запись
         */
        if (!($oWall = E::ModuleWall()->GetWallById(F::GetRequestStr('iId')))) {
            return parent::EventNotFound();
        }
        /**
         * Если разрешено удаление - удаляем
         */
        if ($oWall->isAllowDelete()) {
            E::ModuleWall()->DeleteWall($oWall);
            return;
        }
        return parent::EventNotFound();
    }

    /**
     * Ajax подгрузка сообщений стены
     */
    public function EventWallLoad() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');
        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }

        // * Формируем фильтр для запроса к БД
        $aFilter = array(
            'wall_user_id' => $this->oUserProfile->getId(),
            'pid'          => null
        );
        if (is_numeric(F::GetRequest('iIdLess'))) {
            $aFilter['id_less'] = F::GetRequest('iIdLess');
        } elseif (is_numeric(F::GetRequest('iIdMore'))) {
            $aFilter['id_more'] = F::GetRequest('iIdMore');
        } else {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('error'));
            return;
        }

        // * Получаем сообщения и формируем ответ
        $aWallItems = E::ModuleWall()->GetWall($aFilter, array('id' => 'desc'), 1, Config::Get('module.wall.per_page'));
        E::ModuleViewer()->Assign('aWallItems', $aWallItems['collection']);
        // LS-compatible
        E::ModuleViewer()->Assign('aWall', $aWallItems['collection']);

        E::ModuleViewer()->Assign(
            'oUserCurrent', $this->oUserCurrent
        ); // хак, т.к. к этому моменту текущий юзер не загружен в шаблон
        E::ModuleViewer()->Assign('aLang', E::ModuleLang()->GetLangMsg());

        E::ModuleViewer()->AssignAjax('sText', E::ModuleViewer()->Fetch('actions/profile/action.profile.wall_items.tpl'));
        E::ModuleViewer()->AssignAjax('iCountWall', $aWallItems['count']);
        E::ModuleViewer()->AssignAjax('iCountWallReturn', count($aWallItems['collection']));
    }

    /**
     * Подгрузка ответов на стене к сообщению
     */
    public function EventWallLoadReply() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');
        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }
        // пока оставлю здесь, логику не понял
        //if (!($oWall = E::ModuleWall()->GetWallById($this->GetPost('iPid'))) || $oWall->getPid()) {
        if (!($oWall = E::ModuleWall()->GetWallById($this->GetPost('iPid')))) {
            return parent::EventNotFound();
        }

        // * Формируем фильтр для запроса к БД
        $aFilter = array(
            'wall_user_id' => $this->oUserProfile->getId(),
            'pid'          => $oWall->getId()
        );
        if (is_numeric(F::GetRequest('iIdLess'))) {
            $aFilter['id_less'] = F::GetRequest('iIdLess');
        } elseif (is_numeric(F::GetRequest('iIdMore'))) {
            $aFilter['id_more'] = F::GetRequest('iIdMore');
        } else {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('error'));
            return;
        }

        // * Получаем сообщения и формируем ответ. Необходимо вернуть все ответы, но ставим "разумное" ограничение
        $aWall = E::ModuleWall()->GetWall($aFilter, array('id' => 'asc'), 1, 300);
        E::ModuleViewer()->Assign('aLang', E::ModuleLang()->GetLangMsg());
        E::ModuleViewer()->Assign('aReplyWall', $aWall['collection']);
        E::ModuleViewer()->AssignAjax('sText', E::ModuleViewer()->Fetch('actions/profile/action.profile.wall_items_reply.tpl'));
        E::ModuleViewer()->AssignAjax('iCountWall', $aWall['count']);
        E::ModuleViewer()->AssignAjax('iCountWallReturn', count($aWall['collection']));
    }

    /**
     * Сохраняет заметку о пользователе
     */
    public function EventAjaxNoteSave() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        E::ModuleViewer()->SetResponseAjax('json');
        /**
         * Пользователь авторизован?
         */
        if (!$this->oUserCurrent) {
            return parent::EventNotFound();
        }

        // * Создаем заметку и проводим валидацию
        /** @var ModuleUser_EntityNote $oNote */
        $oNote = E::GetEntity('ModuleUser_EntityNote');
        $oNote->setTargetUserId(F::GetRequestStr('iUserId'));
        $oNote->setUserId($this->oUserCurrent->getId());
        $oNote->setText(F::GetRequestStr('text'));

        if ($oNote->_Validate()) {
            /**
             * Экранируем текст и добавляем запись в БД
             */
            $oNote->setText(htmlspecialchars(strip_tags($oNote->getText())));
            if (E::ModuleUser()->SaveNote($oNote)) {
                E::ModuleViewer()->AssignAjax('sText', nl2br($oNote->getText()));
            } else {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('user_note_save_error'), E::ModuleLang()->Get('error'));
            }
        } else {
            E::ModuleMessage()->AddError($oNote->_getValidateError(), E::ModuleLang()->Get('error'));
        }
    }

    /**
     * Удаляет заметку о пользователе
     */
    public function EventAjaxNoteRemove() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        E::ModuleViewer()->SetResponseAjax('json');
        if (!$this->oUserCurrent) {
            return parent::EventNotFound();
        }

        if (!($oUserTarget = E::ModuleUser()->GetUserById(F::GetRequestStr('iUserId')))) {
            return parent::EventNotFound();
        }
        if (!($oNote = E::ModuleUser()->GetUserNote($oUserTarget->getId(), $this->oUserCurrent->getId()))) {
            return parent::EventNotFound();
        }
        E::ModuleUser()->DeleteUserNoteById($oNote->getId());
    }

    /**
     * Список созданных заметок
     */
    public function EventCreatedNotes() {

        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }
        $this->sMenuSubItemSelect = 'notes';
        /**
         * Заметки может читать только сам пользователь
         */
        if (!$this->oUserCurrent || $this->oUserCurrent->getId() != $this->oUserProfile->getId()) {
            return parent::EventNotFound();
        }
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(2, 2) ? $this->GetParamEventMatch(2, 2) : 1;
        /**
         * Получаем список заметок
         */
        $aResult = E::ModuleUser()->GetUserNotesByUserId(
            $this->oUserProfile->getId(), $iPage, Config::Get('module.user.usernote_per_page')
        );
        $aNotes = $aResult['collection'];
        /**
         * Формируем постраничность
         */
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.user.usernote_per_page'),
            Config::Get('pagination.pages.count'), $this->oUserProfile->getUserUrl() . 'created/notes'
        );
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aNotes', $aNotes);
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_profile') . ' ' . $this->oUserProfile->getLogin());
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('user_menu_profile_notes'));
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('created_notes');
    }

    /**
     * Добавление пользователя в друзья, по отправленной заявке
     */
    public function EventFriendOffer() {

        F::IncludeLib('XXTEA/encrypt.php');
        /**
         * Из реквеста дешефруем ID польователя
         */
        $sUserId = xxtea_decrypt(
            base64_decode(rawurldecode(F::GetRequestStr('code'))), Config::Get('module.talk.encrypt')
        );
        if (!$sUserId) {
            return $this->EventNotFound();
        }
        list($sUserId,) = explode('_', $sUserId, 2);

        $sAction = $this->GetParam(0);
        /**
         * Получаем текущего пользователя
         */
        if (!E::ModuleUser()->IsAuthorization()) {
            return $this->EventNotFound();
        }
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
        /**
         * Получаем объект пользователя приславшего заявку,
         * если пользователь не найден, переводим в раздел сообщений (Talk) -
         * так как пользователь мог перейти сюда либо из talk-сообщений,
         * либо из e-mail письма-уведомления
         */
        if (!$oUser = E::ModuleUser()->GetUserById($sUserId)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('user_not_found'), E::ModuleLang()->Get('error'), true);
            R::Location(R::GetPath('talk'));
            return;
        }
        /**
         * Получаем связь дружбы из базы данных.
         * Если связь не найдена либо статус отличен от OFFER,
         * переходим в раздел Talk и возвращаем сообщение об ошибке
         */
        $oFriend = E::ModuleUser()->GetFriend($this->oUserCurrent->getId(), $oUser->getId(), 0);
        if (!$oFriend
            || !in_array(
                $oFriend->getFriendStatus(),
                array(ModuleUser::USER_FRIEND_OFFER + ModuleUser::USER_FRIEND_NULL,)
            )
        ) {
            $sMessage = ($oFriend)
                ? E::ModuleLang()->Get('user_friend_offer_already_done')
                : E::ModuleLang()->Get('user_friend_offer_not_found');
            E::ModuleMessage()->AddError($sMessage, E::ModuleLang()->Get('error'), true);

            R::Location('talk');
            return;
        }
        /**
         * Устанавливаем новый статус связи
         */
        $oFriend->setStatusTo(
            ($sAction == 'accept')
                ? ModuleUser::USER_FRIEND_ACCEPT
                : ModuleUser::USER_FRIEND_REJECT
        );

        if (E::ModuleUser()->UpdateFriend($oFriend)) {
            $sMessage = ($sAction == 'accept')
                ? E::ModuleLang()->Get('user_friend_add_ok')
                : E::ModuleLang()->Get('user_friend_offer_reject');

            E::ModuleMessage()->AddNoticeSingle($sMessage, E::ModuleLang()->Get('attention'), true);
            $this->NoticeFriendOffer($oUser, $sAction);
        } else {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('system_error'),
                E::ModuleLang()->Get('error'),
                true
            );
        }
        R::Location('talk');
    }

    /**
     * Подтверждение заявки на добавления в друзья
     */
    public function EventAjaxFriendAccept() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        E::ModuleViewer()->SetResponseAjax('json');
        $sUserId = F::GetRequestStr('idUser', null, 'post');
        /**
         * Если пользователь не авторизирован, возвращаем ошибку
         */
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('need_authorization'),
                E::ModuleLang()->Get('error')
            );
            return;
        }
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
        /**
         * При попытке добавить в друзья себя, возвращаем ошибку
         */
        if ($this->oUserCurrent->getId() == $sUserId) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('user_friend_add_self'),
                E::ModuleLang()->Get('error')
            );
            return;
        }
        /**
         * Если пользователь не найден, возвращаем ошибку
         */
        if (!$oUser = E::ModuleUser()->GetUserById($sUserId)) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('user_not_found'),
                E::ModuleLang()->Get('error')
            );
            return;
        }
        $this->oUserProfile = $oUser;
        /**
         * Получаем статус дружбы между пользователями
         */
        $oFriend = E::ModuleUser()->GetFriend($oUser->getId(), $this->oUserCurrent->getId());
        /**
         * При попытке потдвердить ранее отклоненную заявку,
         * проверяем, чтобы изменяющий был принимающей стороной
         */
        if ($oFriend
            && ($oFriend->getStatusFrom() == ModuleUser::USER_FRIEND_OFFER
                || $oFriend->getStatusFrom() == ModuleUser::USER_FRIEND_ACCEPT)
            && ($oFriend->getStatusTo() == ModuleUser::USER_FRIEND_REJECT
                || $oFriend->getStatusTo() == ModuleUser::USER_FRIEND_NULL)
            && $oFriend->getUserTo() == $this->oUserCurrent->getId()
        ) {
            /**
             * Меняем статус с отвергнутое, на акцептованное
             */
            $oFriend->setStatusByUserId(ModuleUser::USER_FRIEND_ACCEPT, $this->oUserCurrent->getId());
            if (E::ModuleUser()->UpdateFriend($oFriend)) {
                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('user_friend_add_ok'), E::ModuleLang()->Get('attention'));
                $this->NoticeFriendOffer($oUser, 'accept');
                /**
                 * Добавляем событие в ленту
                 */
                E::ModuleStream()->Write($oFriend->getUserFrom(), 'add_friend', $oFriend->getUserTo());
                E::ModuleStream()->Write($oFriend->getUserTo(), 'add_friend', $oFriend->getUserFrom());
                /**
                 * Добавляем пользователей к друг другу в ленту активности
                 */
                E::ModuleStream()->SubscribeUser($oFriend->getUserFrom(), $oFriend->getUserTo());
                E::ModuleStream()->SubscribeUser($oFriend->getUserTo(), $oFriend->getUserFrom());

                $oViewerLocal = $this->GetViewerLocal();
                $oViewerLocal->Assign('oUserFriend', $oFriend);
                E::ModuleViewer()->AssignAjax('sToggleText', $oViewerLocal->Fetch('actions/profile/action.profile.friend_item.tpl'));

            } else {
                E::ModuleMessage()->AddErrorSingle(
                    E::ModuleLang()->Get('system_error'),
                    E::ModuleLang()->Get('error')
                );
            }
            return;
        }

        E::ModuleMessage()->AddErrorSingle(
            E::ModuleLang()->Get('system_error'),
            E::ModuleLang()->Get('error')
        );
        return;
    }

    /**
     * Отправляет пользователю Talk уведомление о принятии или отклонении его заявки
     *
     * @param ModuleUser_EntityUser $oUser
     * @param string                $sAction
     *
     * @return bool
     */
    protected function NoticeFriendOffer($oUser, $sAction) {
        /**
         * Проверяем допустимость действия
         */
        if (!in_array($sAction, array('accept', 'reject'))) {
            return false;
        }
        /**
         * Проверяем настройки (нужно ли отправлять уведомление)
         */
        if (!Config::Get("module.user.friend_notice.{$sAction}")) {
            return false;
        }

        $sTitle = E::ModuleLang()->Get("user_friend_{$sAction}_notice_title");
        $sText = E::ModuleLang()->Get(
            "user_friend_{$sAction}_notice_text",
            array(
                 'login' => $this->oUserCurrent->getLogin(),
            )
        );
        $oTalk = E::ModuleTalk()->SendTalk($sTitle, $sText, $this->oUserCurrent, array($oUser), false, false);
        E::ModuleTalk()->DeleteTalkUserByArray($oTalk->getId(), $this->oUserCurrent->getId());
    }

    /**
     * Обработка Ajax добавления в друзья
     */
    public function EventAjaxFriendAdd() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        E::ModuleViewer()->SetResponseAjax('json');
        $sUserId = F::GetRequestStr('idUser');
        $sUserText = F::GetRequestStr('userText', '');
        /**
         * Если пользователь не авторизирован, возвращаем ошибку
         */
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('need_authorization'),
                E::ModuleLang()->Get('error')
            );
            return;
        }
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
        /**
         * При попытке добавить в друзья себя, возвращаем ошибку
         */
        if ($this->oUserCurrent->getId() == $sUserId) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('user_friend_add_self'),
                E::ModuleLang()->Get('error')
            );
            return;
        }
        /**
         * Если пользователь не найден, возвращаем ошибку
         */
        if (!$oUser = E::ModuleUser()->GetUserById($sUserId)) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('user_not_found'),
                E::ModuleLang()->Get('error')
            );
            return;
        }
        $this->oUserProfile = $oUser;
        /**
         * Получаем статус дружбы между пользователями
         */
        $oFriend = E::ModuleUser()->GetFriend($oUser->getId(), $this->oUserCurrent->getId());
        /**
         * Если связи ранее не было в базе данных, добавляем новую
         */
        if (!$oFriend) {
            $this->SubmitAddFriend($oUser, $sUserText, $oFriend);
            return;
        }
        /**
         * Если статус связи соответствует статусам отправленной и акцептованной заявки,
         * то предупреждаем что этот пользователь уже является нашим другом
         */
        if ($oFriend->getFriendStatus() == ModuleUser::USER_FRIEND_OFFER + ModuleUser::USER_FRIEND_ACCEPT) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('user_friend_already_exist'),
                E::ModuleLang()->Get('error')
            );
            return;
        }
        /**
         * Если пользователь ранее отклонил нашу заявку,
         * возвращаем сообщение об ошибке
         */
        if ($oFriend->getUserFrom() == $this->oUserCurrent->getId()
            && $oFriend->getStatusTo() == ModuleUser::USER_FRIEND_REJECT
        ) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('user_friend_offer_reject'),
                E::ModuleLang()->Get('error')
            );
            return;
        }
        /**
         * Если дружба была удалена, то проверяем кто ее удалил
         * и разрешаем восстановить только удалившему
         */
        if ($oFriend->getFriendStatus() > ModuleUser::USER_FRIEND_DELETE
            && $oFriend->getFriendStatus() < ModuleUser::USER_FRIEND_REJECT
        ) {
            /**
             * Определяем статус связи текущего пользователя
             */
            $iStatusCurrent = $oFriend->getStatusByUserId($this->oUserCurrent->getId());

            if ($iStatusCurrent == ModuleUser::USER_FRIEND_DELETE) {
                /**
                 * Меняем статус с удаленного, на акцептованное
                 */
                $oFriend->setStatusByUserId(ModuleUser::USER_FRIEND_ACCEPT, $this->oUserCurrent->getId());
                if (E::ModuleUser()->UpdateFriend($oFriend)) {
                    /**
                     * Добавляем событие в ленту
                     */
                    E::ModuleStream()->Write($oFriend->getUserFrom(), 'add_friend', $oFriend->getUserTo());
                    E::ModuleStream()->Write($oFriend->getUserTo(), 'add_friend', $oFriend->getUserFrom());
                    E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('user_friend_add_ok'), E::ModuleLang()->Get('attention'));

                    $oViewerLocal = $this->GetViewerLocal();
                    $oViewerLocal->Assign('oUserFriend', $oFriend);
                    E::ModuleViewer()->AssignAjax(
                        'sToggleText', $oViewerLocal->Fetch('actions/profile/action.profile.friend_item.tpl')
                    );

                } else {
                    E::ModuleMessage()->AddErrorSingle(
                        E::ModuleLang()->Get('system_error'),
                        E::ModuleLang()->Get('error')
                    );
                }
                return;
            } else {
                E::ModuleMessage()->AddErrorSingle(
                    E::ModuleLang()->Get('user_friend_add_deleted'),
                    E::ModuleLang()->Get('error')
                );
                return;
            }
        }
    }

    /**
     * Функция создает локальный объект вьювера для рендеринга html-объектов в ajax запросах
     *
     * @return ModuleViewer
     */
    protected function GetViewerLocal() {
        /**
         * Получаем HTML код inject-объекта
         */
        $oViewerLocal = E::ModuleViewer()->GetLocalViewer();
        $oViewerLocal->Assign('oUserCurrent', $this->oUserCurrent);
        $oViewerLocal->Assign('oUserProfile', $this->oUserProfile);

        $oViewerLocal->Assign('USER_FRIEND_NULL', ModuleUser::USER_FRIEND_NULL);
        $oViewerLocal->Assign('USER_FRIEND_OFFER', ModuleUser::USER_FRIEND_OFFER);
        $oViewerLocal->Assign('USER_FRIEND_ACCEPT', ModuleUser::USER_FRIEND_ACCEPT);
        $oViewerLocal->Assign('USER_FRIEND_REJECT', ModuleUser::USER_FRIEND_REJECT);
        $oViewerLocal->Assign('USER_FRIEND_DELETE', ModuleUser::USER_FRIEND_DELETE);

        return $oViewerLocal;
    }

    /**
     * Обработка добавления в друзья
     *
     * @param ModuleUser_EntityUser $oUser
     * @param string                $sUserText
     * @param ModuleUser_EntityUser $oFriend
     *
     * @return bool
     */
    protected function SubmitAddFriend($oUser, $sUserText, $oFriend = null) {
        /**
         * Ограничения на добавления в друзья, т.к. приглашение отправляется в личку, то и ограничиваем по ней
         */
        if (!E::ModuleACL()->CanSendTalkTime($this->oUserCurrent)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('user_friend_add_time_limit'), E::ModuleLang()->Get('error'));
            return false;
        }
        /**
         * Обрабатываем текст заявки
         */
        $sUserText = E::ModuleText()->Parser($sUserText);
        /**
         * Создаем связь с другом
         */
        /** @var ModuleUser_EntityFriend $oFriendNew */
        $oFriendNew = E::GetEntity('User_Friend');
        $oFriendNew->setUserTo($oUser->getId());
        $oFriendNew->setUserFrom($this->oUserCurrent->getId());

        // Добавляем заявку в друзья
        $oFriendNew->setStatusFrom(ModuleUser::USER_FRIEND_OFFER);
        $oFriendNew->setStatusTo(ModuleUser::USER_FRIEND_NULL);

        $bStateError = ($oFriend)
            ? !E::ModuleUser()->UpdateFriend($oFriendNew)
            : !E::ModuleUser()->AddFriend($oFriendNew);

        if (!$bStateError) {
            E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('user_friend_offer_send'), E::ModuleLang()->Get('attention'));

            $sTitle = E::ModuleLang()->Get(
                'user_friend_offer_title',
                array(
                     'login'  => $this->oUserCurrent->getLogin(),
                     'friend' => $oUser->getLogin()
                )
            );

            F::IncludeLib('XXTEA/encrypt.php');
            $sCode = $this->oUserCurrent->getId() . '_' . $oUser->getId();
            $sCode = rawurlencode(base64_encode(xxtea_encrypt($sCode, Config::Get('module.talk.encrypt'))));

            $aPath = array(
                'accept' => R::GetPath('profile') . 'friendoffer/accept/?code=' . $sCode,
                'reject' => R::GetPath('profile') . 'friendoffer/reject/?code=' . $sCode
            );

            $sText = E::ModuleLang()->Get(
                'user_friend_offer_text',
                array(
                     'login'       => $this->oUserCurrent->getLogin(),
                     'accept_path' => $aPath['accept'],
                     'reject_path' => $aPath['reject'],
                     'user_text'   => $sUserText
                )
            );
            $oTalk = E::ModuleTalk()->SendTalk($sTitle, $sText, $this->oUserCurrent, array($oUser), false, false);
            /**
             * Отправляем пользователю заявку
             */
            E::ModuleNotify()->SendUserFriendNew(
                $oUser, $this->oUserCurrent, $sUserText,
                R::GetPath('talk') . 'read/' . $oTalk->getId() . '/'
            );
            /**
             * Удаляем отправляющего юзера из переписки
             */
            E::ModuleTalk()->DeleteTalkUserByArray($oTalk->getId(), $this->oUserCurrent->getId());
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
        }

        /**
         * Подписываем запрашивающего дружбу на
         */
        E::ModuleStream()->SubscribeUser($this->oUserCurrent->getId(), $oUser->getId());

        $oViewerLocal = $this->GetViewerLocal();
        $oViewerLocal->Assign('oUserFriend', $oFriendNew);
        E::ModuleViewer()->AssignAjax('sToggleText', $oViewerLocal->Fetch('actions/profile/action.profile.friend_item.tpl'));
    }

    /**
     * Удаление пользователя из друзей
     */
    public function EventAjaxFriendDelete() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        E::ModuleViewer()->SetResponseAjax('json');
        $sUserId = F::GetRequestStr('idUser', null, 'post');
        /**
         * Если пользователь не авторизирован, возвращаем ошибку
         */
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('need_authorization'),
                E::ModuleLang()->Get('error')
            );
            return;
        }
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
        /**
         * При попытке добавить в друзья себя, возвращаем ошибку
         */
        if ($this->oUserCurrent->getId() == $sUserId) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('user_friend_add_self'),
                E::ModuleLang()->Get('error')
            );
            return;
        }
        /**
         * Если пользователь не найден, возвращаем ошибку
         */
        if (!$oUser = E::ModuleUser()->GetUserById($sUserId)) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('user_friend_del_no'),
                E::ModuleLang()->Get('error')
            );
            return;
        }
        $this->oUserProfile = $oUser;
        /**
         * Получаем статус дружбы между пользователями.
         * Если статус не определен, или отличается от принятой заявки,
         * возвращаем ошибку
         */
        $oFriend = E::ModuleUser()->GetFriend($oUser->getId(), $this->oUserCurrent->getId());
        $aAllowedFriendStatus = array(ModuleUser::USER_FRIEND_ACCEPT + ModuleUser::USER_FRIEND_OFFER,
                                      ModuleUser::USER_FRIEND_ACCEPT + ModuleUser::USER_FRIEND_ACCEPT);
        if (!$oFriend || !in_array($oFriend->getFriendStatus(), $aAllowedFriendStatus)) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('user_friend_del_no'),
                E::ModuleLang()->Get('error')
            );
            return;
        }
        /**
         * Удаляем из друзей
         */
        if (E::ModuleUser()->DeleteFriend($oFriend)) {
            E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('user_friend_del_ok'), E::ModuleLang()->Get('attention'));

            $oViewerLocal = $this->GetViewerLocal();
            $oViewerLocal->Assign('oUserFriend', $oFriend);
            E::ModuleViewer()->AssignAjax('sToggleText', $oViewerLocal->Fetch('actions/profile/action.profile.friend_item.tpl'));

            /**
             * Отправляем пользователю сообщение об удалении дружеской связи
             */
            if (Config::Get('module.user.friend_notice.delete')) {
                $sText = E::ModuleLang()->Get(
                    'user_friend_del_notice_text',
                    array(
                         'login' => $this->oUserCurrent->getLogin(),
                    )
                );
                $oTalk = E::ModuleTalk()->SendTalk(
                    E::ModuleLang()->Get('user_friend_del_notice_title'),
                    $sText, $this->oUserCurrent,
                    array($oUser), false, false
                );
                E::ModuleTalk()->DeleteTalkUserByArray($oTalk->getId(), $this->oUserCurrent->getId());
            }
            return;
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
    }

    /**
     * Обработка подтверждения старого емайла при его смене
     */
    public function EventChangemailConfirmFrom() {

        if (!($oChangemail = E::ModuleUser()->GetUserChangemailByCodeFrom($this->GetParamEventMatch(1, 0)))) {
            return parent::EventNotFound();
        }

        if ($oChangemail->getConfirmFrom() || strtotime($oChangemail->getDateExpired()) < time()) {
            return parent::EventNotFound();
        }

        $oChangemail->setConfirmFrom(1);
        E::ModuleUser()->UpdateUserChangemail($oChangemail);

        /**
         * Отправляем уведомление
         */
        $oUser = E::ModuleUser()->GetUserById($oChangemail->getUserId());
        E::ModuleNotify()->Send(
            $oChangemail->getMailTo(),
            'user_changemail_to.tpl',
            E::ModuleLang()->Get('notify_subject_user_changemail'),
            array(
                 'oUser'       => $oUser,
                 'oChangemail' => $oChangemail,
            ),
            null,
            true
        );

        E::ModuleViewer()->Assign('sText', E::ModuleLang()->Get('settings_profile_mail_change_to_notice'));
        // Исправление ошибки смены email {@link https://github.com/altocms/altocms/issues/260}
        E::ModuleViewer()->Assign('oUserProfile', $oUser);
        $this->SetTemplateAction('changemail_confirm');
    }

    /**
     * Обработка подтверждения нового емайла при смене старого
     */
    public function EventChangemailConfirmTo() {

        if (!($oChangemail = E::ModuleUser()->GetUserChangemailByCodeTo($this->GetParamEventMatch(1, 0)))) {
            return parent::EventNotFound();
        }

        if (!$oChangemail->getConfirmFrom() || $oChangemail->getConfirmTo() || strtotime($oChangemail->getDateExpired()) < time()) {
            return parent::EventNotFound();
        }

        $oChangemail->setConfirmTo(1);
        $oChangemail->setDateUsed(F::Now());
        E::ModuleUser()->UpdateUserChangemail($oChangemail);

        $oUser = E::ModuleUser()->GetUserById($oChangemail->getUserId());
        $oUser->setMail($oChangemail->getMailTo());
        E::ModuleUser()->Update($oUser);

        /**
         * Меняем емайл в подписках
         */
        if ($oChangemail->getMailFrom()) {
            E::ModuleSubscribe()->ChangeSubscribeMail(
                $oChangemail->getMailFrom(), $oChangemail->getMailTo(), $oUser->getId()
            );
        }

        E::ModuleViewer()->Assign(
            'sText', E::ModuleLang()->Get(
                'settings_profile_mail_change_ok', array('mail' => htmlspecialchars($oChangemail->getMailTo()))
            )
        );
        // Исправление ошибки смены email {@link https://github.com/altocms/altocms/issues/260}
        E::ModuleViewer()->Assign('oUserProfile', $oUser);
        $this->SetTemplateAction('changemail_confirm');
    }

    /**
     * Выполняется при завершении работы экшена
     */
    public function EventShutdown() {

        if (!$this->oUserProfile) {
            return;
        }
        $iProfileUserId = $this->oUserProfile->getId();

        // Get stats of various user publications topics, comments, images, etc. and stats of favourites
        $aProfileStats = E::ModuleUser()->GetUserProfileStats($iProfileUserId);

        // Получим информацию об изображениях пользователя
        /** @var ModuleMresource_EntityMresourceCategory[] $aUserImagesInfo */
        //$aUserImagesInfo = E::ModuleMresource()->GetAllImageCategoriesByUserId($iProfileUserId);

        // * Загружаем в шаблон необходимые переменные
        E::ModuleViewer()->Assign('oUserProfile', $this->oUserProfile);
        E::ModuleViewer()->Assign('aProfileStats', $aProfileStats);
        // unused
        //E::ModuleViewer()->Assign('aUserImagesInfo', $aUserImagesInfo);

        // * Заметка текущего пользователя о юзере
        if (E::User()) {
            E::ModuleViewer()->Assign('oUserNote', $this->oUserProfile->getUserNote());
        }

        E::ModuleViewer()->Assign('sMenuSubItemSelect', $this->sMenuSubItemSelect);
        E::ModuleViewer()->Assign('sMenuHeadItemSelect', $this->sMenuHeadItemSelect);

        E::ModuleViewer()->Assign('USER_FRIEND_NULL', ModuleUser::USER_FRIEND_NULL);
        E::ModuleViewer()->Assign('USER_FRIEND_OFFER', ModuleUser::USER_FRIEND_OFFER);
        E::ModuleViewer()->Assign('USER_FRIEND_ACCEPT', ModuleUser::USER_FRIEND_ACCEPT);
        E::ModuleViewer()->Assign('USER_FRIEND_REJECT', ModuleUser::USER_FRIEND_REJECT);
        E::ModuleViewer()->Assign('USER_FRIEND_DELETE', ModuleUser::USER_FRIEND_DELETE);

        // Old style skin compatibility
        E::ModuleViewer()->Assign('iCountTopicUser', $aProfileStats['count_topics']);
        E::ModuleViewer()->Assign('iCountCommentUser', $aProfileStats['count_comments']);
        E::ModuleViewer()->Assign('iCountTopicFavourite', $aProfileStats['favourite_topics']);
        E::ModuleViewer()->Assign('iCountCommentFavourite', $aProfileStats['favourite_comments']);
        E::ModuleViewer()->Assign('iCountNoteUser', $aProfileStats['count_usernotes']);
        E::ModuleViewer()->Assign('iCountWallUser', $aProfileStats['count_wallrecords']);

        E::ModuleViewer()->Assign('iPhotoCount', $aProfileStats['count_images']);
        E::ModuleViewer()->Assign('iCountCreated', $aProfileStats['count_created']);

        E::ModuleViewer()->Assign('iCountFavourite', $aProfileStats['count_favourites']);
        E::ModuleViewer()->Assign('iCountFriendsUser', $aProfileStats['count_friends']);
    }

}

// EOF