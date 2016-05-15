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
 * Class Builder
 *
 * @package alto\engine\ar
 */
class Builder extends Query {

    protected $aWithRelations = [];

    protected $aFields = [];

    /**
     * CriteriaORM2 constructor.
     *
     * @param null $xEntity
     */
    public function __construct($xEntity = null) {

        parent::__construct();
        if ($xEntity) {
            $this->setEntity($xEntity);
        }
        $this->oWhere = new Condition($this);
    }

    /**
     * Clone object
     */
    public function __clone() {

        $this->clearProps();
    }

    /**
     * @param $xEntity
     *
     * @return $this
     */
    public function setEntity($xEntity) {

        if (is_object($xEntity)) {
            $oEntity = $xEntity;
            $sEntityClass = get_class($oEntity);
        } else {
            $oEntity = null;
            $sEntityClass = $xEntity;
        }
        $this->setProp('__entity', $oEntity);
        $this->setProp('entity_class', $sEntityClass);

        return $this;
    }

    /**
     * @return EntityRecord
     */
    public function getEntity() {

        $oEntity = $this->getProp('__entity');
        if (!$oEntity && ($sEntityClass = $this->getProp('entity_class'))) {
            $oEntity = E::GetEntity($sEntityClass);
        }
        return $oEntity;
    }

    /**
     * @return ArMapper|null
     */
    public function getEntityMapper() {

        $oEntity = $this->getEntity();
        if ($oEntity) {
            return $oEntity->getMapper();
        }
        return null;
    }

    /**
     * @return ArModule|null
     */
    public function getEntityModule() {

        $oEntity = $this->getEntity();
        if ($oEntity) {
            return $oEntity->getModule();
        }
        return null;
    }

    /**
     * @param $oVar
     */
    protected function _resolveRelations($oVar) {

        if (!empty($this->aWithRelations)) {
            if ($oVar instanceof EntityCollection) {
                $oItem = $oVar->current();
            } else {
                $oItem = $oVar;
            }
            foreach($this->aWithRelations as $sRelation) {
                $oItem->getAttr($sRelation);
            }
        }
    }

    /**
     * @return array
     */
    protected function _resolveFields() {

        $oEntity = $this->getEntity();
        $aEntityFields = $oEntity->getFields();
        foreach($aEntityFields as $sName => $aFieldInfo) {
            if (!empty($aEntityFields[$sName][self::COLUMN_TYPE_FIELD])) {
                $this->aFields[$sName] = $aEntityFields[$sName][self::COLUMN_TYPE_FIELD];
            }
        }

        if ($this->aColumns) {
            if ($aRelations = $oEntity->getRelations()) {
                foreach($aRelations as $oRelation) {
                    $sMasterKey = $oRelation->getMasterKey();
                    if (isset($this->aFields[$sMasterKey])) {
                        $this->_addColumn(array($sMasterKey => $this->aFields[$sMasterKey]));
                    } else {
                        $this->_addColumn($sMasterKey);
                    }
                }
            }
        }

        return $this->aFields;
    }

    /**
     * ->select('aaa.bbb', 'aaa.ccc'])
     * ->select(['b' => 'aaa.bbb'])
     * ->select(['aaa.b as b']))
     * ->select(['COUNT(aaa.b) as c']))
     * ->select(['c' => ['COUNT(aaa.b)]]))
     *
     * @return Builder
     */
    /*
    public function select() {

        $this->aColumns = [];
        foreach(func_get_args() as $xArg) {
            $this->_addColumn($xArg);
        }

        return $this;
    }
    */

