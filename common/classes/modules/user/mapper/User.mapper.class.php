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
 * Маппер для работы с БД
 *
 * @package modules.user
 * @since   1.0
 */
class ModuleUser_MapperUser extends Mapper {
    /**
     * Добавляет юзера
     *
     * @param ModuleUser_EntityUser $oUser    Объект пользователя
     *
     * @return int|bool
     */
    public function Add(ModuleUser_EntityUser $oUser) {

        $sql
            = "INSERT INTO ?_user
			(user_login,
			user_password,
			user_mail,
			user_date_register,
			user_ip_register,
			user_activate,
			user_activate_key
			)
			VALUES(?, ?, ?, ?, ?, ?, ?)
		";
        $nUserId = $this->oDb->query(
            $sql, $oUser->getLogin(), $oUser->getPassword(), $oUser->getMail(), $oUser->getDateRegister(),
            $oUser->getIpRegister(), $oUser->getActivate(), $oUser->getActivationKey()
        );
        return $nUserId ? $nUserId : false;
    }

    /**
     * Обновляет юзера
     *
     * @param ModuleUser_EntityUser $oUser    Объект пользователя
     *
     * @return bool
     */
    public function Update(ModuleUser_EntityUser $oUser) {

        $sql
            = "
            UPDATE ?_user
            SET
                user_password = ?,
                user_mail = ?,
                user_skill = ?,
                user_date_activate = ?,
                user_date_comment_last = ?,
                user_rating = ?,
                user_count_vote = ?,
                user_activate = ?,
                user_activate_key = ?,
                user_profile_name = ?,
                user_profile_sex = ?,
                user_profile_country = ?,
                user_profile_region = ?,
                user_profile_city = ?,
                user_profile_birthday = ?,
                user_profile_about = ?,
                user_profile_date = ?,
                user_profile_avatar = ?,
                user_profile_foto = ?,
                user_settings_notice_new_topic = ?,
                user_settings_notice_new_comment = ?,
                user_settings_notice_new_talk = ?,
                user_settings_notice_reply_comment = ?,
                user_settings_notice_new_friend = ?,
                user_settings_timezone = ?,
                user_last_session = ?
            WHERE user_id = ?
        ";
        $bResult = $this->oDb->query(
            $sql,
            $oUser->getPassword(),
            $oUser->getMail(),
            $oUser->getSkill(),
            $oUser->getDateActivate(),
            $oUser->getDateCommentLast(),
            $oUser->getRating(),
            $oUser->getCountVote(),
            $oUser->getActivate(),
            $oUser->getActivationKey(),
            $oUser->getProfileName(),
            $oUser->getProfileSex(),
            $oUser->getProfileCountry(),
            $oUser->getProfileRegion(),
            $oUser->getProfileCity(),
            $oUser->getProfileBirthday(),
            $oUser->getProfileAbout(),
            $oUser->getProfileDate(),
            $oUser->getProfileAvatar(),
            $oUser->getProfilePhoto(),
            $oUser->getSettingsNoticeNewTopic(),
            $oUser->getSettingsNoticeNewComment(),
            $oUser->getSettingsNoticeNewTalk(),
            $oUser->getSettingsNoticeReplyComment(),
            $oUser->getSettingsNoticeNewFriend(),
            $oUser->getSettingsTimezone(),
            $oUser->getLastSession(),
            $oUser->getId()
        );
        return $bResult !== false;
    }

    /**
     * Получить юзера по ключу сессии
     *
     * @param string $sKey    Сессионный ключ
     *
     * @return int|null
     */
    public function GetUserBySessionKey($sKey) {

        $sql
            = "
            SELECT
				s.user_id
			FROM
				?_session AS s
			WHERE
				s.session_key = ?
			LIMIT 1
			";
        if ($nUserId = $this->oDb->selectCell($sql, $sKey)) {
            return intval($nUserId);
        }
        return null;
    }

    /**
     * Создание пользовательской сессии
     *
     * @param ModuleUser_EntitySession $oSession
     *
     * @return bool
     */
    public function CreateSession(ModuleUser_EntitySession $oSession) {

        $sql = "SELECT session_key FROM ?_session WHERE session_key=? LIMIT 1";
        if ($this->oDb->select($sql, $oSession->getKey())) {
            $sql
                = "UPDATE ?_session
                    SET
                        user_id = ?d:user_id ,
                        session_ip_create = ?:ip_create ,
                        session_ip_last = ?:ip_last ,
                        session_date_create = ?:date_create ,
                        session_date_last = ?:date_last ,
                        session_agent_hash = ?:agent_hash
                    WHERE
                        session_key = ?:key
            ";
        } else {
            $sql
                = "INSERT INTO ?_session
                    (
                        session_key,
                        user_id,
                        session_ip_create,
                        session_ip_last,
                        session_date_create,
                        session_date_last,
                        session_agent_hash
                    )
                    VALUES (
                        ?:key ,
                        ?d:user_id ,
                        ?:ip_create ,
                        ?:ip_last ,
                        ?:date_create ,
                        ?:date_last ,
                        ?:agent_hash
                    )
            ";
        }
        $bResult = $this->oDb->sqlQuery(
            $sql,
            array(
                 ':key'         => $oSession->getKey(),
                 ':user_id'     => $oSession->getUserId(),
                 ':ip_create'   => $oSession->getIpCreate(),
                 ':ip_last'     => $oSession->getIpLast(),
                 ':date_create' => $oSession->getDateCreate(),
                 ':date_last'   => $oSession->getDateLast(),
                 ':agent_hash'  => $oSession->getUserAgentHash()
            )
        );
        return ($bResult !== false);
    }

