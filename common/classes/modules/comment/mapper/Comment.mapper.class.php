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
 * @package modules.comment
 * @since   1.0
 */
class ModuleComment_MapperComment extends Mapper {

    /**
     * @deprecated  since 1.1
     *
     * @param       $sDate
     * @param       $sTargetType
     * @param       $iLimit
     * @param array $aExcludeTarget
     * @param array $aExcludeParentTarget
     *
     * @return array
     */
    public function GetCommentsRatingByDate($sDate, $sTargetType, $iLimit, $aExcludeTarget = array(), $aExcludeParentTarget = array()) {

        return $this->GetCommentsIdByRatingAndDate($sDate, $sTargetType, $iLimit, $aExcludeTarget, $aExcludeParentTarget);
    }

    /**
     * Получить ID комментатриев по рейтингу и дате
     *
     * @param  string   $sDate                   Дата за которую выводить рейтинг
     * @param  string   $sTargetType             Тип владельца комментария
     * @param  int      $iLimit                  Количество элементов
     * @param  int[]    $aExcludeTarget          Список ID владельцев, которые необходимо исключить из выдачи
     * @param  int[]    $aExcludeParentTarget    Список ID родителей владельцев, которые необходимо исключить из выдачи
     *
     * @return int[]
     */
    public function GetCommentsIdByRatingAndDate($sDate, $sTargetType, $iLimit, $aExcludeTarget = array(), $aExcludeParentTarget = array()) {

        $sql = "SELECT
					comment_id
				FROM 
					?_comment
				WHERE 
					target_type = ?
					AND 
					comment_date >= ?
					AND 
					comment_rating >= 0
					AND
					comment_delete = 0
					AND 
					comment_publish = 1 
					{ AND target_id NOT IN(?a) }  
					{ AND target_parent_id NOT IN (?a) }
				ORDER by comment_rating desc, comment_id desc
				LIMIT 0, ?d ";

        $aCommentsId = $this->oDb->selectCol($sql,
            $sTargetType,
            $sDate,
            (is_array($aExcludeTarget) && count($aExcludeTarget)) ? $aExcludeTarget : DBSIMPLE_SKIP,
            (count($aExcludeParentTarget) ? $aExcludeParentTarget : DBSIMPLE_SKIP),
            $iLimit
        );
        return $aCommentsId ? $aCommentsId : array();
    }

    /**
     * Получает уникальный коммент, это помогает спастись от дублей комментов
     *
     * @param int    $iTargetId      ID владельца комментария
     * @param string $sTargetType    Тип владельца комментария
     * @param int    $iUserId        ID пользователя
     * @param int    $iCommentPId    ID родительского комментария
     * @param string $sHash          Хеш строка текста комментария
     *
     * @return int|null
     */
    public function GetCommentUnique($iTargetId, $sTargetType, $iUserId, $iCommentPId, $sHash) {

        $sql = "
            SELECT comment_id
            FROM ?_comment
			WHERE 
				target_id = ?d 
				AND
				target_type = ? 
				AND
				user_id = ?d
				AND
				((comment_pid = ?) OR (? is NULL and comment_pid is NULL))
				AND
				comment_text_hash =?
			LIMIT 1
				";
        $iCommentId = $this->oDb->selectCell($sql, $iTargetId, $sTargetType, $iUserId, $iCommentPId, $iCommentPId, $sHash);
        return $iCommentId ? $iCommentId : null;
    }

    /**
     * @deprecated  since 1.1
     *
     * @param       $sTargetType
     * @param       $iCount
     * @param       $iCurrPage
     * @param       $iPerPage
     * @param array $aExcludeTarget
     * @param array $aExcludeParentTarget
     *
     * @return array
     */
    public function GetCommentsAll($sTargetType, &$iCount, $iCurrPage, $iPerPage, $aExcludeTarget = array(), $aExcludeParentTarget = array()) {

        return $this->GetCommentsIdByTargetType($sTargetType, $iCount, $iCurrPage, $iPerPage, $aExcludeTarget, $aExcludeParentTarget);
    }

    /**
     * Получить ID комментариев по типу
     *
     * @param string $sTargetType             Тип владельца комментария
     * @param int    $iCount                  Возвращает общее количество элементов
     * @param int    $iCurrPage               Номер страницы
     * @param int    $iPerPage                Количество элементов на страницу
     * @param array  $aExcludeTarget          Список ID владельцев, которые необходимо исключить из выдачи
     * @param array  $aExcludeParentTarget    Список ID родителей владельцев, которые необходимо исключить из выдачи, например, исключить комментарии топиков к определенным блогам(закрытым)
     *
     * @return int[]
     */
    public function GetCommentsIdByTargetType($sTargetType, &$iCount, $iCurrPage, $iPerPage, $aExcludeTarget = array(), $aExcludeParentTarget = array()) {

        $aFilter = array(
            'target_type' => $sTargetType,
            'delete' => 0,
            'publish' => 1,
            'target_id' => $aExcludeTarget,
            'target_parent_id' => $aExcludeParentTarget,
        );
        return $this->GetCommentsIdByFilter($aFilter, $iCount, $iCurrPage, $iPerPage);
    }