    /**
     * @return Builder
     */
    /*
    public function addSelect() {

        foreach(func_get_args() as $xArg) {
            $this->_addColumn($xArg);
        }

        return $this;
    }

    protected function _addColumn($xArg) {

        if (is_array($xArg)) {
            $sExp = reset($xArg);
            $sAlias = key($xArg);
            if (is_numeric($sAlias) && !(is_string($sAlias) && strpos($sAlias, '.'))) {
                $this->aColumns[$sExp] = array(
                    'type' => self::COLUMN_TYPE_EXPRESSION,
                    'data' => $sExp,
                    'alias' => null,
                );
            } else {
                if (is_array($sExp)) {
                    $sType = self::COLUMN_TYPE_EXPRESSION;
                    $sExp = reset($sExp);
                } else {
                    $sType = self::COLUMN_TYPE_FIELD;
                }
                $this->aColumns[$sAlias] = array(
                    'type' => $sType,
                    'data' => $sExp,
                    'alias' => $sAlias,
                );
            }
        } elseif (is_scalar($xArg)) {
            $this->aColumns[$xArg] = array(
                'type' => self::COLUMN_TYPE_FIELD,
                'data' => $xArg,
            );
        }
    }
    */

    public function indexBy($sKey) {

        $this->setProp('index_by', $sKey);
    }


    public function indexByPk() {

        $oEntity = $this->getEntity();
        if ($oEntity) {
            $sPrimaryKey = $oEntity->getPrimaryKey();
            if ($sPrimaryKey) {
                $this->setProp('index_by', $sPrimaryKey);
            }
        }
    }

    /**
     * Set cache key
     *
     * @param mixed|null $xCacheKey
     * ->cacheKey('cache_key')
     * ->cacheKey(['cache_key' => ['tag1', 'tag2', ...]])
     *
     * @return Builder
     */
    public function cacheKey($xCacheKey = null) {

        if (is_null($xCacheKey)) {
            $xCacheKey = (bool)$this->iCacheTime;
        }
        if (is_bool($xCacheKey) || is_string($xCacheKey)) {
            $this->sCacheKey = $xCacheKey;
        } elseif (is_array($xCacheKey)) {
            list($sCacheKey, $aCacheTags) = F::Array_Pair($xCacheKey);
            if (is_int($sCacheKey)) {
                $this->sCacheKey = true; // auto key
            } else {
                $this->sCacheKey = $sCacheKey;
            }
            $this->cacheTags($aCacheTags);
        }

        return $this;
    }

    /**
     * @param null $xCacheTime
     * ->cacheTime(3600)
     * ->cacheTime('P1D')
     * ->cacheTime(false);
     *
     * @return Builder
     */
    public function cacheTime($xCacheTime = null) {

        if (!is_null($xCacheTime) && $this->sCacheKey) {
            if (is_numeric($xCacheTime)) {
                $this->iCacheTime = intval($xCacheTime);
            } else {
                $this->iCacheTime = F::ToSeconds($xCacheTime);
            }
        }

        return $this;
    }

    /**
     * @param array $aCacheTags
     * ->cacheTags('tag')
     * ->cacheTags(['tag1', 'tag2'])
     * ->cacheTags([])
     *
     * @return Builder
     */
    public function cacheTags($aCacheTags) {

        if (is_string($aCacheTags)) {
            $this->aCacheTags = [$aCacheTags];
        } elseif (is_array($aCacheTags)) {
            $this->aCacheTags = $aCacheTags;
        } elseif (empty($aCacheTags)) {
            $this->aCacheTags = [];
        }

        return $this;
    }

    /**
     * @param \Module|string $xCacheModule
     * ->cacheModule('ModuleCache')
     * ->cacheModule($oModuleCache)
     *
     * @return Builder
     */
    public function cacheModule($xCacheModule) {

        if (is_object($xCacheModule)) {
            $this->oCacheModule = $xCacheModule;
        } else {
            $this->oCacheModule = E::Module($xCacheModule);
        }

        return $this;
    }

    /**
     * @param array $aParams
     *
     * @return Builder
     */
    public function cacheParams($aParams) {

        foreach($aParams as $sKey => $xVal) {
            if ($sKey === 'key') {
                $this->cacheKey($xVal);
            } elseif ($sKey === 'tags') {
                $this->cacheTags($xVal);
            } elseif ($sKey === 'time') {
                $this->cacheTime($xVal);
            } elseif ($sKey === 'module') {
                $this->cacheModule($xVal);
            }
        }

        return $this;
    }

