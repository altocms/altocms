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
 * Маппер для работы с БД по части блогов
 *
 * @package modules.blog
 * @since   1.0
 */
class ModuleBlog_MapperBlog extends Mapper {
    /**
     * Добавляет блог в БД
     *
     * @param ModuleBlog_EntityBlog $oBlog    Объект блога
     *
     * @return int|bool
     */
    public function AddBlog(ModuleBlog_EntityBlog $oBlog) {

        $sql = "INSERT INTO ?_blog
			(user_owner_id,
			blog_title,
			blog_description,
			blog_type,
			blog_date_add,
			blog_limit_rating_topic,
			blog_url,
			blog_avatar
			)
			VALUES(?d, ?, ?, ?, ?, ?, ?, ?)
		";
        $nId = $this->oDb->query(
            $sql, $oBlog->getOwnerId(), $oBlog->getTitle(), $oBlog->getDescription(), $oBlog->getType(),
            $oBlog->getDateAdd(), $oBlog->getLimitRatingTopic(), $oBlog->getUrl(), $oBlog->getAvatar()
        );
        return $nId ? $nId : false;
    }

    /**
     * Обновляет блог в БД
     *
     * @param ModuleBlog_EntityBlog $oBlog    Объект блога
     *
     * @return bool
     */
    public function UpdateBlog(ModuleBlog_EntityBlog $oBlog) {

        $sql = "
            UPDATE ?_blog
			SET 
				blog_title= ?,
				blog_description= ?,
				blog_type= ?,
				blog_date_edit= ?,
				blog_rating= ?f,
				blog_count_vote = ?d,
				blog_count_user= ?d,
				blog_count_topic= ?d,
				blog_limit_rating_topic= ?f ,
				blog_url= ?,
				blog_avatar= ?
			WHERE
				blog_id = ?d
		";
        $bResult = $this->oDb->query(
            $sql, $oBlog->getTitle(), $oBlog->getDescription(), $oBlog->getType(), $oBlog->getDateEdit(),
            $oBlog->getRating(), $oBlog->getCountVote(), $oBlog->getCountUser(), $oBlog->getCountTopic(),
            $oBlog->getLimitRatingTopic(), $oBlog->getUrl(), $oBlog->getAvatar(), $oBlog->getId()
        );
        return $bResult !== false;
    }

    /**
     * Получает список блогов по ID
     *
     * @param array|int  $aArrayId    Список ID блогов
     * @param array|null $aOrder      Сортировка блогов
     *
     * @return array
     */
    public function GetBlogsByArrayId($aArrayId, $aOrder = null) {

        if (!$aArrayId) {
            return array();
        }
        if (!is_array($aArrayId)) {
            $aArrayId = array(intval($aArrayId));
        }

        if (!is_array($aOrder)) {
            $aOrder = array($aOrder);
        }
        $sOrder = '';
        foreach ($aOrder as $key => $value) {
            $value = (string)$value;
            if (!in_array(
                $key, array('blog_id', 'blog_title', 'blog_type', 'blog_rating', 'blog_count_user', 'blog_date_add')
            )) {
                unset($aOrder[$key]);
            } elseif (in_array($value, array('asc', 'desc'))) {
                $sOrder .= " {$key} {$value},";
            }
        }
        $sOrder = trim($sOrder, ',');

        $sql
            = "SELECT
					*
				FROM 
					?_blog
				WHERE 
					blog_id IN(?a)
				ORDER BY
					{ FIELD(blog_id,?a) }
				";
        if ($sOrder != '') {
            $sql .= $sOrder;
        }

        $aBlogs = array();
        if ($aRows = $this->oDb->select($sql, $aArrayId, $sOrder == '' ? $aArrayId : DBSIMPLE_SKIP)) {
            foreach ($aRows as $aBlog) {
                $aBlogs[] = Engine::GetEntity('Blog', $aBlog);
            }
        }
        return $aBlogs;
    }