    /**
     * @param $xUser
     * @param $iSessionLimit
     *
     * @return bool
     */
    public function LimitSession($xUser, $iSessionLimit) {

        // Число сессий не может быть меньше 1
        if ($iSessionLimit < 1) {
            return true;
        }

        if (is_object($xUser)) {
            $nUserId = $xUser->GetId();
        } else {
            $nUserId = (int)$xUser;
        }

        $sql
            = "
            SELECT
                session_date_last
            FROM ?_session
            WHERE user_id=?d
            ORDER BY session_date_last DESC
            LIMIT ?d
        ";
        $aRows = $this->oDb->selectCol($sql, $nUserId, $iSessionLimit + 1);
        if ($aRows && count($aRows) > $iSessionLimit) {
            $sDate = end($aRows);
            $sql
                = "
                DELETE FROM ?_session
                WHERE user_id=?d AND session_date_last<=?
            ";
            $this->oDb->query($sql, $nUserId, $sDate);
        }
        return true;
    }

    /**
     * Обновление данных сессии
     *
     * @param ModuleUser_EntitySession $oSession
     *
     * @return int|bool
     */
    public function UpdateSession(ModuleUser_EntitySession $oSession) {

        $sql
            = "UPDATE ?_session
			SET
				session_ip_last = ? ,
				session_date_last = ? ,
				session_exit = ?
			WHERE session_key = ?
		";
        $bResult = $this->oDb->query(
            $sql, $oSession->getIpLast(), $oSession->getDateLast(), $oSession->getDateExit(), $oSession->getKey()
        );
        return $bResult !== false;
    }

    /**
     * Close session of user
     *
     * @param $oSession
     *
     * @return bool
     */
    public function CloseSession($oSession) {

        $sql
            = "
            UPDATE ?_session
            SET
                session_exit = ?
            WHERE session_key = ? AND (session_exit IS NULL OR session_exit = '')
            ";
        return ($this->oDb->query($sql, F::Now(), $oSession->getSessionKey()) !== false);
    }

    /**
     * Closes all sessions of specifier user
     *
     * @param   object|int $oUser
     *
     * @return  bool
     */
    public function CloseUserSessions($oUser) {

        if (is_object($oUser)) {
            $nUserId = $oUser->GetId();
        } else {
            $nUserId = intval($oUser);
        }

        $sql
            = "
            UPDATE ?_session
            SET
                session_exit = ?
            WHERE user_id = ? AND (session_exit IS NULL OR session_exit = '')
            ";
        return ($this->oDb->query($sql, F::Now(), $nUserId) !== false);
    }

    /**
     * Return list of session by user ID and (optionally) by session ID
     *
     * @param array  $aUserId     Список ID пользователей
     * @param string $sSessionKey Список ID пользователей
     *
     * @return ModuleUser_EntitySession[]
     */
    public function GetSessionsByArrayId($aUserId, $sSessionKey = null) {

        if (!is_array($aUserId) || count($aUserId) == 0) {
            return array();
        }

        if ($sSessionKey) {
            $iLimit = count($aUserId) * 2;
        } else {
            $iLimit = count($aUserId);
        }
        $sql
            = "
            SELECT
				s.*
			FROM
			    ?_session AS s
				INNER JOIN ?_user AS u ON s.user_id=u.user_id
			WHERE
				s.user_id IN(?a)
				{AND s.session_key=u.user_last_session AND 1=?d}
				{AND s.session_key=?}
			LIMIT " . $iLimit . "
			";
        $aResult = array();
        $aRows = $this->oDb->select($sql,
            $aUserId,
            !$sSessionKey ? 1 : DBSIMPLE_SKIP,
            $sSessionKey ? $sSessionKey : DBSIMPLE_SKIP);
        if ($aRows) {
            $aResult = E::GetEntityRows('User_Session', $aRows);
        }
        return $aResult;
    }