    /**
     * @param string|integer $xTime
     * @param string|array   $xKey
     * @param string|\Module $xModule
     *
     * @return Builder
     */
    public function cache($xTime, $xKey = null, $xModule = null) {

        $this->cacheTime($xTime);
        if (!is_null($xKey)) {
            $this->cacheKey($xKey);
        }
        if (!is_null($xModule)) {
            $this->cacheModule($xModule);
        }

        return $this;
    }

    /**
     * ->width(rel)
     * ->width(rel1, rel2, ...)
     * ->width([rel1, rel1, ...])
     *
     * @return $this
     */
    public function width() {

        switch (func_num_args()) {
            case 0:
                $aRelationNames = [];
                break;
            case 1:
                $aArg = func_get_arg(0);
                if (is_array($aArg)) {
                    $aRelationNames = $aArg;
                } else {
                    $aRelationNames = array($aArg);
                }
                break;
            default:
                $aRelationNames = func_get_args();
                break;
        }
        if (!empty($aRelationNames)) {
            $this->aWithRelations = $aRelationNames;
            /*
            $oEntity = $this->getEntity();
            $aRelationsData = $oEntity->getRelations();
            if (!empty($aRelationsData)) {
                foreach($aRelationNames as $sRelation) {
                    if (isset($aRelationsData[$sRelation])) {
                        $this->aWithRelations[$sRelation] = $aRelationsData[$sRelation];
                    } else {
                        // Err: link to undefined relation
                    }
                }
            } else {
                // Err: link to undefined relation
            }
            */
        }
        return $this;
    }

    protected function _getDefaultAlias() {

        $sMainAlias = '';
        $aTables = $this->getTableNames();
        if ($aTables) {
            $aTable = reset($aTables);
            if (!empty($aTable['alias'])) {
                $sMainAlias = $aTable['alias'];
            }
        }
        return $sMainAlias;
    }

    /**
     * @return array
     */
    public function getColumnNames() {

        $sMainAlias = $this->_getDefaultAlias();
        if (empty($this->aColumns)) {
            if ($sMainAlias) {
                return array($sMainAlias . '.*');
            } else {
                return array('*');
            }
        }

        $aResult = [];
        foreach($this->aColumns as $sColumn => $aColumn) {
            if ($aColumn['type'] == self::COLUMN_TYPE_FIELD) {
                if (!strpos($aColumn['data'], '.')) {
                    $aResult[$sColumn] = array(
                        'name' => $sMainAlias . '.' . $aColumn['data'],
                    );
                } else {
                    $aResult[$sColumn] = array(
                        'name' => $aColumn['data'],
                    );
                }
            }
            $aResult[$sColumn]['alias'] = (!empty($aColumn['alias']) ? $aColumn['alias'] : null);
        }

        return $aResult;
    }

    /**
     * @return array
     */
    public function getTableNames() {

        $aTableNames = [];
        $oEntity = $this->getEntity();
        if ($oEntity) {
            $aTableNames[] = array(
                'name' => $oEntity->getTableName(),
                'alias' => 't',
            );
        }
        if (!empty($this->aTables)) {
            foreach($this->aTables as $aTable) {
                //$aTable['join_type'] . $aTable['join_table'] . $aTable['join_condition'];
            }
        }
        return $aTableNames;
    }

    /**
     * @return array
     */
    public function getJoinTableNames() {

        $aTableNames = [];
        if (!empty($this->aTables)) {
            foreach($this->aTables as $aTable) {
                //$aTable['join'] . $aTable['join_table'] . $aTable['join_alias'] . $aTable['join_condition'];
            }
        }
        return $aTableNames;
    }

    /**
     * @param array|null
     *
     * @return string
     */
    public function getWhereSql() {

        $sResult = $this->oWhere->getConditionStr($this->aFields);
        if (empty($sResult)) {
            $sResult = '(1=1)';
        }

        return $sResult;
    }