    /**
     * Добавляет свзяь пользователя с блогом в БД
     *
     * @param ModuleBlog_EntityBlogUser $oBlogUser    Объект отношения пользователя с блогом
     *
     * @return bool
     */
    public function AddRelationBlogUser(ModuleBlog_EntityBlogUser $oBlogUser) {

        $sql = "
            INSERT INTO ?_blog_user
            SET
                blog_id = ?d,
                user_id = ?d
                user_role = ?d
            ";
        $xResult = $this->oDb->query($sql, $oBlogUser->getBlogId(), $oBlogUser->getUserId(), $oBlogUser->getUserRole());
        return $xResult !== false;
    }

    /**
     * Удаляет отношение пользователя с блогом
     *
     * @param ModuleBlog_EntityBlogUser $oBlogUser    Объект отношения пользователя с блогом
     *
     * @return bool
     */
    public function DeleteRelationBlogUser(ModuleBlog_EntityBlogUser $oBlogUser) {

        $sql = "
            DELETE FROM ?_blog_user
			WHERE
				blog_id = ?d
				AND
				user_id = ?d
		";
        $xResult = $this->oDb->query($sql, $oBlogUser->getBlogId(), $oBlogUser->getUserId());
        return $xResult !== false;
    }

    /**
     * Обновляет отношение пользователя с блогом
     *
     * @param ModuleBlog_EntityBlogUser $oBlogUser    Объект отношения пользователя с блогом
     *
     * @return bool
     */
    public function UpdateRelationBlogUser(ModuleBlog_EntityBlogUser $oBlogUser) {

        $sql = "
            UPDATE ?_blog_user
			SET 
				user_role = ?d
			WHERE
				blog_id = ?d 
				AND
				user_id = ?d
		";
        $xResult = $this->oDb->query($sql, $oBlogUser->getUserRole(), $oBlogUser->getBlogId(), $oBlogUser->getUserId());
        return $xResult !== false;
    }

    /**
     * Получает список отношений пользователей с блогами
     *
     * @param array $aFilter         Фильтр поиска отношений
     * @param int   $iCount          Возвращает общее количество элементов
     * @param int   $iCurrPage       Номер текущейс страницы
     * @param int   $iPerPage        Количество элементов на одну страницу
     *
     * @return array
     */
    public function GetBlogUsers($aFilter, &$iCount = null, $iCurrPage = null, $iPerPage = null) {

        $sWhere = ' 1=1 ';
        if (isset($aFilter['blog_id'])) {
            $sWhere .= " AND bu.blog_id =  " . (int)$aFilter['blog_id'];
        }
        if (isset($aFilter['user_id'])) {
            $sWhere .= " AND bu.user_id =  " . (int)$aFilter['user_id'];
        }
        if (isset($aFilter['user_role'])) {
            if (!is_array($aFilter['user_role'])) {
                $aFilter['user_role'] = array($aFilter['user_role']);
            }
            $sWhere .= " AND bu.user_role IN ('" . join("', '", $aFilter['user_role']) . "')";
        } else {
            $sWhere .= " AND bu.user_role>" . ModuleBlog::BLOG_USER_ROLE_GUEST;
        }

        $sql = "SELECT
					bu.*
				FROM 
					?_blog_user as bu
				WHERE 
					" . $sWhere . " ";

        if (is_null($iCurrPage)) {
            $aRows = $this->oDb->select($sql);
        } else {
            $sql .= " LIMIT ?d, ?d ";
            $aRows = $this->oDb->selectPage($iCount, $sql, ($iCurrPage - 1) * $iPerPage, $iPerPage);
        }

        $aBlogUsers = array();
        if ($aRows) {
            foreach ($aRows as $aUser) {
                $aBlogUsers[] = Engine::GetEntity('Blog_BlogUser', $aUser);
            }
        }
        return $aBlogUsers;
    }

    /**
     * Получает список отношений пользователя к блогам
     *
     * @param array $aArrayId Список ID блогов
     * @param int   $sUserId  ID блогов
     *
     * @return array
     */
    public function GetBlogUsersByArrayBlog($aArrayId, $sUserId) {

        if (!is_array($aArrayId) || count($aArrayId) == 0) {
            return array();
        }

        $sql = "SELECT
					bu.*
				FROM 
					?_blog_user as bu
				WHERE 
					bu.blog_id IN(?a)
					AND
					bu.user_id = ?d ";
        $aBlogUsers = array();
        if ($aRows = $this->oDb->select($sql, $aArrayId, $sUserId)) {
            foreach ($aRows as $aUser) {
                $aBlogUsers[] = Engine::GetEntity('Blog_BlogUser', $aUser);
            }
        }
        return $aBlogUsers;
    }

