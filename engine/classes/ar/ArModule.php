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
                    $oEntity->_setRelationsData([]);
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
                $aChildren = [];
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
                $aAncestors = [];
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
                $aDescendants = [];
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

        return $this->find()->one($xValue);
    }

    /**
     * @param mixed[] $aValues
     *
     * @return EntityCollection
     *
     * @throws Exception
     */
    public function findAll($aValues = []) {

        return $this->find()->all($aValues);
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

        $aIndexedEntities = [];
        foreach ($aEntities as $oEntity) {
            $sKey = in_array('#index-from-primary', $aFilter) || (!empty($aFilter['#index-from']) && $aFilter['#index-from'] == '#primary')
                ? $oEntity->_getPrimaryKey()
                : $oEntity->_getField($aFilter['#index-from']);
            $aIndexedEntities[$oEntity->getProp($sKey)] = $oEntity;
        }
        return $aIndexedEntities;
    }

}

// EOF