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
 * @package modules.favourite
 * @since   1.0
 */
class ModuleFavourite_MapperFavourite extends Mapper {
    /**
     * Добавляет таргет в избранное
     *
     * @param  ModuleFavourite_EntityFavourite $oFavourite Объект избранного
     *
     * @return bool
     */
    public function AddFavourite(ModuleFavourite_EntityFavourite $oFavourite) {

        $sql = "
			INSERT INTO ?_favourite
				( target_id, target_type, user_id, tags )
			VALUES
				(?d, ?, ?d, ?)
		";
        $bResult = $this->oDb->query(
            $sql,
            $oFavourite->getTargetId(),
            $oFavourite->getTargetType(),
            $oFavourite->getUserId(),
            $oFavourite->getTags()
        );
        return $bResult !== false;
    }

    /**
     * Обновляет запись об избранном
     *
     * @param ModuleFavourite_EntityFavourite $oFavourite    Объект избранного
     *
     * @return bool
     */
    public function UpdateFavourite(ModuleFavourite_EntityFavourite $oFavourite) {

        $sql = "
			UPDATE ?_favourite
				SET tags = ? WHERE user_id = ?d and target_id = ?d and target_type = ?
		";
        $bResult = $this->oDb->query(
            $sql,
            $oFavourite->getTags(),
            $oFavourite->getUserId(),
            $oFavourite->getTargetId(),
            $oFavourite->getTargetType()
        );
        return $bResult !== false;
    }

    /**
     * Получить список избранного по списку айдишников
     *
     * @param  array  $aTargetId       Список ID владельцев
     * @param  string $sTargetType    Тип владельца
     * @param  int    $sUserId        ID пользователя
     *
     * @return ModuleFavourite_EntityFavourite[]
     */
    public function GetFavouritesByArray($aTargetId, $sTargetType, $sUserId) {

        if (!is_array($aTargetId) || count($aTargetId) == 0) {
            return array();
        }
        $sql = "SELECT *
				FROM ?_favourite
				WHERE
					user_id = ?d
					AND
					target_id IN(?a)
					AND
					target_type = ? ";
        $aFavourites = array();
        if ($aRows = $this->oDb->select($sql, $sUserId, $aTargetId, $sTargetType)) {
            $aFavourites = E::GetEntityRows('Favourite', $aRows);
        }
        return $aFavourites;
    }

    /**
     * Удаляет таргет из избранного
     *
     * @param  ModuleFavourite_EntityFavourite $oFavourite    Объект избранного
     *
     * @return bool
     */
    public function DeleteFavourite(ModuleFavourite_EntityFavourite $oFavourite) {

        $sql = "
			DELETE FROM ?_favourite
			WHERE
				user_id = ?d
			AND
				target_id = ?d
			AND 
				target_type = ?
		";
        $bResult = $this->oDb->query(
            $sql,
            $oFavourite->getUserId(),
            $oFavourite->getTargetId(),
            $oFavourite->getTargetType()
        );
        return $bResult !== false;
    }

    /**
     * Удаляет теги
     *
     * @param ModuleFavourite_EntityFavourite $oFavourite    Объект избранного
     *
     * @return bool
     */
    public function DeleteTags($oFavourite) {

        $sql = "
			DELETE FROM ?_favourite_tag
			WHERE
				user_id = ?d
				AND
				target_type = ?
				AND
				target_id = ?d
		";
        $bResult = $this->oDb->query(
            $sql,
            $oFavourite->getUserId(),
            $oFavourite->getTargetType(),
            $oFavourite->getTargetId()
        );
        return $bResult !== false;
    }

    /**
     * Добавляет тег
     *
     * @param ModuleFavourite_EntityTag $oTag    Объект тега
     *
     * @return bool
     */
    public function AddTag($oTag) {

        $sql = "
          INSERT INTO ?_favourite_tag
          (
              target_id, target_type, user_id, is_user, text
          )
          VALUES (
              ?d, ?, ?d, ?d, ?
          )
        ";
        $bResult = $this->oDb->query(
            $sql,
            $oTag->getTargetId(),
            $oTag->getTargetType(),
            $oTag->getUserId(),
            $oTag->getIsUser(),
            $oTag->getText()
        );
        return $bResult !== false;
    }