    /**
     * Получает ID персонального блога пользователя
     *
     * @param   int     $sUserId ID пользователя
     *
     * @return  int|null
     */
    public function GetPersonalBlogByUserId($sUserId) {

        $sql = "SELECT blog_id FROM ?_blog WHERE user_owner_id = ?d AND blog_type='personal'";
        if ($aRow = $this->oDb->selectRow($sql, $sUserId)) {
            return $aRow['blog_id'];
        }
        return null;
    }

    /**
     * Возвращает список ID всех блогов, принадлежащих пользователю
     *
     * @param   array   $aUsersId
     *
     * @return  array
     */
    public function GetBlogsIdByOwnersId($aUsersId) {

        $aUsersId = $this->_arrayId($aUsersId);
        $sql = "
            SELECT blog_id
            FROM ?_blog
            WHERE user_owner_id IN (?a) ";
        return $this->oDb->selectCol($sql, $aUsersId);
    }

    /**
     * Получает блог по названию
     *
     * @param string $sTitle Нащвание блога
     *
     * @return ModuleBlog_EntityBlog|null
     */
    public function GetBlogByTitle($sTitle) {

        $sql = "SELECT blog_id FROM ?_blog WHERE blog_title = ? ";
        $nId = $this->oDb->selectCell($sql, $sTitle);
        return $nId ? $nId : null;
    }

    /**
     * Получает блог по URL
     *
     * @param string $sUrl URL блога
     *
     * @return ModuleBlog_EntityBlog|null
     */
    public function GetBlogByUrl($sUrl) {

        $sql = "SELECT
				b.blog_id 
			FROM 
				?_blog as b
			WHERE 
				b.blog_url = ?
				";
        $nId = $this->oDb->selectCell($sql, $sUrl);
        return $nId ? $nId : null;
    }

    /**
     * Получить список блогов по хозяину
     *
     * @param int $nUserId ID пользователя
     *
     * @return array
     */
    public function GetBlogsByOwnerId($nUserId) {

        $aFilter = array(
            'user_id' => intval($nUserId),
            'exclude_type' => 'personal',
        );
        return $this->GetBlogsIdByFilter($aFilter);
    }

    /**
     * Возвращает список всех не персональных блогов
     *
     * @return array
     */
    public function GetBlogs() {

        $aFilter = array(
            'exclude_type' => 'personal',
        );
        return $this->GetBlogsIdByFilter($aFilter);
    }

    /**
     * Возвращает ID блогов по заданному фильтру
     *
     * @param $aFilter
     *
     * @return array
     */
    public function GetBlogsIdByFilter($aFilter) {

        if (isset($aFilter['user_id'])) {
            $aFilter['user_id'] = intval($aFilter['user_id']);
            if (!$aFilter['user_id']) {
                return array();
            }
        }
        $sql = "
            SELECT
                b.blog_id
            FROM
                ?_blog as b
            WHERE
                1=1
                { AND b.user_owner_id = ?d }
                { AND b.blog_type IN (?a) }
                { AND b.blog_type NOT IN (?a) }
            ";
        $aBlogsId = $this->oDb->selectCol(
            $sql,
            isset($aFilter['user_id']) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
            isset($aFilter['include_type']) ? (array)$aFilter['include_type'] : DBSIMPLE_SKIP,
            isset($aFilter['exclude_type']) ? (array)$aFilter['exclude_type'] : DBSIMPLE_SKIP
        );
        return $aBlogsId ? $aBlogsId : array();
    }

    /**
     * Возвращает список не персональных блогов с сортировкой по рейтингу
     *
     * @param int $iCount          Возвращает общее количество элементов
     * @param int $iCurrPage       Номер текущей страницы
     * @param int $iPerPage        Количество элементов на одну страницу
     *
     * @return array
     */
    public function GetBlogsRating(&$iCount, $iCurrPage, $iPerPage) {

        $sql = "SELECT
					b.blog_id
				FROM 
					?_blog as b
				WHERE
					b.blog_type<>'personal'
				ORDER BY b.blog_rating DESC
				LIMIT ?d, ?d
				";
        $aReturn = array();
        if ($aRows = $this->oDb->selectPage($iCount, $sql, ($iCurrPage - 1) * $iPerPage, $iPerPage)) {
            foreach ($aRows as $aRow) {
                $aReturn[] = $aRow['blog_id'];
            }
        }
        return $aReturn;
    }