    /**
     * Список юзеров по ID
     *
     * @param array $aUsersId Список ID пользователей
     *
     * @return array
     */
    public function GetUsersByArrayId($aUsersId) {

        if (!is_array($aUsersId) || count($aUsersId) == 0) {
            return array();
        }

        $sql
            = "
            SELECT
                u.user_id AS ARRAY_KEY,
				u.*,
				ab.banline, ab.banunlim, ab.banactive, ab.bancomment
			FROM
				?_user as u
				LEFT JOIN ?_adminban AS ab ON u.user_id=ab.user_id AND ab.banactive=1
			WHERE
				u.user_id IN(?a)
			LIMIT ?d
			";
        $aUsers = array();
        if ($aRows = $this->oDb->select($sql, $aUsersId, count($aUsersId))) {
            $aUsers = E::GetEntityRows('User', $aRows, $aUsersId);
        }
        return $aUsers;
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
     * @return int|null
     */
    public function GetUserByActivationKey($sKey) {

        $sql
            = "
            SELECT
				u.user_id
			FROM
				?_user as u
			WHERE u.user_activate_key = ?
			LIMIT 1
			";
        if ($aRow = $this->oDb->selectRow($sql, $sKey)) {
            return $aRow['user_id'];
        }
        return null;
    }

    /**
     * Получить юзера по мылу
     *
     * @param string $sMail    Емайл
     *
     * @return int|null
     */
    public function GetUserIdByMail($sMail) {

        return $this->GetUserByMail($sMail);
    }

    /**
     * Получить юзера по мылу
     *
     * @param string $sMail    Емайл
     *
     * @return int|null
     */
    public function GetUserByMail($sMail) {

        $sql
            = "
            SELECT
				u.user_id
			FROM
				?_user as u
			WHERE u.user_mail = ?
			LIMIT 1
			";
        return intval($this->oDb->selectCell($sql, $sMail));
    }

    /**
     * Получить юзера по логину
     *
     * @param string $sLogin Логин пользователя
     *
     * @return int|null
     */
    public function GetUserByLogin($sLogin) {

        return $this->GetUserIdByLogin($sLogin);
    }

    /**
     * Получить ID юзера по логину
     *
     * @param string $sLogin Логин пользователя
     *
     * @return int
     */
    public function GetUserIdByLogin($sLogin) {

        $sql
            = "
            SELECT
				u.user_id
			FROM
				?_user as u
			WHERE
				u.user_login = ?
			LIMIT 1
			";
        return intval($this->oDb->selectCell($sql, $sLogin));
    }

    /**
     * Получить список юзеров по дате последнего визита
     *
     * @param int $iLimit
     * @param bool|null $bSessionExit
     * @param string|null $sSessionTime
     *
     * @return array
     */
    public function GetUsersByDateLast($iLimit, $bSessionExit = null, $sSessionTime = null) {

        $sql
            = "SELECT u.user_id
			FROM
			    ?_user AS u
				INNER JOIN ?_session AS s
				  ON s.session_key = u.user_last_session
				  {AND session_exit IS ?}
				  {AND session_exit IS NOT ?}
				  {AND session_date_last >= ?}
			ORDER BY
				s.session_date_last DESC
			LIMIT 0, ?d
				";
        $aResult = $this->oDb->selectCol(
            $sql,
            ($bSessionExit === false) ? NULL : DBSIMPLE_SKIP,
            ($bSessionExit === true) ? NULL : DBSIMPLE_SKIP,
            $sSessionTime ? $sSessionTime : DBSIMPLE_SKIP,
            $iLimit
        );
        return $aResult ? $aResult : array();
    }

    /**
     * Получить список юзеров по дате регистрации
     *
     * @param int $iLimit    Количество
     *
     * @return array
     */
    public function GetUsersByDateRegister($iLimit) {

        $sql
            = "SELECT
			user_id
			FROM
				?_user
			WHERE
				 user_activate = 1
			ORDER BY
				user_id DESC
			LIMIT 0, ?d
				";
        $aResult = $this->oDb->selectCol($sql, $iLimit);
        return $aResult ? $aResult : array();
    }

//    /**
//     * Возвращает общее количество пользователй
//     *
//     * @return int
//     */
//    public function GetCountUsers() {

//        $sql = "SELECT count(*) as count FROM ?_user";
//        return $this->oDb->selectCell($sql);
//    }

//    public function GetCountAdmins() {

//        $sql = "SELECT count(*) as count FROM ?_user_administrator ";
//        return $this->oDb->selectCell($sql);
//    }

    /**
     * Возвращает количество пользователей по роли
     * @param $iRole
     */
    public function GetCountByRole($iRole) {

        $sql = "SELECT count(user_id) as count FROM ?_user WHERE user_role & ?d";
        return $this->oDb->selectCell($sql, $iRole);

    }

    /**
     * Возвращает количество активных пользователей
     *
     * @param string $sDateActive    Дата
     *
     * @return mixed
     */
    public function GetCountUsersActive($sDateActive) {

        $sql = "SELECT user_id FROM ?_session WHERE session_date_last >= ? GROUP BY user_id";
        $aRows = $this->oDb->select($sql, $sDateActive);
        return $aRows ? count($aRows) : 0;
    }

    /**
     * Возвращает количество пользователей в разрезе полов
     *
     * @return array
     */
    public function GetCountUsersSex() {

        $sql
            = "SELECT user_profile_sex  AS ARRAY_KEY, count(*) as count FROM ?_user WHERE user_activate = 1 GROUP BY user_profile_sex ";
        $result = $this->oDb->select($sql);
        return $result;
    }

    /**
     * Получить список юзеров по первым  буквам логина
     *
     * @param string $sUserLogin    Логин
     * @param int    $iLimit        Количество
     *
     * @return array
     */
    public function GetUsersByLoginLike($sUserLogin, $iLimit) {

        $sql
            = "SELECT
				user_id
			FROM
				?_user
			WHERE
				user_activate = 1
				and
				user_login LIKE ?
			LIMIT 0, ?d
				";
        $aResult = $this->oDb->selectCol($sql, $sUserLogin . '%', $iLimit);
        return $aResult ? $aResult : array();
    }

    /**
     * Добавляет друга
     *
     * @param  ModuleUser_EntityFriend $oFriend    Объект дружбы(связи пользователей)
     *
     * @return bool
     */
    public function AddFriend(ModuleUser_EntityFriend $oFriend) {

        $sql
            = "INSERT INTO ?_friend
			(user_from,
			user_to,
			status_from,
			status_to
			)
			VALUES(?d, ?d, ?d, ?d)
		";
        if (
            $this->oDb->query(
                $sql,
                $oFriend->getUserFrom(),
                $oFriend->getUserTo(),
                $oFriend->getStatusFrom(),
                $oFriend->getStatusTo()
            ) === 0
        ) {
            return true;
        }
        return false;
    }

    /**
     * Удаляет информацию о дружбе из базы данных
     *
     * @param  ModuleUser_EntityFriend $oFriend    Объект дружбы(связи пользователей)
     *
     * @return bool
     */
    public function EraseFriend(ModuleUser_EntityFriend $oFriend) {

        $sql
            = "DELETE FROM ?_friend
			WHERE
				user_from = ?d
				AND
				user_to = ?d
		";
        if ($this->oDb->query($sql, $oFriend->getUserFrom(), $oFriend->getUserTo())) {
            return true;
        }
        return false;
    }

    /**
     * Обновляет информацию о друге
     *
     * @param  ModuleUser_EntityFriend $oFriend    Объект дружбы(связи пользователей)
     *
     * @return bool
     */
    public function UpdateFriend(ModuleUser_EntityFriend $oFriend) {

        $sql
            = "
			UPDATE ?_friend
			SET
				status_from = ?d,
				status_to   = ?d
			WHERE
				user_from = ?d
				AND
				user_to = ?d
		";
        $bResult = $this->oDb->query(
            $sql,
            $oFriend->getStatusFrom(),
            $oFriend->getStatusTo(),
            $oFriend->getUserFrom(),
            $oFriend->getUserTo()
        );
        return $bResult !== false;
    }

    /**
     * Получить список отношений друзей
     *
     * @param  array $aArrayId    Список ID пользователей проверяемых на дружбу
     * @param  int   $nUserId     ID пользователя у которого проверяем друзей
     *
     * @return array
     */
    public function GetFriendsByArrayId($aArrayId, $nUserId) {

        if (!is_array($aArrayId) || count($aArrayId) == 0) {
            return array();
        }

        $sql
            = "SELECT
					*
				FROM
					?_friend
				WHERE
					( `user_from`=?d AND `user_to` IN(?a) )
					OR
					( `user_from` IN(?a) AND `user_to`=?d )
				";
        $aRows = $this->oDb->select(
            $sql,
            $nUserId, $aArrayId,
            $aArrayId, $nUserId
        );
        $aRes = array();
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aRow['user'] = $nUserId;
                $aRes[] = E::GetEntity('User_Friend', $aRow);
            }
        }
        return $aRes;
    }

    /**
     * Получает список друзей
     *
     * @param  int $nUserId      ID пользователя
     * @param  int $iCount       Возвращает общее количество элементов
     * @param  int $iCurrPage    Номер страницы
     * @param  int $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetUsersFriend($nUserId, &$iCount, $iCurrPage, $iPerPage) {

        $sql
            = "SELECT
					uf.user_from,
					uf.user_to
				FROM
					?_friend as uf
				WHERE
					( uf.user_from = ?d
					OR
					uf.user_to = ?d )
					AND
					( 	uf.status_from + uf.status_to = ?d
					OR
						(uf.status_from = ?d AND uf.status_to = ?d )
					)
				LIMIT ?d, ?d ;";
        $aUsers = array();
        $aRows = $this->oDb->selectPage(
            $iCount,
            $sql,
            $nUserId,
            $nUserId,
            ModuleUser::USER_FRIEND_ACCEPT + ModuleUser::USER_FRIEND_OFFER,
            ModuleUser::USER_FRIEND_ACCEPT,
            ModuleUser::USER_FRIEND_ACCEPT,
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );
        if ($aRows) {
            foreach ($aRows as $aUser) {
                $aUsers[] = ($aUser['user_from'] == $nUserId)
                    ? $aUser['user_to']
                    : $aUser['user_from'];
            }
        }
        rsort($aUsers, SORT_NUMERIC);
        return array_unique($aUsers);
    }

    /**
     * Получает количество друзей
     *
     * @param  int $nUserId    ID пользователя
     *
     * @return int
     */
    public function GetCountUsersFriend($nUserId) {

        $sql
            = "SELECT
					count(*) as c
				FROM
					?_friend as uf
				WHERE
					( uf.user_from = ?d
					OR
					uf.user_to = ?d )
					AND
					( 	uf.status_from + uf.status_to = ?d
					OR
						(uf.status_from = ?d AND uf.status_to = ?d )
					)";
        $aRow = $this->oDb->selectRow(
            $sql,
            $nUserId,
            $nUserId,
            ModuleUser::USER_FRIEND_ACCEPT + ModuleUser::USER_FRIEND_OFFER,
            ModuleUser::USER_FRIEND_ACCEPT,
            ModuleUser::USER_FRIEND_ACCEPT
        );
        if ($aRow) {
            return $aRow['c'];
        }
        return 0;
    }

    /**
     * Получить список заявок на добавление в друзья от указанного пользователя
     *
     * @param  string $nUserId
     * @param  int    $nStatus Статус запроса со стороны добавляемого
     *
     * @return array
     */
    public function GetUsersFriendOffer($nUserId, $nStatus = ModuleUser::USER_FRIEND_NULL) {

        $sql
            = "SELECT
					uf.user_to
				FROM
					?_friend as uf
				WHERE
					uf.user_from = ?d
					AND
					uf.status_from = ?d
					AND
					uf.status_to = ?d
				;";
        $aUsers = array();
        $aRows = $this->oDb->select(
            $sql,
            $nUserId,
            ModuleUser::USER_FRIEND_OFFER,
            $nStatus
        );
        if ($aRows) {
            foreach ($aRows as $aUser) {
                $aUsers[] = $aUser['user_to'];
            }
        }
        return $aUsers;
    }

    /**
     * Получить список заявок на добавление в друзья от указанного пользователя
     *
     * @param  string $nUserId
     * @param  int    $nStatus Статус запроса со стороны самого пользователя
     *
     * @return array
     */
    public function GetUserSelfFriendOffer($nUserId, $nStatus = ModuleUser::USER_FRIEND_NULL) {

        $sql
            = "SELECT
					uf.user_from
				FROM
					?_friend as uf
				WHERE
					uf.user_to = ?d
					AND
					uf.status_from = ?d
					AND
					uf.status_to = ?d
				;";
        $aUsers = array();
        $aRows = $this->oDb->select(
            $sql,
            $nUserId,
            ModuleUser::USER_FRIEND_OFFER,
            $nStatus
        );
        if ($aRows) {
            foreach ($aRows as $aUser) {
                $aUsers[] = $aUser['user_from'];
            }
        }
        return $aUsers;
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

        $sql = "SELECT * FROM ?_invite WHERE invite_code = ? AND invite_used = ?d ";
        if ($aRow = $this->oDb->selectRow($sql, $sCode, $iUsed)) {
            return E::GetEntity('User_Invite', $aRow);
        }
        return null;
    }

    /**
     * Добавляет новый инвайт
     *
     * @param ModuleUser_EntityInvite $oInvite    Объект инвайта
     *
     * @return int|bool
     */
    public function AddInvite(ModuleUser_EntityInvite $oInvite) {

        $sql
            = "INSERT INTO ?_invite
			(invite_code,
			user_from_id,
			invite_date_add
			)
			VALUES(?,  ?,	?)
		";
        $nId = $this->oDb->query($sql, $oInvite->getCode(), $oInvite->getUserFromId(), $oInvite->getDateAdd());
        return $nId ? $nId : false;
    }

    /**
     * Обновляет инвайт
     *
     * @param ModuleUser_EntityInvite $oInvite    бъект инвайта
     *
     * @return bool
     */
    public function UpdateInvite(ModuleUser_EntityInvite $oInvite) {

        $sql
            = "UPDATE ?_invite
			SET
				user_to_id = ? ,
				invite_date_used = ? ,
				invite_used =?
			WHERE invite_id = ?
		";
        $bResult = $this->oDb->query(
            $sql, $oInvite->getUserToId(), $oInvite->getDateUsed(), $oInvite->getUsed(), $oInvite->getId()
        );
        return $bResult !== false;
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

        $sql = "SELECT COUNT(invite_id) AS count FROM ?_invite WHERE user_from_id = ?d AND invite_date_add >= ? ";
        if ($aRow = $this->oDb->selectRow($sql, $nUserIdFrom, $sDate)) {
            return $aRow['count'];
        }
        return 0;
    }

    /**
     * Получает полное число использованных приглашений юзера
     *
     * @param int $nUserIdFrom    ID пользователя
     *
     * @return int
     */
    public function GetCountInviteUsed($nUserIdFrom) {

        $sql = "SELECT COUNT(invite_id) AS count FROM ?_invite WHERE user_from_id = ?d";
        if ($aRow = $this->oDb->selectRow($sql, $nUserIdFrom)) {
            return $aRow['count'];
        }
        return 0;
    }

    /**
     * Получает список приглашенных юзеров
     *
     * @param int $nUserId    ID пользователя
     *
     * @return array
     */
    public function GetUsersInvite($nUserId) {

        $sql
            = "
            SELECT
				i.user_to_id
			FROM
				?_invite as i
			WHERE
				i.user_from_id = ?d
			";
        $aUsers = $this->oDb->selectCol($sql, $nUserId);
        return (array)$aUsers;
    }

    /**
     * Получает юзера который пригласил
     *
     * @param int $nUserIdTo    ID пользователя
     *
     * @return int|null
     */
    public function GetUserInviteFrom($nUserIdTo) {

        $sql
            = "SELECT
					i.user_from_id
				FROM
					?_invite as i
				WHERE
					i.user_to_id = ?d
				LIMIT 0,1;
					";
        if ($aRow = $this->oDb->selectRow($sql, $nUserIdTo)) {
            return $aRow['user_from_id'];
        }
        return null;
    }

    /**
     * Добавляем воспоминание(восстановление) пароля
     *
     * @param ModuleUser_EntityReminder $oReminder    Объект восстановления пароля
     *
     * @return bool
     */
    public function AddReminder(ModuleUser_EntityReminder $oReminder) {

        $sql = "
            DELETE FROM ?_reminder WHERE reminder_code=? OR user_id=?d
        ";
        $this->oDb->query($sql, $oReminder->getCode(), $oReminder->getUserId());
        $sql = "
            INSERT INTO ?_reminder
            (
                reminder_code,
                user_id,
                reminder_date_add,
                reminder_date_used,
                reminder_date_expire,
                reminde_is_used
            )
            VALUES (
                ? ,
                ? ,
                ? ,
                ? ,
                ? ,
                ?
            )
        ";
        $xResult = $this->oDb->query(
            $sql,
            $oReminder->getCode(),
            $oReminder->getUserId(),
            $oReminder->getDateAdd(),
            $oReminder->getDateUsed(),
            $oReminder->getDateExpire(),
            $oReminder->getIsUsed()
        );
        return $xResult !== false;
    }

    /**
     * Сохраняем воспомнинание(восстановление) пароля
     *
     * @param ModuleUser_EntityReminder $oReminder    Объект восстановления пароля
     *
     * @return bool
     */
    public function UpdateReminder(ModuleUser_EntityReminder $oReminder) {

        return $this->AddReminder($oReminder);
    }

    /**
     * Получаем запись восстановления пароля по коду
     *
     * @param string $sCode    Код восстановления пароля
     *
     * @return ModuleUser_EntityReminder|null
     */
    public function GetReminderByCode($sCode) {

        $sql
            = "SELECT
					*
				FROM
					?_reminder
				WHERE
					reminder_code = ?
				LIMIT 1
				";
        if ($aRow = $this->oDb->selectRow($sql, $sCode)) {
            return E::GetEntity('User_Reminder', $aRow);
        }
        return null;
    }

    /**
     * Получить дополнительные поля профиля пользователя
     *
     * @param array|null $aType Типы полей, null - все типы
     *
     * @return ModuleUser_EntityField[]
     */
    public function getUserFields($aType = array()) {

        if (!is_null($aType) && !is_array($aType)) {
            $aType = array($aType);
        }
        $sql = "
            SELECT *
            FROM ?_user_field
            WHERE
              1=1
              { AND type IN (?a) }";
        $aFields = $this->oDb->select($sql, empty($aType) ? DBSIMPLE_SKIP : $aType);
        if (empty($aFields)) {
            return array();
        }
        $aResult = array();
        foreach ($aFields as $aField) {
            $aResult[$aField['id']] = E::GetEntity('User_Field', $aField);
        }
        return $aResult;
    }

    /**
     * Получить по имени поля его значение для определённого пользователя
     *
     * @param int    $nUserId    ID пользователя
     * @param string $sName      Имя поля
     *
     * @return string
     */
    public function getUserFieldValueByName($nUserId, $sName) {

        $sql
            = "
            SELECT value
            FROM ?_user_field_value
            WHERE
                user_id = ?d
                AND
                field_id = (SELECT id FROM ?_user_field WHERE name =?)";
        $ret = $this->oDb->selectCol($sql, $nUserId, $sName);
        return $ret[0];
    }

    /**
     * Получить значения дополнительных полей профиля пользователя
     *
     * @param int   $iUserId       ID пользователя
     * @param array $aType         Типы полей, null - все типы
     *
     * @return ModuleUser_EntityField[]
     */
    public function getUserFieldsValues($iUserId, $aType) {

        if (!is_null($aType) && !is_array($aType)) {
            $aType = array($aType);
        }

        $sql
            = "
                SELECT v.value, f.*
                FROM ?_user_field_value AS v
                  JOIN ?_user_field AS f ON f.id = v.field_id
                WHERE
                    v.user_id = ?d
                    { AND f.type IN (?a) }";

        $aRows = $this->oDb->select($sql, $iUserId, empty($aType) ? DBSIMPLE_SKIP : $aType);
        $aResult = array();
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aResult[] = E::GetEntity('User_Field', $aRow);
            }
        }

        return $aResult;
    }

    /**
     * Установить значения дополнительных полей профиля пользователя
     *
     * @param int   $nUserId    ID пользователя
     * @param array $aFields    Ассоциативный массив полей id => value
     * @param int   $iCountMax  Максимальное количество одинаковых полей
     *
     * @return bool
     */
    public function setUserFieldsValues($nUserId, $aFields, $iCountMax) {

        if (!count($aFields)) {
            return;
        }
        foreach ($aFields as $iId => $sValue) {
            $sql = "SELECT count(*) as c FROM ?_user_field_value WHERE user_id = ?d AND field_id = ?";
            $aRow = $this->oDb->selectRow($sql, $nUserId, $iId);
            $iCount = isset($aRow['c']) ? $aRow['c'] : 0;
            if ($iCount < $iCountMax) {
                $sql = "INSERT INTO ?_user_field_value(value, user_id, field_id) VALUES (?, ?d, ?)";
            } elseif ($iCount == $iCountMax && $iCount == 1) {
                $sql = "UPDATE ?_user_field_value SET value = ? WHERE user_id = ?d AND field_id = ?";
            } else {
                continue;
            }
            $this->oDb->query($sql, $sValue, $nUserId, $iId);
        }
    }

    /**
     * Добавить поле
     *
     * @param ModuleUser_EntityField $oField    Объект пользовательского поля
     *
     * @return bool
     */
    public function addUserField($oField) {

        $sql
            = "
            INSERT INTO ?_user_field
            (
                name,
                title,
                pattern,
                type
            )
            VALUES (
                ?,
                ?,
                ?,
                ?
            )
            ";
        $xResult = $this->oDb->query(
            $sql, $oField->getName(), $oField->getTitle(), $oField->getPattern(), $oField->getType()
        );
        return $xResult ? $xResult : false;
    }

    /**
     * Удалить поле
     *
     * @param int $iId    ID пользовательского поля
     *
     * @return bool
     */
    public function deleteUserField($iId) {

        $sql = 'DELETE FROM ?_user_field_value WHERE field_id = ?d';
        $this->oDb->query($sql, $iId);
        $sql
            = 'DELETE FROM ?_user_field WHERE
                    id = ?d';
        $this->oDb->query($sql, $iId);
        return true;
    }

    /**
     * Изменить поле
     *
     * @param ModuleUser_EntityField $oField    Объект пользовательского поля
     *
     * @return bool
     */
    public function updateUserField($oField) {

        $sql
            = '
            UPDATE ?_user_field
            SET
                name = ?,
                title = ?,
                pattern = ?,
                type = ?
            WHERE id = ?d';
        $xResult = $this->oDb->query(
            $sql,
            $oField->getName(),
            $oField->getTitle(),
            $oField->getPattern(),
            $oField->getType(),
            $oField->getId()
        );
        return $xResult;
    }

    /**
     * Проверяет существует ли поле с таким именем
     *
     * @param string   $sName  Имя поля
     * @param int|null $nId    ID поля
     *
     * @return bool
     */
    public function userFieldExistsByName($sName, $nId) {

        $sql = 'SELECT id FROM  ?_user_field WHERE name = ? {AND id != ?d}';
        return $this->oDb->select($sql, $sName, $nId ? $nId : DBSIMPLE_SKIP);
    }

    /**
     * Проверяет существует ли поле с таким ID
     *
     * @param int $nId    ID поля
     *
     * @return bool
     */
    public function userFieldExistsById($nId) {

        $sql = "SELECT id FROM  ?_user_field WHERE id = ?d";
        return $this->oDb->select($sql, $nId);
    }

    /**
     * Удаляет у пользователя значения полей
     *
     * @param   int|array  $aUsersId   ID пользователя или массив ID
     * @param   array|null $aTypes     Список типов для удаления
     *
     * @return  bool
     */
    public function DeleteUserFieldValues($aUsersId, $aTypes = null) {

        $aUsersId = $this->_arrayId($aUsersId);
        if (!$aTypes) {
            $sql
                = "
                DELETE FROM ?_user_field_value
                WHERE user_id IN (?a)
            ";
            return $this->oDb->query($sql, $aUsersId) !== false;
        } else {
            if (!is_array($aTypes)) {
                $aTypes = array($aTypes);
            }
            $sql
                = "
                DELETE FROM ?_user_field_value
                WHERE user_id IN (?a) AND field_id IN
                    (SELECT id FROM ?_user_field WHERE type IN (?a))
            ";
            return $this->oDb->query($sql, $aUsersId, $aTypes);
        }
    }

    /**
     * Возвращает список заметок пользователя
     *
     * @param int $iUserId      ID пользователя
     * @param int $iCount       Возвращает общее количество элементов
     * @param int $iCurrPage    Номер страницы
     * @param int $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetUserNotesByUserId($iUserId, &$iCount, $iCurrPage, $iPerPage) {

        $sql
            = "
			SELECT *
			FROM
				?_user_note
			WHERE
				user_id = ?d
			ORDER BY id DESC
			LIMIT ?d, ?d ";
        $aResult = array();
        if ($aRows = $this->oDb->selectPage($iCount, $sql, $iUserId, ($iCurrPage - 1) * $iPerPage, $iPerPage)) {
            $aResult = E::GetEntityRows('ModuleUser_EntityNote', $aRows);
        }
        return $aResult;
    }

    /**
     * Возвращает количество заметок у пользователя
     *
     * @param int $iUserId    ID пользователя
     *
     * @return int
     */
    public function GetCountUserNotesByUserId($iUserId) {

        $sql
            = "
			SELECT COUNT(*) as c
			FROM
				?_user_note
			WHERE
				user_id = ?d
			";
        $nCnt = $this->oDb->selectCell($sql, $iUserId);
        return $nCnt ? $nCnt : 0;
    }

    /**
     * Возвращет заметку по автору и пользователю
     *
     * @param int $iTargetUserId    ID пользователя о ком заметка
     * @param int $iUserId          ID пользователя автора заметки
     *
     * @return ModuleUser_EntityNote|null
     */
    public function GetUserNote($iTargetUserId, $iUserId) {

        $sql = "SELECT * FROM ?_user_note WHERE target_user_id = ?d AND user_id = ?d ";
        if ($aRow = $this->oDb->selectRow($sql, $iTargetUserId, $iUserId)) {
            return E::GetEntity('ModuleUser_EntityNote', $aRow);
        }
        return null;
    }

    /**
     * Возвращает заметку по ID
     *
     * @param int $iId    ID заметки
     *
     * @return ModuleUser_EntityNote|null
     */
    public function GetUserNoteById($iId) {

        $sql = "
            SELECT *
            FROM ?_user_note
            WHERE id = ?d
            LIMIT 1
            ";
        if ($aRow = $this->oDb->selectRow($sql, $iId)) {
            return E::GetEntity('ModuleUser_EntityNote', $aRow);
        }
        return null;
    }

    /**
     * Возвращает список заметок пользователя по ID целевых юзеров
     *
     * @param array $aArrayId    Список ID целевых пользователей
     * @param int   $nUserId     ID пользователя, кто оставлял заметки
     *
     * @return array
     */
    public function GetUserNotesByArrayUserId($aArrayId, $nUserId) {

        if (!is_array($aArrayId) || count($aArrayId) == 0) {
            return array();
        }
        $sql
            = "SELECT
					*
				FROM
					?_user_note
				WHERE target_user_id IN (?a) AND user_id = ?d
				";
        $aRows = $this->oDb->select($sql, $aArrayId, $nUserId);
        $aResult = array();
        if ($aRows) {
            $aResult = E::GetEntityRows('ModuleUser_EntityNote', $aRows);
        }
        return $aResult;
    }

    /**
     * Удаляет заметку по ID
     *
     * @param int $iId    ID заметки
     *
     * @return bool
     */
    public function DeleteUserNoteById($iId) {

        $sql = "DELETE FROM ?_user_note WHERE id = ?d ";
        return $this->oDb->query($sql, $iId);
    }

    /**
     * Добавляет заметку
     *
     * @param ModuleUser_EntityNote $oNote    Объект заметки
     *
     * @return int|null
     */
    public function AddUserNote($oNote) {

        $sql = "INSERT INTO ?_user_note(?#) VALUES(?a)";
        $iId = $this->oDb->query($sql, $oNote->getKeyProps(), $oNote->getValProps());
        return $iId ? $iId : false;
    }

    /**
     * Обновляет заметку
     *
     * @param ModuleUser_EntityNote $oNote    Объект заметки
     *
     * @return int
     */
    public function UpdateUserNote($oNote) {

        $sql
            = "UPDATE ?_user_note
			SET
			 	text = ?
			WHERE id = ?d
		";
        $bResult = $this->oDb->query($sql, $oNote->getText(), $oNote->getId());
        return $bResult !== false;
    }

    /**
     * Добавляет запись о смене емайла
     *
     * @param ModuleUser_EntityChangemail $oChangemail    Объект смены емайла
     *
     * @return int|null
     */
    public function AddUserChangemail($oChangemail) {

        $sql = "INSERT INTO ?_user_changemail(?#) VALUES (?a)";
        $iId = $this->oDb->query($sql, $oChangemail->getKeyProps(), $oChangemail->getValProps());
        return $iId ? $iId : false;
    }

    /**
     * Обновляет запись о смене емайла
     *
     * @param ModuleUser_EntityChangemail $oChangemail    Объект смены емайла
     *
     * @return int
     */
    public function UpdateUserChangemail($oChangemail) {

        $sql
            = "UPDATE ?_user_changemail
			SET
			 	date_used = ?,
			 	confirm_from = ?d,
			 	confirm_to = ?d
			WHERE id = ?d
		";
        $bResult = $this->oDb->query(
            $sql, $oChangemail->getDateUsed(), $oChangemail->getConfirmFrom(), $oChangemail->getConfirmTo(),
            $oChangemail->getId()
        );
        return $bResult !== false;
    }

    /**
     * Возвращает объект смены емайла по коду подтверждения
     *
     * @param string $sCode Код подтверждения
     *
     * @return ModuleUser_EntityChangemail|null
     */
    public function GetUserChangemailByCodeFrom($sCode) {

        $sql = "
            SELECT *
            FROM ?_user_changemail
            WHERE code_from = ?
            LIMIT 1
            ";
        if ($aRow = $this->oDb->selectRow($sql, $sCode)) {
            return E::GetEntity('ModuleUser_EntityChangemail', $aRow);
        }
        return null;
    }

    /**
     * Возвращает объект смены емайла по коду подтверждения
     *
     * @param string $sCode Код подтверждения
     *
     * @return ModuleUser_EntityChangemail|null
     */
    public function GetUserChangemailByCodeTo($sCode) {

        $sql = "
            SELECT *
            FROM ?_user_changemail
            WHERE code_to = ?
            LIMIT 1
            ";
        if ($aRow = $this->oDb->selectRow($sql, $sCode)) {
            return E::GetEntity('ModuleUser_EntityChangemail', $aRow);
        }
        return null;
    }

    /**
     * Возвращает список пользователей по фильтру
     *
     * @param array $aFilter      Фильтр
     * @param array $aOrder       Сортировка
     * @param int   $iCount       Возвращает общее количество элементов
     * @param int   $iCurrPage    Номер страницы
     * @param int   $iPerPage     Количество элментов на страницу
     *
     * @return array
     */
    public function GetUsersByFilter($aFilter, $aOrder, &$iCount, $iCurrPage, $iPerPage) {

        if (isset($aFilter['login']) && $aFilter['login'] && is_string($aFilter['login'])) {
            if (strpos($aFilter['login'], '%') === false) {
                $aFilter['login'] .= '%';
            }
        }
        if (isset($aFilter['regdate']) && $aFilter['regdate']) {
            if (strpos($aFilter['regdate'], '%') === false) {
                $aFilter['regdate'] .= '%';
            }
        }
        if (isset($aFilter['ip']) && $aFilter['ip']) {
            $aFilter['ip_register'] = F::IpRange($aFilter['ip']);
        }
        if (isset($aFilter['session.session_exit'])) {
            $bJoinSession = true;
        } else {
            $bJoinSession = false;
        }

        $aOrderAllow = array(
            'user_id',
            'user_login',
            'user_date_register',
            'user_rating',
            'user_skill',
            'user_profile_name',
            'session.session_date_last',
        );
        $sOrder = '';
        if (is_array($aOrder) && $aOrder) {
            foreach ($aOrder as $sKey => $sValue) {
                $sValue = strtoupper($sValue);
                if (!in_array($sKey, $aOrderAllow)) {
                    unset($aOrder[$sKey]);
                } elseif ($sValue == 'ASC' || $sValue == 'DESC') {
                    if (strpos($sKey, 'session.') === 0) {
                        $bJoinSession = true;
                        $sKey = str_replace('session.', 's.', $sKey);
                    }
                    $sOrder .= " {$sKey} {$sValue},";
                }
            }
            $sOrder = trim($sOrder, ',');
        }
        if ($sOrder == '') {
            $sOrder = ' user_id desc ';
        }
        $sOrder = str_replace(' user_id ', ' u.user_id ', $sOrder);

        $sql = "SELECT
					u.user_id
				FROM
					?_user AS u
				    { LEFT JOIN ?_session AS s ON s.session_key=u.user_last_session AND 1=?}
				WHERE
					1 = 1
					{ AND u.user_id = ?d }
					{ AND user_mail = ? }
					{ AND user_password = ? }
					{ AND (INET_ATON(user_ip_register) BETWEEN INET_ATON(?) AND  INET_ATON(?))}
					{ AND user_activate = ?d }
					{ AND user_activate_key = ? }
					{ AND user_profile_sex = ? }
					{ AND user_login LIKE ? }
					{ AND user_login IN (?a) }
					{ AND user_date_register LIKE ? }
					{ AND user_profile_name LIKE ? }
					{ AND user_role & ?d}
					{ AND user_role & ~ ?d}
					{ AND user_role & ?d}
					{ AND user_role & ~ ?d}
					{ AND user_role & ?d}
					{ AND s.session_exit IS NULL AND 1=? }
					{ AND s.session_exit IS NOT NULL AND 1=? }
				ORDER by {$sOrder}
				LIMIT ?d, ?d ;
					";
        $aResult = array();
        $aRows = $this->oDb->selectPage(
            $iCount, $sql,
            $bJoinSession ? 1 : DBSIMPLE_SKIP,
            isset($aFilter['id']) ? $aFilter['id'] : DBSIMPLE_SKIP,
            isset($aFilter['email']) ? $aFilter['email'] : DBSIMPLE_SKIP,
            isset($aFilter['password']) ? $aFilter['password'] : DBSIMPLE_SKIP,
            isset($aFilter['ip_register']) ? $aFilter['ip_register'][0] : DBSIMPLE_SKIP,
            isset($aFilter['ip_register']) ? $aFilter['ip_register'][1] : DBSIMPLE_SKIP,
            isset($aFilter['activate']) ? $aFilter['activate'] : DBSIMPLE_SKIP,
            isset($aFilter['activate_key']) ? $aFilter['activate_key'] : DBSIMPLE_SKIP,
            isset($aFilter['profile_sex']) ? $aFilter['profile_sex'] : DBSIMPLE_SKIP,
            (isset($aFilter['login']) && is_string($aFilter['login'])) ? $aFilter['login'] : DBSIMPLE_SKIP,
            (isset($aFilter['login']) && is_array($aFilter['login'])) ? $aFilter['login'] : DBSIMPLE_SKIP,
            (isset($aFilter['regdate']) && $aFilter['regdate']) ? $aFilter['regdate'] : DBSIMPLE_SKIP,
            isset($aFilter['profile_name']) ? $aFilter['profile_name'] : DBSIMPLE_SKIP,
            (isset($aFilter['admin']) && $aFilter['admin']) ? ModuleUser::USER_ROLE_ADMINISTRATOR : DBSIMPLE_SKIP,
            (isset($aFilter['admin']) && !$aFilter['admin']) ? ModuleUser::USER_ROLE_ADMINISTRATOR : DBSIMPLE_SKIP,
            (isset($aFilter['moderator']) && $aFilter['moderator']) ? ModuleUser::USER_ROLE_MODERATOR : DBSIMPLE_SKIP,
            (isset($aFilter['moderator']) && !$aFilter['moderator']) ? ModuleUser::USER_ROLE_MODERATOR : DBSIMPLE_SKIP,
            (isset($aFilter['role']) && $aFilter['role']) ? $aFilter['role'] : DBSIMPLE_SKIP,
            (isset($aFilter['session.session_exit']) && !$aFilter['session.session_exit']) ? 1 : DBSIMPLE_SKIP,
            (isset($aFilter['session.session_exit']) && $aFilter['session.session_exit']) ? 1 : DBSIMPLE_SKIP,
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aResult[] = $aRow['user_id'];
            }
        }
        return $aResult;
    }

    /**
     * Возвращает список префиксов логинов пользователей (для алфавитного указателя)
     *
     * @param int $iPrefixLength    Длина префикса
     *
     * @return array
     */
    public function GetGroupPrefixUser($iPrefixLength = 1) {

        $sql
            = "
			SELECT SUBSTRING(`user_login` FROM 1 FOR ?d ) as prefix
			FROM
				?_user
			WHERE
				user_activate = 1
			GROUP BY prefix
			ORDER BY prefix ";
        $aResult = array();
        if ($aRows = $this->oDb->select($sql, $iPrefixLength)) {
            foreach ($aRows as $aRow) {
                $aResult[] = mb_strtoupper($aRow['prefix'], 'utf-8');
            }
        }
        return $aResult;
    }

    /**
     * issue 258 {@link https://github.com/altocms/altocms/issues/258}
     * Проверяет забанен ли пользователь или нет
     *
     * @param $sIp
     * @return bool
     */
    public function IpIsBanned($sIp) {

        $sql = "SELECT id FROM ?_adminips WHERE
                    INET_ATON(?) >= ip1 AND INET_ATON(?) <= ip2
                    AND banactive = ?d
                    AND banline > ?";

        $aRows = $this->oDb->select($sql, $sIp, $sIp, 1, date('Y-m-d H:i:s'));

        if ($aRows) {
            return TRUE;
        }

        return FALSE;

    }

}

// EOF
