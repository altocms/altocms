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
 * Модуль для работы с блогами
 *
 * @package modules.blog
 * @since   1.0
 */
class ModuleBlog extends Module {

    //  Возможные роли пользователя в блоге
    const BLOG_USER_ROLE_GUEST = 0;
    const BLOG_USER_ROLE_MEMBER = 1;
    const BLOG_USER_ROLE_MODERATOR = 2;
    const BLOG_USER_ROLE_ADMINISTRATOR = 4;
    const BLOG_USER_ROLE_OWNER = 8;
    const BLOG_USER_ROLE_NOTMEMBER = 16;
    const BLOG_USER_ROLE_BAN_FOR_COMMENT = 32;
    const BLOG_USER_ROLE_AUTHOR = 64;

    // BLOG_USER_ROLE_MEMBER | BLOG_USER_ROLE_MODERATOR | BLOG_USER_ROLE_ADMINISTRATOR | BLOG_USER_ROLE_OWNER | BLOG_USER_ROLE_AUTHOR
    const BLOG_USER_ROLE_SUBSCRIBER = 79;

    // LS-compatible //
    const BLOG_USER_ROLE_USER = 1;

    const BLOG_USER_JOIN_NONE = 0;
    const BLOG_USER_JOIN_FREE = 1;
    const BLOG_USER_JOIN_REQUEST = 2;
    const BLOG_USER_JOIN_INVITE = 4;

    const BLOG_USER_ACL_GUEST = 1;
    const BLOG_USER_ACL_USER = 2;
    const BLOG_USER_ACL_MEMBER = 4;

    //  Пользователь, приглашенный админом блога в блог
    const BLOG_USER_ROLE_INVITE = -1;

    //  Пользователь, отклонивший приглашение админа
    const BLOG_USER_ROLE_REJECT = -2;

    //  Забаненный в блоге пользователь
    const BLOG_USER_ROLE_BAN = -4;

    //  User sent request for subscribe to blog
    const BLOG_USER_ROLE_WISHES = -6;

    const BLOG_SORT_TITLE = 1;
    const BLOG_SORT_TITLE_PERSONAL = 2;

    /**
     * Объект маппера
     *
     * @var ModuleBlog_MapperBlog
     */
    protected $oMapper;

    /** @var  LS-compatible */
    protected $oMapperBlog;
    /**
     * Объект текущего пользователя
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent = null;

    protected $aAdditionalData = array('vote', 'owner' => array(), 'relation_user', 'media');

    protected $aBlogsFilter = array('exclude_type' => 'personal');


    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->oMapper = E::GetMapper(__CLASS__);
        $this->oUserCurrent = E::ModuleUser()->getUserCurrent();

        //  LS-compatible
        $this->oMapperBlog = $this->oMapper;
    }

    /**
     * Спавнение по наименованию, но персональные вперед
     *
     * @param object $oBlog1
     * @param object $oBlog2
     *
     * @return int
     */
    public function _compareByTitlePersonal($oBlog1, $oBlog2) {

        if (($oBlog1->getType() == 'personal' && $oBlog2->getType() == 'personal')
            || ($oBlog1->getType() != 'personal' && $oBlog2->getType() != 'personal')
        ) {
            return strcasecmp($oBlog1->getTitle(), $oBlog2->getTitle());
        }
        if ($oBlog1->getType() == 'personal') {
            return -1;
        } else {
            return 1;
        }
    }

    /**
     * Спавнение по наименованию
     *
     * @param object $oBlog1
     * @param object $oBlog2
     *
     * @return int
     */
    public function _compareByTitle($oBlog1, $oBlog2) {

        return strcasecmp($oBlog1->getTitle(), $oBlog2->getTitle());
    }

    /**
     * Сортировка блогов
     *
     * @param array $aBlogList
     * @param int   $nMode
     */
    protected function _sortByTitle(&$aBlogList, $nMode = self::BLOG_SORT_TITLE_PERSONAL) {

        if ($nMode == self::BLOG_SORT_TITLE_PERSONAL) {
            uasort($aBlogList, array($this, '_compareByTitlePersonal'));
        } elseif ($nMode == self::BLOG_SORT_TITLE) {
            uasort($aBlogList, array($this, '_compareByTitle'));
        }
    }

    public function getBlogUserRoleTextKeys() {

        $aResult = array(
            self::BLOG_USER_ROLE_MEMBER          => 'blog_user_role_member',
            self::BLOG_USER_ROLE_MODERATOR       => 'blog_user_role_moderator',
            self::BLOG_USER_ROLE_ADMINISTRATOR   => 'blog_user_role_administrator',
            self::BLOG_USER_ROLE_OWNER           => 'blog_user_role_owner',
            self::BLOG_USER_ROLE_NOTMEMBER       => 'blog_user_role_notmember',
            self::BLOG_USER_ROLE_BAN_FOR_COMMENT => 'blog_user_role_banned_for_comment',
            self::BLOG_USER_ROLE_AUTHOR          => 'blog_user_role_author',
            self::BLOG_USER_ROLE_INVITE          => 'blog_user_role_invite',
            self::BLOG_USER_ROLE_REJECT          => 'blog_user_role_reject',
            self::BLOG_USER_ROLE_BAN             => 'blog_user_role_banned',
            self::BLOG_USER_ROLE_WISHES          => 'blog_user_role_request',
        );

        return $aResult;
    }

    /**
     * @param int $iRole
     *
     * @return string
     */
    public function getBlogUserRoleName($iRole) {

        $aLangKeys = $this->getBlogUserRoleTextKeys();
        if (!empty($aLangKeys[$iRole])) {
            $sResult = E::ModuleLang()->get($aLangKeys[$iRole]);
        } else {
            $sResult = E::ModuleLang()->get('blog_user_role_other');
        }

        return $sResult;
    }

    /**
     * Получает дополнительные данные(объекты) для блогов по их ID
     *
     * @param array|int $aBlogsId   - Список ID блогов
     * @param array     $aAllowData - Список типов дополнительных данных, которые нужно получить для блогов
     * @param array     $aOrder     - Порядок сортировки
     *
     * @return array
     */
    public function getBlogsAdditionalData($aBlogsId, $aAllowData = null, $aOrder = null) {

        if (!$aBlogsId) {
            return array();
        }
        if (is_null($aAllowData)) {
            $aAllowData = $this->aAdditionalData;
        }
        $aAllowData = F::Array_FlipIntKeys($aAllowData);
        if (!is_array($aBlogsId)) {
            $aBlogsId = array($aBlogsId);
        }

        // * Получаем блоги
        $aBlogs = $this->getBlogsByArrayId($aBlogsId, $aOrder);
        if (!$aBlogs || (is_array($aAllowData) && empty($aAllowData))) {
            // additional data not required
            return $aBlogs;
        }

        $sCacheKey = 'Blog_GetBlogsAdditionalData_' . md5(serialize(array($aBlogsId, $aAllowData, $aOrder)));
        if (false !== ($data = E::ModuleCache()->get($sCacheKey, 'tmp'))) {
            return $data;
        }

        // * Формируем ID дополнительных данных, которые нужно получить
        $aUserId = array();
        foreach ($aBlogs as $oBlog) {
            if (isset($aAllowData['owner'])) {
                $aUserId[] = $oBlog->getOwnerId();
            }
        }

        // * Получаем дополнительные данные
        $aBlogUsers = array();
        $aBlogsVote = array();
        $aUsers = (isset($aAllowData['owner']) && is_array($aAllowData['owner']))
            ? E::ModuleUser()->getUsersAdditionalData($aUserId, $aAllowData['owner'])
            : E::ModuleUser()->getUsersAdditionalData($aUserId);

        if (isset($aAllowData['relation_user']) && $this->oUserCurrent) {
            $aBlogUsers = $this->getBlogUsersByArrayBlog($aBlogsId, $this->oUserCurrent->getId());
        }
        if (isset($aAllowData['vote']) && $this->oUserCurrent) {
            $aBlogsVote = E::ModuleVote()->getVoteByArray($aBlogsId, 'blog', $this->oUserCurrent->getId());
        }

        $aBlogTypes = $this->getBlogTypes();

        if (isset($aAllowData['media'])) {
            $aAvatars = E::ModuleUploader()->getMediaObjects('blog_avatar', $aBlogsId, null, array('target_id'));
        }

        // * Добавляем данные к результату - списку блогов
        /** @var ModuleBlog_EntityBlog $oBlog */
        foreach ($aBlogs as $oBlog) {
            if (isset($aUsers[$oBlog->getOwnerId()])) {
                $oBlog->setOwner($aUsers[$oBlog->getOwnerId()]);
            } else {
                $oBlog->setOwner(null); // или $oBlog->setOwner(new ModuleUser_EntityUser());
            }
            if (isset($aBlogUsers[$oBlog->getId()])) {
                $oBlog->setCurrentUserRole($aBlogUsers[$oBlog->getId()]->getUserRole());
            }
            if (isset($aBlogsVote[$oBlog->getId()])) {
                $oBlog->setVote($aBlogsVote[$oBlog->getId()]);
            } else {
                $oBlog->setVote(null);
            }
            if (isset($aBlogTypes[$oBlog->getType()])) {
                $oBlog->setBlogType($aBlogTypes[$oBlog->getType()]);
            }

            if (isset($aAllowData['media'])) {
                // Sets blogs avatars
                if (isset($aAvatars[$oBlog->getId()])) {
                    $oBlog->setMediaResources('blog_avatar', $aAvatars[$oBlog->getId()]);
                } else {
                    $oBlog->setMediaResources('blog_avatar', array());
                }
            }
        }
        // Saves only for executing session, so any additional tags no required
        E::ModuleCache()->Set($aBlogs, $sCacheKey, array(), 'P1D', 'tmp');

        return $aBlogs;
    }

