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
 * Объект маппера для работы с БД
 *
 * @package modules.talk
 * @since   1.0
 */
class ModuleTalk_MapperTalk extends Mapper {

    /**
     * Добавляет новую тему разговора
     *
     * @param ModuleTalk_EntityTalk $oTalk Объект сообщения
     *
     * @return int|bool
     */
    public function AddTalk(ModuleTalk_EntityTalk $oTalk) {

        $sql = "INSERT INTO ?_talk
			(user_id,
			talk_title,
			talk_text,
			talk_date,
			talk_date_last,
			talk_user_id_last,
			talk_user_ip
			)
			VALUES(?d, ?, ?, ?, ?, ?, ?)
		";
        $iId = $this->oDb->query(
            $sql, $oTalk->getUserId(), $oTalk->getTitle(), $oTalk->getText(), $oTalk->getDate(), $oTalk->getDateLast(),
            $oTalk->getUserIdLast(), $oTalk->getUserIp()
        );
        return $iId ? $iId : false;
    }

    /**
     * Удаление письма из БД
     *
     * @param int $iTalkId    ID разговора
     *
     * @return bool
     */
    public function DeleteTalk($iTalkId) {

        // Удаление беседы
        $sql = 'DELETE FROM ?_talk  WHERE talk_id = ?d';
        $this->oDb->query($sql, $iTalkId);
        // Физическое удаление пользователей беседы (не флагом)
        $sql = 'DELETE FROM ?_talk_user  WHERE talk_id = ?d';
        $this->oDb->query($sql, $iTalkId);

        return true;
    }

    /**
     * Обновление разговора
     *
     * @param ModuleTalk_EntityTalk $oTalk    Объект сообщения
     *
     * @return int
     */
    public function UpdateTalk(ModuleTalk_EntityTalk $oTalk) {

        $sql = "UPDATE ?_talk SET
				talk_date_last = ? ,
				talk_user_id_last = ? ,
				talk_comment_id_last = ? ,
				talk_count_comment = ?
			WHERE 
				talk_id = ?d
		";
        $bResult = $this->oDb->query(
            $sql, $oTalk->getDateLast(), $oTalk->getUserIdLast(), $oTalk->getCommentIdLast(), $oTalk->getCountComment(),
            $oTalk->getId()
        );
        return $bResult !== false;
    }

    /**
     * Получить список разговоров по списку айдишников
     *
     * @param array $aTalkId    Список ID сообщений
     *
     * @return array
     */
    public function GetTalksByArrayId($aTalkId) {

        if (!$aTalkId) {
            return array();
        }
        if (!is_array($aTalkId)) {
            $aTalkId = array(intval($aTalkId));
        }

        $nLimit = sizeof($aTalkId);
        $sql
            = "SELECT
                    t.talk_id AS ARRAY_KEYS,
                    t.*
                FROM
                    ?_talk AS t
                WHERE
                    t.talk_id IN(?a)
                LIMIT $nLimit";
        $aTalks = array();
        if ($aRows = $this->oDb->select($sql, $aTalkId)) {
            $aTalks = E::GetEntityRows('Talk', $aRows, $aTalkId);
        }
        return $aTalks;
    }

    /**
     * Получить список отношений разговор-юзер по списку айдишников
     *
     * @param array $aTalkId    Список ID сообщений
     * @param int   $sUserId     ID пользователя
     *
     * @return array
     */
    public function GetTalkUserByArray($aTalkId, $sUserId) {

        if (!is_array($aTalkId) || count($aTalkId) == 0) {
            return array();
        }

        $sql
            = "SELECT
					t.*
				FROM 
					?_talk_user as t
				WHERE 
					t.talk_id IN(?a)
					AND
					t.user_id = ?d
				";
        $aTalkUsers = array();
        if ($aRows = $this->oDb->select($sql, $aTalkId, $sUserId)) {
            $aTalkUsers = E::GetEntityRows('Talk_TalkUser', $aRows);
        }
        return $aTalkUsers;
    }

    /**
     * Получает тему разговора по айдишнику
     *
     * @param int $iTalkId    ID сообщения
     *
     * @return ModuleTalk_EntityTalk|null
     */
    public function GetTalkById($iTalkId) {

        $sql
            = "SELECT
				t.*,
				u.user_login as user_login
				FROM 
					?_talk as t,
					?_user as u
				WHERE 
					t.talk_id = ?d
					AND
					t.user_id=u.user_id
					";

        if ($aRow = $this->oDb->selectRow($sql, $iTalkId)) {
            return E::GetEntity('Talk', $aRow);
        }
        return null;
    }

