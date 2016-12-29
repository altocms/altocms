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
 * Модуль для работы с комментариями
 *
 * @package modules.comment
 * @since 1.0
 */
class ModuleComment extends Module {
    /**
     * Объект маппера
     *
     * @var ModuleComment_MapperComment
     */
    protected $oMapper;
    /**
     * Объект текущего пользователя
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent = null;

    protected $aAdditionalData = array('vote', 'target', 'favourite', 'user' => array());

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->oMapper = E::GetMapper(__CLASS__);
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
    }

    /**
     * Получить коммент по ID
     *
     * @param int $nId    ID комментария
     *
     * @return ModuleComment_EntityComment|null
     */
    public function GetCommentById($nId) {

        if (!intval($nId)) {
            return null;
        }
        $aComments = $this->GetCommentsAdditionalData($nId);
        if (isset($aComments[$nId])) {
            return $aComments[$nId];
        }
        return null;
    }

    /**
     * Получает уникальный коммент, это помогает спастись от дублей комментов
     *
     * @param int    $nTargetId      ID владельца комментария
     * @param string $sTargetType    Тип владельца комментария
     * @param int    $nUserId        ID пользователя
     * @param int    $nCommentPid    ID родительского комментария
     * @param string $sHash          Хеш строка текста комментария
     *
     * @return ModuleComment_EntityComment|null
     */
    public function GetCommentUnique($nTargetId, $sTargetType, $nUserId, $nCommentPid, $sHash) {

        $nId = $this->oMapper->GetCommentUnique($nTargetId, $sTargetType, $nUserId, $nCommentPid, $sHash);

        return $this->GetCommentById($nId);
    }