    /**
     * @return string
     */
    public function getOrderByStr() {

        $sResult = '';
        $aFields = $this->aFields;
        foreach($this->aOrderBy as $sField => $sOrder) {
            if ($sResult) {
                $sResult .= ', ';
            }
            if (!empty($aFields[$sField])) {
                $sResult .= $aFields[$sField];
            } else {
                $sResult .= $sField;
            }
            if ($sOrder) {
                $sResult .= ' ' . $sOrder;
            }
        }
        return $sResult;
    }

    /**
     * @return string
     */
    public function getQueryStr() {

        $this->_resolveFields();

        $sSql = parent::getQueryStr();

        return $sSql;
    }

    /**
     * @param mixed|null $xPrimaryKeyValue
     *
     * @return EntityRecord|null
     */
    public function one($xPrimaryKeyValue = null) {

        $oEntity = $this->getEntity();
        if (!is_null($xPrimaryKeyValue) && $oEntity) {
            $sPrimaryKey = $oEntity->getPrimaryKey();
            if ($sPrimaryKey) {
                if (is_array($sPrimaryKey) && is_array($xPrimaryKeyValue)) {
                    if (count($sPrimaryKey) == count($xPrimaryKeyValue)) {
                        $this->where(array_combine($sPrimaryKey, $xPrimaryKeyValue));
                    } else {
                        // Err: sizes is differ
                    }
                } elseif (is_scalar($sPrimaryKey) && is_scalar($xPrimaryKeyValue)) {
                    $this->where($sPrimaryKey, '=', $xPrimaryKeyValue);
                } else {
                    // Err: types is differ
                }
            } else {
                // Err: primary key not found
            }
        }

        $aRelations = $oEntity->getRelations();
        if ($aRelations) {
            foreach($aRelations as $sRelName => $oRelation) {
                if (!$oRelation->getProp('lazy')) {
                    $this->width($sRelName);
                }
            }
        }

        $this->limit(0, 1);

        $aResult = $this->_execQuery();

        if (!empty($aResult)) {
            /** @var EntityRecord $oEntity */
            $oEntity = reset($aResult);
            $this->_resolveRelations($oEntity);
            $oEntity->setRecordStatus(ArModule::RECORD_STATUS_SAVED);
            return $oEntity;
        }
        return null;
    }

    /**
     * @param array $aPrimaryKeyValues
     *
     * @return EntityCollection
     */
    public function all($aPrimaryKeyValues = []) {

        if (!empty($aPrimaryKeyValues) && ($oEntity = $this->getEntity())) {
            $sPrimaryKey = $oEntity->getPrimaryKey();
            if ($sPrimaryKey) {
                if (is_string($sPrimaryKey)) {
                    $this->andWhere($sPrimaryKey, 'in', $aPrimaryKeyValues);
                } else {
                    $this->andWhereBegin();
                    foreach($aPrimaryKeyValues as $xValue) {
                        $this->orWhere($sPrimaryKey, '=', $xValue);
                    }
                    $this->whereEnd();
                }
            } else {
                // Err: primary key not found
            }
        }

        $aResult = $this->_execQuery();

        $oCollection = new EntityCollection();
        if ($aResult) {
            $sIndexKey = $this->getProp('index_by');
            if (!$sIndexKey) {
                $oCollection->setItems($aResult, function($oItem){
                    /** @var EntityRecord $oItem */
                    $oItem->setRecordStatus(ArModule::RECORD_STATUS_SAVED);
                });
            } else {
                /** @var EntityRecord $oItem */
                foreach($aResult as $oItem) {
                    $xIndex = $oItem->getAttr($sIndexKey);
                    $oItem->setRecordStatus(ArModule::RECORD_STATUS_SAVED);
                    $oCollection[$xIndex] = $oItem;
                }
            }
            $this->_resolveRelations($oCollection);
        }

        return $oCollection;
    }

    /**
     * @return array
     */
    protected function _execQuery() {

        $oMapper = $this->getEntityMapper();
        if ($oMapper) {
            $aResult = $oMapper->getItemsByCriteria($this);
        } else {
            $aResult = false;
        }

        return $aResult;
    }

}

// EOF