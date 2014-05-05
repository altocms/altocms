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
 * Модуль для работы с пользователями
 *
 * @package modules.user
 * @since   1.0
 */
class ModuleUser extends Module {

    const USER_SESSION_KEY = 'user_key';

    /**
     * Статусы дружбы между пользователями
     */
    const USER_FRIEND_OFFER = 1;
    const USER_FRIEND_ACCEPT = 2;
    const USER_FRIEND_DELETE = 4;
    const USER_FRIEND_REJECT = 8;
    const USER_FRIEND_NULL = 16;

    /**
     * Объект маппера
     *
     * @var ModuleUser_MapperUser
     */
    protected $oMapper;

    /**
     * Объект текущего пользователя
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent = null;

    /**
     * Объект сессии текущего пользователя
     *
     * @var ModuleUser_EntitySession|null
     */
    protected $oSession = null;

    /**
     * Список типов пользовательских полей
     *
     * @var array
     */
    protected $aUserFieldTypes
        = array(
            'social', 'contact'
        );

    protected $aAdditionalData = array('vote', 'session', 'friend', 'geo_target', 'note');

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->oMapper = Engine::GetMapper(__CLASS__);

        // * Проверяем есть ли у юзера сессия, т.е. залогинен или нет
        $nUserId = intval($this->Session_Get('user_id'));
        if ($nUserId && ($oUser = $this->GetUserById($nUserId)) && $oUser->getActivate()) {
            if ($this->oSession = $oUser->getSession()) {
                if ($this->oSession->GetSessionExit()) {
                    // Сессия была закрыта
                    $this->Logout();
                    return;
                }
                /**
                 * Сюда можно вставить условие на проверку айпишника сессии
                 */
                $this->oUserCurrent = $oUser;
            }
        }
        /**
         * Запускаем автозалогинивание
         * В куках стоит время на сколько запоминать юзера
         */
        $this->AutoLogin();

