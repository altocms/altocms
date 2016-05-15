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
class Condition extends \Entity implements \Serializable {

    protected $iConditionsId = 0;

    protected $aConditions = [];

    /** @var int Level of condition block */
    protected $iConditionLevel = 0;

    protected $aAutoParams = [];

    protected $aBindParams = [];

    protected $aSubConditions = [];

    static private $iConditionsCnt = 0;

    /**
     * Condition constructor.
     *
     */
    public function __construct() {

        parent::__construct();
        $this->iConditionsId = ++self::$iConditionsCnt;
    }

    /**
     * @return string
     */
    public function serialize() {

        return serialize(['cond' => $this->aConditions, 'auto' => $this->aAutoParams, 'bind' => $this->aBindParams]);
    }

    /**
     * @param array $sData
     *
     */
    public function unserialize($sData) {

        $aData = @unserialize($sData);

        if (is_array($aData['cond']) && is_array($aData['auto']) && is_array($aData['bind'])) {
            $this->aConditions = $aData['cond'];
            $this->aAutoParams = $aData['auto'];
            $this->aBindParams = $aData['bind'];
        }
    }

    protected function _addSubCondition($oSubCondition) {

        $this->aSubConditions[] = $oSubCondition;
    }

    /**
     * Create and add auto parameter
     *
     * @param mixed $xValue
     *
     * @return string
     */
    protected function _autoParam($xValue) {

        $sName = false;
        if (!empty($this->aAutoParams)) {
            $sName = array_search($xValue, $this->aAutoParams);
        }
        if ($sName === false) {
            $iIdx = count($this->aAutoParams) + 1;
            while (isset($this->aAutoParams[$sName = ':_' . $this->iConditionsId . '_' . $iIdx])) {
                $iIdx += 1;
            }
            $this->aAutoParams[$sName] = $xValue;
        }

        return $sName;
    }

    /**
     * Add parameter
     *
     * @param string $sName
     * @param mixed  $xValue
     */
    protected function _bindParam($sName, $xValue) {

        $this->aBindParams[$sName] = $xValue;
    }

    /**
     * @param string $sName
     * @param mixed  $xValue
     *
     * @return bool|string
     */
    /*
    static public function _x_addParam($sName, $xValue) {

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
    */

    /**
     * @return array
     */
    public function getQueryParams() {

        $aResult = [];
        if ($this->aSubConditions) {
            foreach ($this->aSubConditions as $oSubCondition) {
                $aResult = array_merge($aResult, $oSubCondition->getQueryParams());
            }
        }
        if (!empty($this->aAutoParams)) {
            foreach($this->aAutoParams as $sName => $xVal) {
                $aResult[$sName] = $xVal;
            }
        }
        if ($aResult && !empty($this->aBindParams)) {
            $aResult = array_merge($aResult, $this->aBindParams);
        } elseif (!empty($this->aBindParams)) {
            $aResult = $this->aBindParams;
        }

        return $aResult;
    }

    /**
     * @param $sStr
     *
     * @return Expression
     */
    static public function SQL($sStr) {

        return new Expression($sStr);
    }

    /**
     * @param string $sStr
     *
     * @return bool
     */
    protected function _isPlaceholder($sStr) {

        if (!empty($sStr) && is_string($sStr)) {
            $n = strlen($sStr);
            if ($n > 2 && $sStr[0] == '?' && $sStr[2] == ':') {
                return true;
            }
            if ($n > 1) {
                if ($sStr[0] == '?' && $sStr[1] == ':') {
                    return true;
                }
                if ($sStr[0] == '?' || $sStr[0] == ':') {
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
                // ->where(['foo', 123]) -- autoparams
                // ->where(['foo', '?d:foo'], [':foo' => 123]) -- set params
                foreach($xExp as $sKey => $sVal) {
                    if (is_array($sVal) || substr($sVal, 0, 3) === '?a:') {
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
            if ($xValue instanceof Expression) {
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
                    //$sValue = self::addParam(null, $xValue);
                    $sValue = $this->_autoParam($xValue);
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

        if (!$sLogic && empty($this->aConditions)) {
            $sLogic = 'AND';
        }
        $this->aConditions[] = array(
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
                $sParam = $aCondBlock['data']['value'];
                if ($aCondBlock['data']['operator'] == 'IN' || $aCondBlock['data']['operator'] == 'NOT IN') {
                    //$sExpression .= '(' . $this->_getParamStr($aCondBlock['data']['value']) . ')';
                    $sExpression .= '(' . $sParam . ')';
                } else {
                    //$sExpression .= $this->_getParamStr($aCondBlock['data']['value']);
                    $sExpression .= $sParam;
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
    protected function _condition($iNum, $sLogic, $xExp, $sOperator = null, $xValue = null) {

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
        if (!empty($this->aConditions)) {
            reset($this->aConditions);
            $sResult = $this->_getConditionStr($this->aConditions, $aFieldsMap);
        }

        if (empty($sResult)) {
            //$sResult = '(1=1)';
        }

        return $sResult;
    }

}

// EOF