    /**
     * Список комментов по ID
     *
     * @param array $aCommentsId    Список ID комментариев
     *
     * @return ModuleComment_EntityComment[]
     */
    public function GetCommentsByArrayId($aCommentsId) {

        if (!$aCommentsId) {
            return array();
        }
        if (!is_array($aCommentsId)) {
            $aCommentsId = array(intval($aCommentsId));
        }

        $iLimit = sizeof($aCommentsId);
        $sql = "
            SELECT
                c.comment_id AS ARRAY_KEYS,
                c.*
            FROM
                ?_comment AS c
            WHERE
                c.comment_id IN(?a)
            LIMIT $iLimit
            ";
        $aComments = array();
        if ($aRows = $this->oDb->select($sql, $aCommentsId)) {
            $aComments = E::GetEntityRows('Comment', $aRows, $aCommentsId);
        }
        return $aComments;
    }

    /**
     * @deprecated  since 1.1
     *
     * @param $sTargetType
     * @param $aExcludeTargets
     * @param $iLimit
     *
     * @return array
     */
    public function GetCommentsOnline($sTargetType, $aExcludeTargets, $iLimit) {

        return $this->GetCommentsIdOnline($sTargetType, $aExcludeTargets, $iLimit);
    }

    /**
     * Получить ID комментариев, сгрупированных по типу (для вывода прямого эфира)
     *
     * @param string $sTargetType        Тип владельца комментария
     * @param array  $aExcludeTargets    Список ID владельцев для исключения
     * @param int    $iLimit             Количество элементов
     *
     * @return int[]
     */
    public function GetCommentsIdOnline($sTargetType, $aExcludeTargets, $iLimit) {

        $sql = "SELECT
					comment_id
				FROM 
					?_comment_online
				WHERE
					target_type = ?
				{ AND target_parent_id NOT IN(?a) }
				ORDER by comment_online_id DESC
				LIMIT 0, ?d ;";

        $aCommentsId = $this->oDb->selectCol($sql,
            $sTargetType,
            (count($aExcludeTargets) ? $aExcludeTargets : DBSIMPLE_SKIP),
            $iLimit);

        return $aCommentsId ? $aCommentsId : array();
    }

    /**
     * Получить ID комментов по владельцу
     *
     * @param   array   $aTargetsId     ID владельца коммента
     * @param   string  $sTargetType    Тип владельца комментария
     *
     * @return  int[]
     */
    public function GetCommentsIdByTargetsId($aTargetsId, $sTargetType) {

        $aTargetsId = $this->_arrayId($aTargetsId);
        $sql = "
            SELECT comment_id
            FROM ?_comment
            WHERE target_id IN (?a) AND target_type = ?
        ";

        $aCommentsId = $this->oDb->selectCol($sql, $aTargetsId, $sTargetType);

        return $aCommentsId ? $aCommentsId : array();
    }

    /**
     * Получить комменты по владельцу
     *
     * @param  int    $iTargetId      ID владельца коммента
     * @param  string $sTargetType    Тип владельца комментария
     *
     * @return array
     */
    public function GetCommentsByTargetId($iTargetId, $sTargetType) {

        $sql = "SELECT
					comment_id,
					comment_id as ARRAY_KEY,
					comment_pid as PARENT_KEY
				FROM 
					?_comment
				WHERE 
					target_id = ?d 
					AND
					target_type = ?
				ORDER BY comment_id ASC;
					";
        if ($aRows = $this->oDb->select($sql, $iTargetId, $sTargetType)) {
            return $aRows;
        }
        return null;
    }

    /**
     * Получает комменты используя nested set
     *
     * @param int    $sId            ID владельца коммента
     * @param string $sTargetType    Тип владельца комментария
     *
     * @return int[]
     */
    public function GetCommentsTreeByTargetId($sId, $sTargetType) {

        $sql = "SELECT
					comment_id 
				FROM 
					?_comment
				WHERE 
					target_id = ?d 
					AND
					target_type = ?
				ORDER BY comment_left ASC;
					";

        $aCommentsId = $this->oDb->selectCol($sql, $sId, $sTargetType);

        return $aCommentsId;
    }