    /**
     * Меняет параметры публикации у таргета
     *
     * @param  array|int $aTargetId      Список ID владельцев
     * @param  string    $sTargetType    Тип владельца
     * @param  int       $iPublish       Флаг публикации
     *
     * @return bool
     */
    public function SetFavouriteTargetPublish($aTargetId, $sTargetType, $iPublish) {

        $sql = "
			UPDATE ?_favourite
			SET 
				target_publish = ?d
			WHERE
				target_id IN(?a)
			AND
				target_type = ?
		";
        $bResult = $this->oDb->query($sql, $iPublish, $aTargetId, $sTargetType);
        return $bResult !== false;
    }

    /**
     * Получает список таргетов из избранного
     *
     * @param  int    $iUserId           ID пользователя
     * @param  string $sTargetType       Тип владельца
     * @param  int    $iCount            Возвращает количество элементов
     * @param  int    $iCurrPage         Номер страницы
     * @param  int    $iPerPage          Количество элементов на страницу
     * @param  array  $aExcludeTarget    Список ID владельцев для исклчения
     *
     * @return array
     */
    public function GetFavouritesByUserId($iUserId, $sTargetType, &$iCount, $iCurrPage, $iPerPage, $aExcludeTarget = array()) {

        $sql = "
			SELECT target_id
			FROM ?_favourite
			WHERE 
					user_id = ?
				AND
					target_publish = 1
				AND
					target_type = ? 
				{ AND target_id NOT IN (?a) }
            ORDER BY target_id DESC
            LIMIT ?d, ?d ";

        $aFavourites = array();
        $aRows = $this->oDb->selectPage(
            $iCount,
            $sql,
            $iUserId,
            $sTargetType,
            (count($aExcludeTarget) ? $aExcludeTarget : DBSIMPLE_SKIP),
            ($iCurrPage - 1) * $iPerPage,
            $iPerPage
        );
        if ($aRows) {
            foreach ($aRows as $aFavourite) {
                $aFavourites[] = $aFavourite['target_id'];
            }
        }
        return $aFavourites;
    }

    /**
     * Возвращает число таргетов определенного типа в избранном по ID пользователя
     *
     * @param  int    $iUserId           ID пользователя
     * @param  string $sTargetType       Тип владельца
     * @param  array  $aExcludeTarget    Список ID владельцев для исклчения
     *
     * @return array
     */
    public function GetCountFavouritesByUserId($iUserId, $sTargetType, $aExcludeTarget) {

        $sql = "SELECT
					COUNT(target_id) as cnt
				FROM 
					?_favourite
				WHERE 
						user_id = ?
					AND
						target_publish = 1
					AND
						target_type = ?
					{ AND target_id NOT IN (?a) }
					;";
        $aRow = $this->oDb->selectRow(
            $sql, $iUserId,
            $sTargetType,
            (count($aExcludeTarget) ? $aExcludeTarget : DBSIMPLE_SKIP)
        );
        return $aRow ? $aRow['cnt'] : false;
    }

