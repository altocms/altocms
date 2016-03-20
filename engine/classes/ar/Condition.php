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
 * Class Condition
 *
 * @package alto\engine\ar
 */
class Condition extends \Entity {

    protected $aCondition = [];

    /** @var int Level of condition block */
    protected $iConditionLevel = 0;

    /** @var array Params in expressions */
    static private $aConditionParams = [];

    /**
     * Condition constructor.
     *
     * @param null $oParent
     */
    public function __construct($oParent = null) {

        parent::__construct();
    }

    static public function addParam($sName, $xValue) {

        if (is_null($sName)) {
            $sName = false;
            if (!empty(self::$aConditionParams)) {
                $sName = array_search($xValue, self::$aConditionParams);
            }
            if ($sName === false) {
                $iIdx = count(self::$aConditionParams) + 1;
                while (isset(self::$aConditionParams[$sName = ':' . $iIdx])) {
                    $iIdx += 1;
                }
            }
        }
        self::$aConditionParams[$sName] = $xValue;
        return $sName;
    }

    static public function getParams() {

        return self::$aConditionParams;
    }

    static public function SQL($sStr) {

        return new ExpressionORM2($sStr);
    }

    public function clearProps() {

        foreach($this->_aData as $sKey => $xVal) {
            if (substr($sKey, 0, 2) == '__') {
                unset($this->_aData[$sKey]);
            }
        }
    }

    /**
     * @param string $sStr
     *
     * @return bool
     */
    protected function _isPlaceholder($sStr) {

        if (!empty($sStr) && is_string($sStr)) {
            switch (strlen($s = substr($sStr, 0, 3))) {
                case 3:
                    if ($s[0] == '?' && $s[2] == ':') {
                        return true;
                    }
                case 2:
                    if ($s[0] == '?' && $s[1] == ':') {
                        return true;
                    }
                    if ($s[0] == '?' || $s[0] == ':') {
                        return true;
                    }
            }
        }
        return false;
    }

    /**
     * @param bool         $bHasOperator
     * @param string|array $xExp
     * @param string|null  $sOperator
     * @param string|null  $xValue
     *
     * @return array
     */
    protected function _prepare($bHasOperator, $xExp, $sOperator = null, $xValue = null) {

        $aResult = [];
        if (!$bHasOperator) {
            if (is_array($xExp)) {
                // ->where(['foo', 123])
                // ->where(['foo', '?d:foo'], [':foo' => 123])
                foreach($xExp as $sKey => $sVal) {
                    if (is_array($sVal)) {
                        $aResult = array_merge($aResult, $this->_prepare(true, $sKey, 'in', $sVal));
                    } else {
                        $aResult = array_merge($aResult, $this->_prepare(true, $sKey, '=', $sVal));
                    }
                }
            } else {
                // ->where('foo > 123')
                // ->where('foo > ?d:foo', [':foo' => 123])
                $aResult[] = array(
                    'type' => 'sql',
                    'expression' => $xExp,
                );
            }
        } else {
            if ($xValue instanceof ExpressionORM2) {
                $aResult[] = array(
                    'type' => 'sql',
                    'expression' => $xExp . ' ' . $sOperator . ' ' . $xValue['text'],
                );
            } elseif (is_null($xValue)) {
                if ($sOperator == '!=' || $sOperator == '<>') {
                    $aResult[] = array(
                        'type' => 'sql',
                        'expression' => $xExp . ' IS NOT NULL',
                    );
                } elseif ($sOperator == '=' || $sOperator == '==') {
                    $aResult[] = array(
                        'type' => 'sql',
                        'expression' => $xExp . ' IS NULL',
                    );
                }
            } else {
                // ->where('foo', 'in', [123, 456])
                // ->where('foo', '>', 123)
                $sOperator = strtoupper($sOperator);
                if (!$this->_isPlaceholder($xValue)) {
                    $sValue = self::addParam(null, $xValue);
                    if (is_array($xValue)) {
                        $sValue = '?a' . $sValue;
                    } else {
                        $sValue = '?' . $sValue;
                    }
                } else {
                    if ($xValue[0] == ':') {
                        $xValue = '?' . $xValue;
                    }
                    $sValue = $xValue;
                }
                $aData = array(
                    'field' => $xExp,
                    'operator' => $sOperator,
                    'value' => $sValue,
                );
                $aResult[] = array(
                    'type' => 'exp',
                    'data' => $aData,
                );
            }
        }
        return $aResult;
    }

