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

    const USER_LOGIN_ERR_MIN        = 1;
    const USER_LOGIN_ERR_LEN        = 2;
    const USER_LOGIN_ERR_CHARS      = 4;
    const USER_LOGIN_ERR_DISABLED   = 8;

    /**
     * Статусы дружбы между пользователями
     */
    const USER_FRIEND_OFFER     = 1;
    const USER_FRIEND_ACCEPT    = 2;
    const USER_FRIEND_DELETE    = 4;
    const USER_FRIEND_REJECT    = 8;
    const USER_FRIEND_NULL      = 16;

    /**
     * Права
     */
    const USER_ROLE_USER = 1;
    const USER_ROLE_ADMINISTRATOR = 2;
    const USER_ROLE_MODERATOR = 4;

    const USER_AUTH_RESULT_OK           = 0;

    const USER_AUTH_ERROR               = 1;
    const USER_AUTH_ERR_LOGIN           = 2;
    const USER_AUTH_ERR_MAIL            = 3;
    const USER_AUTH_ERR_ID              = 4;
    const USER_AUTH_ERR_SESSION         = 5;
    const USER_AUTH_ERR_PASSWORD        = 9;

    const USER_AUTH_ERR_NOT_ACTIVATED   = 11;
    const USER_AUTH_ERR_IP_BANNED       = 12;
    const USER_AUTH_ERR_BANNED_DATE     = 13;
    const USER_AUTH_ERR_BANNED_UNLIM    = 14;

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
    protected $aUserFieldTypes = array('social', 'contact');

    /**
     * @var array
     */
    protected $aAdditionalData = array('vote', 'session', 'friend', 'geo_target', 'note');

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->oMapper = E::GetMapper(__CLASS__);

        // * Проверяем есть ли у юзера сессия, т.е. залогинен или нет
        $iUserId = intval(E::ModuleSession()->Get('user_id'));
        if ($iUserId && ($oUser = $this->GetUserById($iUserId)) && $oUser->getActivate()) {
            if ($this->oSession = $oUser->getCurrentSession()) {
                if ($this->oSession->GetSessionExit()) {
                    // Сессия была закрыта
                    $this->Logout();
                    return;
                }
                $this->oUserCurrent = $oUser;
            }
        }
        // Если сессия оборвалась по таймауту (не сам пользователь ее завершил),
        // то пытаемся автоматически авторизоваться
        if (!$this->oUserCurrent) {
            $this->AutoLogin();
        }

        // * Обновляем сессию
        if (isset($this->oSession)) {
            $this->UpdateSession();
        }
    }

    /**
     * Compares user's password and passed password
     *
     * @param ModuleUser_EntityUser $oUser
     * @param string $sCheckPassword
     *
     * @return bool
     */
    public function CheckPassword($oUser, $sCheckPassword) {

        $sUserPassword = $oUser->getPassword();
        if (E::ModuleSecurity()->CheckSalted($sUserPassword, $sCheckPassword, 'pass')
            || E::ModuleSecurity()->CheckSalted($sUserPassword, trim($sCheckPassword), 'pass')) {
            return true;
        }
        return false;
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
     * @param array|int $aUsersId   - Список ID пользователей
     * @param array     $aAllowData - Список типоd дополнительных данных для подгрузки у пользователей
     *
     * @return ModuleUser_EntityUser[]
     */
    public function GetUsersAdditionalData($aUsersId, $aAllowData = null) {

        if (!$aUsersId) {
            return array();
        }

        if (!is_array($aUsersId)) {
            $aUsersId = array($aUsersId);
        } else {
            $aUsersId = array_unique($aUsersId);
        }

        if (sizeof($aUsersId) == 1) {
            $iUserId = reset($aUsersId);
            if ($this->oUserCurrent && ($this->oUserCurrent->getId() == $iUserId)) {
                return array($iUserId => $this->oUserCurrent);
            }
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
            $aVote = E::ModuleVote()->GetVoteByArray($aUsersId, 'user', $this->oUserCurrent->getId());
        }
        if (isset($aAllowData['geo_target'])) {
            $aGeoTargets = E::ModuleGeo()->GetTargetsByTargetArray('user', $aUsersId);
        }
        if (isset($aAllowData['note']) && $this->oUserCurrent) {
            $aNotes = $this->GetUserNotesByArray($aUsersId, $this->oUserCurrent->getId());
        }

        $aAvatars = E::ModuleUploader()->GetMediaObjects('profile_avatar', $aUsersId, null, array('target_id'));

        // * Добавляем данные к результату
        /** @var ModuleUser_EntityUser $oUser */
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
            if (isset($aAvatars[$oUser->getId()])) {
                $oUser->setMediaResources('profile_avatar', $aAvatars[$oUser->getId()]);
            } else {
                $oUser->setMediaResources('profile_avatar', array());
            }
        }

        return $aUsers;
    }

    /**
     * Список юзеров по ID
     *
     * @param array $aUsersId - Список ID пользователей
     *
     * @return ModuleUser_EntityUser[]
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
        if (false !== ($data = E::ModuleCache()->Get($aCacheKeys))) {

            // * Проверяем что досталось из кеша
            foreach ($aCacheKeys as $iIndex => $sKey) {
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aUsers[$data[$sKey]->getId()] = $data[$sKey];
                    } else {
                        $aUserIdNotNeedQuery[] = $aUsersId[$iIndex];
                    }
                }
            }
        }

        // * Смотрим каких юзеров не было в кеше и делаем запрос в БД
        $aUserIdNeedQuery = array_diff($aUsersId, array_keys($aUsers));
        $aUserIdNeedQuery = array_diff($aUserIdNeedQuery, $aUserIdNotNeedQuery);
        $aUserIdNeedStore = $aUserIdNeedQuery;

        if ($aUserIdNeedQuery) {
            if ($data = $this->oMapper->GetUsersByArrayId($aUserIdNeedQuery)) {
                foreach ($data as $oUser) {

                    // * Добавляем к результату и сохраняем в кеш
                    $aUsers[$oUser->getId()] = $oUser;
                    E::ModuleCache()->Set($oUser, "user_{$oUser->getId()}", array(), 'P4D');
                    $aUserIdNeedStore = array_diff($aUserIdNeedStore, array($oUser->getId()));
                }
            }
        }

        // * Сохраняем в кеш запросы не вернувшие результата
        foreach ($aUserIdNeedStore as $sId) {
            E::ModuleCache()->Set(null, "user_{$sId}", array(), 'P4D');
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
     * @return ModuleUser_EntityUser[]
     */
    public function GetUserItemsByArrayId($aUsersId) {

        return $this->GetUsersByArrayId($aUsersId);
    }

    /**
     * Получение пользователей по списку ID используя общий кеш
     *
     * @param array $aUsersId    Список ID пользователей
     *
     * @return ModuleUser_EntityUser[]
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
        if (false === ($data = E::ModuleCache()->Get("user_id_{$s}"))) {
            $data = $this->oMapper->GetUsersByArrayId($aUsersId);
            foreach ($data as $oUser) {
                $aUsers[$oUser->getId()] = $oUser;
            }
            E::ModuleCache()->Set($aUsers, "user_id_{$s}", array("user_update", "user_new"), 'P1D');
            return $aUsers;
        }
        return $data;
    }

    /**
     * Список сессий юзеров по ID
     *
     * @param array $aUsersId    Список ID пользователей
     *
     * @return ModuleUser_EntitySession[]
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
        if (false !== ($data = E::ModuleCache()->Get($aCacheKeys))) {
            // * проверяем что досталось из кеша
            foreach ($aCacheKeys as $iIndex => $sKey) {
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey] && $data[$sKey]['session']) {
                        $aSessions[$data[$sKey]['session']->getUserId()] = $data[$sKey]['session'];
                    } else {
                        $aUserIdNotNeedQuery[] = $aUsersId[$iIndex];
                    }
                }
            }
        }

        // * Смотрим каких юзеров не было в кеше и делаем запрос в БД
        $aUserIdNeedQuery = array_diff($aUsersId, array_keys($aSessions));
        $aUserIdNeedQuery = array_diff($aUserIdNeedQuery, $aUserIdNotNeedQuery);
        $aUserIdNeedStore = $aUserIdNeedQuery;

        if ($aUserIdNeedQuery) {
            if ($data = $this->oMapper->GetSessionsByArrayId($aUserIdNeedQuery)) {
                foreach ($data as $oSession) {
                    // * Добавляем к результату и сохраняем в кеш
                    $aSessions[$oSession->getUserId()] = $oSession;
                    E::ModuleCache()->Set(
                        array('time' => time(), 'session' => $oSession),
                        "user_session_{$oSession->getUserId()}", array('user_session_update'),
                        'P4D'
                    );
                    $aUserIdNeedStore = array_diff($aUserIdNeedStore, array($oSession->getUserId()));
                }
            }
        }

        // * Сохраняем в кеш запросы не вернувшие результата
        foreach ($aUserIdNeedStore as $sId) {
            E::ModuleCache()->Set(array('time' => time(), 'session' => null), "user_session_{$sId}", array('user_session_update'), 'P4D');
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
     * @return ModuleUser_EntitySession[]
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
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetSessionsByArrayId($aUsersId);
            foreach ($data as $oSession) {
                $aSessions[$oSession->getUserId()] = $oSession;
            }
            E::ModuleCache()->Set($aSessions, $sCacheKey, array("user_session_update"), 'P1D');
            return $aSessions;
        }
        return $data;
    }

    /**
     * Return user's session
     *
     * @param int    $iUserId     User ID
     * @param string $sSessionKey Session ID
     *
     * @return ModuleUser_EntitySession|null
     */
    public function GetSessionByUserId($iUserId, $sSessionKey = null) {

        if ($sSessionKey) {
            $aSessions = $this->oMapper->GetSessionsByArrayId(array($iUserId), $sSessionKey);
            if ($aSessions) {
                return reset($aSessions);
            }
        } else {
            $aSessions = $this->GetSessionsByArrayId($iUserId);
            if (isset($aSessions[$iUserId])) {
                return $aSessions[$iUserId];
            }
        }
        return null;
    }

    /**
     * При завершенни модуля загружаем в шалон объект текущего юзера
     *
     */
    public function Shutdown() {

        if ($this->oUserCurrent) {
            E::ModuleViewer()->Assign(
                'iUserCurrentCountTrack', E::ModuleUserfeed()->GetCountTrackNew($this->oUserCurrent->getId())
            );
            E::ModuleViewer()->Assign('iUserCurrentCountTalkNew', E::ModuleTalk()->GetCountTalkNew($this->oUserCurrent->getId()));
            E::ModuleViewer()->Assign(
                'iUserCurrentCountTopicDraft', E::ModuleTopic()->GetCountDraftTopicsByUserId($this->oUserCurrent->getId())
            );
        }
        E::ModuleViewer()->Assign('oUserCurrent', $this->oUserCurrent);
        E::ModuleViewer()->Assign('aContentTypes', E::ModuleTopic()->GetContentTypes(array('content_active' => 1)));
        if ($this->oUserCurrent) {
            E::ModuleViewer()->Assign('aAllowedContentTypes', E::ModuleTopic()->GetAllowContentTypeByUserId($this->oUserCurrent));
        }

    }

    /**
     * Добавляет юзера
     *
     * @param ModuleUser_EntityUser $oUser    Объект пользователя
     *
     * @return ModuleUser_EntityUser|bool
     */
    public function Add(ModuleUser_EntityUser $oUser) {

        if ($nId = $this->oMapper->Add($oUser)) {
            $oUser->setId($nId);

            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags(array('user_new'));

            // * Создаем персональный блог (проверки на права там внутри)
            E::ModuleBlog()->CreatePersonalBlog($oUser);

            if (!$this->IsAuthorization()) {
                // Авторизуем пользователя
                $this->Authorization($oUser, true);
            }
            return $oUser;
        }
        return false;
    }

    /**
     * @param ModuleUser_EntityUser $oUser
     *
     * @return bool
     */
    public function Activate($oUser) {

        $oUser->setActivate(1);
        $oUser->setDateActivate(F::Now());

        return E::ModuleUser()->Update($oUser);
    }

    /**
     * LS-compatibility
     * @deprecated
     * @see GetUserByActivationKey()
     */
    public function GetUserByActivateKey($sKey) {

        return $this->GetUserByActivationKey($sKey);
    }

    /**
     * Получить юзера по ключу активации
     *
     * @param string $sKey    Ключ активации
     *
     * @return ModuleUser_EntityUser|null
     */
    public function GetUserByActivationKey($sKey) {

        $id = $this->oMapper->GetUserByActivationKey($sKey);
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
        if (false === ($nUserId = E::ModuleCache()->Get($sCacheKey))) {
            if ($nUserId = $this->oMapper->GetUserByMail($sMail)) {
                E::ModuleCache()->Set($nUserId, $sCacheKey, array(), 'P1D');
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
        if (false === ($nUserId = E::ModuleCache()->Get($sCacheKey))) {
            if ($nUserId = $this->oMapper->GetUserByLogin($sLogin)) {
                E::ModuleCache()->Set($nUserId, $sCacheKey, array(), 'P1D');
            }
        }
        if ($nUserId) {
            return $this->GetUserById($nUserId);
        }
        return null;
    }

    /**
     * @param $sUserMailOrLogin
     *
     * @return ModuleUser_EntityUser|null
     */
    public function GetUserByMailOrLogin($sUserMailOrLogin) {

        if ((F::CheckVal($sUserMailOrLogin, 'mail') && ($oUser = $this->GetUserByMail($sUserMailOrLogin)))
            || ($oUser = $this->GetUserByLogin($sUserMailOrLogin))) {
            return $oUser;
        }
        return null;
    }

    /**
     * @param      $aUserAuthData
     *
     * @return bool|ModuleUser_EntityUser|null
     */
    public function GetUserAuthorization($aUserAuthData) {

        $oUser = null;
        $iError = null;
        if (!empty($aUserAuthData['login'])) {
            $oUser = $this->GetUserByLogin($aUserAuthData['login']);
            if (!$oUser) {
                $iError = self::USER_AUTH_ERR_LOGIN;
            }
        }
        if (!$oUser && !empty($aUserAuthData['email'])) {
            if (F::CheckVal($aUserAuthData['email'], 'email')) {
                $oUser = $this->GetUserByMail($aUserAuthData['email']);
                if (!$oUser) {
                    $iError = self::USER_AUTH_ERR_MAIL;
                }
            }
        }
        if (!$oUser && !empty($aUserAuthData['id'])) {
            if (F::CheckVal(!empty($aUserAuthData['id']), 'id')) {
                $oUser = $this->GetUserById($aUserAuthData['id']);
                if (!$oUser) {
                    $iError = self::USER_AUTH_ERR_ID;
                }
            }
        }
        if (!$oUser && !empty($aUserAuthData['session'])) {
            $oUser = $this->GetUserBySessionKey($aUserAuthData['session']);
            if (!$oUser) {
                $iError = self::USER_AUTH_ERR_SESSION;
            }
        }
        if ($oUser && !empty($aUserAuthData['password'])) {
            if (!$this->CheckPassword($oUser, $aUserAuthData['password'])) {
                $iError = self::USER_AUTH_ERR_PASSWORD;
            }
        }
        if ($oUser && !$iError) {
            $iError = self::USER_AUTH_RESULT_OK;
            if (!$oUser->getActivate()) {
                $iError = self::USER_AUTH_ERR_NOT_ACTIVATED;
            }
            // Не забанен ли юзер
            if ($oUser->IsBanned()) {
                if ($oUser->IsBannedByIp()) {
                    $iError = self::USER_AUTH_ERR_IP_BANNED;
                } elseif ($oUser->GetBanLine()) {
                    $iError = self::USER_AUTH_ERR_BANNED_DATE;
                } else {
                    $iError = self::USER_AUTH_ERR_BANNED_UNLIM;
                }
            }
        } elseif(!$iError) {
            $iError = self::USER_AUTH_ERROR;
        }
        $aUserAuthData['error'] = $iError;

        return $oUser;
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
        E::ModuleCache()->CleanByTags(array('user_update'));
        E::ModuleCache()->Delete("user_{$oUser->getId()}");
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
            $sSessionKey = E::ModuleSession()->GetKey();
        }

        // * Создаём новую сессию
        if (!$this->CreateSession($oUser, $sSessionKey)) {
            return false;
        }

        // * Запоминаем в сесси юзера
        E::ModuleSession()->Set('user_id', $oUser->getId());
        $this->oUserCurrent = $oUser;

        // * Ставим куку
        if ($bRemember) {
            E::ModuleSession()->SetCookie($this->GetKeyName(), $sSessionKey, Config::Get('sys.cookie.time'));
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

        $sSessionKey = E::ModuleSession()->GetCookie($this->GetKeyName());
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
            // Close current session of the current user
            $this->CloseSession();
        }
        E::ModuleCache()->CleanByTags(array('user_session_update'));

        // * Удаляем из сессии
        E::ModuleSession()->Drop('user_id');

        // * Удаляем куки
        E::ModuleSession()->DelCookie($this->GetKeyName());

        E::ModuleSession()->DropSession();

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
        if (false === ($data = E::ModuleCache()->Get($sCacheKey, true))) {
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
        E::ModuleCache()->Set($data, $sCacheKey, array('user_session_update'), 'PT20M', true);
    }

    /**
     * Close current session of the user
     *
     * @param ModuleUser_EntityUser|null $oUser
     */
    public function CloseSession($oUser = null) {

        if (!$oUser) {
            $oUser = $this->oUserCurrent;
        }
        if (!$this->oSession) {
            $oSession = $oUser->getSession();
        } else {
            $oSession = $this->oSession;
        }
        if ($oUser) {
            $this->oMapper->CloseSession($oSession);
            E::ModuleCache()->CleanByTags(array('user_session_update'));
        }
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
        if ($oUser) {
            $this->oMapper->CloseUserSessions($oUser);
            E::ModuleCache()->CleanByTags(array('user_session_update'));
        }
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

        E::ModuleCache()->CleanByTags(array('user_session_update'));
        E::ModuleCache()->Delete("user_session_{$oUser->getId()}");

        /** @var $oSession ModuleUser_EntitySession */
        $oSession = E::GetEntity('User_Session');

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
     * Remove old session of user
     *
     * @param $oUser
     * @param $nSessionLimit
     *
     * @return bool|void
     */
    protected function LimitSession($oUser, $nSessionLimit) {

        return $this->oMapper->LimitSession($oUser, $nSessionLimit);
    }

    /**
     * Получить список юзеров по дате последнего визита
     *
     * @param int $nLimit Количество
     *
     * @return ModuleUser_EntityUser[]
     */
    public function GetUsersByDateLast($nLimit = 20) {

        if ($this->IsAuthorization()) {
            $data = $this->oMapper->GetUsersByDateLast($nLimit);
        } elseif (false === ($data = E::ModuleCache()->Get("user_date_last_{$nLimit}"))) {
            $data = $this->oMapper->GetUsersByDateLast($nLimit);
            E::ModuleCache()->Set($data, "user_date_last_{$nLimit}", array("user_session_update"), 'P1D');
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
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = array(
                'collection' => $this->oMapper->GetUsersByFilter($aFilter, $aOrder, $iCount, $iCurrPage, $iPerPage),
                'count'      => $iCount);
            E::ModuleCache()->Set($data, $sCacheKey, array('user_update', 'user_new'), 'P1D');
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
     * @return ModuleUser_EntityUser[]
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

        if (false === ($aStat = E::ModuleCache()->Get('user_stats'))) {
            $aStat['count_all'] = $this->oMapper->GetCountByRole(self::USER_ROLE_USER);
            $sDate = date('Y-m-d H:i:s', time() - Config::Get('module.user.time_active'));
            $aStat['count_active'] = $this->oMapper->GetCountUsersActive($sDate);
            $aStat['count_inactive'] = $aStat['count_all'] - $aStat['count_active'];
            $aSex = $this->oMapper->GetCountUsersSex();
            $aStat['count_sex_man'] = (isset($aSex['man']) ? $aSex['man']['count'] : 0);
            $aStat['count_sex_woman'] = (isset($aSex['woman']) ? $aSex['woman']['count'] : 0);
            $aStat['count_sex_other'] = (isset($aSex['other']) ? $aSex['other']['count'] : 0);

            E::ModuleCache()->Set($aStat, 'user_stats', array('user_update', 'user_new'), 'P4D');
        }
        return $aStat;
    }

    /**
     * Получить список юзеров по первым  буквам логина
     *
     * @param string $sUserLogin - Логин
     * @param int    $nLimit     - Количество
     *
     * @return ModuleUser_EntityUser[]
     */
    public function GetUsersByLoginLike($sUserLogin, $nLimit) {

        $sCacheKey = "user_like_{$sUserLogin}_{$nLimit}";
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetUsersByLoginLike($sUserLogin, $nLimit);
            E::ModuleCache()->Set($data, $sCacheKey, array("user_new"), 'P2D');
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
     * @param   int       $iUserId  - ID пользователя у которого проверяем друзей
     *
     * @return ModuleUser_EntityFriend[]
     */
    public function GetFriendsByArray($aUsersId, $iUserId) {

        if (Config::Get('sys.cache.solid')) {
            return $this->GetFriendsByArraySolid($aUsersId, $iUserId);
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
        $aCacheKeys = F::Array_ChangeValues($aUsersId, 'user_friend_', '_' . $iUserId);
        if (false !== ($data = E::ModuleCache()->Get($aCacheKeys))) {
            // * проверяем что досталось из кеша
            foreach ($aCacheKeys as $iIndex => $sKey) {
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aFriends[$data[$sKey]->getFriendId()] = $data[$sKey];
                    } else {
                        $aUserIdNotNeedQuery[] = $aUsersId[$iIndex];
                    }
                }
            }
        }

        // * Смотрим каких френдов не было в кеше и делаем запрос в БД
        $aUserIdNeedQuery = array_diff($aUsersId, array_keys($aFriends));
        $aUserIdNeedQuery = array_diff($aUserIdNeedQuery, $aUserIdNotNeedQuery);
        $aUserIdNeedStore = $aUserIdNeedQuery;

        if ($aUserIdNeedQuery) {
            if ($data = $this->oMapper->GetFriendsByArrayId($aUserIdNeedQuery, $iUserId)) {
                foreach ($data as $oFriend) {
                    // * Добавляем к результату и сохраняем в кеш
                    $aFriends[$oFriend->getFriendId($iUserId)] = $oFriend;
                    /**
                     * Тут кеш нужно будет продумать как-то по другому.
                     * Пока не трогаю, ибо этот код все равно не выполняется.
                     * by Kachaev
                     */
                    E::ModuleCache()->Set(
                        $oFriend, "user_friend_{$oFriend->getFriendId()}_{$oFriend->getUserId()}", array(), 'P4D'
                    );
                    $aUserIdNeedStore = array_diff($aUserIdNeedStore, array($oFriend->getFriendId()));
                }
            }
        }

        // * Сохраняем в кеш запросы не вернувшие результата
        foreach ($aUserIdNeedStore as $sId) {
            E::ModuleCache()->Set(null, "user_friend_{$sId}_{$iUserId}", array(), 'P4D');
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
     * @return ModuleUser_EntityFriend[]
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
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetFriendsByArrayId($aUsersId, $nUserId);
            foreach ($data as $oFriend) {
                $aFriends[$oFriend->getFriendId($nUserId)] = $oFriend;
            }

            E::ModuleCache()->Set($aFriends, $sCacheKey, array("friend_change_user_{$nUserId}"), 'P1D');
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
        E::ModuleCache()->CleanByTags(
            array("friend_change_user_{$oFriend->getUserFrom()}", "friend_change_user_{$oFriend->getUserTo()}")
        );
        E::ModuleCache()->Delete("user_friend_{$oFriend->getUserFrom()}_{$oFriend->getUserTo()}");
        E::ModuleCache()->Delete("user_friend_{$oFriend->getUserTo()}_{$oFriend->getUserFrom()}");

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
        E::ModuleCache()->CleanByTags(
            array("friend_change_user_{$oFriend->getUserFrom()}", "friend_change_user_{$oFriend->getUserTo()}")
        );
        E::ModuleCache()->Delete("user_friend_{$oFriend->getUserFrom()}_{$oFriend->getUserTo()}");
        E::ModuleCache()->Delete("user_friend_{$oFriend->getUserTo()}_{$oFriend->getUserFrom()}");

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
        E::ModuleCache()->CleanByTags(
            array("friend_change_user_{$oFriend->getUserFrom()}", "friend_change_user_{$oFriend->getUserTo()}")
        );
        E::ModuleCache()->Delete("user_friend_{$oFriend->getUserFrom()}_{$oFriend->getUserTo()}");
        E::ModuleCache()->Delete("user_friend_{$oFriend->getUserTo()}_{$oFriend->getUserFrom()}");
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
        E::ModuleCache()->CleanByTags(
            array("friend_change_user_{$oFriend->getUserFrom()}", "friend_change_user_{$oFriend->getUserTo()}")
        );
        E::ModuleCache()->Delete("user_friend_{$oFriend->getUserFrom()}_{$oFriend->getUserTo()}");
        E::ModuleCache()->Delete("user_friend_{$oFriend->getUserTo()}_{$oFriend->getUserFrom()}");
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
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = array(
                'collection' => $this->oMapper->GetUsersFriend($nUserId, $iCount, $iPage, $iPerPage),
                'count'      => $iCount
            );
            E::ModuleCache()->Set($data, $sCacheKey, array("friend_change_user_{$nUserId}"), 'P2D');
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
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetCountUsersFriend($nUserId);
            E::ModuleCache()->Set($data, $sCacheKey, array("friend_change_user_{$nUserId}"), 'P2D');
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
        E::ModuleCache()->CleanByTags(
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

        $oInvite = E::GetEntity('User_Invite');
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

        if (false === ($data = E::ModuleCache()->Get("users_invite_{$nUserId}"))) {
            $data = $this->oMapper->GetUsersInvite($nUserId);
            E::ModuleCache()->Set($data, "users_invite_{$nUserId}", array("invate_new_from_{$nUserId}"), 'P1D');
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

        if (false === ($id = E::ModuleCache()->Get("user_invite_from_{$nUserIdTo}"))) {
            $id = $this->oMapper->GetUserInviteFrom($nUserIdTo);
            E::ModuleCache()->Set($id, "user_invite_from_{$nUserIdTo}", array("invate_new_to_{$nUserIdTo}"), 'P1D');
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
     * @param  string     $sFile - Путь до оригинального файла
     * @param  object|int $xUser - Сущность пользователя или ID пользователя
     * @param  array      $aSize - Размер области из которой нужно вырезать картинку - array('x1'=>0,'y1'=>0,'x2'=>100,'y2'=>100)
     *
     * @return string|bool
     */
    public function UploadAvatar($sFile, $xUser, $aSize = array()) {

        if ($sFile && $xUser) {
            if (is_object($xUser)) {
                $iUserId = $xUser->getId();
            } else {
                $iUserId = intval($xUser);
            }
            if ($iUserId && ($oStoredFile = E::ModuleUploader()->StoreImage($sFile, 'profile_avatar', $iUserId, $aSize))) {
                return $oStoredFile->GetUrl();
            }
        }
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

        $bResult = true;
        // * Если аватар есть, удаляем его и его рейсайзы
        if ($sAvatar = $oUser->getProfileAvatar()) {
            $sFile = E::ModuleUploader()->Url2Dir($sAvatar);
            $bResult = E::ModuleImg()->Delete($sFile);
            if ($bResult) {
                $oUser->setProfileAvatar(null);
                E::ModuleUser()->Update($oUser);
            }
        }
        return $bResult;
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
        if ($sAvatar = $oUser->getProfileAvatar()) {
            $sFile = E::ModuleUploader()->Url2Dir($sAvatar);
            return E::ModuleUploader()->DeleteAs($sFile . '-*.*');
        }
        return true;
    }

    /**
     * Загрузка фотографии пользователя
     *
     * @param  string     $sFile - Серверный путь до временной фотографии
     * @param  object|int $xUser - Сущность пользователя или ID пользователя
     * @param  array      $aSize - Размер области из которой нужно вырезать картинку - array('x1'=>0,'y1'=>0,'x2'=>100,'y2'=>100)
     *
     * @return string|bool
     */
    public function UploadPhoto($sFile, $xUser, $aSize = array()) {

        if ($sFile && $xUser) {
            if (is_object($xUser)) {
                $iUserId = $xUser->getId();
            } else {
                $iUserId = intval($xUser);
            }
            if ($iUserId && ($oStoredFile = E::ModuleUploader()->StoreImage($sFile, 'profile_photo', $iUserId, $aSize))) {
                return $oStoredFile->GetUrl();
            }
        }
        return false;
    }

    /**
     * Удаляет фото пользователя
     *
     * @param ModuleUser_EntityUser $oUser
     *
     * @return bool
     */
    public function DeletePhoto($oUser) {

        $bResult = true;
        if ($sPhoto = $oUser->getProfilePhoto()) {
            $sFile = E::ModuleUploader()->Url2Dir($sPhoto);
            $bResult = E::ModuleImg()->Delete($sFile);
            if ($bResult) {
                $oUser->setProfilePhoto(null);
                E::ModuleUser()->Update($oUser);
            }
        }
        return $bResult;
    }

    /**
     * Проверяет логин на корректность
     *
     * @param string $sLogin    Логин пользователя
     * @param int    $nError    Ошибка (если есть)
     *
     * @return bool
     */
    public function CheckLogin($sLogin, &$nError) {

        // проверка на допустимость логина
        $aDisabledLogins = F::Array_Str2Array(Config::Get('module.user.login.disabled'));
        if (F::Array_StrInArray($sLogin, $aDisabledLogins)) {
            $nError = self::USER_LOGIN_ERR_DISABLED;
            return false;
        } elseif(strpos(strtolower($sLogin), 'id-') === 0 || strpos(strtolower($sLogin), 'login-') === 0) {
            $nError = self::USER_LOGIN_ERR_DISABLED;
            return false;
        }

        $sCharset = Config::Get('module.user.login.charset');
        $nMin = intval(Config::Get('module.user.login.min_size'));
        $nMax = intval(Config::Get('module.user.login.max_size'));

        // Логин не может быть меньше 1
        if ($nMin < 1) {
            $nMin = 1;
        }

        $nError = 0;
        // поверка на длину логина
        if (!$nMax) {
            $bOk = mb_strlen($sLogin, 'UTF-8') >= $nMin;
            if (!$bOk) {
                $nError = self::USER_LOGIN_ERR_MIN;
            }
        } else {
            $bOk = mb_strlen($sLogin, 'UTF-8') >= $nMin && mb_strlen($sLogin, 'UTF-8') <= $nMax;
            if (!$bOk) {
                $nError = self::USER_LOGIN_ERR_LEN;
            }
        }
        if ($bOk && $sCharset) {
            // поверка на набор символов
            if (!preg_match('/^([' . $sCharset . ']+)$/iu', $sLogin)) {
                $nError = self::USER_LOGIN_ERR_CHARS;
                $bOk = false;
            }
        }
        return $bOk;
    }

    /**
     * @param string $sLogin
     *
     * @return int
     */
    public function InvalidLogin($sLogin) {

        $this->CheckLogin($sLogin, $nError);

        return $nError;
    }

    /**
     * Получить дополнительные поля профиля пользователя
     *
     * @param array|null $aType Типы полей, null - все типы
     *
     * @return ModuleUser_EntityField[]
     */
    public function getUserFields($aType = null) {

        $sCacheKey = 'user_fields';
        if (false === ($data = E::ModuleCache()->Get($sCacheKey, 'tmp,'))) {
            $data = $this->oMapper->getUserFields();
            E::ModuleCache()->Set($data, $sCacheKey, array('user_fields_update'), 'P10D', 'tmp,');
        }
        $aResult = array();
        if ($data) {
            if (empty($aType)) {
                $aResult = $data;
            } else {
                if (!is_array($aType)) {
                    $aType = array($aType);
                }
                foreach($data as $oUserField) {
                    if (in_array($oUserField->getType(), $aType)) {
                        $aResult[$oUserField->getId()] = $oUserField;
                    }
                }
            }
        }

        return $aResult;
    }

    /**
     * Получить значения дополнительных полей профиля пользователя
     *
     * @param int          $iUserId       ID пользователя
     * @param bool         $bNotEmptyOnly Загружать только непустые поля
     * @param array|string $xType         Типы полей, null - все типы
     *
     * @return ModuleUser_EntityField[]
     */
    public function getUserFieldsValues($iUserId, $bNotEmptyOnly = true, $xType = array()) {

        if (!is_array($xType)) {
            $xType = array($xType);
        }
        $sCacheKey = 'user_fields_values_' . serialize(array($iUserId, $bNotEmptyOnly, $xType));
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $aResult = array();
            // Get all user fields
            $aAllFields = $this->getUserFields($xType);
            // Get user fields with values and group them by ID
            $data = $this->oMapper->getUserFieldsValues($iUserId, $xType);
            $aValuesByTypes = array();
            foreach($data as $oFieldValue) {
                if ($oFieldValue->getValue()) {
                    $aValuesByTypes[$oFieldValue->getId()][] = $oFieldValue;
                }
            }
            // Forming result
            foreach($aAllFields as $iIdx => $oUserField) {
                if (isset($aValuesByTypes[$oUserField->getId()])) {
                    // If field of the type has values then add them ...
                    foreach($aValuesByTypes[$oUserField->getId()] as $oFieldValue) {
                        $aResult[] = $oFieldValue;
                    }
                } elseif(!$bNotEmptyOnly) {
                    // ... else add empty field (if has no flag $bNotEmptyOnly)
                    $aResult[] = $oUserField;
                }
            }
            E::ModuleCache()->Set($aResult, $sCacheKey, array('user_fields_update', "user_update_{$iUserId}"), 'P10D');
        } else {
            $aResult = $data;
        }

        return $aResult;
    }

    /**
     * Получить по имени поля его значение для определённого пользователя
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

        $xResult = $this->oMapper->setUserFieldsValues($nUserId, $aFields, $nCountMax);
        E::ModuleCache()->CleanByTags("user_update_{$nUserId}");

        return $xResult;
    }

    /**
     * Добавить поле
     *
     * @param ModuleUser_EntityField $oField - Объект пользовательского поля
     *
     * @return bool
     */
    public function addUserField($oField) {

        $xResult = $this->oMapper->addUserField($oField);
        E::ModuleCache()->CleanByTags('user_fields_update');

        return $xResult;
    }

    /**
     * Изменить поле
     *
     * @param ModuleUser_EntityField $oField - Объект пользовательского поля
     *
     * @return bool
     */
    public function updateUserField($oField) {

        $xResult = $this->oMapper->updateUserField($oField);
        E::ModuleCache()->CleanByTags('user_fields_update');

        return $xResult;
    }

    /**
     * Удалить поле
     *
     * @param int $nId - ID пользовательского поля
     *
     * @return bool
     */
    public function deleteUserField($nId) {

        $xResult = $this->oMapper->deleteUserField($nId);
        E::ModuleCache()->CleanByTags('user_fields_update');

        return $xResult;
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
     * @param  int|array  $aUsersId   ID пользователя
     * @param  array|null $aTypes     Список типов для удаления
     *
     * @return bool
     */
    public function DeleteUserFieldValues($aUsersId, $aTypes = null) {

        $xResult = $this->oMapper->DeleteUserFieldValues($aUsersId, $aTypes);
        E::ModuleCache()->CleanByTags('user_fields_update');

        return $xResult;
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
                        $oNote->setTargetUser(E::GetEntity('User'));
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
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetUserNotesByArrayUserId($aUsersId, $nUserId);
            foreach ($data as $oNote) {
                $aNotes[$oNote->getTargetUserId()] = $oNote;
            }

            E::ModuleCache()->Set($aNotes, $sCacheKey, array("user_note_change_by_user_{$nUserId}"), 'P1D');
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
            E::ModuleCache()->CleanByTags(array("user_note_change_by_user_{$oNote->getUserId()}"));
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

        E::ModuleCache()->CleanByTags(array("user_note_change_by_user_{$oNote->getUserId()}"));
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
     * @return string[]
     */
    public function GetGroupPrefixUser($nPrefixLength = 1) {

        $sCacheKey = "group_prefix_user_{$nPrefixLength}";
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetGroupPrefixUser($nPrefixLength);
            E::ModuleCache()->Set($data, $sCacheKey, array("user_new"), 'P1D');
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

        /** @var ModuleUser_EntityChangemail $oChangemail */
        $oChangemail = E::GetEntity('ModuleUser_EntityChangemail');
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
                E::ModuleUser()->UpdateUserChangemail($oChangemail);

                // * Отправляем уведомление на новый емайл
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

            } else {
                // * Отправляем уведомление на старый емайл
                E::ModuleNotify()->Send(
                    $oUser,
                    'user_changemail_from.tpl',
                    E::ModuleLang()->Get('notify_subject_user_changemail'),
                    array(
                         'oUser'       => $oUser,
                         'oChangemail' => $oChangemail,
                    ),
                    null,
                    true
                );
            }
            return $oChangemail;
        }
        return false;
    }

    public function GetCountUsers() {

        return $this->GetCountByRole(self::USER_ROLE_USER);
    }

    public function GetCountModerators() {

        return $this->GetCountByRole(self::USER_ROLE_MODERATOR);
    }

    public function GetCountAdmins() {

        return $this->GetCountByRole(self::USER_ROLE_ADMINISTRATOR);
    }

    /**
     * Возвращает количество пользователей по роли
     * @param $iRole
     */
    public function GetCountByRole($iRole) {

        return $this->oMapper->GetCountByRole($iRole);
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
        E::ModuleBlog()->DeleteBlogsByUsers($aUsersId);
        E::ModuleTopic()->DeleteTopicsByUsersId($aUsersId);

        if ($bResult = $this->oMapper->DeleteUser($aUsersId)) {
            $this->DeleteUserFieldValues($aUsersId, $aType = null);
            $aUsers = $this->GetUsersByArrayId($aUsersId);
            foreach ($aUsers as $oUser) {
                $this->DeleteAvatar($oUser);
                $this->DeletePhoto($oUser);
            }
        }
        foreach ($aUsersId as $nUserId) {
            E::ModuleCache()->CleanByTags(array("topic_update_user_{$nUserId}"));
            E::ModuleCache()->Delete("user_{$nUserId}");
        }
        return $bResult;
    }

    /**
     * issue 258 {@link https://github.com/altocms/altocms/issues/258}
     * Проверяет, не забанен ли этот адрес
     *
     * @param string $sIp Ip Адрес
     * @return mixed
     */
    public function IpIsBanned($sIp) {

        return $this->oMapper->IpIsBanned($sIp);
    }

    /**
     * Returns stats of user publications and favourites
     *
     * @param int|object $xUser
     *
     * @return int[]
     */
    public function GetUserProfileStats($xUser) {

        if (is_object($xUser)) {
            $iUserId = $xUser->getId();
        } else {
            $iUserId = intval($xUser);
        }

        $iCountTopicFavourite = E::ModuleTopic()->GetCountTopicsFavouriteByUserId($iUserId);
        $iCountCommentFavourite = E::ModuleComment()->GetCountCommentsFavouriteByUserId($iUserId);
        $iCountTopics = E::ModuleTopic()->GetCountTopicsPersonalByUser($iUserId, 1);
        $iCountComments = E::ModuleComment()->GetCountCommentsByUserId($iUserId, 'topic');
        $iCountWallRecords = E::ModuleWall()->GetCountWall(array('wall_user_id' => $iUserId, 'pid' => null));
        $iImageCount = E::ModuleMresource()->GetCountImagesByUserId($iUserId);

        $iCountUserNotes = $this->GetCountUserNotesByUserId($iUserId);
        $iCountUserFriends = $this->GetCountUsersFriend($iUserId);

        $aUserPublicationStats = array(
            'favourite_topics' => $iCountTopicFavourite,
            'favourite_comments' => $iCountCommentFavourite,
            'count_topics' => $iCountTopics,
            'count_comments' => $iCountComments,
            'count_usernotes' => $iCountUserNotes,
            'count_wallrecords' => $iCountWallRecords,
            'count_images' => $iImageCount,
            'count_friends' => $iCountUserFriends,
        );
        $aUserPublicationStats['count_created'] =
            $aUserPublicationStats['count_topics']
            + $aUserPublicationStats['count_comments']
            + $aUserPublicationStats['count_images'];

        if ($iUserId == E::UserId()) {
            $aUserPublicationStats['count_created'] += $aUserPublicationStats['count_usernotes'];
        }

        $aUserPublicationStats['count_favourites'] =
            $aUserPublicationStats['favourite_topics']
            + $aUserPublicationStats['favourite_comments'];

        return $aUserPublicationStats;
    }
}

// EOF