    /**
     * Получает список комментариев к записям открытых блогов
     * из избранного указанного пользователя
     *
     * @param  int $iUserId      ID пользователя
     * @param  int $iCount       Возвращает количество элементов
     * @param  int $iCurrPage    Номер страницы
     * @param  int $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetFavouriteOpenCommentsByUserId($iUserId, &$iCount, $iCurrPage, $iPerPage) {

        $aOpenBlogTypes = E::ModuleBlog()->GetOpenBlogTypes();
        $sql = "
			SELECT f.target_id
			FROM 
				?_favourite AS f,
				?_comment AS c,
				?_topic AS t,
				?_blog AS b
			WHERE 
					f.user_id = ?d
				AND
					f.target_publish = 1
				AND
					f.target_type = 'comment'
				AND
					f.target_id = c.comment_id
				AND 
					c.target_id = t.topic_id
				AND 
					t.blog_id = b.blog_id
				AND 
					b.blog_type IN (?a)
            ORDER BY target_id DESC
            LIMIT ?d, ?d ";

        $aFavourites = array();
        $aRows = $this->oDb->selectPage($iCount, $sql,
            $iUserId,
            $aOpenBlogTypes,
            ($iCurrPage - 1) * $iPerPage,
            $iPerPage
        );
        if ($aRows) {
            foreach ($aRows as $aFavourite) {
                $aFavourites[] = $aFavourite['target_id'];
            }
        }
        return $aFavourites;
    }

    /**
     * Возвращает число комментариев к открытым блогам в избранном по ID пользователя
     *
     * @param  int $sUserId    ID пользователя
     *
     * @return array
     */
    public function GetCountFavouriteOpenCommentsByUserId($sUserId) {

        $aOpenBlogTypes = E::ModuleBlog()->GetOpenBlogTypes();
        $sql = "SELECT
					COUNT(f.target_id) as cnt
				FROM 
					?_favourite AS f,
					?_comment AS c,
					?_topic AS t,
					?_blog AS b
				WHERE 
						f.user_id = ?d
					AND
						f.target_publish = 1
					AND
						f.target_type = 'comment'
					AND
						f.target_id = c.comment_id
					AND 
						c.target_id = t.topic_id
					AND 
						t.blog_id = b.blog_id
					AND 
						b.blog_type IN (?a)
					;";
        $aRow = $this->oDb->selectRow($sql,
            $sUserId,
            $aOpenBlogTypes
        );
        return $aRow ? $aRow['cnt'] : false;
    }

    /**
     * Получает список топиков из открытых блогов
     * из избранного указанного пользователя
     *
     * @param  int $iUserId      ID пользователя
     * @param  int $iCount       Возвращает количество элементов
     * @param  int $iCurrPage    Номер страницы
     * @param  int $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetFavouriteOpenTopicsByUserId($iUserId, &$iCount, $iCurrPage, $iPerPage) {

        $aOpenBlogTypes = E::ModuleBlog()->GetOpenBlogTypes();
        $sql = "
			SELECT f.target_id
			FROM 
				?_favourite AS f,
				?_topic AS t,
				?_blog AS b
			WHERE 
					f.user_id = ?d
				AND
					f.target_publish = 1
				AND
					f.target_type = 'topic'
				AND
					f.target_id = t.topic_id
				AND 
					t.blog_id = b.blog_id
				AND 
					b.blog_type IN (?a)
            ORDER BY target_id DESC
            LIMIT ?d, ?d ";

        $aFavourites = array();
        $aRows = $this->oDb->selectPage($iCount, $sql,
            $iUserId,
            $aOpenBlogTypes,
            ($iCurrPage - 1) * $iPerPage,
            $iPerPage
        );
        if ($aRows) {
            foreach ($aRows as $aFavourite) {
                $aFavourites[] = $aFavourite['target_id'];
            }
        }
        return $aFavourites;
    }

    /**
     * Возвращает число топиков в открытых блогах из избранного по ID пользователя
     *
     * @param  string $iUserId    ID пользователя
     *
     * @return array
     */
    public function GetCountFavouriteOpenTopicsByUserId($iUserId) {

        $aOpenBlogTypes = E::ModuleBlog()->GetOpenBlogTypes();
        $sql = "SELECT
					COUNT(f.target_id) as cnt
				FROM 
					?_favourite AS f,
					?_topic AS t,
					?_blog AS b
				WHERE 
						f.user_id = ?d
					AND
						f.target_publish = 1
					AND
						f.target_type = 'topic'
					AND
						f.target_id = t.topic_id
					AND 
						t.blog_id = b.blog_id
					AND 
						b.blog_type IN (?a)
					;";
        $aRow = $this->oDb->selectRow($sql,
            $iUserId,
            $aOpenBlogTypes
        );
        return ($aRow ? $aRow['cnt'] : false);
    }

