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
 * Class EntityCollection
 *
 * @package alto\engine\ar
 */
class EntityCollection implements \ArrayAccess, \Iterator, \Countable {

    /** @var array  */
    private $aItems = array();

    /** @var array  */
    private $aKeys = null;

    /** @var int  */
    private $iPosition = 0;

    /**
     * EntityCollection constructor.
     *
     * @param null|array $aItems
     */
    public function __construct($aItems = null) {

        $this->iPosition = 0;
        if (func_num_args() == 0) {
            $aItems = array();
        } elseif (!is_array($aItems)) {
            $aItems = array($aItems);
        }
        $this->aItems = $aItems;
    }

    /**
     * @param mixed $xOffset
     * @param mixed $xValue
     */
    public function offsetSet($xOffset, $xValue) {

        if (is_null($xOffset)) {
            $this->aItems[] = $xValue;
        } else {
            $this->aItems[$xOffset] = $xValue;
        }
        if ($xValue instanceof EntityRecord) {
            $xValue->setCollection($this);
        }
        $this->aKeys = null;
    }

    /**
     * @param mixed $xOffset
     *
     * @return bool
     */
    public function offsetExists($xOffset) {

        return array_key_exists($xOffset, $this->aItems);
    }

    /**
     * @param mixed $xOffset
     */
    public function offsetUnset($xOffset) {

        unset($this->aItems[$xOffset]);
        $this->aKeys = null;
    }

    /**
     * @param mixed $xOffset
     *
     * @return null|mixed
     */
    public function offsetGet($xOffset) {

        return isset($this->aItems[$xOffset]) ? $this->aItems[$xOffset] : null;
    }

    /**
     *
     */
    public function rewind() {

        $this->iPosition = 0;
        $this->aKeys = array_keys($this->aItems);
    }

    /**
     * @return mixed
     */
    public function current() {

        return $this->aItems[$this->key()];
    }

    /**
     * @return mixed
     */
    public function key() {

        if (is_null($this->aKeys)) {
            $this->rewind();
        }
        return $this->aKeys[$this->iPosition];
    }

    /**
     * @return mixed
     */
    public function next() {

        ++$this->iPosition;
        return $this->current();
    }

    /**
     * @return bool
     */
    public function valid() {

        return isset($this->aItems[$this->key()]);
    }

    /**
     * @return int
     */
    public function count() {

        return count($this->aItems);
    }

    /**
     * @param $aItems
     */
    public function setItems($aItems) {

        foreach($aItems as $xItem) {
            if ($xItem instanceof EntityRecord) {
                $xItem->setCollection($this);
            }
        }
        $this->aItems = $aItems;
        $this->aKeys = null;
    }

    /**
     * @return array|null
     */
    public function getItems() {

        return $this->aItems;
    }

    /**
     * @param bool $bItemsAsArray
     *
     * @return array|null
     */
    public function asArray($bItemsAsArray = false) {

        if (!$bItemsAsArray) {
            return $this->getAItems();
        }
        $aResult = array();

        /** @var EntityRecord $oItem */
        foreach($this->aItems as $xKey => $oItem) {
            $aResult[$xKey] = $oItem->getAllProps();
        }
        return $aResult;
    }

    /**
     * @param $sColumn
     *
     * @return mixed
     */
    public function getColumn($sColumn) {

        $aResult = $this->asArray(true);
        return \F::Array_Column($aResult, $sColumn);
    }

    /**
     * @param $sKey
     * @param $xValue
     *
     * @return mixed|null
     */
    public function seekItemByKey($sKey, $xValue) {

        $aResult = array_filter($this->aItems, function ($oItem) use ($sKey, $xValue) {
            return $oItem->getFieldValue($sKey) == $xValue;
        });
        return !empty($aResult) ? reset($aResult) : null;
    }

    /**
     * @param $aParams
     *
     * @return mixed|null
     */
    public function seekItemByKeys($aParams) {

        $xValue = reset($aParams);
        $sKey = key($aParams);
        $aResult = array_filter($this->aItems, function ($oItem) use ($sKey, $xValue) {
            return $oItem->getFieldValue($sKey) == $xValue;
        });
        return !empty($aResult) ? reset($aResult) : null;
    }
}

// EOF