    /**
     * Получает список блогов в которых состоит пользователь
     *
     * @param int $sUserId   ID пользователя
     * @param int $iLimit    Ограничение на выборку элементов
     *
     * @return array
     */
    public function GetBlogsRatingJoin($sUserId, $iLimit) {

        $sql = "SELECT
					b.*
				FROM 
					?_blog_user as bu,
					?_blog as b
				WHERE
					bu.user_id = ?d
					AND
					bu.blog_id = b.blog_id
					AND
					b.blog_type<>'personal'
				ORDER by b.blog_rating desc
				LIMIT 0, ?d";
        $aReturn = array();
        if ($aRows = $this->oDb->select($sql, $sUserId, $iLimit)) {
            foreach ($aRows as $aRow) {
                $aReturn[] = Engine::GetEntity('Blog', $aRow);
            }
        }
        return $aReturn;
    }

    /**
     * Получает список блогов, которые создал пользователь
     *
     * @param int $sUserId   ID пользователя
     * @param int $iLimit    Ограничение на выборку элементов
     *
     * @return array
     */
    public function GetBlogsRatingSelf($sUserId, $iLimit) {

        $sql = "SELECT
					b.*
				FROM
					?_blog as b
				WHERE
					b.user_owner_id = ?d
					AND
					b.blog_type<>'personal'
				ORDER BY b.blog_rating DESC
				LIMIT 0, ?d";
        $aReturn = array();
        if ($aRows = $this->oDb->select($sql, $sUserId, $iLimit)) {
            foreach ($aRows as $aRow) {
                $aReturn[] = Engine::GetEntity('Blog', $aRow);
            }
        }
        return $aReturn;
    }

    /**
     * Возвращает полный список закрытых блогов
     *
     * @return array
     */
    public function GetCloseBlogs() {

        $aFilter = array(
            'include_type' => 'close',
        );
        return $this->GetBlogsIdByFilter($aFilter);
    }

    /**
     * Удаление блога (или нескольких блогов) из базы данных
     *
     * @param   int|array   $aBlogsId ID блога
     *
     * @return  bool
     */
    public function DeleteBlog($aBlogsId) {

        $aBlogsId = $this->_arrayId($aBlogsId);
        $sql = "
            DELETE FROM ?_blog
            WHERE blog_id IN (?a)
        ";
        return ($this->oDb->query($sql, $aBlogsId) !== false);
    }

    /**
     * Удалить пользователей блога по идентификатору блога
     *
     * @param   int|array   $aBlogsId   ID блога или массив ID
     *
     * @return  bool
     */
    public function DeleteBlogUsersByBlogId($aBlogsId) {

        $aBlogsId = $this->_arrayId($aBlogsId);
        $sql = "
            DELETE FROM ?_blog_user
            WHERE blog_id IN (?a)
        ";
        return ($this->oDb->query($sql, $aBlogsId) !== false);
    }

    /**
     * Пересчитывает число топиков в блогах
     *
     * @param int|null $iBlogId ID блога
     *
     * @return bool
     */
    public function RecalculateCountTopic($iBlogId = null) {

        $sql = "
                UPDATE ?_blog b
                SET b.blog_count_topic = (
                    SELECT count(*)
                    FROM " . Config::Get('db.table.topic') . " t
                    WHERE
                        t.blog_id = b.blog_id
                    AND
                        t.topic_publish = 1
                )
                WHERE 1=1
                    { AND b.blog_id = ?d }
            ";
        $bResult = $this->oDb->query($sql, is_null($iBlogId) ? DBSIMPLE_SKIP : $iBlogId);
        return $bResult !== false;
    }

