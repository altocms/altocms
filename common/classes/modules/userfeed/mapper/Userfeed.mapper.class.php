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
    public function subscribeUser($iUserId, $iSubscribeType, $iTargetId) {

        $sql = '
            SELECT *
            FROM ?_userfeed_subscribe
            WHERE
                user_id = ?d AND subscribe_type = ?d AND target_id = ?d';
        if (!$this->oDb->select($sql, $iUserId, $iSubscribeType, $iTargetId)) {
            $sql = '
                INSERT INTO ?_userfeed_subscribe
                SET
                    user_id = ?d, subscribe_type = ?d, target_id = ?d';
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
    public function unsubscribeUser($iUserId, $iSubscribeType, $iTargetId) {

        $sql = '
            DELETE FROM ?_userfeed_subscribe
            WHERE
                user_id = ?d AND subscribe_type = ?d AND target_id = ?d';
        $this->oDb->query($sql, $iUserId, $iSubscribeType, $iTargetId);
        return true;
    }

    /**
     * Получить список подписок пользователя
     *
     * @param int $iUserId ID пользователя, для которого загружаются подписки
     *
     * @return array
     */
    public function getUserSubscribes($iUserId) {

        $sql = '
            SELECT subscribe_type, target_id
            FROM ?_userfeed_subscribe
            WHERE
                user_id = ?d';
        $aSubscribes = $this->oDb->select($sql, $iUserId);
        $aResult = array('blogs' => array(), 'users' => array());

        if (!count($aSubscribes)) {
            return $aResult;
        }

        foreach ($aSubscribes as $aSubscribe) {
            if ($aSubscribe['subscribe_type'] == ModuleUserfeed::SUBSCRIBE_TYPE_BLOG) {
                $aResult['blogs'][] = $aSubscribe['target_id'];
            } elseif ($aSubscribe['subscribe_type'] == ModuleUserfeed::SUBSCRIBE_TYPE_USER) {
                $aResult['users'][] = $aSubscribe['target_id'];
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
     *
     * @return array
     */
    public function readFeed($aUserSubscribes, $iCount, $iFromId) {

        $sql
            = "
				SELECT
					t.topic_id
				FROM
					?_topic as t,
					?_blog as b
				WHERE
					t.topic_publish = 1
					AND t.blog_id=b.blog_id
					AND b.blog_type!='close'
					{ AND t.topic_id < ?d }
					AND ( 1=0 { OR t.blog_id IN (?a) } { OR t.user_id IN (?a) } )
                ORDER BY t.topic_id DESC
                { LIMIT 0, ?d }";

        $aTopics = $aTopics = $this->oDb->selectCol(
            $sql,
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