    /**
     * Добавляет юзера к разговору(теме)
     *
     * @param ModuleTalk_EntityTalkUser $oTalkUser    Объект связи пользователя и сообщения(разговора)
     *
     * @return bool
     */
    public function AddTalkUser(ModuleTalk_EntityTalkUser $oTalkUser) {

        $sql = "
            SELECT user_id
            FROM ?_talk_user
            WHERE talk_id=?d AND user_id=?d
            LIMIT 1
        ";
        if ($this->oDb->query($sql, $oTalkUser->getTalkId(), $oTalkUser->getUserId())) {
            $sql = "
                UPDATE ?_talk_user
                SET talk_user_active = ?d
                WHERE talk_id=?d AND user_id=?d
            ";
            $xResult = $this->oDb->query(
                $sql,
                $oTalkUser->getUserActive(),
                $oTalkUser->getTalkId(),
                $oTalkUser->getUserId()
            );
        } else {
            $sql = "
                INSERT INTO ?_talk_user (
                    talk_id,
                    user_id,
                    date_last,
                    talk_user_active
                )
			    VALUES(?d, ?d, ?, ?d)
		";
            $xResult = $this->oDb->query(
                $sql,
                $oTalkUser->getTalkId(),
                $oTalkUser->getUserId(),
                $oTalkUser->getDateLast(),
                $oTalkUser->getUserActive()
            );
        }
        return $xResult !== false;
    }

    /**
     * Обновляет связку разговор-юзер
     *
     * @param ModuleTalk_EntityTalkUser $oTalkUser    Объект связи пользователя с разговором
     *
     * @return bool
     */
    public function UpdateTalkUser(ModuleTalk_EntityTalkUser $oTalkUser) {

        $sql = "UPDATE ?_talk_user
			SET 
				date_last = ?,
				comment_id_last = ?d,
				comment_count_new = ?d,
				talk_user_active = ?d
			WHERE
				talk_id = ?d
				AND
				user_id = ?d
		";

        $bResult = $this->oDb->query(
            $sql,
            $oTalkUser->getDateLast(),
            $oTalkUser->getCommentIdLast(),
            $oTalkUser->getCommentCountNew(),
            $oTalkUser->getUserActive(),
            $oTalkUser->getTalkId(),
            $oTalkUser->getUserId()
        );
        return $bResult !== false;
    }

    /**
     * Удаляет юзера из разговора
     *
     * @param array $aTalkId    Список ID сообщений
     * @param int   $sUserId    ID пользователя
     * @param int   $iActive    Статус связи
     *
     * @return bool
     */
    public function DeleteTalkUserByArray($aTalkId, $sUserId, $iActive) {

        if (!is_array($aTalkId)) {
            $aTalkId = array($aTalkId);
        }
        $sql
            = "
			UPDATE ?_talk_user
			SET 
				talk_user_active = ?d
			WHERE
				talk_id IN (?a)
				AND
				user_id = ?d
		";
        $bResult = $this->oDb->query($sql, $iActive, $aTalkId, $sUserId);
        return $bResult !== false;
    }

    /**
     * Возвращает количество новых комментариев
     *
     * @param $sUserId
     *
     * @return bool
     */
    public function GetCountCommentNew($sUserId) {

        $sql
            = "
			SELECT
				SUM(tu.comment_count_new) as count_new
			FROM
  				?_talk_user as tu
			WHERE
  				tu.user_id = ?d
  				AND
  				tu.talk_user_active=?d
		";
        if ($aRow = $this->oDb->selectRow($sql, $sUserId, ModuleTalk::TALK_USER_ACTIVE)) {
            return $aRow['count_new'];
        }
        return false;
    }

    /**
     * Получает число новых тем и комментов где есть юзер
     *
     * @param int $sUserId    ID пользователя
     *
     * @return int
     */
    public function GetCountTalkNew($sUserId) {

        $sql
            = "
			SELECT
			    COUNT(tu.talk_id) as count_new
			FROM
  				?_talk_user as tu
			WHERE
				tu.user_id = ?d
  				AND
  				tu.date_last IS NULL
  				AND
  				tu.talk_user_active=?d
		";
        if ($aRow = $this->oDb->selectRow($sql, $sUserId, ModuleTalk::TALK_USER_ACTIVE)) {
            return $aRow['count_new'];
        }
        return false;
    }