    /**
     * Возвращает список блогов по ID
     *
     * @param array      $aBlogsId    Список ID блогов
     * @param array|null $aOrder     Порядок сортировки
     *
     * @return ModuleBlog_EntityBlog[]
     */
    public function getBlogsByArrayId($aBlogsId, $aOrder = null) {

        if (!$aBlogsId) {
            return array();
        }
        if (Config::Get('sys.cache.solid')) {
            return $this->getBlogsByArrayIdSolid($aBlogsId, $aOrder);
        }
        if (!is_array($aBlogsId)) {
            $aBlogsId = array($aBlogsId);
        }
        $aBlogsId = array_unique($aBlogsId);
        $aBlogs = array();
        $aBlogIdNotNeedQuery = array();

        // * Делаем мульти-запрос к кешу
        $aCacheKeys = F::Array_ChangeValues($aBlogsId, 'blog_');
        if (false !== ($data = E::ModuleCache()->get($aCacheKeys))) {
            // * проверяем что досталось из кеша
            foreach ($aCacheKeys as $iIndex => $sKey) {
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aBlogs[$data[$sKey]->getId()] = $data[$sKey];
                    } else {
                        $aBlogIdNotNeedQuery[] = $aBlogsId[$iIndex];
                    }
                }
            }
        }
        // * Смотрим каких блогов не было в кеше и делаем запрос в БД
        $aBlogIdNeedQuery = array_diff($aBlogsId, array_keys($aBlogs));
        $aBlogIdNeedQuery = array_diff($aBlogIdNeedQuery, $aBlogIdNotNeedQuery);
        $aBlogIdNeedStore = $aBlogIdNeedQuery;

        if ($aBlogIdNeedQuery) {
            if ($data = $this->oMapper->getBlogsByArrayId($aBlogIdNeedQuery)) {
                foreach ($data as $oBlog) {
                    // * Добавляем к результату и сохраняем в кеш
                    $aBlogs[$oBlog->getId()] = $oBlog;
                    E::ModuleCache()->Set($oBlog, "blog_{$oBlog->getId()}", array(), 'P4D');
                    $aBlogIdNeedStore = array_diff($aBlogIdNeedStore, array($oBlog->getId()));
                }
            }
        }

        // * Сохраняем в кеш запросы не вернувшие результата
        foreach ($aBlogIdNeedStore as $sId) {
            E::ModuleCache()->Set(null, "blog_{$sId}", array(), 'P4D');
        }
        // * Сортируем результат согласно входящему массиву
        $aBlogs = F::Array_SortByKeysArray($aBlogs, $aBlogsId);
        return $aBlogs;
    }

    /**
     * Возвращает список блогов по ID, но используя единый кеш
     *
     * @param array      $aBlogId    Список ID блогов
     * @param array|null $aOrder     Сортировка блогов
     *
     * @return array
     */
    public function getBlogsByArrayIdSolid($aBlogId, $aOrder = null) {

        if (!is_array($aBlogId)) {
            $aBlogId = array($aBlogId);
        }
        $aBlogId = array_unique($aBlogId);
        $aBlogs = array();
        $sCacheKey = 'blog_id_' . join(',', $aBlogId);
        if (false === ($data = E::ModuleCache()->get($sCacheKey))) {
            $data = $this->oMapper->getBlogsByArrayId($aBlogId, $aOrder);
            foreach ($data as $oBlog) {
                $aBlogs[$oBlog->getId()] = $oBlog;
            }
            E::ModuleCache()->Set($aBlogs, $sCacheKey, array('blog_update'), 'P1D');
            return $aBlogs;
        }
        return $data;
    }

    /**
     * Получить персональный блог юзера
     *
     * @param int $iUserId    ID пользователя
     *
     * @return ModuleBlog_EntityBlog|null
     */
    public function getPersonalBlogByUserId($iUserId) {

        $sCacheKey = 'blog_personal_' . $iUserId;
        if (false === ($iBlogId = E::ModuleCache()->get($sCacheKey))) {
            $iBlogId = $this->oMapper->getPersonalBlogByUserId($iUserId);
            if ($iBlogId) {
                E::ModuleCache()->Set($iBlogId, $sCacheKey, array("blog_update_{$iBlogId}", "user_update_{$iUserId}"), 'P30D');
            } else {
                E::ModuleCache()->Set(null, $sCacheKey, array('blog_update', 'blog_new', "user_update_{$iUserId}"), 'P30D');
            }
        }

        if ($iBlogId) {
            return $this->getBlogById($iBlogId);
        }
        return null;
    }

    /**
     * Получить блог по айдишнику(номеру)
     *
     * @param int $iBlogId    ID блога
     *
     * @return ModuleBlog_EntityBlog|null
     */
    public function getBlogById($iBlogId) {

        if (!intval($iBlogId)) {
            return null;
        }
        $aBlogs = $this->getBlogsAdditionalData($iBlogId);
        if (isset($aBlogs[$iBlogId])) {
            return $aBlogs[$iBlogId];
        }
        return null;
    }

    /**
     * Получить блог по УРЛу
     *
     * @param   string $sBlogUrl    URL блога
     *
     * @return  ModuleBlog_EntityBlog|null
     */
    public function getBlogByUrl($sBlogUrl) {

        $sCacheKey = 'blog_url_' . $sBlogUrl;
        if (false === ($iBlogId = E::ModuleCache()->get($sCacheKey))) {
            if ($iBlogId = $this->oMapper->getBlogsIdByUrl($sBlogUrl)) {
                E::ModuleCache()->Set($iBlogId, $sCacheKey, array("blog_update_{$iBlogId}"), 'P30D');
            } else {
                E::ModuleCache()->Set(null, $sCacheKey, array('blog_update', 'blog_new'), 'P30D');
            }
        }
        if ($iBlogId) {
            return $this->getBlogById($iBlogId);
        }
        return null;
    }

    /**
     * Returns array of blogs by URLs
     *
     * @param array $aBlogsUrl
     *
     * @return array
     */
    public function getBlogsByUrl($aBlogsUrl) {

        $sCacheKey = 'blogs_by_url_' . serialize($aBlogsUrl);
        if (false === ($aBlogs = E::ModuleCache()->get($sCacheKey))) {
            if ($aBlogsId = $this->oMapper->getBlogsIdByUrl($aBlogsUrl)) {
                $aBlogs = $this->getBlogsAdditionalData($aBlogsId);
                $aOrders = array_flip($aBlogsUrl);
                foreach($aBlogs as $oBlog) {
                    $oBlog->setProp('_order', $aOrders[$oBlog->getUrl()]);
                }
                $aBlogs = F::Array_SortEntities($aBlogs, '_order');
                $aAdditionalCacheKeys = F::Array_ChangeValues($aBlogsUrl, 'blog_update_');
            } else {
                $aBlogs = array();
                $aAdditionalCacheKeys = array();
            }
            $aAdditionalCacheKeys[] = 'blog_update';
            $aAdditionalCacheKeys[] = 'blog_new';
            E::ModuleCache()->Set(array(), $sCacheKey, $aAdditionalCacheKeys, 'P30D');
        }
        return $aBlogs;
    }

    /**
     * Получить блог по названию
     *
     * @param string $sTitle    Название блога
     *
     * @return ModuleBlog_EntityBlog|null
     */
    public function getBlogByTitle($sTitle) {

        if (false === ($id = E::ModuleCache()->get("blog_title_{$sTitle}"))) {
            if ($id = $this->oMapper->getBlogByTitle($sTitle)) {
                E::ModuleCache()->Set($id, "blog_title_{$sTitle}", array("blog_update_{$id}", 'blog_new'), 'P2D');
            } else {
                E::ModuleCache()->Set(null, "blog_title_{$sTitle}", array('blog_update', 'blog_new'), 60 * 60);
            }
        }
        return $this->getBlogById($id);
    }

    /**
     * Создаёт персональный блог
     *
     * @param ModuleUser_EntityUser $oUser    Пользователь
     *
     * @return ModuleBlog_EntityBlog|bool
     */
    public function CreatePersonalBlog(ModuleUser_EntityUser $oUser) {

        $oBlogType = $this->getBlogTypeByCode('personal');

        // Создаем персональный блог, только если это разрешено
        if ($oBlogType && $oBlogType->IsActive()) {
            $oBlog = E::GetEntity('Blog');
            $oBlog->setOwnerId($oUser->getId());
            $oBlog->setOwner($oUser);
            $oBlog->setTitle(E::ModuleLang()->get('blogs_personal_title') . ' ' . $oUser->getLogin());
            $oBlog->setType('personal');
            $oBlog->setDescription(E::ModuleLang()->get('blogs_personal_description'));
            $oBlog->setDateAdd(F::Now());
            $oBlog->setLimitRatingTopic(-1000);
            $oBlog->setUrl(null);
            $oBlog->setAvatar(null);
            return $this->AddBlog($oBlog);
        }
        return false;
    }

    /**
     * Добавляет блог
     *
     * @param ModuleBlog_EntityBlog $oBlog    Блог
     *
     * @return ModuleBlog_EntityBlog|bool
     */
    public function AddBlog(ModuleBlog_EntityBlog $oBlog) {

        if ($sId = $this->oMapper->AddBlog($oBlog)) {
            $oBlog->setId($sId);
            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags(array('blog_new'));


            // 1. Удалить значение target_tmp
            // Нужно затереть временный ключ в ресурсах, что бы в дальнейшем картнка не
            // воспринималась как временная.
            if ($sTargetTmp = E::ModuleSession()->getCookie(ModuleUploader::COOKIE_TARGET_TMP)) {
                // 2. Удалить куку.
                // Если прозошло сохранение вновь созданного топика, то нужно
                // удалить куку временной картинки. Если же сохранялся уже существующий топик,
                // то удаление куки ни на что влиять не будет.
                E::ModuleSession()->DelCookie(ModuleUploader::COOKIE_TARGET_TMP);

                // 3. Переместить фото
                $sTargetType = 'blog_avatar';
                $sTargetId = $sId;

                $aMresourceRel = E::ModuleMresource()->getMresourcesRelByTargetAndUser($sTargetType, 0, E::UserId());

                if ($aMresourceRel) {
                    $oResource = array_shift($aMresourceRel);
                    $sOldPath = $oResource->getFile();

                    //$oStoredFile = E::ModuleUploader()->Store($sOldPath, $sNewPath);
                    $oStoredFile = E::ModuleUploader()->StoreImage($sOldPath, $sTargetType, $sTargetId);
                    /** @var ModuleMresource_EntityMresource $oResource */
                    $oResource = E::ModuleMresource()->getMresourcesByUuid($oStoredFile->getUuid());
                    if ($oResource) {
                        $oResource->setUrl(E::ModuleMresource()->NormalizeUrl(E::ModuleUploader()->getTargetUrl($sTargetType, $sTargetId)));
                        $oResource->setType($sTargetType);
                        $oResource->setUserId(E::UserId());

                        $oResource = array($oResource);
                        E::ModuleMresource()->UnlinkFile($sTargetType, 0, E::UserId());
                        E::ModuleMresource()->AddTargetRel($oResource, $sTargetType, $sTargetId);

                        // 4. Обновим сведения об аватаре у блога для обеспечения обратной
                        // совместимости. Могут быть плагины которые берут картинку непосредственно
                        // из свойства блога, а не через модуль uploader
                        $oBlog->setAvatar($oBlog->getAvatar());
                        $this->UpdateBlog($oBlog);
                    }
                }
            }

            return $oBlog;
        }
        return false;
    }

    /**
     * Обновляет блог
     *
     * @param ModuleBlog_EntityBlog $oBlog    Блог
     *
     * @return ModuleBlog_EntityBlog|bool
     */
    public function UpdateBlog(ModuleBlog_EntityBlog $oBlog) {

        $oBlog->setDateEdit(F::Now());
        $bResult = $this->oMapper->UpdateBlog($oBlog);
        if ($bResult) {
            $aTags = array('blog_update', "blog_update_{$oBlog->getId()}", 'topic_update');
            if ($oBlog->getOldType() && $oBlog->getOldType() != $oBlog->getType()) {
                // Списк авторов блога
                $aUsersId = $this->getAuthorsIdByBlog($oBlog->getId());
                foreach($aUsersId as $nUserId) {
                    $aTags[] = 'topic_update_user_' . $nUserId;
                }
            }
            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags($aTags);
            E::ModuleCache()->Delete("blog_{$oBlog->getId()}");
            return true;
        }
        return false;
    }

    /**
     * Добавляет отношение юзера к блогу, по сути присоединяет к блогу
     *
     * @param ModuleBlog_EntityBlogUser $oBlogUser    Объект связи(отношения) блога с пользователем
     *
     * @return bool
     */
    public function AddRelationBlogUser(ModuleBlog_EntityBlogUser $oBlogUser) {

        if ($this->oMapper->AddRelationBlogUser($oBlogUser)) {
            E::ModuleCache()->CleanByTags(
                array("blog_relation_change_{$oBlogUser->getUserId()}",
                      "blog_relation_change_blog_{$oBlogUser->getBlogId()}")
            );
            E::ModuleCache()->Delete("blog_relation_user_{$oBlogUser->getBlogId()}_{$oBlogUser->getUserId()}");
            return true;
        }
        return false;
    }

    /**
     * Обновляет отношения пользователя с блогом
     *
     * @param ModuleBlog_EntityBlogUser $oBlogUser    Объект отновшения
     *
     * @return bool
     */
    public function UpdateRelationBlogUser(ModuleBlog_EntityBlogUser $oBlogUser) {

        $bResult = $this->oMapper->UpdateRelationBlogUser($oBlogUser);
        if ($bResult) {
            E::ModuleCache()->CleanByTags(
                array("blog_relation_change_{$oBlogUser->getUserId()}",
                      "blog_relation_change_blog_{$oBlogUser->getBlogId()}")
            );
            E::ModuleCache()->Delete("blog_relation_user_{$oBlogUser->getBlogId()}_{$oBlogUser->getUserId()}");
            return $bResult;
        }
    }

    /**
     * Удалет отношение юзера к блогу, по сути отключает от блога
     *
     * @param ModuleBlog_EntityBlogUser $oBlogUser    Объект связи(отношения) блога с пользователем
     *
     * @return bool
     */
    public function DeleteRelationBlogUser(ModuleBlog_EntityBlogUser $oBlogUser) {

        if ($this->oMapper->DeleteRelationBlogUser($oBlogUser)) {
            E::ModuleCache()->CleanByTags(
                array("blog_relation_change_{$oBlogUser->getUserId()}",
                      "blog_relation_change_blog_{$oBlogUser->getBlogId()}")
            );
            E::ModuleCache()->Delete("blog_relation_user_{$oBlogUser->getBlogId()}_{$oBlogUser->getUserId()}");
            return true;
        }
        return false;
    }

    /**
     * Получает список блогов по хозяину
     *
     * @param int  $iUserId          ID пользователя
     * @param bool $bReturnIdOnly    Возвращать только ID блогов или полные объекты
     *
     * @return array
     */
    public function getBlogsByOwnerId($iUserId, $bReturnIdOnly = false) {

        $iUserId = intval($iUserId);
        if (!$iUserId) {
            return array();
        }

        $sCacheKey = 'blogs_by_owner' . $iUserId;
        if (false === ($data = E::ModuleCache()->get($sCacheKey))) {
            $data = $this->oMapper->getBlogsIdByOwnerId($iUserId);
            E::ModuleCache()->Set($data, $sCacheKey, array('blog_update', 'blog_new', "user_update_{$iUserId}"), 'P30D');
        }

        // * Возвращаем только иденитификаторы
        if ($bReturnIdOnly) {
            return $data;
        }
        if ($data) {
            $data = $this->getBlogsAdditionalData($data);
        }
        return $data;
    }

    /**
     * Получает список всех НЕ персональных блогов
     *
     * @param bool|array $xReturnOptions  true - Возвращать только ID блогов, array - Доп.данные блога
     *
     * @return ModuleBlog_EntityBlog[]
     */
    public function getBlogs($xReturnOptions = null) {

        $sCacheKey = 'Blog_GetBlogsId';
        if (false === ($data = E::ModuleCache()->get($sCacheKey))) {
            $data = $this->oMapper->getBlogsId();
            E::ModuleCache()->Set($data, $sCacheKey, array('blog_update', 'blog_new'), 'P1D');
        }

        // * Возвращаем только иденитификаторы
        if ($xReturnOptions === true) {
            return $data;
        }
        if ($data) {
            if (is_array($xReturnOptions)) {
                $aAdditionalData = $xReturnOptions;
            } else {
                $aAdditionalData = null;
            }
            $data = $this->getBlogsAdditionalData($data, $aAdditionalData);
        }
        return $data;
    }

    /**
     * Получает список пользователей блога.
     * Если роль не указана, то считаем что поиск производиться по положительным значениям (статусом выше GUEST).
     *
     * @param int            $iBlogId  ID блога
     * @param int|array|bool $xRole    Роль пользователей в блоге (null == subscriber only; true === all roles)
     * @param int            $iPage    Номер текущей страницы
     * @param int            $iPerPage Количество элементов на одну страницу
     *
     * @return array
     */
    public function getBlogUsersByBlogId($iBlogId, $xRole = null, $iPage = 1, $iPerPage = 100) {

        $aFilter = array(
            'blog_id' => $iBlogId,
        );
        if ($xRole === true) {
            $aFilter['user_all_role'] = true;
        } elseif (is_int($xRole) || is_array($xRole)) {
            $aFilter['user_role'] = $xRole;
        }
        if (is_null($iPage)) {
            $iPerPage = null;
        }
        $sCacheKey = 'blog_relation_user_by_filter_' . serialize($aFilter) . '_' . $iPage . '_' . $iPerPage;
        if (false === ($data = E::ModuleCache()->get($sCacheKey))) {
            $data = array(
                'collection' => $this->oMapper->getBlogUsers($aFilter, $iCount, $iPage, $iPerPage),
                'count'      => $iCount,
            );
            E::ModuleCache()->Set($data, $sCacheKey, array("blog_relation_change_blog_{$iBlogId}"), 'P3D');
        }

        // * Достаем дополнительные данные, для этого формируем список юзеров и делаем мульти-запрос
        if ($data['collection']) {
            $aUserId = array();
            /** @var ModuleBlog_EntityBlogUser $oBlogUser */
            foreach ($data['collection'] as $oBlogUser) {
                $aUserId[] = $oBlogUser->getUserId();
            }
            $aUsers = E::ModuleUser()->getUsersAdditionalData($aUserId);
            $aBlogs = E::ModuleBlog()->getBlogsAdditionalData($iBlogId);

            $aResults = array();
            foreach ($data['collection'] as $oBlogUser) {
                if (isset($aUsers[$oBlogUser->getUserId()])) {
                    $oBlogUser->setUser($aUsers[$oBlogUser->getUserId()]);
                } else {
                    $oBlogUser->setUser(null);
                }
                if (isset($aBlogs[$oBlogUser->getBlogId()])) {
                    $oBlogUser->setBlog($aBlogs[$oBlogUser->getBlogId()]);
                } else {
                    $oBlogUser->setBlog(null);
                }
                $aResults[$oBlogUser->getUserId()] = $oBlogUser;
            }
            $data['collection'] = $aResults;
        }
        return $data;
    }

    public function getBlogUsersByUserId($iUserId, $xRole = null, $bReturnIdOnly = false) {

        return $this->getBlogUserRelsByUserId($iUserId, $xRole, $bReturnIdOnly);
    }

    /**
     * Получает отношения юзера к блогам (подписан на блог или нет)
     *
     * @param int       $iUserId          ID пользователя
     * @param int|int[] $xRole            Роль пользователя в блоге
     * @param bool      $bReturnIdOnly    Возвращать только ID блогов или полные объекты
     *
     * @return int[]|ModuleBlog_EntityBlogUser[]
     */
    public function getBlogUserRelsByUserId($iUserId, $xRole = null, $bReturnIdOnly = false) {

        $aFilter = array(
            'user_id' => $iUserId
        );
        if ($xRole !== null) {
            $aFilter['user_role'] = $xRole;
        }
        $sCacheKey = 'blog_relation_user_by_filter_' . serialize($aFilter);
        if (false === ($aBlogUserRels = E::ModuleCache()->get($sCacheKey, 'tmp,'))) {
            $aBlogUserRels = $this->oMapper->getBlogUsers($aFilter);
            E::ModuleCache()->Set(
                $aBlogUserRels, $sCacheKey, array('blog_update', "blog_relation_change_{$iUserId}"), 'P3D', ',tmp'
            );
        }
        //  Достаем дополнительные данные, для этого формируем список блогов и делаем мульти-запрос
        $aBlogId = array();
        $aResult = array();
        if ($aBlogUserRels) {
            foreach ($aBlogUserRels as $oBlogUser) {
                $aBlogId[] = $oBlogUser->getBlogId();
            }
            //  Если указано возвращать полные объекты
            if (!$bReturnIdOnly) {
                $aUsers = E::ModuleUser()->getUsersAdditionalData($iUserId);
                $aBlogs = E::ModuleBlog()->getBlogsAdditionalData($aBlogId);
                foreach ($aBlogUserRels as $oBlogUser) {
                    if (isset($aUsers[$oBlogUser->getUserId()])) {
                        $oBlogUser->setUser($aUsers[$oBlogUser->getUserId()]);
                    } else {
                        $oBlogUser->setUser(null);
                    }
                    if (isset($aBlogs[$oBlogUser->getBlogId()])) {
                        $oBlogUser->setBlog($aBlogs[$oBlogUser->getBlogId()]);
                    } else {
                        $oBlogUser->setBlog(null);
                    }
                    $aResult[$oBlogUser->getBlogId()] = $oBlogUser;
                }
            }
        }
        return ($bReturnIdOnly) ? $aBlogId : $aResult;
    }

    public function getBlogUserByBlogIdAndUserId($iBlogId, $iUserId) {

        return $this->getBlogUserRelByBlogIdAndUserId($iBlogId, $iUserId);
    }

    /**
     * Состоит ли юзер в конкретном блоге
     *
     * @param int $iBlogId    ID блога
     * @param int $iUserId    ID пользователя
     *
     * @return ModuleBlog_EntityBlogUser|null
     */
    public function getBlogUserRelByBlogIdAndUserId($iBlogId, $iUserId) {

        if ($aBlogUser = $this->getBlogUsersByArrayBlog($iBlogId, $iUserId)) {
            if (isset($aBlogUser[$iBlogId])) {
                return $aBlogUser[$iBlogId];
            }
        }
        return null;
    }

    public function getBlogUsersByArrayBlog($aBlogId, $iUserId) {

        return $this->getBlogUserRelsByArrayBlog($aBlogId, $iUserId);
    }

    /**
     * Получить список отношений блог-юзер по списку айдишников
     *
     * @param array|int $aBlogId Список ID блогов
     * @param int       $iUserId ID пользователя
     *
     * @return ModuleBlog_EntityBlogUser[]
     */
    public function getBlogUserRelsByArrayBlog($aBlogId, $iUserId) {

        if (!$aBlogId) {
            return array();
        }
        if (Config::Get('sys.cache.solid')) {
            return $this->getBlogUsersByArrayBlogSolid($aBlogId, $iUserId);
        }
        if (!is_array($aBlogId)) {
            $aBlogId = array(intval($aBlogId));
        }
        $aBlogId = array_unique($aBlogId);
        $aBlogUsers = array();
        $aBlogIdNotNeedQuery = array();

        // * Делаем мульти-запрос к кешу
        $aCacheKeys = F::Array_ChangeValues($aBlogId, 'blog_relation_user_', '_' . $iUserId);
        if (false !== ($data = E::ModuleCache()->get($aCacheKeys))) {
            // * проверяем что досталось из кеша
            foreach ($aCacheKeys as $iIndex => $sKey) {
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aBlogUsers[$data[$sKey]->getBlogId()] = $data[$sKey];
                    } else {
                        $aBlogIdNotNeedQuery[] = $aBlogId[$iIndex];
                    }
                }
            }
        }
        // * Смотрим каких блогов не было в кеше и делаем запрос в БД
        $aBlogIdNeedQuery = array_diff($aBlogId, array_keys($aBlogUsers));
        $aBlogIdNeedQuery = array_diff($aBlogIdNeedQuery, $aBlogIdNotNeedQuery);
        $aBlogIdNeedStore = $aBlogIdNeedQuery;

        if ($aBlogIdNeedQuery) {
            if ($data = $this->oMapper->getBlogUsersByArrayBlog($aBlogIdNeedQuery, $iUserId)) {
                foreach ($data as $oBlogUser) {
                    // * Добавляем к результату и сохраняем в кеш
                    $aBlogUsers[$oBlogUser->getBlogId()] = $oBlogUser;
                    E::ModuleCache()->Set(
                        $oBlogUser, "blog_relation_user_{$oBlogUser->getBlogId()}_{$oBlogUser->getUserId()}", array(), 'P4D'
                    );
                    $aBlogIdNeedStore = array_diff($aBlogIdNeedStore, array($oBlogUser->getBlogId()));
                }
            }
        }

        // * Сохраняем в кеш запросы не вернувшие результата
        foreach ($aBlogIdNeedStore as $sId) {
            E::ModuleCache()->Set(null, "blog_relation_user_{$sId}_{$iUserId}", array(), 'P4D');
        }

        // * Сортируем результат согласно входящему массиву
        $aBlogUsers = F::Array_SortByKeysArray($aBlogUsers, $aBlogId);

        return $aBlogUsers;
    }

    /**
     * Получить список отношений блог-юзер по списку айдишников используя общий кеш
     *
     * @param array $aBlogId    Список ID блогов
     * @param int   $iUserId    ID пользователя
     *
     * @return array
     */
    public function getBlogUsersByArrayBlogSolid($aBlogId, $iUserId) {

        if (!is_array($aBlogId)) {
            $aBlogId = array($aBlogId);
        }
        $aBlogId = array_unique($aBlogId);
        $aBlogUsers = array();
        $sCacheKey = 'blog_relation_user_' . $iUserId . '_id_' . join(',', $aBlogId);
        if (false === ($data = E::ModuleCache()->get($sCacheKey))) {
            $data = $this->oMapper->getBlogUsersByArrayBlog($aBlogId, $iUserId);
            foreach ($data as $oBlogUser) {
                $aBlogUsers[$oBlogUser->getBlogId()] = $oBlogUser;
            }
            E::ModuleCache()->Set(
                $aBlogUsers, $sCacheKey,
                array('blog_update', "blog_relation_change_{$iUserId}"), 'P1D'
            );
            return $aBlogUsers;
        }
        return $data;
    }

    /**
     * Возвращает список ID пользователей, являющихся авторами в блоге
     *
     * @param $xBlogId
     *
     * @return array
     */
    public function getAuthorsIdByBlog($xBlogId) {

        $nBlogId = $this->_entityId($xBlogId);
        if ($nBlogId) {
            $sCacheKey = 'authors_id_by_blog_' . $nBlogId;
            if (false === ($data = E::ModuleCache()->get($sCacheKey))) {
                $data = $this->oMapper->getAuthorsIdByBlogId($nBlogId);
                E::ModuleCache()->Set($data, $sCacheKey, array('blog_update', 'blog_new', 'topic_new', 'topic_update'), 'P1D');
            }
            return $data;
        }
        return array();
    }

    public function SetBlogsFilter($aFilter) {

        $this->aBlogsFilter = $aFilter;
    }

    public function getBlogsFilter() {

        return $this->aBlogsFilter;
    }

    /**
     * Возвращает список блогов по фильтру
     *
     * @param array $aFilter    Фильтр выборки блогов
     * @param int   $iPage      Номер текущей страницы
     * @param int   $iPerPage   Количество элементов на одну страницу
     * @param array $aAllowData Список типов данных, которые нужно подтянуть к списку блогов
     *
     * @return array('collection'=>array,'count'=>int)
     *
     * Old interface: GetBlogsByFilter($aFilter, $aOrder, $iPage, $iPerPage, $aAllowData = null)
     */
    public function getBlogsByFilter($aFilter, $iPage, $iPerPage, $aAllowData = null) {

        // Old interface compatibility
        if (!isset($aFilter['order']) && is_numeric($iPerPage) && is_numeric($aAllowData)) {
            $aOrder = $iPage;
            $iPage = $iPerPage;
            $iPerPage = $aAllowData;
            if (func_num_args() == 5) {
                $aAllowData = func_get_arg(4);
            } else {
                $aAllowData = null;
            }
        } else {
            $aOrder = (isset($aFilter['order']) ? (array)$aFilter['order'] : array());
        }
        if (is_null($aAllowData)) {
            $aAllowData = array('owner' => array(), 'relation_user');
        }
        $sCacheKey = 'blog_filter_' . serialize($aFilter) . serialize($aOrder) . "_{$iPage}_{$iPerPage}";
        if (false === ($data = E::ModuleCache()->get($sCacheKey))) {
            $data = array(
                'collection' => $this->oMapper->getBlogsIdByFilterPerPage($aFilter, $aOrder, $iCount, $iPage, $iPerPage),
                'count'      => $iCount
            );
            E::ModuleCache()->Set($data, $sCacheKey, array('blog_update', 'blog_new'), 'P2D');
        }
        if ($data['collection']) {
            $data['collection'] = $this->getBlogsAdditionalData($data['collection'], $aAllowData);
        }
        return $data;
    }

    /**
     * Return filter for blog list by name and params
     *
     * @param string $sFilterName
     * @param array  $aParams
     *
     * @return array
     */
    public function getNamedFilter($sFilterName, $aParams = array()) {

        $aFilter = $this->getBlogsFilter();
        $aFilter['include_type'] = $this->getAllowBlogTypes(E::User(), 'list', true);
        switch ($sFilterName) {
            case 'top':
                $aFilter['order'] = array('blog_rating' => 'desc');
                break;
            default:
                break;
        }
        if (!empty($aParams['exclude_type'])) {
            $aFilter['exclude_type'] = $aParams['exclude_type'];
        }
        if (!empty($aParams['owner_id'])) {
            $aFilter['user_owner_id'] = $aParams['owner_id'];
        }

        return $aFilter;
    }

    /**
     * Получает список блогов по рейтингу
     *
     * @param int $iPage       Номер текущей страницы
     * @param int $iPerPage    Количество элементов на одну страницу
     *
     * @return array('collection'=>array,'count'=>int)
     */
    public function getBlogsRating($iPage, $iPerPage) {

        $aFilter = $this->getNamedFilter('top');
        return $this->getBlogsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Список подключенных блогов по рейтингу
     *
     * @param int $iUserId    ID пользователя
     * @param int $iLimit     Ограничение на количество в ответе
     *
     * @return array
     */
    public function getBlogsRatingJoin($iUserId, $iLimit) {

        $sCacheKey = "blog_rating_join_{$iUserId}_{$iLimit}";
        if (false === ($data = E::ModuleCache()->get($sCacheKey))) {
            $data = $this->oMapper->getBlogsRatingJoin($iUserId, $iLimit);
            E::ModuleCache()->Set($data, $sCacheKey, array('blog_update', "blog_relation_change_{$iUserId}"), 'P1D');
        }
        return $data;
    }

    /**
     * Список своих блогов по рейтингу
     *
     * @param int $iUserId    ID пользователя
     * @param int $iLimit     Ограничение на количество в ответе
     *
     * @return array
     */
    public function getBlogsRatingSelf($iUserId, $iLimit) {

        $aFilter = $this->getNamedFilter('top', array('owner_id' => $iUserId));
        $aResult = $this->getBlogsByFilter($aFilter, 1, $iLimit);

        return $aResult['collection'];
    }

    /**
     * Получает список блогов в которые может постить юзер
     *
     * @param ModuleUser_EntityUser $oUser - Объект пользователя
     * @param bool                  $bSortByTitle
     *
     * @return array
     */
    public function getBlogsAllowByUser($oUser, $bSortByTitle = true) {

        return $this->getBlogsAllowTo('write', $oUser, null, false, $bSortByTitle);
    }

    /**
     * Получает список блогов, которые доступны пользователю для заданного действия.
     * Или проверяет на заданное действие конкретный блог
     *
     * @param string                    $sAllow
     * @param ModuleUser_EntityUser     $oUser
     * @param int|ModuleBlog_EntityBlog $xBlog
     * @param bool                      $bCheckOnly
     * @param bool                      $bSortByTitle
     *
     * @return array|bool
     */
    public function getBlogsAllowTo($sAllow, $oUser, $xBlog = null, $bCheckOnly = false, $bSortByTitle = true) {

        if (empty($oUser)) {
            return null;
        }

        if (is_object($xBlog)) {
            $iRequestBlogId = intval($xBlog->getId());
        } else {
            $iRequestBlogId = intval($xBlog);
        }
        if (!$iRequestBlogId && $bCheckOnly) {
            return false;
        }

        $sCacheKeyAll = E::ModuleCache()->Key('blogs_allow_to_', $sAllow, $oUser->getId(), $iRequestBlogId);
        $sCacheKeySorted = $sCacheKeyAll . '_sort';
        $sCacheKeyChecked = $sCacheKeyAll . '_check';
        if ($bCheckOnly) {
            // Если только проверка прав, то проверяем временный кеш
            if (is_int($xCacheResult = E::ModuleCache()->get($sCacheKeyChecked, 'tmp'))) {
                return $xCacheResult;
            }
            if (($xCacheResult = E::ModuleCache()->get($sCacheKeySorted, 'tmp,')) && ($xCacheResult !== false)) {
                // see sorted result in cache
                $xResult = !empty($xCacheResult[$iRequestBlogId]);
            } elseif (($xCacheResult = E::ModuleCache()->get($sCacheKeyAll, 'tmp,')) && ($xCacheResult !== false)) {
                // see unsorted result in cache
                $xResult = !empty($xCacheResult[$iRequestBlogId]);
            } else {
                $xResult = $this->_getBlogsAllowTo($sAllow, $oUser, $xBlog, true);
            }
            // Чтоб не было ложных сробатываний, используем в этом кеше числовое значение
            E::ModuleCache()->Set(!empty($xResult) ? 1 : 0, $sCacheKeyChecked, array('blog_update', 'user_update'), 0, 'tmp');

            return $xResult;
        }

        if ($bSortByTitle) {
            // see sorted blogs in cache
            if (($xCacheResult = E::ModuleCache()->get($sCacheKeySorted, 'tmp,')) && ($xCacheResult !== false)) {
                return $xCacheResult;
            }
        }

        // see unsorted blogs in cache
        $xCacheResult = E::ModuleCache()->get($sCacheKeyAll, 'tmp,');
        if ($xCacheResult !== false) {
            if ($bSortByTitle) {
                $this->_sortByTitle($xCacheResult);
                E::ModuleCache()->Set($xCacheResult, $sCacheKeySorted, array('blog_update', 'user_update'), 'P10D', ',tmp');
            }
            return $xCacheResult;
        }

        $aAllowBlogs = $this->_getBlogsAllowTo($sAllow, $oUser, $xBlog, false);
        if ($bSortByTitle) {
            $this->_sortByTitle($aAllowBlogs);
            E::ModuleCache()->Set($aAllowBlogs, $sCacheKeySorted, array('blog_update', 'user_update'), 'P10D', ',tmp');
        } else {
            E::ModuleCache()->Set($aAllowBlogs, $sCacheKeyAll, array('blog_update', 'user_update'), 'P10D', ',tmp');
        }

        return $aAllowBlogs;
    }


    /**
     * @param string                    $sAllow
     * @param ModuleUser_EntityUser     $oUser
     * @param int|ModuleBlog_EntityBlog $xBlog
     * @param bool                      $bCheckOnly
     *
     * @return array|bool|mixed|ModuleBlog_EntityBlog[]
     */
    protected function _getBlogsAllowTo($sAllow, $oUser, $xBlog = null, $bCheckOnly = false) {

        /** @var ModuleBlog_EntityBlog $oRequestBlog */
        $oRequestBlog = null;
        if (is_object($xBlog)) {
            $iRequestBlogId = intval($xBlog->getId());
            $oRequestBlog = $xBlog;
        } else {
            $iRequestBlogId = intval($xBlog);
        }

        if ($oUser->isAdministrator() || $oUser->isModerator()) {
            // Если админ и если проверка на конкретный блог, то возвращаем без проверки
            if ($iRequestBlogId) {
                return $iRequestBlogId;
            }
            $aAdditionalData = array('relation_user');
            $aAllowBlogs = $this->getBlogs($aAdditionalData);
            if ($iRequestBlogId) {
                return isset($aAllowBlogs[$iRequestBlogId]) ? $aAllowBlogs[$iRequestBlogId] : array();
            }
            return $aAllowBlogs;
        }

        // User is owner of the blog
        if ($oRequestBlog && $oRequestBlog->getOwnerId() == $oUser->getId()) {
            return $oRequestBlog;
        }

        // Блоги, созданные пользователем
        $aAllowBlogs = $this->getBlogsByOwnerId($oUser->getId());
        if ($iRequestBlogId && isset($aAllowBlogs[$iRequestBlogId])) {
            return $aAllowBlogs[$iRequestBlogId];
        }

        // Блоги, в которых состоит пользователь
        if ($iRequestBlogId) {
            // Requests one blog
            $aBlogUsers = $this->getBlogUsersByArrayBlog($iRequestBlogId, $oUser->getId());
            if ($oBlogUser = reset($aBlogUsers)) {
                if (!$oBlogUser->getBlog()) {
                    if (!$oRequestBlog) {
                        $oRequestBlog = $this->getBlogById($iRequestBlogId);
                    }
                    $oBlogUser->setBlog($oRequestBlog);
                }
            }
        } else {
            // Requests any allowed blogs
            $aBlogUsers = $this->getBlogUsersByUserId($oUser->getId());
        }

        foreach ($aBlogUsers as $oBlogUser) {
            /** @var ModuleBlog_EntityBlog $oBlog */
            $oBlog = $oBlogUser->getBlog();
            /** @var ModuleBlog_EntityBlogType $oBlogType */
            $oBlogType = $oBlog->getBlogType();

            // админа и модератора блога не проверяем
            if ($oBlogUser->IsBlogAdministrator() || $oBlogUser->IsBlogModerator()) {
                $aAllowBlogs[$oBlog->getId()] = $oBlog;
            } elseif (($oBlogUser->getUserRole() !== self::BLOG_USER_ROLE_NOTMEMBER) && ($oBlogUser->getUserRole() > self::BLOG_USER_ROLE_GUEST)) {
                $bAllow = false;
                if ($oBlogType) {
                    if ($sAllow == 'write') {
                        $bAllow = ($oBlogType->getAclWrite(self::BLOG_USER_ACL_MEMBER)
                                && $oBlogType->getMinRateWrite() <= $oUser->getRating())
                            || E::ModuleACL()->CheckBlogEditContent($oBlog, $oUser);
                    } elseif ($sAllow == 'read') {
                        $bAllow = $oBlogType->getAclRead(self::BLOG_USER_ACL_MEMBER)
                            && $oBlogType->getMinRateRead() <= $oUser->getRating();
                    } elseif ($sAllow == 'comment') {
                        $bAllow = $oBlogType->getAclComment(self::BLOG_USER_ACL_MEMBER)
                            && $oBlogType->getMinRateComment() <= $oUser->getRating();
                    }
                    if ($bAllow) {
                        $aAllowBlogs[$oBlog->getId()] = $oBlog;
                    }
                }
            }
            // Если задан конкретный блог и он найден, то проверять больше не нужно
            if ($iRequestBlogId && isset($aAllowBlogs[$iRequestBlogId])) {
                return $aAllowBlogs[$iRequestBlogId];
            }
        }

        $aFilter = array();
        if ($sAllow == 'list') {
            // Blogs which user can list
            $aFilter['allow_list'] = true;
        } elseif ($sAllow == 'read') {
            // Blogs which can be read without subscribing
            $aFilter = array(
                'acl_read'      => self::BLOG_USER_ACL_USER,
                'min_rate_read' => $oUser->getUserRating(),
            );
        } elseif ($sAllow == 'comment') {
            // Blogs in which user can comment without subscription
            $aFilter = array(
                'acl_comment'      => self::BLOG_USER_ACL_USER,
                'min_rate_comment' => $oUser->getUserRating(),
            );
        } elseif ($sAllow == 'write') {
            // Blogs in which user can write without subscription
            $aFilter = array(
                'acl_write'      => self::BLOG_USER_ACL_USER,
                'min_rate_write' => $oUser->getUserRating(),
            );
        }

        // Получаем типы блогов
        if ($aFilter && ($aBlogTypes = $this->getBlogTypes($aFilter, true))) {
            // Получаем ID блогов
            $aCriteria = array(
                'filter' => array('blog_type' => $aBlogTypes)
            );
            // Получаем ID блогов
            $aResult = $this->oMapper->getBlogsIdByCriteria($aCriteria);

            // Получаем сами блоги
            if ($aResult['data']) {
                // если задана только проверка, то сам блог(и) не нужен
                if ($iRequestBlogId && $bCheckOnly) {
                    return in_array($iRequestBlogId, $aResult['data']);
                }
                if ($aBlogs = $this->getBlogsAdditionalData($aResult['data'], array())) {
                    foreach ($aBlogs as $oBlog) {
                        if (!isset($aAllowBlogs[$oBlog->getId()])) {
                            $aAllowBlogs[$oBlog->getId()] = $oBlog;
                        }
                    }
                }
            }
        }
        if ($iRequestBlogId) {
            return isset($aAllowBlogs[$iRequestBlogId]) ? $aAllowBlogs[$iRequestBlogId] : array();
        }

        return $aAllowBlogs;
    }

    /**
     * Получаем массив блогов, которые являются открытыми для пользователя
     *
     * @param  ModuleUser_EntityUser $oUser    Объект пользователя
     *
     * @return array
     */
    public function getAccessibleBlogsByUser($oUser) {

        if ($oUser->isAdministrator() || $oUser->isModerator()) {
            return $this->getBlogs(true);
        }
        if (false === ($aOpenBlogsUser = E::ModuleCache()->get("blog_accessible_user_{$oUser->getId()}"))) {
            //  Заносим блоги, созданные пользователем
            $aOpenBlogsUser = $this->getBlogsByOwnerId($oUser->getId(), true);

            // Добавляем блоги, в которых состоит пользователь
            // (читателем, модератором, или администратором)
            $aOpenBlogsUser = array_merge($aOpenBlogsUser, $this->getBlogUsersByUserId($oUser->getId(), null, true));
            E::ModuleCache()->Set(
                $aOpenBlogsUser, "blog_accessible_user_{$oUser->getId()}",
                array('blog_new', 'blog_update', "blog_relation_change_{$oUser->getId()}"), 60 * 60 * 24
            );
        }
        return $aOpenBlogsUser;
    }

    /**
     * Получаем массив идентификаторов блогов, которые являются закрытыми для пользователя
     *
     * @param  ModuleUser_EntityUser|null $oUser    Пользователь
     *
     * @return array
     */
    public function getInaccessibleBlogsByUser($oUser = null) {

        if ($oUser && ($oUser->isAdministrator() || $oUser->isModerator())) {
            return array();
        }
        $nUserId = $oUser ? $oUser->getId() : 0;
        $sCacheKey = 'blog_inaccessible_user_' . $nUserId;
        if (false === ($aCloseBlogsId = E::ModuleCache()->get($sCacheKey))) {
            $aCloseBlogsId = $this->oMapper->getCloseBlogsId($oUser);

            if ($oUser) {
                // * Получаем массив идентификаторов блогов, которые являются откытыми для данного пользователя
                $aOpenBlogsId = $this->getBlogUsersByUserId($nUserId, null, true);

                // * Получаем закрытые блоги, где пользователь является автором
                $aCloseBlogTypes = $this->getCloseBlogTypes($oUser);
                if ($aCloseBlogTypes) {
                    $aOwnerBlogs = $this->getBlogsByFilter(
                        array(
                            'type' => $aCloseBlogTypes,
                            'user_owner_id' => $nUserId,
                        ),
                        array(), 1, 1000, array()
                    );
                    $aOwnerBlogsId = array_keys($aOwnerBlogs['collection']);
                    $aCloseBlogsId = array_diff($aCloseBlogsId, $aOpenBlogsId, $aOwnerBlogsId);
                }
            }

            // * Сохраняем в кеш
            if ($oUser) {
                E::ModuleCache()->Set(
                    $aCloseBlogsId, $sCacheKey,
                    array('blog_new', 'blog_update', "blog_relation_change_{$nUserId}"), 'P1D'
                );
            } else {
                E::ModuleCache()->Set(
                    $aCloseBlogsId, $sCacheKey, array('blog_new', 'blog_update'), 'P3D'
                );
            }
        }
        return $aCloseBlogsId;
    }

    /**
     * Удаляет блог
     *
     * @param   int|array $aBlogsId   ID блога|массив ID блогов
     *
     * @return  bool
     */
    public function DeleteBlog($aBlogsId) {

        // Получаем массив ID, если передан объект или массив объектов
        $aBlogsId = $this->_entitiesId($aBlogsId);
        if ($aBlogsId) {
            // * Получаем идентификаторы топиков блога. Удаляем топики блога.
            // * При удалении топиков удаляются комментарии к ним и голоса.
            $aTopicsId = E::ModuleTopic()->getTopicsByBlogId($aBlogsId);

            // * Если блог не удален, возвращаем false
            if (!$this->oMapper->DeleteBlog($aBlogsId)) {
                return false;
            }

            if ($aTopicsId) {
                // * Удаляем топики
                E::ModuleTopic()->DeleteTopics($aTopicsId);
            }

            // * Удаляем связи пользователей блога.
            $this->oMapper->DeleteBlogUsersByBlogId($aBlogsId);

            // * Удаляем голосование за блог
            E::ModuleVote()->DeleteVoteByTarget($aBlogsId, 'blog');

            // * Чистим кеш
            E::ModuleCache()->CleanByTags(array('blog_update', 'topic_update', 'comment_online_update_topic', 'comment_update'));
            foreach ($aBlogsId as $nBlogId) {
                E::ModuleCache()->CleanByTags(array("blog_relation_change_blog_{$nBlogId}"));
                E::ModuleCache()->Delete("blog_{$nBlogId}");
            }
        }

        return true;
    }

    /**
     * Удаление блогов по ID владельцев
     *
     * @param array $aUsersId
     *
     * @return bool
     */
    public function DeleteBlogsByUsers($aUsersId) {

        $aBlogsId = $this->oMapper->getBlogsIdByOwnersId($aUsersId);
        return $this->DeleteBlog($aBlogsId);
    }

    /**
     * Загружает аватар в блог
     *
     * @param array                 $aFile - Массив $_FILES при загрузке аватара
     * @param ModuleBlog_EntityBlog $oBlog - Блог
     *
     * @return bool
     */
    public function UploadBlogAvatar($aFile, $oBlog) {

        $sTmpFile = E::ModuleUploader()->UploadLocal($aFile);
        if ($sTmpFile && ($oImg = E::ModuleImg()->CropSquare($sTmpFile))) {
            if ($sTmpFile = $oImg->Save($sTmpFile)) {
                if ($oStoredFile = E::ModuleUploader()->StoreImage($sTmpFile, 'blog_avatar', $oBlog->getId())) {
                    return $oStoredFile->getUrl();
                }
            }
        }

        // * В случае ошибки, возвращаем false
        return false;
    }

    /**
     * Удаляет аватар блога с сервера
     *
     * @param ModuleBlog_EntityBlog $oBlog    Блог
     */
    public function DeleteBlogAvatar($oBlog) {

        $this->DeleteAvatar($oBlog);
    }

    /**
     * Удаляет аватар блога с сервера
     *
     * @param ModuleBlog_EntityBlog $oBlog    Блог
     */
    public function DeleteAvatar($oBlog) {

        if ($oBlog) {
            // * Если аватар есть, удаляем его и его рейсайзы (старая схема)
            if ($sUrl = $oBlog->getAvatar()) {
                E::ModuleImg()->Delete(E::ModuleUploader()->Url2Dir($sUrl));
            }
            // Deletes blog avatar from media resources
            E::ModuleMresource()->DeleteMresourcesRelByTarget('blog_avatar', $oBlog->getid());
        }
    }

    /**
     * Пересчет количества топиков в блогах
     *
     * @return bool
     */
    public function RecalculateCountTopic() {

        $bResult = $this->oMapper->RecalculateCountTopic();
        if ($bResult) {
            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags(array('blog_update'));
        }
        return $bResult;
    }

    /**
     * Пересчет количества топиков в конкретном блоге
     *
     * @param int|array $aBlogsId - ID of blog | IDs of blogs
     *
     * @return bool
     */
    public function RecalculateCountTopicByBlogId($aBlogsId) {

        $aBlogsId = $this->_entitiesId($aBlogsId);
        if ($aBlogsId) {
            $bResult = $this->oMapper->RecalculateCountTopic($aBlogsId);
            if ($bResult) {
                //чистим зависимые кеши
                if (is_array($aBlogsId)) {
                    $aCacheTags = array('blog_update');
                    foreach ($aBlogsId as $iBlogId) {
                        E::ModuleCache()->Delete("blog_{$iBlogId}");
                        $aCacheTags[] = "blog_update_{$iBlogId}";
                    }
                    E::ModuleCache()->CleanByTags($aCacheTags);
                } else {
                    E::ModuleCache()->CleanByTags(array('blog_update', "blog_update_{$aBlogsId}"));
                    E::ModuleCache()->Delete("blog_{$aBlogsId}");
                }
                return $bResult;
            }
        }
        return true;
    }

    /**
     * Алиас для корректной работы ORM
     *
     * @param array $aBlogId    Список ID блогов
     *
     * @return array
     */
    public function getBlogItemsByArrayId($aBlogId) {

        return $this->getBlogsByArrayId($aBlogId);
    }

    /**
     * Возвращает список доступных типов для определенного действия
     *
     * @param ModuleUser_EntityUser $oUser
     * @param string                $sAction
     * @param bool                  $bTypeCodesOnly
     *
     * @return array
     */
    public function getAllowBlogTypes($oUser, $sAction, $bTypeCodesOnly = false) {

        $aFilter = array(
            'exclude_type' => in_array($sAction, array('add', 'list')) ? 'personal' : null,
            'is_active' => true,
        );

        if ($sAction && !in_array($sAction, array('add', 'list', 'write'))) {
            return array();
        }

        if (!$oUser) {
            // Если пользователь не задан
            if ($sAction == 'add') {
                $aFilter['allow_add'] = true;
            } elseif ($sAction == 'list') {
                $aFilter['allow_list'] = true;
            }
        } elseif ($oUser && !$oUser->IsAdministrator() && !$oUser->isModerator()) {
            // Если пользователь задан и он не админ, то надо учитывать рейтинг
            if ($sAction == 'add') {
                $aFilter['allow_add'] = true;
                $aFilter['min_rate_add'] = $oUser->getUserRating();
            } elseif ($sAction == 'list') {
                $aFilter['allow_list'] = true;
                $aFilter['min_rate_list'] = $oUser->getUserRating();
            } elseif ($sAction == 'write') {
                $aFilter['min_rate_write'] = $oUser->getUserRating();
            }
        }
        $aBlogTypes = $this->getBlogTypes($aFilter, $bTypeCodesOnly);

        return $aBlogTypes;
    }

    /**
     * Returns types of blogs which user can read (without personal subscriptions/membership)
     *
     * @param object|null|int $xUser - If 0 then types for guest
     *
     * @return array
     */
    public function getOpenBlogTypes($xUser = null) {

        if (is_null($xUser)) {
            $iUserId = E::UserId();
            if (!$iUserId) {
                $iUserId = 0;
            }
        } elseif (is_numeric($xUser) && intval($xUser) === 0) {
            $iUserId = 0;
        } else {
            $iUserId = (is_object($xUser) ? $xUser->getId() : intval($xUser));
        }
        $sCacheKey = 'blog_types_open_' . ($iUserId ? 'user' : 'guest');
        if (false === ($aBlogTypes = E::ModuleCache()->get($sCacheKey, 'tmp'))) {
            if ($this->oUserCurrent) {
                $aFilter = array(
                    'acl_read' => ModuleBlog::BLOG_USER_ACL_GUEST | ModuleBlog::BLOG_USER_ACL_USER,
                );
            } else {
                $aFilter = array(
                    'acl_read' => ModuleBlog::BLOG_USER_ACL_GUEST,
                );
            }
            // Blog types for guest and all users
            $aBlogTypes = $this->getBlogTypes($aFilter, true);
            E::ModuleCache()->Set($aBlogTypes, $sCacheKey, array('blog_update', 'blog_new'), 'P30D', 'tmp');
        }
        return $aBlogTypes;
    }

    /**
     * Returns types of blogs which user cannot read (without personal subscriptions/membership)
     *
     * @param ModuleUser_EntityUser $oUser
     *
     * @return array
     */
    public function getCloseBlogTypes($oUser = null) {

        if (is_null($oUser)) {
            $iUserId = E::UserId();
        } else {
            $iUserId = (is_object($oUser) ? $oUser->getId() : intval($oUser));
        }
        $sCacheKey = 'blog_types_close_' . ($iUserId ? 'user' : 'guest');
        if (false === ($aBlogTypes = E::ModuleCache()->get($sCacheKey, 'tmp'))) {
            if ($this->oUserCurrent) {
                $aFilter = array(
                    'acl_read' => ModuleBlog::BLOG_USER_ACL_MEMBER,
                );
            } else {
                $aFilter = array(
                    'acl_read' => ModuleBlog::BLOG_USER_ACL_USER | ModuleBlog::BLOG_USER_ACL_MEMBER,
                );
            }
            // Blog types for guest and all users
            $aBlogTypes = $this->getBlogTypes($aFilter, true);
            E::ModuleCache()->Set($aBlogTypes, $sCacheKey, array('blog_update', 'blog_new'), 'P30D', 'tmp');
        }
        return $aBlogTypes;
    }

    /**
     * Получить типы блогов
     *
     * @param   array   $aFilter
     * @param   bool    $bTypeCodesOnly
     *
     * @return  ModuleBlog_EntityBlogType[]
     */
    public function getBlogTypes($aFilter = array(), $bTypeCodesOnly = false) {

        $aResult = array();
        $sCacheKey = 'blog_types';
        if (false === ($data = E::ModuleCache()->get($sCacheKey, 'tmp'))) {
            if (false === ($data = E::ModuleCache()->get($sCacheKey))) {
                /** @var ModuleBlog_EntityBlogType[] $data */
                $data = $this->oMapper->getBlogTypes();
                E::ModuleCache()->Set($data, $sCacheKey, array('blog_update', 'blog_new'), 'P30D');
            }
            E::ModuleCache()->Set($data, $sCacheKey, array('blog_update', 'blog_new'), 'P30D', 'tmp');
        }
        $aBlogTypes = array();
        if ($data) {
            foreach ($data as $nKey => $oBlogType) {
                $bOk = true;
                if (isset($aFilter['include_type'])) {
                    $bOk = $bOk && ($aFilter['include_type'] == $oBlogType->getTypeCode());
                    if (!$bOk) continue;
                }
                if (isset($aFilter['exclude_type'])) {
                    $bOk = $bOk && ($aFilter['exclude_type'] != $oBlogType->getTypeCode());
                    if (!$bOk) continue;
                }
                if (isset($aFilter['is_active'])) {
                    $bOk = $bOk && $oBlogType->IsActive();
                    if (!$bOk) continue;
                }
                if (isset($aFilter['not_active'])) {
                    $bOk = $bOk && !$oBlogType->IsActive();
                    if (!$bOk) continue;
                }
                if (isset($aFilter['allow_add'])) {
                    $bOk = $bOk && $oBlogType->IsAllowAdd();
                    if (!$bOk) continue;
                }
                if (isset($aFilter['allow_list'])) {
                    $bOk = $bOk && $oBlogType->IsShowTitle();
                    if (!$bOk) continue;
                }
                if (isset($aFilter['min_rate_add'])) {
                    $bOk = $bOk && ($oBlogType->getMinRateAdd() <= $aFilter['min_rate_add']);
                    if (!$bOk) continue;
                }
                if (isset($aFilter['min_rate_list'])) {
                    $bOk = $bOk && ($oBlogType->getMinRateList() <= $aFilter['min_rate_list']);
                    if (!$bOk) continue;
                }
                if (isset($aFilter['min_rate_write'])) {
                    $bOk = $bOk && ($oBlogType->getMinRateWrite() <= $aFilter['min_rate_write']);
                    if (!$bOk) continue;
                }
                if (isset($aFilter['min_rate_read'])) {
                    $bOk = $bOk && ($oBlogType->getMinRateRead() <= $aFilter['min_rate_read']);
                    if (!$bOk) continue;
                }
                if (isset($aFilter['min_rate_comment'])) {
                    $bOk = $bOk && ($oBlogType->getMinRateComment() <= $aFilter['min_rate_comment']);
                    if (!$bOk) continue;
                }
                if (isset($aFilter['acl_write'])) {
                    $bOk = $bOk && ($oBlogType->getAclWrite() & $aFilter['acl_write']);
                    if (!$bOk) continue;
                }
                if (isset($aFilter['acl_read'])) {
                    $bOk = $bOk && ($oBlogType->getAclRead() & $aFilter['acl_read']);
                    if (!$bOk) continue;
                }
                if (isset($aFilter['acl_comment'])) {
                    $bOk = $bOk && ($oBlogType->getAclComment() & $aFilter['acl_comment']);
                    if (!$bOk) continue;
                }
                // Проверим, есть ли в данном типе блога вообще типы контента
                /** @var ModuleTopic_EntityContentType[] $aContentTypes */
                if ($aContentTypes = $oBlogType->getContentTypes()) {
                    foreach ($aContentTypes as $iCTId => $oContentType) {
                        // Тип контента не активирован
                        if (!$oContentType->getActive()) {
                            unset($aContentTypes[$iCTId]);
                        }
                        // Тип контента включен, но создавать могу только админы
                        if (!$oContentType->isAccessible()) {
                            unset($aContentTypes[$iCTId]);
                        }
                    }
                }
                // Проверим существующие типы контента на возможность создания пользователей

                if ($bOk) {
                    $aBlogTypes[$oBlogType->getTypeCode()] = $oBlogType;
                }
                $data[$nKey] = null;
            }
        }
        if ($aBlogTypes) {
            if ($bTypeCodesOnly) {
                $aResult = array_keys($aBlogTypes);
            } else {
                $aResult = $aBlogTypes;
            }
        }
        return $aResult;
    }

    /**
     * Получить объект типа блога по его ID
     *
     * @param int $iBlogTypeId
     *
     * @return null|ModuleBlog_EntityBlogType
     */
    public function getBlogTypeById($iBlogTypeId) {

        $sCacheKey = 'blog_type_' . $iBlogTypeId;
        if (false === ($data = E::ModuleCache()->get($sCacheKey))) {
            $data = $this->oMapper->getBlogTypeById($iBlogTypeId);
            E::ModuleCache()->Set($data, $sCacheKey, array('blog_update', 'blog_new'), 'PT30M');
        }
        return $data;
    }

    /**
     * Получить объект типа блога по его коду
     *
     * @param string $sTypeCode
     *
     * @return null|ModuleBlog_EntityBlogType
     */
    public function getBlogTypeByCode($sTypeCode) {

        $aBlogTypes = $this->getBlogTypes();
        if (isset($aBlogTypes[$sTypeCode])) {
            return $aBlogTypes[$sTypeCode];
        }
        return null;
    }

    /**
     * @return ModuleBlog_EntityBlogType|null
     */
    public function getBlogTypeDefault() {

        $oBlogType = $this->getBlogTypeByCode('open');
        return $oBlogType;
    }

    /**
     * Добавить тип блога
     *
     * @param ModuleBlog_EntityBlogType$oBlogType
     *
     * @return bool
     */
    public function AddBlogType($oBlogType) {

        $nId = $this->oMapper->AddBlogType($oBlogType);
        if ($nId) {
            $oBlogType->SetId($nId);
            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags(array('blog_update'));
            E::ModuleCache()->Delete("blog_type_{$oBlogType->getId()}");
            return true;
        }
        return false;
    }

    /**
     * Обновить тип блога
     *
     * @param ModuleBlog_EntityBlogType$oBlogType
     *
     * @return bool
     */
    public function UpdateBlogType($oBlogType) {

        $bResult = $this->oMapper->UpdateBlogType($oBlogType);
        if ($bResult) {
            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags(array('blog_update'));
            E::ModuleCache()->Delete("blog_type_{$oBlogType->getId()}");
            return true;
        }
        return false;
    }

    /**
     * Удалить тип блога
     *
     * @param ModuleBlog_EntityBlogType$oBlogType
     *
     * @return bool
     */
    public function DeleteBlogType($oBlogType) {

        $aInfo = $this->oMapper->getBlogCountsByTypes($oBlogType->getTypeCode());
        // Если есть блоги такого типа, то НЕ удаляем тип
        if (empty($aInfo[$oBlogType->getTypeCode()])) {
            $bResult = $this->oMapper->DeleteBlogType($oBlogType->getTypeCode());
            if ($bResult) {
                //чистим зависимые кеши
                E::ModuleCache()->CleanByTags(array('blog_update'));
                E::ModuleCache()->Delete("blog_type_{$oBlogType->getId()}");
                return true;
            }
        }
        return false;
    }

    /**
     * Активен ли этот тип блога
     *
     * @param string $sBlogType
     *
     * @return bool
     */
    public function BlogTypeEnabled($sBlogType) {

        $oBlogType = $this->getBlogTypeByCode($sBlogType);
        return $oBlogType && $oBlogType->IsActive();
    }

    /**
     * Статистка блогов
     *
     * @param array $aExcludeTypes
     *
     * @return array
     */
    public function getBlogsData($aExcludeTypes = array('personal')) {

        return $this->oMapper->getBlogsData($aExcludeTypes);
    }

    /*********************************************************/

    public function getBlogsId($aFilter) {


    }
}

// EOF