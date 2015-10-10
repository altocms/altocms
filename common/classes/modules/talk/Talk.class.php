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
 * Модуль разговоров(почта)
 *
 * @package modules.talk
 * @since   1.0
 */
class ModuleTalk extends Module {
    /**
     * Статус TalkUser в базе данных
     * Пользователь активен в разговоре
     */
    const TALK_USER_ACTIVE = 1;
    /**
     * Пользователь удалил разговор
     */
    const TALK_USER_DELETE_BY_SELF = 2;
    /**
     * Пользователя удалил из разговора автор письма
     */
    const TALK_USER_DELETE_BY_AUTHOR = 4;

    /**
     * Объект маппера
     *
     * @var ModuleTalk_MapperTalk
     */
    protected $oMapper;
    /**
     * Объект текущего пользователя
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent = null;

    protected $aAdditionalData = array('user', 'talk_user', 'favourite', 'comment_last');

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->oMapper = E::GetMapper(__CLASS__);
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
    }

    /**
     * Формирует и отправляет личное сообщение
     *
     * @param string                          $sTitle           Заголовок сообщения
     * @param string                          $sText            Текст сообщения
     * @param int|ModuleUser_EntityUser       $oUserFrom        Пользователь от которого отправляем
     * @param array|int|ModuleUser_EntityUser $aUserTo          Пользователь которому отправляем
     * @param bool                            $bSendNotify      Отправлять или нет уведомление на емайл
     * @param bool                            $bUseBlacklist    Исклюать или нет пользователей из блэклиста
     *
     * @return ModuleTalk_EntityTalk|bool
     */
    public function SendTalk($sTitle, $sText, $oUserFrom, $aUserTo, $bSendNotify = true, $bUseBlacklist = true) {

        $iUserIdFrom = $oUserFrom instanceof ModuleUser_EntityUser ? $oUserFrom->getId() : (int)$oUserFrom;
        if (!is_array($aUserTo)) {
            $aUserTo = array($aUserTo);
        }
        $aUserIdTo = array($iUserIdFrom);
        if ($bUseBlacklist) {
            $aUserInBlacklist = $this->GetBlacklistByTargetId($iUserIdFrom);
        }

        foreach ($aUserTo as $oUserTo) {
            $nUserIdTo = $oUserTo instanceof ModuleUser_EntityUser ? $oUserTo->getId() : (int)$oUserTo;
            if (!$bUseBlacklist || !in_array($nUserIdTo, $aUserInBlacklist)) {
                $aUserIdTo[] = $nUserIdTo;
            }
        }
        $aUserIdTo = array_unique($aUserIdTo);
        if (!empty($aUserIdTo)) {
            $oTalk = E::GetEntity('Talk');
            $oTalk->setUserId($iUserIdFrom);
            $oTalk->setTitle($sTitle);
            $oTalk->setText($sText);
            $oTalk->setDate(date('Y-m-d H:i:s'));
            $oTalk->setDateLast(date('Y-m-d H:i:s'));
            $oTalk->setUserIdLast($oTalk->getUserId());
            $oTalk->setUserIp(F::GetUserIp());
            if ($oTalk = $this->AddTalk($oTalk)) {
                foreach ($aUserIdTo as $iUserId) {
                    $oTalkUser = E::GetEntity('Talk_TalkUser');
                    $oTalkUser->setTalkId($oTalk->getId());
                    $oTalkUser->setUserId($iUserId);
                    if ($iUserId == $iUserIdFrom) {
                        $oTalkUser->setDateLast(date('Y-m-d H:i:s'));
                    } else {
                        $oTalkUser->setDateLast(null);
                    }
                    $this->AddTalkUser($oTalkUser);

                    if ($bSendNotify) {
                        if ($iUserId != $iUserIdFrom) {
                            $oUserFrom = E::ModuleUser()->GetUserById($iUserIdFrom);
                            $oUserToMail = E::ModuleUser()->GetUserById($iUserId);
                            E::ModuleNotify()->SendTalkNew($oUserToMail, $oUserFrom, $oTalk);
                        }
                    }
                }
                return $oTalk;
            }
        }
        return false;
    }