    /**
     * Получает комменты используя nested set
     *
     * @param int    $sId            ID владельца коммента
     * @param string $sTargetType    Тип владельца комментария
     * @param int    $iCount         Возвращает общее количество элементов
     * @param int    $iPage          Номер страницы
     * @param int    $iPerPage       Количество элементов на страницу
     *
     * @return int[]
     */
    public function GetCommentsTreePageByTargetId($sId, $sTargetType, &$iCount, $iPage, $iPerPage) {

        // * Сначала получаем корни и определяем границы выборки веток
        $sql = "SELECT
					comment_left,
					comment_right 
				FROM 
					?_comment
				WHERE 
					target_id = ?d 
					AND
					target_type = ? 
					AND
					comment_pid IS NULL
				ORDER BY comment_left DESC
				LIMIT ?d , ?d ;";

        if ($aRows = $this->oDb->selectPage($iCount, $sql, $sId, $sTargetType, ($iPage - 1) * $iPerPage, $iPerPage)) {
            $aCmt = array_pop($aRows);
            $iLeft = $aCmt['comment_left'];
            if ($aRows) {
                $aCmt = array_shift($aRows);
            }
            $iRight = $aCmt['comment_right'];
        } else {
            return array();
        }

        // * Теперь получаем полный список комментов
        $sql = "SELECT
					comment_id 
				FROM 
					?_comment
				WHERE 
					target_id = ?d 
					AND
					target_type = ? 
					AND
					comment_left >= ?d
					AND
					comment_right <= ?d
				ORDER BY comment_left ASC;
					";

        $aCommentsId = $this->oDb->selectCol($sql, $sId, $sTargetType, $iLeft, $iRight);

        return $aCommentsId ? $aCommentsId : array();
    }

    /**
     * Возвращает количество дочерних комментариев у корневого коммента
     *
     * @param int    $iTargetId   - ID владельца коммента
     * @param string $sTargetType - Тип владельца комментария
     *
     * @return int
     */
    public function GetCountCommentsRootByTargetId($iTargetId, $sTargetType) {

        $sql = "SELECT
					COUNT(comment_id) AS cnt
				FROM 
					?_comment
				WHERE 
					target_id = ?d 
					AND
					target_type = ?
					AND
					comment_pid IS NULL;";

        $iCount = $this->oDb->selectCell($sql, $iTargetId, $sTargetType);

        return $iCount ? $iCount : 0;
    }

    /**
     * Возвращает количество комментариев
     *
     * @param int    $iTargetId   - ID владельца коммента
     * @param string $sTargetType - Тип владельца комментария
     * @param int    $iLeft       - Значение left для дерева nested set
     *
     * @return int
     */
    public function GetCountCommentsAfterByTargetId($iTargetId, $sTargetType, $iLeft) {

        $sql = "SELECT
					COUNT(comment_id) AS cnt
				FROM 
					?_comment
				WHERE 
					target_id = ?d 
					AND
					target_type = ?
					AND
					comment_pid IS NULL	
					AND 
					comment_left >= ?d ;";

        $iCount = $this->oDb->selectCell($sql, $iTargetId, $sTargetType, $iLeft);

        return $iCount ? $iCount : 0;
    }

    /**
     * Возвращает корневой комментарий
     *
     * @param int    $iTargetId   ID владельца коммента
     * @param string $sTargetType Тип владельца комментария
     * @param int    $iLeft       Значение left для дерева nested set
     *
     * @return ModuleComment_EntityComment|null
     */
    public function GetCommentRootByTargetIdAndChildren($iTargetId, $sTargetType, $iLeft) {

        $sql = "SELECT
					*
				FROM 
					?_comment
				WHERE 
					target_id = ?d 
					AND
					target_type = ?
					AND
					comment_pid IS NULL	
					AND 
					comment_left < ?d 
					AND 
					comment_right > ?d 
				LIMIT 0,1 ;";

        if ($aRow = $this->oDb->selectRow($sql, $iTargetId, $sTargetType, $iLeft, $iLeft)) {
            return E::GetEntity('Comment', $aRow);
        }
        return null;
    }

    /**
     * @deprecated  since 1.1
     *
     * @param $iTargetId
     * @param $sTargetType
     * @param $sIdCommentLast
     *
     * @return array
     */
    public function GetCommentsNewByTargetId($iTargetId, $sTargetType, $sIdCommentLast) {

        return $this->GetCommentsIdNewByTargetId($iTargetId, $sTargetType, $sIdCommentLast);
    }