    /**
     * @param string|null $sLogic
     * @param string      $sType
     * @param mixed       $xData
     *
     * @return Condition
     */
    protected function _addCondition($sLogic, $sType, $xData) {

        if (!$sLogic && empty($this->aCondition)) {
            $sLogic = 'AND';
        }
        $this->aCondition[] = array(
            'level' => $this->iConditionLevel,
            'logic' => $sLogic,
            'type' => $sType,
            'data' => $xData,
        );

        return $this;
    }

    /**
     * @return Condition
     */
    public function andConditionBegin() {

        $this->iConditionLevel += 1;
        $this->_addCondition('AND', 'sub', null);
        return $this;
    }

    /**
     * @return Condition
     */
    public function orConditionBegin() {

        $this->iConditionLevel += 1;
        $this->_addCondition('AND', 'sub', null);
        return $this;
    }

    /**
     * @return Condition
     */
    public function conditionEnd() {

        $this->_addCondition('AND', 'sub', null);
        $this->iConditionLevel -= 1;
        return $this;
    }

    /**
     * @param array|null $aCondition
     * @param array|null $aFieldsMap
     *
     * @return string
     */
    protected function _getConditionStr(&$aCondition, $aFieldsMap) {

        $sResult = '';
        $aCondBlock = current($aCondition);
        $iLevel = $aCondBlock['level'];
        while (list($iIdx, $aCondBlock) = each($aCondition)) {
            if ($aCondBlock['type'] == 'sub') {
                if ($aCondBlock['level'] > $iLevel) {
                    $sExpression = $this->_getConditionStr($aCondition, $aFieldsMap);
                } else {
                    break;
                }
            } elseif ($aCondBlock['type'] == 'sql') {
                $sExpression = $aCondBlock['data'];
            } elseif ($aCondBlock['type'] == 'exp') {
                $sFieldName = $aCondBlock['data']['field'];
                if (!empty($aFieldsMap[$sFieldName])) {
                    $sFieldName = $aFieldsMap[$sFieldName];
                }
                $sExpression = $sFieldName . ' ' . $aCondBlock['data']['operator'] . ' ';
                if ($aCondBlock['data']['operator'] == 'IN' || $aCondBlock['data']['operator'] == 'NOT IN') {
                    $sExpression .= '(' . $aCondBlock['data']['value'] . ')';
                } else {
                    $sExpression .= $aCondBlock['data']['value'];
                }
            } else {
                $sExpression = '';
            }
            if ($sExpression) {
                if ($sResult) {
                    if ($aCondBlock['logic']) {
                        $sLogicOperator = ' ' . $aCondBlock['logic'] . ' ';
                    } else {
                        $sLogicOperator = ' AND ';
                    }
                } else {
                    $sLogicOperator = '';
                }
                $sResult .= $sLogicOperator . '(' . $sExpression . ')';
            }
        }

        return $sResult;
    }

    /**
     *   where(exp)
     *   where(exp, operator, value)
     *
     * @param int          $iNum
     * @param string       $sLogic
     * @param string|array $xExp
     * @param string|null  $sOperator
     * @param mixed|null   $xValue
     *
     * @return Condition
     */
    public function _condition($iNum, $sLogic, $xExp, $sOperator = null, $xValue = null) {

        if ($iNum < 3) {
            $aConditionSet = $this->_prepare(false, $xExp, $sOperator, $xValue);
        } else {
            $aConditionSet = $this->_prepare(true, $xExp, $sOperator, $xValue);
        }
        foreach($aConditionSet as $aCondition) {
            if (isset($aCondition['expression'])) {
                $this->_addCondition($sLogic, $aCondition['type'], $aCondition['expression']);
            } elseif (isset($aCondition['data'])) {
                $this->_addCondition($sLogic, $aCondition['type'], $aCondition['data']);
            }
        }

        return $this;
    }


    /**
     *   condition(exp [, params])
     *   condition(exp, operator, value [, params])
     *
     * @param string|array $xExp
     * @param string|null  $sOperator
     * @param mixed|null   $xValue
     * @param array        $aParams
     *
     * @return Condition
     */
    public function condition($xExp, $sOperator = null, $xValue = null, $aParams = []) {

        $this->_condition(func_num_args(), null, $xExp, $sOperator, $xValue);
        if (!empty($aParams)) {
            foreach($aParams as $sKey => $xValue) {
                self::addParam($sKey, $xValue);
            }
        }
        return $this;
    }

    /**
     * @param array|null $aFieldsMap
     *
     * @return string
     */
    public function getConditionStr($aFieldsMap = null) {

        $sResult = '';
        if (!empty($this->aCondition)) {
            reset($this->aCondition);
            $sResult = $this->_getConditionStr($this->aCondition, $aFieldsMap);
        }

        if (empty($sResult)) {
            $sResult = '(1=1)';
        }

        return $sResult;
    }

}

// EOF