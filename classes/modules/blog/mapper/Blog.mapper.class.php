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

    protected $aBlogOrderAllow = array('blog_id', 'blog_title', 'blog_rating', 'blog_count_user', 'blog_count_topic');

    /**
     * Добавляет блог в БД
     *
     * @param   ModuleBlog_EntityBlog $oBlog    - Объект блога
     *
     * @return  int|bool
     */
    public function AddBlog($oBlog) {

        $sql
            = "
            INSERT INTO ?_blog
            SET
                user_owner_id = ?d,
                blog_title = ?,
                blog_description = ?,
                blog_type = ?,
                blog_date_add = ?,
                blog_limit_rating_topic = ?,
                blog_url = ?,
                blog_avatar = ?
        ";
        $nId = $this->oDb->query(
            $sql,
            $oBlog->getOwnerId(),
            $oBlog->getTitle(),
            $oBlog->getDescription(),
            $oBlog->getType(),
            $oBlog->getDateAdd(),
            $oBlog->getLimitRatingTopic(),
            $oBlog->getUrl(),
            $oBlog->getAvatar()
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
    public function UpdateBlog($oBlog) {

        $sql
            = "
            UPDATE ?_blog
            SET
                blog_title = ?,
                blog_description = ?,
                blog_type = ?,
                blog_date_edit = ?,
                blog_rating = ?f,
                blog_count_vote = ?d,
                blog_count_user = ?d,
                blog_count_topic = ?d,
                blog_limit_rating_topic = ?f ,
                blog_url = ?,
                blog_avatar = ?
            WHERE
                blog_id = ?d
        ";
        $bResult = $this->oDb->query(
            $sql,
            $oBlog->getTitle(),
            $oBlog->getDescription(),
            $oBlog->getType(),
            $oBlog->getDateEdit(),
            $oBlog->getRating(),
            $oBlog->getCountVote(),
            $oBlog->getCountUser(),
            $oBlog->getCountTopic(),
            $oBlog->getLimitRatingTopic(),
            $oBlog->getUrl(),
            $oBlog->getAvatar(),
            $oBlog->getId()
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
        foreach ($aOrder as $sKey => $sValue) {
            $sValue = strtoupper($sValue);
            if (!in_array(strtolower($sKey), $this->aBlogOrderAllow)) {
                unset($aOrder[$sKey]);
            } elseif (in_array($sValue, array('ASC', 'DESC'))) {
                $sOrder .= " {$sKey} {$sValue},";
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
     * Возвращает список ID пользователей, являющихся авторами в блоге
     *
     * @param $nBlogId
     *
     * @return array
     */
    public function GetAuthorsIdByBlogId($nBlogId) {

        $sql = "
            SELECT DISTINCT t.user_id
            FROM
                ?_topic t
            WHERE
                t.blog_id = ?d
        ";
        $aResult = $this->oDb->selectCol($sql, $nBlogId);
        return $aResult ? $aResult : array();
    }

    /**
     * Получает ID персонального блога пользователя
     *
     * @param   int     $nUserId ID пользователя
     *
     * @return  int|null
     */
    public function GetPersonalBlogByUserId($nUserId) {

        $aCriteria = array(
            'filter' => array(
                'user_id' => intval($nUserId),
                'blog_type' => 'personal',
            ),
            'limit' => 1,
        );
        $aResult = $this->GetBlogsIdByCriteria($aCriteria);
        return $aResult['data'] ? $aResult['data'][0] : null;
    }

    /**
     * Возвращает список ID всех блогов, принадлежащих пользователю
     *
     * @param   array   $aUsersId
     *
     * @return  array
     */
    public function GetBlogsIdByOwnersId($aUsersId) {

        $aCriteria = array(
            'filter' => array(
                'user_id' => $aUsersId,
            ),
        );
        $aResult = $this->GetBlogsIdByCriteria($aCriteria);
        return $aResult['data'];
    }

    /**
     * Получает блог по названию
     *
     * @param   string $sTitle     - Название блога
     *
     * @return  ModuleBlog_EntityBlog|null
     */
    public function GetBlogByTitle($sTitle) {

        $aCriteria = array(
            'filter' => array(
                'blog_title' => $sTitle,
            ),
            'limit'  => 1,
        );
        $aResult = $this->GetBlogsIdByCriteria($aCriteria);
        return $aResult['data'] ? $aResult['data'][0] : null;
    }

    /**
     * Получает блог по URL
     *
     * @param   string $sUrl   - URL блога
     *
     * @return ModuleBlog_EntityBlog|null
     */
    public function GetBlogByUrl($sUrl) {

        $aCriteria = array(
            'filter' => array(
                'blog_url' => $sUrl,
            ),
            'limit'  => 1,
        );
        $aResult = $this->GetBlogsIdByCriteria($aCriteria);
        return $aResult['data'] ? $aResult['data'][0] : null;
    }

    /**
     * Получить список блогов по хозяину
     *
     * @param   int $nUserId    - ID пользователя
     *
     * @return  array
     */
    public function GetBlogsByOwnerId($nUserId) {

        $aCriteria = array(
            'filter' => array(
                'user_id'       => intval($nUserId),
                'not_blog_type' => 'personal',
            ),
        );
        $aResult = $this->GetBlogsIdByCriteria($aCriteria);
        return $aResult['data'];
    }

    /**
     * Возвращает список всех не персональных блогов
     *
     * @return  array
     */
    public function GetBlogs() {

        $aCriteria = array(
            'filter' => array(
                'not_blog_type' => 'personal',
            ),
        );
        $aResult = $this->GetBlogsIdByCriteria($aCriteria);
        return $aResult['data'];
    }

    /**
     * Возвращает ID блогов по заданному фильтру
     *
     * @param   array $aFilter    - фильтр
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

        $aCriteria = array(
            'filter' => array(),
        );
        if (isset($aFilter['user_id'])) {
            $aCriteria['filter']['user_id'] = $aFilter['user_id'];
        }
        if (isset($aFilter['include_type'])) {
            $aCriteria['filter']['blog_type'] = $aFilter['include_type'];
        }
        if (isset($aFilter['exclude_type'])) {
            $aCriteria['filter']['not_blog_type'] = $aFilter['exclude_type'];
        }
        $aResult = $this->GetBlogsIdByCriteria($aCriteria);
        return $aResult['data'];
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

        $aCriteria = array(
            'filter' => array(
                'not_blog_type' => 'personal',
            ),
            'order'  => array(
                'blog_rating' => 'DESC',
            ),
            'limit'  => array(($iCurrPage - 1) * $iPerPage, $iPerPage),
        );

        $aResult = $this->GetBlogsIdByCriteria($aCriteria);
        $iCount = $aResult['total'];
        return $aResult['data'];
    }

    /**
     * Получает список блогов в которых состоит пользователь
     *
     * @param int $nUserId   ID пользователя
     * @param int $nLimit    Ограничение на выборку элементов
     *
     * @return array
     */
    public function GetBlogsRatingJoin($nUserId, $nLimit) {

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
        if ($aRows = $this->oDb->select($sql, $nUserId, $nLimit)) {
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
     * @param int $nLimit    Ограничение на выборку элементов
     *
     * @return array
     */
    public function GetBlogsRatingSelf($sUserId, $nLimit) {

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
        if ($aRows = $this->oDb->select($sql, $sUserId, $nLimit)) {
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

        $aCriteria = array(
            'filter' => array(
                'blog_type' => 'close',
            ),
        );
        $aResult = $this->GetBlogsIdByCriteria($aCriteria);
        return $aResult['data'];
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
     * @param int|null $nBlogId ID блога
     *
     * @return bool
     */
    public function RecalculateCountTopic($nBlogId = null) {

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
        $bResult = $this->oDb->query($sql, is_null($nBlogId) ? DBSIMPLE_SKIP : $nBlogId);
        return $bResult !== false;
    }

    /**
     * DEPRECATED
     * 
     * Use GetBlogsIdByFilterPerPage() instead
     */
    public function GetBlogsByFilter($aFilter, $aOrder, &$iCount, $iCurrPage, $iPerPage) {

        return $this->GetBlogsIdByFilterPerPage($aFilter, $aOrder, $iCount, $iCurrPage, $iPerPage);
    }
    
    /**
     * Получает список блогов по фильтру
     *
     * @param array $aFilter         - Фильтр выборки
     * @param array $aOrder          - Сортировка
     * @param int   $iCount          - Возвращает общее количество элментов
     * @param int   $iCurrPage       - Номер текущей страницы
     * @param int   $iPerPage        - Количество элементов на одну страницу
     *
     * @return array
     */
    public function GetBlogsIdByFilterPerPage($aFilter, $aOrder, &$iCount, $iCurrPage, $iPerPage) {

        $aCriteria = array(
            'filter' => array(),
            'order'  => array(),
            'limit'  => array(($iCurrPage - 1) * $iPerPage, $iPerPage),
        );

        if (isset($aFilter['type']) && !isset($aFilter['include_type'])) {
            $aCriteria['filter']['blog_type'] = $aFilter['type'];
        } elseif (isset($aFilter['include_type'])) {
            $aCriteria['filter']['blog_type'] = $aFilter['include_type'];
        }
        if (isset($aFilter['exclude_type'])) {
            $aCriteria['filter']['not_blog_type'] = $aFilter['exclude_type'];
        }

        if (is_array($aOrder) && $aOrder) {
            $aCriteria['order'] = $aOrder;
        } else {
            $aCriteria['order'] = array('blog_id' => 'DESC');
        }
        $aResult = $this->GetBlogsIdByCriteria($aCriteria);
        $iCount = $aResult['total'];
        return $aResult['data'];
    }

    /**
     * Возвращает объекты типов блогов
     *
     * @return  array(ModuleBlog_BlogType)
     */
    public function GetBlogTypes() {

        $sql
            = "
            SELECT *
            FROM ?_blog_type
            ORDER BY norder, id";

        $aRows = $this->oDb->select(
            $sql,
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

    /**
     * Возвращает количество блогов с разбивкой по типам
     *
     * @param null|string|array $aTypes
     *
     * @return array
     */
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
     * Возвращает объект типа блога по ID
     *
     * @param $nId
     *
     * @return ModuleBlog_BlogType|null
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
        return null;
    }

    /**
     * Добавляет тип блога
     *
     * @param $oBlogType
     *
     * @return bool
     */
    public function AddBlogType($oBlogType) {

        $sql = "
            INSERT INTO ?_blog_type
            SET
                type_code = ?,
                allow_add = ?d,
                min_rate_add = ?f,
                allow_list = ?d,
                min_rate_list = ?f,
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
            $oBlogType->getAllowAdd() ? 1 : 0,
            $oBlogType->getMinRateAdd(),
            $oBlogType->getAllowList() ? 1 : 0,
            $oBlogType->getMinRateList(),
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
            $oBlogType->CanDelete() ? 1 : 0,
            $oBlogType->getId()
        );
        return $nId ? $nId : false;
    }

    /**
     * Обновляет тип блога
     *
     * @param $oBlogType
     *
     * @return bool
     */
    public function UpdateBlogType($oBlogType) {

        $sql
            = "
            UPDATE ?_blog_type
            SET
                allow_add = ?d,
                min_rate_add = ?f,
                allow_list = ?d,
                min_rate_list = ?f,
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
            $oBlogType->getAllowAdd() ? 1 : 0,
            $oBlogType->getMinRateAdd(),
            $oBlogType->getAllowList() ? 1 : 0,
            $oBlogType->getMinRateList(),
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
            $oBlogType->CanDelete() ? 1 : 0,
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
        $sql
            = "
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
        $aBlogs = array();
        if ($aRows = $this->oDb->select($sql, $aExcludeTypes)) {
            foreach ($aRows as $aBlog) {
                $aBlogs[] = Engine::GetEntity('Blog', $aBlog);
            }
        }
        return $aBlogs;
    }

    /*********************************************************/

    /**
     * Возвращает список ID блогов по заданным критериям
     *
     * Общая структура массива критериев
     *  $aCriteria = array(
     *      'filter' => array(..),
     *      'order' => array(..),
     *      'limit' => array(..),
     * );
     *
     * Возвращаемое значение:
     *  array('data' => array(), 'total' => int)
     *
     * @param array $aCriteria
     *
     * @return array
     */
    public function GetBlogsIdByCriteria($aCriteria = array()) {

        if (isset($aCriteria['filter'])) {
            $aFilter = $aCriteria['filter'];
        } else {
            $aFilter = array();
        }
        if (isset($aFilter['not_blog_id'])) {
            if (!is_array($aFilter['not_blog_id'])) {
                $aFilter['not_blog_id'] = array(intval($aFilter['not_blog_id']));
            }
        }
        if (isset($aFilter['blog_title_like'])) {
            if (substr($aFilter['blog_title'], -1) !== '%') {
                $aFilter['blog_title'] .= '%';
            }
        }

        // Сортировка
        $sSqlOrder = '';
        if (isset($aCriteria['order'])) {
            $aOrderAllow = array('blog_id', 'blog_title', 'blog_rating', 'blog_count_user', 'blog_count_topic');
            if (!is_array($aCriteria['order'])) {
                $aCriteria['order'] = array($aCriteria['order']);
            }
            $aOrders = F::Array_FlipIntKeys($aCriteria['order'], 'ASC');
            $aOrderList = array();
            foreach ($aOrders as $sField => $sWay) {
                $sField = strtolower(trim($sField));
                if (strpos($sField, ' ')) {
                    list($sField, $sWay) = explode(' ', $sField, 2);
                }
                if (in_array($sField, $aOrderAllow)) {
                    $aOrderList[] = $sField . ' ' . ((strtoupper($sWay) == 'DESC') ? 'DESC' : 'ASC');
                }
            }
            if ($aOrderList) {
                $sSqlOrder = 'ORDER BY ' . implode(',' , $aOrderList);
            }
        }

        // Установка лимита
        $sSqlLimit = '';
        list($nOffset, $nLimit) = $this->_prepareLimit($aCriteria);

        // Если задан ID блога, то всегда устанавливаем лимит
        if (isset($aFilter['blog_id']) && !is_array($aFilter['blog_id'])) {
            $nOffset = false;
            $nLimit = 1;
        }

        // Формируем строку лимита и автосчетчик общего числа записей
        if ($nOffset !== false && $nLimit !== false) {
            $sSqlLimit = 'LIMIT ' . $nOffset . ', ' . $nLimit;
            $nCalcTotal = static::CRITERIA_CALC_TOTAL_AUTO;
        } elseif ($nLimit != false && $nLimit != 1) {
            $sSqlLimit = 'LIMIT ' . $nLimit;
            $nCalcTotal = static::CRITERIA_CALC_TOTAL_AUTO;
        } else {
            $nCalcTotal = static::CRITERIA_CALC_TOTAL_SKIP;
        }

        // Обрабатываем опции
        if (isset($aCriteria['options']) && is_array($aCriteria['options'])) {
            if (array_key_exists('calc_total', $aCriteria['options'])) {
                if ($aCriteria['options']['calc_total'] != static::CRITERIA_CALC_TOTAL_AUTO) {
                    $nCalcTotal = $aCriteria['options']['calc_total'];
                }
                // Если требуется только подсчет записей, то строку лимита принудительно устанавливаем в 0
                // Запрос с LIMIT 0 отрабатывает моментально
                if ($aCriteria['options']['calc_total'] != static::CRITERIA_CALC_TOTAL_ONLY) {
                    $sSqlLimit = 'LIMIT 0';
                }
            }
        }

        // Необходимость JOIN'а
        $aBlogTypeFields = array(
            'allow_add', 'min_rate_add', 'allow_list', 'min_rate_list', 'acl_read', 'min_rate_read', 'acl_write',
            'min_rate_write', 'acl_comment', 'min_rate_comment', 'index_ignore', 'membership',
        );
        if ($aFilter && array_intersect($aFilter, $aBlogTypeFields)) {
            $bBlogTypeJoin = true;
        } else {
            $bBlogTypeJoin = false;
        }

        $sql = "
            SELECT b.blog_id
            FROM ?_blog AS b
                { INNER JOIN ?_blog_type AS bt ON bt.type_code=b.blog_type AND 1=?d }
            WHERE
                1 = 1
                { AND (b.blog_id = ?d) }
                { AND (b.blog_id IN (?a)) }
                { AND (b.blog_id NOT IN (?a)) }
                { AND (b.user_owner_id = ?d) }
                { AND (b.user_owner_id IN (?a)) }
                { AND (b.blog_type = ?) }
                { AND (b.blog_type IN (?a)) }
                { AND (b.blog_type != ?) }
                { AND (b.blog_type NOT IN (?a)) }
                { AND blog_url = ? }
                { AND blog_title = ? }
                { AND blog_title LIKE ? }
                { AND (bt.allow_add = ?d) }
                { AND (bt.min_rate_add >= ?d) }
                { AND (bt.allow_list = ?d) }
                { AND (bt.min_rate_list >= ?d) }
                { AND (bt.acl_read & ?d > 0) }
                { AND (bt.min_rate_read >= ?d) }
                { AND (bt.acl_write & ?d > 0) }
                { AND (bt.min_rate_write >= ?d) }
                { AND (bt.acl_comment & ?d > 0) }
                { AND (bt.min_rate_comment >= ?d) }
                { AND (bt.index_ignore = ?d) }
                { AND (bt.membership = ?d) }
        " . $sSqlOrder . ' ' . $sSqlLimit;
        $aData = $this->oDb->selectCol($sql,
            $bBlogTypeJoin ? 1 : DBSIMPLE_SKIP,
            (isset($aFilter['blog_id']) && !is_array($aFilter['blog_id'])) ? $aFilter['blog_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['blog_id']) && is_array($aFilter['blog_id'])) ? $aFilter['blog_id'] : DBSIMPLE_SKIP,
            isset($aFilter['not_blog_id']) ? $aFilter['not_blog_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['user_id']) && !is_array($aFilter['user_id'])) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['user_id']) && is_array($aFilter['user_id'])) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['blog_type']) && !is_array($aFilter['blog_type'])) ? $aFilter['blog_type'] : DBSIMPLE_SKIP,
            (isset($aFilter['blog_type']) && is_array($aFilter['blog_type'])) ? $aFilter['blog_type'] : DBSIMPLE_SKIP,
            (isset($aFilter['not_blog_type']) && !is_array($aFilter['not_blog_type'])) ? $aFilter['not_blog_type'] : DBSIMPLE_SKIP,
            (isset($aFilter['not_blog_type']) && is_array($aFilter['not_blog_type'])) ? $aFilter['not_blog_type'] : DBSIMPLE_SKIP,
            isset($aFilter['blog_url']) ? $aFilter['blog_url'] : DBSIMPLE_SKIP,
            isset($aFilter['blog_title']) ? $aFilter['blog_title'] : DBSIMPLE_SKIP,
            isset($aFilter['blog_title_like']) ? $aFilter['blog_title_like'] : DBSIMPLE_SKIP,
            isset($aFilter['allow_add']) ? ($aFilter['allow_add'] ? 1 : 0) : DBSIMPLE_SKIP,
            isset($aFilter['min_rate_add']) ? $aFilter['min_rate_add'] : DBSIMPLE_SKIP,
            isset($aFilter['allow_list']) ? ($aFilter['allow_list'] ? 1 : 0) : DBSIMPLE_SKIP,
            isset($aFilter['min_rate_list']) ? $aFilter['min_rate_list'] : DBSIMPLE_SKIP,
            isset($aFilter['acl_read']) ? $aFilter['acl_read'] : DBSIMPLE_SKIP,
            isset($aFilter['min_rate_read']) ? $aFilter['min_rate_read'] : DBSIMPLE_SKIP,
            isset($aFilter['acl_write']) ? $aFilter['acl_write'] : DBSIMPLE_SKIP,
            isset($aFilter['min_rate_write']) ? $aFilter['min_rate_write'] : DBSIMPLE_SKIP,
            isset($aFilter['acl_comment']) ? $aFilter['acl_comment'] : DBSIMPLE_SKIP,
            isset($aFilter['min_rate_comment']) ? $aFilter['min_rate_comment'] : DBSIMPLE_SKIP,
            isset($aFilter['index_ignore']) ? ($aFilter['index_ignore'] ? 1 : 0) : DBSIMPLE_SKIP,
            isset($aFilter['membership']) ? ($aFilter['membership'] ? 1 : 0) : DBSIMPLE_SKIP
        );
        $aResult = array(
            'data' => $aData ? $aData : array(),
            'total' => -1,
        );
        if ($nCalcTotal) {
            $sLastQuery = trim($this->Database_GetLastQuery());
            $n = strpos($sLastQuery, ' LIMIT ');
            if ($n) {
                $sql = str_replace('SELECT b.blog_id', 'SELECT COUNT(*) AS cnt', substr($sLastQuery, 0, $n));
                $aData = $this->oDb->select($sql);
                if ($aData) {
                    $aResult['total'] = $aData[0]['cnt'];
                }
            }
        }
        return $aResult;
    }

}

// EOF