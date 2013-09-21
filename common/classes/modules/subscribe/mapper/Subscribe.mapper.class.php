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
 * @package modules.subscribe
 * @since   1.0
 */
class ModuleSubscribe_MapperSubscribe extends Mapper {

    /**
     * Добавляет подписку в БД
     *
     * @param ModuleSubscribe_EntitySubscribe $oSubscribe    Объект подписки
     *
     * @return int|bool
     */
    public function AddSubscribe($oSubscribe) {

        $sql = "INSERT INTO ?_subscribe SET ?a ";
        if ($iId = $this->oDb->query($sql, $oSubscribe->_getData())) {
            return $iId;
        }
        return false;
    }

    /**
     * Получение подписки по типы и емайлу
     *
     * @param string $sType    Тип
     * @param string $sMail    Емайл
     *
     * @return ModuleSubscribe_EntitySubscribe|null
     */
    public function GetSubscribeByTypeAndMail($sType, $sMail) {

        $sql = "SELECT * FROM ?_subscribe WHERE target_type = ? AND mail = ?";
        if ($aRow = $this->oDb->selectRow($sql, $sType, $sMail)) {
            return Engine::GetEntity('Subscribe', $aRow);
        }
        return null;
    }

    /**
     * Обновление подписки
     *
     * @param ModuleSubscribe_EntitySubscribe $oSubscribe    Объект подписки
     *
     * @return int
     */
    public function UpdateSubscribe($oSubscribe) {

        $sql = "UPDATE ?_subscribe
			SET 
			 	status = ?, 
			 	date_remove = ?
			WHERE id = ?d
		";
        $bResult = $this->oDb->query(
            $sql, $oSubscribe->getStatus(),
            $oSubscribe->getDateRemove(),
            $oSubscribe->getId()
        );
        return $bResult !== false;
    }

    /**
     * Смена емайла в подписках
     *
     * @param string   $sMailOld Старый емайл
     * @param string   $sMailNew Новый емайл
     * @param int|null $iUserId  Id пользователя
     *
     * @return int
     */
    public function ChangeSubscribeMail($sMailOld, $sMailNew, $iUserId = null) {

        $sql = "UPDATE ?_subscribe
			SET
			 	mail = ?
			WHERE mail = ? { and user_id = ?d }
		";
        $bResult = $this->oDb->query($sql, $sMailNew, $sMailOld, $iUserId ? $iUserId : DBSIMPLE_SKIP);
        return $bResult !== false;
    }

    /**
     * Возвращает список подписок по фильтру
     *
     * @param array $aFilter      Фильтр
     * @param array $aOrder       Сортировка
     * @param int   $iCount       Возвращает общее количество элементов
     * @param int   $iCurrPage    Номер страницы
     * @param int   $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetSubscribes($aFilter, $aOrder, &$iCount, $iCurrPage, $iPerPage) {
        $aOrderAllow = array('id', 'date_add', 'status');
        $sOrder = '';
        foreach ($aOrder as $key => $value) {
            if (!in_array($key, $aOrderAllow)) {
                unset($aOrder[$key]);
            } elseif (in_array($value, array('asc', 'desc'))) {
                $sOrder .= " {$key} {$value},";
            }
        }
        $sOrder = trim($sOrder, ',');
        if ($sOrder == '') {
            $sOrder = ' id desc ';
        }

        if (isset($aFilter['exclude_mail']) and !is_array($aFilter['exclude_mail'])) {
            $aFilter['exclude_mail'] = array($aFilter['exclude_mail']);
        }

        $sql
            = "SELECT
					*
				FROM
					?_subscribe
				WHERE
					1 = 1
					{ AND target_type = ? }
					{ AND target_id = ?d }
					{ AND mail = ? }
					{ AND mail not IN (?a) }
					{ AND `key` = ? }
					{ AND status = ?d }
				ORDER by {$sOrder}
				LIMIT ?d, ?d ;
					";
        $aResult = array();
        if ($aRows = $this->oDb->selectPage(
            $iCount, $sql,
            isset($aFilter['target_type']) ? $aFilter['target_type'] : DBSIMPLE_SKIP,
            isset($aFilter['target_id']) ? $aFilter['target_id'] : DBSIMPLE_SKIP,
            isset($aFilter['mail']) ? $aFilter['mail'] : DBSIMPLE_SKIP,
            (isset($aFilter['exclude_mail']) and count($aFilter['exclude_mail'])) ? $aFilter['exclude_mail']
                : DBSIMPLE_SKIP,
            isset($aFilter['key']) ? $aFilter['key'] : DBSIMPLE_SKIP,
            isset($aFilter['status']) ? $aFilter['status'] : DBSIMPLE_SKIP,
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        )
        ) {
            foreach ($aRows as $aRow) {
                $aResult[] = Engine::GetEntity('Subscribe', $aRow);
            }
        }
        return $aResult;
    }


    /**
     * Добавляет трекинг в БД
     *
     * @param ModuleSubscribe_EntityTrack $oTrack    Объект подписки
     *
     * @return int|bool
     */
    public function AddTrack($oTrack) {

        $sql = "INSERT INTO ?_track SET ?a ";
        if ($iId = $this->oDb->query($sql, $oTrack->_getData())) {
            return $iId;
        }
        return false;
    }