    /**
     * Добавляет новую тему разговора
     *
     * @param ModuleTalk_EntityTalk $oTalk Объект сообщения
     *
     * @return ModuleTalk_EntityTalk|bool
     */
    public function AddTalk($oTalk) {

        if ($nTalkId = $this->oMapper->AddTalk($oTalk)) {
            $oTalk->setId($nTalkId);
            //чистим зависимые кеши
            E::ModuleCache()->Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('talk_new', "talk_new_user_{$oTalk->getUserId()}"));

            return $oTalk;
        }
        return false;
    }

    /**
     * Обновление разговора
     *
     * @param ModuleTalk_EntityTalk $oTalk    Объект сообщения
     *
     * @return int
     */
    public function UpdateTalk($oTalk) {

        $xResult = $this->oMapper->UpdateTalk($oTalk);
        E::ModuleCache()->Delete("talk_{$oTalk->getId()}");

        return $xResult;
    }

    /**
     * Получает дополнительные данные(объекты) для разговоров по их ID
     *
     * @param array      $aTalkId       Список ID сообщений
     * @param array|null $aAllowData    Список дополнительных типов подгружаемых в объект
     *
     * @return array
     */
    public function GetTalksAdditionalData($aTalkId, $aAllowData = null) {

        if (!$aTalkId) {
            return array();
        }
        if (is_null($aAllowData)) {
            $aAllowData = $this->aAdditionalData;
        }
        $aAllowData = F::Array_FlipIntKeys($aAllowData);
        if (!is_array($aTalkId)) {
            $aTalkId = array($aTalkId);
        }

        // * Получаем "голые" разговоры
        $aTalks = $this->GetTalksByArrayId($aTalkId);

        // * Формируем ID дополнительных данных, которые нужно получить
        if (isset($aAllowData['favourite']) && $this->oUserCurrent) {
            $aFavouriteTalks = E::ModuleFavourite()->GetFavouritesByArray($aTalkId, 'talk', $this->oUserCurrent->getId());
        }

        $aUserId = array();
        $aCommentLastId = array();
        foreach ($aTalks as $oTalk) {
            if (isset($aAllowData['user'])) {
                $aUserId[] = $oTalk->getUserId();
            }
            if (isset($aAllowData['comment_last']) && $oTalk->getCommentIdLast()) {
                $aCommentLastId[] = $oTalk->getCommentIdLast();
            }
        }

        // * Получаем дополнительные данные
        $aTalkUsers = array();
        $aCommentLast = array();
        if (isset($aAllowData['user']) && is_array($aAllowData['user'])) {
            $aUsers = E::ModuleUser()->GetUsersAdditionalData($aUserId, $aAllowData['user']);
        } else {
            $aUsers = E::ModuleUser()->GetUsersAdditionalData($aUserId);
        }

        if (isset($aAllowData['talk_user']) && $this->oUserCurrent) {
            $aTalkUsers = $this->GetTalkUsersByArray($aTalkId, $this->oUserCurrent->getId());
        }
        if (isset($aAllowData['comment_last'])) {
            $aCommentLast = E::ModuleComment()->GetCommentsAdditionalData($aCommentLastId, array());
        }

        // Добавляем данные к результату - списку разговоров
        /** @var ModuleTalk_EntityTalk $oTalk */
        foreach ($aTalks as $oTalk) {
            if (isset($aUsers[$oTalk->getUserId()])) {
                $oTalk->setUser($aUsers[$oTalk->getUserId()]);
            } else {
                $oTalk->setUser(null); // или $oTalk->setUser(new ModuleUser_EntityUser());
            }

            if (isset($aTalkUsers[$oTalk->getId()])) {
                $oTalk->setTalkUser($aTalkUsers[$oTalk->getId()]);
            } else {
                $oTalk->setTalkUser(null);
            }

            if (isset($aFavouriteTalks[$oTalk->getId()])) {
                $oTalk->setIsFavourite(true);
            } else {
                $oTalk->setIsFavourite(false);
            }

            if ($oTalk->getCommentIdLast() && isset($aCommentLast[$oTalk->getCommentIdLast()])) {
                $oTalk->setCommentLast($aCommentLast[$oTalk->getCommentIdLast()]);
            } else {
                $oTalk->setCommentLast(null);
            }
        }
        return $aTalks;
    }

    /**
     * Получить список разговоров по списку айдишников
     *
     * @param array $aTalksId    Список ID сообщений
     *
     * @return array
     */
    public function GetTalksByArrayId($aTalksId) {

        if (Config::Get('sys.cache.solid')) {
            return $this->GetTalksByArrayIdSolid($aTalksId);
        }
        if (!is_array($aTalksId)) {
            $aTalksId = array($aTalksId);
        }
        $aTalksId = array_unique($aTalksId);
        $aTalks = array();
        $aTalkIdNotNeedQuery = array();

        // Делаем мульти-запрос к кешу
        $aCacheKeys = F::Array_ChangeValues($aTalksId, 'talk_');
        if (false !== ($data = E::ModuleCache()->Get($aCacheKeys))) {

            // проверяем что досталось из кеша
            foreach ($aCacheKeys as $iIndex => $sKey) {
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aTalks[$data[$sKey]->getId()] = $data[$sKey];
                    } else {
                        $aTalkIdNotNeedQuery[] = $aTalksId[$iIndex];
                    }
                }
            }
        }

        // Смотрим каких разговоров не было в кеше и делаем запрос в БД
        $aTalkIdNeedQuery = array_diff($aTalksId, array_keys($aTalks));
        $aTalkIdNeedQuery = array_diff($aTalkIdNeedQuery, $aTalkIdNotNeedQuery);
        $aTalkIdNeedStore = $aTalkIdNeedQuery;

        if ($aTalkIdNeedQuery) {
            if ($data = $this->oMapper->GetTalksByArrayId($aTalkIdNeedQuery)) {
                /** @var ModuleTalk_EntityTalk $oTalk */
                foreach ($data as $oTalk) {

                    // Добавляем к результату и сохраняем в кеш
                    $aTalks[$oTalk->getId()] = $oTalk;
                    E::ModuleCache()->Set($oTalk, "talk_{$oTalk->getId()}", array(), 60 * 60 * 24 * 4);
                    $aTalkIdNeedStore = array_diff($aTalkIdNeedStore, array($oTalk->getId()));
                }
            }
        }

        // Сохраняем в кеш запросы не вернувшие результата
        foreach ($aTalkIdNeedStore as $sId) {
            E::ModuleCache()->Set(null, "talk_{$sId}", array(), 60 * 60 * 24 * 4);
        }

        // Сортируем результат согласно входящему массиву
        $aTalks = F::Array_SortByKeysArray($aTalks, $aTalksId);

        return $aTalks;
    }

    /**
     * Получить список разговоров по списку айдишников, используя общий кеш
     *
     * @param array $aTalkId    Список ID сообщений
     *
     * @return array
     */
    public function GetTalksByArrayIdSolid($aTalkId) {

        if (!is_array($aTalkId)) {
            $aTalkId = array($aTalkId);
        }
        $aTalkId = array_unique($aTalkId);
        $aTalks = array();
        $sCacheKey = 'talk_id_' . join(',', $aTalkId);
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetTalksByArrayId($aTalkId);
            foreach ($data as $oTalk) {
                $aTalks[$oTalk->getId()] = $oTalk;
            }
            E::ModuleCache()->Set($aTalks, $sCacheKey, array("update_talk_user", "talk_new"), 'P1D');

            return $aTalks;
        }
        return $data;
    }

    /**
     * Получить список отношений разговор-юзер по списку айдишников
     *
     * @param array $aTalksId    Список ID сообщений
     * @param int   $iUserId    ID пользователя
     *
     * @return array
     */
    public function GetTalkUsersByArray($aTalksId, $iUserId) {

        if (!is_array($aTalksId)) {
            $aTalksId = array($aTalksId);
        }
        $aTalksId = array_unique($aTalksId);
        $aTalkUsers = array();
        $aTalkIdNotNeedQuery = array();

        // Делаем мульти-запрос к кешу
        $aCacheKeys = F::Array_ChangeValues($aTalksId, 'talk_user_', '_' . $iUserId);
        if (false !== ($data = E::ModuleCache()->Get($aCacheKeys))) {

            // проверяем что досталось из кеша
            foreach ($aCacheKeys as $iIndex => $sKey) {
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aTalkUsers[$data[$sKey]->getTalkId()] = $data[$sKey];
                    } else {
                        $aTalkIdNotNeedQuery[] = $aTalksId[$iIndex];
                    }
                }
            }
        }

        // Смотрим чего не было в кеше и делаем запрос в БД
        $aTalkIdNeedQuery = array_diff($aTalksId, array_keys($aTalkUsers));
        $aTalkIdNeedQuery = array_diff($aTalkIdNeedQuery, $aTalkIdNotNeedQuery);
        $aTalkIdNeedStore = $aTalkIdNeedQuery;

        if ($aTalkIdNeedQuery) {
            if ($data = $this->oMapper->GetTalkUserByArray($aTalkIdNeedQuery, $iUserId)) {
                foreach ($data as $oTalkUser) {
                    // Добавляем к результату и сохраняем в кеш
                    $aTalkUsers[$oTalkUser->getTalkId()] = $oTalkUser;
                    E::ModuleCache()->Set(
                        $oTalkUser, "talk_user_{$oTalkUser->getTalkId()}_{$oTalkUser->getUserId()}",
                        array("update_talk_user_{$oTalkUser->getTalkId()}"), 60 * 60 * 24 * 4
                    );
                    $aTalkIdNeedStore = array_diff($aTalkIdNeedStore, array($oTalkUser->getTalkId()));
                }
            }
        }

        // Сохраняем в кеш запросы не вернувшие результата
        foreach ($aTalkIdNeedStore as $sId) {
            E::ModuleCache()->Set(null, "talk_user_{$sId}_{$iUserId}", array("update_talk_user_{$sId}"), 60 * 60 * 24 * 4);
        }

        // Сортируем результат согласно входящему массиву
        $aTalkUsers = F::Array_SortByKeysArray($aTalkUsers, $aTalksId);

        return $aTalkUsers;
    }

    /**
     * Получает тему разговора по айдишнику
     *
     * @param int $nTalkId    ID сообщения
     *
     * @return ModuleTalk_EntityTalk|null
     */
    public function GetTalkById($nTalkId) {

        if (!intval($nTalkId)) {
            return null;
        }
        $aTalks = $this->GetTalksAdditionalData($nTalkId);
        if (isset($aTalks[$nTalkId])) {
            $aResult = (array)$this->GetTalkUsersByTalkId($nTalkId);
            foreach ($aResult as $oTalkUser) {
                $aTalkUsers[$oTalkUser->getUserId()] = $oTalkUser;
            }
            $aTalks[$nTalkId]->setTalkUsers($aTalkUsers);

            return $aTalks[$nTalkId];
        }
        return null;
    }

    /**
     * Добавляет юзера к разговору (теме)
     *
     * @param ModuleTalk_EntityTalkUser $oTalkUser    Объект связи пользователя и сообщения(разговора)
     *
     * @return bool
     */
    public function AddTalkUser($oTalkUser) {

        $xResult = $this->oMapper->AddTalkUser($oTalkUser);

        $iTalkId = is_object($oTalkUser) ? $oTalkUser->getTalkId() : 0;
        E::ModuleCache()->Delete("talk_{$iTalkId}");
        E::ModuleCache()->Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("update_talk_user_{$iTalkId}"));

        return $xResult;
    }

    /**
     * Помечает разговоры как прочитанные
     *
     * @param array $aTalkId    Список ID сообщений
     * @param int   $nUserId    ID пользователя
     */
    public function MarkReadTalkUserByArray($aTalkId, $nUserId) {

        if (!is_array($aTalkId)) {
            $aTalkId = array($aTalkId);
        }
        foreach ($aTalkId as $sTalkId) {
            if ($oTalk = $this->GetTalkById((string)$sTalkId)) {
                if ($oTalkUser = $this->GetTalkUser($oTalk->getId(), $nUserId)) {
                    $oTalkUser->setDateLast(date('Y-m-d H:i:s'));
                    if ($oTalk->getCommentIdLast()) {
                        $oTalkUser->setCommentIdLast($oTalk->getCommentIdLast());
                    }
                    $oTalkUser->setCommentCountNew(0);
                    $this->UpdateTalkUser($oTalkUser);
                }
            }
        }
    }

    /**
     * Помечает разговоры как непрочитанные
     *
     * @param array $aTalkId    Список ID сообщений
     * @param int   $nUserId    ID пользователя
     */
    public function MarkUnreadTalkUserByArray($aTalkId, $nUserId) {

        if (!is_array($aTalkId)) {
            $aTalkId = array((int)$aTalkId);
        }
        foreach ($aTalkId as $nTalkId) {
            if (intval($nTalkId) && ($oTalk = $this->GetTalkById($nTalkId))) {
                if ($oTalkUser = $this->GetTalkUser($oTalk->getId(), $nUserId)) {
                    $oTalkUser->setDateLast($oTalk->getTalkDate());
                    $oTalkUser->setCommentIdLast('0');
                    $oTalkUser->setCommentCountNew($oTalk->getCountComment());
                    $this->UpdateTalkUser($oTalkUser);
                }
            }
        }
    }

    /**
     * Удаляет юзера из разговора
     *
     * @param array|int $aTalkId Список ID сообщений
     * @param int       $nUserId ID пользователя
     * @param int       $iActive Статус связи
     *
     * @return bool
     */
    public function DeleteTalkUserByArray($aTalkId, $nUserId, $iActive = self::TALK_USER_DELETE_BY_SELF) {

        if (!is_array($aTalkId)) {
            $aTalkId = array($aTalkId);
        }
        // Удаляем для каждого отметку избранного
        foreach ($aTalkId as $nTalkId) {
            $this->DeleteFavouriteTalk(
                E::GetEntity(
                    'Favourite',
                    array(
                         'target_id'   => intval($nTalkId),
                         'target_type' => 'talk',
                         'user_id'     => $nUserId
                    )
                )
            );
        }
        $xResult = $this->oMapper->DeleteTalkUserByArray($aTalkId, $nUserId, $iActive);

        // Нужно почистить зависимые кеши
        foreach ($aTalkId as $nTalkId) {
            E::ModuleCache()->Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG,array("update_talk_user_{$nTalkId}"));
        }
        E::ModuleCache()->Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("update_talk_user"));

        // Удаляем пустые беседы, если в них нет пользователей
        foreach ($aTalkId as $nTalkId) {
            $nTalkId = (string)$nTalkId;
            if (!count($this->GetUsersTalk($nTalkId, array(self::TALK_USER_ACTIVE)))) {
                $this->DeleteTalk($nTalkId);
            }
        }
        return $xResult;
    }

    /**
     * Есть ли юзер в этом разговоре
     *
     * @param int $nTalkId    ID разговора
     * @param int $nUserId    ID пользователя
     *
     * @return ModuleTalk_EntityTalkUser|null
     */
    public function GetTalkUser($nTalkId, $nUserId) {

        $aTalkUser = $this->GetTalkUsersByArray($nTalkId, $nUserId);
        if (isset($aTalkUser[$nTalkId])) {
            return $aTalkUser[$nTalkId];
        }
        return null;
    }

    /**
     * Получить все темы разговора где есть юзер
     *
     * @param  int $nUserId     ID пользователя
     * @param  int $iPage       Номер страницы
     * @param  int $iPerPage    Количество элементов на страницу
     *
     * @return array('collection'=>array,'count'=>int)
     */
    public function GetTalksByUserId($nUserId, $iPage, $iPerPage) {

        $data = array(
            'collection' => $this->oMapper->GetTalksByUserId($nUserId, $iCount, $iPage, $iPerPage),
            'count'      => $iCount
        );
        if ($data['collection']) {
            $aTalks = $this->GetTalksAdditionalData($data['collection']);

            // Добавляем данные об участниках разговора
            /** @var ModuleTalk_EntityTalk $oTalk */
            foreach ($aTalks as $oTalk) {
                $aResult = (array)$this->GetTalkUsersByTalkId($oTalk->getId());
                $aTalkUsers = array();
                foreach ($aResult as $oTalkUser) {
                    $aTalkUsers[$oTalkUser->getUserId()] = $oTalkUser;
                }
                $oTalk->setTalkUsers($aTalkUsers);
                unset($aTalkUsers);
            }
            $data['collection'] = $aTalks;
        }
        return $data;
    }

    /**
     * Получить все темы разговора по фильтру
     *
     * @param  array $aFilter     Фильтр
     * @param  int   $iPage       Номер страницы
     * @param  int   $iPerPage    Количество элементов на страницу
     *
     * @return array('collection'=>array,'count'=>int)
     */
    public function GetTalksByFilter($aFilter, $iPage, $iPerPage) {

        $data = array(
            'collection' => $this->oMapper->GetTalksByFilter($aFilter, $iCount, $iPage, $iPerPage),
            'count'      => $iCount
        );
        if ($data['collection']) {
            $aTalks = $this->GetTalksAdditionalData($data['collection']);

            // * Добавляем данные об участниках разговора
            /** @var ModuleTalk_EntityTalk $oTalk */
            foreach ($aTalks as $oTalk) {
                $aResult = (array)$this->GetTalkUsersByTalkId($oTalk->getId());
                $aTalkUsers = array();
                foreach ($aResult as $oTalkUser) {
                    $aTalkUsers[$oTalkUser->getUserId()] = $oTalkUser;
                }
                $oTalk->setTalkUsers($aTalkUsers);
            }
            $data['collection'] = $aTalks;
        }
        return $data;
    }

    /**
     * Обновляет связку разговор-юзер
     *
     * @param ModuleTalk_EntityTalkUser $oTalkUser    Объект связи пользователя с разговором
     *
     * @return bool
     */
    public function UpdateTalkUser($oTalkUser) {

        $xResult = $this->oMapper->UpdateTalkUser($oTalkUser);

        //чистим зависимые кеши
        E::ModuleCache()->Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("talk_read_user_{$oTalkUser->getUserId()}"));
        E::ModuleCache()->Delete("talk_user_{$oTalkUser->getTalkId()}_{$oTalkUser->getUserId()}");

        return $xResult;
    }

    /**
     * Получает число новых тем и комментов где есть юзер
     *
     * @param int|object $xUser    ID пользователя
     *
     * @return int
     */
    public function GetCountTalkNew($xUser) {

        $nUserId = is_object($xUser) ? $xUser->getId() : intval($xUser);

        $sCacheKey = "talk_count_all_new_user_{$nUserId}";
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetCountCommentNew($nUserId) + $this->oMapper->GetCountTalkNew($nUserId);
            E::ModuleCache()->Set($data, $sCacheKey, array("talk_new", "update_talk_user", "talk_read_user_{$nUserId}"), 'P1D');
        }
        return $data;
    }

    /**
     * Получает список юзеров в теме разговора
     *
     * @param  int   $nTalkId    ID разговора
     * @param  array $aActive    Список статусов
     *
     * @return array
     */
    public function GetUsersTalk($nTalkId, $aActive = array()) {

        if (!is_array($aActive)) {
            $aActive = array($aActive);
        }

        $data = $this->oMapper->GetUsersTalk($nTalkId, $aActive);

        return E::ModuleUser()->GetUsersAdditionalData($data);
    }

    /**
     * Возвращает массив пользователей, участвующих в разговоре
     *
     * @param  int $nTalkId    ID разговора
     *
     * @return array
     */
    public function GetTalkUsersByTalkId($nTalkId) {

        $sCacheKey = "talk_relation_user_by_talk_id_{$nTalkId}";
        if (false === ($aTalkUsers = E::ModuleCache()->Get($sCacheKey))) {
            $aTalkUsers = $this->oMapper->GetTalkUsers($nTalkId);
            E::ModuleCache()->Set($aTalkUsers, $sCacheKey, array("update_talk_user_{$nTalkId}"), 'P1D');
        }

        if ($aTalkUsers) {
            $aUserId = array();
            foreach ($aTalkUsers as $oTalkUser) {
                $aUserId[] = $oTalkUser->getUserId();
            }
            $aUsers = E::ModuleUser()->GetUsersAdditionalData($aUserId);

            foreach ($aTalkUsers as $oTalkUser) {
                if (isset($aUsers[$oTalkUser->getUserId()])) {
                    $oTalkUser->setUser($aUsers[$oTalkUser->getUserId()]);
                } else {
                    $oTalkUser->setUser(null);
                }
            }
        }
        return $aTalkUsers;
    }

    /**
     * Увеличивает число новых комментов у юзеров
     *
     * @param int   $nTalkId       ID разговора
     * @param array $aExcludeId    Список ID пользователей для исключения
     *
     * @return int
     */
    public function increaseCountCommentNew($nTalkId, $aExcludeId = null) {

        $xResult = $this->oMapper->increaseCountCommentNew($nTalkId, $aExcludeId);

        E::ModuleCache()->Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("update_talk_user_{$nTalkId}"));
        E::ModuleCache()->Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("update_talk_user"));

        return $xResult;
    }

    /**
     * Получает привязку письма к ибранному(добавлено ли письмо в избранное у юзера)
     *
     * @param  int $sTalkId    ID разговора
     * @param  int $nUserId    ID пользователя
     *
     * @return ModuleFavourite_EntityFavourite|null
     */
    public function GetFavouriteTalk($sTalkId, $nUserId) {

        return E::ModuleFavourite()->GetFavourite($sTalkId, 'talk', $nUserId);
    }

    /**
     * Получить список избранного по списку айдишников
     *
     * @param array $aTalkId    Список ID разговоров
     * @param int   $nUserId    ID пользователя
     *
     * @return array
     */
    public function GetFavouriteTalkByArray($aTalkId, $nUserId) {

        return E::ModuleFavourite()->GetFavouritesByArray($aTalkId, 'talk', $nUserId);
    }

    /**
     * Получить список избранного по списку айдишников, но используя единый кеш
     *
     * @param array $aTalkId    Список ID разговоров
     * @param int   $nUserId    ID пользователя
     *
     * @return array
     */
    public function GetFavouriteTalksByArraySolid($aTalkId, $nUserId) {

        return E::ModuleFavourite()->GetFavouritesByArraySolid($aTalkId, 'talk', $nUserId);
    }

    /**
     * Получает список писем из избранного пользователя
     *
     * @param  int $nUserId      ID пользователя
     * @param  int $iCurrPage    Номер текущей страницы
     * @param  int $iPerPage     Количество элементов на страницу
     *
     * @return array('collection'=>array,'count'=>int)
     */
    public function GetTalksFavouriteByUserId($nUserId, $iCurrPage, $iPerPage) {

        // Получаем список идентификаторов избранных комментов
        $data = E::ModuleFavourite()->GetFavouritesByUserId($nUserId, 'talk', $iCurrPage, $iPerPage);

        if ($data['collection']) {
            // Получаем комменты по переданому массиву айдишников
            $aTalks = $this->GetTalksAdditionalData($data['collection']);

            // * Добавляем данные об участниках разговора
            /** @var ModuleTalk_EntityTalk $oTalk */
            foreach ($aTalks as $oTalk) {
                $aResult = $this->GetTalkUsersByTalkId($oTalk->getId());
                $aTalkUsers = array();
                foreach ((array)$aResult as $oTalkUser) {
                    $aTalkUsers[$oTalkUser->getUserId()] = $oTalkUser;
                }
                $oTalk->setTalkUsers($aTalkUsers);
            }
            $data['collection'] = $aTalks;
        }
        return $data;
    }

    /**
     * Возвращает число писем в избранном
     *
     * @param  int $nUserId ID пользователя
     *
     * @return int
     */
    public function GetCountTalksFavouriteByUserId($nUserId) {

        return E::ModuleFavourite()->GetCountFavouritesByUserId($nUserId, 'talk');
    }

    /**
     * Добавляет письмо в избранное
     *
     * @param  ModuleFavourite_EntityFavourite $oFavourite    Объект избранного
     *
     * @return bool
     */
    public function AddFavouriteTalk(ModuleFavourite_EntityFavourite $oFavourite) {

        return ($oFavourite->getTargetType() == 'talk')
            ? E::ModuleFavourite()->AddFavourite($oFavourite)
            : false;
    }

    /**
     * Удаляет письмо из избранного
     *
     * @param  ModuleFavourite_EntityFavourite $oFavourite    Объект избранного
     *
     * @return bool
     */
    public function DeleteFavouriteTalk(ModuleFavourite_EntityFavourite $oFavourite) {

        return ($oFavourite->getTargetType() == 'talk')
            ? E::ModuleFavourite()->DeleteFavourite($oFavourite)
            : false;
    }

    /**
     * Получает информацию о пользователях, занесенных в блеклист
     *
     * @param  int $nUserId    ID пользователя
     *
     * @return array
     */
    public function GetBlacklistByUserId($nUserId) {

        $data = $this->oMapper->GetBlacklistByUserId($nUserId);
        return E::ModuleUser()->GetUsersAdditionalData($data);
    }

    /**
     * Возвращает пользователей, у которых данный занесен в Blacklist
     *
     * @param  int $nUserId ID пользователя
     *
     * @return array
     */
    public function GetBlacklistByTargetId($nUserId) {

        return $this->oMapper->GetBlacklistByTargetId($nUserId);
    }

    /**
     * Добавление пользователя в блеклист по переданному идентификатору
     *
     * @param  int $nTargetId    ID пользователя, которого добавляем в блэклист
     * @param  int $nUserId      ID пользователя
     *
     * @return bool
     */
    public function AddUserToBlacklist($nTargetId, $nUserId) {

        return $this->oMapper->AddUserToBlacklist($nTargetId, $nUserId);
    }

    /**
     * Добавление пользователя в блеклист по списку идентификаторов
     *
     * @param  array $aTargetId    Список ID пользователей, которых добавляем в блэклист
     * @param  int   $nUserId      ID пользователя
     *
     * @return bool
     */
    public function AddUserArrayToBlacklist($aTargetId, $nUserId) {

        foreach ((array)$aTargetId as $oUser) {
            $aUsersId[] = $oUser instanceof ModuleUser_EntityUser ? $oUser->getId() : (int)$oUser;
        }
        return $this->oMapper->AddUserArrayToBlacklist($aUsersId, $nUserId);
    }

    /**
     * Удаляем пользователя из блеклиста
     *
     * @param  int $sTargetId    ID пользователя, которого удаляем из блэклиста
     * @param  int $nUserId      ID пользователя
     *
     * @return bool
     */
    public function DeleteUserFromBlacklist($sTargetId, $nUserId) {

        return $this->oMapper->DeleteUserFromBlacklist($sTargetId, $nUserId);
    }

    /**
     * Возвращает список последних инбоксов пользователя,
     * отправленных не более чем $iTimeLimit секунд назад
     *
     * @param  int $nUserId        ID пользователя
     * @param  int $iTimeLimit     Количество секунд
     * @param  int $iCountLimit    Количество
     *
     * @return array
     */
    public function GetLastTalksByUserId($nUserId, $iTimeLimit, $iCountLimit = 1) {

        $aFilter = array(
            'sender_id' => $nUserId,
            'date_min'  => date('Y-m-d H:i:s', time() - $iTimeLimit),
        );
        $aTalks = $this->GetTalksByFilter($aFilter, 1, $iCountLimit);

        return $aTalks;
    }

    /**
     * Удаление письма из БД
     *
     * @param int $iTalkId    ID разговора
     */
    public function DeleteTalk($iTalkId) {

        $this->oMapper->DeleteTalk($iTalkId);
        /**
         * Удаляем комментарии к письму.
         * При удалении комментариев они удаляются из избранного,прямого эфира и голоса за них
         */
        E::ModuleComment()->DeleteCommentByTargetId($iTalkId, 'talk');
    }
}

// EOF
