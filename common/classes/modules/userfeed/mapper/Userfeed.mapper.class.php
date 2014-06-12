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
 * @package modules.userfeed
 * @since   1.0
 */
class ModuleUserfeed_MapperUserfeed extends Mapper {

    /**
     * Подписать пользователя
     *
     * @param int $iUserId        ID подписываемого пользователя
     * @param int $iSubscribeType Тип подписки (см. константы класса)
     * @param int $iTargetId      ID цели подписки
     *
     * @return bool
     */
    public function SubscribeUser($iUserId, $iSubscribeType, $iTargetId) {

        $sql = "
            SELECT *
            FROM ?_userfeed_subscribe
            WHERE
                user_id = ?d AND subscribe_type = ?d AND target_id = ?d
            LIMIT 1
            ";
        if (!$this->oDb->select($sql, $iUserId, $iSubscribeType, $iTargetId)) {
            $sql = "
                INSERT INTO ?_userfeed_subscribe
                (
                    user_id, subscribe_type, target_id
                )
                VALUES (
                    ?d, ?d, ?d
                )
                ";
            $this->oDb->query($sql, $iUserId, $iSubscribeType, $iTargetId);
            return true;
        }
        return false;
    }

    /**
     * Отписать пользователя
     *
     * @param int $iUserId        ID подписываемого пользователя
     * @param int $iSubscribeType Тип подписки (см. константы класса)
     * @param int $iTargetId      ID цели подписки
     *
     * @return bool
     */
    public function UnsubscribeUser($iUserId, $iSubscribeType, $iTargetId) {

        $sql = '
            DELETE FROM ?_userfeed_subscribe
            WHERE
                user_id = ?d AND subscribe_type = ?d AND target_id = ?d';
        $this->oDb->query($sql, $iUserId, $iSubscribeType, $iTargetId);
        return true;
    }

    /**
     * Gets list of subscribtion
     *
     * @param int        $iUserId
     * @param string|int $xTargetType
     * @param array      $aTargetsId
     *
     * @return array
     */
    public function GetUserSubscribes($iUserId, $xTargetType = null, $aTargetsId = array()) {

        $aResult = array(
            'blogs' => array(),
            'blog' => array(),
            'users' => array(),
            'user' => array(),
        );
        if (!is_null($xTargetType)) {
            if (!is_array($xTargetType)) {
                $xTargetType = array($xTargetType);
            }
            if (is_array($xTargetType)) {
                foreach ($xTargetType as $iKey => $iTargetType) {
                    if (!is_integer($iTargetType)) {
                        switch ($iTargetType) {
                            case 'blog':
                            case 'blogs':
                                $xTargetType[$iKey] = ModuleUserfeed::SUBSCRIBE_TYPE_BLOG;
                                break;
                            case 'user':
                            case 'users':
                                $xTargetType[$iKey] = ModuleUserfeed::SUBSCRIBE_TYPE_USER;
                                break;
                            default:
                                $xTargetType[$iKey] = 0;
                        }
                    }
                }
            }
            $xTargetType = array_unique($xTargetType);
            if (sizeof($xTargetType) == 0) {
                return $aResult;
            } elseif (sizeof($xTargetType) == 1) {
                $xTargetType = reset($xTargetType);
            }
        } else {
            $xTargetType = null;
        }

        $sql = '
            SELECT subscribe_type, target_id
            FROM ?_userfeed_subscribe
            WHERE
                user_id = ?d
                {AND subscribe_type=?d}
                {AND subscribe_type IN (?a)}
                {AND target_id=?d}
                {AND target_id IN (?a)}
            ';
        $aSubscribes = $this->oDb->select(
            $sql,
            $iUserId,
            ($xTargetType && !is_array($xTargetType)) ? $xTargetType : DBSIMPLE_SKIP,
            ($xTargetType && is_array($xTargetType)) ? $xTargetType : DBSIMPLE_SKIP,
            ($aTargetsId && !is_array($aTargetsId)) ? $aTargetsId : DBSIMPLE_SKIP,
            ($aTargetsId && is_array($aTargetsId)) ? $aTargetsId : DBSIMPLE_SKIP
        );

        if (!count($aSubscribes)) {
            return $aResult;
        }

        foreach ($aSubscribes as $aSubscribe) {
            if ($aSubscribe['subscribe_type'] == ModuleUserfeed::SUBSCRIBE_TYPE_BLOG) {
                $aResult['blogs'][$aSubscribe['target_id']] = $aSubscribe['target_id'];
                $aResult['blog'][$aSubscribe['target_id']] = $aSubscribe['target_id'];
            } elseif ($aSubscribe['subscribe_type'] == ModuleUserfeed::SUBSCRIBE_TYPE_USER) {
                $aResult['users'][$aSubscribe['target_id']] = $aSubscribe['target_id'];
                $aResult['user'][$aSubscribe['target_id']] = $aSubscribe['target_id'];
            }
        }
        return $aResult;
    }

