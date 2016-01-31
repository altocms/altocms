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
 * Абстрактный класс сущности ORM - аналог active record
 * Позволяет без написания SQL запросов работать с базой данных.
 * <pre>
 * $oUser=E::ModuleUser()->GetUserById(1);
 * $oUser->setName('Claus');
 * $oUser->Update();
 * </pre>
 * Возможно получать списки объектов по фильтру:
 * <pre>
 * $aUsers=E::ModuleUser()->GetUserItemsByAgeAndSex(18,'male');
 * // эквивалентно
 * $aUsers=E::ModuleUser()->GetUserItemsByFilter(array('age'=>18,'sex'=>'male'));
 * // эквивалентно
 * $aUsers=E::ModuleUser()->GetUserItemsByFilter(array('#where'=>array('age = ?d and sex = ?' => array(18,'male'))));
 * </pre>
 *
 * @package engine.ar
 * @since   1.2
 */
abstract class EntityRecord extends \Entity {

    const PROP_IS_PROP = 1;
    const PROP_IS_FIELD = 2;
    const PROP_IS_CALLABLE = 3;
    const PROP_IS_RELATION = 4;

    /**
     * Массив исходных данных сущности
     *
     * @var array
     */
    protected $_aOriginalData = array();

    protected $sPrimaryKey;

    protected $sTableName;

    protected $aFields = array();

    /**
     * Список полей таблицы сущности
     *
     * @var array
     */
    protected $aTableFields = null;

    /**
     * Список данных связей
     *
     * @var array
     */
    protected $aRelationsData = array();

    /**
     * Флаг новая сущность или нет
     *
     * @var bool
     */
    protected $bIsNew = true;

    static protected $oInstance;

    /**
     * Установка связей
     * @see Entity::__construct
     *
     * @param bool $aParam Ассоциативный массив данных сущности
     */
    public function __construct($aParam = false) {

        parent::__construct($aParam);
    }

    public function __sleep() {

        foreach($this->_aData as $sKey => $xVal) {
            if (substr($sKey, 0, 2) === '__') {
                unset($this->_aData[$sKey]);
            }
        }
        $aProperties = get_class_vars(get_called_class());
        $aProperties = array_keys($aProperties);

        return $aProperties;
    }

    /**
     * @return EntityRecord
     */
    static public function instance() {

        if (!self::$oInstance) {
            self::$oInstance = E::GetEntity(get_called_class());
        }
        return self::$oInstance;
    }

    static public function tableName() {

        return self::instance()->getTableName();
    }

    public function clearProps() {

        foreach($this->_aData as $sKey => $xVal) {
            if (substr($sKey, 0, 2) === '__') {
                unset($this->_aData[$sKey]);
            }
        }
    }

    /**
     * @return string
     */
    public function getModuleClass() {

        $sModuleClass = $this->getProp('__module_class');
        if (!$sModuleClass) {
            $aInfo = E::GetClassInfo($this, E::CI_MODULE | E::CI_PPREFIX);
            /*
            $sModuleClass = $aInfo[E::CI_MODULE];
            if (!empty($aInfo[E::CI_PPREFIX])) {
                $sModuleClass = $aInfo[E::CI_PPREFIX] . $sModuleClass;
            }
            */
            $sModuleClass = E::ModulePlugin()->GetDelegate('module', $aInfo[E::CI_MODULE]);
            if ($sModuleClass == $aInfo[E::CI_MODULE] && !empty($aInfo[E::CI_PPREFIX])) {
                $sPluginModuleClass = $aInfo[E::CI_PPREFIX] . 'Module' . $sModuleClass;
                if (class_exists($sPluginModuleClass, false)) {
                    // class like "PluginTest_ModuleTest" has no delegates
                    $sModuleClass = $sPluginModuleClass;
                }
            }
            $this->setProp('__module_class', $sModuleClass);
        }
        return $sModuleClass;
    }

    /**
     * @return ArModule
     */
    public function getModule() {

        $oModule = $this->getProp('__module');
        if (!$oModule) {
            $sModuleClass = $this->getModuleClass();
            $oModule = E::Module(str_replace('_', '\\', $sModuleClass));
            $this->setProp('__module', $oModule);
        }
        return $oModule;
    }

    /**
     * @param ArModule $xModule
     */
    public function setModule($xModule) {

        if (is_object($xModule)) {
            $this->setProp('__module', $xModule);
            $this->setProp('__module_class', get_class($xModule));
        } else {
            $this->setProp('__module', null);
            $this->setProp('__module_class', $xModule);
        }
    }