    /**
     * Получает список блогов по фильтру
     *
     * @param array $aFilter         Фильтр выборки
     * @param array $aOrder          Сортировка
     * @param int   $iCount          Возвращает общее количество элментов
     * @param int   $iCurrPage       Номер текущей страницы
     * @param int   $iPerPage        Количество элементов на одну страницу
     *
     * @return array
     */
    public function GetBlogsByFilter($aFilter, $aOrder, &$iCount, $iCurrPage, $iPerPage) {

        $aOrderAllow = array('blog_id', 'blog_title', 'blog_rating', 'blog_count_user', 'blog_count_topic');
        $sOrder = '';
        if (is_array($aOrder) && $aOrder) {
            foreach ($aOrder as $key => $value) {
                if (!in_array($key, $aOrderAllow)) {
                    unset($aOrder[$key]);
                } elseif (in_array($value, array('asc', 'desc'))) {
                    $sOrder .= " {$key} {$value},";
                }
            }
            $sOrder = trim($sOrder, ',');
        }
        if ($sOrder == '') {
            $sOrder = ' blog_id desc ';
        }

        if (isset($aFilter['exclude_type']) && !is_array($aFilter['exclude_type'])) {
            $aFilter['exclude_type'] = array($aFilter['exclude_type']);
        }
        if (isset($aFilter['type']) && !is_array($aFilter['type'])) {
            $aFilter['type'] = array($aFilter['type']);
        }

        $sql = "SELECT
					blog_id
				FROM
					?_blog
				WHERE
					1 = 1
					{ AND blog_id = ?d }
					{ AND user_owner_id = ?d }
					{ AND blog_type IN (?a) }
					{ AND blog_type NOT IN (?a) }
					{ AND blog_url = ? }
					{ AND blog_title LIKE ? }
				ORDER by {$sOrder}
				LIMIT ?d, ?d ;
					";
        $aResult = array();
        $aRows = $this->oDb->selectPage(
            $iCount, $sql,
            isset($aFilter['id']) ? $aFilter['id'] : DBSIMPLE_SKIP,
            isset($aFilter['user_owner_id']) ? $aFilter['user_owner_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['type']) && count($aFilter['type'])) ? $aFilter['type'] : DBSIMPLE_SKIP,
            (isset($aFilter['exclude_type']) && count($aFilter['exclude_type'])) ? $aFilter['exclude_type']
                : DBSIMPLE_SKIP,
            isset($aFilter['url']) ? $aFilter['url'] : DBSIMPLE_SKIP,
            isset($aFilter['title']) ? $aFilter['title'] : DBSIMPLE_SKIP,
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aResult[] = $aRow['blog_id'];
            }
        }
        return $aResult;
    }

    /**
     * Получить типы блогов
     *
     * @param array $aFilter
     *
     * @return  array
     */
    public function GetBlogTypes($aFilter) {

        $sql
            = "
            SELECT *
            FROM ?_blog_type
            WHERE
                1=1
                {AND allow_add=?}
            ORDER BY norder, id";

        $aRows = $this->oDb->select($sql,
            isset($aFilter['allow_add']) ? ($aFilter['allow_add'] ? 1 : 0) : DBSIMPLE_SKIP
        );
        if ($aRows) {
            $aResult = array();
            $aStat = $this->GetBlogCountsByTypes();
            foreach ($aRows as $aType) {
                if (isset($aStat[$aType['type_code']])) {
                    $aType['blogs_count'] = $aStat[$aType['type_code']]['blogs_count'];
                } else {
                    $aType['blogs_count'] = 0;
                }
                $aResult[] = Engine::GetEntity('Blog_BlogType', $aType);
            }
            return $aResult;
        }
        return array();
    }

    public function GetBlogCountsByTypes($aTypes = null) {

        if ($aTypes && !is_array($aTypes)) {
            $aTypes = array($aTypes);
        }
        $sql
            = "
            SELECT DISTINCT blog_type AS ARRAY_KEY, Count( blog_id ) AS blogs_count
            FROM ?_blog
            GROUP BY blog_type
            {WHERE blog_type IN (?a)}
            ORDER BY blog_type
            ";
        $aRows = $this->oDb->select($sql, $aTypes ? $aTypes : DBSIMPLE_SKIP);
        return $aRows;
    }

