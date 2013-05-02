<?php
/*-------------------------------------------------------
*
*   LiveStreet Engine Social Networking
*   Copyright © 2008 Mzhelskiy Maxim
*
*--------------------------------------------------------
*
*   Official site: www.livestreet.ru
*   Contact e-mail: rus.engine@gmail.com
*
*   GNU General Public License, version 2:
*   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
---------------------------------------------------------
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
        $sql = "INSERT INTO " . Config::Get('db.table.topic') . "
			(blog_id,
			user_id,
			topic_type,
			topic_title,
			topic_tags,
			topic_date_add,
			topic_user_ip,
			topic_publish,
			topic_publish_draft,
			topic_publish_index,
			topic_cut_text,
			topic_forbid_comment,
			topic_text_hash,
			topic_url
			)
			VALUES(?d, ?d, ?, ?, ?, ?, ?, ?d, ?d, ?d, ?, ?, ?, ?)
		";
        $nId = $this->oDb->query(
            $sql, $oTopic->getBlogId(), $oTopic->getUserId(), $oTopic->getType(), $oTopic->getTitle(),
            $oTopic->getTags(), $oTopic->getDateAdd(), $oTopic->getUserIp(), $oTopic->getPublish(),
            $oTopic->getPublishDraft(), $oTopic->getPublishIndex(), $oTopic->getCutText(), $oTopic->getForbidComment(),
            $oTopic->getTextHash(), $oTopic->getTopicUrl()
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
        $sql = "INSERT INTO " . Config::Get('db.table.topic_content') . "
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
        $sql = "INSERT INTO " . Config::Get('db.table.topic_tag') . "
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
     * @param   int|array   $aIds   - ID топика или массив ID
     *
     * @return  bool
     */
    public function DeleteTopicContentByTopicId($aIds) {
        $sql = "
            DELETE FROM " . Config::Get('db.table.topic_content') . "
            WHERE topic_id IN (?a) ";
        return ($this->oDb->query($sql, $aIds) !== false);
    }

    /**
     * Удаляет теги у топика
     *
     * @param   int|array   $aIds   - ID топика или массив ID
     *
     * @return  bool
     */
    public function DeleteTopicTagsByTopicId($aIds) {
        $aIds = $this->_arrayId($aIds);
        $sql = "
            DELETE FROM " . Config::Get('db.table.topic_tag') . "
            WHERE topic_id IN (?a)
        ";
        return ($this->oDb->query($sql, $aIds) !== false);
    }

    /**
     * Удаляет топик(и)
     * Если тип таблиц в БД InnoDB, то удалятся всё связи по топику (комменты, голосования, избранное)
     *
     * @param   int|array   $aIds   - ID топика или массив ID
     *
     * @return  bool
     */
    public function DeleteTopic($aIds) {
        $aIds = $this->_arrayId($aIds);
        $sql = "
            DELETE FROM " . Config::Get('db.table.topic') . "
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
        $sql = "SELECT topic_id FROM " . Config::Get('db.table.topic') . "
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
     * @param string    $sUrl
     *
     * @return int
     */
    public function GetTopicIdByUrl($sUrl) {
        $sql = "
            SELECT topic_id FROM " . Config::Get('db.table.topic') . "
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
        $sql = "
            SELECT topic_id FROM " . Config::Get('db.table.topic') . "
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
     * @param array $aArrayId    Список ID топиков
     *
     * @return array
     */
    public function GetTopicsByArrayId($aArrayId) {
        if (!is_array($aArrayId) || count($aArrayId) == 0) {
            return array();
        }

        $sql = "SELECT
					t.*,
					tc.*
				FROM 
					" . Config::Get('db.table.topic') . " as t
					JOIN  " . Config::Get('db.table.topic_content') . " AS tc ON t.topic_id=tc.topic_id
				WHERE 
					t.topic_id IN(?a)
				ORDER BY FIELD(t.topic_id,?a) ";
        $aTopics = array();
        if ($aRows = $this->oDb->select($sql, $aArrayId, $aArrayId)) {
            foreach ($aRows as $aTopic) {
                $aTopics[] = Engine::GetEntity('Topic', $aTopic);
            }
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
            $aFilter['order'] = 't.topic_date_add desc';
        }
        if (!is_array($aFilter['order'])) {
            $aFilter['order'] = array($aFilter['order']);
        }

        $sql = "
            SELECT
			    t.topic_id
			FROM
			    " . Config::Get('db.table.topic') . " as t,
				" . Config::Get('db.table.blog') . " as b
			WHERE
			    1=1
				" . $sWhere . "
				AND t.blog_id=b.blog_id
			ORDER BY " . implode(', ', $aFilter['order']) . "
			LIMIT ?d, ?d";
        $aTopics = array();
        if ($aRows = $this->oDb->selectPage($iCount, $sql, ($iCurrPage - 1) * $iPerPage, $iPerPage)) {
            foreach ($aRows as $aTopic) {
                $aTopics[] = $aTopic['topic_id'];
            }
        }
        return $aTopics;
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
        $sql = "SELECT
					count(t.topic_id) as count
				FROM 
					" . Config::Get('db.table.topic') . " as t,
					" . Config::Get('db.table.blog') . " as b
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

        $sql = "SELECT
						t.topic_id
					FROM 
						" . Config::Get('db.table.topic') . " as t,
						" . Config::Get('db.table.blog') . " as b
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
     * @param  string   $sTag            Тег
     * @param  array    $aExcludeBlog    Список ID блогов для исключения
     * @param  int      $iCount          Возвращает общее количество элементов
     * @param  int      $iCurrPage       Номер страницы
     * @param  int      $iPerPage        Количество элементов на страницу
     *
     * @return array
     */
    public function GetTopicsByTag($sTag, $aExcludeBlog, &$iCount, $iCurrPage, $iPerPage) {
        $sql = "
            SELECT
			    topic_id
			FROM
			    " . Config::Get('db.table.topic_tag') . "
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
        $sql = "SELECT
						t.topic_id
					FROM 
						" . Config::Get('db.table.topic') . " as t
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
        $sql = "SELECT
			tt.topic_tag_text,
			count(tt.topic_tag_text) as count
			FROM 
				" . Config::Get('db.table.topic_tag') . " as tt
			WHERE 
				1=1
				{AND tt.topic_id NOT IN(?a) }
			GROUP BY 
				tt.topic_tag_text
			ORDER BY 
				count desc
			LIMIT 0, ?d
				";
        $aReturn = array();
        $aReturnSort = array();
        $aRows = $this->oDb->select(
            $sql,
            (is_array($aExcludeTopic) && count($aExcludeTopic)) ? $aExcludeTopic : DBSIMPLE_SKIP,
            $iLimit
        );
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aReturn[mb_strtolower($aRow['topic_tag_text'], 'UTF-8')] = $aRow;
            }
            ksort($aReturn);
            foreach ($aReturn as $aRow) {
                $aReturnSort[] = Engine::GetEntity('Topic_TopicTag', $aRow);
            }
        }
        return $aReturnSort;
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
        $sql = "
			SELECT 
				tt.topic_tag_text,
				count(tt.topic_tag_text) as count
			FROM 
				" . Config::Get('db.table.topic_tag') . " as tt,
				" . Config::Get('db.table.blog') . " as b
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
        $aReturn = array();
        $aReturnSort = array();
        if ($aRows = $this->oDb->select($sql, is_null($iUserId) ? DBSIMPLE_SKIP : $iUserId, $iLimit)) {
            foreach ($aRows as $aRow) {
                $aReturn[mb_strtolower($aRow['topic_tag_text'], 'UTF-8')] = $aRow;
            }
            ksort($aReturn);
            foreach ($aReturn as $aRow) {
                $aReturnSort[] = Engine::GetEntity('Topic_TopicTag', $aRow);
            }
        }
        return $aReturnSort;
    }

    /**
     * Увеличивает у топика число комментов
     *
     * @param int $sTopicId    ID топика
     *
     * @return bool
     */
    public function increaseTopicCountComment($sTopicId) {
        $sql = "UPDATE " . Config::Get('db.table.topic') . "
			SET 
				topic_count_comment=topic_count_comment+1
			WHERE
				topic_id = ?
		";
        $bResult = $this->oDb->query($sql, $sTopicId);
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
        $sql = "UPDATE " . Config::Get('db.table.topic') . "
			SET 
				blog_id = ?d,
				topic_title = ?,
				topic_tags = ?,
				topic_date_add = ?,
				topic_date_edit = ?,
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
            $sql, $oTopic->getBlogId(), $oTopic->getTitle(), $oTopic->getTags(), $oTopic->getDateAdd(),
            $oTopic->getDateEdit(), $oTopic->getUserIp(), $oTopic->getPublish(), $oTopic->getPublishDraft(),
            $oTopic->getPublishIndex(), $oTopic->getRating(), $oTopic->getCountVote(), $oTopic->getCountVoteUp(),
            $oTopic->getCountVoteDown(), $oTopic->getCountVoteAbstain(), $oTopic->getCountRead(),
            $oTopic->getCountComment(), $oTopic->getCountFavourite(), $oTopic->getCutText(),
            $oTopic->getForbidComment(), $oTopic->getTextHash(), $oTopic->getTopicUrl(),
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
        $sql = "UPDATE " . Config::Get('db.table.topic_content') . "
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
            $sWhere .= " AND t.topic_date_add >  " . $this->oDb->escape($aFilter['topic_date_more']);
        }
        if (isset($aFilter['topic_publish'])) {
            $sWhere .= " AND t.topic_publish =  " . (int)$aFilter['topic_publish'];
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
            $sWhere .= " AND t.topic_date_add >=  '" . $aFilter['topic_new'] . "'";
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
            $sWhere .= " AND t.topic_type IN (" . join(",", array_map(array($this->oDb, 'escape'), $aFilter['topic_type'])) . ")";
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
        $sql = "SELECT
				topic_tag_text
			FROM 
				" . Config::Get('db.table.topic_tag') . "
			WHERE
				topic_tag_text LIKE ?
			GROUP BY 
				topic_tag_text
			LIMIT 0, ?d
				";
        $aReturn = array();
        if ($aRows = $this->oDb->select($sql, $sTag . '%', $iLimit)) {
            foreach ($aRows as $aRow) {
                $aReturn[] = Engine::GetEntity('Topic_TopicTag', $aRow);
            }
        }
        return $aReturn;
    }

    /**
     * Обновляем дату прочтения топика
     *
     * @param ModuleTopic_EntityTopicRead $oTopicRead    Объект факта чтения топика
     *
     * @return int
     */
    public function UpdateTopicRead(ModuleTopic_EntityTopicRead $oTopicRead) {
        $sql = "UPDATE " . Config::Get('db.table.topic_read') . "
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
        $sql = "INSERT INTO " . Config::Get('db.table.topic_read') . "
			SET 
				comment_count_last = ? ,
				comment_id_last = ? ,
				date_read = ? ,
				topic_id = ? ,
				user_id = ? 
		";
        return $this->oDb->query(
            $sql, $oTopicRead->getCommentCountLast(), $oTopicRead->getCommentIdLast(), $oTopicRead->getDateRead(),
            $oTopicRead->getTopicId(), $oTopicRead->getUserId()
        ) !== false;
    }

    /**
     * Удаляет записи о чтении записей по списку идентификаторов
     *
     * @param   array   $aIds   - Список ID топиков
     *
     * @return  bool
     */
    public function DeleteTopicReadByArrayId($aIds) {
        $sql = "
			DELETE FROM " . Config::Get('db.table.topic_read') . "
			WHERE topic_id IN(?a)
		";
        return ($this->oDb->query($sql, $aIds) !== false);
    }

    /**
     * Получить список просмотром/чтения топиков по списку айдишников
     *
     * @param array $aArrayId    Список ID топиков
     * @param int   $sUserId     ID пользователя
     *
     * @return array
     */
    public function GetTopicsReadByArray($aArrayId, $sUserId) {
        if (!is_array($aArrayId) || count($aArrayId) == 0) {
            return array();
        }

        $sql = "SELECT
					t.*
				FROM 
					" . Config::Get('db.table.topic_read') . " as t
				WHERE 
					t.topic_id IN(?a)
					AND
					t.user_id = ?d 
				";
        $aReads = array();
        if ($aRows = $this->oDb->select($sql, $aArrayId, $sUserId)) {
            foreach ($aRows as $aRow) {
                $aReads[] = Engine::GetEntity('Topic_TopicRead', $aRow);
            }
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
        $sql = "
            INSERT INTO " . Config::Get('db.table.topic_question_vote') . "
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
     * @param array $aArrayId    Список ID топиков
     * @param int   $sUserId     ID пользователя
     *
     * @return array
     */
    public function GetTopicsQuestionVoteByArray($aArrayId, $sUserId) {
        if (!is_array($aArrayId) || count($aArrayId) == 0) {
            return array();
        }

        $sql = "SELECT
					v.*
				FROM 
					" . Config::Get('db.table.topic_question_vote') . " as v
				WHERE 
					v.topic_id IN(?a)
					AND	
					v.user_voter_id = ?d
				";
        $aVotes = array();
        if ($aRows = $this->oDb->select($sql, $aArrayId, $sUserId)) {
            foreach ($aRows as $aRow) {
                $aVotes[] = Engine::GetEntity('Topic_TopicQuestionVote', $aRow);
            }
        }
        return $aVotes;
    }

    /**
     * Перемещает топики в другой блог
     *
     * @param  array  $aTopics    Список ID топиков
     * @param  int    $sBlogId    ID блога
     *
     * @return bool
     */
    public function MoveTopicsByArrayId($aTopics, $sBlogId) {
        if (!is_array($aTopics)) {
            $aTopics = array($aTopics);
        }

        $sql = "UPDATE " . Config::Get('db.table.topic') . "
			SET 
				blog_id= ?d
			WHERE
				topic_id IN(?a)
		";
        $bResult = $this->oDb->query($sql, $sBlogId, $aTopics);
        return $bResult !== false;
    }

    /**
     * Перемещает топики в другой блог
     *
     * @param  int $sBlogId       ID старого блога
     * @param  int $sBlogIdNew    ID нового блога
     *
     * @return bool
     */
    public function MoveTopics($sBlogId, $sBlogIdNew) {
        $sql = "UPDATE " . Config::Get('db.table.topic') . "
			SET 
				blog_id= ?d
			WHERE
				blog_id = ?d
		";
        $bResult = $this->oDb->query($sql, $sBlogIdNew, $sBlogId);
        return $bResult !== false;
    }

    /**
     * Перемещает теги топиков в другой блог
     *
     * @param int $sBlogId       ID старого блога
     * @param int $sBlogIdNew    ID нового блога
     *
     * @return bool
     */
    public function MoveTopicsTags($sBlogId, $sBlogIdNew) {
        $sql = "UPDATE " . Config::Get('db.table.topic_tag') . "
			SET 
				blog_id= ?d
			WHERE
				blog_id = ?d
		";
        $bResult = $this->oDb->query($sql, $sBlogIdNew, $sBlogId);
        return $bResult !== false;
    }

    /**
     * Перемещает теги топиков в другой блог
     *
     * @param array $aTopics    Список ID топиков
     * @param int   $sBlogId    ID блога
     *
     * @return bool
     */
    public function MoveTopicsTagsByArrayId($aTopics, $sBlogId) {
        if (!is_array($aTopics)) {
            $aTopics = array($aTopics);
        }

        $sql = "UPDATE " . Config::Get('db.table.topic_tag') . "
			SET 
				blog_id= ?d
			WHERE
				topic_id IN(?a)
		";
        $bResult = $this->oDb->query($sql, $sBlogId, $aTopics);
        return $bResult !== false;
    }

    /**
     * Возвращает список фотографий к топику-фотосет по списку id фоток
     *
     * @param array $aPhotoId    Список ID фото
     *
     * @return array
     */
    public function GetTopicPhotosByArrayId($aArrayId) {
        if (!is_array($aArrayId) || count($aArrayId) == 0) {
            return array();
        }

        $sql = "SELECT
					*
				FROM 
					" . Config::Get('db.table.topic_photo') . "
				WHERE 
					id IN(?a)
				ORDER BY FIELD(id,?a) ";
        $aReturn = array();
        if ($aRows = $this->oDb->select($sql, $aArrayId, $aArrayId)) {
            foreach ($aRows as $aPhoto) {
                $aReturn[] = Engine::GetEntity('Topic_TopicPhoto', $aPhoto);
            }
        }
        return $aReturn;
    }

    /**
     * Получить список изображений из фотосета по id топика
     *
     * @param int      $iTopicId    ID топика
     * @param int|null $iFromId     ID с которого начинать выборку
     * @param int|null $iCount      Количество
     *
     * @return array
     */
    public function getPhotosByTopicId($iTopicId, $iFromId, $iCount) {
        $sql
            = 'SELECT * FROM ' . Config::Get('db.table.topic_photo') . ' WHERE topic_id = ?d {AND id > ?d LIMIT 0, ?d}';
        $aPhotos = $this->oDb->select($sql, $iTopicId, ($iFromId !== null) ? $iFromId : DBSIMPLE_SKIP, $iCount);
        $aReturn = array();
        if (is_array($aPhotos) && count($aPhotos)) {
            foreach ($aPhotos as $aPhoto) {
                $aReturn[] = Engine::GetEntity('Topic_TopicPhoto', $aPhoto);
            }
        }
        return $aReturn;
    }

    /**
     * Получить список изображений из фотосета по временному коду
     *
     * @param string $sTargetTmp    Временный ключ
     *
     * @return array
     */
    public function getPhotosByTargetTmp($sTargetTmp) {
        $sql = 'SELECT * FROM ' . Config::Get('db.table.topic_photo') . ' WHERE target_tmp = ?';
        $aPhotos = $this->oDb->select($sql, $sTargetTmp);
        $aReturn = array();
        if (is_array($aPhotos) && count($aPhotos)) {
            foreach ($aPhotos as $aPhoto) {
                $aReturn[] = Engine::GetEntity('Topic_TopicPhoto', $aPhoto);
            }
        }
        return $aReturn;
    }

    /**
     * Получить изображение из фотосета по его id
     *
     * @param int $iPhotoId    ID фото
     *
     * @return ModuleTopic_EntityTopicPhoto|null
     */
    public function getTopicPhotoById($iPhotoId) {
        $sql = 'SELECT * FROM ' . Config::Get('db.table.topic_photo') . ' WHERE id = ?d';
        $aPhoto = $this->oDb->selectRow($sql, $iPhotoId);
        if ($aPhoto) {
            return Engine::GetEntity('Topic_TopicPhoto', $aPhoto);
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
        $sql = 'SELECT count(id) FROM ' . Config::Get('db.table.topic_photo') . ' WHERE topic_id = ?d';
        $aPhotosCount = $this->oDb->selectCol($sql, $iTopicId);
        return $aPhotosCount[0];
    }

    /**
     * Получить число изображений из фотосета по id топика
     *
     * @param string $sTargetTmp    Временный ключ
     *
     * @return int
     */
    public function getCountPhotosByTargetTmp($sTargetTmp) {
        $sql = 'SELECT count(id) FROM ' . Config::Get('db.table.topic_photo') . ' WHERE target_tmp = ?';
        $aPhotosCount = $this->oDb->selectCol($sql, $sTargetTmp);
        return $aPhotosCount[0];
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
        $sTargetType = ($oPhoto->getTopicId()) ? 'topic_id' : 'target_tmp';
        $iTargetId = ($sTargetType == 'topic_id') ? $oPhoto->getTopicId() : $oPhoto->getTargetTmp();
        $sql = 'INSERT INTO ' . Config::Get('db.table.topic_photo') . ' SET
                        path = ?, description = ?, ?# = ?';
        return $this->oDb->query($sql, $oPhoto->getPath(), $oPhoto->getDescription(), $sTargetType, $iTargetId);
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
        $sql = 'UPDATE ' . Config::Get('db.table.topic_photo') . ' SET
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
     * @param int $iPhotoId    ID фото
     */
    public function deleteTopicPhoto($iPhotoId) {
        $sql = "DELETE FROM " . Config::Get('db.table.topic_photo') . " WHERE  id= ?d";
        return $this->oDb->query($sql, $iPhotoId) !== false;
    }

    /**
     * Пересчитывает счетчик избранных топиков
     *
     * @return bool
     */
    public function RecalculateFavourite() {
        $sql = "
                UPDATE " . Config::Get('db.table.topic') . " t
                SET t.topic_count_favourite = (
                    SELECT count(f.user_id)
                    FROM " . Config::Get('db.table.favourite') . " f
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
        $sql = "
                UPDATE " . Config::Get('db.table.topic') . " t
                SET t.topic_count_vote_up = (
                    SELECT count(*)
                    FROM " . Config::Get('db.table.vote') . " v
                    WHERE
                        v.target_id = t.topic_id
                    AND
                        v.vote_direction = 1
                    AND
                        v.target_type = 'topic'
                ), t.topic_count_vote_down = (
                    SELECT count(*)
                    FROM " . Config::Get('db.table.vote') . " v
                    WHERE
                        v.target_id = t.topic_id
                    AND
                        v.vote_direction = -1
                    AND
                        v.target_type = 'topic'
                ), t.topic_count_vote_abstain = (
                    SELECT count(*)
                    FROM " . Config::Get('db.table.vote') . " v
                    WHERE
                        v.target_id = t.topic_id
                    AND
                        v.vote_direction = 0
                    AND
                        v.target_type = 'topic'
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
        $sql = "SELECT
						*
					FROM
						" . Config::Get('db.table.content') . "
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
                $aTypes[$aType['content_url']] = Engine::GetEntity('Topic_Content', $aType);
            }
        }
        return $aTypes;
    }


    /**
     * Добавляет тип контента
     *
     * @param ModuleTopic_EntityContent $oType    Объект типа контента
     *
     * @return int|bool
     */
    public function AddContentType(ModuleTopic_EntityContent $oType) {
        $sql = "INSERT INTO " . Config::Get('db.table.content') . "
			(content_title,
			content_title_decl,
			content_url,
			content_candelete,
			content_access,
			content_config
			)
			VALUES(	?,	?,	?,  ?d, ?d, ?)
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
    public function UpdateContentType(ModuleTopic_EntityContent $oType) {
        $sql = "UPDATE " . Config::Get('db.table.content') . "
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
     * Получает тип контента по id
     *
     * @param  int $nId
     *
     * @return ModuleTopic_EntityContent|null
     */
    public function getContentTypeById($nId) {
        $sql = "SELECT
						*
					FROM
						" . Config::Get('db.table.content') . "
					WHERE
						content_id = ?d
					";
        if ($aRow = $this->oDb->selectRow($sql, $nId)) {
            return Engine::GetEntity('Topic_Content', $aRow);
        }
        return null;
    }

    /**
     * Получает тип контента по url
     *
     * @param  int $nId
     *
     * @return ModuleTopic_EntityContent|null
     */
    public function getContentTypeByUrl($sUrl) {
        $sql = "SELECT
						*
					FROM
						" . Config::Get('db.table.content') . "
					WHERE
						content_url = ?
					";
        if ($aRow = $this->oDb->selectRow($sql, $sUrl)) {
            return Engine::GetEntity('Topic_Content', $aRow);
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
        $sql = "UPDATE " . Config::Get('db.table.topic') . "
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
        $sql = "INSERT INTO " . Config::Get('db.table.content_field') . "
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
        $sql = "UPDATE " . Config::Get('db.table.content_field') . "
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
        $sql = "SELECT
						*
					FROM
						" . Config::Get('db.table.content_field') . "
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
     * @param array $aArrayId    Список ID типов контента
     *
     * @return array
     * @TODO рефакторинг + solid
     */
    public function GetFieldsByArrayId($aArrayId) {
        if (!is_array($aArrayId) || count($aArrayId) == 0) {
            return array();
        }

        $sql = "SELECT
					*
				FROM
					" . Config::Get('db.table.content_field') . "
				WHERE
					content_id IN(?a)
				";
        $aFields = array();
        if ($aRows = $this->oDb->select($sql, $aArrayId)) {
            foreach ($aRows as $aRow) {
                $aFields[] = Engine::GetEntity('Topic_Field', $aRow);
            }
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
        $sql = "SELECT
						*
					FROM
						" . Config::Get('db.table.content_field') . "
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
        $sql = "DELETE FROM " . Config::Get('db.table.content_field') . "
			WHERE
				field_id = ?d
		";
        return $this->oDb->query($sql, $oField->getFieldId()) !== false;
    }


    /**
     * Удаляет значения полей у топика
     *
     * @param int $sTopicId    ID топика
     *
     * @return bool
     */
    public function DeleteTopicValuesByTopicId($sTopicId) {
        $sql = "DELETE FROM " . Config::Get('db.table.content_values') . "
			WHERE
				target_id = ?d
				AND
				target_type = 'topic'
		";
        return $this->oDb->query($sql, $sTopicId) !== false;
    }

    /**
     * Добавление поля к топику
     *
     * @param ModuleTopic_EntityContentValues $oValue    Объект поля топика
     *
     * @return int
     */
    public function AddTopicValue(ModuleTopic_EntityContentValues $oValue) {
        $sql = "INSERT INTO " . Config::Get('db.table.content_values') . "
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
        $sql = "UPDATE " . Config::Get('db.table.content_values') . "
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
     * @param array $aArrayId    Список ID топиков
     *
     * @return array
     * @TODO рефакторинг + solid
     */
    public function GetTopicValuesByArrayId($aArrayId) {
        if (!is_array($aArrayId) || count($aArrayId) == 0) {
            return array();
        }

        $sql = "SELECT
					*
				FROM
					" . Config::Get('db.table.content_values') . "
				WHERE
					target_id IN(?a)
					AND
					target_type = 'topic'
				";
        $aFields = array();
        if ($aRows = $this->oDb->select($sql, $aArrayId)) {
            foreach ($aRows as $aRow) {
                $aFields[] = Engine::GetEntity('Topic_ContentValues', $aRow);
            }
        }
        return $aFields;
    }
}

// EOF