    /**
     * Получить новые комменты для владельца
     *
     * @param int    $iTargetId      ID владельца коммента
     * @param string $sTargetType    Тип владельца комментария
     * @param int    $sIdCommentLast ID последнего прочитанного комментария
     *
     * @return int[]
     */
    public function GetCommentsIdNewByTargetId($iTargetId, $sTargetType, $sIdCommentLast) {

        $sql = "SELECT
					comment_id
				FROM 
					?_comment
				WHERE 
					target_id = ?d 
					AND
					target_type = ?
					AND
					comment_id > ?d
				ORDER BY comment_id ASC;
					";

        $aCommentsId = $this->oDb->selectCol($sql, $iTargetId, $sTargetType, $sIdCommentLast);

        return $aCommentsId ? $aCommentsId : array();
    }

    /**
     * @deprecated  since 1.1
     *
     * @param       $iTargetId
     * @param       $sTargetType
     * @param       $iCount
     * @param       $iCurrPage
     * @param       $iPerPage
     * @param array $aExcludeTarget
     * @param array $aExcludeParentTarget
     *
     * @return array
     */
    public function GetCommentsByUserId($iTargetId, $sTargetType, &$iCount, $iCurrPage, $iPerPage, $aExcludeTarget = array(), $aExcludeParentTarget = array()) {

        return $this->GetCommentsIdByUserId($iTargetId, $sTargetType, $iCount, $iCurrPage, $iPerPage, $aExcludeTarget, $aExcludeParentTarget);
    }

