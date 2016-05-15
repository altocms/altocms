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
use Alto\GithubApi\Entity;
use \E as E, \F as F, \C as C;

/**
 * Абстрактный класс сущности ORM - аналог Active Record
 *
 * @package engine.ar
 * @since   1.2
 */
class EntityRecord extends \Entity {

    const ATTR_IS_PROP     = 1;
    const ATTR_IS_FIELD    = 2;
    const ATTR_IS_CALLABLE = 3;
    const ATTR_IS_RELATION = 4;

    const DEFAULT_ALIAS = 't';

    /** @var array Attributes definition */
    protected $aAttributes = [];

    protected $aTables = [['alias' => self::DEFAULT_ALIAS]];

    protected $xPrimaryKey;

    protected $iRecordStatus = 0;
    
    protected $aExtra;

    /**
     * Список полей таблицы сущности
     *
     * @var array
     */
    protected $aTableColumns = null;

    static protected $oInstance;

    /**
     * Установка связей
     * @see \Entity::__construct
     *
     * @param bool $aParams Ассоциативный массив данных сущности
     */
    public function __construct($aParams = null) {

        parent::__construct($aParams);
    }

    /**
     * @return array
     */
    public function __sleep() {

        foreach($this->_aData as $sKey => $xVal) {
            if (0 === strpos($sKey, '__')) {
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

    /**
     * @return string
     */
    static public function tableName() {

        return self::instance()->getTableName();
    }

    /* *** Extra data *** */

    /**
     * @param array $aExtra
     *
     * @return string
     */
    protected function extraSerialize($aExtra) {

        $aExtra = (array)$aExtra;
        return 'j:' . json_encode($aExtra);
    }

    /**
     * @param string $sExtra
     *
     * @return array
     */
    protected function extraUnserialize($sExtra) {

        $aExtra = [];
        if ($sExtra) {
            if (0 === strpos($sExtra, 'j:')) {
                $aExtra = @json_decode($sExtra, true);
            } else {
                $aExtra = @unserialize($sExtra);
            }
            $aExtra = (array)$aExtra;
        }
        return $aExtra;
    }

    /**
     * @param string $sKey
     * @param mixed  $xVal
     *
     * @return EntityRecord
     */
    public function setPropExtra($sKey, $xVal) {

        if (is_null($this->aExtra)) {
            $this->aExtra = $this->extraUnserialize($this->getAttr('extra'));
        }
        $this->aExtra[$sKey] = $xVal;

        return $this;
    }

    /**
     * @param string $sKey
     *
     * @return null|mixed
     */
    public function getPropExtra($sKey) {

        if (is_null($this->aExtra)) {
            $this->aExtra = $this->extraUnserialize($this->getAttr('extra'));
        }
        if (isset($this->aExtra[$sKey])) {
            return $this->aExtra[$sKey];
        }
        return null;
    }

    /**
     * @return mixed|null
     */
    public function getExtra() {

        if (is_null($this->aExtra)) {
            return $this->getProp('extra');
        }
        return $this->extraSerialize($this->aExtra);
    }

    /**
     * @param $sExtra
     *
     * @return EntityRecord
     */
    public function setExtra($sExtra) {

        $this->setProp('extra', $sExtra);
        return $this;
    }

    /* *** --- *** */

    /**
     * @return bool
     */
    public function isNew() {
        
        return $this->iRecordStatus === ArModule::RECORD_STATUS_NEW;
    }

    /**
     * @param int $iStatus
     *
     * @return EntityRecord
     */
    public function setRecordStatus($iStatus) {
        
        $this->iRecordStatus = (int)$iStatus;
        if ($this->iRecordStatus === ArModule::RECORD_STATUS_SAVED) {
            $this->resetUpdated();
        }
        return $this;
    }

    /**
     * @return EntityRecord
     */
    public function clearTmpProps() {

        foreach($this->_aData as $sKey => $xVal) {
            if (0 === strpos($sKey, '__')) {
                unset($this->_aData[$sKey]);
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getModuleClass() {

        $sModuleClass = $this->getProp('__module_class');
        if (!$sModuleClass) {
            $aInfo = E::GetClassInfo($this, E::CI_MODULE | E::CI_PPREFIX);

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
     * Sets table name and alias (default alias is 't')
     * <pre>
     * setTableName($sTableName);
     * setTableName([$sTableAlias => $sTableName]);
     * </pre>
     *
     * @param string|array $xTable
     *
     * @return EntityRecord
     */
    public function setTableName($xTable) {

        if (is_array($xTable)) {
            list($sTableName, $sTableAlias) = F::Array_Pair($xTable);
        } else {
            $sTableName = (string)$xTable;
            $sTableAlias = static::DEFAULT_ALIAS;
        }
        $this->aTables[0] = ['name' => $sTableName, 'alias' => $sTableAlias];

        return $this;
    }

    /**
     * @param string|array $xTable
     * @param array        $aCondition
     *
     * @return EntityRecord
     */
    public function joinTable($xTable, $aCondition) {

        if (is_array($xTable)) {
            list($sTableName, $sTableAlias) = F::Array_Pair($xTable);
        } else {
            $sTableName = (string)$xTable;
            $sTableAlias = static::DEFAULT_ALIAS . count($this->aTables);
        }
        $this->aTables[] = [
            'type' => 'LEFT JOIN',
            'name' => $sTableName,
            'alias' => $sTableAlias,
            'on' => $aCondition,
        ];

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName() {

        if (!isset($this->aTables[0]['name'])) {
            //$sClass = E::ModulePlugin()->GetDelegater('entity', get_called_class());
            $sClass = get_called_class();
            $sModuleName = F::StrUnderscore(E::GetModuleName($sClass));
            $sEntityName = F::StrUnderscore(E::GetEntityName($sClass));
            if (strpos($sEntityName, $sModuleName) === 0) {
                $sTable = F::StrUnderscore($sEntityName);
            } else {
                $sTable = F::StrUnderscore($sModuleName) . '_' . F::StrUnderscore($sEntityName);
            }

            $this->aTables[0]['name'] = '?_' . $sTable;
        }

        return $this->aTables[0]['name'];
    }

    /**
     * @return string
     */
    public function getTableAlias() {

        if (isset($this->aTables[0]['alias'])) {
            return $this->aTables[0]['alias'];
        }
        return static::DEFAULT_ALIAS;
    }

    /**
     * Получение primary key из схемы таблицы
     *
     * @return string|array    Если индекс составной, то возвращает массив полей
     */
    public function getPrimaryKey() {

        if (!$this->sPrimaryKey) {
            /** @var array $aIndex */
            $aIndex = $this->getModule()->getMapper()->readPrimaryIndexFromTable($this->getTableName());
            if (is_array($aIndex)) {
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
     * @param bool $bAsArray
     *
     * @return mixed
     */
    public function getPrimaryKeyValue($bAsArray = false) {

        $xResult = null;
        $aPrimaryKey = $this->getPrimaryKey();
        if (is_array($aPrimaryKey)) {
            foreach($aPrimaryKey as $sKey) {
                $xResult[$sKey] = $this->getProp($sKey);
            }
            if (!$bAsArray) {
                $xResult = array_values($xResult);
            }
        } else {
            if ($bAsArray) {
                $xResult = [$aPrimaryKey => $this->getProp($this->getPrimaryKey())];
            } else {
                $xResult = $this->getProp($this->getPrimaryKey());
            }
        }
        return $xResult;
    }

    /**
     * @return array
     */
    public function getQueryFields() {
        
        $sAlias = $this->getTableAlias();
        $aFields = [$sAlias . '.*'];
        
        return $aFields;
    }
    
    /**
     * @param EntityCollection $oCollection
     *
     * @return EntityRecord
     */
    public function setCollection($oCollection) {

        $this->setProp('__collection', $oCollection);
        return $this;
    }

    /**
     * @return EntityCollection|null
     */
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
     * Сохранение сущности в БД (если новая, то создается)
     *
     * @return EntityRecord|false
     */
    public function save() {

        if ($this->beforeSave()) {
            $oModule = $this->getModule();
            if ($xResult = $oModule->save($this)) {
                $this->afterSave();
                return $xResult;
            }
        }
        return false;
    }

    /**
     * Удаление сущности из БД
     *
     * @return EntityRecord|false
     */
    public function delete() {

        if ($this->beforeDelete()) {
            $oModule = $this->getModule();
            if ($xResult = $oModule->delete($this)) {
                $this->afterDelete();
                return $xResult;
            }
        }
        return false;
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
     * Возвращает список полей сущности
     *
     * @return array
     */
    public function readColumns() {

        $oMapper = $this->getMapper();
        if ($oMapper) {
            $aColumns = $oMapper->readColumnsFromTable($this->getTableName());
        } else {
            $aColumns = [];
        }
        
        return $aColumns;
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
        $aClassInfo = E::GetClassInfo($sPluginPrefix . 'Module_' . $sModuleName, E::CI_MODULE);
        if (empty($aClassInfo[E::CI_MODULE])) {
            $sRootDelegator = E::ModulePlugin()->getFirstOf('entity', $sClass);
            if ($sRootDelegator) {
                $sModuleName = E::GetModuleName($sRootDelegator);
                $sPluginName = E::GetPluginName($sRootDelegator);
            }
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
     * Define attributes
     *
     * @param string $sKey
     * @param int    $iType
     * @param mixed  $xData
     *
     * @return EntityRecord
     */
    protected function _defineAttr($sKey, $iType, $xData) {

        $this->aAttributes[$sKey] = [
            'type' => $iType,
            'data' => $xData,
        ];
        if ($iType === self::ATTR_IS_PROP) {
            $this->setProp($sKey, $xData);
        }
        return $this;
    }

    /**
     * Define attribute as simple data
     *
     * @param string     $sName
     * @param null|mixed $xDefault
     *
     * @return EntityRecord
     */
    public function defineProp($sName, $xDefault = null) {

        return $this->_defineAttr($sName, self::ATTR_IS_PROP, $xDefault);
    }

    /**
     * Define attribute as field alias
     *
     * @param string $sName
     * @param string $sFieldName
     *
     * @return EntityRecord
     */
    public function defineAttrField($sName, $sFieldName) {

        return $this->_defineAttr($sName, self::ATTR_IS_FIELD, $sFieldName);
    }

    /**
     * Define attribute as field alias
     *
     * @param string $sName
     * @param string $sFieldName
     *
     * @return EntityRecord
     */
    public function defineField($sName, $sFieldName) {

        return $this->defineAttrField($sName, $sFieldName);
    }

    /**
     * Define attribute as callback function
     *
     * @param string   $sName
     * @param callable $xCallback
     *
     * @return EntityRecord
     */
    public function defineAttrFunc($sName, callable $xCallback) {

        return $this->_defineAttr($sName, self::ATTR_IS_CALLABLE, $xCallback);
    }

    /**
     * Define attribute as relation
     *
     * @param string   $sName
     * @param Relation $oRelation
     *
     * @return EntityRecord
     */
    public function defineAttrRelation($sName, $oRelation) {

        return $this->_defineAttr($sName, self::ATTR_IS_RELATION, $oRelation);
    }


    /**
     * @param string|array $xTable
     * @param array        $aFields
     *
     * @return EntityRecord
     */
    public function addFieldsFrom($xTable, $aFields) {

        if (is_string($xTable)) {
            $sTable = $xTable;
            if (substr($sTable, 0, 2) === '?_') {
                $sAlias = substr($sTable, 2);
            } else {
                $sAlias = $sTable;
            }
        } else {
            list($sTable, $sAlias) = F::Array_Pair($xTable);
        }
        $oRelEntity = new self();
        $oRelEntity->setTableName($sTable);
        
        $oRelation = $this->defineRelation(ArModule::RELATION_HAS_ONE, $sAlias, $oRelEntity, $aFields);
        $oRelation->setLazy(false);

        return $this;
    }

    /**
     * Calculate and return value of attribute
     *
     * @param string $sAttrName
     * @param array  $aAttrData
     *
     * @return mixed|null
     */
    protected function _getAttrValue($sAttrName, $aAttrData) {

        $iType = (isset($aAttrData['type']) ? $aAttrData['type'] : 0);
        $xData = (isset($aAttrData['data']) ? $aAttrData['data'] : null);
        if ($iType && $xData) {
            switch ($iType) {
                case self::ATTR_IS_PROP:
                    return $this->getProp($sAttrName);
                case self::ATTR_IS_FIELD:
                    return $this->getProp($xData);
                case self::ATTR_IS_CALLABLE:
                    return call_user_func($xData, $sAttrName, $this);
                case self::ATTR_IS_RELATION:
                    if ($this->hasRelBind($sAttrName)) {
                        return $this->getRelBind($sAttrName);
                    }
                    /** @var $xData Relation */
                    return $xData->getResult($this);
            }
        }
        return null;
    }

    /**
     * Return array of attribute data by type
     *
     * @param int $iType
     *
     * @return array
     */
    protected function _getAttrDataByType($iType) {

        $aResult = [];
        if ($this->aAttributes) {
            foreach($this->aAttributes as $sAttrName => $aAttrData) {
                if (isset($aAttrData['type']) && $aAttrData['type'] === $iType) {
                    $aResult[$sAttrName] = $aAttrData;
                }
            }
        }
        return $aResult;
    }

    /**
     * Return value of the attribute
     *
     * @param $sName
     *
     * @return mixed|null
     */
    public function getAttr($sName) {

        if (strpos($sName, '.')) {
            list($sAttrName, $sLastName) = explode('.', $sName, 2);
            $xData = $this->getAttr($sAttrName);
            if (is_object($xData) && $xData instanceof EntityRecord) {
                return $xData->getAttr($sLastName);
            }
        } elseif (isset($this->aAttributes[$sName])) {
            return $this->_getAttrValue($sName, $this->aAttributes[$sName]);
        }
        return parent::getProp($sName);
    }

    /**
     * Return array of property values
     * 
     * @param array|null $aKeys
     * @param bool       $bSkipTmp
     *
     * @return array
     */
    protected function _getAttrValues($aKeys = null, $bSkipTmp = false) {

        if (is_null($aKeys) && $bSkipTmp) {
            $aResult = $this->getProps();
        } else {
            $aResult = parent::getAllProps($aKeys);
        }
        if ($this->aAttributes) {
            if (!is_array($aKeys)) {
                $aKeys = (array)$aKeys;
            }
            foreach($this->aAttributes as $sAttrName => $aAttrData) {
                if (empty($aKeys) || in_array($sAttrName, $aKeys)) {
                    $aResult[$sAttrName] = $this->_getAttrValue($sAttrName, $aAttrData);
                }
            }
        }
        return $aResult;
    }

    public function getProps($aKeys = null) {

        $aResult = parent::getAllProps($aKeys);
        if (is_null($aKeys)) {
            foreach($aResult as $sKey => $xVal) {
                if (substr($sKey, 0, 2) == '__') {
                    unset($aResult[$sKey]);
                }
            }
        }

        return $aResult;
    }

    /**
     * Return values of all attributes
     *
     * @param null $aKeys
     *
     * @return array
     */
    public function getAttributes($aKeys = null) {

        return $this->_getAttrValues($aKeys, true);
    }

    /**
     * @param string $sName
     * @param mixed  $xValue
     *
     * @return EntityRecord|\Entity
     */
    public function setAttr($sName, $xValue) {

        $iType = (isset($this->aAttributes[$sName]['type']) ? $this->aAttributes[$sName]['type'] : 0);
        $xData = (isset($this->aAttributes[$sName]['data']) ? $this->aAttributes[$sName]['data'] : null);
        if ($iType) {
            switch ($iType) {
                case self::ATTR_IS_PROP:
                    return $this->setProp($sName, $xValue);
                case self::ATTR_IS_FIELD:
                    if ($xData && is_string($xData)) {
                        return $this->setProp($xData, $xValue);
                    }
                    break;
                default:
                    break;
            }
        } else {
            parent::setProp($sName, $xValue);
        }

        return $this;
    }

    /**
     * Возвращает список полей сущности
     *
     * @return array
     */
    public function getTableColumns() {

        $aColumns = $this->getProp('__columns');
        if (is_null($aColumns)) {
            if (is_null($this->aTableColumns)) {
                $this->aTableColumns = $this->readColumns();
            }
            if (!empty($this->aFields)) {
                $aColumns = array_merge($this->aTableColumns, $this->aFields);
            } else {
                $aColumns = $this->aTableColumns;
            }
            $this->setProp('__columns', $aColumns);
        }

        return $aColumns;
    }

    /**
     * @param bool $bWithValues
     *
     * @return array
     */
    public function getFields($bWithValues = false) {
        
        $aFields = $this->getTableColumns();
        /*
        $aFieldAliases = $this->_getPropDataByType(self::ATTR_IS_FIELD);
        foreach ($aFieldAliases as $sName => $aAttr) {
            if (!empty($aAttr['data']) && is_string($aAttr['data']) && isset($aFields[$aAttr['data']])) {
                $aFields[$sName] = $aFields[$aAttr['data']];
            }
        }
        */
        $aNames = array_keys($aFields);
        if (!$bWithValues) {
            return array_keys($aNames);
        }
        $aResult = [];
        foreach($aNames as $sName) {
            $aResult[$sName] = $this->getAttr($sName);
        }

        return $aResult;
    }
    
    /**
     * @param string $sField Название поля
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
     * Add relation
     * 
     * @param string $sRelType
     * @param string $sField
     * @param string $sRelEntity
     * @param array $aRelFields
     *
     * @return Relation
     */
    public function defineRelation($sRelType, $sField, $sRelEntity, $aRelFields) {

        $oRelation = new Relation($sRelType, $this, $sField, $sRelEntity, $aRelFields);
        $this->aAttributes[$sField] = [
            'type' => self::ATTR_IS_RELATION,
            'data' => $oRelation,
        ];

        return $oRelation;
    }

    /**
     * Add relation one-to-one
     * 
     * @param array $aRelation
     * @param array $aRelFields
     *
     * @return Relation
     */
    public function relOne($aRelation, $aRelFields = null) {

        $sRelEntity = reset($aRelation);
        $sField = key($aRelation);

        return $this->defineRelation(ArModule::RELATION_HAS_ONE, $sField, $sRelEntity, $aRelFields);
    }

    /**
     * Add relation one-to-many
     * 
     * @param array $aRelation
     * @param array $aRelFields
     *
     * @return Relation
     */
    public function relMany($aRelation, $aRelFields = null) {

        $sRelEntity = reset($aRelation);
        $sField = key($aRelation);

        return $this->defineRelation(ArModule::RELATION_HAS_MANY, $sField, $sRelEntity, $aRelFields);
    }

    /**
     * Add relation many-to-many via junction table
     * 
     * @param array $aRelation
     * @param string $sJuncTable
     * @param null $xJuncToRelation
     * @param null $xJuncToMaster
     *
     * @return Relation
     */
    public function relManyVia($aRelation, $sJuncTable, $xJuncToRelation = null, $xJuncToMaster = null) {

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
            ->defineRelation(ArModule::RELATION_HAS_MANY, $sField, $sRelEntity, array($sRelKey => $sMasterKey))
            ->viaTable($sJuncTable, $sJuncRelKey, $sJuncMasterKey);
    }

    /**
     * Add relation with aggregate function
     *
     * @param string $sField
     * @param string $sRelEntity
     * @param array  $aRelFields
     *
     * @return Relation
     */
    public function relStat($sField, $sRelEntity, $aRelFields = null) {

        if (is_array($sField) && (is_array($sRelEntity))) {
            $aRelFields = $sRelEntity;
            $sRelEntity = reset($sField);
            $sField = key($sField);
        }
        return $this->defineRelation(ArModule::RELATION_HAS_STAT, $sField, $sRelEntity, $aRelFields);
    }

    /**
     * Return all relations
     *
     * @return array
     */
    public function getRelations() {

        $aResult = [];
        $aData = $this->_getAttrDataByType(self::ATTR_IS_RELATION);
        if ($aData) {
            foreach($aData as $sAttrName => $aAttrData) {
                if (!empty($aAttrData['data']) && $aAttrData['data'] instanceof Relation) {
                    $aResult[$sAttrName] = $aAttrData['data'];
                }
            }
        }
        return $aResult;
    }

    public function getRelation($sName) {
        
        $aRelations = $this->getRelations();
        if (isset($aRelations[$sName])) {
            return $aRelations[$sName];
        }
        return null;
    }

    /**
     * Bind result data to relation
     * 
     * @param string $sName
     * @param mixed  $xData
     */
    public function setRelBind($sName, $xData) {
        
        if (isset($this->aAttributes[$sName]['type']) && $this->aAttributes[$sName]['type'] == self::ATTR_IS_RELATION) {
            $this->aAttributes[$sName]['bind'] = $xData;
        }
    }

    /**
     * Return binding data if exists
     * 
     * @param string $sName
     *
     * @return null
     */
    public function getRelBind($sName) {

        if ($this->hasRelBind($sName)) {
            return $this->aAttributes[$sName]['bind'];
        }
        return null;
    }

    /**
     * Check binding data
     * 
     * @param string $sName
     *
     * @return bool
     */
    public function hasRelBind($sName) {

        if (isset($this->aAttributes[$sName]['type']) && $this->aAttributes[$sName]['type'] == self::ATTR_IS_RELATION) {
            return array_key_exists('bind', $this->aAttributes[$sName]);
        }
        return false;
    }
    
    /**
     * @param string $sName
     *
     * @return int
     */
    public function hasAttribute($sName) {

        if ($this->hasProp($sName)) {
            return self::ATTR_IS_PROP;
        }

        if (isset($this->aAttributes[$sName]['type'])) {
            return $this->aAttributes[$sName]['type'];
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
            $aResult = [];
            foreach($xKey as $sKey) {
                $aResult[$sKey] = $this->getAttr($sKey);
            }
            return $aResult;
        }
        $sKey = (string)$xKey;
        $iFlag = $this->hasAttribute($sKey);
        if ($iFlag == self::ATTR_IS_PROP) {
            return $this->getProp($sKey);
        } elseif ($iFlag == self::ATTR_IS_FIELD) {
            $sField = $this->getFieldName($sKey);
            if ($sField != $sKey && $this->hasProp($sField)) {
                return $this->getProp($sField);
            }
        } elseif ($iFlag == self::ATTR_IS_CALLABLE) {
            $xCallback = $this->getFieldName($sKey);
            return $xCallback($this);
        } elseif ($iFlag == self::ATTR_IS_RELATION) {
            $sField = $this->getFieldName($sKey);

            /** @var Relation $oRelation */
            $oRelation = $this->aRelationsData[$sKey];
            $xValue = $oRelation->getResult();

            $this->setProp($sField, $xValue);
            return $xValue;
        }
        return null;
    }

    /**
     * Returns an array of fields to insert a new entity
     *
     * @return array
     */
    public function getInsertFields() {
        
        return $this->getFields(true);
    }

    /**
     * Returns an array of fields to update entity
     *
     * @return array
     */
    public function getUpdateFields() {

        return $this->getFields(true);
    }

    /**
     * LS-compatible
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
                return $this->getProp($sKey);
            } elseif ($sType == 'set' && array_key_exists(0, $aArgs)) {
                return $this->setProp($sKey, $aArgs[0]);
            }
        }
        return parent::__call($sName, $aArgs);
    }

}

// EOF