    /**
     * Получить тип блога по ID
     *
     * @param $nId
     *
     * @return bool|Entity
     */
    public function GetBlogTypeById($nId) {

        $sql
            = "
            SELECT bt.*
            FROM ?_blog_type AS bt
            WHERE bt.id=?d
            ";
        $aRow = $this->oDb->selectRow($sql, $nId);
        if ($aRow) {
            return Engine::GetEntity('Blog_BlogType', $aRow);
        }
        return false;
    }

    public function AddBlogType($oBlogType) {

        $sql = "
            INSERT INTO ?_blog_type
            SET
                type_code = ?,
                min_rating = ?f,
                allow_add = ?d,
                show_title = ?d,
                index_ignore = ?d,
                membership = ?d,
                acl_write = ?d,
                acl_read = ?d,
                acl_comment = ?d,
                min_rate_write = ?f,
                min_rate_read = ?f,
                min_rate_comment = ?f,
                active = ?d,
                norder = ?d,
                candelete = ?d
        ";
        $nId = $this->oDb->query($sql,
            $oBlogType->getTypeCode(),
            $oBlogType->getMinRating(),
            $oBlogType->IsAllowAdd() ? 1 : 0,
            $oBlogType->IsShowTitle() ? 1 : 0,
            $oBlogType->IsIndexIgnore() ? 1 : 0,
            $oBlogType->getMembership(),
            $oBlogType->getAclWrite(),
            $oBlogType->getAclRead(),
            $oBlogType->getAclComment(),
            $oBlogType->getMinRateWrite(),
            $oBlogType->getMinRateRead(),
            $oBlogType->getMinRateComment(),
            $oBlogType->IsActive() ? 1 : 0,
            $oBlogType->getNorder(),
            $oBlogType->IsCanDelete() ? 1 : 0,
            $oBlogType->getId()
        );
        return $nId ? $nId : false;
    }

    public function UpdateBlogType($oBlogType) {

        $sql = "
            UPDATE ?_blog_type
            SET
                min_rating = ?f,
                allow_add = ?d,
                show_title = ?d,
                index_ignore = ?d,
                membership = ?d,
                acl_write = ?d,
                acl_read = ?d,
                acl_comment = ?d,
                min_rate_write = ?f,
                min_rate_read = ?f,
                min_rate_comment = ?f,
                active = ?d,
                norder = ?d,
                candelete = ?d
            WHERE
                id = ?d
        ";
        $xResult = $this->oDb->query($sql,
            $oBlogType->getMinRating(),
            $oBlogType->IsAllowAdd() ? 1 : 0,
            $oBlogType->IsShowTitle() ? 1 : 0,
            $oBlogType->IsIndexIgnore() ? 1 : 0,
            $oBlogType->getMembership(),
            $oBlogType->getAclWrite(),
            $oBlogType->getAclRead(),
            $oBlogType->getAclComment(),
            $oBlogType->getMinRateWrite(),
            $oBlogType->getMinRateRead(),
            $oBlogType->getMinRateComment(),
            $oBlogType->IsActive() ? 1 : 0,
            $oBlogType->getNorder(),
            $oBlogType->IsCanDelete() ? 1 : 0,
            $oBlogType->getId()
        );
        return $xResult !== false;
    }

    /**
     * Статистка блогов
     *
     * @param $aExcludeTypes
     *
     * @return array
     */
    public function GetBlogsData($aExcludeTypes) {

        if (isset($aExcludeTypes) && !is_array($aExcludeTypes)) {
            $aExcludeTypes = array($aExcludeTypes);
        }
        $sql = "
            SELECT
                b.*,
                SUM(t.topic_rating) as sum_rating
            FROM
                ?_blog as b,
                " . Config::Get('db.table.topic') . " as t
            WHERE
                b.blog_id=t.blog_id
                AND
                t.topic_publish=1
                AND
                blog_type not IN (?a) 
            GROUP BY b.blog_id
            ";
        $aBlogs=array();
        if($aRows = $this->oDb->select($sql,$aExcludeTypes)){
            foreach ($aRows as $aBlog) {
                $aBlogs[] = Engine::GetEntity('Blog', $aBlog);
            }
        }
        return $aBlogs;
    }

}

// EOF