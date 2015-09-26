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
            (
                user_owner_id,
                blog_title,
                blog_description,
                blog_type,
                blog_date_add,
                blog_limit_rating_topic,
                blog_url,
                blog_avatar
            )
            VALUES (
                ?d,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
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
     * @param array|int  $aBlogId    Список ID блогов
     * @param array|null $aOrder      Сортировка блогов
     *
     * @return array
     */
    public function GetBlogsByArrayId($aBlogId, $aOrder = null) {

        if (!$aBlogId) {
            return array();
        }
        if (!is_array($aBlogId)) {
            $aBlogId = array(intval($aBlogId));
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
        $nLimit = sizeof($aBlogId);

        $sql
            = "SELECT
                    b.blog_id AS ARRAY_KEYS,
                    b.*
                FROM 
                    ?_blog AS b
                WHERE 
                    blog_id IN(?a)
            ";
        if ($sOrder != '') {
            $sql .= 'ORDER BY ' . $sOrder;
        }
        $sql .= ' LIMIT ' . $nLimit;

        $aBlogs = array();
        if ($aRows = $this->oDb->select($sql, $aBlogId)) {
            $aBlogs = E::GetEntityRows('Blog', $aRows, !$sOrder ? $aBlogId : null);
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
            (
                blog_id,
                user_id,
                user_role
            )
            VALUES (
                ?d,
                ?d,
                ?d
            )
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
     * @return ModuleBlog_EntityBlogUser[]
     */
    public function GetBlogUsers($aFilter, &$iCount = null, $iCurrPage = null, $iPerPage = null) {

        if (!empty($aFilter['user_all_role']) && !empty($aFilter['user_role'])) {
            unset($aFilter['user_role']);
        }

        $sql = "SELECT
                    bu.*
                FROM 
                    ?_blog_user as bu
                WHERE
                  1=1
                  {AND bu.blog_id=?d}
                  {AND bu.blog_id IN (?a)}
                  {AND bu.user_id=?d}
                  {AND bu.user_id IN(?a)}
                  {AND bu.user_role=?d}
                  {AND bu.user_role IN(?a)}
                  {AND bu.user_role>?d}
                ";

        if ((func_num_args() == 1) || is_null($iPerPage)) {
            $aRows = $this->oDb->select($sql,
                (!empty($aFilter['blog_id']) && is_numeric($aFilter['blog_id'])) ? $aFilter['blog_id'] : DBSIMPLE_SKIP,
                (!empty($aFilter['blog_id']) && is_array($aFilter['blog_id'])) ? $aFilter['blog_id'] : DBSIMPLE_SKIP,
                (!empty($aFilter['user_id']) && is_numeric($aFilter['user_id'])) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
                (!empty($aFilter['user_id']) && is_array($aFilter['user_id'])) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
                (isset($aFilter['user_role']) && is_numeric($aFilter['user_role'])) ? $aFilter['user_role'] : DBSIMPLE_SKIP,
                (isset($aFilter['user_role']) && is_array($aFilter['user_role'])) ? $aFilter['user_role'] : DBSIMPLE_SKIP,
                (empty($aFilter['user_all_role']) && !isset($aFilter['user_role'])) ? ModuleBlog::BLOG_USER_ROLE_GUEST : DBSIMPLE_SKIP
            );
        } else {
            if (!$iCurrPage) {
                $iCurrPage = 1;
            }
            $sql .= " LIMIT " . (($iCurrPage - 1) * $iPerPage) . ", " . intval($iPerPage);
            $aRows = $this->oDb->selectPage($iCount, $sql,
                (!empty($aFilter['blog_id']) && is_numeric($aFilter['blog_id'])) ? $aFilter['blog_id'] : DBSIMPLE_SKIP,
                (!empty($aFilter['blog_id']) && is_array($aFilter['blog_id'])) ? $aFilter['blog_id'] : DBSIMPLE_SKIP,
                (!empty($aFilter['user_id']) && is_numeric($aFilter['user_id'])) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
                (!empty($aFilter['user_id']) && is_array($aFilter['user_id'])) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
                (isset($aFilter['user_role']) && is_numeric($aFilter['user_role'])) ? $aFilter['user_role'] : DBSIMPLE_SKIP,
                (isset($aFilter['user_role']) && is_array($aFilter['user_role'])) ? $aFilter['user_role'] : DBSIMPLE_SKIP,
                (empty($aFilter['user_all_role']) && !isset($aFilter['user_role'])) ? ModuleBlog::BLOG_USER_ROLE_GUEST : DBSIMPLE_SKIP
            );
        }

        $aBlogUsers = array();
        if ($aRows) {
            $aBlogUsers = E::GetEntityRows('Blog_BlogUser', $aRows);
        }
        return $aBlogUsers;
    }

    /**
     * Получает список отношений пользователя к блогам
     *
     * @param array $aBlogId Список ID блогов
     * @param int   $nUserId  ID блогов
     *
     * @return ModuleBlog_EntityBlogUser[]
     */
    public function GetBlogUsersByArrayBlog($aBlogId, $nUserId) {

        if (!is_array($aBlogId) || count($aBlogId) == 0) {
            return array();
        }
        $nLimit = sizeof($aBlogId);
        $sql = "SELECT
                    bu.*
                FROM 
                    ?_blog_user as bu
                WHERE 
                    bu.blog_id IN(?a)
                    AND
                    bu.user_id = ?d
                LIMIT $nLimit";
        $aBlogUsers = array();
        if ($aRows = $this->oDb->select($sql, $aBlogId, $nUserId)) {
            $aBlogUsers = E::GetEntityRows('Blog_BlogUser', $aRows);
        }
        return $aBlogUsers;
    }

    /**
     * Возвращает список ID пользователей, являющихся авторами в блоге
     *
     * @param $nBlogId
     *
     * @return int[]
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
     * @param   int     $iUserId ID пользователя
     *
     * @return  int|null
     */
    public function GetPersonalBlogByUserId($iUserId) {

        $aCriteria = array(
            'filter' => array(
                'user_id' => intval($iUserId),
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
     * @param   string $xUrl   - URL блога
     *
     * @return ModuleBlog_EntityBlog|null
     */
    public function GetBlogByUrl($xUrl) {

        return $this->GetBlogsIdByUrl($xUrl);
    }

    /**
     * Returns ID of blog or array of IDs
     *
     * @param string|array $xUrl
     *
     * @return int|array
     */
    public function GetBlogsIdByUrl($xUrl) {

        $aCriteria = array(
            'filter' => array(
                'blog_url' => $xUrl,
            ),
        );
        if (is_array($xUrl)) {
            $aCriteria['limit'] = sizeof($xUrl);
            $bSingle = false;
        } else {
            $aCriteria['limit'] = 1;
            $bSingle = true;
        }
        $aResult = $this->GetBlogsIdByCriteria($aCriteria);
        if ($bSingle) {
            return $aResult['data'] ? $aResult['data'][0] : null;
        } else {
            return $aResult['data'] ? $aResult['data'] : array();
        }
    }

    public function GetBlogsByOwnerId($iUserId) {

        return $this->GetBlogsIdByOwnerId($iUserId);
    }

    /**
     * Получить список блогов по хозяину
     *
     * @param   int $iUserId    - ID пользователя
     *
     * @return  array
     */
    public function GetBlogsIdByOwnerId($iUserId) {

        $aFilter = E::ModuleBlog()->GetNamedFilter('default');
        $aFilter['user_id'] = intval($iUserId);
        $aCriteria = array(
            'filter' => $aFilter,
        );
        $aResult = $this->GetBlogsIdByCriteria($aCriteria);
        return $aResult['data'];
    }

    /**
     * @deprecated
     *
     * LS-compatibility
     */
    public function GetBlogs() {

        return $this->GetBlogsId();
    }

    /**
     * Возвращает список всех не персональных блогов
     *
     * @return  array
     */
    public function GetBlogsId() {

        $aFilter = E::ModuleBlog()->GetNamedFilter('default');
        $aCriteria = array(
            'filter' => $aFilter,
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

        $aFilter = E::ModuleBlog()->GetNamedFilter('default');
        $aCriteria = array(
            'filter' => $aFilter,
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
        $aResult = array();
        if ($aRows = $this->oDb->select($sql, $nUserId, $nLimit)) {
            $aResult = E::GetEntityRows('Blog', $aRows);
        }
        return $aResult;
    }

    /**
     * Получает список блогов, которые создал пользователь
     *
     * @param int $nUserId   ID пользователя
     * @param int $nLimit    Ограничение на выборку элементов
     *
     * @return array
     */
    public function GetBlogsRatingSelf($nUserId, $nLimit) {

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
        $aResult = array();
        if ($aRows = $this->oDb->select($sql, $nUserId, $nLimit)) {
            $aResult = E::GetEntityRows('Blog', $aRows);
        }
        return $aResult;
    }

    /**
     * LS-compatibility
     */
    public function GetCloseBlogs($oUser = null) {

        return $this->GetCloseBlogsId($oUser);
    }

    /**
     * Returns array of IDs of blogs that closed for user
     *
     * @param ModuleUser_EntityUser $oUser
     *
     * @return array
     */
    public function GetCloseBlogsId($oUser = null) {

        // Gets an array of types of blogs that closed for user
        $aTypes = E::ModuleBlog()->GetCloseBlogTypes($oUser);

        // If array is not empty...
        if ($aTypes) {
            $aCriteria = array(
                'filter' => array(
                    //'blog_type' => 'close',
                    'blog_type' => $aTypes,
                ),
            );
            $aResult = $this->GetBlogsIdByCriteria($aCriteria);
            return $aResult['data'];
        }
        return array();
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
     * @param int|array|null $aBlogId - ID of blog | IDs of blogs
     *
     * @return bool
     */
    public function RecalculateCountTopic($aBlogId = null) {

        $sql = "
                UPDATE ?_blog b
                SET b.blog_count_topic = (
                    SELECT count(*)
                    FROM ?_topic t
                    WHERE
                        t.blog_id = b.blog_id
                    AND
                        t.topic_publish = 1
                )
                WHERE 1=1
                    { AND b.blog_id = ?d }
                    { AND b.blog_id IN (?a) }
            ";
        $bResult = $this->oDb->query($sql,
            (is_null($aBlogId) || is_array($aBlogId)) ? DBSIMPLE_SKIP : $aBlogId,
            (is_null($aBlogId) || is_int($aBlogId)) ? DBSIMPLE_SKIP : $aBlogId
        );
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
    public function GetBlogsIdByFilterPerPage($aFilter, $aOrder, &$iCount, $iCurrPage = null, $iPerPage = null) {

        $aCriteria = array(
            'filter' => array(),
            'order'  => array(),
        );
        if (!is_null($iPerPage)) {
            $iCurrPage = intval($iCurrPage);
            if ($iCurrPage < 1) {
                $iCurrPage = 1;
            }
            $aCriteria['limit'] = array(($iCurrPage - 1) * $iPerPage, $iPerPage);
        }

        if (isset($aFilter['type']) && !isset($aFilter['include_type'])) {
            $aCriteria['filter']['blog_type'] = $aFilter['type'];
            unset($aFilter['type']);
        } elseif (isset($aFilter['include_type'])) {
            $aCriteria['filter']['blog_type'] = $aFilter['include_type'];
            unset($aFilter['include_type']);
        }
        if (isset($aFilter['exclude_type'])) {
            $aCriteria['filter']['not_blog_type'] = $aFilter['exclude_type'];
            unset($aFilter['exclude_type']);
        }
        if (isset($aFilter['title'])) {
            if (strpos($aFilter['title'], '%') !== false) {
                $aCriteria['filter']['blog_title_like'] = $aFilter['title'];
            } else {
                $aCriteria['filter']['blog_title'] = $aFilter['title'];
            }
            unset($aFilter['title']);
        }
        if ($aFilter && is_array($aFilter)) {
            $aCriteria['filter'] = F::Array_Merge($aCriteria['filter'], $aFilter);
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
     * @return  ModuleBlog_BlogType[]
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

        // Получим типы контента
        $aResult = E::GetEntityRows('Blog_BlogType', $aRows);

        // Если вернули хотя бы один тип контента. то можно делать
        // дополнительные запросы
        if ($aResult) {

            $aBlogTypeKeys = array();
            foreach ($aResult as $oBlogType) {
                $aBlogTypeKeys[] = $oBlogType->getId();
            }

            // Сделаем дополнительные запросы по количеству и типам контента, если нужно
            $aStat = $this->GetBlogCountsByTypes();
            $aContentType = $this->GetBlogTypeContentByArrayId($aBlogTypeKeys);

            /**
             * Установим доп. данные в свойства типов контента
             *
             * @var int $iId
             * @var ModuleBlog_EntityBlogType $oBlogType
             */
            foreach ($aResult as $oBlogType) {
                if (isset($aContentType[$oBlogType->getId()])) {
                    $oBlogType->setContentTypes($aContentType[$oBlogType->getId()]);
                }

                $oBlogType->setBlogsCount(
                    isset($aStat[$oBlogType->getTypeCode()])
                        ? intval($aStat[$oBlogType->getTypeCode()])
                        : 0
                );
            }

        }

        return $aResult;
    }

    /**
     * Получает массив типов контента для укзанных в параметре типов блогов
     *
     * @param ModuleBlog_EntityBlogType[] $aBlogTypeId
     *
     * @return ModuleTopic_EntityContentType[][]
     */
    public function GetBlogTypeContentByArrayId($aBlogTypeId) {

        $sql =
            "SELECT
                  bct.blog_type_id blog_type_id,
                  ct.*
              FROM
                  ?_blog_type_content bct,
                  ?_content ct
              WHERE
                  ct.content_id = bct.content_id
                  AND bct.blog_type_id IN ( ?a )

                  -- Здесь такой манёвр: тип контента должен быть либо привязан к типу
                  -- блога по таблице ?_blog_type_content, либо, из соображений свместимости
                  -- с версией Alto 1.0, должен храниться в соответствующем свойстве типа блога
                  -- запрос на выборку из этого всего уникальных не делаю, поскольку варианта
                  -- тут два - либо контент в свойстве типа блога и тогда по нему работает
                  -- второй подзапрос, либо только в таблице связей - тогда работает первый.
              UNION
                  SELECT
                    bt.id blog_type_id,
                    ct.*
                  FROM
                    ?_blog_type bt, ?_content ct
                  WHERE
                    bt.content_type = ct.content_url AND bt.id IN ( ?a )";

        /** @var ModuleTopic_EntityContentType $aContentType */
        $aContentType = E::GetEntityRows(
            'Topic_ContentType',
            $this->oDb->select($sql, $aBlogTypeId, $aBlogTypeId)
        );

        $aResult = array();
        foreach ($aContentType as $oContentType) {
            $aResult[$oContentType->getBlogTypeId()][] = $oContentType;
    }

        return $aResult;
    }

    /**
     * Удаляет связанные с типом блога типы контента
     *
     * @param $iBlogTypeId
     * @return bool
     */
    public function DeleteBlogTypeContent($iBlogTypeId) {

        return $this->oDb->query(
            "DELETE FROM ?_blog_type_content WHERE blog_type_id = ?d",
            $iBlogTypeId
        ) !== false;

    }

    /**
     * Удаляет связанные с типом блога типы контента по массиву кодов типа блога
     *
     * @param $aBlogTypes
     * @return bool
     */
    public function DeleteBlogTypeContentByTypeCode($aBlogTypes) {

        return $this->oDb->query(
            "DELETE FROM
                ?_blog_type_content
            WHERE
                blog_type_id IN (
                  SELECT bt.id FROM ?_blog_type bt WHERE bt.type_code IN ( ?a )
                )",
            $aBlogTypes
        ) !== false;

    }

    /**
     * Сохраняет связь между типом блога и типом контента
     *
     * @param int $iBlogTypeId
     * @param int[]|ModuleTopic_EntityContentType[] $aContentType
     */
    public function SetBlogTypeContent($iBlogTypeId, $aContentType) {

        // Сначала удалим старые связи
        $this->DeleteBlogTypeContent($iBlogTypeId);

        // И создадим новые
        foreach ($aContentType as $iContentTypeId) {
            if (is_object($iContentTypeId)) {
                $iContentTypeId = $iContentTypeId->getId();
            }
            // Подставляем различные значения параметров.
            $this->oDb->query(
                'INSERT INTO ?_blog_type_content (blog_type_id, content_id) VALUES(?d, ?d)',
                $iBlogTypeId,
                $iContentTypeId
            );
        }

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
            SELECT
              DISTINCT blog_type AS ARRAY_KEY,
              Count(blog_id) AS blogs_count
            FROM ?_blog
            {WHERE blog_type IN (?a)}
            GROUP BY blog_type
            ORDER BY blog_type
            ";
        $aRows = $this->oDb->selectCol($sql, $aTypes ? $aTypes : DBSIMPLE_SKIP);
        return $aRows;
    }

    /**
     * Возвращает объект типа блога по ID
     *
     * @param $nBlogTypeId
     *
     * @return ModuleBlog_BlogType|null
     */
    public function GetBlogTypeById($nBlogTypeId) {

        $sql
            = "
            SELECT bt.*
            FROM ?_blog_type AS bt
            WHERE bt.id=?d
            ";
        $aRow = $this->oDb->selectRow($sql, $nBlogTypeId);
        if ($aRow) {

            /** @var ModuleBlog_EntityBlogType $oBlogType */
            $oBlogType = E::GetEntity('Blog_BlogType', $aRow);

            /** @var ModuleTopic_EntityContentType[] $aContentType */
            $aContentType = $this->GetBlogTypeContentByArrayId(array($oBlogType->getId()));

            if (isset($aContentType[$oBlogType->getId()])) {
                // Установим полученые типы контента типу блога
                $oBlogType->setContentTypes($aContentType[$oBlogType->getId()]);
            }

            return $oBlogType;

        }
        return null;
    }

    /**
     * Добавляет тип блога
     *
     * @param ModuleBlog_EntityBlogType $oBlogType
     *
     * @return bool
     */
    public function AddBlogType($oBlogType) {

        $sql = "
            INSERT INTO ?_blog_type
            (
                type_code,
                type_name,
                type_description,
                allow_add,
                min_rate_add,
                allow_list,
                min_rate_list,
                index_ignore,
                membership,
                acl_write,
                acl_read,
                acl_comment,
                min_rate_write,
                min_rate_read,
                min_rate_comment,
                content_type,
                active,
                norder,
                candelete
            )
            VALUES (
                ?:type_code,
                ?:type_name,
                ?:type_description,
                ?d:allow_add,
                ?f:min_rate_add,
                ?d:allow_list,
                ?f:min_rate_list,
                ?d:index_ignore,
                ?d:membership,
                ?d:acl_write,
                ?d:acl_read,
                ?d:acl_comment,
                ?f:min_rate_write,
                ?f:min_rate_read,
                ?f:min_rate_comment,
                ?:content_type,
                ?d:active,
                ?d:norder,
                ?d:candelete
            )
        ";
        $nId = $this->oDb->sqlQuery(
            $sql,
            array(
                 ':type_code'        => $oBlogType->getTypeCode(),
                 ':type_name'        => $oBlogType->getTypeName(),
                 ':type_description' => $oBlogType->getTypeDescription(),
                 ':allow_add'        => $oBlogType->getAllowAdd() ? 1 : 0,
                 ':min_rate_add'     => $oBlogType->getMinRateAdd(),
                 ':allow_list'       => $oBlogType->getAllowList() ? 1 : 0,
                 ':min_rate_list'    => $oBlogType->getMinRateList(),
                 ':index_ignore'     => $oBlogType->IsIndexIgnore() ? 1 : 0,
                 ':membership'       => $oBlogType->getMembership(),
                 ':acl_write'        => $oBlogType->getAclWrite(),
                 ':acl_read'         => $oBlogType->getAclRead(),
                 ':acl_comment'      => $oBlogType->getAclComment(),
                 ':min_rate_write'   => $oBlogType->getMinRateWrite(),
                 ':min_rate_read'    => $oBlogType->getMinRateRead(),
                 ':min_rate_comment' => $oBlogType->getMinRateComment(),
                 ':content_type'     => $oBlogType->getContentType(),
                 ':active'           => $oBlogType->IsActive() ? 1 : 0,
                 ':norder'           => $oBlogType->getNOrder(),
                 ':candelete'        => $oBlogType->CanDelete() ? 1 : 0,
                 ':id'               => $oBlogType->getId()
            )
        );

        /** @var int $iBlogTypeId Ид. Созданного типа блога*/
        $iBlogTypeId = $nId ? $nId : false;

        // Теперь зафиксируем типы контента для нашего типа блога
        if ($iBlogTypeId) {
            $this->SetBlogTypeContent($iBlogTypeId, $oBlogType->getContentTypes());
    }

        return $iBlogTypeId;
    }

    /**
     * Обновляет тип блога
     *
     * @param ModuleBlog_EntityBlogType $oBlogType
     *
     * @return bool
     */
    public function UpdateBlogType($oBlogType) {

        $sql
            = "
            UPDATE ?_blog_type
            SET
                type_code = ?:type_code,
                type_name = ?:type_name,
                type_description = ?:type_description,
                allow_add = ?d:allow_add,
                min_rate_add = ?f:min_rate_add,
                allow_list = ?d:allow_list,
                min_rate_list = ?f:min_rate_list,
                index_ignore = ?d:index_ignore,
                membership = ?d:membership,
                acl_write = ?d:acl_write,
                acl_read = ?d:acl_read,
                acl_comment = ?d:acl_comment,
                min_rate_write = ?f:min_rate_write,
                min_rate_read = ?f:min_rate_read,
                min_rate_comment = ?f:min_rate_comment,
                content_type = ?:content_type,
                active = ?d:active,
                norder = ?d:norder,
                candelete = ?d:candelete
            WHERE
                id = ?d:id
        ";
        $xResult = $this->oDb->sqlQuery(
            $sql,
            array(
                 ':type_code'        => $oBlogType->getTypeCode(),
                 ':type_name'        => $oBlogType->getTypeName(),
                 ':type_description' => $oBlogType->getTypeDescription(),
                 ':allow_add'        => $oBlogType->getAllowAdd() ? 1 : 0,
                 ':min_rate_add'     => $oBlogType->getMinRateAdd(),
                 ':allow_list'       => $oBlogType->getAllowList() ? 1 : 0,
                 ':min_rate_list'    => $oBlogType->getMinRateList(),
                 ':index_ignore'     => $oBlogType->IsIndexIgnore() ? 1 : 0,
                 ':membership'       => $oBlogType->getMembership(),
                 ':acl_write'        => $oBlogType->getAclWrite(),
                 ':acl_read'         => $oBlogType->getAclRead(),
                 ':acl_comment'      => $oBlogType->getAclComment(),
                 ':min_rate_write'   => $oBlogType->getMinRateWrite(),
                 ':min_rate_read'    => $oBlogType->getMinRateRead(),
                 ':min_rate_comment' => $oBlogType->getMinRateComment(),
                 ':content_type'     => $oBlogType->getContentType(),
                 ':active'           => $oBlogType->IsActive() ? 1 : 0,
                 ':norder'           => $oBlogType->getNOrder(),
                 ':candelete'        => $oBlogType->CanDelete() ? 1 : (is_null($oBlogType->CanDelete()) ? 1 : 0),
                 ':id'               => $oBlogType->getId()
            )
        );

        $bResult = $xResult !== false;

        // Теперь зафиксируем типы контента для нашего типа блога
        if ($bResult && $oBlogType->getContentTypes()) {
            $this->SetBlogTypeContent($oBlogType->getId(), $oBlogType->getContentTypes());
        } elseif (!$oBlogType->getContentTypes()) {
            $this->DeleteBlogTypeContent($oBlogType->getId());
    }


        return $bResult;
    }

    /**
     * @param array $aBlogTypes
     *
     * @return bool
     */
    public function DeleteBlogType($aBlogTypes) {

        if (!is_array($aBlogTypes)) {
            $aBlogTypes = array((string)$aBlogTypes);
        }
        $sql = "
            DELETE FROM ?_blog_type
            WHERE type_code IN(?a)
        ";
        $xResult = $this->oDb->query($sql, $aBlogTypes);

        $bResult = $xResult !== false;

        // Теперь зафиксируем типы контента для нашего типа блога
        if ($bResult) {
            $this->DeleteBlogTypeContentByTypeCode($aBlogTypes);
        }

        return $bResult;

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
                ?_topic as t
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
            $aBlogs = E::GetEntityRows('Blog', $aRows);
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
            if (substr($aFilter['blog_title_like'], -1) !== '%') {
                $aFilter['blog_title_like'] .= '%';
            }
        }
        if (isset($aFilter['exclude_type']) && !isset($aFilter['not_blog_type'])) {
            $aFilter['not_blog_type'] = $aFilter['exclude_type'];
        }
        if (isset($aFilter['include_type']) && !isset($aFilter['blog_type'])) {
            $aFilter['blog_type'] = $aFilter['include_type'];
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

        // Необходимость JOIN'ов
        $aBlogTypeFields = array(
            'allow_add', 'min_rate_add', 'allow_list', 'min_rate_list', 'acl_read', 'min_rate_read', 'acl_write',
            'min_rate_write', 'acl_comment', 'min_rate_comment', 'index_ignore', 'membership',
        );
        if ($aFilter && array_intersect(array_keys($aFilter), $aBlogTypeFields)) {
            $bBlogTypeJoin = true;
        } else {
            $bBlogTypeJoin = false;
        }
        $aBlogUserFields = array(
            'user_role',
        );
        if ($aFilter && array_intersect(array_keys($aFilter), $aBlogUserFields)) {
            $bBlogUserJoin = true;
        } else {
            $bBlogUserJoin = false;
        }

        $sql = "
            SELECT b.blog_id
            FROM ?_blog AS b
                { INNER JOIN ?_blog_type AS bt ON bt.type_code=b.blog_type AND 1=?d }
                { INNER JOIN ?_blog_user AS bu ON bu.blog_id=b.blog_id AND 1=?d }
            WHERE
                1 = 1
                { AND (b.blog_id = ?d) }
                { AND (b.blog_id IN (?a)) }
                { AND (b.blog_id NOT IN (?a)) }
                { AND (b.user_owner_id = ?d) }
                { AND (b.user_owner_id IN (?a)) }
                { AND (b.user_owner_id = ?d) }
                { AND (b.user_owner_id IN (?a)) }
                { AND (b.blog_type = ?) }
                { AND (b.blog_type IN (?a)) }
                { AND (b.blog_type != ?) }
                { AND (b.blog_type NOT IN (?a)) }
                { AND blog_url = ? }
                { AND blog_url IN(?a) }
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
                { AND (bu.user_role = ?d) }
        " . $sSqlOrder . ' ' . $sSqlLimit;
        $aData = $this->oDb->selectCol($sql,
            $bBlogTypeJoin ? 1 : DBSIMPLE_SKIP,
            $bBlogUserJoin ? 1 : DBSIMPLE_SKIP,
            (isset($aFilter['blog_id']) && !is_array($aFilter['blog_id'])) ? $aFilter['blog_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['blog_id']) && is_array($aFilter['blog_id'])) ? $aFilter['blog_id'] : DBSIMPLE_SKIP,
            isset($aFilter['not_blog_id']) ? $aFilter['not_blog_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['user_id']) && !is_array($aFilter['user_id'])) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['user_id']) && is_array($aFilter['user_id'])) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['user_owner_id']) && !is_array($aFilter['user_owner_id'])) ? $aFilter['user_owner_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['user_owner_id']) && is_array($aFilter['user_owner_id'])) ? $aFilter['user_owner_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['blog_type']) && !is_array($aFilter['blog_type'])) ? $aFilter['blog_type'] : DBSIMPLE_SKIP,
            (isset($aFilter['blog_type']) && is_array($aFilter['blog_type'])) ? $aFilter['blog_type'] : DBSIMPLE_SKIP,
            (isset($aFilter['not_blog_type']) && !is_array($aFilter['not_blog_type'])) ? $aFilter['not_blog_type'] : DBSIMPLE_SKIP,
            (isset($aFilter['not_blog_type']) && is_array($aFilter['not_blog_type'])) ? $aFilter['not_blog_type'] : DBSIMPLE_SKIP,
            (isset($aFilter['blog_url']) && !is_array($aFilter['blog_url'])) ? $aFilter['blog_url'] : DBSIMPLE_SKIP,
            (isset($aFilter['blog_url']) && is_array($aFilter['blog_url'])) ? $aFilter['blog_url'] : DBSIMPLE_SKIP,
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
            isset($aFilter['membership']) ? ($aFilter['membership'] ? 1 : 0) : DBSIMPLE_SKIP,
            isset($aFilter['user_role']) ? $aFilter['user_role'] : DBSIMPLE_SKIP
        );
        $aResult = array(
            'data' => $aData ? $aData : array(),
            'total' => -1,
        );
        if ($nCalcTotal) {
            if (($nOffset == 0) && ($nLimit > 0) && (sizeof($aResult['data']) < $nLimit)) {
                $aResult['total'] = sizeof($aResult['data']);
            } else {
                $sLastQuery = trim(E::ModuleDatabase()->GetLastQuery());
                $n = strpos($sLastQuery, ' LIMIT ');
                if ($n) {
                    $sql = str_replace('SELECT b.blog_id', 'SELECT COUNT(*) AS cnt', substr($sLastQuery, 0, $n));
                    $aData = $this->oDb->select($sql);
                    if ($aData) {
                        $aResult['total'] = $aData[0]['cnt'];
                    }
                }
            }
        }
        return $aResult;
    }

}

// EOF