    /**
     * Обновление трекинга
     *
     * @param ModuleSubscribe_EntityTrack $oTrack    Объект подписки
     *
     * @return int
     */
    public function UpdateTrack($oTrack) {

        $sql = "UPDATE ?_track
			SET
			 	status = ?,
			 	date_remove = ?
			WHERE id = ?d
		";
        $bResult = $this->oDb->query(
            $sql, $oTrack->getStatus(),
            $oTrack->getDateRemove(),
            $oTrack->getId()
        );
        return $bResult !== false;
    }

    /**
     * Возвращает список треков по фильтру
     *
     * @param array $aFilter      Фильтр
     * @param array $aOrder       Сортировка
     * @param int   $iCount       Возвращает общее количество элементов
     * @param int   $iCurrPage    Номер страницы
     * @param int   $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetTracks($aFilter, $aOrder, &$iCount, $iCurrPage, $iPerPage) {

        $aOrderAllow = array('id', 'date_add', 'status');
        $sOrder = '';
        foreach ($aOrder as $key => $value) {
            if (!in_array($key, $aOrderAllow)) {
                unset($aOrder[$key]);
            } elseif (in_array($value, array('asc', 'desc'))) {
                $sOrder .= " {$key} {$value},";
            }
        }
        $sOrder = trim($sOrder, ',');
        if ($sOrder == '') {
            $sOrder = ' id desc ';
        }

        if (isset($aFilter['exclude_mail']) and !is_array($aFilter['exclude_mail'])) {
            $aFilter['exclude_mail'] = array($aFilter['exclude_mail']);
        }

        $sql
            = "SELECT
					*
				FROM
					?_track trc
				WHERE
					1 = 1
					{ AND trc.target_type = ? }
					{ AND trc.target_id = ?d }
					{ AND trc.user_id = ?d }
					{ AND trc.user_id not IN (?a) }
					{ AND trc.`key` = ? }
					{ AND trc.status = ?d }
					{ AND exists   (  SELECT ?d
					                  FROM   ?_topic_read as tr,
					                         ?_topic as t
					                  WHERE   t.topic_id = trc.target_id
					                  AND t.topic_id = tr.topic_id
					                  AND (t.topic_count_comment - tr.comment_count_last) > 0
					                  AND  tr.user_id = trc.user_id
					                  ) }
				ORDER by {$sOrder}
				LIMIT ?d, ?d ;
					";
        $aResult = array();
        $aRows = $this->oDb->selectPage(
            $iCount, $sql,
            isset($aFilter['target_type']) ? $aFilter['target_type'] : DBSIMPLE_SKIP,
            isset($aFilter['target_id']) ? $aFilter['target_id'] : DBSIMPLE_SKIP,
            isset($aFilter['user_id']) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['exclude_user']) and count($aFilter['exclude_user'])) ? $aFilter['exclude_user']
                : DBSIMPLE_SKIP,
            isset($aFilter['key']) ? $aFilter['key'] : DBSIMPLE_SKIP,
            isset($aFilter['status']) ? $aFilter['status'] : DBSIMPLE_SKIP,
            (isset($aFilter['only_new']) and $aFilter['only_new']) ? 1 : DBSIMPLE_SKIP,
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aResult[] = Engine::GetEntity('ModuleSubscribe_EntityTrack', $aRow);
            }
        }
        return $aResult;
    }

}

// EOF