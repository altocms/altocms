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
 * Модуль для работы с избранным
 *
 * @package modules.favourite
 * @since   1.0
 */
class ModuleFavourite extends Module {

    /** @var ModuleFavourite_MapperFavourite  */
    protected $oMapper;

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->oMapper = E::GetMapper(__CLASS__);
    }

    /**
     * Получает информацию о том, найден ли таргет в избранном или нет
     *
     * @param  int    $nTargetId      ID владельца
     * @param  string $sTargetType    Тип владельца
     * @param  int    $nUserId        ID пользователя
     *
     * @return ModuleFavourite_EntityFavourite|null
     */
    public function GetFavourite($nTargetId, $sTargetType, $nUserId) {

        if (!is_numeric($nTargetId) || !is_string($sTargetType)) {
            return null;
        }
        $data = $this->GetFavouritesByArray($nTargetId, $sTargetType, $nUserId);
        return (isset($data[$nTargetId])) ? $data[$nTargetId] : null;
    }

    /**
     * Получить список избранного по списку айдишников
     *
     * @param  array  $aTargetsId      Список ID владельцев
     * @param  string $sTargetType    Тип владельца
     * @param  int    $iUserId        ID пользователя
     *
     * @return ModuleFavourite_EntityFavourite[]
     */
    public function GetFavouritesByArray($aTargetsId, $sTargetType, $iUserId) {

        if (!$aTargetsId) {
            return array();
        }
        if (Config::Get('sys.cache.solid')) {
            return $this->GetFavouritesByArraySolid($aTargetsId, $sTargetType, $iUserId);
        }
        if (!is_array($aTargetsId)) {
            $aTargetsId = array($aTargetsId);
        }
        $aTargetsId = array_unique($aTargetsId);
        $aFavourite = array();
        $aIdNotNeedQuery = array();

        // * Делаем мульти-запрос к кешу
        $aCacheKeys = F::Array_ChangeValues($aTargetsId, "favourite_{$sTargetType}_", '_' . $iUserId);
        if (false !== ($data = E::ModuleCache()->Get($aCacheKeys))) {
            // * проверяем что досталось из кеша
            foreach ($aCacheKeys as $iIndex => $sKey) {
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aFavourite[$data[$sKey]->getTargetId()] = $data[$sKey];
                    } else {
                        $aIdNotNeedQuery[] = $aTargetsId[$iIndex];
                    }
                }
            }
        }
        // * Смотрим чего не было в кеше и делаем запрос в БД
        $aIdNeedQuery = array_diff($aTargetsId, array_keys($aFavourite));
        $aIdNeedQuery = array_diff($aIdNeedQuery, $aIdNotNeedQuery);
        $aIdNeedStore = $aIdNeedQuery;

        if ($aIdNeedQuery) {
            if ($data = $this->oMapper->GetFavouritesByArray($aIdNeedQuery, $sTargetType, $iUserId)) {
                foreach ($data as $oFavourite) {
                    // * Добавляем к результату и сохраняем в кеш
                    $aFavourite[$oFavourite->getTargetId()] = $oFavourite;
                    E::ModuleCache()->Set(
                        $oFavourite, "favourite_{$oFavourite->getTargetType()}_{$oFavourite->getTargetId()}_{$iUserId}",
                        array(), 60 * 60 * 24 * 7
                    );
                    $aIdNeedStore = array_diff($aIdNeedStore, array($oFavourite->getTargetId()));
                }
            }
        }

        // * Сохраняем в кеш запросы не вернувшие результата
        foreach ($aIdNeedStore as $sId) {
            E::ModuleCache()->Set(null, "favourite_{$sTargetType}_{$sId}_{$iUserId}", array(), 60 * 60 * 24 * 7);
        }

        // * Сортируем результат согласно входящему массиву
        $aFavourite = F::Array_SortByKeysArray($aFavourite, $aTargetsId);

        return $aFavourite;
    }

    /**
     * Получить список избранного по списку айдишников, но используя единый кеш
     *
     * @param  array  $aTargetId      Список ID владельцев
     * @param  string $sTargetType    Тип владельца
     * @param  int    $nUserId        ID пользователя
     *
     * @return array
     */
    public function GetFavouritesByArraySolid($aTargetId, $sTargetType, $nUserId) {

        if (!is_array($aTargetId)) {
            $aTargetId = array($aTargetId);
        }
        $aTargetId = array_unique($aTargetId);
        $aFavourites = array();

        $sCacheKey = "favourite_{$sTargetType}_{$nUserId}_id_" . join(',', $aTargetId);
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetFavouritesByArray($aTargetId, $sTargetType, $nUserId);
            foreach ($data as $oFavourite) {
                $aFavourites[$oFavourite->getTargetId()] = $oFavourite;
            }
            E::ModuleCache()->Set($aFavourites, $sCacheKey, array("favourite_{$sTargetType}_change_user_{$nUserId}"), 'P1D');
            return $aFavourites;
        }
        return $data;
    }

    /**
     * Получает список таргетов из избранного
     *
     * @param  int    $nUserId           ID пользователя
     * @param  string $sTargetType       Тип владельца
     * @param  int    $iCurrPage         Номер страницы
     * @param  int    $iPerPage          Количество элементов на страницу
     * @param  array  $aExcludeTarget    Список ID владельцев для исклчения
     *
     * @return array
     */
    public function GetFavouritesByUserId($nUserId, $sTargetType, $iCurrPage, $iPerPage, $aExcludeTarget = array()) {

        $sCacheKey = "{$sTargetType}_favourite_user_{$nUserId}_{$iCurrPage}_{$iPerPage}_" . serialize($aExcludeTarget);
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = array(
                'collection' => $this->oMapper->GetFavouritesByUserId($nUserId, $sTargetType, $iCount, $iCurrPage, $iPerPage, $aExcludeTarget),
                'count'      => $iCount
            );
            E::ModuleCache()->Set(
                $data,
                $sCacheKey,
                array(
                    "favourite_{$sTargetType}_change",
                    "favourite_{$sTargetType}_change_user_{$nUserId}"
                ),
                60 * 60 * 24 * 1
            );
        }
        return $data;
    }

    /**
     * Возвращает число таргетов определенного типа в избранном по ID пользователя
     *
     * @param  int    $sUserId           ID пользователя
     * @param  string $sTargetType       Тип владельца
     * @param  array  $aExcludeTarget    Список ID владельцев для исклчения
     *
     * @return array
     */
    public function GetCountFavouritesByUserId($sUserId, $sTargetType, $aExcludeTarget = array()) {

        $s = serialize($aExcludeTarget);
        if (false === ($data = E::ModuleCache()->Get("{$sTargetType}_count_favourite_user_{$sUserId}_{$s}"))) {
            $data = $this->oMapper->GetCountFavouritesByUserId($sUserId, $sTargetType, $aExcludeTarget);
            E::ModuleCache()->Set(
                $data,
                "{$sTargetType}_count_favourite_user_{$sUserId}_{$s}",
                array(
                    "favourite_{$sTargetType}_change",
                    "favourite_{$sTargetType}_change_user_{$sUserId}"
                ),
                60 * 60 * 24 * 1
            );
        }
        return $data;
    }

    /**
     * Получает список комментариев к записям открытых блогов
     * из избранного указанного пользователя
     *
     * @param  int $sUserId      ID пользователя
     * @param  int $iCurrPage    Номер страницы
     * @param  int $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetFavouriteOpenCommentsByUserId($sUserId, $iCurrPage, $iPerPage) {

        if (false === ($data = E::ModuleCache()->Get("comment_favourite_user_{$sUserId}_{$iCurrPage}_{$iPerPage}_open"))) {
            $data = array(
                'collection' => $this->oMapper->GetFavouriteOpenCommentsByUserId(
                    $sUserId, $iCount, $iCurrPage, $iPerPage
                ),
                'count'      => $iCount
            );
            E::ModuleCache()->Set(
                $data,
                "comment_favourite_user_{$sUserId}_{$iCurrPage}_{$iPerPage}_open",
                array(
                    "favourite_comment_change",
                    "favourite_comment_change_user_{$sUserId}"
                ),
                60 * 60 * 24 * 1
            );
        }
        return $data;
    }

    /**
     * Возвращает число комментариев к открытым блогам в избранном по ID пользователя
     *
     * @param  int $sUserId    ID пользователя
     *
     * @return array
     */
    public function GetCountFavouriteOpenCommentsByUserId($sUserId) {

        if (false === ($data = E::ModuleCache()->Get("comment_count_favourite_user_{$sUserId}_open"))) {
            $data = $this->oMapper->GetCountFavouriteOpenCommentsByUserId($sUserId);
            E::ModuleCache()->Set(
                $data,
                "comment_count_favourite_user_{$sUserId}_open",
                array(
                    "favourite_comment_change",
                    "favourite_comment_change_user_{$sUserId}"
                ),
                60 * 60 * 24 * 1
            );
        }
        return $data;
    }

    /**
     * Получает список топиков из открытых блогов
     * из избранного указанного пользователя
     *
     * @param  int $sUserId      ID пользователя
     * @param  int $iCurrPage    Номер страницы
     * @param  int $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetFavouriteOpenTopicsByUserId($sUserId, $iCurrPage, $iPerPage) {

        if (false === ($data = E::ModuleCache()->Get("topic_favourite_user_{$sUserId}_{$iCurrPage}_{$iPerPage}_open"))) {
            $data = array(
                'collection' => $this->oMapper->GetFavouriteOpenTopicsByUserId(
                    $sUserId, $iCount, $iCurrPage, $iPerPage
                ),
                'count'      => $iCount
            );
            E::ModuleCache()->Set(
                $data,
                "topic_favourite_user_{$sUserId}_{$iCurrPage}_{$iPerPage}_open",
                array(
                    "favourite_topic_change",
                    "favourite_topic_change_user_{$sUserId}"
                ),
                60 * 60 * 24 * 1
            );
        }
        return $data;
    }

    /**
     * Возвращает число топиков в открытых блогах из избранного по ID пользователя
     *
     * @param  string $sUserId    ID пользователя
     *
     * @return array
     */
    public function GetCountFavouriteOpenTopicsByUserId($sUserId) {

        if (false === ($data = E::ModuleCache()->Get("topic_count_favourite_user_{$sUserId}_open"))) {
            $data = $this->oMapper->GetCountFavouriteOpenTopicsByUserId($sUserId);
            E::ModuleCache()->Set(
                $data,
                "topic_count_favourite_user_{$sUserId}_open",
                array(
                    "favourite_topic_change",
                    "favourite_topic_change_user_{$sUserId}"
                ),
                60 * 60 * 24 * 1
            );
        }
        return $data;
    }

    /**
     * Добавляет таргет в избранное
     *
     * @param  ModuleFavourite_EntityFavourite $oFavourite Объект избранного
     *
     * @return bool
     */
    public function AddFavourite(ModuleFavourite_EntityFavourite $oFavourite) {

        if (!$oFavourite->getTags()) {
            $oFavourite->setTags('');
        }
        $this->SetFavouriteTags($oFavourite);
        //чистим зависимые кеши
        E::ModuleCache()->Clean(
            Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array("favourite_{$oFavourite->getTargetType()}_change_user_{$oFavourite->getUserId()}")
        );
        E::ModuleCache()->Delete(
            "favourite_{$oFavourite->getTargetType()}_{$oFavourite->getTargetId()}_{$oFavourite->getUserId()}"
        );
        return $this->oMapper->AddFavourite($oFavourite);
    }

    /**
     * Обновляет запись об избранном
     *
     * @param ModuleFavourite_EntityFavourite $oFavourite    Объект избранного
     *
     * @return bool
     */
    public function UpdateFavourite(ModuleFavourite_EntityFavourite $oFavourite) {

        if (!$oFavourite->getTags()) {
            $oFavourite->setTags('');
        }
        $this->SetFavouriteTags($oFavourite);
        E::ModuleCache()->Clean(
            Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array("favourite_{$oFavourite->getTargetType()}_change_user_{$oFavourite->getUserId()}")
        );
        E::ModuleCache()->Delete(
            "favourite_{$oFavourite->getTargetType()}_{$oFavourite->getTargetId()}_{$oFavourite->getUserId()}"
        );
        return $this->oMapper->UpdateFavourite($oFavourite);
    }

    /**
     * Устанавливает список тегов для избранного
     *
     * @param ModuleFavourite_EntityFavourite $oFavourite    Объект избранного
     * @param bool                            $bAddNew       Добавлять новые теги или нет
     */
    public function SetFavouriteTags($oFavourite, $bAddNew = true) {
        /**
         * Удаляем все теги
         */
        $this->oMapper->DeleteTags($oFavourite);
        /**
         * Добавляем новые
         */
//      issue 252, {@link https://github.com/altocms/altocms/issues/252} В избранном не отображаются теги
//      Свойство $oFavourite->getTags() содержит только пользовательские теги, а не все теги избранного объекта,
//      соответственно при отсутствии пользовательских тегов в условие не заходили и теги избранного
//      объекта не добалялись.
//      if ($bAddNew && $oFavourite->getTags()) {
        if ($bAddNew) {
            /**
             * Добавляем теги объекта избранного, если есть
             */
            if ($aTags = $this->GetTagsTarget($oFavourite->getTargetType(), $oFavourite->getTargetId())) {
                foreach ($aTags as $sTag) {
                    /** @var ModuleFavourite_EntityTag $oTag */
                    $oTag = E::GetEntity('ModuleFavourite_EntityTag', $oFavourite->getAllProps());
                    $oTag->setText(htmlspecialchars($sTag));
                    $oTag->setIsUser(0);
                    $this->oMapper->AddTag($oTag);
                }
            }
            /**
             * Добавляем пользовательские теги
             */
            foreach ($oFavourite->getTagsArray() as $sTag) {
                $oTag = E::GetEntity('ModuleFavourite_EntityTag', $oFavourite->getAllProps());
                $oTag->setText($sTag); // htmlspecialchars уже используется при установке тегов
                $oTag->setIsUser(1);
                $this->oMapper->AddTag($oTag);
            }
        }
    }

    /**
     * Удаляет таргет из избранного
     *
     * @param  ModuleFavourite_EntityFavourite $oFavourite    Объект избранного
     *
     * @return bool
     */
    public function DeleteFavourite(ModuleFavourite_EntityFavourite $oFavourite) {

        $this->SetFavouriteTags($oFavourite, false);
        //чистим зависимые кеши
        E::ModuleCache()->Clean(
            Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array("favourite_{$oFavourite->getTargetType()}_change_user_{$oFavourite->getUserId()}")
        );
        E::ModuleCache()->Delete(
            "favourite_{$oFavourite->getTargetType()}_{$oFavourite->getTargetId()}_{$oFavourite->getUserId()}"
        );
        return $this->oMapper->DeleteFavourite($oFavourite);
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

        if (!is_array($aTargetId)) {
            $aTargetId = array($aTargetId);
        }

        E::ModuleCache()->Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("favourite_{$sTargetType}_change"));
        return $this->oMapper->SetFavouriteTargetPublish($aTargetId, $sTargetType, $iPublish);
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

        if (!is_array($aTargetsId)) {
            $aTargetsId = array($aTargetsId);
        }
        $this->DeleteTagByTarget($aTargetsId, $sTargetType);
        $bResult = $this->oMapper->DeleteFavouriteByTargetId($aTargetsId, $sTargetType);

        // * Чистим зависимые кеши
        E::ModuleCache()->Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("favourite_{$sTargetType}_change"));
        return $bResult;
    }

    /**
     * Удаление тегов по таргету
     *
     * @param   array  $aTargetsId     - Список ID владельцев
     * @param   string $sTargetType    - Тип владельца
     *
     * @return  bool
     */
    public function DeleteTagByTarget($aTargetsId, $sTargetType) {

        return $this->oMapper->DeleteTagByTarget($aTargetsId, $sTargetType);
    }

    /**
     * Возвращает список тегов для объекта избранного
     *
     * @param string $sTargetType    Тип владельца
     * @param int    $nTargetId      ID владельца
     *
     * @return bool|array
     */
    public function GetTagsTarget($sTargetType, $nTargetId) {

        $sMethod = 'GetTagsTarget' . F::StrCamelize($sTargetType);
        if (method_exists($this, $sMethod)) {
            return $this->$sMethod($nTargetId);
        }
        return false;
    }

    /**
     * Возвращает наиболее часто используемые теги
     *
     * @param int    $iUserId        ID пользователя
     * @param string $sTargetType    Тип владельца
     * @param bool   $bIsUser        Возвращает все теги ли только пользовательские
     * @param int    $iLimit         Количество элементов
     *
     * @return array
     */
    public function GetGroupTags($iUserId, $sTargetType, $bIsUser, $iLimit) {

        return $this->oMapper->GetGroupTags($iUserId, $sTargetType, $bIsUser, $iLimit);
    }

    /**
     * Возвращает список тегов по фильтру
     *
     * @param array $aFilter      Фильтр
     * @param array $aOrder       Сортировка
     * @param int   $iCurrPage    Номер страницы
     * @param int   $iPerPage     Количество элементов на страницу
     *
     * @return array('collection'=>array,'count'=>int)
     */
    public function GetTags($aFilter, $aOrder, $iCurrPage, $iPerPage) {

        return array('collection' => $this->oMapper->GetTags($aFilter, $aOrder, $iCount, $iCurrPage, $iPerPage),
                     'count'      => $iCount);
    }

    /**
     * Возвращает список тегов для топика, название метода формируется автоматически из GetTagsTarget()
     *
     * @see GetTagsTarget
     *
     * @param int $iTargetId    ID владельца
     *
     * @return bool|array
     */
    public function GetTagsTargetTopic($iTargetId) {

        if ($oTopic = E::ModuleTopic()->GetTopicById($iTargetId)) {
            return $oTopic->getTagsArray();
        }
        return false;
    }
}

// EOF