        // * Обновляем сессию
        if (isset($this->oSession)) {
            $this->UpdateSession();
        }
    }

    /**
     * Возвращает список типов полей
     *
     * @return array
     */
    public function GetUserFieldTypes() {

        return $this->aUserFieldTypes;
    }

    /**
     * Добавляет новый тип с пользовательские поля
     *
     * @param string $sType    Тип
     *
     * @return bool
     */
    public function AddUserFieldTypes($sType) {

        if (!in_array($sType, $this->aUserFieldTypes)) {
            $this->aUserFieldTypes[] = $sType;
            return true;
        }
        return false;
    }

    /**
     * Получает дополнительные данные(объекты) для юзеров по их ID
     *
     * @param array $aUsersId   - Список ID пользователей
     * @param array $aAllowData - Список типоd дополнительных данных для подгрузки у пользователей
     *
     * @return array
     */
    public function GetUsersAdditionalData($aUsersId, $aAllowData = null) {

        if (!$aUsersId) {
            return array();
        } elseif (!is_array($aUsersId)) {
            $aUsersId = array($aUsersId);
        } else {
            $aUsersId = array_unique($aUsersId);
        }

        if (is_null($aAllowData)) {
            $aAllowData = $this->aAdditionalData;
        }
        $aAllowData = F::Array_FlipIntKeys($aAllowData);

        // * Получаем юзеров
        $aUsers = $this->GetUsersByArrayId($aUsersId);

        // * Получаем дополнительные данные
        $aSessions = array();
        $aFriends = array();
        $aVote = array();
        $aGeoTargets = array();
        $aNotes = array();
        if (isset($aAllowData['session'])) {
            $aSessions = $this->GetSessionsByArrayId($aUsersId);
        }
        if (isset($aAllowData['friend']) && $this->oUserCurrent) {
            $aFriends = $this->GetFriendsByArray($aUsersId, $this->oUserCurrent->getId());
        }

        if (isset($aAllowData['vote']) && $this->oUserCurrent) {
            $aVote = $this->Vote_GetVoteByArray($aUsersId, 'user', $this->oUserCurrent->getId());
        }
        if (isset($aAllowData['geo_target'])) {
            $aGeoTargets = $this->Geo_GetTargetsByTargetArray('user', $aUsersId);
        }
        if (isset($aAllowData['note']) && $this->oUserCurrent) {
            $aNotes = $this->GetUserNotesByArray($aUsersId, $this->oUserCurrent->getId());
        }

        // * Добавляем данные к результату
        foreach ($aUsers as $oUser) {
            if (isset($aSessions[$oUser->getId()])) {
                $oUser->setSession($aSessions[$oUser->getId()]);
            } else {
                $oUser->setSession(null); // или $oUser->setSession(new ModuleUser_EntitySession());
            }
            if ($aFriends && isset($aFriends[$oUser->getId()])) {
                $oUser->setUserFriend($aFriends[$oUser->getId()]);
            } else {
                $oUser->setUserFriend(null);
            }

            if (isset($aVote[$oUser->getId()])) {
                $oUser->setVote($aVote[$oUser->getId()]);
            } else {
                $oUser->setVote(null);
            }
            if (isset($aGeoTargets[$oUser->getId()])) {
                $aTargets = $aGeoTargets[$oUser->getId()];
                $oUser->setGeoTarget(isset($aTargets[0]) ? $aTargets[0] : null);
            } else {
                $oUser->setGeoTarget(null);
            }
            if (isset($aAllowData['note'])) {
                if (isset($aNotes[$oUser->getId()])) {
                    $oUser->setUserNote($aNotes[$oUser->getId()]);
                } else {
                    $oUser->setUserNote(false);
                }
            }
        }

        return $aUsers;
    }

    /**
     * Список юзеров по ID
     *
     * @param array $aUsersId - Список ID пользователей
     *
     * @return array
     */
    public function GetUsersByArrayId($aUsersId) {

        if (Config::Get('sys.cache.solid')) {
            return $this->GetUsersByArrayIdSolid($aUsersId);
        }

        if (!$aUsersId) {
            return array();
        } elseif (!is_array($aUsersId)) {
            $aUsersId = array($aUsersId);
        } else {
            $aUsersId = array_unique($aUsersId);
        }

        $aUsers = array();
        $aUserIdNotNeedQuery = array();

        // * Делаем мульти-запрос к кешу
        $aCacheKeys = F::Array_ChangeValues($aUsersId, 'user_');
        if (false !== ($data = $this->Cache_Get($aCacheKeys))) {

            // * Проверяем что досталось из кеша
            foreach ($aCacheKeys as $sValue => $sKey) {
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aUsers[$data[$sKey]->getId()] = $data[$sKey];
                    } else {
                        $aUserIdNotNeedQuery[] = $sValue;
                    }
                }
            }
        }

        // * Смотрим каких юзеров не было в кеше и делаем запрос в БД
        $aUserIdNeedQuery = array_diff($aUsersId, array_keys($aUsers));
        $aUserIdNeedQuery = array_diff($aUserIdNeedQuery, $aUserIdNotNeedQuery);
        $aUserIdNeedStore = $aUserIdNeedQuery;
        if ($data = $this->oMapper->GetUsersByArrayId($aUserIdNeedQuery)) {
            foreach ($data as $oUser) {

                // * Добавляем к результату и сохраняем в кеш
                $aUsers[$oUser->getId()] = $oUser;
                $this->Cache_Set($oUser, "user_{$oUser->getId()}", array(), 'P4D');
                $aUserIdNeedStore = array_diff($aUserIdNeedStore, array($oUser->getId()));
            }
        }

        // * Сохраняем в кеш запросы не вернувшие результата
        foreach ($aUserIdNeedStore as $sId) {
            $this->Cache_Set(null, "user_{$sId}", array(), 'P4D');
        }

        // * Сортируем результат согласно входящему массиву
        $aUsers = F::Array_SortByKeysArray($aUsers, $aUsersId);
        return $aUsers;
    }

    /**
     * Алиас для корректной работы ORM
     *
     * @param array $aUsersId - Список ID пользователей
     *
     * @return array
     */
    public function GetUserItemsByArrayId($aUsersId) {

        return $this->GetUsersByArrayId($aUsersId);
    }

    /**
     * Получение пользователей по списку ID используя общий кеш
     *
     * @param array $aUsersId    Список ID пользователей
     *
     * @return array
     */
    public function GetUsersByArrayIdSolid($aUsersId) {

        if (!$aUsersId) {
            return array();
        } elseif (!is_array($aUsersId)) {
            $aUsersId = array($aUsersId);
        } else {
            $aUsersId = array_unique($aUsersId);
        }

        $aUsers = array();
        $s = join(',', $aUsersId);
        if (false === ($data = $this->Cache_Get("user_id_{$s}"))) {
            $data = $this->oMapper->GetUsersByArrayId($aUsersId);
            foreach ($data as $oUser) {
                $aUsers[$oUser->getId()] = $oUser;
            }
            $this->Cache_Set($aUsers, "user_id_{$s}", array("user_update", "user_new"), 'P1D');
            return $aUsers;
        }
        return $data;
    }

    /**
     * Список сессий юзеров по ID
     *
     * @param array $aUsersId    Список ID пользователей
     *
     * @return array
     */
    public function GetSessionsByArrayId($aUsersId) {

        if (Config::Get('sys.cache.solid')) {
            return $this->GetSessionsByArrayIdSolid($aUsersId);
        }

        if (!$aUsersId) {
            return array();
        } elseif (!is_array($aUsersId)) {
            $aUsersId = array($aUsersId);
        } else {
            $aUsersId = array_unique($aUsersId);
        }

        $aSessions = array();
        $aUserIdNotNeedQuery = array();

        // * Делаем мульти-запрос к кешу
        $aCacheKeys = F::Array_ChangeValues($aUsersId, 'user_session_');
        if (false !== ($data = $this->Cache_Get($aCacheKeys))) {

            // * проверяем что досталось из кеша
            foreach ($aCacheKeys as $sValue => $sKey) {
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey] && $data[$sKey]['session']) {
                        $aSessions[$data[$sKey]['session']->getUserId()] = $data[$sKey]['session'];
                    } else {
                        $aUserIdNotNeedQuery[] = $sValue;
                    }
                }
            }
        }

        // * Смотрим каких юзеров не было в кеше и делаем запрос в БД
        $aUserIdNeedQuery = array_diff($aUsersId, array_keys($aSessions));
        $aUserIdNeedQuery = array_diff($aUserIdNeedQuery, $aUserIdNotNeedQuery);
        $aUserIdNeedStore = $aUserIdNeedQuery;

        if ($data = $this->oMapper->GetSessionsByArrayId($aUserIdNeedQuery)) {
            foreach ($data as $oSession) {
                // * Добавляем к результату и сохраняем в кеш
                $aSessions[$oSession->getUserId()] = $oSession;
                $this->Cache_Set(
                    array('time' => time(), 'session' => $oSession),
                    "user_session_{$oSession->getUserId()}", array(),
                    'P4D'
                );
                $aUserIdNeedStore = array_diff($aUserIdNeedStore, array($oSession->getUserId()));
            }
        }

        // * Сохраняем в кеш запросы не вернувшие результата
        foreach ($aUserIdNeedStore as $sId) {
            $this->Cache_Set(array('time' => time(), 'session' => null), "user_session_{$sId}", array(), 'P4D');
        }

        // * Сортируем результат согласно входящему массиву
        $aSessions = F::Array_SortByKeysArray($aSessions, $aUsersId);
        return $aSessions;
    }

    /**
     * Получить список сессий по списку айдишников, но используя единый кеш
     *
     * @param array $aUsersId    Список ID пользователей
     *
     * @return array
     */
    public function GetSessionsByArrayIdSolid($aUsersId) {

        if (!$aUsersId) {
            return array();
        } elseif (!is_array($aUsersId)) {
            $aUsersId = array($aUsersId);
        } else {
            $aUsersId = array_unique($aUsersId);
        }

        $aSessions = array();

        $sCacheKey = 'user_session_id_' . join(',', $aUsersId);
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetSessionsByArrayId($aUsersId);
            foreach ($data as $oSession) {
                $aSessions[$oSession->getUserId()] = $oSession;
            }
            $this->Cache_Set($aSessions, $sCacheKey, array("user_session_update"), 'P1D');
            return $aSessions;
        }
        return $data;
    }

    /**
     * Получает сессию юзера
     *
     * @param int $sUserId    ID пользователя
     *
     * @return ModuleUser_EntitySession|null
     */
    public function GetSessionByUserId($sUserId) {

        $aSessions = $this->GetSessionsByArrayId($sUserId);
        if (isset($aSessions[$sUserId])) {
            return $aSessions[$sUserId];
        }
        return null;
    }

    /**
     * При завершенни модуля загружаем в шалон объект текущего юзера
     *
     */
    public function Shutdown() {

        if ($this->oUserCurrent) {
            $this->Viewer_Assign(
                'iUserCurrentCountTrack', $this->Userfeed_GetCountTrackNew($this->oUserCurrent->getId())
            );
            $this->Viewer_Assign('iUserCurrentCountTalkNew', $this->Talk_GetCountTalkNew($this->oUserCurrent->getId()));
            $this->Viewer_Assign(
                'iUserCurrentCountTopicDraft', $this->Topic_GetCountDraftTopicsByUserId($this->oUserCurrent->getId())
            );
        }
        $this->Viewer_Assign('oUserCurrent', $this->oUserCurrent);
        $this->Viewer_Assign('aContentTypes', $this->Topic_GetContentTypes(array('content_active' => 1)));

    }

    /**
     * Добавляет юзера
     *
     * @param ModuleUser_EntityUser $oUser    Объект пользователя
     *
     * @return ModuleUser_EntityUser|bool
     */
    public function Add(ModuleUser_EntityUser $oUser) {

        if ($sId = $this->oMapper->Add($oUser)) {
            $oUser->setId($sId);

            //чистим зависимые кеши
            $this->Cache_CleanByTags(array('user_new'));

            // * Создаем персональный блог (проверки на права там внутри)
            $this->Blog_CreatePersonalBlog($oUser);

            // Авторизуем пользователя
            $this->Authorization($oUser, true);
            return $oUser;
        }
        return false;
    }

    /**
     * Получить юзера по ключу активации
     *
     * @param string $sKey    Ключ активации
     *
     * @return ModuleUser_EntityUser|null
     */
    public function GetUserByActivateKey($sKey) {

        $id = $this->oMapper->GetUserByActivateKey($sKey);
        return $this->GetUserById($id);
    }

    /**
     * Получить юзера по ключу сессии
     *
     * @param   string $sKey    Сессионный ключ
     *
     * @return  ModuleUser_EntityUser|null
     */
    public function GetUserBySessionKey($sKey) {

        $nUserId = $this->oMapper->GetUserBySessionKey($sKey);
        return $this->GetUserById($nUserId);
    }

    /**
     * Получить юзера по мылу
     *
     * @param   string $sMail
     *
     * @return  ModuleUser_EntityUser|null
     */
    public function GetUserByMail($sMail) {

        $sMail = strtolower($sMail);
        $sCacheKey = "user_mail_{$sMail}";
        if (false === ($nUserId = $this->Cache_Get($sCacheKey))) {
            if ($nUserId = $this->oMapper->GetUserByMail($sMail)) {
                $this->Cache_Set($nUserId, $sCacheKey, array(), 'P1D');
            }
        }
        if ($nUserId) {
            return $this->GetUserById($nUserId);
        }
        return null;
    }

    /**
     * Получить юзера по логину
     *
     * @param string $sLogin
     *
     * @return ModuleUser_EntityUser|null
     */
    public function GetUserByLogin($sLogin) {

        $sLogin = mb_strtolower($sLogin, 'UTF-8');
        $sCacheKey = "user_login_{$sLogin}";
        if (false === ($nUserId = $this->Cache_Get($sCacheKey))) {
            if ($nUserId = $this->oMapper->GetUserByLogin($sLogin)) {
                $this->Cache_Set($nUserId, $sCacheKey, array(), 'P1D');
            }
        }
        if ($nUserId) {
            return $this->GetUserById($nUserId);
        }
        return null;
    }

    /**
     * Получить юзера по ID
     *
     * @param int $nId    ID пользователя
     *
     * @return ModuleUser_EntityUser|null
     */
    public function GetUserById($nId) {

        if (!intval($nId)) {
            return null;
        }
        $aUsers = $this->GetUsersAdditionalData($nId);
        if (isset($aUsers[$nId])) {
            return $aUsers[$nId];
        }
        return null;
    }

    /**
     * Обновляет юзера
     *
     * @param ModuleUser_EntityUser $oUser    Объект пользователя
     *
     * @return bool
     */
    public function Update(ModuleUser_EntityUser $oUser) {

        $bResult = $this->oMapper->Update($oUser);
        //чистим зависимые кеши
        $this->Cache_CleanByTags(array('user_update'));
        $this->Cache_Delete("user_{$oUser->getId()}");
        return $bResult;
    }

    /**
     * Авторизация юзера
     *
     * @param   ModuleUser_EntityUser $oUser       - Объект пользователя
     * @param   bool                  $bRemember   - Запоминать пользователя или нет
     * @param   string                $sSessionKey - Ключ сессии
     *
     * @return  bool
     */
    public function Authorization(ModuleUser_EntityUser $oUser, $bRemember = true, $sSessionKey = null) {

        if (!$oUser->getId() || !$oUser->getActivate()) {
            return false;
        }

        // * Получаем ключ текущей сессии
        if (is_null($sSessionKey)) {
            $sSessionKey = $this->Session_GetKey();
        }

        // * Создаём новую сессию
        if (!$this->CreateSession($oUser, $sSessionKey)) {
            return false;
        }

        // * Запоминаем в сесси юзера
        $this->Session_Set('user_id', $oUser->getId());
        $this->oUserCurrent = $oUser;

        // * Ставим куку
        if ($bRemember) {
            $this->Session_SetCookie($this->GetKeyName(), $sSessionKey, Config::Get('sys.cookie.time'));
        }
        return true;
    }

    /**
     * Автоматическое залогинивание по ключу из куков
     *
     */
    protected function AutoLogin() {

        if ($this->oUserCurrent) {
            return;
        }
        $sSessionKey = $this->RestoreSessionKey();
        if ($sSessionKey) {
            if ($oUser = $this->GetUserBySessionKey($sSessionKey)) {
                // Не забываем продлить куку
                $this->Authorization($oUser, true);
            } else {
                $this->Logout();
            }
        }
    }

    protected function GetKeyName() {

        if (!($sKeyName = Config::Get('security.user_session_key'))) {
            $sKeyName = self::USER_SESSION_KEY;
        }
        return $sKeyName;
    }

    /**
     * Restores user's session key from cookie
     *
     * @return string|null
     */
    protected function RestoreSessionKey() {

        $sSessionKey = $this->Session_GetCookie($this->GetKeyName());
        if ($sSessionKey && is_string($sSessionKey)) {
            return $sSessionKey;
        }
    }

    /**
     * Авторизован ли текущий пользователь
     *
     * @return  bool
     */
    public function IsAuthorization() {

        if ($this->oUserCurrent) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получить текущего юзера
     *
     * @return ModuleUser_EntityUser|null
     */
    public function GetUserCurrent() {

        return $this->oUserCurrent;
    }

    /**
     * Разлогинивание
     *
     */
    public function Logout() {

        if ($this->oSession) {
            // Обновляем сессию
            $this->oMapper->UpdateSession($this->oSession);
        }
        if ($this->oUserCurrent) {
            // И закрываем все сессии текущего юзера
            // TODO: разделить закрытие всех сессий и закрытие текущей
            $this->CloseAllSessions();
        }
        $this->Cache_CleanByTags(array('user_session_update'));

        // * Удаляем из сессии
        $this->Session_Drop('user_id');

        // * Удаляем куки
        $this->Session_DelCookie($this->GetKeyName());

        $this->Session_DropSession();

        $this->oUserCurrent = null;
        $this->oSession = null;
    }

    /**
     * Обновление данных сессии
     * Важный момент: сессию обновляем в кеше и раз в 10 минут скидываем в БД
     */
    protected function UpdateSession() {

        $this->oSession->setDateLast(F::Now());
        $this->oSession->setIpLast(F::GetUserIp());

        $sCacheKey = "user_session_{$this->oSession->getUserId()}";

        // Используем кеширование по запросу
        if (false === ($data = $this->Cache_Get($sCacheKey, true))) {
            $data = array(
                'time'    => time(),
                'session' => $this->oSession
            );
        } else {
            $data['session'] = $this->oSession;
        }
        if ($data['time'] <= time()) {
            $data['time'] = time() + 600;
            $this->oMapper->UpdateSession($this->oSession);
        }
        $this->Cache_Set($data, $sCacheKey, array(), 'PT20M', true);
    }

    /**
     * Закрытие всех сессий для заданного или для текущего юзера
     *
     * @param ModuleUser_EntityUser|null $oUser
     */
    public function CloseAllSessions($oUser = null) {

        if (!$oUser) {
            $oUser = $this->oUserCurrent;
        }
        $this->oMapper->CloseUserSessions($oUser);
        $this->Cache_CleanByTags(array('user_session_update'));
    }

    /**
     * Создание пользовательской сессии
     *
     * @param ModuleUser_EntityUser $oUser   - Объект пользователя
     * @param string                $sKey    - Сессионный ключ
     *
     * @return bool
     */
    protected function CreateSession(ModuleUser_EntityUser $oUser, $sKey) {

        $this->Cache_CleanByTags(array('user_session_update'));
        $this->Cache_Delete("user_session_{$oUser->getId()}");

        /** @var $oSession ModuleUser_EntitySession */
        $oSession = Engine::GetEntity('User_Session');

        $oSession->setUserId($oUser->getId());
        $oSession->setKey($sKey);
        $oSession->setIpLast(F::GetUserIp());
        $oSession->setIpCreate(F::GetUserIp());
        $oSession->setDateLast(F::Now());
        $oSession->setDateCreate(F::Now());
        $oSession->setUserAgentHash();
        if ($this->oMapper->CreateSession($oSession)) {
            if ($nSessionLimit = Config::Get('module.user.max_session_history')) {
                $this->LimitSession($oUser, $nSessionLimit);
            }
            $oUser->setLastSession($sKey);
            if ($this->Update($oUser)) {
                $this->oSession = $oSession;
                return true;
            }
        }
        return false;
    }

    /**
     * Удаляет лишние старые сессии пользователя
     *
     * @param $oUser
     * @param $nSessionLimit
     */
    protected function LimitSession($oUser, $nSessionLimit) {

        return $this->oMapper->LimitSession($oUser, $nSessionLimit);
    }

    /**
     * Получить список юзеров по дате последнего визита
     *
     * @param int $nLimit Количество
     *
     * @return array
     */
    public function GetUsersByDateLast($nLimit = 20) {

        if ($this->IsAuthorization()) {
            $data = $this->oMapper->GetUsersByDateLast($nLimit);
        } elseif (false === ($data = $this->Cache_Get("user_date_last_{$nLimit}"))) {
            $data = $this->oMapper->GetUsersByDateLast($nLimit);
            $this->Cache_Set($data, "user_date_last_{$nLimit}", array("user_session_update"), 'P1D');
        }
        if ($data) {
            $data = $this->GetUsersAdditionalData($data);
        }
        return $data;
    }

    /**
     * Возвращает список пользователей по фильтру
     *
     * @param   array $aFilter    - Фильтр
     * @param   array $aOrder     - Сортировка
     * @param   int   $iCurrPage  - Номер страницы
     * @param   int   $iPerPage   - Количество элментов на страницу
     * @param   array $aAllowData - Список типо данных для подгрузки к пользователям
     *
     * @return  array('collection'=>array,'count'=>int)
     */
    public function GetUsersByFilter($aFilter, $aOrder, $iCurrPage, $iPerPage, $aAllowData = null) {

        $sCacheKey = "user_filter_" . serialize($aFilter) . serialize($aOrder) . "_{$iCurrPage}_{$iPerPage}";
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = array(
                'collection' => $this->oMapper->GetUsersByFilter($aFilter, $aOrder, $iCount, $iCurrPage, $iPerPage),
                'count'      => $iCount);
            $this->Cache_Set($data, $sCacheKey, array('user_update', 'user_new'), 'P1D');
        }
        if ($data['collection']) {
            $data['collection'] = $this->GetUsersAdditionalData($data['collection'], $aAllowData);
        }
        return $data;
    }

    /**
     * Получить список юзеров по дате регистрации
     *
     * @param int $nLimit    Количество
     *
     * @return array
     */
    public function GetUsersByDateRegister($nLimit = 20) {

        $aResult = $this->GetUsersByFilter(array('activate' => 1), array('id' => 'desc'), 1, $nLimit);
        return $aResult['collection'];
    }

    /**
     * Получить статистику по юзерам
     *
     * @return array
     */
    public function GetStatUsers() {

        if (false === ($aStat = $this->Cache_Get('user_stats'))) {
            $aStat['count_all'] = $this->oMapper->GetCountUsers();
            $sDate = date('Y-m-d H:i:s', time() - Config::Get('module.user.time_active'));
            $aStat['count_active'] = $this->oMapper->GetCountUsersActive($sDate);
            $aStat['count_inactive'] = $aStat['count_all'] - $aStat['count_active'];
            $aSex = $this->oMapper->GetCountUsersSex();
            $aStat['count_sex_man'] = (isset($aSex['man']) ? $aSex['man']['count'] : 0);
            $aStat['count_sex_woman'] = (isset($aSex['woman']) ? $aSex['woman']['count'] : 0);
            $aStat['count_sex_other'] = (isset($aSex['other']) ? $aSex['other']['count'] : 0);

            $this->Cache_Set($aStat, 'user_stats', array('user_update', 'user_new'), 'P4D');
        }
        return $aStat;
    }

    /**
     * Получить список юзеров по первым  буквам логина
     *
     * @param string $sUserLogin - Логин
     * @param int    $nLimit     - Количество
     *
     * @return array
     */
    public function GetUsersByLoginLike($sUserLogin, $nLimit) {

        $sCacheKey = "user_like_{$sUserLogin}_{$nLimit}";
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetUsersByLoginLike($sUserLogin, $nLimit);
            $this->Cache_Set($data, $sCacheKey, array("user_new"), 'P2D');
        }
        if ($data) {
            $data = $this->GetUsersAdditionalData($data);
        }
        return $data;
    }

    /**
     * Получить список отношений друзей
     *
     * @param   int|array $aUsersId - Список ID пользователей проверяемых на дружбу
     * @param   int       $nUserId  - ID пользователя у которого проверяем друзей
     *
     * @return array
     */
    public function GetFriendsByArray($aUsersId, $nUserId) {

        if (Config::Get('sys.cache.solid')) {
            return $this->GetFriendsByArraySolid($aUsersId, $nUserId);
        }

        if (!$aUsersId) {
            return array();
        } elseif (!is_array($aUsersId)) {
            $aUsersId = array($aUsersId);
        } else {
            $aUsersId = array_unique($aUsersId);
        }

        $aFriends = array();
        $aUserIdNotNeedQuery = array();

        // * Делаем мульти-запрос к кешу
        $aCacheKeys = F::Array_ChangeValues($aUsersId, 'user_friend_', '_' . $nUserId);
        if (false !== ($data = $this->Cache_Get($aCacheKeys))) {
            // * проверяем что досталось из кеша
            foreach ($aCacheKeys as $sValue => $sKey) {
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aFriends[$data[$sKey]->getFriendId()] = $data[$sKey];
                    } else {
                        $aUserIdNotNeedQuery[] = $sValue;
                    }
                }
            }
        }

        // * Смотрим каких френдов не было в кеше и делаем запрос в БД
        $aUserIdNeedQuery = array_diff($aUsersId, array_keys($aFriends));
        $aUserIdNeedQuery = array_diff($aUserIdNeedQuery, $aUserIdNotNeedQuery);
        $aUserIdNeedStore = $aUserIdNeedQuery;
        if ($data = $this->oMapper->GetFriendsByArrayId($aUserIdNeedQuery, $nUserId)) {
            foreach ($data as $oFriend) {
                // * Добавляем к результату и сохраняем в кеш
                $aFriends[$oFriend->getFriendId($nUserId)] = $oFriend;
                /**
                 * Тут кеш нужно будет продумать как-то по другому.
                 * Пока не трогаю, ибо этот код все равно не выполняется.
                 * by Kachaev
                 */
                $this->Cache_Set(
                    $oFriend, "user_friend_{$oFriend->getFriendId()}_{$oFriend->getUserId()}", array(), 'P4D'
                );
                $aUserIdNeedStore = array_diff($aUserIdNeedStore, array($oFriend->getFriendId()));
            }
        }

        // * Сохраняем в кеш запросы не вернувшие результата
        foreach ($aUserIdNeedStore as $sId) {
            $this->Cache_Set(null, "user_friend_{$sId}_{$nUserId}", array(), 'P4D');
        }

        // * Сортируем результат согласно входящему массиву
        $aFriends = F::Array_SortByKeysArray($aFriends, $aUsersId);
        return $aFriends;
    }

    /**
     * Получить список отношений друзей используя единый кеш
     *
     * @param  array $aUsersId    Список ID пользователей проверяемых на дружбу
     * @param  int   $nUserId    ID пользователя у которого проверяем друзей
     *
     * @return array
     */
    public function GetFriendsByArraySolid($aUsersId, $nUserId) {

        if (!$aUsersId) {
            return array();
        } elseif (!is_array($aUsersId)) {
            $aUsersId = array($aUsersId);
        } else {
            $aUsersId = array_unique($aUsersId);
        }

        $aFriends = array();
        $sCacheKey = "user_friend_{$nUserId}_id_" . join(',', $aUsersId);
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetFriendsByArrayId($aUsersId, $nUserId);
            foreach ($data as $oFriend) {
                $aFriends[$oFriend->getFriendId($nUserId)] = $oFriend;
            }

            $this->Cache_Set($aFriends, $sCacheKey, array("friend_change_user_{$nUserId}"), 'P1D');
            return $aFriends;
        }
        return $data;
    }

    /**
     * Получаем привязку друга к юзеру(есть ли у юзера данный друг)
     *
     * @param  int $nFriendId    ID пользователя друга
     * @param  int $nUserId      ID пользователя
     *
     * @return ModuleUser_EntityFriend|null
     */
    public function GetFriend($nFriendId, $nUserId) {

        $data = $this->GetFriendsByArray($nFriendId, $nUserId);
        if (isset($data[$nFriendId])) {
            return $data[$nFriendId];
        }
        return null;
    }

    /**
     * Добавляет друга
     *
     * @param  ModuleUser_EntityFriend $oFriend    Объект дружбы(связи пользователей)
     *
     * @return bool
     */
    public function AddFriend($oFriend) {

        $bResult = $this->oMapper->AddFriend($oFriend);
        //чистим зависимые кеши
        $this->Cache_CleanByTags(
            array("friend_change_user_{$oFriend->getUserFrom()}", "friend_change_user_{$oFriend->getUserTo()}")
        );
        $this->Cache_Delete("user_friend_{$oFriend->getUserFrom()}_{$oFriend->getUserTo()}");
        $this->Cache_Delete("user_friend_{$oFriend->getUserTo()}_{$oFriend->getUserFrom()}");

        return $bResult;
    }

    /**
     * Удаляет друга
     *
     * @param  ModuleUser_EntityFriend $oFriend Объект дружбы(связи пользователей)
     *
     * @return bool
     */
    public function DeleteFriend($oFriend) {

        // устанавливаем статус дружбы "удалено"
        $oFriend->setStatusByUserId(ModuleUser::USER_FRIEND_DELETE, $oFriend->getUserId());
        $bResult = $this->oMapper->UpdateFriend($oFriend);
        // чистим зависимые кеши
        $this->Cache_CleanByTags(
            array("friend_change_user_{$oFriend->getUserFrom()}", "friend_change_user_{$oFriend->getUserTo()}")
        );
        $this->Cache_Delete("user_friend_{$oFriend->getUserFrom()}_{$oFriend->getUserTo()}");
        $this->Cache_Delete("user_friend_{$oFriend->getUserTo()}_{$oFriend->getUserFrom()}");

        return $bResult;
    }

    /**
     * Удаляет информацию о дружбе из базы данных
     *
     * @param  ModuleUser_EntityFriend $oFriend    Объект дружбы(связи пользователей)
     *
     * @return bool
     */
    public function EraseFriend($oFriend) {

        $bResult = $this->oMapper->EraseFriend($oFriend);
        // чистим зависимые кеши
        $this->Cache_CleanByTags(
            array("friend_change_user_{$oFriend->getUserFrom()}", "friend_change_user_{$oFriend->getUserTo()}")
        );
        $this->Cache_Delete("user_friend_{$oFriend->getUserFrom()}_{$oFriend->getUserTo()}");
        $this->Cache_Delete("user_friend_{$oFriend->getUserTo()}_{$oFriend->getUserFrom()}");
        return $bResult;
    }

    /**
     * Обновляет информацию о друге
     *
     * @param  ModuleUser_EntityFriend $oFriend    Объект дружбы(связи пользователей)
     *
     * @return bool
     */
    public function UpdateFriend($oFriend) {

        $bResult = $this->oMapper->UpdateFriend($oFriend);
        // чистим зависимые кеши
        $this->Cache_CleanByTags(
            array("friend_change_user_{$oFriend->getUserFrom()}", "friend_change_user_{$oFriend->getUserTo()}")
        );
        $this->Cache_Delete("user_friend_{$oFriend->getUserFrom()}_{$oFriend->getUserTo()}");
        $this->Cache_Delete("user_friend_{$oFriend->getUserTo()}_{$oFriend->getUserFrom()}");
        return $bResult;
    }

    /**
     * Получает список друзей
     *
     * @param  int $nUserId     ID пользователя
     * @param  int $iPage       Номер страницы
     * @param  int $iPerPage    Количество элементов на страницу
     *
     * @return array
     */
    public function GetUsersFriend($nUserId, $iPage = 1, $iPerPage = 10) {

        $sCacheKey = "user_friend_{$nUserId}_{$iPage}_{$iPerPage}";
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = array(
                'collection' => $this->oMapper->GetUsersFriend($nUserId, $iCount, $iPage, $iPerPage),
                'count'      => $iCount
            );
            $this->Cache_Set($data, $sCacheKey, array("friend_change_user_{$nUserId}"), 'P2D');
        }
        if ($data['collection']) {
            $data['collection'] = $this->GetUsersAdditionalData($data['collection']);
        }
        return $data;
    }

    /**
     * Получает количество друзей
     *
     * @param  int $nUserId    ID пользователя
     *
     * @return int
     */
    public function GetCountUsersFriend($nUserId) {

        $sCacheKey = "count_user_friend_{$nUserId}";
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetCountUsersFriend($nUserId);
            $this->Cache_Set($data, $sCacheKey, array("friend_change_user_{$nUserId}"), 'P2D');
        }
        return $data;
    }

    /**
     * Получает инвайт по его коду
     *
     * @param  string $sCode    Код инвайта
     * @param  int    $iUsed    Флаг испольщования инвайта
     *
     * @return ModuleUser_EntityInvite|null
     */
    public function GetInviteByCode($sCode, $iUsed = 0) {

        return $this->oMapper->GetInviteByCode($sCode, $iUsed);
    }

    /**
     * Добавляет новый инвайт
     *
     * @param ModuleUser_EntityInvite $oInvite    Объект инвайта
     *
     * @return ModuleUser_EntityInvite|bool
     */
    public function AddInvite($oInvite) {

        if ($nId = $this->oMapper->AddInvite($oInvite)) {
            $oInvite->setId($nId);
            return $oInvite;
        }
        return false;
    }

    /**
     * Обновляет инвайт
     *
     * @param ModuleUser_EntityInvite $oInvite    бъект инвайта
     *
     * @return bool
     */
    public function UpdateInvite($oInvite) {

        $bResult = $this->oMapper->UpdateInvite($oInvite);
        // чистим зависимые кеши
        $this->Cache_CleanByTags(
            array("invate_new_to_{$oInvite->getUserToId()}", "invate_new_from_{$oInvite->getUserFromId()}")
        );
        return $bResult;
    }

    /**
     * Генерирует новый инвайт
     *
     * @param ModuleUser_EntityUser $oUser    Объект пользователя
     *
     * @return ModuleUser_EntityInvite|bool
     */
    public function GenerateInvite($oUser) {

        $oInvite = Engine::GetEntity('User_Invite');
        $oInvite->setCode(F::RandomStr(32));
        $oInvite->setDateAdd(F::Now());
        $oInvite->setUserFromId($oUser->getId());
        return $this->AddInvite($oInvite);
    }

    /**
     * Получает число использованых приглашений юзером за определенную дату
     *
     * @param int    $nUserIdFrom    ID пользователя
     * @param string $sDate          Дата
     *
     * @return int
     */
    public function GetCountInviteUsedByDate($nUserIdFrom, $sDate) {

        return $this->oMapper->GetCountInviteUsedByDate($nUserIdFrom, $sDate);
    }

    /**
     * Получает полное число использованных приглашений юзера
     *
     * @param int $nUserIdFrom    ID пользователя
     *
     * @return int
     */
    public function GetCountInviteUsed($nUserIdFrom) {

        return $this->oMapper->GetCountInviteUsed($nUserIdFrom);
    }

    /**
     * Получаем число доступных приглашений для юзера
     *
     * @param ModuleUser_EntityUser $oUserFrom Объект пользователя
     *
     * @return int
     */
    public function GetCountInviteAvailable($oUserFrom) {

        $sDay = 7;
        $iCountUsed = $this->GetCountInviteUsedByDate(
            $oUserFrom->getId(), date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), date('d') - $sDay, date('Y')))
        );
        $iCountAllAvailable = round($oUserFrom->getRating() + $oUserFrom->getSkill());
        $iCountAllAvailable = $iCountAllAvailable < 0 ? 0 : $iCountAllAvailable;
        $iCountAvailable = $iCountAllAvailable - $iCountUsed;
        $iCountAvailable = $iCountAvailable < 0 ? 0 : $iCountAvailable;

        return $iCountAvailable;
    }

    /**
     * Получает список приглашенных юзеров
     *
     * @param int $nUserId    ID пользователя
     *
     * @return array
     */
    public function GetUsersInvite($nUserId) {

        if (false === ($data = $this->Cache_Get("users_invite_{$nUserId}"))) {
            $data = $this->oMapper->GetUsersInvite($nUserId);
            $this->Cache_Set($data, "users_invite_{$nUserId}", array("invate_new_from_{$nUserId}"), 'P1D');
        }
        if ($data) {
            $data = $this->GetUsersAdditionalData($data);
        }
        return $data;
    }

    /**
     * Получает юзера который пригласил
     *
     * @param int $nUserIdTo    ID пользователя
     *
     * @return ModuleUser_EntityUser|null
     */
    public function GetUserInviteFrom($nUserIdTo) {

        if (false === ($id = $this->Cache_Get("user_invite_from_{$nUserIdTo}"))) {
            $id = $this->oMapper->GetUserInviteFrom($nUserIdTo);
            $this->Cache_Set($id, "user_invite_from_{$nUserIdTo}", array("invate_new_to_{$nUserIdTo}"), 'P1D');
        }
        return $this->GetUserById($id);
    }

    /**
     * Добавляем воспоминание(восстановление) пароля
     *
     * @param ModuleUser_EntityReminder $oReminder    Объект восстановления пароля
     *
     * @return bool
     */
    public function AddReminder($oReminder) {

        return $this->oMapper->AddReminder($oReminder);
    }

    /**
     * Сохраняем воспомнинание(восстановление) пароля
     *
     * @param ModuleUser_EntityReminder $oReminder    Объект восстановления пароля
     *
     * @return bool
     */
    public function UpdateReminder($oReminder) {

        return $this->oMapper->UpdateReminder($oReminder);
    }

    /**
     * Получаем запись восстановления пароля по коду
     *
     * @param string $sCode    Код восстановления пароля
     *
     * @return ModuleUser_EntityReminder|null
     */
    public function GetReminderByCode($sCode) {

        return $this->oMapper->GetReminderByCode($sCode);
    }

    /**
     * Загрузка аватара пользователя
     *
     * @param  string                $sFile - Путь до оригинального файла
     * @param  ModuleUser_EntityUser $oUser - Объект пользователя
     * @param  array                 $aSize - Размер области из которой нужно вырезать картинку - array('x1'=>0,'y1'=>0,'x2'=>100,'y2'=>100)
     *
     * @return string|bool
     */
    public function UploadAvatar($sFile, $oUser, $aSize = array()) {

        if (!F::File_Exists($sFile)) {
            return false;
        }
        if (!$aSize) {
            $oImg = $this->Img_CropSquare($sFile, true);
        } else {
            if (!isset($aSize['w'])) {
                $aSize['w'] = $aSize['x2'] - $aSize['x1'];
            }
            if (!isset($aSize['h'])) {
                $aSize['h'] = $aSize['y2'] - $aSize['y1'];
            }
            $oImg = $this->Img_Crop($sFile, $aSize['w'], $aSize['h'], $aSize['x1'], $aSize['y1']);
        }
        $sExtension = strtolower(pathinfo($sFile, PATHINFO_EXTENSION));
        $sName = pathinfo($sFile, PATHINFO_FILENAME);

        // Сохраняем аватар во временный файл
        if ($sTmpFile = $oImg->Save(F::File_UploadUniqname($sExtension))) {

            // Файл, куда будет записан аватар
            $sAvatar = $this->Uploader_GetUserAvatarDir($oUser->GetId()) . $sName . '.' . $sExtension;

            // Окончательная запись файла только через модуль Uploader
            if ($xStoredFile = $this->Uploader_Store($sTmpFile, $sAvatar)) {
                if (is_object($xStoredFile)) {
                    return $xStoredFile->GetUrl();
                } else {
                    return $this->Uploader_Dir2Url($xStoredFile);
                }
            }
        }

        // * В случае ошибки, возвращаем false
        $this->Message_AddErrorSingle($this->Lang_Get('system_error'));
        return false;
    }

    /**
     * Удаляет аватары пользователя всех размеров
     *
     * @param ModuleUser_EntityUser $oUser - Объект пользователя
     *
     * @return bool
     */
    public function DeleteAvatar($oUser) {

        // * Если аватар есть, удаляем его и его рейсайзы
        if ($sAavatar = $oUser->getProfileAvatar()) {
            $sFile = $this->Uploader_Url2Dir($sAavatar);
            return $this->Uploader_Delete($sFile) && $this->Uploader_DeleteAs($sFile . '-*.*');
        }
    }

    /**
     * Удаляет аватары производных размеров (основной не трогает)
     *
     * @param ModuleUser_EntityUser $oUser
     *
     * @return bool
     */
    public function DeleteAvatarSizes($oUser) {

        // * Если аватар есть, удаляем его и его рейсайзы
        if ($sAavatar = $oUser->getProfileAvatar()) {
            $sFile = $this->Uploader_Url2Dir($sAavatar);
            return $this->Uploader_DeleteAs($sFile . '-*.*');
        }
    }

    /**
     * загрузка фотографии пользователя
     *
     * @param  string                $sFile - Серверный путь до временной фотографии
     * @param  ModuleUser_EntityUser $oUser - Объект пользователя
     * @param  array                 $aSize - Размер области из которой нужно вырезать картинку - array('x1'=>0,'y1'=>0,'x2'=>100,'y2'=>100)
     *
     * @return string|bool
     */
    public function UploadPhoto($sFile, $oUser, $aSize = array()) {

        if (!F::File_Exists($sFile)) {
            return false;
        }
        if (!$aSize) {
            $oImg = $this->Img_CropSquare($sFile, true);
        } else {
            if (!isset($aSize['w'])) {
                $aSize['w'] = $aSize['x2'] - $aSize['x1'];
            }
            if (!isset($aSize['h'])) {
                $aSize['h'] = $aSize['y2'] - $aSize['y1'];
            }
            $oImg = $this->Img_Crop($sFile, $aSize['w'], $aSize['h'], $aSize['x1'], $aSize['y1']);
        }
        $sExtension = strtolower(pathinfo($sFile, PATHINFO_EXTENSION));

        // Сохраняем фото во временный файл
        if ($sTmpFile = $oImg->Save(F::File_UploadUniqname($sExtension))) {

            // Файл, куда будет записано фото
            $sPhoto = $this->Uploader_Uniqname($this->Uploader_GetUserImageDir($oUser->GetId()), $sExtension);

            // Окончательная запись файла только через модуль Uploader
            if ($xStoredFile = $this->Uploader_Store($sTmpFile, $sPhoto)) {
                if (is_object($xStoredFile)) {
                    return $xStoredFile->GetUrl();
                } else {
                    return $this->Uploader_Dir2Url($xStoredFile);
                }
            }
        }

        // * В случае ошибки, возвращаем false
        $this->Message_AddErrorSingle($this->Lang_Get('system_error'));
        return false;
    }

    /**
     * Удаляет фото пользователя
     *
     * @param ModuleUser_EntityUser $oUser
     */
    public function DeletePhoto($oUser) {

        $this->Img_Delete($this->Uploader_Url2Dir($oUser->getProfilePhoto()));
    }

    /**
     * Проверяет логин на корректность
     *
     * @param string $sLogin    Логин пользователя
     *
     * @return bool
     */
    public function CheckLogin($sLogin) {

        // проверка на допустимость логина
        $aDisabledLogins = F::Array_Str2Array(Config::Get('module.user.login.disabled'));
        if (F::Array_StrInArray($sLogin, $aDisabledLogins)) {
            return false;
        } elseif(strpos(strtolower($sLogin), 'id-') === 0 || strpos(strtolower($sLogin), 'login-') === 0) {
            return false;
        }

        $sCharset = Config::Get('module.user.login.charset');
        $nMin = intval(Config::Get('module.user.login.min_size'));
        $nMax = intval(Config::Get('module.user.login.max_size'));

        // Логин не может быть меньше 1
        if (!$nMin) {
            $nMin =1;
        }

        if (!$sCharset) {
            // поверка на длину логина
            if (!$nMax) {
                return strlen($sLogin) >= $nMin;
            } else {
                return strlen($sLogin) >= $nMin && strlen($sLogin) <= $nMax;
            }
        } else {
            // поверка на набор символов и длину логина
            if (!$nMax) {
                return strlen($sLogin) >= $nMin && preg_match('/^[' . $sCharset . ']$/i', $sLogin);
            } else {
                return preg_match('/^[' . $sCharset . ']{' . $nMin . ',' . $nMax . '}$/i', $sLogin);
            }
        }
        return false;
    }

    /**
     * Получить дополнительные поля профиля пользователя
     *
     * @param array|null $aType Типы полей, null - все типы
     *
     * @return array
     */
    public function getUserFields($aType = null) {

        return $this->oMapper->getUserFields($aType);
    }

    /**
     * Получить значения дополнительных полей профиля пользователя
     *
     * @param int   $nUserId      - ID пользователя
     * @param bool  $bOnlyNoEmpty - Загружать только непустые поля
     * @param array $aType        - Типы полей, null - все типы
     *
     * @return array
     */
    public function getUserFieldsValues($nUserId, $bOnlyNoEmpty = true, $aType = array('')) {

        return $this->oMapper->getUserFieldsValues($nUserId, $bOnlyNoEmpty, $aType);
    }

    /**
     * Получить по имени поля его значение дял определённого пользователя
     *
     * @param int    $nUserId - ID пользователя
     * @param string $sName   - Имя поля
     *
     * @return string
     */
    public function getUserFieldValueByName($nUserId, $sName) {

        return $this->oMapper->getUserFieldValueByName($nUserId, $sName);
    }

    /**
     * Установить значения дополнительных полей профиля пользователя
     *
     * @param int   $nUserId    ID пользователя
     * @param array $aFields    Ассоциативный массив полей id => value
     * @param int   $nCountMax  Максимальное количество одинаковых полей
     *
     * @return bool
     */
    public function setUserFieldsValues($nUserId, $aFields, $nCountMax = 1) {

        return $this->oMapper->setUserFieldsValues($nUserId, $aFields, $nCountMax);
    }

    /**
     * Добавить поле
     *
     * @param ModuleUser_EntityField $oField - Объект пользовательского поля
     *
     * @return bool
     */
    public function addUserField($oField) {

        return $this->oMapper->addUserField($oField);
    }

    /**
     * Изменить поле
     *
     * @param ModuleUser_EntityField $oField - Объект пользовательского поля
     *
     * @return bool
     */
    public function updateUserField($oField) {

        return $this->oMapper->updateUserField($oField);
    }

    /**
     * Удалить поле
     *
     * @param int $nId - ID пользовательского поля
     *
     * @return bool
     */
    public function deleteUserField($nId) {

        return $this->oMapper->deleteUserField($nId);
    }

    /**
     * Проверяет существует ли поле с таким именем
     *
     * @param string $sName - Имя поля
     * @param int    $nId   - ID поля
     *
     * @return bool
     */
    public function userFieldExistsByName($sName, $nId = null) {

        return $this->oMapper->userFieldExistsByName($sName, $nId);
    }

    /**
     * Проверяет существует ли поле с таким ID
     *
     * @param int $nId    ID поля
     *
     * @return bool
     */
    public function userFieldExistsById($nId) {

        return $this->oMapper->userFieldExistsById($nId);
    }

    /**
     * Удаляет у пользователя значения полей
     *
     * @param   int|array  $aUsersId   ID пользователя
     * @param   array|null $aTypes     Список типов для удаления
     *
     * @return bool
     */
    public function DeleteUserFieldValues($aUsersId, $aTypes = null) {

        return $this->oMapper->DeleteUserFieldValues($aUsersId, $aTypes);
    }

    /**
     * Возвращает список заметок пользователя
     *
     * @param int $nUserId      ID пользователя
     * @param int $iCurrPage    Номер страницы
     * @param int $iPerPage     Количество элементов на страницу
     *
     * @return array('collection'=>array,'count'=>int)
     */
    public function GetUserNotesByUserId($nUserId, $iCurrPage, $iPerPage) {

        $aResult = $this->oMapper->GetUserNotesByUserId($nUserId, $iCount, $iCurrPage, $iPerPage);

        if ($aResult) {
            // * Цепляем пользователей
            $aUsersId = array();
            foreach ($aResult as $oNote) {
                $aUsersId[] = $oNote->getTargetUserId();
            }
            if ($aUsersId) {
                $aUsers = $this->GetUsersAdditionalData($aUsersId, array());
                foreach ($aResult as $oNote) {
                    if (isset($aUsers[$oNote->getTargetUserId()])) {
                        $oNote->setTargetUser($aUsers[$oNote->getTargetUserId()]);
                    } else {
                        // пустого пользователя во избеания ошибок, т.к. пользователь всегда должен быть
                        $oNote->setTargetUser(Engine::GetEntity('User'));
                    }
                }
            }
        }
        return array('collection' => $aResult, 'count' => $iCount);
    }

    /**
     * Возвращает количество заметок у пользователя
     *
     * @param int $nUserId    ID пользователя
     *
     * @return int
     */
    public function GetCountUserNotesByUserId($nUserId) {

        return $this->oMapper->GetCountUserNotesByUserId($nUserId);
    }

    /**
     * Возвращет заметку по автору и пользователю
     *
     * @param int $nTargetUserId    ID пользователя о ком заметка
     * @param int $nUserId          ID пользователя автора заметки
     *
     * @return ModuleUser_EntityNote
     */
    public function GetUserNote($nTargetUserId, $nUserId) {

        return $this->oMapper->GetUserNote($nTargetUserId, $nUserId);
    }

    /**
     * Возвращает заметку по ID
     *
     * @param int $nId    ID заметки
     *
     * @return ModuleUser_EntityNote
     */
    public function GetUserNoteById($nId) {

        return $this->oMapper->GetUserNoteById($nId);
    }

    /**
     * Возвращает список заметок пользователя по ID целевых юзеров
     *
     * @param array $aUsersId    Список ID целевых пользователей
     * @param int   $nUserId    ID пользователя, кто оставлял заметки
     *
     * @return array
     */
    public function GetUserNotesByArray($aUsersId, $nUserId) {

        if (!$aUsersId) {
            return array();
        } elseif (!is_array($aUsersId)) {
            $aUsersId = array($aUsersId);
        } else {
            $aUsersId = array_unique($aUsersId);
        }

        $aNotes = array();

        $sCacheKey = "user_notes_{$nUserId}_id_" . join(',', $aUsersId);
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetUserNotesByArrayUserId($aUsersId, $nUserId);
            foreach ($data as $oNote) {
                $aNotes[$oNote->getTargetUserId()] = $oNote;
            }

            $this->Cache_Set($aNotes, $sCacheKey, array("user_note_change_by_user_{$nUserId}"), 'P1D');
            return $aNotes;
        }
        return $data;
    }

    /**
     * Удаляет заметку по ID
     *
     * @param int $nId    ID заметки
     *
     * @return bool
     */
    public function DeleteUserNoteById($nId) {

        $bResult = $this->oMapper->DeleteUserNoteById($nId);
        if ($oNote = $this->GetUserNoteById($nId)) {
            $this->Cache_CleanByTags(array("user_note_change_by_user_{$oNote->getUserId()}"));
        }
        return $bResult;
    }

    /**
     * Сохраняет заметку в БД, если ее нет то создает новую
     *
     * @param ModuleUser_EntityNote $oNote    Объект заметки
     *
     * @return bool|ModuleUser_EntityNote
     */
    public function SaveNote($oNote) {

        if (!$oNote->getDateAdd()) {
            $oNote->setDateAdd(F::Now());
        }

        $this->Cache_CleanByTags(array("user_note_change_by_user_{$oNote->getUserId()}"));
        if ($oNoteOld = $this->GetUserNote($oNote->getTargetUserId(), $oNote->getUserId())) {
            $oNoteOld->setText($oNote->getText());
            $this->oMapper->UpdateUserNote($oNoteOld);
            return $oNoteOld;
        } else {
            if ($nId = $this->oMapper->AddUserNote($oNote)) {
                $oNote->setId($nId);
                return $oNote;
            }
        }
        return false;
    }

    /**
     * Возвращает список префиксов логинов пользователей (для алфавитного указателя)
     *
     * @param int $nPrefixLength    Длина префикса
     *
     * @return array
     */
    public function GetGroupPrefixUser($nPrefixLength = 1) {

        $sCacheKey = "group_prefix_user_{$nPrefixLength}";
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetGroupPrefixUser($nPrefixLength);
            $this->Cache_Set($data, $sCacheKey, array("user_new"), 'P1D');
        }
        return $data;
    }

    /**
     * Добавляет запись о смене емайла
     *
     * @param ModuleUser_EntityChangemail $oChangemail    Объект смены емайла
     *
     * @return bool|ModuleUser_EntityChangemail
     */
    public function AddUserChangemail($oChangemail) {

        if ($sId = $this->oMapper->AddUserChangemail($oChangemail)) {
            $oChangemail->setId($sId);
            return $oChangemail;
        }
        return false;
    }

    /**
     * Обновляет запись о смене емайла
     *
     * @param ModuleUser_EntityChangemail $oChangemail    Объект смены емайла
     *
     * @return int
     */
    public function UpdateUserChangemail($oChangemail) {

        return $this->oMapper->UpdateUserChangemail($oChangemail);
    }

    /**
     * Возвращает объект смены емайла по коду подтверждения
     *
     * @param string $sCode Код подтверждения
     *
     * @return ModuleUser_EntityChangemail|null
     */
    public function GetUserChangemailByCodeFrom($sCode) {

        return $this->oMapper->GetUserChangemailByCodeFrom($sCode);
    }

    /**
     * Возвращает объект смены емайла по коду подтверждения
     *
     * @param string $sCode Код подтверждения
     *
     * @return ModuleUser_EntityChangemail|null
     */
    public function GetUserChangemailByCodeTo($sCode) {

        return $this->oMapper->GetUserChangemailByCodeTo($sCode);
    }

    /**
     * Формирование процесса смены емайла в профиле пользователя
     *
     * @param ModuleUser_EntityUser $oUser       Объект пользователя
     * @param string                $sMailNew    Новый емайл
     *
     * @return bool|ModuleUser_EntityChangemail
     */
    public function MakeUserChangemail($oUser, $sMailNew) {

        $oChangemail = Engine::GetEntity('ModuleUser_EntityChangemail');
        $oChangemail->setUserId($oUser->getId());
        $oChangemail->setDateAdd(date('Y-m-d H:i:s'));
        $oChangemail->setDateExpired(date('Y-m-d H:i:s', time() + 3 * 24 * 60 * 60)); // 3 дня для смены емайла
        $oChangemail->setMailFrom($oUser->getMail() ? $oUser->getMail() : '');
        $oChangemail->setMailTo($sMailNew);
        $oChangemail->setCodeFrom(F::RandomStr(32));
        $oChangemail->setCodeTo(F::RandomStr(32));
        if ($this->AddUserChangemail($oChangemail)) {
            // * Если у пользователя раньше не было емайла, то сразу шлем подтверждение на новый емайл
            if (!$oChangemail->getMailFrom()) {
                $oChangemail->setConfirmFrom(1);
                $this->User_UpdateUserChangemail($oChangemail);

                // * Отправляем уведомление на новый емайл
                $this->Notify_Send(
                    $oChangemail->getMailTo(),
                    'user_changemail_to.tpl',
                    $this->Lang_Get('notify_subject_user_changemail'),
                    array(
                         'oUser'       => $oUser,
                         'oChangemail' => $oChangemail,
                    )
                );

            } else {
                // * Отправляем уведомление на старый емайл
                $this->Notify_Send(
                    $oUser,
                    'user_changemail_from.tpl',
                    $this->Lang_Get('notify_subject_user_changemail'),
                    array(
                         'oUser'       => $oUser,
                         'oChangemail' => $oChangemail,
                    )
                );
            }
            return $oChangemail;
        }
        return false;
    }

    public function GetCountUsers() {

        return $this->oMapper->GetCountUsers();
    }

    public function GetCountAdmins() {

        return $this->oMapper->GetCountAdmins();
    }

    /**
     * Удаление пользователей
     *
     * @param $aUsersId
     */
    public function DeleteUsers($aUsersId) {

        if (!is_array($aUsersId)) {
            $aUsersId = array(intval($aUsersId));
        }
        $this->Blog_DeleteBlogsByUsers($aUsersId);
        $this->Topic_DeleteTopicsByUsersId($aUsersId);

        if ($bResult = $this->oMapper->DeleteUser($aUsersId)) {
            $this->DeleteUserFieldValues($aUsersId, $aType = null);
            $aUsers = $this->GetUsersByArrayId($aUsersId);
            foreach ($aUsers as $oUser) {
                $this->DeleteAvatar($oUser);
                $this->DeletePhoto($oUser);
            }
        }
        foreach ($aUsersId as $nUserId) {
            $this->Cache_CleanByTags(array("topic_update_user_{$nUserId}"));
            $this->Cache_Delete("user_{$nUserId}");
        }
        return $bResult;
    }

}

// EOF