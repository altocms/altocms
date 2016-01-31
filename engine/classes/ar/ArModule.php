<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

namespace alto\engine\ar;
use \E as E, \F as F, \C as C;

/**
 * Абстракция модуля ORM
 * Предоставляет базовые методы для работы с EntityRecord, например,
 * <pre>
 *    $aUsers=E::ModuleUser()->GetUserItemsByAgeAndSex(18,'male');
 * </pre>
 *
 * @package alto\engine\ar
 * @since   1.2
 */
abstract class ArModule extends \Module {

    /**
     * Relation types
     */
    const RELATION_HAS_ONE  = 'has_one';
    const RELATION_HAS_MANY = 'has_many';
    const RELATION_HAS_STAT = 'has_stat';
    const RELATION_TREE     = 'tree';

    /**
     * Объект маппера ORM
     *
     * @var ArMapper
     */
    protected $oMapper = null;

    /**
     * Инициализация
     * В наследнике этот метод нельзя перекрывать, необходимо вызывать через parent::Init();
     *
     */
    public function Init() {

        $this->_loadMapperORM();
    }

    public function getMapper() {

        if (!$this->oMapper) {
            $this->oMapper = new ArMapper(E::ModuleDatabase()->GetConnect());
        }
        return $this->oMapper;
    }

    public static function Sql($sSql) {

        return new Expression($sSql);
    }

    /**
     * Загрузка маппера ORM
     *
     */
    protected function _loadMapperORM() {

        $this->oMapper = new ArMapper(E::ModuleDatabase()->GetConnect());
    }