    /**
     * Получить комменты по юзеру
     *
     * @param  int    $iUserId                 ID пользователя
     * @param  string $sTargetType             Тип владельца комментария
     * @param  int    $iCount                  Возращает общее количество элементов
     * @param  int    $iCurrPage               Номер страницы
     * @param  int    $iPerPage                Количество элементов на страницу
     * @param array   $aExcludeTarget          Список ID владельцев, которые необходимо исключить из выдачи
     * @param array   $aExcludeParentTarget    Список ID родителей владельцев, которые необходимо исключить из выдачи
     *
     * @return int[]
     */
    public function GetCommentsIdByUserId($iUserId, $sTargetType, &$iCount, $iCurrPage, $iPerPage, $aExcludeTarget = array(), $aExcludeParentTarget = array()) {

        $sql = "SELECT
					comment_id
				FROM 
					?_comment
				WHERE 
					user_id = ?d 
					AND
					target_type= ? 
					AND
					comment_delete = 0
					AND
					comment_publish = 1 
					{ AND target_id NOT IN (?a) }
					{ AND target_parent_id NOT IN (?a) }
				ORDER BY comment_id DESC
				LIMIT ?d, ?d ";

        $aCommentsId = array();
        $aRows = $this->oDb->selectPage($iCount, $sql,
            $iUserId,
            $sTargetType,
            (!empty($aExcludeTarget) ? $aExcludeTarget : DBSIMPLE_SKIP),
            (!empty($aExcludeParentTarget) ? $aExcludeParentTarget : DBSIMPLE_SKIP),
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aCommentsId[] = $aRow['comment_id'];
            }
        }
        return $aCommentsId;
    }

    /**
     * Получает количество комментариев одного пользователя
     *
     * @param int    $iUserId              ID пользователя
     * @param string $sTargetType          Тип владельца комментария
     * @param array  $aExcludeTarget       Список ID владельцев, которые необходимо исключить из выдачи
     * @param array  $aExcludeParentTarget Список ID родителей владельцев, которые необходимо исключить из выдачи
     *
     * @return int
     */
    public function GetCountCommentsByUserId($iUserId, $sTargetType, $aExcludeTarget = array(), $aExcludeParentTarget = array()) {

        $sql = "SELECT
					COUNT(comment_id) AS cnt
				FROM 
					?_comment
				WHERE 
					user_id = ?d 
					AND
					target_type= ? 
					AND
					comment_delete = 0
					AND
					comment_publish = 1
					{ AND target_id NOT IN (?a) }
					{ AND target_parent_id NOT IN (?a) }
					";
        $iCount = $this->oDb->selectCell($sql,
            $iUserId,
            $sTargetType,
            (!empty($aExcludeTarget) ? $aExcludeTarget : DBSIMPLE_SKIP),
            (!empty($aExcludeParentTarget) ? $aExcludeParentTarget : DBSIMPLE_SKIP)
        );
        return $iCount ? $iCount : 0;
    }

    /**
     * Добавляет коммент
     *
     * @param  ModuleComment_EntityComment $oComment    Объект комментария
     *
     * @return bool|int
     */
    public function AddComment(ModuleComment_EntityComment $oComment) {

        $sql = "INSERT INTO ?_comment
          (
              comment_pid,
              target_id,
              target_type,
              target_parent_id,
              user_id,
              comment_text,
              comment_date,
              comment_user_ip,
              comment_publish,
              comment_text_hash
          )
          VALUES (
              ?, ?d, ?, ?d, ?d, ?, ?, ?, ?d, ?
          )
        ";
        $iId = $this->oDb->query($sql,
            $oComment->getPid(),
            $oComment->getTargetId(),
            $oComment->getTargetType(),
            $oComment->getTargetParentId(),
            $oComment->getUserId(),
            $oComment->getText(),
            $oComment->getDate(),
            $oComment->getUserIp(),
            $oComment->getPublish(),
            $oComment->getTextHash()
        );
        return $iId ? $iId : false;
    }

    /**
     * Добавляет коммент в дерево nested set
     *
     * @param  ModuleComment_EntityComment $oComment    Объект комментария
     *
     * @return bool|int
     */
    public function AddCommentTree(ModuleComment_EntityComment $oComment) {

        $this->oDb->transaction();

        if ($oComment->getPid() && $oCommentParent = $this->GetCommentsByArrayId(array($oComment->getPid()))) {
            $oCommentParent = $oCommentParent[$oComment->getPid()];
            $iLeft = $oCommentParent->getRight();
            $iLevel = $oCommentParent->getLevel() + 1;

            $sql = "UPDATE ?_comment SET comment_left=comment_left+2 WHERE target_id=?d AND target_type=? AND comment_left>? ;";
            $this->oDb->query($sql, $oComment->getTargetId(), $oComment->getTargetType(), $iLeft - 1);

            $sql = "UPDATE ?_comment SET comment_right=comment_right+2 WHERE target_id=?d AND target_type=? AND comment_right>? ;";
            $this->oDb->query($sql, $oComment->getTargetId(), $oComment->getTargetType(), $iLeft - 1);
        } else {
            if ($oCommentLast = $this->GetCommentLast($oComment->getTargetId(), $oComment->getTargetType())) {
                $iLeft = $oCommentLast->getRight() + 1;
            } else {
                $iLeft = 1;
            }
            $iLevel = 0;
        }

        if ($iId = $this->AddComment($oComment)) {
            $sql = "UPDATE ?_comment SET comment_left = ?d, comment_right = ?d, comment_level = ?d WHERE comment_id = ? ;";
            $this->oDb->query($sql, $iLeft, $iLeft + 1, $iLevel, $iId);
            $this->oDb->commit();
            return $iId;
        }

        if (strtolower(Config::Get('db.tables.engine')) == 'innodb') {
            $this->oDb->rollback();
        }

        return false;
    }

    /**
     * Возвращает последний комментарий
     *
     * @param int    $iTargetId      ID владельца коммента
     * @param string $sTargetType    Тип владельца комментария
     *
     * @return ModuleComment_EntityComment|null
     */
    public function GetCommentLast($iTargetId, $sTargetType) {

        $sql = "
            SELECT *
            FROM ?_comment
			WHERE 
				target_id = ?d 
				AND
				target_type = ? 
			ORDER BY comment_right DESC
			LIMIT 0,1
				";
        if ($aRow = $this->oDb->selectRow($sql, $iTargetId, $sTargetType)) {
            return E::GetEntity('Comment', $aRow);
        }
        return null;
    }

    /**
     * Добавляет новый коммент в прямой эфир
     *
     * @param ModuleComment_EntityCommentOnline $oCommentOnline    Объект онлайн комментария
     *
     * @return bool|int
     */
    public function AddCommentOnline(ModuleComment_EntityCommentOnline $oCommentOnline) {

        $this->DeleteCommentOnlineByTargetId($oCommentOnline->getTargetId(), $oCommentOnline->getTargetType());
        $sql = "
                INSERT INTO ?_comment_online
                (
                  target_id, target_type, target_parent_id, comment_id
                )
                VALUES (
                  ?d, ?, ?d, ?d
                )
            ";
        $xResult = $this->oDb->query($sql,
            $oCommentOnline->getTargetId(),
            $oCommentOnline->getTargetType(),
            $oCommentOnline->getTargetParentId(),
            $oCommentOnline->getCommentId()
        );
        return $xResult !== false;
    }

    /**
     * Удаляет коммент из прямого эфира
     *
     * @param  int    $iTargetId      ID владельца коммента
     * @param  string $sTargetType    Тип владельца комментария
     *
     * @return bool
     */
    public function DeleteCommentOnlineByTargetId($iTargetId, $sTargetType) {

        $sql = "DELETE FROM ?_comment_online WHERE target_id = ?d AND target_type = ? ";
        if ($this->oDb->query($sql, $iTargetId, $sTargetType)) {
            return true;
        }
        return false;
    }

    /**
     * Обновляет коммент
     *
     * @param  ModuleComment_EntityComment $oComment    Объект комментария
     *
     * @return bool
     */
    public function UpdateComment(ModuleComment_EntityComment $oComment) {

        $sql = "UPDATE ?_comment
			SET 
				comment_text= ?,
				comment_rating= ?f,
				comment_count_vote= ?d,
				comment_count_favourite= ?d,
				comment_delete = ?d ,
				comment_publish = ?d ,
				comment_date_edit = CASE comment_text_hash WHEN ? THEN comment_date_edit ELSE ? END,
				comment_text_hash = ?
			WHERE
				comment_id = ?d
		";
        $bResult = $this->oDb->query(
            $sql,
            $oComment->getText(),
            $oComment->getRating(),
            $oComment->getCountVote(),
            $oComment->getCountFavourite(),
            $oComment->getDelete(),
            $oComment->getPublish(),
            $oComment->getTextHash(), // проверка на изменение
            F::Now(),
            $oComment->getTextHash(), // новый хеш
            $oComment->getId()
        );
        return $bResult !== false;
    }

    /**
     * Устанавливает publish у коммента
     *
     * @param  int    $iTargetId      ID владельца коммента
     * @param  string $sTargetType    Тип владельца комментария
     * @param  int    $iPublish       Статус отображать комментарии или нет
     *
     * @return bool
     */
    public function SetCommentsPublish($iTargetId, $sTargetType, $iPublish) {

        $sql = "UPDATE ?_comment
			SET 
				comment_publish = ?
			WHERE
				target_id = ?d AND target_type = ? 
		";
        $bResult = $this->oDb->query($sql, $iPublish, $iTargetId, $sTargetType);
        return $bResult !== false;
    }

    /**
     * Удаляет комментарии из базы данных
     *
     * @param   array|int   $aTargetsId     - Список ID владельцев
     * @param   string      $sTargetType    - Тип владельцев
     *
     * @return  bool
     */
    public function DeleteCommentByTargetId($aTargetsId, $sTargetType) {

        $aTargetsId = $this->_arrayId($aTargetsId);
        $sql = "
			DELETE FROM ?_comment
			WHERE
				target_id IN (?a)
				AND
				target_type = ?
		";
        return ($this->oDb->query($sql, $aTargetsId, $sTargetType) !== false);
    }

    /**
     * Удаляет коммент из прямого эфира по массиву переданных идентификаторов
     *
     * @param  array|int    $aCommentsId
     * @param  string       $sTargetType    - Тип владельцев
     *
     * @return bool
     */
    public function DeleteCommentOnlineByArrayId($aCommentsId, $sTargetType) {

        $aCommentsId = $this->_arrayId($aCommentsId);
        $sql = "
			DELETE FROM ?_comment_online
			WHERE 
				comment_id IN (?a) 
				AND 
				target_type = ? 
		";
        return ($this->oDb->query($sql, $aCommentsId, $sTargetType) !== false);
    }

    /**
     * Меняем target parent по массиву идентификаторов
     *
     * @param  int       $iParentId      Новый ID родителя владельца
     * @param  string    $sTargetType    Тип владельца
     * @param  array|int $aTargetId      Список ID владельцев
     *
     * @return bool
     */
    public function UpdateTargetParentByTargetId($iParentId, $sTargetType, $aTargetId) {

        $sql = "
			UPDATE ?_comment
			SET 
				target_parent_id = ?d
			WHERE 
				target_id IN (?a)
				AND 
				target_type = ? 
		";
        $bResult = $this->oDb->query($sql, $iParentId, $aTargetId, $sTargetType);
        return $bResult !== false;
    }

    /**
     * Меняем target parent по массиву идентификаторов в таблице комментариев online
     *
     * @param  int       $iParentId      Новый ID родителя владельца
     * @param  string    $sTargetType    Тип владельца
     * @param  array|int $aTargetId      Список ID владельцев
     *
     * @return bool
     */
    public function UpdateTargetParentByTargetIdOnline($iParentId, $sTargetType, $aTargetId) {

        $sql = "
			UPDATE ?_comment_online
			SET 
				target_parent_id = ?d
			WHERE 
				target_id IN (?a)
				AND 
				target_type = ? 
		";
        $bResult = $this->oDb->query($sql, $iParentId, $aTargetId, $sTargetType);
        return $bResult !== false;
    }

    /**
     * Меняет target parent на новый
     *
     * @param int    $iParentId       Прежний ID родителя владельца
     * @param string $sTargetType     Тип владельца
     * @param int    $iParentIdNew    Новый ID родителя владельца
     *
     * @return bool
     */
    public function MoveTargetParent($iParentId, $sTargetType, $iParentIdNew) {

        $sql = "
			UPDATE ?_comment
			SET 
				target_parent_id = ?d
			WHERE 
				target_parent_id = ?d
				AND 
				target_type = ? 
		";
        $bResult = $this->oDb->query($sql, $iParentIdNew, $iParentId, $sTargetType);
        return $bResult !== false;
    }

    /**
     * Меняет target parent на новый в прямом эфире
     *
     * @param int    $iParentId       Прежний ID родителя владельца
     * @param string $sTargetType     Тип владельца
     * @param int    $iParentIdNew    Новый ID родителя владельца
     *
     * @return bool
     */
    public function MoveTargetParentOnline($iParentId, $sTargetType, $iParentIdNew) {

        $sql = "
			UPDATE ?_comment_online
			SET 
				target_parent_id = ?d
			WHERE 
				target_parent_id = ?d
				AND 
				target_type = ? 
		";
        $bResult = $this->oDb->query($sql, $iParentIdNew, $iParentId, $sTargetType);
        return $bResult !== false;
    }

    /**
     * Перестраивает дерево комментариев
     * Восстанавливает значения left, right и level
     *
     * @param int    $iPid           ID родителя
     * @param int    $iLft           Значение left для дерева nested set
     * @param int    $iLevel         Уровень
     * @param int    $aTargetId      Список ID владельцев
     * @param string $sTargetType    Тип владельца
     *
     * @return int
     */
    public function RestoreTree($iPid, $iLft, $iLevel, $aTargetId, $sTargetType) {

        $iRgt = $iLft + 1;
        $iLevel++;
        $sql = "
              SELECT comment_id
              FROM ?_comment
              WHERE target_id = ? AND target_type = ? { AND comment_pid = ?  } { AND comment_pid IS NULL AND 1=?d}
              ORDER BY  comment_id ASC";

        $aRows = $this->oDb->select($sql,
            $aTargetId,
            $sTargetType,
            !is_null($iPid) ? $iPid : DBSIMPLE_SKIP,
            is_null($iPid) ? 1 : DBSIMPLE_SKIP
        );
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $iRgt = $this->RestoreTree($aRow['comment_id'], $iRgt, $iLevel, $aTargetId, $sTargetType);
            }
        }
        $iLevel--;
        if (!is_null($iPid)) {
            $sql = "UPDATE ?_comment
				SET comment_left=?d, comment_right=?d , comment_level =?d
				WHERE comment_id = ? ";
            $this->oDb->query($sql, $iLft, $iRgt, $iLevel, $iPid);
        }

        return $iRgt + 1;
    }

    /**
     * Возвращает список всех используемых типов владельца
     *
     * @return string[]
     */
    public function GetCommentTypes() {

        $sql = "
            SELECT target_type
            FROM ?_comment
			GROUP BY target_type ";
        $aTypes = array();
        if ($aRows = $this->oDb->select($sql)) {
            foreach ($aRows as $aRow) {
                $aTypes[] = $aRow['target_type'];
            }
        }
        return $aTypes;
    }

    /**
     * Возвращает список ID владельцев
     *
     * @param string $sTargetType    Тип владельца
     * @param int    $iPage          Номер страницы
     * @param int    $iPerPage       Количество элементов на одну старницу
     *
     * @return int[]
     */
    public function GetTargetIdByType($sTargetType, $iPage, $iPerPage) {

        $sql = "
            SELECT target_id
            FROM ?_comment
			WHERE  target_type = ?
			GROUP BY target_id
			ORDER BY target_id LIMIT ?d, ?d ";

        if ($aRows = $this->oDb->select($sql, $sTargetType, ($iPage - 1) * $iPerPage, $iPerPage)) {
            return $aRows;
        }
        return array();
    }

    /**
     * Пересчитывает счетчик избранных комментариев
     *
     * @return bool
     */
    public function RecalculateFavourite() {

        $sql = "
            UPDATE ?_comment c
            SET c.comment_count_favourite = (
                SELECT count(f.user_id)
                FROM ?_favourite f
                WHERE 
                    f.target_id = c.comment_id
                AND
					f.target_publish = 1
				AND
					f.target_type = 'comment'
            )
		";
        $bResult = $this->oDb->query($sql);

        return $bResult !== false;
    }

    /**
     * @deprecated  since 1.1
     *
     * @param $aFilter
     * @param $aOrder
     * @param $iCount
     * @param $iCurrPage
     * @param $iPerPage
     *
     * @return int[]
     */
    public function GetCommentsByFilter($aFilter, $aOrder, &$iCount, $iCurrPage, $iPerPage) {

        $aFilter['order'] = $aOrder;
        return $this->GetCommentsIdByFilter($aFilter, $iCount, $iCurrPage, $iPerPage);
    }

    /**
     * Получает список комментариев по фильтру
     *
     * @param array $aFilter         Фильтр выборки
     * @param int   $iCount          Возвращает общее количество элментов
     * @param int   $iCurrPage       Номер текущей страницы
     * @param int   $iPerPage        Количество элементов на одну страницу
     *
     * @return int[]
     */
    public function GetCommentsIdByFilter($aFilter, &$iCount, $iCurrPage = 0, $iPerPage = 0) {

        $aOrderAllow = array('comment_id', 'comment_pid', 'comment_rating', 'comment_date');
        $sOrder = '';
        if (!empty($aFilter['order'])) {
            if (is_string($aFilter['order'])) {
                $sOrder = $aFilter['order'];
            } elseif (is_array($aFilter['order'])) {
                $aOrders = array();
                foreach ($aFilter['order'] as $key => $value) {
                    if (is_numeric($key)) {
                        if (strpos($value, ' ')) {
                            list($key, $value) = explode(' ', $value);
                        } else {
                            $key = $value;
                            $value = 'asc';
                        }
                    }
                    if (in_array($key, $aOrderAllow)) {
                        if (!$value) {
                            $aOrders[] = $key;
                        } elseif (in_array($value, array('asc', 'desc'))) {
                            $aOrders[] = $key . ' ' . $value;
                        }
                    }
                }
                if ($aOrders) {
                    $sOrder = join(',', $aOrders);
                }
            }
        }
        if ($sOrder == '') {
            $sOrder = ' comment_id DESC ';
        }

        if (isset($aFilter['target_type']) && !is_array($aFilter['target_type'])) {
            $aFilter['target_type'] = array($aFilter['target_type']);
        }

        if ($iPerPage) {
            if ($iCurrPage < 1) {
                $iCurrPage = 1;
            }
            $iLimitOffset = ($iCurrPage - 1) * $iPerPage;
            $iLimitCount = $iPerPage;
            $sLimit = " LIMIT $iLimitOffset, $iLimitCount";
        } else {
            $sLimit = '';
        }

        $sql = "SELECT
					comment_id
				FROM
					?_comment
				WHERE
					1 = 1
					{ AND comment_id = ?d }
					{ AND user_id = ?d }
					{ AND target_type = ? }
					{ AND target_type IN (?a) }
					{ AND target_id = ?d }
					{ AND target_id IN (?a) }
					{ AND target_id NOT IN (?a) }
					{ AND target_parent_id = ?d }
					{ AND target_parent_id IN (?a) }
					{ AND target_parent_id NOT IN (?a) }
					{ AND comment_delete = ?d }
					{ AND comment_publish = ?d }
				ORDER BY {$sOrder}
				$sLimit;
					";
        $aResult = array();
        $aRows = $this->oDb->selectPage($iCount, $sql,
            isset($aFilter['id']) ? $aFilter['id'] : DBSIMPLE_SKIP,
            isset($aFilter['user_id']) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['target_type']) && !is_array($aFilter['target_type'])) ? $aFilter['target_type'] : DBSIMPLE_SKIP,
            (isset($aFilter['target_type']) && is_array($aFilter['target_type'])) ? $aFilter['target_type'] : DBSIMPLE_SKIP,
            (isset($aFilter['target_id']) && !is_array($aFilter['target_id'])) ? $aFilter['target_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['target_id']) && is_array($aFilter['target_id'])) ? $aFilter['target_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['not_target_id']) && is_array($aFilter['not_target_id'])) ? $aFilter['not_target_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['target_parent_id']) && !is_array($aFilter['target_parent_id'])) ? $aFilter['target_parent_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['target_parent_id']) && is_array($aFilter['target_parent_id'])) ? $aFilter['target_parent_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['not_target_parent_id']) && is_array($aFilter['not_target_parent_id'])) ? $aFilter['not_target_parent_id'] : DBSIMPLE_SKIP,
            isset($aFilter['delete']) ? $aFilter['delete'] : DBSIMPLE_SKIP,
            isset($aFilter['publish']) ? $aFilter['publish'] : DBSIMPLE_SKIP
        );
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aResult[] = $aRow['comment_id'];
            }
        }
        return $aResult;
    }
}

// EOF