    /**
     * @return ArMapper
     */
    public function getMapper() {

        if ($oModule = $this->getModule()) {
            return $oModule->getMapper();
        }
        return null;
    }
    /**
     * @return string
     */
    public function getTableName() {

        if (empty($this->sTableName)) {
            $sClass = E::ModulePlugin()->GetDelegater('entity', get_called_class());
            $sModuleName = F::StrUnderscore(E::GetModuleName($sClass));
            $sEntityName = F::StrUnderscore(E::GetEntityName($sClass));
            if (strpos($sEntityName, $sModuleName) === 0) {
                $sTable = F::StrUnderscore($sEntityName);
            } else {
                $sTable = F::StrUnderscore($sModuleName) . '_' . F::StrUnderscore($sEntityName);
            }

            // * Если название таблиц переопределено в конфиге, то возвращаем его
            if (C::Get('db.table.' . $sTable)) {
                $this->sTableName = C::Get('db.table.' . $sTable);
            } else {
                $this->sTableName = C::Get('db.table.prefix') . $sTable;
            }
        } elseif ($this->sTableName[0] == '?' && $this->sTableName[0] == '_') {
            return C::Get('db.table.prefix') . substr($this->sTableName, 2);
        }
        return $this->sTableName;
    }

    /**
     * Получение primary key из схемы таблицы
     *
     * @return string|array    Если индекс составной, то возвращает массив полей
     */
    public function getPrimaryKey() {

        if (!$this->sPrimaryKey) {
            if ($aIndex = $this->getModule()->getMapper()->readPrimaryIndexFromTable($this->getTableName())) {
                if (count($aIndex) > 1) {
                    // Составной индекс
                    $this->sPrimaryKey = $aIndex;
                } else {
                    $this->sPrimaryKey = $aIndex[1];
                }
            }
        }
        return $this->sPrimaryKey;
    }

    /**
     * Получение значения primary key
     *
     * @return string
     */
    public function _getPrimaryKeyValue() {

        return $this->getProp($this->_getPrimaryKey());
    }

    /**
     * Новая или нет сущность
     * Новая - еще не сохранялась в БД
     *
     * @return bool
     */
    public function isNew() {

        return $this->bIsNew;
    }

    /**
     * Установка флага "новая"
     *
     * @param bool $bIsNew    Флаг - новая сущность или нет
     */
    public function setNew($bIsNew) {

        $this->bIsNew = $bIsNew;
    }

    public function setCollection($oCollection) {

        $this->setProp('__collection', $oCollection);
        return $this;
    }

    public function getCollection() {

        return $this->getProp('__collection');
    }

    /**
     * @param $oBuilder
     *
     * @return Builder
     */
    public function find($oBuilder) {

        return $oBuilder;
    }

    /**
     * Добавление сущности в БД
     *
     * @return Entity|false
     */
    public function add() {

        if ($this->beforeSave()) {
            if ($res = $this->_Method(__FUNCTION__)) {
                $this->afterSave();
                return $res;
            }
        }
        return false;
    }

    /**
     * Обновление сущности в БД
     *
     * @return Entity|false
     */
    public function update() {

        if ($this->beforeSave()) {
            if ($res = $this->_Method(__FUNCTION__)) {
                $this->afterSave();
                return $res;
            }
        }
        return false;
    }

    /**
     * Сохранение сущности в БД (если новая то создается)
     *
     * @return Entity|false
     */
    public function save() {

        if ($this->beforeSave()) {
            if ($res = $this->_Method(__FUNCTION__)) {
                $this->setNew(false);
                $this->afterSave();
                return $res;
            }
        }
        return false;
    }

    /**
     * Удаление сущности из БД
     *
     * @return Entity|false
     */
    public function delete() {

        if ($this->beforeDelete()) {
            if ($res = $this->_Method(__FUNCTION__)) {
                $this->afterDelete();
                return $res;
            }
        }
        return false;
    }

    /**
     * Обновляет данные сущности из БД
     *
     * @return Entity|false
     */
    public function Reload() {

        return $this->_Method(__FUNCTION__);
    }

    /**
     * Возвращает список полей сущности
     *
     * @return array
     */
    public function readColumns() {

        $aColumns = $this->getMapper()->readColumnsFromTable($this->getTableName());
        return $aColumns;
    }