    /**
     * Получить все комменты
     *
     * @param string $sTargetType             Тип владельца комментария
     * @param int    $iPage                   Номер страницы
     * @param int    $iPerPage                Количество элементов на страницу
     * @param array  $aExcludeTarget          Список ID владельцев, которые необходимо исключить из выдачи
     * @param array  $aExcludeParentTarget    Список ID родителей владельцев, которые необходимо исключить из выдачи,
     *                                        например, исключить комментарии топиков к определенным блогам(закрытым)
     *
     * @return array('collection'=>array, 'count'=>int)
     */
    public function GetCommentsAll($sTargetType, $iPage, $iPerPage, $aExcludeTarget = array(), $aExcludeParentTarget = array()) {

        $sCacheKey = "comment_all_" . serialize(func_get_args());
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = array(
                'collection' => $this->oMapper->GetCommentsIdByTargetType($sTargetType, $iCount, $iPage, $iPerPage, $aExcludeTarget, $aExcludeParentTarget),
                'count'      => $iCount,
            );
            E::ModuleCache()->Set($data, $sCacheKey, array("comment_new_{$sTargetType}", "comment_update_status_{$sTargetType}"), 'P1D');
        }
        if ($data['collection']) {
            $data['collection'] = $this->GetCommentsAdditionalData($data['collection'], array('target', 'favourite', 'user' => array()));
        }
        return $data;
    }

    /**
     * Получает дополнительные данные(объекты) для комментов по их ID
     *
     * @param array|int  $aCommentId      Список ID комментов
     * @param array|null $aAllowData      Список типов дополнительных данных, которые нужно получить для комментариев
     * @param array      $aAdditionalData Predefined additional data
     *
     * @return array
     */
    public function GetCommentsAdditionalData($aCommentId, $aAllowData = null, $aAdditionalData = array()) {

        if (!$aCommentId) {
            return array();
        }
        if (is_null($aAllowData)) {
            $aAllowData = $this->aAdditionalData;
        }
        $aAllowData = F::Array_FlipIntKeys($aAllowData);
        if (!is_array($aCommentId)) {
            $aCommentId = array($aCommentId);
        }

        // * Получаем комменты
        $aComments = $this->GetCommentsByArrayId($aCommentId);

        // * Формируем ID дополнительных данных, которые нужно получить
        $aUserId = array();
        $aTargetTypeId = array();
        foreach ($aComments as $oComment) {
            if (isset($aAllowData['user'])) {
                $aUserId[] = $oComment->getUserId();
            }
            if (isset($aAllowData['target'])) {
                $aTargetTypeId[$oComment->getTargetType()][] = $oComment->getTargetId();
            }
        }

        // * Получаем дополнительные данные
        if ($aUserId) {
            $aUsers = (isset($aAllowData['user']) && is_array($aAllowData['user']))
                ? E::ModuleUser()->GetUsersAdditionalData($aUserId, $aAllowData['user'])
                : E::ModuleUser()->GetUsersAdditionalData($aUserId);
        }

        // * В зависимости от типа target_type достаем данные
        $aTargets = array();
        foreach ($aTargetTypeId as $sTargetType => $aTargetId) {
            if (isset($aAdditionalData['target'][$sTargetType])) {
                // predefined targets' data
                $aTargets[$sTargetType] = $aAdditionalData['target'][$sTargetType];
            } else {
                if (isset($aTargetTypeId['topic']) && $aTargetTypeId['topic']) {
                    // load targets' data
                    $aTargets['topic'] = E::ModuleTopic()->GetTopicsAdditionalData(
                        $aTargetTypeId['topic'], array('blog' => array('owner' => array(), 'relation_user'), 'user' => array())
                    );
                } else {
                    // we don't know how to get targets' data
                    $aTargets['topic'] = array();
                }
            }
        }

        if (isset($aAllowData['vote']) && $this->oUserCurrent) {
            $aVote = E::ModuleVote()->GetVoteByArray($aCommentId, 'comment', $this->oUserCurrent->getId());
        } else {
            $aVote = array();
        }

        if (isset($aAllowData['favourite']) && $this->oUserCurrent) {
            $aFavouriteComments = E::ModuleFavourite()->GetFavouritesByArray($aCommentId, 'comment', $this->oUserCurrent->getId());
        } else {
            $aFavouriteComments = array();
        }

        // * Добавляем данные к результату
        foreach ($aComments as $oComment) {
            if (isset($aUsers[$oComment->getUserId()])) {
                $oComment->setUser($aUsers[$oComment->getUserId()]);
            } else {
                $oComment->setUser(null); // или $oComment->setUser(new ModuleUser_EntityUser());
            }
            if (isset($aTargets[$oComment->getTargetType()][$oComment->getTargetId()])) {
                $oComment->setTarget($aTargets[$oComment->getTargetType()][$oComment->getTargetId()]);
            } else {
                $oComment->setTarget(null);
            }
            if (isset($aVote[$oComment->getId()])) {
                $oComment->setVote($aVote[$oComment->getId()]);
            } else {
                $oComment->setVote(null);
            }
            if (isset($aFavouriteComments[$oComment->getId()])) {
                $oComment->setIsFavourite(true);
            } else {
                $oComment->setIsFavourite(false);
            }
        }
        return $aComments;
    }

    /**
     * Список комментов по ID
     *
     * @param array|int $aCommentsId    Список ID комментариев
     *
     * @return array
     */
    public function GetCommentsByArrayId($aCommentsId) {

        if (!$aCommentsId) {
            return array();
        }
        if (Config::Get('sys.cache.solid')) {
            return $this->GetCommentsByArrayIdSolid($aCommentsId);
        }
        if (!is_array($aCommentsId)) {
            $aCommentsId = array($aCommentsId);
        }
        $aCommentsId = array_unique($aCommentsId);
        $aComments = array();
        $aCommentIdNotNeedQuery = array();

        // * Делаем мульти-запрос к кешу
        $aCacheKeys = F::Array_ChangeValues($aCommentsId, 'comment_');
        if (false !== ($data = E::ModuleCache()->Get($aCacheKeys))) {
            // * Проверяем что досталось из кеша
            foreach ($aCacheKeys as $iIndex => $sKey) {
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aComments[$data[$sKey]->getId()] = $data[$sKey];
                    } else {
                        $aCommentIdNotNeedQuery[] = $aCommentsId[$iIndex];
                    }
                }
            }
        }
        // * Смотрим каких комментов не было в кеше и делаем запрос в БД
        $aCommentIdNeedQuery = array_diff($aCommentsId, array_keys($aComments));
        $aCommentIdNeedQuery = array_diff($aCommentIdNeedQuery, $aCommentIdNotNeedQuery);
        $aCommentIdNeedStore = $aCommentIdNeedQuery;

        if ($aCommentIdNeedQuery) {
            if ($data = $this->oMapper->GetCommentsByArrayId($aCommentIdNeedQuery)) {
                foreach ($data as $oComment) {
                    // * Добавляем к результату и сохраняем в кеш
                    $aComments[$oComment->getId()] = $oComment;
                    E::ModuleCache()->Set($oComment, "comment_{$oComment->getId()}", array(), 'P4D');
                    $aCommentIdNeedStore = array_diff($aCommentIdNeedStore, array($oComment->getId()));
                }
            }
        }

        // * Сохраняем в кеш запросы не вернувшие результата
        foreach ($aCommentIdNeedStore as $nId) {
            E::ModuleCache()->Set(null, "comment_{$nId}", array(), 'P4D');
        }
        // * Сортируем результат согласно входящему массиву
        $aComments = F::Array_SortByKeysArray($aComments, $aCommentsId);

        return $aComments;
    }

    /**
     * Получает список комментариев по ID используя единый кеш
     *
     * @param array $aCommentId Список ID комментариев
     *
     * @return array
     */
    public function GetCommentsByArrayIdSolid($aCommentId) {

        if (!is_array($aCommentId)) {
            $aCommentId = array($aCommentId);
        }
        $aCommentId = array_unique($aCommentId);
        $aComments = array();

        $sCacheKey = 'comment_id_' . join(',', $aCommentId);
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetCommentsByArrayId($aCommentId);
            foreach ($data as $oComment) {
                $aComments[$oComment->getId()] = $oComment;
            }
            E::ModuleCache()->Set($aComments, $sCacheKey, array("comment_update"), 'P1D');
            return $aComments;
        }
        return $data;
    }

    /**
     * Получить все комменты сгрупированные по типу(для вывода прямого эфира)
     *
     * @param string $sTargetType    Тип владельца комментария
     * @param int    $iLimit         Количество элементов
     *
     * @return array
     */
    public function GetCommentsOnline($sTargetType, $iLimit) {
        /**
         * Исключаем из выборки идентификаторы закрытых блогов (target_parent_id)
         */
        $aCloseBlogs = ($this->oUserCurrent)
            ? E::ModuleBlog()->GetInaccessibleBlogsByUser($this->oUserCurrent)
            : E::ModuleBlog()->GetInaccessibleBlogsByUser();

        $s = serialize($aCloseBlogs);

        $sCacheKey = "comment_online_{$sTargetType}_{$s}_{$iLimit}";
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetCommentsIdOnline($sTargetType, $aCloseBlogs, $iLimit);
            E::ModuleCache()->Set($data, $sCacheKey, array("comment_online_update_{$sTargetType}"), 'P1D');
        }
        if ($data) {
            $data = $this->GetCommentsAdditionalData($data);
            // не может быть онлайн-комментариев без топиков
            foreach ($data as $iCommentId => $oComment) {
                if ($oComment->getTarget() === null) {
                    unset($data[$iCommentId]);
                }
            }
        }
        return $data;
    }

    /**
     * Получить комменты по юзеру
     *
     * @param  int    $iUserId        ID пользователя
     * @param  string $sTargetType    Тип владельца комментария
     * @param  int    $iPage          Номер страницы
     * @param  int    $iPerPage       Количество элементов на страницу
     *
     * @return array
     */
    public function GetCommentsByUserId($iUserId, $sTargetType, $iPage, $iPerPage) {
        /**
         * Исключаем из выборки идентификаторы закрытых блогов
         */
        $aCloseBlogs = ($this->oUserCurrent && $iUserId == $this->oUserCurrent->getId())
            ? array()
            : E::ModuleBlog()->GetInaccessibleBlogsByUser();

        $sCacheKey = "comment_user_{$iUserId}_{$sTargetType}_{$iPage}_{$iPerPage}_" . serialize($aCloseBlogs);
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = array(
                'collection' => $this->oMapper->GetCommentsByUserId($iUserId, $sTargetType, $iCount, $iPage, $iPerPage, array(), $aCloseBlogs),
                'count'      => $iCount,
            );
            E::ModuleCache()->Set(
                $data, $sCacheKey,
                array("comment_new_user_{$iUserId}_{$sTargetType}", "comment_update_status_{$sTargetType}"), 'P2D'
            );
        }
        if ($data['collection']) {
            $data['collection'] = $this->GetCommentsAdditionalData($data['collection']);
        }
        return $data;
    }

    /**
     * Получает количество комментариев одного пользователя
     *
     * @param  int    $iUserId     ID пользователя
     * @param  string $sTargetType Тип владельца комментария
     *
     * @return int
     */
    public function GetCountCommentsByUserId($iUserId, $sTargetType) {
        /**
         * Исключаем из выборки идентификаторы закрытых блогов
         */
        if ($this->oUserCurrent && $iUserId == $this->oUserCurrent->getId()) {
            $aCloseBlogs = E::ModuleBlog()->GetInaccessibleBlogsByUser();
        } else {
            $aCloseBlogs = array();
        }

        $sCacheKey = "comment_count_user_{$iUserId}_{$sTargetType}_" . serialize($aCloseBlogs);
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetCountCommentsByUserId($iUserId, $sTargetType, array(), $aCloseBlogs);
            E::ModuleCache()->Set(
                $data, $sCacheKey,
                array("comment_new_user_{$iUserId}_{$sTargetType}", "comment_update_status_{$sTargetType}"), 'P2D'
            );
        }
        return $data;
    }

    /**
     * Получить комменты по рейтингу и дате
     *
     * @param  string $sDate          Дата за которую выводить рейтинг, т.к. кеширование происходит по дате, то дату лучше передавать с точностью до часа (минуты и секунды как 00:00)
     * @param  string $sTargetType    Тип владельца комментария
     * @param  int    $iLimit         Количество элементов
     *
     * @return array
     */
    public function GetCommentsRatingByDate($sDate, $sTargetType, $iLimit = 20) {
        /**
         * Выбираем топики, комметарии к которым являются недоступными для пользователя
         */
        $aCloseBlogs = ($this->oUserCurrent)
            ? E::ModuleBlog()->GetInaccessibleBlogsByUser($this->oUserCurrent)
            : E::ModuleBlog()->GetInaccessibleBlogsByUser();

        $sCacheKey = "comment_rating_{$sDate}_{$sTargetType}_{$iLimit}_" . serialize($aCloseBlogs);
        /**
         * Т.к. время передаётся с точностью 1 час то можно по нему замутить кеширование
         */
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetCommentsIdByRatingAndDate($sDate, $sTargetType, $iLimit, array(), $aCloseBlogs);
            E::ModuleCache()->Set(
                $data, $sCacheKey,
                array("comment_new_{$sTargetType}", "comment_update_status_{$sTargetType}",
                      "comment_update_rating_{$sTargetType}"), 'P2D'
            );
        }
        if ($data) {
            $data = $this->GetCommentsAdditionalData($data);
        }
        return $data;
    }

    /**
     * Получить комменты по владельцу
     *
     * @param  int|object $xTarget     ID/экземпляр владельца комментария
     * @param  string     $sTargetType Тип владельца комментария
     * @param  int        $iPage       Номер страницы
     * @param  int        $iPerPage    Количество элементов на страницу
     *
     * @return array('comments'=>array,'iMaxIdComment'=>int)
     */
    public function GetCommentsByTargetId($xTarget, $sTargetType, $iPage = 1, $iPerPage = 0) {

        if (Config::Get('module.comment.use_nested')) {
            return $this->GetCommentsTreeByTargetId($xTarget, $sTargetType, $iPage, $iPerPage);
        }

        if (is_object($xTarget)) {
            $iTargetId = $xTarget->getId();
            $oTarget = $xTarget;
        } else {
            $iTargetId = intval($xTarget);
            $oTarget = null;
        }

        $sCacheKey = "comment_target_{$iTargetId}_{$sTargetType}";
        if (false === ($aCommentsRec = E::ModuleCache()->Get($sCacheKey))) {
            $aCommentsRow = $this->oMapper->GetCommentsByTargetId($iTargetId, $sTargetType);
            if (count($aCommentsRow)) {
                $aCommentsRec = $this->BuildCommentsRecursive($aCommentsRow);
            }
            E::ModuleCache()->Set($aCommentsRec, $sCacheKey, array("comment_new_{$sTargetType}_{$iTargetId}"), 'P2D');
        }
        if (!isset($aCommentsRec['comments'])) {
            return array('comments' => array(), 'iMaxIdComment' => 0);
        }
        $aComments = $aCommentsRec;
        $aCommentsId = array_keys($aCommentsRec['comments']);
        if ($aCommentsId) {
            if ($oTarget) {
                $aAdditionalData = array(
                    'target' => array(
                        $sTargetType => array($iTargetId => $oTarget),
                    ),
                );
            } else {
                $aAdditionalData = array();
            }
            $aComments['comments'] = $this->GetCommentsAdditionalData($aCommentsId, null, $aAdditionalData);
            foreach ($aComments['comments'] as $oComment) {
                $oComment->setLevel($aCommentsRec['comments'][$oComment->getId()]);
            }
        }
        return $aComments;
    }

    /**
     * Возвращает массив ID комментариев
     *
     * @param   array   $aTargetsId
     * @param   string  $sTargetType
     * @return  array
     */
    public function GetCommentsIdByTargetsId($aTargetsId, $sTargetType) {

        return $this->oMapper->GetCommentsIdByTargetsId($aTargetsId, $sTargetType);
    }

    /**
     * Получает комменты используя nested set
     *
     * @param int|object $xTarget     ID/экземпляр владельца коммента
     * @param string     $sTargetType Тип владельца комментария
     * @param  int       $iPage       Номер страницы
     * @param  int       $iPerPage    Количество элементов на страницу
     *
     * @return array('comments'=>array, 'iMaxIdComment'=>int, 'count'=>int)
     */
    public function GetCommentsTreeByTargetId($xTarget, $sTargetType, $iPage = 1, $iPerPage = 0) {

        if (is_object($xTarget)) {
            $iTargetId = $xTarget->getId();
            $oTarget = $xTarget;
        } else {
            $iTargetId = intval($xTarget);
            $oTarget = null;
        }
        if (!Config::Get('module.comment.nested_page_reverse')
            && $iPerPage
            && $iCountPage = ceil($this->GetCountCommentsRootByTargetId($iTargetId, $sTargetType) / $iPerPage)
        ) {
            $iPage = $iCountPage - $iPage + 1;
        }
        $iPage = $iPage < 1 ? 1 : $iPage;
        $sCacheKey = "comment_tree_target_{$iTargetId}_{$sTargetType}_{$iPage}_{$iPerPage}";
        if (false === ($aReturn = E::ModuleCache()->Get($sCacheKey))) {
            // * Нужно или нет использовать постраничное разбиение комментариев
            if ($iPerPage) {
                $aComments = $this->oMapper->GetCommentsTreePageByTargetId($iTargetId, $sTargetType, $iCount, $iPage, $iPerPage);
            } else {
                $aComments = $this->oMapper->GetCommentsTreeByTargetId($iTargetId, $sTargetType);
                $iCount = count($aComments);
            }
            $iMaxIdComment = count($aComments) ? max($aComments) : 0;
            $aReturn = array('comments' => $aComments, 'iMaxIdComment' => $iMaxIdComment, 'count' => $iCount);
            E::ModuleCache()->Set(
                $aReturn, $sCacheKey,
                array("comment_new_{$sTargetType}_{$iTargetId}"), 'P2D'
            );
        }
        if ($aReturn['comments']) {
            if ($oTarget) {
                // If there'is target object in arguments then sets in as predefined data
                $aAdditionalData = array(
                    'target' => array(
                        $sTargetType => array($iTargetId => $oTarget),
                    ),
                );
            } else {
                $aAdditionalData = array();
            }
            $aReturn['comments'] = $this->GetCommentsAdditionalData($aReturn['comments'], null, $aAdditionalData);
        }
        return $aReturn;
    }

    /**
     * Возвращает количество дочерних комментариев у корневого коммента
     *
     * @param int    $iTargetId      ID владельца коммента
     * @param string $sTargetType    Тип владельца комментария
     *
     * @return int
     */
    public function GetCountCommentsRootByTargetId($iTargetId, $sTargetType) {

        return $this->oMapper->GetCountCommentsRootByTargetId($iTargetId, $sTargetType);
    }

    /**
     * Возвращает номер страницы, на которой расположен комментарий
     *
     * @param int                         $iTargetId            ID владельца коммента
     * @param string                      $sTargetType    Тип владельца комментария
     * @param ModuleComment_EntityComment $oComment       Объект комментария
     *
     * @return bool|int
     */
    public function GetPageCommentByTargetId($iTargetId, $sTargetType, $oComment) {

        if (!Config::Get('module.comment.nested_per_page')) {
            return 1;
        }
        if (is_numeric($oComment)) {
            if (!($oComment = $this->GetCommentById($oComment))) {
                return false;
            }
            if (($oComment->getTargetId() != $iTargetId) || ($oComment->getTargetType() != $sTargetType)) {
                return false;
            }
        }
        // * Получаем корневого родителя
        if ($oComment->getPid()) {
            $oCommentRoot = $this->oMapper->GetCommentRootByTargetIdAndChildren($iTargetId, $sTargetType, $oComment->getLeft());
            if (!$oCommentRoot) {
                return false;
            }
        } else {
            $oCommentRoot = $oComment;
        }
        $iCount = ceil(
            $this->oMapper->GetCountCommentsAfterByTargetId($iTargetId, $sTargetType, $oCommentRoot->getLeft()) / Config::Get('module.comment.nested_per_page')
        );

        if (!Config::Get('module.comment.nested_page_reverse')
            && $iCountPage = ceil($this->GetCountCommentsRootByTargetId($iTargetId, $sTargetType) / Config::Get('module.comment.nested_per_page'))
        ) {
            $iCount = $iCountPage - $iCount + 1;
        }
        return $iCount ? $iCount : 1;
    }

    /**
     * Добавляет коммент
     *
     * @param  ModuleComment_EntityComment $oComment    Объект комментария
     *
     * @return bool|ModuleComment_EntityComment
     */
    public function AddComment(ModuleComment_EntityComment $oComment) {

        if (Config::Get('module.comment.use_nested')) {
            $iCommentId = $this->oMapper->AddCommentTree($oComment);
            E::ModuleCache()->CleanByTags(array("comment_update"));
        } else {
            $iCommentId = $this->oMapper->AddComment($oComment);
        }
        if ($iCommentId) {
            $oComment->setId($iCommentId);
            if ($oComment->getTargetType() == 'topic') {
                E::ModuleTopic()->RecalcCountOfComments($oComment->getTargetId());
            }

            // Освежим хранилище картинок
            E::ModuleMresource()->CheckTargetTextForImages(
                $oComment->getTargetType() . '_comment',
                $iCommentId,
                $oComment->getText()
            );

            if (E::IsUser()) {
                // * Сохраняем дату последнего коммента для юзера
                E::User()->setDateCommentLast(F::Now());
                E::ModuleUser()->Update(E::User());
                // чистим зависимые кеши
                E::ModuleCache()->CleanByTags(
                    array("comment_new", "comment_new_{$oComment->getTargetType()}",
                          "comment_new_user_{$oComment->getUserId()}_{$oComment->getTargetType()}",
                          "comment_new_{$oComment->getTargetType()}_{$oComment->getTargetId()}")
                );
            } else {
                // чистим зависимые кеши
                E::ModuleCache()->CleanByTags(
                    array("comment_new", "comment_new_{$oComment->getTargetType()}",
                          "comment_new_{$oComment->getTargetType()}_{$oComment->getTargetId()}")
                );
            }

            return $oComment;
        }
        return false;
    }

    /**
     * Обновляет коммент
     *
     * @param  ModuleComment_EntityComment $oComment    Объект комментария
     *
     * @return bool
     */
    public function UpdateComment(ModuleComment_EntityComment $oComment) {

        if ($this->oMapper->UpdateComment($oComment)) {

            // Освежим хранилище картинок
            E::ModuleMresource()->CheckTargetTextForImages(
                $oComment->getTargetType() . '_comment',
                $oComment->getId(),
                $oComment->getText()
            );

            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags(
                array("comment_update", "comment_update_{$oComment->getTargetType()}_{$oComment->getTargetId()}")
            );
            E::ModuleCache()->Delete("comment_{$oComment->getId()}");
            return true;
        }
        return false;
    }

    /**
     * Обновляет рейтинг у коммента
     *
     * @param  ModuleComment_EntityComment $oComment    Объект комментария
     *
     * @return bool
     */
    public function UpdateCommentRating(ModuleComment_EntityComment $oComment) {

        if ($this->oMapper->UpdateComment($oComment)) {
            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags(array("comment_update_rating_{$oComment->getTargetType()}"));
            E::ModuleCache()->Delete("comment_{$oComment->getId()}");
            return true;
        }
        return false;
    }

    /**
     * Обновляет статус у коммента - delete или publish
     *
     * @param  ModuleComment_EntityComment $oComment    Объект комментария
     *
     * @return bool
     */
    public function UpdateCommentStatus(ModuleComment_EntityComment $oComment) {

        if ($this->oMapper->UpdateComment($oComment)) {
            // * Если комментарий удаляется, удаляем его из прямого эфира
            if ($oComment->getDelete()) {
                $this->DeleteCommentOnlineByArrayId(
                    $oComment->getId(), $oComment->getTargetType()
                );
            }
            // * Обновляем избранное
            E::ModuleFavourite()->SetFavouriteTargetPublish($oComment->getId(), 'comment', !$oComment->getDelete());

            // * Чистим зависимые кеши
            if (Config::Get('sys.cache.solid')) {
                E::ModuleCache()->CleanByTags(array("comment_update"));
            }
            E::ModuleCache()->CleanByTags(array("comment_update_status_{$oComment->getTargetType()}"));
            E::ModuleCache()->Delete("comment_{$oComment->getId()}");
            return true;
        }
        return false;
    }

    /**
     * Устанавливает publish у коммента
     *
     * @param  int    $iTargetId      ID владельца коммента
     * @param  string $sTargetType    Тип владельца комментария
     * @param  int    $iPublish       Статус отображать комментарии или нет
     *
     * @return bool
     */
    public function SetCommentsPublish($iTargetId, $sTargetType, $iPublish) {

        $aComments = $this->GetCommentsByTargetId($iTargetId, $sTargetType);
        if (!$aComments || !isset($aComments['comments']) || count($aComments['comments']) == 0) {
            return false;
        }

        $bResult = false;
        /**
         * Если статус публикации успешно изменен, то меняем статус в отметке "избранное".
         * Если комментарии снимаются с публикации, удаляем их из прямого эфира.
         */
        if ($this->oMapper->SetCommentsPublish($iTargetId, $sTargetType, $iPublish)) {
            E::ModuleFavourite()->SetFavouriteTargetPublish(array_keys($aComments['comments']), 'comment', $iPublish);
            if ($iPublish != 1) {
                $this->DeleteCommentOnlineByTargetId($iTargetId, $sTargetType);
            }
            $bResult = true;
        }
        E::ModuleCache()->CleanByTags(array("comment_update_status_{$sTargetType}"));

        return $bResult;
    }

    /**
     * Удаляет коммент из прямого эфира
     *
     * @param  int    $iTargetId      ID владельца коммента
     * @param  string $sTargetType    Тип владельца комментария
     *
     * @return bool
     */
    public function DeleteCommentOnlineByTargetId($iTargetId, $sTargetType) {

        $bResult = $this->oMapper->DeleteCommentOnlineByTargetId($iTargetId, $sTargetType);
        E::ModuleCache()->CleanByTags(array("comment_online_update_{$sTargetType}"));

        return $bResult;
    }

    /**
     * Добавляет новый коммент в прямой эфир
     *
     * @param ModuleComment_EntityCommentOnline $oCommentOnline    Объект онлайн комментария
     *
     * @return bool|int
     */
    public function AddCommentOnline(ModuleComment_EntityCommentOnline $oCommentOnline) {

        $bResult = $this->oMapper->AddCommentOnline($oCommentOnline);
        E::ModuleCache()->CleanByTags(array("comment_online_update_{$oCommentOnline->getTargetType()}"));

        return $bResult;
    }

    /**
     * Получить новые комменты для владельца
     *
     * @param int    $nTargetId      ID владельца коммента
     * @param string $sTargetType    Тип владельца комментария
     * @param int    $nIdCommentLast ID последнего прочитанного комментария
     *
     * @return array('comments'=>array,'iMaxIdComment'=>int)
     */
    public function GetCommentsNewByTargetId($nTargetId, $sTargetType, $nIdCommentLast) {

        $sCacheKey = "comment_target_{$nTargetId}_{$sTargetType}_{$nIdCommentLast}";
        if (false === ($aCommentsId = E::ModuleCache()->Get($sCacheKey))) {
            $aCommentsId = $this->oMapper->GetCommentsIdNewByTargetId($nTargetId, $sTargetType, $nIdCommentLast);
            E::ModuleCache()->Set($aCommentsId, $sCacheKey, array("comment_new_{$sTargetType}_{$nTargetId}"), 'P1D');
        }
        $aComments = array();
        if (count($aCommentsId)) {
            $aComments = $this->GetCommentsAdditionalData($aCommentsId);
        }
        if (!$aComments) {
            return array('comments' => array(), 'iMaxIdComment' => $nIdCommentLast);
        }

        $iMaxIdComment = max($aCommentsId);

        $aVars = array(
            'oUserCurrent' => E::ModuleUser()->GetUserCurrent(),
            'bOneComment'  => true,
        );
        if ($sTargetType != 'topic') {
            $aVars['bNoCommentFavourites'] = true;
        }
        $aCommentsHtml = array();

        $bAllowToComment = false;
        if ($sTargetType == 'talk') {
            $bAllowToComment = TRUE;
        } elseif ($oUserCurrent = E::User()) {
            $oComment = reset($aComments);
            if ($oComment->getTarget() && $oComment->getTarget()->getBlog()) {
                $iBlogId = $oComment->getTarget()->getBlog()->GetId();
                $bAllowToComment = E::ModuleBlog()->GetBlogsAllowTo('comment', $oUserCurrent, $iBlogId, TRUE);
            }
        }
        $aVars['bAllowToComment'] = $bAllowToComment;
        foreach ($aComments as $oComment) {
            $aVars['oComment'] = $oComment;
            $sText = E::ModuleViewer()->Fetch($this->GetTemplateCommentByTarget($nTargetId, $sTargetType), $aVars);
            $aCommentsHtml[] = array(
                'html' => $sText,
                'obj'  => $oComment,
            );
        }
        return array('comments' => $aCommentsHtml, 'iMaxIdComment' => $iMaxIdComment);
    }

    /**
     * Возвращает шаблон комментария для рендеринга
     * Плагин может переопределить данный метод и вернуть свой шаблон в зависимости от типа
     *
     * @param int    $iTargetId      ID объекта комментирования
     * @param string $sTargetType    Типа объекта комментирования
     *
     * @return string
     */
    public function GetTemplateCommentByTarget($iTargetId, $sTargetType) {

        return 'comments/comment.single.tpl';
    }

    /**
     * Строит дерево комментариев
     *
     * @param array $aComments    Список комментариев
     * @param bool  $bBegin       Флаг начала построения дерева, для инициализации параметров внутри метода
     *
     * @return array('comments'=>array,'iMaxIdComment'=>int)
     */
    protected function BuildCommentsRecursive($aComments, $bBegin = true) {
        static $aResultCommnets;
        static $iLevel;
        static $iMaxIdComment;

        if ($bBegin) {
            $aResultCommnets = array();
            $iLevel = 0;
            $iMaxIdComment = 0;
        }
        foreach ($aComments as $aComment) {
            $aTemp = $aComment;
            if ($aComment['comment_id'] > $iMaxIdComment) {
                $iMaxIdComment = $aComment['comment_id'];
            }
            $aTemp['level'] = $iLevel;
            unset($aTemp['childNodes']);
            $aResultCommnets[$aTemp['comment_id']] = $aTemp['level'];
            if (isset($aComment['childNodes']) && count($aComment['childNodes']) > 0) {
                $iLevel++;
                $this->BuildCommentsRecursive($aComment['childNodes'], false);
            }
        }
        $iLevel--;

        return array('comments' => $aResultCommnets, 'iMaxIdComment' => $iMaxIdComment);
    }

    /**
     * Получает привязку комментария к ибранному(добавлен ли коммент в избранное у юзера)
     *
     * @param  int $iCommentId    ID комментария
     * @param  int $iUserId       ID пользователя
     *
     * @return ModuleFavourite_EntityFavourite|null
     */
    public function GetFavouriteComment($iCommentId, $iUserId) {

        return E::ModuleFavourite()->GetFavourite($iCommentId, 'comment', $iUserId);
    }

    /**
     * Получить список избранного по списку айдишников
     *
     * @param array $aCommentId    Список ID комментов
     * @param int   $iUserId       ID пользователя
     *
     * @return array
     */
    public function GetFavouriteCommentsByArray($aCommentId, $iUserId) {

        return E::ModuleFavourite()->GetFavouritesByArray($aCommentId, 'comment', $iUserId);
    }

    /**
     * Получить список избранного по списку айдишников, но используя единый кеш
     *
     * @param array  $aCommentId    Список ID комментов
     * @param int    $iUserId       ID пользователя
     *
     * @return array
     */
    public function GetFavouriteCommentsByArraySolid($aCommentId, $iUserId) {

        return E::ModuleFavourite()->GetFavouritesByArraySolid($aCommentId, 'comment', $iUserId);
    }

    /**
     * Получает список комментариев из избранного пользователя
     *
     * @param  int    $iUserId      ID пользователя
     * @param  int    $iCurrPage    Номер страницы
     * @param  int    $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetCommentsFavouriteByUserId($iUserId, $iCurrPage, $iPerPage) {

        $aCloseTopics = array();
        /**
         * Получаем список идентификаторов избранных комментов
         */
        $data = ($this->oUserCurrent && $iUserId == $this->oUserCurrent->getId())
            ? E::ModuleFavourite()->GetFavouritesByUserId($iUserId, 'comment', $iCurrPage, $iPerPage, $aCloseTopics)
            : E::ModuleFavourite()->GetFavouriteOpenCommentsByUserId($iUserId, $iCurrPage, $iPerPage);
        /**
         * Получаем комменты по переданому массиву айдишников
         */
        if ($data['collection']) {
            $data['collection'] = $this->GetCommentsAdditionalData($data['collection']);
        }
        //if ($data['collection'] && !E::IsAdmin()) {
        if ($data['collection']) {
            $aAllowBlogTypes = E::ModuleBlog()->GetOpenBlogTypes();
            if ($this->oUserCurrent) {
                $aClosedBlogs = E::ModuleBlog()->GetAccessibleBlogsByUser($this->oUserCurrent);
            } else {
                $aClosedBlogs = array();
            }
            foreach ($data['collection'] as $oComment) {
                $oTopic = $oComment->getTarget();
                if ($oTopic && ($oBlog = $oTopic->getBlog())) {
                    if (!in_array($oBlog->getType(), $aAllowBlogTypes) && !in_array($oBlog->getId(), $aClosedBlogs)) {
                        $oTopic->setTitle('...');
                        $oComment->setText(E::ModuleLang()->Get('acl_cannot_show_content'));
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Возвращает число комментариев в избранном
     *
     * @param  int $iUserId    ID пользователя
     *
     * @return int
     */
    public function GetCountCommentsFavouriteByUserId($iUserId) {

        return ($this->oUserCurrent && $iUserId == $this->oUserCurrent->getId())
            ? E::ModuleFavourite()->GetCountFavouritesByUserId($iUserId, 'comment')
            : E::ModuleFavourite()->GetCountFavouriteOpenCommentsByUserId($iUserId);
    }

    /**
     * Добавляет комментарий в избранное
     *
     * @param  ModuleFavourite_EntityFavourite $oFavourite    Объект избранного
     *
     * @return bool|ModuleFavourite_EntityFavourite
     */
    public function AddFavouriteComment(ModuleFavourite_EntityFavourite $oFavourite) {

        if (($oFavourite->getTargetType() == 'comment')
            && ($oComment = E::ModuleComment()->GetCommentById($oFavourite->getTargetId()))
            && in_array($oComment->getTargetType(), Config::get('module.comment.favourite_target_allow'))
        ) {
            return E::ModuleFavourite()->AddFavourite($oFavourite);
        }
        return false;
    }

    /**
     * Удаляет комментарий из избранного
     *
     * @param  ModuleFavourite_EntityFavourite $oFavourite    Объект избранного
     *
     * @return bool
     */
    public function DeleteFavouriteComment(ModuleFavourite_EntityFavourite $oFavourite) {

        if (($oFavourite->getTargetType() == 'comment')
            && ($oComment = E::ModuleComment()->GetCommentById($oFavourite->getTargetId()))
            && in_array($oComment->getTargetType(), Config::get('module.comment.favourite_target_allow'))
        ) {
            return E::ModuleFavourite()->DeleteFavourite($oFavourite);
        }
        return false;
    }

    /**
     * Удаляет комментарии из избранного по списку
     *
     * @param  array $aCommentsId    Список ID комментариев
     *
     * @return bool
     */
    public function DeleteFavouriteCommentsByArrayId($aCommentsId) {

        return E::ModuleFavourite()->DeleteFavouriteByTargetId($aCommentsId, 'comment');
    }

    /**
     * Удаляет комментарии из базы данных
     *
     * @param   array|int   $aTargetsId      Список ID владельцев
     * @param   string      $sTargetType     Тип владельцев
     *
     * @return  bool
     */
    public function DeleteCommentByTargetId($aTargetsId, $sTargetType) {

        if (!is_array($aTargetsId)) {
            $aTargetsId = array($aTargetsId);
        }

        // * Получаем список идентификаторов удаляемых комментариев
        $aCommentsId = $this->GetCommentsIdByTargetsId($aTargetsId, $sTargetType);

        // * Если ни одного комментария не найдено, выходим
        if (!count($aCommentsId)) {
            return true;
        }
        $bResult = $this->oMapper->DeleteCommentByTargetId($aTargetsId, $sTargetType);
        if ($bResult) {

            // * Удаляем комментарии из избранного
            $this->DeleteFavouriteCommentsByArrayId($aCommentsId);

            // * Удаляем комментарии к топику из прямого эфира
            $this->DeleteCommentOnlineByArrayId($aCommentsId, $sTargetType);

            // * Удаляем голосование за комментарии
            E::ModuleVote()->DeleteVoteByTarget($aCommentsId, 'comment');
        }

        // * Чистим зависимые кеши, даже если что-то не так пошло
        if (Config::Get('sys.cache.solid')) {
            foreach ($aTargetsId as $nTargetId) {
                E::ModuleCache()->CleanByTags(
                    array("comment_update", "comment_target_{$nTargetId}_{$sTargetType}")
                );
            }
        } else {
            foreach ($aTargetsId as $nTargetId) {
                E::ModuleCache()->CleanByTags(array("comment_target_{$nTargetId}_{$sTargetType}")
                );
            }
            if ($aCommentsId) {
                // * Удаляем кеш для каждого комментария
                foreach ($aCommentsId as $iCommentId) {
                    E::ModuleCache()->Delete("comment_{$iCommentId}");
                }
            }
        }
        return $bResult;
    }

    /**
     * Удаляет коммент из прямого эфира по массиву переданных идентификаторов
     *
     * @param  array|int $aCommentId
     * @param  string      $sTargetType    Тип владельцев
     * @return bool
     */
    public function DeleteCommentOnlineByArrayId($aCommentId, $sTargetType) {

        if (!is_array($aCommentId)) {
            $aCommentId = array($aCommentId);
        }
        $bResult = $this->oMapper->DeleteCommentOnlineByArrayId($aCommentId, $sTargetType);

        // чистим зависимые кеши
        E::ModuleCache()->CleanByTags(array("comment_online_update_{$sTargetType}"));

        return $bResult;
    }

    /**
     * Меняем target parent по массиву идентификаторов
     *
     * @param  int       $iParentId      Новый ID родителя владельца
     * @param  string    $sTargetType    Тип владельца
     * @param  array|int $aTargetId      Список ID владельцев
     *
     * @return bool
     */
    public function UpdateTargetParentByTargetId($iParentId, $sTargetType, $aTargetId) {

        if (!is_array($aTargetId)) {
            $aTargetId = array($aTargetId);
        }
        $bResult = $this->oMapper->UpdateTargetParentByTargetId($iParentId, $sTargetType, $aTargetId);

        // чистим зависимые кеши
        E::ModuleCache()->CleanByTags(array("comment_new_{$sTargetType}"));

        return $bResult;
    }

    /**
     * Меняем target parent по массиву идентификаторов в таблице комментариев online
     *
     * @param  int       $iParentId      Новый ID родителя владельца
     * @param  string    $sTargetType    Тип владельца
     * @param  array|int $aTargetId      Список ID владельцев
     *
     * @return bool
     */
    public function UpdateTargetParentByTargetIdOnline($iParentId, $sTargetType, $aTargetId) {

        if (!is_array($aTargetId)) {
            $aTargetId = array($aTargetId);
        }
        $bResult = $this->oMapper->UpdateTargetParentByTargetIdOnline($iParentId, $sTargetType, $aTargetId);

        // чистим зависимые кеши
        E::ModuleCache()->CleanByTags(array("comment_online_update_{$sTargetType}"));

        return $bResult;
    }

    /**
     * Меняет target parent на новый
     *
     * @param int    $iParentId       Прежний ID родителя владельца
     * @param string $sTargetType     Тип владельца
     * @param int    $iParentIdNew    Новый ID родителя владельца
     *
     * @return bool
     */
    public function MoveTargetParent($iParentId, $sTargetType, $iParentIdNew) {

        $bResult = $this->oMapper->MoveTargetParent($iParentId, $sTargetType, $iParentIdNew);

        // чистим зависимые кеши
        E::ModuleCache()->CleanByTags(array("comment_new_{$sTargetType}"));

        return $bResult;
    }

    /**
     * Меняет target parent на новый в прямом эфире
     *
     * @param int    $iParentId       Прежний ID родителя владельца
     * @param string $sTargetType     Тип владельца
     * @param int    $iParentIdNew    Новый ID родителя владельца
     *
     * @return bool
     */
    public function MoveTargetParentOnline($iParentId, $sTargetType, $iParentIdNew) {

        $bResult = $this->oMapper->MoveTargetParentOnline($iParentId, $sTargetType, $iParentIdNew);

        // чистим зависимые кеши
        E::ModuleCache()->CleanByTags(array("comment_online_update_{$sTargetType}"));

        return $bResult;
    }

    /**
     * Перестраивает дерево комментариев
     * Восстанавливает значения left, right и level
     *
     * @param int    $aTargetId      Список ID владельцев
     * @param string $sTargetType    Тип владельца
     */
    public function RestoreTree($aTargetId = null, $sTargetType = null) {

        // обработать конкретную сущность
        if (!is_null($aTargetId) && !is_null($sTargetType)) {
            $this->oMapper->RestoreTree(null, 0, -1, $aTargetId, $sTargetType);
            return;
        }
        $aType = array();
        // обработать все сущности конкретного типа
        if (!is_null($sTargetType)) {
            $aType[] = $sTargetType;
        } else {
            // обработать все сущности всех типов
            $aType = $this->oMapper->GetCommentTypes();
        }
        foreach ($aType as $sTargetType) {
            // для каждого типа получаем порциями ID сущностей
            $iPage = 1;
            $iPerPage = 50;
            while ($aResult = $this->oMapper->GetTargetIdByType($sTargetType, $iPage, $iPerPage)) {
                foreach ($aResult as $Row) {
                    $this->oMapper->RestoreTree(null, 0, -1, $Row['target_id'], $sTargetType);
                }
                $iPage++;
            }
        }
    }

    /**
     * Пересчитывает счетчик избранных комментариев
     *
     * @return bool
     */
    public function RecalculateFavourite() {

        return $this->oMapper->RecalculateFavourite();
    }

    /**
     * Получает список комментариев по фильтру
     *
     * @param array $aFilter           Фильтр выборки
     * @param array $aOrder            Сортировка
     * @param int   $iCurrPage         Номер текущей страницы
     * @param int   $iPerPage          Количество элементов на одну страницу
     * @param array $aAllowData        Список типов данных, которые нужно подтянуть к списку комментов
     *
     * @return array
     */
    public function GetCommentsByFilter($aFilter, $aOrder, $iCurrPage, $iPerPage, $aAllowData = null) {

        if (is_null($aAllowData)) {
            $aAllowData = array('target', 'user' => array());
        }
        $aFilter['order'] = $aOrder;
        $aCollection = $this->oMapper->GetCommentsIdByFilter($aFilter, $iCount, $iCurrPage, $iPerPage);
        if ($aCollection) {
            $aCollection = $this->GetCommentsAdditionalData($aCollection, $aAllowData);
        }
        return array('collection' => $aCollection, 'count' => $iCount);
    }

    /**
     * Алиас для корректной работы ORM
     *
     * @param array $aCommentId  Список ID комментариев
     *
     * @return array
     */
    public function GetCommentItemsByArrayId($aCommentId) {

        return $this->GetCommentsByArrayId($aCommentId);
    }

}

// EOF