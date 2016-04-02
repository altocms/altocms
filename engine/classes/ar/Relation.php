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
 * Class Relation
 *
 * @package alto\engine\ar
 *
 * @method setType(string $sParam)
 * @method setMasterField(string $sParam)
 * @method setMasterKey(string $sParam)
 * @method setEntityKey(string $sParam)
 * @method setLazy(bool $bParam)
 * @method setJuncTable(string $sTable);
 * @method setJuncRelKey(string $sKeyToRelation);
 * @method setJuncMasterKey(string $sKeyToMaster);
 *
 * @method string getType()
 * @method string getMasterField()
 * @method string getMasterKey()
 * @method string getEntityKey()
 * @method bool   getLazy()
 * @method string getJuncTable()
 * @method string getJuncRelKey()
 * @method string getJuncMasterKey()
 */
class Relation extends Builder {

    protected $aLimit;

    protected $sStatFunc;

    protected $sStatField;

    /**
     * Relation constructor.
     *
     * @param $sRelType
     * @param $xEntity
     * @param $sField
     * @param $sRelEntity
     * @param $aRelFields
     */
    public function __construct($sRelType, $xEntity, $sField, $sRelEntity, $aRelFields) {

        parent::__construct(null);
        $this->setRelation($sRelType, $xEntity, $sField, $sRelEntity, $aRelFields);
    }

    /**
     * @param $xEntity
     * @param $sRelType
     * @param $sRelEntity
     * @param $aRelFields
     *
     * @return Relation
     */
    static public function make($sRelType, $xEntity, $sField, $sRelEntity, $aRelFields) {

        return new self($sRelType, $xEntity, $sField, $sRelEntity, $aRelFields);
    }

    /**
     * @param int $iOffset
     * @param int $iLimit
     *
     * @return Relation
     */
    public function limit($iOffset, $iLimit = null) {

        if (func_num_args() == 1) {
            $iLimit = $iOffset;
            $iOffset = 0;
        }
        $this->aLimit = array($iOffset, $iLimit);

        return $this;
    }

    /**
     * @param string $sName
     * @param string $sField
     *
     * @return Relation
     */
    protected function _setStatFunc($sName, $sField) {

        $this->sStatFunc = strtoupper($sName);
        $this->sStatField = $sField;

        return $this;
    }

    /**
     * @param string $sField
     *
     * @return Relation
     */
    public function count($sField = '*') {

        return $this->_setStatFunc(__FUNCTION__, $sField);
    }

    /**
     * @param string $sField
     *
     * @return Relation
     */
    public function sum($sField) {

        return $this->_setStatFunc(__FUNCTION__, $sField);
    }

    /**
     * @param string $sField
     *
     * @return Relation
     */
    public function avg($sField) {

        return $this->_setStatFunc(__FUNCTION__, $sField);
    }

    /**
     * @param string $sField
     *
     * @return Relation
     */
    public function average($sField) {

        return $this->avg($sField);
    }

    /**
     * @param string $sField
     *
     * @return Relation
     */
    public function min($sField) {

        return $this->_setStatFunc(__FUNCTION__, $sField);
    }

    /**
     * @param string $sField
     *
     * @return Relation
     */
    public function max($sField) {

        return $this->_setStatFunc(__FUNCTION__, $sField);
    }

    /**
     * @param string $sTable
     * @param string $sKeyToRelation
     * @param string $sKeyToMaster
     *
     * @return Relation
     */
    public function viaTable($sTable, $sKeyToRelation, $sKeyToMaster) {

        $this->setJuncTable($sTable);
        $this->setJuncRelKey($sKeyToRelation);
        $this->setJuncMasterKey($sKeyToMaster);

        return $this;
    }