    /**
     * Хук, срабатывает перед сохранением сущности
     *
     * @return bool
     */
    protected function beforeSave() {

        return true;
    }

    /**
     * Хук, срабатывает после сохранением сущности
     *
     */
    protected function afterSave() {

    }

    /**
     * Хук, срабатывает перед удалением сущности
     *
     * @return bool
     */
    protected function beforeDelete() {

        return true;
    }

    /**
     * Хук, срабатывает после удаления сущности
     *
     */
    protected function afterDelete() {

    }

    /**
     * Для сущности со связью RELATION_TREE возвращает список прямых потомков
     *
     * @return array
     */
    public function getChildren() {

        if (in_array(ArModule::RELATION_TREE, $this->aRelations)) {
            return $this->_Method(__FUNCTION__ . 'Of');
        }
        return $this->__call(__FUNCTION__, array());
    }

    /**
     * Для сущности со связью RELATION_TREE возвращает список всех потомков
     *
     * @return array
     */
    public function getDescendants() {

        if (in_array(ArModule::RELATION_TREE, $this->aRelations)) {
            return $this->_Method(__FUNCTION__ . 'Of');
        }
        return $this->__call(__FUNCTION__, array());
    }

    /**
     * Для сущности со связью RELATION_TREE возвращает предка
     *
     * @return Entity
     */
    public function getParent() {

        if (in_array(ArModule::RELATION_TREE, $this->aRelations)) {
            return $this->_Method(__FUNCTION__ . 'Of');
        }
        return $this->__call(__FUNCTION__, array());
    }

    /**
     * Для сущности со связью RELATION_TREE возвращает список всех предков
     *
     * @return array
     */
    public function getAncestors() {

        if (in_array(ArModule::RELATION_TREE, $this->aRelations)) {
            return $this->_Method(__FUNCTION__ . 'Of');
        }
        return $this->__call(__FUNCTION__, array());
    }

    /**
     * Для сущности со связью RELATION_TREE устанавливает потомков
     *
     * @param array $aChildren    Список потомков
     *
     * @return mixed
     */
    public function setChildren($aChildren = array()) {

        if (in_array(ArModule::RELATION_TREE, $this->aRelations)) {
            $this->aRelationsData['children'] = $aChildren;
        } else {
            $aArgs = func_get_args();
            return $this->__call(__FUNCTION__, $aArgs);
        }
    }

    /**
     * Для сущности со связью RELATION_TREE устанавливает потомков
     *
     * @param array $aDescendants    Список потомков
     *
     * @return mixed
     */
    public function setDescendants($aDescendants = array()) {

        if (in_array(ArModule::RELATION_TREE, $this->aRelations)) {
            $this->aRelationsData['descendants'] = $aDescendants;
        } else {
            $aArgs = func_get_args();
            return $this->__call(__FUNCTION__, $aArgs);
        }
    }

    /**
     * Для сущности со связью RELATION_TREE устанавливает предка
     *
     * @param Entity $oParent    Родитель
     *
     * @return mixed
     */
    public function setParent($oParent = null) {

        if (in_array(ArModule::RELATION_TREE, $this->aRelations)) {
            $this->aRelationsData['parent'] = $oParent;
        } else {
            $aArgs = func_get_args();
            return $this->__call(__FUNCTION__, $aArgs);
        }
    }

    /**
     * Для сущности со связью RELATION_TREE устанавливает предков
     *
     * @param array $oParent    Родитель
     *
     * @return mixed
     */
    public function setAncestors($oParent = null) {

        if (in_array(ArModule::RELATION_TREE, $this->aRelations)) {
            $this->aRelationsData['ancestors'] = $oParent;
        } else {
            $aArgs = func_get_args();
            return $this->__call(__FUNCTION__, $aArgs);
        }
    }

