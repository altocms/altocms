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
 * @package modules.topic
 * @since   1.0
 */
class ModuleTopic_MapperTopic extends Mapper {

    /**
     * Добавляет топик
     *
     * @param ModuleTopic_EntityTopic $oTopic    Объект топика
     *
     * @return int|bool
     */
    public function AddTopic(ModuleTopic_EntityTopic $oTopic) {

        $sql = "INSERT INTO ?_topic (
                blog_id,
                user_id,
                topic_type,
                topic_title,
                topic_tags,
                topic_date_add,
                topic_date_show,
                topic_user_ip,
                topic_publish,
                topic_publish_draft,
                topic_publish_index,
                topic_cut_text,
                topic_forbid_comment,
                topic_text_hash,
                topic_url
			)
			VALUES (
                ?d:blog_id,
                ?d:user_id,
                ?:topic_type,
                ?:topic_title,
                ?:topic_tags,
                ?:topic_date_add,
                ?:topic_date_show,
                ?:topic_user_ip,
                ?d:topic_publish,
                ?d:topic_publish_draft,
                ?d:topic_publish_index,
                ?:topic_cut_text,
                ?d:topic_forbid_comment,
                ?:topic_text_hash,
                ?:topic_url
			)
		";
        $nId = $this->oDb->sqlQuery(
            $sql,
            array(
                ':blog_id'              => $oTopic->getBlogId(),
                ':user_id'              => $oTopic->getUserId(),
                ':topic_type'           => $oTopic->getType(),
                ':topic_title'          => $oTopic->getTitle(),
                ':topic_tags'           => $oTopic->getTags(),
                ':topic_date_add'       => $oTopic->getDateAdd(),
                ':topic_date_show'      => $oTopic->getDateShow(),
                ':topic_user_ip'        => $oTopic->getUserIp(),
                ':topic_publish'        => $oTopic->getPublish(),
                ':topic_publish_draft'  => $oTopic->getPublishDraft(),
                ':topic_publish_index'  => $oTopic->getPublishIndex(),
                ':topic_cut_text'       => $oTopic->getCutText(),
                ':topic_forbid_comment' => $oTopic->getForbidComment(),
                ':topic_text_hash'      => $oTopic->getTextHash(),
                ':topic_url'            => $oTopic->getTopicUrl()
            )
        );
        if ($nId) {
            $oTopic->setId($nId);
            $this->AddTopicContent($oTopic);
            return $nId;
        }
        return false;
    }

    /**
     * Добавляет контент топика
     *
     * @param ModuleTopic_EntityTopic $oTopic    Объект топика
     *
     * @return int|bool
     */
    public function AddTopicContent(ModuleTopic_EntityTopic $oTopic) {

        $sql = "INSERT INTO ?_topic_content
			(topic_id,
			topic_text,
			topic_text_short,
			topic_text_source,
			topic_extra
			)
			VALUES(?d, ?, ?, ?, ? )
		";
        $nId = $this->oDb->query(
            $sql, $oTopic->getId(), $oTopic->getText(),
            $oTopic->getTextShort(), $oTopic->getTextSource(), $oTopic->getExtra()
        );
        return $nId ? $nId : false;
    }

    /**
     * Добавление тега к топику
     *
     * @param ModuleTopic_EntityTopicTag $oTopicTag    Объект тега топика
     *
     * @return int
     */
    public function AddTopicTag(ModuleTopic_EntityTopicTag $oTopicTag) {

        $sql = "INSERT INTO ?_topic_tag
			(topic_id,
			user_id,
			blog_id,
			topic_tag_text
			)
			VALUES(?d, ?d, ?d, ?)
		";
        $nId = $this->oDb->query(
            $sql, $oTopicTag->getTopicId(), $oTopicTag->getUserId(), $oTopicTag->getBlogId(), $oTopicTag->getText()
        );
        return $nId ? $nId : false;
    }

    /**
     * Удаление контента топика по его номеру
     *
     * @param   int|array $aIds   - ID топика или массив ID
     *
     * @return  bool
     */
    public function DeleteTopicContentByTopicId($aIds) {

        $sql
            = "
            DELETE FROM ?_topic_content
            WHERE topic_id IN (?a) ";
        return ($this->oDb->query($sql, $aIds) !== false);
    }

    /**
     * Удаляет теги у топика
     *
     * @param   int|array $aIds   - ID топика или массив ID
     *
     * @return  bool
     */
    public function DeleteTopicTagsByTopicId($aIds) {

        $aIds = $this->_arrayId($aIds);
        $sql
            = "
            DELETE FROM ?_topic_tag
            WHERE topic_id IN (?a)
        ";
        return ($this->oDb->query($sql, $aIds) !== false);
    }

    /**
     * Удаляет топик(и)
     * Если тип таблиц в БД InnoDB, то удалятся всё связи по топику (комменты, голосования, избранное)
     *
     * @param   int|array $aIds   - ID топика или массив ID
     *
     * @return  bool
     */
    public function DeleteTopic($aIds) {

        $aIds = $this->_arrayId($aIds);
        $sql
            = "
            DELETE FROM ?_topic
            WHERE topic_id IN (?a)
        ";
        return ($this->oDb->query($sql, $aIds) !== false);
    }

    /**
     * Получает топик по уникальному хешу(текст топика)
     *
     * @param int    $nUserId
     * @param string $sHash
     *
     * @return int|null
     */
    public function GetTopicUnique($nUserId, $sHash) {

        $sql = "SELECT topic_id FROM ?_topic
			WHERE
				topic_text_hash =?
				{AND user_id = ?d}
			LIMIT 0,1
				";
        if ($aRow = $this->oDb->selectRow($sql, $sHash, $nUserId ? $nUserId : DBSIMPLE_SKIP)) {
            return $aRow['topic_id'];
        }
        return null;
    }

    /**
     * Получает ID топика по URL
     *
     * @param string $sUrl
     *
     * @return int
     */
    public function GetTopicIdByUrl($sUrl) {

        $sql
            = "
            SELECT topic_id FROM ?_topic
            WHERE
                topic_url =?
            LIMIT 0,1
            ";
        $xResult = $this->oDb->selectCell($sql, $sUrl);
        return intval($xResult);
    }

    /**
     * Получает ID топиков по похожим URL
     *
     * @param $sUrl
     *
     * @return int
     */
    public function GetTopicsIdLikeUrl($sUrl) {

        $sql
            = "
            SELECT topic_id FROM ?_topic
            WHERE
                topic_url = '" . $sUrl . "'
                OR topic_url RLIKE '^" . $sUrl . "-[0-9]+$'
            ";
        $xResult = $this->oDb->selectCol($sql, $sUrl);
        return $xResult;
    }

    /**
     * Получить список топиков по списку айдишников
     *
     * @param array $aTopicId    Список ID топиков
     *
     * @return array
     */
    public function GetTopicsByArrayId($aTopicId) {

        if (!is_array($aTopicId) || count($aTopicId) == 0) {
            return array();
        }
        $nLimit = sizeof($aTopicId);
        $sql
            = "SELECT
					t.*,
					tc.*
				FROM 
					?_topic as t
					JOIN  ?_topic_content AS tc ON t.topic_id=tc.topic_id
				WHERE 
					t.topic_id IN(?a)
				ORDER BY FIELD(t.topic_id,?a)
				LIMIT $nLimit";
        $aTopics = array();
        if ($aRows = $this->oDb->select($sql, $aTopicId, $aTopicId)) {
            $aTopics = Engine::GetEntityRows('Topic', $aRows);
        }
        return $aTopics;
    }

    /**
     * Список топиков по фильтру
     *
     * @param  array $aFilter      Фильтр
     * @param  int   $iCount       Возвращает общее число элементов
     * @param  int   $iCurrPage    Номер страницы
     * @param  int   $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetTopics($aFilter, &$iCount, $iCurrPage, $iPerPage) {

        $sWhere = $this->buildFilter($aFilter);

        if (!isset($aFilter['order'])) {
            $aFilter['order'] = 't.topic_date_show DESC, t.topic_date_add DESC';
        }
        if (!is_array($aFilter['order'])) {
            $aFilter['order'] = array($aFilter['order']);
        }

        $aTopics = array();
        if ($iCurrPage && $iPerPage) {
            $sql
                = "
            SELECT
			    t.topic_id
			FROM
			    ?_topic as t,
				?_blog as b
			WHERE
			    1=1
				" . $sWhere . "
				AND t.blog_id=b.blog_id
			ORDER BY " . implode(', ', $aFilter['order']) . "
			LIMIT ?d, ?d";
            if ($aRows = $this->oDb->selectPage($iCount, $sql, ($iCurrPage - 1) * $iPerPage, $iPerPage)) {
                foreach ($aRows as $aTopic) {
                    $aTopics[] = $aTopic['topic_id'];
                }
            }
        } else {
            $sql
                = "
            SELECT
			    t.topic_id
			FROM
			    ?_topic as t,
				?_blog as b
			WHERE
			    1=1
				" . $sWhere . "
				AND t.blog_id=b.blog_id
			ORDER BY " . implode(', ', $aFilter['order']);
            $aTopics = $this->oDb->selectCol($sql);
        }
        return $aTopics ? $aTopics : array();
    }

    /**
     * Количество топиков по фильтру
     *
     * @param array $aFilter    Фильтр
     *
     * @return int
     */
    public function GetCountTopics($aFilter) {

        $sWhere = $this->buildFilter($aFilter);
        $sql
            = "SELECT
					count(t.topic_id) as count
				FROM 
					?_topic as t,
					?_blog as b
				WHERE 
					1=1
					
					" . $sWhere . "
					
					AND
					t.blog_id=b.blog_id;";
        if ($aRow = $this->oDb->selectRow($sql)) {
            return $aRow['count'];
        }
        return false;
    }

    /**
     * Возвращает все топики по фильтру
     *
     * @param array $aFilter    Фильтр
     *
     * @return array
     */
    public function GetAllTopics($aFilter) {

        $sWhere = $this->buildFilter($aFilter);

        if (!isset($aFilter['order'])) {
            $aFilter['order'] = 't.topic_id desc';
        }
        if (!is_array($aFilter['order'])) {
            $aFilter['order'] = array($aFilter['order']);
        }

        $sql
            = "SELECT
						t.topic_id
					FROM 
						?_topic as t,
						?_blog as b
					WHERE 
						1=1
						" . $sWhere . "
						AND
						t.blog_id=b.blog_id
					ORDER by " . implode(', ', $aFilter['order']) . " ";
        $aTopics = array();
        if ($aRows = $this->oDb->select($sql)) {
            foreach ($aRows as $aTopic) {
                $aTopics[] = $aTopic['topic_id'];
            }
        }

        return $aTopics;
    }

    /**
     * Получает список топиков по тегу
     *
     * @param  string $sTag            Тег
     * @param  array  $aExcludeBlog    Список ID блогов для исключения
     * @param  int    $iCount          Возвращает общее количество элементов
     * @param  int    $iCurrPage       Номер страницы
     * @param  int    $iPerPage        Количество элементов на страницу
     *
     * @return array
     */
    public function GetTopicsByTag($sTag, $aExcludeBlog, &$iCount, $iCurrPage, $iPerPage) {

        $sql
            = "
            SELECT
			    topic_id
			FROM
			    ?_topic_tag
			WHERE
			    topic_tag_text = ?
				{ AND blog_id NOT IN (?a) }
            ORDER BY topic_id DESC
            LIMIT ?d, ?d ";

        $aTopics = array();
        $aRows = $this->oDb->selectPage(
            $iCount, $sql, $sTag,
            (is_array($aExcludeBlog) && count($aExcludeBlog)) ? $aExcludeBlog : DBSIMPLE_SKIP,
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );
        if ($aRows) {
            foreach ($aRows as $aTopic) {
                $aTopics[] = $aTopic['topic_id'];
            }
        }
        return $aTopics;
    }

    /**
     * Получает топики по рейтингу и дате
     *
     * @param string $sDate           Дата
     * @param int    $iLimit          Количество
     * @param array  $aExcludeBlog    Список ID блогов для исключения
     *
     * @return array
     */
    public function GetTopicsRatingByDate($sDate, $iLimit, $aExcludeBlog = array()) {

        $sql
            = "SELECT
						t.topic_id
					FROM 
						?_topic as t
					WHERE
						t.topic_publish = 1
						AND
						t.topic_date_add >= ?
						AND
						t.topic_rating >= 0
						{ AND t.blog_id NOT IN(?a) }
					ORDER by t.topic_rating desc, t.topic_id desc
					LIMIT 0, ?d ";
        $aTopics = array();
        $aRows = $this->oDb->select(
            $sql, $sDate,
            (is_array($aExcludeBlog) && count($aExcludeBlog)) ? $aExcludeBlog : DBSIMPLE_SKIP,
            $iLimit
        );
        if ($aRows) {
            foreach ($aRows as $aTopic) {
                $aTopics[] = $aTopic['topic_id'];
            }
        }
        return $aTopics;
    }

    /**
     * Получает список тегов топиков
     *
     * @param int   $iLimit           Количество
     * @param array $aExcludeTopic    Список ID топиков для исключения
     *
     * @return array
     */
    public function GetTopicTags($iLimit, $aExcludeTopic = array()) {

        $sql
            = "SELECT
			tt.topic_tag_text,
			count(tt.topic_tag_text) as count
			FROM 
				?_topic_tag as tt
			WHERE 
				1=1
				{AND tt.topic_id NOT IN(?a) }
			GROUP BY 
				tt.topic_tag_text
			ORDER BY 
				count desc
			LIMIT 0, ?d
				";

        $aResult = array();
        $aRows = $this->oDb->select(
            $sql,
            (is_array($aExcludeTopic) && count($aExcludeTopic)) ? $aExcludeTopic : DBSIMPLE_SKIP,
            $iLimit
        );
        if ($aRows) {
            $aData = array();
            foreach ($aRows as $aRow) {
                $aData[mb_strtolower($aRow['topic_tag_text'], 'UTF-8')] = $aRow;
            }
            ksort($aData);
            foreach ($aData as $aRow) {
                $aResult[] = Engine::GetEntity('Topic_TopicTag', $aRow);
            }
        }
        return $aResult;
    }

    /**
     * Получает список тегов из топиков открытых блогов (open,personal)
     *
     * @param  int      $iLimit     Количество
     * @param  int|null $iUserId    ID пользователя, чью теги получаем
     *
     * @return array
     */
    public function GetOpenTopicTags($iLimit, $iUserId = null) {

        $sql
            = "
			SELECT 
				tt.topic_tag_text,
				COUNT(tt.topic_tag_text) as count
			FROM 
				?_topic_tag as tt,
				?_blog as b
			WHERE
				1 = 1
				{ AND tt.user_id = ?d }
				AND
				tt.blog_id = b.blog_id
				AND
				b.blog_type <> 'close'
			GROUP BY 
				tt.topic_tag_text
			ORDER BY 
				count DESC
			LIMIT 0, ?d
				";
        $aResult = array();
        if ($aRows = $this->oDb->select($sql, is_null($iUserId) ? DBSIMPLE_SKIP : $iUserId, $iLimit)) {
            $aData = array();
            foreach ($aRows as $aRow) {
                $aData[mb_strtolower($aRow['topic_tag_text'], 'UTF-8')] = $aRow;
            }
            ksort($aData);
            foreach ($aData as $aRow) {
                $aResult[] = Engine::GetEntity('Topic_TopicTag', $aRow);
            }
        }
        return $aResult;
    }

    /**
     * Увеличивает у топика число комментов
     *
     * @param int $iTopicId    ID топика
     *
     * @return bool
     */
    public function increaseTopicCountComment($iTopicId) {

        $sql = "UPDATE ?_topic
			SET 
				topic_count_comment=topic_count_comment+1
			WHERE
				topic_id = ?d
		";
        $bResult = $this->oDb->query($sql, $iTopicId);
        return $bResult !== false;
    }

    /**
     * Recalculate count of comments
     *
     * @param $iTopicId
     *
     * @return bool
     */
    public function RecalcCountOfComments($iTopicId) {

        $sql = "UPDATE ?_topic
			SET
				topic_count_comment=(SELECT COUNT(*) FROM ?_comment WHERE target_id=?d)
			WHERE
				topic_id = ?d
		";
        $bResult = $this->oDb->query($sql, $iTopicId, $iTopicId);
        return $bResult !== false;
    }

    /**
     * Обновляет топик
     *
     * @param ModuleTopic_EntityTopic $oTopic    Объект топика
     *
     * @return bool
     */
    public function UpdateTopic(ModuleTopic_EntityTopic $oTopic) {

        $sql = "UPDATE ?_topic
			SET 
				blog_id = ?d,
				topic_title = ?,
				topic_tags = ?,
				topic_date_add = ?,
				topic_date_edit = ?,
				topic_date_show = ?,
				topic_user_ip = ?,
				topic_publish = ?d ,
				topic_publish_draft = ?d ,
				topic_publish_index = ?d,
				topic_rating = ?f,
				topic_count_vote = ?d,
				topic_count_vote_up = ?d,
				topic_count_vote_down = ?d,
				topic_count_vote_abstain = ?d,
				topic_count_read = ?d,
				topic_count_comment = ?d,
				topic_count_favourite = ?d,
				topic_cut_text = ? ,
				topic_forbid_comment = ? ,
				topic_text_hash = ?,
				topic_url = ?
			WHERE
				topic_id = ?d
		";
        $bResult = $this->oDb->query(
            $sql,
            $oTopic->getBlogId(),
            $oTopic->getTitle(),
            $oTopic->getTags(),
            $oTopic->getDateAdd(),
            $oTopic->getDateEdit(),
            $oTopic->getDateShow(),
            $oTopic->getUserIp(),
            $oTopic->getPublish(),
            $oTopic->getPublishDraft(),
            $oTopic->getPublishIndex(),
            $oTopic->getRating(),
            $oTopic->getCountVote(),
            $oTopic->getCountVoteUp(),
            $oTopic->getCountVoteDown(),
            $oTopic->getCountVoteAbstain(),
            $oTopic->getCountRead(),
            $oTopic->getCountComment(),
            $oTopic->getCountFavourite(),
            $oTopic->getCutText(),
            $oTopic->getForbidComment(),
            $oTopic->getTextHash(),
            $oTopic->getTopicUrl(),
            $oTopic->getId()
        );
        if ($bResult !== false) {
            $this->UpdateTopicContent($oTopic);
            return true;
        }
        return false;
    }

    /**
     * Обновляет контент топика
     *
     * @param ModuleTopic_EntityTopic $oTopic    Объект топика
     *
     * @return bool
     */
    public function UpdateTopicContent(ModuleTopic_EntityTopic $oTopic) {

        $sql = "UPDATE ?_topic_content
			SET
				topic_text= ?,
				topic_text_short= ?,
				topic_text_source= ?,
				topic_extra= ?
			WHERE
				topic_id = ?d
		";
        $bResult = $this->oDb->query(
            $sql, $oTopic->getText(), $oTopic->getTextShort(), $oTopic->getTextSource(), $oTopic->getExtra(),
            $oTopic->getId()
        );
        return $bResult !== false;
    }

    /**
     * Строит строку условий для SQL запроса топиков
     *
     * @param array $aFilter    Фильтр
     *
     * @return string
     */
    protected function buildFilter($aFilter) {

        $sWhere = '';
        if (isset($aFilter['topic_date_more'])) {
            $sWhere .= " AND ((t.topic_date_show IS NOT NULL AND t.topic_date_show >  " . $this->oDb->escape($aFilter['topic_date_more']) . ")";
            $sWhere .= " OR (t.topic_date_show IS NULL AND t.topic_date_add >  " . $this->oDb->escape($aFilter['topic_date_more']) . "))";
        }
        if (isset($aFilter['topic_publish'])) {
            $sWhere .= " AND (t.topic_publish =  " . ($aFilter['topic_publish'] ? 1 : 0) . ")";
            $sWhere .= " AND (t.topic_date_show IS NULL OR t.topic_date_show <= NOW())";
        }
        if (isset($aFilter['topic_rating']) && is_array($aFilter['topic_rating'])) {
            $sPublishIndex = '';
            if (isset($aFilter['topic_rating']['publish_index']) && $aFilter['topic_rating']['publish_index'] == 1) {
                $sPublishIndex = " OR topic_publish_index=1 ";
            }
            if ($aFilter['topic_rating']['type'] == 'top') {
                $sWhere
                    .= " AND ( t.topic_rating >= " . (float)$aFilter['topic_rating']['value'] . " {$sPublishIndex} ) ";
            } else {
                $sWhere .= " AND ( t.topic_rating < " . (float)$aFilter['topic_rating']['value'] . "  ) ";
            }
        }
        if (isset($aFilter['topic_new'])) {
            $sWhere .= " AND (";
            $sWhere .= "(t.topic_date_show IS NOT NULL AND t.topic_date_show >=  '" . $aFilter['topic_new'] . "' AND t.topic_date_show < NOW())";
            $sWhere .= " OR (t.topic_date_show IS NULL AND t.topic_date_add >=  '" . $aFilter['topic_new'] . "')";
            $sWhere .= ")";
        }
        if (isset($aFilter['user_id'])) {
            $sWhere .= is_array($aFilter['user_id'])
                ? " AND t.user_id IN(" . implode(', ', $aFilter['user_id']) . ")"
                : " AND t.user_id =  " . (int)$aFilter['user_id'];
        }
        if (isset($aFilter['blog_id'])) {
            if (!is_array($aFilter['blog_id'])) {
                $aFilter['blog_id'] = array($aFilter['blog_id']);
            }
            $sWhere .= " AND t.blog_id IN ('" . join("','", $aFilter['blog_id']) . "')";
        }
        if (isset($aFilter['blog_type']) && is_array($aFilter['blog_type'])) {
            $aBlogTypes = array();
            foreach ($aFilter['blog_type'] as $sType => $aBlogId) {
                /**
                 * Позиция вида 'type'=>array('id1', 'id2')
                 */
                if (!is_array($aBlogId) && is_string($sType)) {
                    $aBlogId = array($aBlogId);
                }
                /**
                 * Позиция вида 'type'
                 */
                if (is_string($aBlogId) && is_int($sType)) {
                    $sType = $aBlogId;
                    $aBlogId = array();
                }

                $aBlogTypes[] = (count($aBlogId) == 0)
                    ? "(b.blog_type='" . $sType . "')"
                    : "(b.blog_type='" . $sType . "' AND t.blog_id IN ('" . join("','", $aBlogId) . "'))";
            }
            $sWhere .= " AND (" . join(" OR ", (array)$aBlogTypes) . ")";
        }
        if (isset($aFilter['topic_type'])) {
            if (!is_array($aFilter['topic_type'])) {
                $aFilter['topic_type'] = array($aFilter['topic_type']);
            }
            $sWhere .= " AND t.topic_type IN (" . join(
                    ",", array_map(array($this->oDb, 'escape'), $aFilter['topic_type'])
                ) . ")";
        }
        return $sWhere;
    }

    /**
     * Получает список тегов по первым буквам тега
     *
     * @param string $sTag      Тэг
     * @param int    $iLimit    Количество
     *
     * @return bool
     */
    public function GetTopicTagsByLike($sTag, $iLimit) {

        $sTag = mb_strtolower($sTag, 'UTF-8');
        $sql
            = "SELECT
				topic_tag_text
			FROM 
				?_topic_tag
			WHERE
				topic_tag_text LIKE ?
			GROUP BY 
				topic_tag_text
			LIMIT 0, ?d
				";
        $aResult = array();
        if ($aRows = $this->oDb->select($sql, $sTag . '%', $iLimit)) {
            $aResult = Engine::GetEntityRows('Topic_TopicTag', $aRows);
        }
        return $aResult;
    }

    /**
     * Обновляем дату прочтения топика
     *
     * @param ModuleTopic_EntityTopicRead $oTopicRead    Объект факта чтения топика
     *
     * @return int
     */
    public function UpdateTopicRead(ModuleTopic_EntityTopicRead $oTopicRead) {

        $sql = "UPDATE ?_topic_read
			SET 
				comment_count_last = ? ,
				comment_id_last = ? ,
				date_read = ? 
			WHERE
				topic_id = ? 
				AND
				user_id = ? 
		";
        $bResult = $this->oDb->query(
            $sql, $oTopicRead->getCommentCountLast(), $oTopicRead->getCommentIdLast(), $oTopicRead->getDateRead(),
            $oTopicRead->getTopicId(), $oTopicRead->getUserId()
        );
        return $bResult !== false;
    }

    /**
     * Устанавливаем дату прочтения топика
     *
     * @param ModuleTopic_EntityTopicRead $oTopicRead    Объект факта чтения топика
     *
     * @return bool
     */
    public function AddTopicRead(ModuleTopic_EntityTopicRead $oTopicRead) {

        $sql = "
            INSERT INTO ?_topic_read
            (
                comment_count_last,
                comment_id_last,
                date_read,
                topic_id,
                user_id
            )
            VALUES (
                ? ,
                ? ,
                ? ,
                ? ,
                ?
            )
        ";
        return $this->oDb->query(
            $sql, $oTopicRead->getCommentCountLast(), $oTopicRead->getCommentIdLast(), $oTopicRead->getDateRead(),
            $oTopicRead->getTopicId(), $oTopicRead->getUserId()
        ) !== false;
    }

    /**
     * Удаляет записи о чтении записей по списку идентификаторов
     *
     * @param   array $aIds   - Список ID топиков
     *
     * @return  bool
     */
    public function DeleteTopicReadByArrayId($aIds) {
        $sql
            = "
			DELETE FROM ?_topic_read
			WHERE topic_id IN(?a)
		";
        return ($this->oDb->query($sql, $aIds) !== false);
    }

    /**
     * Получить список просмотром/чтения топиков по списку айдишников
     *
     * @param array $aTopicId    Список ID топиков
     * @param int   $iUserId     ID пользователя
     *
     * @return array
     */
    public function GetTopicsReadByArray($aTopicId, $iUserId) {

        if (!is_array($aTopicId) || count($aTopicId) == 0) {
            return array();
        }

        $sql
            = "SELECT
					t.*
				FROM 
					?_topic_read as t
				WHERE 
					t.topic_id IN(?a)
					AND
					t.user_id = ?d 
				";
        $aReads = array();
        if ($aRows = $this->oDb->select($sql, $aTopicId, $iUserId)) {
            $aReads = Engine::GetEntityRows('Topic_TopicRead', $aRows);
        }
        return $aReads;
    }

    /**
     * Добавляет факт голосования за топик-вопрос
     *
     * @param ModuleTopic_EntityTopicQuestionVote $oTopicQuestionVote    Объект голосования в топике-опросе
     *
     * @return bool
     */
    public function AddTopicQuestionVote(ModuleTopic_EntityTopicQuestionVote $oTopicQuestionVote) {

        $sql
            = "
            INSERT INTO ?_topic_question_vote
                (topic_id, user_voter_id, answer)
			VALUES(?d, ?d, ?f)
		";
        $bResult = $this->oDb->query(
            $sql, $oTopicQuestionVote->getTopicId(), $oTopicQuestionVote->getVoterId(), $oTopicQuestionVote->getAnswer()
        );
        return $bResult !== false;
    }

    /**
     * Получить список голосований в топике-опросе по списку айдишников
     *
     * @param array $aTopicId    Список ID топиков
     * @param int   $iUserId     ID пользователя
     *
     * @return array
     */
    public function GetTopicsQuestionVoteByArray($aTopicId, $iUserId) {

        if (!is_array($aTopicId) || count($aTopicId) == 0) {
            return array();
        }

        $sql
            = "SELECT
					v.*
				FROM 
					?_topic_question_vote as v
				WHERE 
					v.topic_id IN(?a)
					AND
					v.user_voter_id = ?d
				";
        $aVotes = array();
        if ($aRows = $this->oDb->select($sql, $aTopicId, $iUserId)) {
            $aVotes = Engine::GetEntityRows('Topic_TopicQuestionVote', $aRows);
        }
        return $aVotes;
    }

    /**
     * Перемещает топики в другой блог
     *
     * @param  array $aTopics       Список ID топиков
     * @param  int   $nBlogIdNew    ID блога
     *
     * @return bool
     */
    public function MoveTopicsByArrayId($aTopics, $nBlogIdNew) {

        if (!is_array($aTopics)) {
            $aTopics = array($aTopics);
        }

        return $this->MoveTopicsByFilter($nBlogIdNew, array('topic_id' => $aTopics));
    }

    /**
     * Перемещает топики в другой блог
     *
     * @param  int $nBlogIdOld       ID старого блога
     * @param  int $nBlogIdNew       ID нового блога
     *
     * @return bool
     */
    public function MoveTopics($nBlogIdOld, $nBlogIdNew) {

        return $this->MoveTopicsByFilter($nBlogIdNew, array('blog_id' => $nBlogIdOld));
    }

    /**
     * Перемещает топики в другой блог
     *
     * @param $nBlogIdNew
     * @param $aFilter
     *
     * @return bool
     */
    public function MoveTopicsByFilter($nBlogIdNew, $aFilter) {

        if (!isset($aFilter['blog_id']) && !isset($aFilter['topic_id'])) {
            return false;
        }

        if (isset($aFilter['blog_id']) && !is_array($aFilter['blog_id'])) {
            $aFilter['blog_id'] = array($aFilter['blog_id']);
        }

        if (isset($aFilter['topic_id']) && !is_array($aFilter['topic_id'])) {
            $aFilter['topic_id'] = array($aFilter['topic_id']);
        }

        $oBlogType = $this->Blog_GetBlogTypeById($nBlogIdNew);
        if ($oBlogType) {
            $nIndexIgnore = $oBlogType->getIndexIgnore();
        } else {
            $nIndexIgnore = 0;
        }
        $sql
            = "UPDATE ?_topic
                SET
                    blog_id = ?d,
                    topic_index_ignore = CASE WHEN topic_index_ignore = ?d THEN topic_index_ignore ELSE ?d END
                WHERE
                    1 = 1
                    { AND (blog_id IN (?a)) }
                    { AND (topic_id IN(?a)) }
                ";
        $bResult = $this->oDb->query(
            $sql,
            $nBlogIdNew,
            ModuleTopic_EntityTopic::INDEX_IGNORE_LOCK,
            $nIndexIgnore,
            isset($aFilter['blog_id']) ? $aFilter['blog_id'] : DBSIMPLE_SKIP,
            isset($aFilter['topic_id']) ? $aFilter['topic_id'] : DBSIMPLE_SKIP
        );
        return $bResult !== false;
    }

    /**
     * Перемещает теги топиков в другой блог
     *
     * @param int $iBlogId       ID старого блога
     * @param int $iBlogIdNew    ID нового блога
     *
     * @return bool
     */
    public function MoveTopicsTags($iBlogId, $iBlogIdNew) {

        $sql = "UPDATE ?_topic_tag
			SET 
				blog_id= ?d
			WHERE
				blog_id = ?d
		";
        $bResult = $this->oDb->query($sql, $iBlogIdNew, $iBlogId);
        return $bResult !== false;
    }

    /**
     * Перемещает теги топиков в другой блог
     *
     * @param array $aTopics    Список ID топиков
     * @param int   $iBlogId    ID блога
     *
     * @return bool
     */
    public function MoveTopicsTagsByArrayId($aTopics, $iBlogId) {

        if (!is_array($aTopics)) {
            $aTopics = array($aTopics);
        }

        $sql = "UPDATE ?_topic_tag
			SET 
				blog_id= ?d
			WHERE
				topic_id IN(?a)
		";
        $bResult = $this->oDb->query($sql, $iBlogId, $aTopics);
        return $bResult !== false;
    }

    /**
     * Возвращает список фотографий к топику-фотосет по списку id фоток
     *
     * @param array $aPhotoId    Список ID фото
     *
     * @return array
     */
    public function GetTopicPhotosByArrayId($aPhotoId) {

        if (!is_array($aPhotoId) || count($aPhotoId) == 0) {
            return array();
        }
        $nLimit = sizeof($aPhotoId);
        $sql
            = "SELECT
					*
				FROM 
					?_topic_photo
				WHERE 
					id IN(?a)
				ORDER BY FIELD(id,?a)
				LIMIT $nLimit
				";
        $aResult = array();
        if ($aRows = $this->oDb->select($sql, $aPhotoId, $aPhotoId)) {
            $aResult = Engine::GetEntityRows('Topic_TopicPhoto', $aRows);
        }
        return $aResult;
    }

    /**
     * Получить список изображений из фотосета по id топика
     *
     * @param int|array $aTopicId - ID топика или массив ID топиков
     * @param int       $iFromId  - ID с которого начинать выборку
     * @param int       $iCount   - Количество
     *
     * @return array
     */
    public function getPhotosByTopicId($aTopicId, $iFromId, $iCount) {

        $sql = "
            SELECT *
            FROM ?_topic_photo
            WHERE
                1=1
                {AND topic_id = ?d}
                {AND topic_id IN (?a)}
                {AND id >= ?d}
            ORDER BY id
            {LIMIT 0, ?d}
            ";
        $aRows = $this->oDb->select($sql,
            (!is_array($aTopicId)) ? $aTopicId : DBSIMPLE_SKIP,
            (is_array($aTopicId)) ? $aTopicId : DBSIMPLE_SKIP,
            ($iFromId !== null) ? $iFromId : DBSIMPLE_SKIP,
            $iCount ? $iCount : DBSIMPLE_SKIP);
        $aResult = array();
        if ($aRows) {
            $aResult = Engine::GetEntityRows('Topic_TopicPhoto', $aRows);
        }
        return $aResult;
    }

    /**
     * Получить список изображений из фотосета по временному коду
     *
     * @param string $sTargetTmp    Временный ключ
     *
     * @return array
     */
    public function getPhotosByTargetTmp($sTargetTmp) {

        $sql = "SELECT * FROM ?_topic_photo WHERE target_tmp = ?";
        $aRows = $this->oDb->select($sql, $sTargetTmp);
        $aResult = array();
        if ($aRows) {
            $aResult = Engine::GetEntityRows('Topic_TopicPhoto', $aRows);
        }
        return $aResult;
    }

    /**
     * Получить изображение из фотосета по его id
     *
     * @param int $iPhotoId    ID фото
     *
     * @return ModuleTopic_EntityTopicPhoto|null
     */
    public function getTopicPhotoById($iPhotoId) {

        $sql = "SELECT * FROM ?_topic_photo WHERE id = ?d LIMIT 1";
        $aRow = $this->oDb->selectRow($sql, $iPhotoId);
        if ($aRow) {
            return Engine::GetEntity('Topic_TopicPhoto', $aRow);
        } else {
            return null;
        }
    }

    /**
     * Получить число изображений из фотосета по id топика
     *
     * @param int $iTopicId    ID топика
     *
     * @return int
     */
    public function getCountPhotosByTopicId($iTopicId) {

        $sql = "SELECT COUNT(id) FROM ?_topic_photo WHERE topic_id = ?d";
        return $this->oDb->selectCell($sql, $iTopicId);
    }

    /**
     * Получить число изображений из фотосета по id топика
     *
     * @param string $sTargetTmp    Временный ключ
     *
     * @return int
     */
    public function getCountPhotosByTargetTmp($sTargetTmp) {

        $sql = "SELECT count(id) FROM ?_topic_photo WHERE target_tmp = ?";
        return $this->oDb->selectCell($sql, $sTargetTmp);
    }

    /**
     * Добавить к топику изображение
     *
     * @param ModuleTopic_EntityTopicPhoto $oPhoto    Объект фото к топику-фотосету
     *
     * @return bool
     */
    public function addTopicPhoto($oPhoto) {

        if (!$oPhoto->getTopicId() && !$oPhoto->getTargetTmp()) {
            return false;
        }

        if ($iTargetId = $oPhoto->getTopicId()) {
            $sTargetTmp = null;
        } else {
            $sTargetTmp = $oPhoto->getTargetTmp();
        }

        $sql = "
            INSERT INTO ?_topic_photo
            (
                path, description, topic_id, target_tmp
            )
            VALUES (
                ?, ?, ?d, ?
            )
        ";
        $iId = $this->oDb->query($sql, $oPhoto->getPath(), $oPhoto->getDescription(), $iTargetId, $sTargetTmp);
        return $iId ? $iId : false;
    }

    /**
     * Обновить данные по изображению
     *
     * @param ModuleTopic_EntityTopicPhoto $oPhoto Объект фото
     *
     * @return  bool
     */
    public function updateTopicPhoto($oPhoto) {

        if (!$oPhoto->getTopicId() && !$oPhoto->getTargetTmp()) {
            return false;
        }
        if ($oPhoto->getTopicId()) {
            $oPhoto->setTargetTmp = null;
        }
        $sql = 'UPDATE ?_topic_photo SET
                        path = ?, description = ?, topic_id = ?d, target_tmp=? WHERE id = ?d';
        $bResult = $this->oDb->query(
            $sql, $oPhoto->getPath(), $oPhoto->getDescription(), $oPhoto->getTopicId(), $oPhoto->getTargetTmp(),
            $oPhoto->getId()
        );
        return $bResult !== false;
    }

    /**
     * Удалить изображение
     *
     * @param int $iPhotoId - ID фото
     *
     * @return  bool
     */
    public function deleteTopicPhoto($iPhotoId) {

        $sql = "DELETE FROM ?_topic_photo WHERE  id= ?d";
        return $this->oDb->query($sql, $iPhotoId) !== false;
    }

    /**
     * Присоединение фотографий к фотосету топика
     *
     * @param ModuleTopic_EntityTopic $oTopic
     *
     * @return bool
     */
    public function attachTmpPhotoToTopic($oTopic) {

        if ($sTargetTmp = $oTopic->getTargetTmp()) {
            $sql = "
                UPDATE ?_topic_photo SET topic_id=?d, target_tmp=NULL WHERE target_tmp=?
            ";
            return $this->oDb->query($sql, $oTopic->getId(), $sTargetTmp) !== false;
        }
        return true;
    }

    /**
     * Пересчитывает счетчик избранных топиков
     *
     * @return bool
     */
    public function RecalculateFavourite() {

        $sql
            = "
                UPDATE ?_topic t
                SET t.topic_count_favourite = (
                    SELECT count(f.user_id)
                    FROM ?_favourite f
                    WHERE 
                        f.target_id = t.topic_id
                    AND
                        f.target_publish = 1
                    AND
                        f.target_type = 'topic'
                )
            ";
        $bResult = $this->oDb->query($sql);
        return $bResult !== false;
    }

    /**
     * Пересчитывает счетчики голосований
     *
     * @return bool
     */
    public function RecalculateVote() {

        $sql
            = "
                UPDATE ?_topic t
                SET t.topic_count_vote_up = (
                    SELECT count(*)
                    FROM ?_vote v
                    WHERE
                        v.target_id = t.topic_id
                    AND
                        v.target_type = 'topic'
                    AND
                        v.vote_direction = 1
                ), t.topic_count_vote_down = (
                    SELECT count(*)
                    FROM ?_vote v
                    WHERE
                        v.target_id = t.topic_id
                    AND
                        v.target_type = 'topic'
                    AND
                        v.vote_direction = -1
                ), t.topic_count_vote_abstain = (
                    SELECT count(*)
                    FROM ?_vote v
                    WHERE
                        v.target_id = t.topic_id
                    AND
                        v.target_type = 'topic'
                    AND
                        v.vote_direction = 0
                )
            ";
        $bResult = $this->oDb->query($sql);
        return $bResult !== false;
    }

    /**
     * Список типов контента
     *
     * @param  array $aFilter    Фильтр
     * @param  bool  $urls       Возвращать url или полные объекты
     *
     * @return array
     */
    public function getContentTypes($aFilter) {

        $sql
            = "SELECT
						*
					FROM
						?_content
					WHERE
						1=1
						{ AND content_active = ?d }
					ORDER BY content_sort desc
					";
        $aTypes = array();
        if ($aRows = $this->oDb->select(
            $sql,
            (isset($aFilter['content_active']) ? 1 : DBSIMPLE_SKIP)
        )
        ) {
            foreach ($aRows as $aType) {
                $aTypes[$aType['content_url']] = Engine::GetEntity('Topic_ContentType', $aType);
            }
        }
        return $aTypes;
    }


    /**
     * Добавляет тип контента
     *
     * @param ModuleTopic_EntityContentType $oType    Объект типа контента
     *
     * @return int|bool
     */
    public function AddContentType($oType) {

        $sql = "INSERT INTO ?_content
			(content_title,
			content_title_decl,
			content_url,
			content_candelete,
			content_access,
			content_config
			)
			VALUES(?, ?, ?, ?d, ?d, ?)
		";
        if ($iId = $this->oDb->query(
            $sql,
            $oType->getContentTitle(),
            $oType->getContentTitleDecl(),
            $oType->getContentUrl(),
            $oType->getContentCandelete(),
            $oType->getContentAccess(),
            $oType->getExtra()
        )
        ) {
            $oType->setContentId($iId);
            return $iId;
        }
        return false;
    }

    /**
     * Обновляет тип контента
     *
     * @param ModuleTopic_EntityType $oType    Объект типа контента
     *
     * @return bool
     */
    public function UpdateContentType($oType) {

        $sql = "UPDATE ?_content
			SET
				content_title=?,
				content_title_decl=?,
				content_url=?,
				content_sort=?d,
				content_candelete=?d,
				content_active=?d,
				content_access=?d,
				content_config=?
			WHERE
				content_id = ?d
		";
        $bResult = $this->oDb->query(
            $sql,
            $oType->getContentTitle(),
            $oType->getContentTitleDecl(),
            $oType->getContentUrl(),
            $oType->getContentSort(),
            $oType->getContentCandelete(),
            $oType->getContentActive(),
            $oType->getContentAccess(),
            $oType->getExtra(),
            $oType->getContentId()
        );
        return $bResult !== false;
    }

    /**
     * Получает тип контента по ID
     *
     * @param  int $nId
     *
     * @return ModuleTopic_EntityContentType|null
     */
    public function getContentTypeById($nId) {

        $sql
            = "SELECT
						*
					FROM
						?_content
					WHERE
						content_id = ?d
					";
        if ($aRow = $this->oDb->selectRow($sql, $nId)) {
            return Engine::GetEntity('Topic_ContentType', $aRow);
        }
        return null;
    }

    /**
     * Получает тип контента по URL
     *
     * @param  string $sUrl
     *
     * @return ModuleTopic_EntityContentType|null
     */
    public function getContentTypeByUrl($sUrl) {

        $sql
            = "SELECT
						*
					FROM
						?_content
					WHERE
						content_url = ?
					";
        if ($aRow = $this->oDb->selectRow($sql, $sUrl)) {
            return Engine::GetEntity('Topic_ContentType', $aRow);
        }
        return null;
    }

    /**
     * заменить системный тип контента у уже созданных топиков
     *
     * @param string $sTypeOld
     * @param string $sTypeNew
     *
     * @return bool
     */
    public function changeType($sTypeOld, $sTypeNew) {

        $sql = "UPDATE ?_topic
			SET
				topic_type = ?
			WHERE
				topic_type = ?
		";
        $bResult = $this->oDb->query($sql, $sTypeNew, $sTypeOld);
        return $bResult !== false;
    }


    /**
     * Добавляет поле
     *
     * @param ModuleTopic_EntityField $oField    Объект поля
     *
     * @return int|bool
     */
    public function AddContentField(ModuleTopic_EntityField $oField) {

        $sql = "INSERT INTO ?_content_field
			(
			content_id,
			field_name,
			field_type,
			field_description,
			field_options,
			field_required,
			field_postfix
			)
			VALUES(?d, ?, ?, ?, ?, ?d, ?)
		";
        if ($iId = $this->oDb->query(
            $sql,
            $oField->getContentId(),
            $oField->getFieldName(),
            $oField->getFieldType(),
            $oField->getFieldDescription(),
            $oField->getFieldOptions(),
            $oField->getFieldRequired() ? 1 : 0,
            $oField->getFieldPostfix()
        )
        ) {
            $oField->setFieldId($iId);
            return $iId;
        }
        return false;
    }

    /**
     * Обновляет поле
     *
     * @param ModuleTopic_EntityField $oField    Объект поля
     *
     * @return bool
     */
    public function UpdateContentField(ModuleTopic_EntityField $oField) {

        $sql = "UPDATE ?_content_field
			SET
				content_id=?d,
				field_name=?,
				field_sort=?d,
				field_type=?,
				field_description=?,
				field_options=?,
				field_required=?d,
				field_postfix=?
			WHERE
				field_id = ?d
		";
        $bResult = $this->oDb->query(
            $sql,
            $oField->getContentId(),
            $oField->getFieldName(),
            $oField->getFieldSort(),
            $oField->getFieldType(),
            $oField->getFieldDescription(),
            $oField->getFieldOptions(),
            $oField->getFieldRequired() ? 1 : 0,
            $oField->getFieldPostfix(),
            $oField->getFieldId()
        );
        return $bResult !== false;
    }


    /**
     * Список полей типа контента
     *
     * @param  array $aFilter    Фильтр
     *
     * @return array
     */
    public function getContentFields($aFilter) {

        $sql
            = "SELECT
						*
					FROM
						?_content_field
					WHERE
						1=1
						{ AND content_id = ?d }
					ORDER BY field_sort desc
					";
        $aFields = array();
        $aRows = $this->oDb->select($sql, (isset($aFilter['content_id']) ? $aFilter['content_id'] : DBSIMPLE_SKIP));
        if ($aRows) {
            foreach ($aRows as $aField) {
                $aFields[] = Engine::GetEntity('Topic_Field', $aField);
            }
        }
        return $aFields;
    }

    /**
     * Возвращает список полей по списку id типов контента
     *
     * @param array $aContentId    Список ID типов контента
     *
     * @return array
     * @TODO рефакторинг + solid
     */
    public function GetFieldsByArrayId($aContentId) {

        if (!is_array($aContentId) || count($aContentId) == 0) {
            return array();
        }

        $sql
            = "SELECT
					*
				FROM
					?_content_field
				WHERE
					content_id IN(?a)
				";
        $aFields = array();
        if ($aRows = $this->oDb->select($sql, $aContentId)) {
            $aFields = Engine::GetEntityRows('Topic_Field', $aRows);
        }
        return $aFields;
    }

    /**
     * Получает тип контента по id
     *
     * @param  int $nId
     *
     * @return ModuleTopic_EntityField|null
     */
    public function getContentFieldById($nId) {

        $sql
            = "SELECT
						*
					FROM
						?_content_field
					WHERE
						field_id = ?d
					";
        if ($aRow = $this->oDb->selectRow($sql, $nId)) {
            return Engine::GetEntity('Topic_Field', $aRow);
        }
        return null;
    }

    /**
     * Удаляет поле
     *
     * @param int $oField
     *
     * @return bool
     */
    public function DeleteField($oField) {

        $sql = "DELETE FROM ?_content_field
			WHERE
				field_id = ?d
		";
        return $this->oDb->query($sql, $oField->getFieldId()) !== false;
    }


    /**
     * Удаляет значения полей у топика
     *
     * @param int $iTopicId    ID топика
     *
     * @return bool
     */
    public function DeleteTopicValuesByTopicId($iTopicId) {

        $sql = "DELETE FROM ?_content_values
			WHERE
				target_id = ?d
				AND
				target_type = 'topic'
		";
        return $this->oDb->query($sql, $iTopicId) !== false;
    }

    /**
     * Добавление поля к топику
     *
     * @param ModuleTopic_EntityContentValues $oValue    Объект поля топика
     *
     * @return int
     */
    public function AddTopicValue(ModuleTopic_EntityContentValues $oValue) {

        $sql = "INSERT INTO ?_content_values
			(target_id,
			target_type,
			field_id,
			field_type,
			value,
			value_varchar,
			value_source
			)
			VALUES(?d, ?, ?d, ?, ?, ?, ?)
		";
        $nId = $this->oDb->query(
            $sql,
            $oValue->getTargetId(),
            $oValue->getTargetType(),
            $oValue->getFieldId(),
            $oValue->getFieldType(),
            $oValue->getValue(),
            $oValue->getValueVarchar(),
            $oValue->getValueSource()
        );
        return $nId ? $nId : false;
    }

    /**
     * Обновляет значение поля
     *
     * @param ModuleTopic_EntityContentValues $oValue    Объект значения поля
     *
     * @return bool
     */
    public function UpdateContentFieldValue(ModuleTopic_EntityContentValues $oValue) {

        $sql = "UPDATE ?_content_values
			SET
				value=?,
				value_varchar=?,
				value_source=?
			WHERE
				id = ?d
		";
        $bResult = $this->oDb->query(
            $sql,
            $oValue->getValue(),
            $oValue->getValueVarchar(),
            $oValue->getValueSource(),
            $oValue->getId()
        );
        return $bResult !== false;
    }

    /**
     * Возвращает список полей по списку id топиков
     *
     * @param array $aTargetId    Список ID топиков
     *
     * @return array
     * @TODO рефакторинг + solid
     */
    public function GetTopicValuesByArrayId($aTargetId) {

        if (!is_array($aTargetId) || count($aTargetId) == 0) {
            return array();
        }

        $sql
            = "SELECT
					*
				FROM
					?_content_values
				WHERE
					target_id IN(?a)
					AND
					target_type = 'topic'
				";
        $aFields = array();
        if ($aRows = $this->oDb->select($sql, $aTargetId)) {
            $aFields = Engine::GetEntityRows('Topic_ContentValues', $aRows);
        }
        return $aFields;
    }

}

// EOF