    /**
     * @param      $oEntity
     * @param null $sField
     * @param null $sKey
     *
     * @return Relation
     */
    public function setMasterEntity($oEntity, $sField = null, $sKey = null) {

        $this->setProp('__master_entity', $oEntity);
        if (func_num_args() > 1) {
            $this->setMasterField($sField);
        }
        if (func_num_args() > 2) {
            $this->setMasterKey($sKey);
        }
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getMasterEntity() {

        return $this->getProp('__master_entity');
    }

    /**
     * @param $xEntity
     * @param $sRelType
     * @param $sRelEntity
     * @param $aRelFields
     *
     * @return Relation
     */
    public function setRelation($sRelType, $xEntity, $sField, $sRelEntity, $aRelFields) {

        $aJuncTable = '';
        if (is_array($aRelFields)) {
            if (count($aRelFields) == 1) {
                list($sRelKey, $sMasterKey) = F::Array_Pair($aRelFields);
            } else {
                list($sRelKey, $aJuncTable, $sMasterKey) = $aRelFields;
            }
        } else {
            $sMasterKey = $sRelKey = (string)$aRelFields;
        }

        $this->setType($sRelType);

        $this->setMasterEntity($xEntity, $sField, $sMasterKey);

        $this->setEntity($sRelEntity);
        $this->setEntityKey($sRelKey);
        $this->setLazy(true);
        if (!empty($aJuncTable)) {
            $this->setJuncTable($aJuncTable[0]);
            $this->setJuncRelKey($aJuncTable[1]);
            $this->setJuncMasterKey($aJuncTable[2]);
        }

        return $this;
    }

    /**
     * @param EntityRecord $oMasterEntity
     *
     * @return EntityCollection|EntityRecord
     */
    public function getResult($oMasterEntity = null) {

        if ($this->hasProp('__result')) {
            return $this->getProp('__result');
        }

        $xResult = $this->getProp('__result');
        if (!$oMasterEntity) {
            $oMasterEntity = $this->getMasterEntity();
        }
        if ($oMasterEntity) {
            $oCollection = $oMasterEntity->getCollection();
            if ($oCollection) {
                $xResult = $this->_queryCollection($oMasterEntity, $oCollection);
            } else {
                $xResult = $this->_queryEntity($oMasterEntity);
            }
            $this->setProp('__result', $xResult);
        }

        return $xResult;
    }

    /**
     * @param EntityRecord $oMasterEntity
     *
     * @return EntityCollection|EntityRecord|null
     */
    protected function _queryEntity($oMasterEntity) {

        $sMasterKey = $this->getMasterKey();
        $sRelEntityKey = $this->getEntityKey();
        switch ($this->getType()) {
            case ArModule::RELATION_HAS_ONE:
                $this->where([$sRelEntityKey => $oMasterEntity->getAttr($sMasterKey)]);
                $xRelValue = $this->one();
                break;
            case ArModule::RELATION_HAS_MANY :
                $sJuncTable = $this->getJuncTable();
                $sJuncRelKey = $this->getJuncRelKey();
                $sJuncMasterKey = $this->getJuncMasterKey();
                if ($sJuncTable) {
                    if (!$sJuncRelKey) {
                        $sJuncRelKey = $sRelEntityKey;
                    }

                    if (!$sJuncMasterKey) {
                        $sJuncMasterKey = $sMasterKey;
                    }
                    $this->leftJoin($sJuncTable, array($sJuncRelKey => $sRelEntityKey));
                    $this->where([$sJuncMasterKey => $oMasterEntity->getAttr($sMasterKey)]);
                } else {
                    $this->where([$sRelEntityKey => $oMasterEntity->getAttr($sMasterKey)]);
                }
                if (!empty($this->aLimit)) {
                    $this->limit($this->aLimit[0], $this->aLimit[1]);
                }
                $xRelValue = $this->all();
                break;
            case ArModule::RELATION_HAS_STAT :
                $xRelValue = null;
                if ($this->sStatFunc) {
                    $sColumn = strtoupper($this->sStatFunc);
                    if ($this->sStatField) {
                        $sColumn = $sColumn . '(' . $this->sStatField . ')';
                    } else {
                        $sColumn = $sColumn . '()';
                    }
                    $this->select([$sColumn]);
                    $xRelValue = $this->queryScalar();
                }
                break;
            default:
                $xRelValue = null;
        }
        return $xRelValue;
    }

    /**
     * @param EntityRecord     $oMasterEntity
     * @param EntityCollection $oCollection
     *
     * @return EntityCollection|null
     */
    protected function _queryCollection($oMasterEntity, $oCollection) {

        $sMasterKey = $this->getMasterKey();
        $sMasterField = $this->getMasterField();
        //$aKeyValues = array_filter(array_unique($oCollection->getColumn($sMasterKey)));
        $aKeyValues = array_unique($oCollection->getColumn($sMasterKey));
        $sRelKey = $this->getEntityKey();

        switch ($this->getType()) {
            case ArModule::RELATION_HAS_ONE:
                $this->where([$sRelKey => $aKeyValues]);
                $this->indexBy($sRelKey);
                $aResults = $this->all()->asArray();

                if (count($aResults)) {
                    /** @var EntityRecord $oEntity */
                    foreach($oCollection->asArray() as $oEntity) {
                        $oEntity->setProp($sMasterField, null);
                    }
                    foreach($aResults as $sKey => $oItem) {
                        /** @var EntityRecord $oCollectionEntity */
                        $oCollectionEntity = $oCollection->seekItemByKey($sMasterKey, $sKey);
                        if ($oCollectionEntity && !$oCollectionEntity->getProp($sMasterField)) {
                            $oCollectionEntity->setRelBind($sMasterField, $oItem);
                        }
                    }
                }
                $xRelValue = $oMasterEntity->getRelBind($sMasterField);
                break;
            case ArModule::RELATION_HAS_MANY :
                $sJuncTable = $this->getJuncTable();
                $sJuncRelKey = $this->getJuncRelKey();
                $sJuncMasterKey = $this->getJuncMasterKey();

                if ($sJuncTable) {
                    if (!$sJuncRelKey) {
                        $sJuncRelKey = $sRelKey;
                    }

                    if (!$sJuncMasterKey) {
                        $sJuncMasterKey = $sMasterKey;
                    }
                    $this->leftJoin($sJuncTable, array($sJuncRelKey => $sRelKey));
                    $this->where([$sJuncMasterKey => $aKeyValues]);
                    $this->addSelect($sJuncTable . '.' . $sJuncMasterKey);
                    $sSubsetKey = $sJuncMasterKey;
                } else {
                    $this->where([$sRelKey => $aKeyValues]);
                    $sSubsetKey = $sRelKey;
                }

                $aResults = $this->all()->asArray();

                if (count($aResults)) {
                    $aCollections = [];
                    /** @var EntityRecord $oItem */
                    foreach($aResults as $oItem) {
                        $aCollections[$oItem->getProp($sSubsetKey)][] = $oItem;
                    }
                    /** @var EntityRecord $oEntity */
                    foreach($oCollection->asArray() as $oEntity) {
                        $oEntity->setProp($sMasterField, new EntityCollection());
                    }
                    foreach($aCollections as $sKey => $aSubset) {
                        /** @var EntityRecord $oCollectionEntity */
                        $oCollectionEntity = $oCollection->seekItemByKey($sMasterKey, $sKey);
                        if ($oCollectionEntity) {
                            if (!empty($this->aLimit)) {
                                $aSubset = array_slice($aSubset, $this->aLimit[0], $this->aLimit[1], true);
                                $this->limit($this->aLimit[0], $this->aLimit[1]);
                            }
                            $oCollectionEntity->setRelBind($sMasterField, new EntityCollection($aSubset));
                        }
                    }
                }
                $xRelValue = $oMasterEntity->getRelBind($sMasterField);
                break;
            case ArModule::RELATION_HAS_STAT :
                $xRelValue = null;
                if ($this->sStatFunc) {
                    $sColumn = strtoupper($this->sStatFunc);
                    if ($this->sStatField) {
                        $sColumn = $sColumn . '(' . $this->sStatField . ')';
                    } else {
                        $sColumn = $sColumn . '()';
                    }
                    $this->select($sRelKey, [$sColumn]);
                    $this->where([$sRelKey => $aKeyValues]);
                    $this->indexBy($sRelKey);
                    $this->groupBy($sRelKey);
                    $aResults = $this->query();
                    if (count($aResults)) {
                        /** @var EntityRecord $oEntity */
                        foreach($oCollection->asArray() as $oEntity) {
                            $oEntity->setProp($sMasterField, 0);
                        }
                        foreach($aResults as $aRow) {
                            $sKey = $aRow[$sRelKey];
                            $iVal = $aRow[$sColumn];
                            /** @var EntityRecord $oCollectionEntity */
                            $oCollectionEntity = $oCollection->seekItemByKey($sMasterKey, $sKey);
                            if ($oCollectionEntity && !$oCollectionEntity->getProp($sMasterField)) {
                                $oCollectionEntity->setRelBind($sMasterField, $iVal);
                            }
                        }
                    }
                    $xRelValue = $oMasterEntity->getRelBind($sMasterField);
                }
                break;
            default:
                $xRelValue = null;
        }
        return $xRelValue;
    }
    
}

// EOF