    /**
     * @return ArModule
     */
    static public function model() {

        $sClass = get_called_class();
        $aClassInfoPrim = E::GetClassInfo($sClass, E::CI_MODULE | E::CI_PPREFIX | E::CI_PLUGIN);
        $sModuleName = (!empty($aClassInfoPrim[E::CI_MODULE]) ? $aClassInfoPrim[E::CI_MODULE] : null);
        $sPluginPrefix = (!empty($aClassInfoPrim[E::CI_PPREFIX]) ? $aClassInfoPrim[E::CI_PPREFIX] : null);
        $sPluginName = (!empty($aClassInfoPrim[E::CI_PLUGIN]) ? $aClassInfoPrim[E::CI_PLUGIN] : null);

        // * If Module not exists, try to find its root Delegator
        $aClassInfo = E::GetClassInfo($sPluginPrefix . 'Module_' . $sModuleName, Engine::CI_MODULE);
        if (empty($aClassInfo[E::CI_MODULE]) && $sRootDelegator = E::ModulePlugin()->GetRootDelegater('entity', $sClass)) {
            $sModuleName = E::GetModuleName($sRootDelegator);
            $sPluginName = E::GetPluginName($sRootDelegator);
        }
        if ($sPluginName) {
            $sModuleName = 'Plugin' . $sPluginName . '\\' . $sModuleName;
        }

        return E::Module($sModuleName);
    }

    /**
     * @return ArMapper
     */
    static public function mapper() {

        return static::model()->getMapper();
    }

    /**
     * @param null $aKeys
     *
     * @return array
     */
    public function getAllProps($aKeys = null) {

        $aProps = parent::getAllProps($aKeys);
        if (is_null($aKeys)) {
            foreach($aProps as $sKey => $xVal) {
                if (substr($sKey, 0, 2) == '__') {
                    unset($aProps[$sKey]);
                }
            }
        }
        return $aProps;
    }

    /**
     * Проксирует вызов методов в модуль сущности
     *
     * @param string $sName    Название метода
     *
     * @return mixed
     */
    protected function _Method($sName) {

        $sModuleName = E::GetModuleName($this);
        $sEntityName = E::GetEntityName($this);
        $sPluginPrefix = E::GetPluginPrefix($this);
        $sPluginName = E::GetPluginName($this);
        /**
         * If Module not exists, try to find its root Delegator
         */
        $aClassInfo = E::GetClassInfo($sPluginPrefix . 'Module_' . $sModuleName, Engine::CI_MODULE);
        if (empty($aClassInfo[E::CI_MODULE])
            && $sRootDelegater = E::ModulePlugin()->GetRootDelegater('entity', get_class($this))
        ) {
            $sModuleName = E::GetModuleName($sRootDelegater);
            $sPluginPrefix = E::GetPluginPrefix($sRootDelegater);
            $sPluginName = E::GetPluginName($sRootDelegater);
        }
        //$aCallArgs = array($this);
        //return E::GetInstance()->_CallModule("{$sPluginPrefix}{$sModuleName}_{$sName}{$sEntityName}", $aCallArgs);
        if ($sPluginName) {
            $sModuleName = 'Plugin' . $sPluginName . '\\' . $sModuleName;
        }
        $sMethodName = $sName . $sEntityName;
        return E::Module($sModuleName)->$sMethodName($this);
    }

    /**
     * Устанавливает данные сущности
     *
     * @param array $aData    Ассоциативный массив данных сущности
     */
    public function _setData($aData) {

        if (is_array($aData)) {
            foreach ($aData as $sKey => $val) {
                if (array_key_exists($sKey, $this->aRelations)) {
                    $this->aRelationsData[$sKey] = $val;
                } else {
                    $this->_aData[$sKey] = $val;
                }
            }
            $this->_aOriginalData = $this->_aData;
        }
    }

    /**
     * Возвращает все данные сущности
     *
     * @return array
     */
    public function _getOriginalData() {

        return $this->_aOriginalData;
    }

    public function hasField($sProperty, $sTableField) {

        $this->aFields[$sProperty] = array('field' => $sTableField);
    }

    /**
     * Возвращает список полей сущности
     *
     * @return array
     */
    public function getFieldsInfo() {

        $aFields = $this->getProp('__fields');
        if (is_null($aFields)) {
            if (is_null($this->aTableFields)) {
                $this->aTableFields = $this->readColumns();
            }
            if (!empty($this->aFields)) {
                $aFields = array_merge($this->aTableFields, $this->aFields);
            } else {
                $aFields = $this->aTableFields;
            }
            $this->setProp('__fields', $aFields);
        }

        return $aFields;
    }

    /**
     * @param string $sField          Название поля
     *
     * @return null|string
     */
    public function getFieldName($sField) {

        if ($aFields = $this->getFieldsInfo()) {
            $sFieldKey = strtolower($sField);
            if (isset($aFields[$sFieldKey])) {
                if (isset($aFields[$sFieldKey]['field'])) {
                    return $aFields[$sFieldKey]['field'];
                }
                return $sField;
            }
        }
        return $sField;
    }