    /**
     * Удаляет избранное по списку идентификаторов таргетов
     *
     * @param  array|int $aTargetsId     Список ID владельцев
     * @param  string    $sTargetType    Тип владельца
     *
     * @return bool
     */
    public function DeleteFavouriteByTargetId($aTargetsId, $sTargetType) {

        $aTargetsId = $this->_arrayId($aTargetsId);
        $sql = "
			DELETE FROM ?_favourite
			WHERE 
				target_id IN(?a) 
				AND 
				target_type = ? ";
        return ($this->oDb->query($sql, $aTargetsId, $sTargetType) !== false);
    }

    /**
     * Удаление тегов по таргету
     *
     * @param   array   $aTargetsId     - Список ID владельцев
     * @param   string  $sTargetType    - Тип владельца
     *
     * @return  bool
     */
    public function DeleteTagByTarget($aTargetsId, $sTargetType) {

        $aTargetsId = $this->_arrayId($aTargetsId);
        $sql = "
			DELETE FROM ?_favourite_tag
			WHERE
				target_type = ?
				AND
				target_id IN(?a)
				";
        return ($this->oDb->query($sql, $sTargetType, $aTargetsId) !== false);
    }

    /**
     * Возвращает наиболее часто используемые теги
     *
     * @param int    $iUserId        ID пользователя
     * @param string $sTargetType    Тип владельца
     * @param bool   $bIsUser        Возвращает все теги или только пользовательские
     * @param int    $iLimit         Количество элементов
     *
     * @return array
     */
    public function GetGroupTags($iUserId, $sTargetType, $bIsUser, $iLimit) {

        $sql = "SELECT
			text,
			COUNT(text) AS count
			FROM
				?_favourite_tag
			WHERE
				1=1
				{AND user_id = ?d }
				{AND target_type = ? }
				{AND is_user = ?d }
			GROUP BY
				text
			ORDER BY
				count DESC
			LIMIT 0, ?d
				";

        $aResult = array();
        $aRows = $this->oDb->select(
            $sql, $iUserId, $sTargetType, is_null($bIsUser) ? DBSIMPLE_SKIP : $bIsUser, $iLimit
        );
        if ($aRows) {
            $aData = array();
            foreach ($aRows as $aRow) {
                $aData[mb_strtolower($aRow['text'], 'UTF-8')] = $aRow;
            }
            ksort($aData);
            $aResult = E::GetEntityRows('ModuleFavourite_EntityTag', $aData);
        }
        return $aResult;
    }

    /**
     * Возвращает список тегов по фильтру
     *
     * @param array $aFilter      Фильтр
     * @param array $aOrder       Сортировка
     * @param int   $iCount       Возвращает количество элементов
     * @param int   $iCurrPage    Номер страницы
     * @param int   $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetTags($aFilter, $aOrder, &$iCount, $iCurrPage, $iPerPage) {

        $aOrderAllow = array('target_id', 'user_id', 'is_user');
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
            $sOrder = ' target_id DESC ';
        }

        $sql = "SELECT
					*
				FROM
					?_favourite_tag
				WHERE
					1 = 1
					{ AND user_id = ?d }
					{ AND target_type = ? }
					{ AND target_id = ?d }
					{ AND is_user = ?d }
					{ AND text = ? }
				ORDER by {$sOrder}
				LIMIT ?d, ?d ;
					";
        $aResult = array();
        $aRows = $this->oDb->selectPage(
            $iCount, $sql,
            isset($aFilter['user_id']) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
            isset($aFilter['target_type']) ? $aFilter['target_type'] : DBSIMPLE_SKIP,
            isset($aFilter['target_id']) ? $aFilter['target_id'] : DBSIMPLE_SKIP,
            isset($aFilter['is_user']) ? $aFilter['is_user'] : DBSIMPLE_SKIP,
            isset($aFilter['text']) ? $aFilter['text'] : DBSIMPLE_SKIP,
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );
        if ($aRows) {
            $aResult = E::GetEntityRows('ModuleFavourite_EntityTag', $aRows);
        }
        return $aResult;
    }
}

// EOF