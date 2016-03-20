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

    /** Relation types */
    const RELATION_HAS_ONE  = 'rel_one';
    const RELATION_HAS_MANY = 'rel_many';
    const RELATION_HAS_STAT = 'rel_stat';
    const RELATION_TREE     = 'rel_tree';

    /** Statuses of EntityRecord */
    const RECORD_STATUS_NEW     = 0;
    const RECORD_STATUS_SAVED   = 1;
    const RECORD_STATUS_CHANGED = 2;
    const RECORD_STATUS_DELETED = 9;
    
    /**
     * Mapper of Active Records
     *
     * @var ArMapper
     */
    protected $oMapper = null;

    /**
     * Инициализация
     */
    public function init() {

    }

    /**
     * @return ArMapper
     */
    public function getMapper() {

        if (!$this->oMapper) {
            $this->oMapper = new ArMapper(E::ModuleDatabase()->GetConnect());
        }
        return $this->oMapper;
    }

    public static function Sql($sSql) {

        return new Expression($sSql);
    }

    protected function _entityTag($oEntity) {

        return strtolower(E::ModulePlugin()->getRootDelegater('entity', get_class($oEntity)));
    }
    
    /**
     * Добавление сущности в БД
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return EntityRecord|bool
     */
    public function insertEntity($oEntity) {

        $xResult = $this->getMapper()->insertEntity($oEntity);

        if ($xResult !== false) {
            // сбрасываем кеш
            $sTag = $this->_entityTag($oEntity) . '_save';
            E::ModuleCache()->CleanByTags([$sTag]);

            if (is_int($xResult) && $xResult > 0) {
                // есть автоинкремент, устанавливаем его
                $oEntity->setProp($oEntity->getPrimaryKey(), (int)$xResult);
            }
            $oEntity->setRecordStatus(ArModule::RECORD_STATUS_SAVED);
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
    public function updateEntity($oEntity) {

        $xResult = $this->getMapper()->updateEntity($oEntity);

        if ($xResult !== false) {
            // сбрасываем кеш
            $sTag = $this->_entityTag($oEntity) . '_save';
            E::ModuleCache()->CleanByTags([$sTag]);

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
    public function save($oEntity) {

        if ($oEntity->isNew()) {
            return $this->insertEntity($oEntity);
        } else {
            return $this->updateEntity($oEntity);
        }
    }

    /**
     * Удаление сущности из БД
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return EntityRecord|bool
     */
    public function delete($oEntity) {

        $xResult = $this->getMapper()->deleteEntity($oEntity);
        if ($xResult !== false) {
            // сбрасываем кеш
            $sEntity = E::ModulePlugin()->GetRootDelegater('entity', get_class($oEntity));
            E::ModuleCache()->CleanByTags([$sEntity . '_delete']);

            $oEntity->setRecordStatus(ArModule::RECORD_STATUS_DELETED);
            return $oEntity;
        }
        return false;
    }

    /**
     * @param string $sEntityClass
     *
     * @return \Entity
     */
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
     * Create new entity
     *
     * @param null $sEntity
     *
     * @return EntityRecord
     *
     * @throws \Exception
     */
    public function make($sEntity = null) {

        $oEntity = $this->_getEntity($sEntity);
        if (!($oEntity instanceof EntityRecord)) {
            throw new \Exception('Entity class "' . get_class($oEntity) . '" is not instance of EntityRecord');
        }

        return $oEntity;
    }

    /**
     * @param string|EntityRecord $xEntity
     *
     * @return Builder
     *
     * @throws \Exception
     */
    public function find($xEntity = null) {

        if (!is_object($xEntity)) {
            $oEntity = $this->_getEntity($xEntity);
        } else {
            $oEntity = $xEntity;
        }
        if (!($oEntity instanceof EntityRecord)) {
            throw new \Exception('Entity class "' . get_class($oEntity) . '" is not instance of EntityRecord');
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
     * @throws \Exception
     */
    public function findOne($xValue = null) {

        return $this->find()->one($xValue);
    }

    /**
     * @param mixed[] $aValues
     *
     * @return EntityCollection
     *
     * @throws \Exception
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