    /**
     * Возвращает список связей
     *
     * @return array
     */
    public function getRelations() {

        return $this->aRelationsData;
    }

    /**
     * @param string $sRelType
     * @param string $sField
     * @param string $sRelEntity
     * @param array $aRelFields
     *
     * @return Relation
     */
    public function setRelation($sRelType, $sField, $sRelEntity, $aRelFields) {

        $oRelation = new Relation($sRelType, $this, $sField, $sRelEntity, $aRelFields);
        $this->aRelationsData[$sField] = $oRelation;
        return $oRelation;
    }

    /**
     * @param array $aRelation
     * @param array $aRelFields
     *
     * @return Relation
     */
    public function hasRelOne($aRelation, $aRelFields = null) {

        $sRelEntity = reset($aRelation);
        $sField = key($aRelation);

        return $this->setRelation(ArModule::RELATION_HAS_ONE, $sField, $sRelEntity, $aRelFields);
    }

    /**
     * @param array $aRelation
     * @param array $aRelFields
     *
     * @return Relation
     */
    public function hasRelMany($aRelation, $aRelFields = null) {

        $sRelEntity = reset($aRelation);
        $sField = key($aRelation);

        return $this->setRelation(ArModule::RELATION_HAS_MANY, $sField, $sRelEntity, $aRelFields);
    }

    /**
     * @param array $aRelation
     * @param string $sJuncTable
     * @param null $xJuncToRelation
     * @param null $xJuncToMaster
     *
     * @return Relation
     */
    public function hasRelManyVia($aRelation, $sJuncTable, $xJuncToRelation = null, $xJuncToMaster = null) {

        $sRelEntity = reset($aRelation);
        $sField = key($aRelation);

        if (is_array($xJuncToRelation)) {
            $sRelKey = reset($xJuncToRelation);
            $sJuncRelKey = key($xJuncToRelation);
        } else {
            $sRelKey = $sJuncRelKey = $xJuncToRelation;
        }
        if (is_array($xJuncToMaster)) {
            $sMasterKey = reset($xJuncToMaster);
            $sJuncMasterKey = key($xJuncToMaster);
        } else {
            $sMasterKey = $sJuncMasterKey = $xJuncToMaster;
        }

        return $this
            ->setRelation(ArModule::RELATION_HAS_MANY, $sField, $sRelEntity, array($sRelKey => $sMasterKey))
            ->viaTable($sJuncTable, $sJuncRelKey, $sJuncMasterKey);
    }

    /**
     * @param string $sField
     * @param string $sRelEntity
     * @param array $aRelFields
     *
     * @return Relation
     */
    public function hasRelStat($sField, $sRelEntity, $aRelFields = null) {

        if (is_array($sField) && (is_array($sRelEntity))) {
            $aRelFields = $sRelEntity;
            $sRelEntity = reset($sField);
            $sField = key($sField);
        }
        return $this->setRelation(ArModule::RELATION_HAS_STAT, $sField, $sRelEntity, $aRelFields);
    }

    public function isField($sKey) {

        if ($this->isProp($sKey)) {
            return self::PROP_IS_PROP;
        }

        $sField = $this->getFieldName($sKey);
        if (is_scalar($sField) && $sField != $sKey) {
            return self::PROP_IS_FIELD;
        }
        if (is_callable($sField)) {
            return self::PROP_IS_CALLABLE;
        }

        if (!empty($this->aRelationsData[$sKey])) {
            return self::PROP_IS_RELATION;
        }

        return 0;
    }

    /**
     * @param string|array $xKey
     *
     * @return mixed|null
     */
    public function getFieldValue($xKey) {

        if (is_array($xKey)) {
            $aResult = array();
            foreach($xKey as $sKey) {
                $aResult[$sKey] = $this->getFieldValue($sKey);
            }
            return $aResult;
        }
        $sKey = (string)$xKey;
        $iFlag = $this->isField($sKey);
        if ($iFlag == self::PROP_IS_PROP) {
            return $this->getProp($sKey);
        } elseif ($iFlag == self::PROP_IS_FIELD) {
            $sField = $this->getFieldName($sKey);
            if ($sField != $sKey && $this->isProp($sField)) {
                return $this->getProp($sField);
            }
        } elseif ($iFlag == self::PROP_IS_CALLABLE) {
            $xCallback = $this->getFieldName($sKey);
            return $xCallback($this);
        } elseif ($iFlag == self::PROP_IS_RELATION) {
            $sField = $this->getFieldName($sKey);

            /** @var Relation $oRelation */
            $oRelation = $this->aRelationsData[$sKey];
            $xValue = $oRelation->getResult();

            $this->setProp($sField, $xValue);
            return $xValue;
        }
        return null;
    }