    /**
     * Получить все темы разговора где есть юзер
     *
     * @param  int $sUserId      ID пользователя
     * @param  int $iCount       Возвращает общее количество элементов
     * @param  int $iCurrPage    Номер страницы
     * @param  int $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetTalksByUserId($sUserId, &$iCount, $iCurrPage, $iPerPage) {

        $sql
            = "SELECT
					tu.talk_id
				FROM 
					?_talk_user as tu,
					?_talk as t
				WHERE 
					tu.user_id = ?d 
					AND
					tu.talk_id=t.talk_id
					AND
					tu.talk_user_active = ?d
				ORDER BY t.talk_date_last desc, t.talk_date desc
				LIMIT ?d, ?d
					";

        $aTalks = array();
        if ($aRows = $this->oDb->selectPage(
            $iCount, $sql, $sUserId, ModuleTalk::TALK_USER_ACTIVE, ($iCurrPage - 1) * $iPerPage, $iPerPage
        )
        ) {
            foreach ($aRows as $aRow) {
                $aTalks[] = $aRow['talk_id'];
            }
        }
        return $aTalks;
    }

    /**
     * Получает список юзеров в теме разговора
     *
     * @param  int   $iTalkId        ID разговора
     * @param  array $aUserActive    Список статусов
     *
     * @return array
     */
    public function GetUsersTalk($iTalkId, $aUserActive = array()) {

        $sql
            = "
			SELECT 
				user_id
			FROM 
				?_talk_user
			WHERE
				talk_id = ? 
				{ AND talk_user_active IN(?a) }
			";

        $aResult = $this->oDb->selectCol($sql, $iTalkId, (count($aUserActive) ? $aUserActive : DBSIMPLE_SKIP));

        return $aResult ? $aResult : array();
    }

    /**
     * Увеличивает число новых комментов у юзеров
     *
     * @param int   $iTalkId       ID разговора
     * @param array $aExcludeId    Список ID пользователей для исключения
     *
     * @return int
     */
    public function increaseCountCommentNew($iTalkId, $aExcludeId) {

        if (!is_null($aExcludeId) && !is_array($aExcludeId)) {
            $aExcludeId = array($aExcludeId);
        }

        $sql
            = "UPDATE
				?_talk_user
				SET comment_count_new=comment_count_new+1 
			WHERE
				talk_id = ? 
				{ AND user_id NOT IN (?a) }";
        $bResult = $this->oDb->select($sql, $iTalkId, !is_null($aExcludeId) ? $aExcludeId : DBSIMPLE_SKIP);
        return $bResult !== false;
    }

    /**
     * Возвращает массив пользователей, участвующих в разговоре
     *
     * @param  int $iTalkId    ID разговора
     *
     * @return array
     */
    public function GetTalkUsers($iTalkId) {
        $sql
            = "
			SELECT 
				t.* 
			FROM 
				?_talk_user as t
			WHERE
				talk_id = ? 
			";
        $aResult = array();
        if ($aRows = $this->oDb->select($sql, $iTalkId)) {
            $aResult = E::GetEntityRows('Talk_TalkUser', $aRows);;
        }

        return $aResult;
    }