    /**
     * Добавление сущности в БД
     * Вызывается не напрямую, а через сущность, например
     * <pre>
     *  $oUser->setName('Claus');
     *    $oUser->Add();
     * </pre>
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return EntityRecord|bool
     */
    protected function _addEntity($oEntity) {

        $xResult = $this->oMapper->addEntity($oEntity);

        // сбрасываем кеш
        if ($xResult === 0 || $xResult) {
            $sEntity = E::ModulePlugin()->GetRootDelegater('entity', get_class($oEntity));
            E::ModuleCache()->Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($sEntity . '_save'));
        }
        if ($xResult === 0) {
            // у таблицы нет автоинремента
            return $oEntity;
        } elseif ($xResult) {
            // есть автоинкремент, устанавливаем его
            //$oEntity->_setData(array($oEntity->_getPrimaryKey() => $res));
            $oEntity->setProp($oEntity->_getPrimaryKey(), $xResult);
            return $oEntity;
        }
        return false;
    }

    /**
     * Обновление сущности в БД
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return EntityRecord|bool
     */
    protected function _updateEntity($oEntity) {

        $res = $this->oMapper->UpdateEntity($oEntity);
        if ($res === 0 || $res) { // запись не изменилась, либо изменилась
            // сбрасываем кеш
            $sEntity = E::ModulePlugin()->GetRootDelegater('entity', get_class($oEntity));
            E::ModuleCache()->Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($sEntity . '_save'));
            return $oEntity;
        }
        return false;
    }

    /**
     * Сохранение сущности в БД
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return EntityRecord|bool
     */
    protected function _saveEntity($oEntity) {

        if ($oEntity->isNew()) {
            return $this->_addEntity($oEntity);
        } else {
            return $this->_UpdateEntity($oEntity);
        }
    }

    /**
     * Удаление сущности из БД
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return EntityRecord|bool
     */
    protected function _deleteEntity($oEntity) {

        $res = $this->oMapper->DeleteEntity($oEntity);
        if ($res) {
            // сбрасываем кеш
            $sEntity = E::ModulePlugin()->GetRootDelegater('entity', get_class($oEntity));
            E::ModuleCache()->Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($sEntity . '_delete'));

            return $oEntity;
        }
        return false;
    }

    /**
     * Обновляет данные сущности из БД
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return EntityRecord|bool
     */
    protected function _reloadEntity($oEntity) {

        if ($sPrimaryKey = $oEntity->_getPrimaryKey()) {
            if ($sPrimaryKeyValue = $oEntity->getProp($sPrimaryKey)) {
                if ($oEntityNew = $this->GetByFilter(
                    array($sPrimaryKey => $sPrimaryKeyValue), E::GetEntityName($oEntity)
                )
                ) {
                    $oEntity->_setData($oEntityNew->_getData());
                    $oEntity->_setRelationsData(array());
                    return $oEntity;
                }
            }
        }
        return false;
    }

    /**
     * Список полей сущности
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return array
     */
    protected function _ShowColumnsFrom($oEntity) {

        return $this->oMapper->ShowColumnsFrom($oEntity);
    }

    /**
     * Primary индекс сущности
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return array
     */
    protected function _ShowPrimaryIndexFrom($oEntity) {

        return $this->oMapper->ShowPrimaryIndexFrom($oEntity);
    }

    /**
     * Для сущности со связью RELATION_TREE возвращает список прямых потомков
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return array
     */
    protected function _GetChildrenOfEntity($oEntity) {

        if (in_array(EntityRecord::RELATION_TREE, $oEntity->_getRelations())) {
            $aRelationsData = $oEntity->_getRelationsData();
            if (array_key_exists('children', $aRelationsData)) {
                $aChildren = $aRelationsData['children'];
            } else {
                $aChildren = array();
                if ($sPrimaryKey = $oEntity->_getPrimaryKey()) {
                    if ($sPrimaryKeyValue = $oEntity->getProp($sPrimaryKey)) {
                        $aChildren = $this->GetItemsByFilter(
                            array('parent_id' => $sPrimaryKeyValue), E::GetEntityName($oEntity)
                        );
                    }
                }
            }
            if (is_array($aChildren)) {
                $oEntity->setChildren($aChildren);
                return $aChildren;
            }
        }
        return false;
    }

    /**
     * Для сущности со связью RELATION_TREE возвращает предка
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return EntityRecord|bool
     */
    protected function _GetParentOfEntity($oEntity) {

        if (in_array(EntityRecord::RELATION_TREE, $oEntity->_getRelations())) {
            $aRelationsData = $oEntity->_getRelationsData();
            if (array_key_exists('parent', $aRelationsData)) {
                $oParent = $aRelationsData['parent'];
            } else {
                $oParent = '%%NULL_PARENT%%';
                if ($sPrimaryKey = $oEntity->_getPrimaryKey()) {
                    if ($sParentId = $oEntity->getParentId()) {
                        $oParent = $this->GetByFilter(
                            array($sPrimaryKey => $sParentId), E::GetEntityName($oEntity)
                        );
                    }
                }
            }
            if (!is_null($oParent)) {
                $oEntity->setParent($oParent);
                return $oParent;
            }
        }
        return false;
    }

    /**
     * Для сущности со связью RELATION_TREE возвращает список всех предков
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return array
     */
    protected function _GetAncestorsOfEntity($oEntity) {

        if (in_array(EntityRecord::RELATION_TREE, $oEntity->_getRelations())) {
            $aRelationsData = $oEntity->_getRelationsData();
            if (array_key_exists('ancestors', $aRelationsData)) {
                $aAncestors = $aRelationsData['ancestors'];
            } else {
                $aAncestors = array();
                $oEntityParent = $oEntity->getParent();
                while (is_object($oEntityParent)) {
                    $aAncestors[] = $oEntityParent;
                    $oEntityParent = $oEntityParent->getParent();
                }
            }
            if (is_array($aAncestors)) {
                $oEntity->setAncestors($aAncestors);
                return $aAncestors;
            }
        }
        return false;
    }

    /**
     * Для сущности со связью RELATION_TREE возвращает список всех потомков
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return array
     */
    protected function _GetDescendantsOfEntity($oEntity) {

        if (in_array(EntityRecord::RELATION_TREE, $oEntity->_getRelations())) {
            $aRelationsData = $oEntity->_getRelationsData();
            if (array_key_exists('descendants', $aRelationsData)) {
                $aDescendants = $aRelationsData['descendants'];
            } else {
                $aDescendants = array();
                if ($aChildren = $oEntity->getChildren()) {
                    $aTree = self::buildTree($aChildren);
                    foreach ($aTree as $aItem) {
                        $aDescendants[] = $aItem['entity'];
                    }
                }
            }
            if (is_array($aDescendants)) {
                $oEntity->setDescendants($aDescendants);
                return $aDescendants;
            }
        }
        return false;
    }

    protected function _getEntity($sEntityClass = null) {

        $sModuleClass = get_called_class();
        if (is_null($sEntityClass)) {
            $sEntityClass = E::GetPluginPrefix($sModuleClass)
                . 'Module' . E::GetModuleName($sModuleClass) . '_Entity' . E::GetModuleName($sModuleClass);
        } elseif (!substr_count($sEntityClass, '_')) {
            $sEntityClass = E::GetPluginPrefix($sModuleClass)
                . 'Module' . E::GetModuleName($sModuleClass) . '_Entity' . $sEntityClass;
        }

        return E::GetEntity($sEntityClass);
    }

    /**
     * Create nee entity
     *
     * @param null $sEntity
     *
     * @return Entity
     */
    public function make($sEntity = null) {

        $oEntity = $this->_getEntity($sEntity);

        return $oEntity;
    }

    /**
     * @param string|EntityRecord $xEntity
     * @return Builder
     * @throws Exception
     */
    public function find($xEntity = null) {

        if (!is_object($xEntity)) {
            $oEntity = $this->_getEntity($xEntity);
        } else {
            $oEntity = $xEntity;
        }
        if (!($oEntity instanceof EntityRecord)) {
            throw new Exception('Entity class "' . get_class($oEntity) . '" is not instance of EntityRecord');
        }
        $oEntity->setModule($this);
        $oBuilder = new Builder($oEntity);

        return $oEntity->find($oBuilder);
    }

    /**
     * @param mixed $xValue
     *
     * @return EntityRecord
     *
     * @throws Exception
     */
    public function findOne($xValue = null) {

        return $this->find()->all($xValue);
    }

    /**
     * @param mixed[] $aValues
     *
     * @return Collection
     *
     * @throws Exception
     */
    public function findAll($aValues = array()) {

        return $this->find()->all($aValues);
    }

    /**
     * Для сущностей со связью RELATION_TREE возвращает список сущностей в виде дерева
     *
     * @param array  $aFilter        Фильтр
     * @param string $sEntityFull    Название класса сущности
     *
     * @return array|bool
     */
    public function LoadTree($aFilter = array(), $sEntityFull = null) {

        if (is_null($sEntityFull)) {
            $sEntityFull = E::GetPluginPrefix($this)
                . 'Module' . E::GetModuleName($this) . '_Entity' . E::GetModuleName(get_class($this));
        } elseif (!substr_count($sEntityFull, '_')) {
            $sEntityFull = E::GetPluginPrefix($this)
                . 'Module' . E::GetModuleName($this) . '_Entity' . $sEntityFull;
        }
        if ($oEntityDefault = E::GetEntity($sEntityFull)) {
            if (in_array(EntityRecord::RELATION_TREE, $oEntityDefault->_getRelations())) {
                if ($sPrimaryKey = $oEntityDefault->_getPrimaryKey()) {
                    if ($aItems = $this->GetItemsByFilter($aFilter, $sEntityFull)) {
                        $aItemsById = array();
                        $aItemsByParentId = array();
                        foreach ($aItems as $oEntity) {
                            $oEntity->setChildren(array());
                            $aItemsById[$oEntity->getProp($sPrimaryKey)] = $oEntity;
                            if (empty($aItemsByParentId[$oEntity->getParentId()])) {
                                $aItemsByParentId[$oEntity->getParentId()] = array();
                            }
                            $aItemsByParentId[$oEntity->getParentId()][] = $oEntity;
                        }
                        foreach ($aItemsByParentId as $iParentId => $aItems) {
                            if ($iParentId > 0) {
                                $aItemsById[$iParentId]->setChildren($aItems);
                                foreach ($aItems as $oEntity) {
                                    $oEntity->setParent($aItemsById[$iParentId]);
                                }
                            }
                        }
                        return $aItemsByParentId[0];
                    }
                }
            }
        }
        return false;
    }

    /**
     * Returns assotiative array, indexed by PRIMARY KEY or another field.
     *
     * @param array $aEntities    Список сущностей
     * @param array $aFilter      Фильтр
     *
     * @return array
     */
    protected function _setIndexesFromField($aEntities, $aFilter) {

        $aIndexedEntities = array();
        foreach ($aEntities as $oEntity) {
            $sKey = in_array('#index-from-primary', $aFilter) || (!empty($aFilter['#index-from']) && $aFilter['#index-from'] == '#primary')
                ? $oEntity->_getPrimaryKey()
                : $oEntity->_getField($aFilter['#index-from']);
            $aIndexedEntities[$oEntity->getProp($sKey)] = $oEntity;
        }
        return $aIndexedEntities;
    }

    /**
     * Ставим хук на вызов неизвестного метода и считаем что хотели вызвать метод какого либо модуля.
     * Также обрабатывает различные ORM методы сущности, например
     * <pre>
     * $oUser->Save();
     * $oUser->Delete();
     * </pre>
     * И методы модуля ORM, например
     * <pre>
     *    E::ModuleUser()->GetUserItemsByName('Claus');
     *    E::ModuleUser()->GetUserItemsAll();
     * </pre>
     *
     * @see Engine::_CallModule
     *
     * @param string $sName Имя метода
     * @param array  $aArgs Аргументы
     *
     * @return mixed
     */
    public function __call($sName, $aArgs) {

        if (preg_match("@^add([\w]+)$@i", $sName, $aMatch)) {
            return $this->_addEntity($aArgs[0]);
        }

        if (preg_match("@^update([\w]+)$@i", $sName, $aMatch)) {
            return $this->_UpdateEntity($aArgs[0]);
        }

        if (preg_match("@^save([\w]+)$@i", $sName, $aMatch)) {
            return $this->_SaveEntity($aArgs[0]);
        }

        if (preg_match("@^delete([\w]+)$@i", $sName, $aMatch)) {
            return $this->_DeleteEntity($aArgs[0]);
        }

        if (preg_match("@^reload([\w]+)$@i", $sName, $aMatch)) {
            return $this->_ReloadEntity($aArgs[0]);
        }

        if (preg_match("@^showcolumnsfrom([\w]+)$@i", $sName, $aMatch)) {
            return $this->_ShowColumnsFrom($aArgs[0]);
        }

        if (preg_match("@^showprimaryindexfrom([\w]+)$@i", $sName, $aMatch)) {
            return $this->_ShowPrimaryIndexFrom($aArgs[0]);
        }

        if (preg_match("@^getchildrenof([\w]+)$@i", $sName, $aMatch)) {
            return $this->_GetChildrenOfEntity($aArgs[0]);
        }

        if (preg_match("@^getparentof([\w]+)$@i", $sName, $aMatch)) {
            return $this->_GetParentOfEntity($aArgs[0]);
        }

        if (preg_match("@^getdescendantsof([\w]+)$@i", $sName, $aMatch)) {
            return $this->_GetDescendantsOfEntity($aArgs[0]);
        }

        if (preg_match("@^getancestorsof([\w]+)$@i", $sName, $aMatch)) {
            return $this->_GetAncestorsOfEntity($aArgs[0]);
        }

        if (preg_match("@^loadtreeof([\w]+)$@i", $sName, $aMatch)) {
            $sEntityFull = array_key_exists(1, $aMatch) ? $aMatch[1] : null;
            return $this->LoadTree($aArgs[0], $sEntityFull);
        }

        $sNameUnderscore = F::StrUnderscore($sName);
        $iEntityPosEnd = 0;
        if (strpos($sNameUnderscore, '_items') >= 3) {
            $iEntityPosEnd = strpos($sNameUnderscore, '_items');
        } else {
            if (strpos($sNameUnderscore, '_by') >= 3) {
                $iEntityPosEnd = strpos($sNameUnderscore, '_by');
            } else {
                if (strpos($sNameUnderscore, '_all') >= 3) {
                    $iEntityPosEnd = strpos($sNameUnderscore, '_all');
                }
            }
        }
        if ($iEntityPosEnd && $iEntityPosEnd > 4) {
            $sEntityName = substr($sNameUnderscore, 4, $iEntityPosEnd - 4);
        } else {
            $sEntityName = F::StrUnderscore(E::GetModuleName($this)) . '_';
            $sNameUnderscore = substr_replace($sNameUnderscore, $sEntityName, 4, 0);
            $iEntityPosEnd = strlen($sEntityName) - 1 + 4;
        }

        $sNameUnderscore = substr_replace($sNameUnderscore, str_replace('_', '', $sEntityName), 4, $iEntityPosEnd - 4);

        $sEntityName = F::StrCamelize($sEntityName);

        /**
         * getUserItemsByFilter() get_user_items_by_filter
         */
        if (preg_match("@^get_([a-z]+)((_items)|())_by_filter$@i", $sNameUnderscore, $aMatch)) {
            if ($aMatch[2] == '_items') {
                return $this->GetItemsByFilter($aArgs[0], $sEntityName);
            } else {
                return $this->GetByFilter($aArgs[0], $sEntityName);
            }
        }

        /**
         * getUserItemsByArrayId() get_user_items_by_array_id
         */
        if (preg_match("@^get_([a-z]+)_items_by_array_([_a-z]+)$@i", $sNameUnderscore, $aMatch)) {
            return $this->GetItemsByArray(array($aMatch[2] => $aArgs[0]), $sEntityName);
        }

        /**
         * getUserItemsByJoinTable() get_user_items_by_join_table
         */
        if (preg_match("@^get_([a-z]+)_items_by_join_table$@i", $sNameUnderscore, $aMatch)) {
            return $this->GetItemsByJoinTable($aArgs[0], F::StrCamelize($sEntityName));
        }

        /**
         * getUserByLogin()                    get_user_by_login
         * getUserByLoginAndMail()            get_user_by_login_and_mail
         * getUserItemsByName()                get_user_items_by_name
         * getUserItemsByNameAndActive()    get_user_items_by_name_and_active
         * getUserItemsByDateRegisterGte()    get_user_items_by_date_register_gte        (>=)
         * getUserItemsByProfileNameLike()    get_user_items_by_profile_name_like
         * getUserItemsByCityIdIn()            get_user_items_by_city_id_in
         */
        if (preg_match("@^get_([a-z]+)((_items)|())_by_([_a-z]+)$@i", $sNameUnderscore, $aMatch)) {
            $aAliases = array(
                '_gte' => ' >=', '_lte' => ' <=', '_gt' => ' >', '_lt' => ' <', '_like' => ' LIKE', '_in' => ' IN');
            $sSearchParams = str_replace(array_keys($aAliases), array_values($aAliases), $aMatch[5]);
            $aSearchParams = explode('_and_', $sSearchParams);
            $aSplit = array_chunk($aArgs, count($aSearchParams));
            $aFilter = array_combine($aSearchParams, $aSplit[0]);
            if (isset($aSplit[1][0])) {
                $aFilter = array_merge($aFilter, $aSplit[1][0]);
            }
            if ($aMatch[2] == '_items') {
                return $this->GetItemsByFilter($aFilter, $sEntityName);
            } else {
                return $this->GetByFilter($aFilter, $sEntityName);
            }
        }

        /**
         * getUserAll()            get_user_all        OR
         * getUserItemsAll()    get_user_items_all
         */
        if (preg_match("@^get_([a-z]+)_all$@i", $sNameUnderscore, $aMatch)
            || preg_match("@^get_([a-z]+)_items_all$@i", $sNameUnderscore, $aMatch)
        ) {
            $aFilter = array();
            if (isset($aArgs[0]) && is_array($aArgs[0])) {
                $aFilter = $aArgs[0];
            }
            return $this->GetItemsByFilter($aFilter, $sEntityName);
        }

        return $this->oEngine->_CallModule($sName, $aArgs);
    }

    /**
     * Построение дерева
     *
     * @param array $aItems    Список сущностей
     * @param array $aList
     * @param int   $iLevel    Текущий уровень вложенности
     *
     * @return array
     */
    static public function buildTree($aItems, $aList = array(), $iLevel = 0) {

        foreach ($aItems as $oEntity) {
            $aChildren = $oEntity->getChildren();
            $bHasChildren = !empty($aChildren);
            $sEntityId = $oEntity->getProp($oEntity->_getPrimaryKey());
            $aList[$sEntityId] = array(
                'entity'         => $oEntity,
                'parent_id'      => $oEntity->getParentId(),
                'children_count' => $bHasChildren ? count($aChildren) : 0,
                'level'          => $iLevel,
            );
            if ($bHasChildren) {
                $aList = self::buildTree($aChildren, $aList, $iLevel + 1);
            }
        }
        return $aList;
    }

}

// EOF