    public function __clone() {

        $this->clearProps();
        if (!empty($this->aRelationsData)) {
            foreach($this->aRelationsData as $sKey => $oRelation) {
                $this->aRelationsData[$sKey] = clone $oRelation;
                $this->aRelationsData[$sKey]->setMasterEntity($this);
            }
        }
    }

    /**
     * Ставим хук на вызов неизвестного метода и считаем что хотели вызвать метод какого либо модуля
     * Также производит обработку методов set* и get*
     * Учитывает связи и может возвращать связанные данные
     *
     * @see Engine::_CallModule
     *
     * @param string $sName Имя метода
     * @param array  $aArgs Аргументы
     *
     * @return mixed
     */
    public function __call($sName, $aArgs) {

        $sType = substr($sName, 0, strpos(F::StrUnderscore($sName), '_'));
        if (!strpos($sName, '_') && in_array($sType, array('get', 'set', 'reload'))) {
            $sKey = F::StrUnderscore(preg_replace('/' . $sType . '/', '', $sName, 1));
            if ($sType == 'get') {
                return $this->getFieldValue($sKey);

                if ($this->isProp($sKey)) {
                    return $this->getProp($sKey);
                } else {
                    $sField = $this->getFieldName($sKey);
                    if ($sField != $sKey && $this->isProp($sField)) {
                        return $this->getProp($sField);
                    }
                }

                // * Check relations
                if (!empty($this->aRelationsData[$sKey])) {
                    $oRelation = $this->aRelationsData[$sKey];
                    $xRelValue = $oRelation->query();

                    /*
                    $sRelationType = $this->aRelationsData[$sKey]['type'];

                    $sEntityRel = $this->aRelationsData[$sKey]['rel_entity'];
                    $sRelKey = $this->aRelationsData[$sKey]['rel_entity_key'];

                    $sProperKey = $this->aRelationsData[$sKey]['proper_key'];

                    $oRelModule = E::Module($sEntityRel);
                    $xValue = $this->getProp($sProperKey);
                    $oBuilder = $oRelModule->find()->where([$sRelKey => $xValue]);

                    switch ($sRelationType) {
                        case Module::RELATION_HAS_ONE:
                            $xRelValue = $oBuilder->one();
                            break;
                        case Module::RELATION_HAS_MANY :
                            $xRelValue = $oBuilder->all();
                            break;
                        default:
                            $xRelValue = null;
                    }
                    */
                    $this->setProp($sField, $xRelValue);
                    return $xRelValue;

                    // Нужно ли учитывать дополнительный фильтр
                    $bUseFilter = is_array($mCmdArgs) && array_key_exists(0, $aArgs) && is_array($aArgs[0]);
                    if ($bUseFilter) {
                        $mCmdArgs = array_merge($mCmdArgs, $aArgs[0]);
                    }
                    $aCallArgs = array($mCmdArgs);
                    $res = E::GetInstance()->_CallModule($sCmd, $aCallArgs);

                    // Сохраняем данные только в случае "чистой" выборки
                    if (!$bUseFilter) {
                        $this->aRelationsData[$sKey] = $res;
                    }

                    return $res;
                }

                return null;
            } elseif ($sType == 'set' && array_key_exists(0, $aArgs)) {
                if (array_key_exists($sKey, $this->aRelationsData)) {
                    $this->aRelationsData[$sKey] = $aArgs[0];
                } else {
                    //$this->_aData[$this->getFieldName($sKey)] = $aArgs[0];
                    return $this->setProp($sKey, $aArgs[0]);
                }
            } elseif ($sType == 'reload') {
                if (array_key_exists($sKey, $this->aRelationsData)) {
                    unset($this->aRelationsData[$sKey]);
                    return $this->__call('get' . F::StrCamelize($sKey), $aArgs);
                }
            }
        } else {
            return parent::__call($sName, $aArgs);
        }
    }

    /**
     * Сбрасывает данные необходимой связи
     *
     * @param string $sKey    Ключ(поле) связи
     */
    public function resetRelationsData($sKey) {

        if (isset($this->aRelationsData[$sKey])) {
            unset($this->aRelationsData[$sKey]);
        }
    }
}

// EOF