    /**
     * Получить все темы разговора по фильтру
     *
     * @param  array $aFilter      Фильтр
     * @param  int   $iCount       Возвращает общее количество элементов
     * @param  int   $iCurrPage    Номер страницы
     * @param  int   $iPerPage     Количество элементов на страницу
     *
     * @return array('collection'=>array,'count'=>int)
     */
    public function GetTalksByFilter($aFilter, &$iCount, $iCurrPage, $iPerPage) {

        if (isset($aFilter['id']) && !is_array($aFilter['id'])) {
            $aFilter['id'] = array($aFilter['id']);
        }
        $sql
            = "SELECT
					t.talk_id
				FROM 
					?_talk_user AS tui
                  JOIN ?_talk AS t ON t.talk_id=tui.talk_id
                  JOIN ?_talk_user AS tu ON tu.talk_id=t.talk_id
                  JOIN ?_user AS u ON u.user_id=tu.user_id
				WHERE 
					tui.talk_user_active = ?d
					{ AND tui.user_id = ?d }
					{ AND tui.talk_id IN (?a) }
					{ AND ( tui.comment_count_new > ?d OR tui.date_last IS NULL ) }
					{ AND t.talk_date <= ? }
					{ AND t.talk_date >= ? }
					{ AND t.talk_title LIKE ? }
					{ AND t.talk_text LIKE ? }
					{ AND u.user_login = ? }
					{ AND u.user_login IN (?a) }
					{ AND t.user_id = ?d }
				ORDER BY t.talk_date_last desc, t.talk_date desc
				LIMIT ?d, ?d
					";

        $aTalks = array();
        $aRows = $this->oDb->selectPage(
            $iCount,
            $sql,
            ModuleTalk::TALK_USER_ACTIVE,
            (!empty($aFilter['user_id']) ? $aFilter['user_id'] : DBSIMPLE_SKIP),
            ((isset($aFilter['id']) && count($aFilter['id'])) ? $aFilter['id'] : DBSIMPLE_SKIP),
            (!empty($aFilter['only_new']) ? 0 : DBSIMPLE_SKIP),
            (!empty($aFilter['date_max']) ? $aFilter['date_max'] : DBSIMPLE_SKIP),
            (!empty($aFilter['date_min']) ? $aFilter['date_min'] : DBSIMPLE_SKIP),
            (!empty($aFilter['keyword']) ? $aFilter['keyword'] : DBSIMPLE_SKIP),
            (!empty($aFilter['text_like']) ? $aFilter['text_like'] : DBSIMPLE_SKIP),
            ((!empty($aFilter['user_login']) && !is_array($aFilter['user_login']))? $aFilter['user_login'] : DBSIMPLE_SKIP),
            ((!empty($aFilter['user_login']) && is_array($aFilter['user_login']))? $aFilter['user_login'] : DBSIMPLE_SKIP),
            (!empty($aFilter['sender_id']) ? $aFilter['sender_id'] : DBSIMPLE_SKIP),
            ($iCurrPage - 1) * $iPerPage,
            $iPerPage
        );
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aTalks[] = $aRow['talk_id'];
            }
        }
        return $aTalks;
    }

    /**
     * Получает информацию о пользователях, занесенных в блеклист
     *
     * @param  int $sUserId    ID пользователя
     *
     * @return array
     */
    public function GetBlacklistByUserId($sUserId) {

        $sql
            = "SELECT
					tb.user_target_id
				FROM 
					?_talk_blacklist as tb
				WHERE 
					tb.user_id = ?d";
        $aTargetId = array();
        if ($aRows = $this->oDb->select($sql, $sUserId)) {
            foreach ($aRows as $aRow) {
                $aTargetId[] = $aRow['user_target_id'];
            }
        }
        return $aTargetId;
    }

    /**
     * Возвращает пользователей, у которых данный занесен в Blacklist
     *
     * @param  int $sUserId ID пользователя
     *
     * @return array
     */
    public function GetBlacklistByTargetId($sUserId) {

        $sql
            = "SELECT
					tb.user_id
				FROM 
					?_talk_blacklist as tb
				WHERE 
					tb.user_target_id = ?d";
        $aUserId = array();
        if ($aRows = $this->oDb->select($sql, $sUserId)) {
            foreach ($aRows as $aRow) {
                $aUserId[] = $aRow['user_id'];
            }
        }
        return $aUserId;
    }

    /**
     * Добавление пользователя в блеклист по переданному идентификатору
     *
     * @param  int $sTargetId    ID пользователя, которого добавляем в блэклист
     * @param  int $sUserId      ID пользователя
     *
     * @return bool
     */
    public function AddUserToBlacklist($sTargetId, $sUserId) {

        $sql
            = "
			INSERT INTO ?_talk_blacklist
				( user_id, user_target_id )
			VALUES
				(?d, ?d)
		";
        $xResult = $this->oDb->query($sql, $sUserId, $sTargetId);
        return $xResult !== false;
    }

    /**
     * Удаляем пользователя из блеклиста
     *
     * @param  int $sTargetId    ID пользователя, которого удаляем из блэклиста
     * @param  int $sUserId      ID пользователя
     *
     * @return bool
     */
    public function DeleteUserFromBlacklist($sTargetId, $sUserId) {

        $sql
            = "
			DELETE FROM ?_talk_blacklist
			WHERE
				user_id = ?d
			AND
				user_target_id = ?d
		";
        if ($this->oDb->query($sql, $sUserId, $sTargetId)) {
            return true;
        }
        return false;
    }

    /**
     * Добавление пользователя в блеклист по списку идентификаторов
     *
     * @param  array $aTargetId    Список ID пользователей, которых добавляем в блэклист
     * @param  int   $sUserId      ID пользователя
     *
     * @return bool
     */
    public function AddUserArrayToBlacklist($aTargetId, $sUserId) {

        $sql
            = "
			INSERT INTO ?_talk_blacklist
				( user_id, user_target_id )
			VALUES
				(?d, ?d)
		";
        $bOk = true;
        foreach ($aTargetId as $sTarget) {
            $bOk = $bOk && $this->oDb->query($sql, $sUserId, $sTarget);
        }
        return $bOk;
    }
}

// EOF