    /**
     * Получить ленту топиков по подписке
     *
     * @param array $aUserSubscribes Список подписок пользователя
     * @param int   $iCount          Число получаемых записей (если null, из конфига)
     * @param int   $iFromId         Получить записи, начиная с указанной
     * @param array $aFilter         Дополнительные фильтры
     *
     * @return array
     */
    public function ReadFeed($aUserSubscribes, $iCount, $iFromId, $aFilter) {

        $sql
            = "
				SELECT
					t.topic_id
				FROM
					?_topic as t,
					?_blog as b
				WHERE
					t.topic_publish = 1
					AND (t.topic_date_show IS NULL OR t.topic_date_show <= ?)
					AND t.blog_id=b.blog_id
					{ AND b.blog_type=? }
					{ AND b.blog_type IN (?a) }
					{ AND b.blog_type!=? }
					{ AND b.blog_type NOT IN (?a) }
					{ AND t.topic_id < ?d }
					AND ( 1=0 { OR t.blog_id IN (?a) } { OR t.user_id IN (?a) } )
                ORDER BY t.topic_id DESC
                { LIMIT 0, ?d }";

        $aTopics = $aTopics = $this->oDb->selectCol(
            $sql,
            F::Now(),
            (isset($aFilter['include_types']) && !is_array($aFilter['include_types'])) ? $aFilter['include_types'] : DBSIMPLE_SKIP,
            (isset($aFilter['include_types']) && is_array($aFilter['include_types'])) ? $aFilter['include_types'] : DBSIMPLE_SKIP,
            (isset($aFilter['exclude_types']) && !is_array($aFilter['exclude_types'])) ? $aFilter['exclude_types'] : DBSIMPLE_SKIP,
            (isset($aFilter['exclude_types']) && is_array($aFilter['exclude_types'])) ? $aFilter['exclude_types'] : DBSIMPLE_SKIP,
            $iFromId ? $iFromId : DBSIMPLE_SKIP,
            count($aUserSubscribes['blogs']) ? $aUserSubscribes['blogs'] : DBSIMPLE_SKIP,
            count($aUserSubscribes['users']) ? $aUserSubscribes['users'] : DBSIMPLE_SKIP,
            $iCount ? $iCount : DBSIMPLE_SKIP
        );
        return $aTopics;
    }


    /**
     * Возвращает количество новых комментариев
     *
     * @param $sUserId
     *
     * @return bool
     */
    public function GetCountTrackNew($sUserId) {

        $sql
            = "
				SELECT
					SUM(t.topic_count_comment - tr.comment_count_last) as count_new
				FROM
 						?_topic_read as tr,
					?_topic as t
				WHERE
					t.topic_id=tr.topic_id
					AND
					tr.user_id=?d
					AND
					t.topic_id IN (
						SELECT target_id FROM ?_track WHERE
							target_type = ?
							AND
							user_id = ?d
							AND
							status=?d
						)
		";
        if ($aRow = $this->oDb->selectRow($sql, $sUserId, 'topic_new_comment', $sUserId, 1)) {
            return $aRow['count_new'];
        }
        return false;
    }

}

// EOF