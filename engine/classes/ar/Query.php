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
 * Class Query
 *
 * @package alto\engine\ar
 */
class Query extends Condition {

    CONST COLUMN_TYPE_FIELD = 'field';
    CONST COLUMN_TYPE_EXPRESSION = 'expression';

    protected $oMapper;

    protected $aQueryData = [];

    protected $sCacheKey = false;

    protected $iCacheTime = 0;

    protected $aCacheTags = [];

    protected $oCacheModule = null;

    /**
     * Query constructor.
     *
     * @param null $oMapper
     */
    public function __construct($oMapper = null) {

        parent::__construct();
        $this->oMapper = $oMapper;
        $this->aQueryData = $this->_fill();

        if ($xAutoCache = C::Get('ar.cache_auto')) {
            if (is_bool($xAutoCache)) {
                $this->sCacheKey = $xAutoCache; // auto key
            } else {
                $this->iCacheTime = F::ToSeconds($xAutoCache);
                if ($this->iCacheTime) {
                    $this->sCacheKey = true; // auto key
                }
            }
        }
    }

    protected function _fill($aData = null) {

        $aResult = [
            'columns' => null,
            'table' => null,
            'join' => null,
            'order_by' => null,
            'group_by' => null,
            'limit' => null,
        ];

        if (is_array($aData) && $aData) {
            $aResult = array_merge($aResult, $aData);
        }

        return $aResult;
    }

    /**
     * @return string
     */
    public function serialize() {

        $sParentData = parent::serialize();
        $aSelfData = $this->aQueryData;
        foreach($aSelfData as $sKey => $xVal) {
            if (empty($xVal)) {
                $aSelfData[$sKey] = null;
            }
        }

        return serialize(['parent' => $sParentData, 'self' => $aSelfData]);
    }

    /**
     * @param string $sData
     *
     */
    public function unserialize($sData) {

        $aData = @unserialize($sData);
        if (!empty($aData['self'])) {
            $this->aQueryData = $this->_fill($aData['self']);
        } else {
            $this->aQueryData = $this->_fill();
        }
        if (!empty($aData['parent'])) {
            parent::unserialize($aData['parent']);
        }
    }

    /**
     * @param string $sName
     *
     * @return string
     */
    protected function _escapeName($sName) {

        if ($sName == '*') {

        } elseif (strpos($sName, '.')) {
            $aParts = explode('.', $sName);
            foreach($aParts as $iIdx => $sPart) {
                $aParts[$iIdx] = $this->_escapeName($sPart);
            }
            $sName = implode('.', $aParts);
        } else {
            //$sName = $this->oDb->escape($sName, true);
        }
        return $sName;
    }

    /**
     * @param array $aList
     *
     * @return string
     */
    protected function _escapeNameList($aList) {

        $aResult = [];
        foreach($aList as $aName) {
            if (is_array($aName) && !empty($aName['name'])) {
                $sName = $this->_escapeName($aName['name']);
                if (!empty($aName['alias'])) {
                    $sName .= ' AS ' . $this->_escapeName($aName['alias']);
                }
                $aResult[] = $sName;
            } elseif (is_scalar($aName)) {
                $sName = $this->_escapeName($aName);
                $aResult[] = $sName;
            } else {
                // Err:
            }
        }
        return implode(', ', $aResult);
    }

    /**
     * Retrieves alias and name from array
     *
     * @param array|string $xName
     *
     * @return array
     */
    protected function _aliasName($xName) {

        if (is_array($xName)) {
            list($sAlias, $sName) = F::Array_Pair($xName);
        } else {
            $sAlias = $sName = (string)$xName;
        }
        return [$sAlias, $sName];
    }

    /**
     * ->select('aaa.bbb', 'aaa.ccc'])
     * ->select(['b' => 'aaa.bbb'])
     * ->select(['aaa.b as b']))
     * ->select(['COUNT(aaa.b) as c']))
     * ->select(['c' => ['COUNT(aaa.b)]]))
     *
     * @return Query
     */
    public function select() {

        $this->aQueryData['columns'] = [];
        foreach(func_get_args() as $xArg) {
            $this->_addColumn($xArg);
        }

        return $this;
    }

    /**
     * @return Query
     */
    public function addSelect() {

        if (empty($this->aQueryData['columns'])) {
            $this->_addColumn('*');
        }
        foreach(func_get_args() as $xArg) {
            $this->_addColumn($xArg);
        }

        return $this;
    }

    /**
     * @param array|string $xArg
     */
    protected function _addColumn($xArg) {

        if (is_array($xArg)) {
            list($sAlias, $sExp) = $this->_aliasName($xArg);
            if (is_numeric($sAlias) && !(is_string($sAlias) && strpos($sAlias, '.'))) {
                $this->aQueryData['columns'][$sExp] = array(
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
                $this->aQueryData['columns'][$sAlias] = array(
                    'type' => $sType,
                    'data' => $sExp,
                    'alias' => $sAlias,
                );
            }
        } elseif (is_scalar($xArg)) {
            $this->aQueryData['columns'][$xArg] = array(
                'type' => self::COLUMN_TYPE_FIELD,
                'data' => $xArg,
            );
        }
    }

    /**
     * ->from('table_name')
     * ->from(['alias' => 'table_name'])
     * ->from(['alias' => Query'])
     *
     * @param string|array $xTable
     *
     * @return Query
     */
    public function from($xTable) {

        $sAlias = null;
        if (is_array($xTable)) {
            list($sAlias, $xTable) = F::Array_Pair($xTable);
        }
        $this->aQueryData['from'] = [
            'table' => $xTable,
            'alias' => $sAlias,
        ];
        if ($xTable instanceof Condition) {
            $this->_addSubCondition($xTable);
        }

        return $this;
    }

    /**
     * ->joinTable('LEFT JOIN', ['p' => 'post'], 'p.user_id = user_id');
     *
     * @param string       $sType
     * @param array|string $xTable
     * @param array|string $sCondition
     *
     * @return Query
     */
    public function joinTable($sType, $xTable, $sCondition) {

        list($sAlias, $xTable) = $this->_aliasName($xTable);
        $this->aQueryData['join'][] = array(
            'type' => strtoupper($sType),
            'name' => $xTable,
            'alias' => $sAlias,
            'on' => $sCondition,
        );
        if ($xTable instanceof Condition) {
            $this->_addSubCondition($xTable);
        }

        return $this;
    }

    /**
     * @param array|string $xTable
     * @param array|string $sCondition
     *
     * @return Query
     */
    public function innerJoin($xTable, $sCondition) {

        return $this->joinTable('INNER JOIN', $xTable, $sCondition);
    }

    /**
     * @param array|string $xTable
     * @param array|string $sCondition
     *
     * @return Query
     */
    public function leftJoin($xTable, $sCondition) {

        return $this->joinTable('LEFT JOIN', $xTable, $sCondition);
    }

    /**
     * @param array|string $xTable
     * @param array|string $sCondition
     *
     * @return Query
     */
    public function rightJoin($xTable, $sCondition) {

        return $this->joinTable('RIGHT JOIN', $xTable, $sCondition);
    }

    /**
     * @param string $sExp
     * @param array  $aParams
     *
     * @return Query
     */
    public function whereSql($sExp, $aParams = []) {

        $this->_addCondition(null, 'sql', $sExp);

        return $this->bind($aParams);
    }

    /**
     * @param string $sExp
     * @param array  $aParams
     *
     * @return Query
     */
    public function andWhereSql($sExp, $aParams = []) {

        $this->_addCondition('AND', 'sql', $sExp);

        return $this->bind($aParams);
    }

    /**
     * @param string $sExp
     * @param array  $aParams
     *
     * @return Query
     */
    public function orWhereSql($sExp, $aParams = []) {

        $this->_addCondition('OR', 'sql', $sExp);

        return $this->bind($aParams);
    }

    /**
     *   where(exp [, params])
     *   where(exp, operator, value [, params])
     *
     * @param string|array $xExp
     * @param string|null  $sOperator
     * @param mixed|null   $xValue
     * @param array        $aParams
     *
     * @return Query
     */
    public function where($xExp, $sOperator = null, $xValue = null, $aParams = []) {

        if (func_num_args() == 2) {
            $aParams = $sOperator;
        }
        $this->_condition(func_num_args(), null, $xExp, $sOperator, $xValue);

        return $this->bind($aParams);
    }

    /**
     *   where(exp [, params])
     *   where(exp, operator, value [, params])
     *
     * @param string|array $xExp
     * @param string|null  $sOperator
     * @param mixed|null   $xValue
     * @param array        $aParams
     *
     * @return Query
     */
    public function andWhere($xExp, $sOperator = null, $xValue = null, $aParams = []) {

        if (func_num_args() == 2) {
            $aParams = $sOperator;
        }
        $this->_condition(func_num_args(), 'AND', $xExp, $sOperator, $xValue);

        return $this->bind($aParams);
    }

    /**
     *   where(exp [, params])
     *   where(exp, operator, value [, params])
     *
     * @param string|array $xExp
     * @param string|null  $sOperator
     * @param mixed|null   $xValue
     * @param array        $aParams
     *
     * @return Query
     */
    public function orWhere($xExp, $sOperator = null, $xValue = null, $aParams = []) {

        if (func_num_args() == 2) {
            $aParams = $sOperator;
        }
        $this->_condition(func_num_args(), 'OR', $xExp, $sOperator, $xValue);

        return $this->bind($aParams);
    }

    /**
     * @return Query
     */
    public function andWhereBegin() {

        $this->andConditionBegin();

        return $this;
    }

    /**
     * @return Query
     */
    public function orWhereBegin() {

        $this->orConditionBegin();

        return $this;
    }

    /**
     * @return Query
     */
    public function whereEnd() {

        $this->conditionEnd();

        return $this;
    }

    /**
     * @param $xFields
     *
     * @return Query
     */
    public function groupBy($xFields) {

        if (is_string($xFields)) {
            $this->aQueryData['group_by'] = array($xFields);
        }
        if (is_array($xFields)) {
            $aData = [];
            foreach($xFields as $sField) {
                $aData[] = array('name' => $sField);
            }
            $this->aQueryData['group_by'] = $aData;
        }
        return $this;
    }

    /**
     * @param array|string $xFields
     *
     * @return Query
     */
    public function orderBy($xFields) {

        if (is_string($xFields)) {
            $this->aQueryData['order_by'][$xFields] = '';
        } elseif (is_array($xFields)) {
            foreach($xFields as $sField => $sOrder) {
                if (is_numeric($sField)) {
                    $sField = $sOrder;
                    $sOrder = '';
                }
                if (isset($this->aQueryData['order_by'][$sField])) {
                    unset($this->aQueryData['order_by'][$sField]);
                }
                $sOrder = strtoupper($sOrder);
                if ($sOrder != 'ASC' && $sOrder != 'DESC') {
                    $sOrder = '';
                }
                $this->aQueryData['order_by'][$sField] = $sOrder;
            }
        }
        return $this;
    }

    /**
     * @param int  $iOffset
     * @param null $iLimit
     *
     * @return Query
     */
    public function limit($iOffset, $iLimit = null) {

        if (is_null($iLimit)) {
            $iLimit = $iOffset;
            $iOffset = 0;
        }
        if (is_numeric($iLimit) && is_numeric($iOffset)) {
            $this->aQueryData['limit'] = [$iOffset, $iLimit];
        }

        return $this;
    }

    /**
     * @param array $aBindParams
     *
     * @return Query
     */
    public function bind($aBindParams) {

        if (!empty($aBindParams) && is_array($aBindParams)) {
            foreach($aBindParams as $sName => $xValue) {
                self::addParam($sName, $xValue);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function _getDefaultAlias($bSubQuery = false) {

        if (!empty($this->aQueryData['from']['alias'])) {
            return $this->aQueryData['from']['alias'];
        }

        return 't' . ($bSubQuery ? $this->iConditionsId : '');
    }

    /**
     * @return array
     */
    protected function _getColumns() {

        return $this->aQueryData['columns'];
    }

    /**
     * @param bool $bSubQuery
     *
     * @return string
     */
    public function getColumnsStr($bSubQuery = false) {

        $sMainAlias = $this->_getDefaultAlias($bSubQuery);
        if (empty($this->aQueryData['columns'])) {
            if ($sMainAlias) {
                return $sMainAlias . '.*';
            } else {
                return '*';
            }
        }

        $aResult = [];
        foreach($this->_getColumns() as $sColumn => $aColumn) {
            $sExpression = '';
            if ($aColumn['type'] == self::COLUMN_TYPE_FIELD) {
                if (!strpos($aColumn['data'], '.') && $sMainAlias) {
                    $sExpression = $this->_escapeName($sMainAlias . '.' . $aColumn['data']);
                } else {
                    $sExpression = $this->_escapeName($aColumn['data']);
                }
            } elseif ($aColumn['type'] == self::COLUMN_TYPE_EXPRESSION) {
                $sExpression = $aColumn['data'];
            }
            if (!empty($aColumn['alias'])) {
                $sExpression .= ' AS ' . $this->_escapeName($aColumn['alias']);
            }
            if ($sExpression) {
                $aResult[] = $sExpression;
            }
        }

        return implode(',', $aResult);
    }

    /**
     * @param bool $bSubQuery
     *
     * @return string
     */
    public function getTableStr($bSubQuery = false) {

        if (!empty($this->aQueryData['from']['table'])) {
            if (empty($this->aQueryData['from']['alias'])) {
                $sAlias = $this->_getDefaultAlias($bSubQuery);
            } else {
                $sAlias = $this->aQueryData['from']['alias'];
            }
            $xTable = $this->aQueryData['from']['table'];
            if (is_string($xTable)) {
                return $xTable . ' AS ' . $sAlias;
            } elseif ($xTable instanceof Query) {
                return '(' . $xTable->getQueryStr(true) . ') AS ' . $sAlias;
            }
        }

        return '';
    }

    /**
     * @param bool $bSubQuery
     *
     * @return mixed
     */
    public function getJoinTablesStr($bSubQuery = false) {

        $aResult = [];
        foreach($this->aQueryData['join'] as $aJoinTable) {
            $xTable = $aJoinTable['name'];
            if ($xTable instanceof Query) {
                $sTable = '(' . $xTable->getQueryStr(true) . ')';
            } else {
                $sTable = $this->_escapeName($xTable);
            }

            $sJoinTable = $aJoinTable['type'] . ' ' . $sTable;
            if (!empty($aJoinTable['alias'])) {
                $sJoinTable .= ' AS ' . $aJoinTable['alias'];
            }
            if (is_string($aJoinTable['on'])) {
                $sJoinTable .= ' ON ' . $aJoinTable['on'];
            } elseif (is_array($aJoinTable['on'])) {
                list($sField1, $sField2) = F::Array_Pair($aJoinTable['on']);
                if (!strpos($sField1, '.') && !strpos($sField2, '.')) {
                    $sAlias1 = ($aJoinTable['alias'] ? $aJoinTable['alias'] : $aJoinTable['name']);
                    $sField1 = $sAlias1 . '.' . $sField1;
                    $sAlias2 = $this->_getDefaultAlias($bSubQuery);
                    if ($sAlias2) {
                        $sField2 = $sAlias2 . '.' . $sField2;
                    }
                }
                $sJoinTable .= ' ON ' . $sField1 . '=' . $sField2;
            }
            $aResult[] = $sJoinTable;
        }
        return implode("\n", $aResult);
    }

    /**
     * @param array|null
     *
     * @return string
     */
    public function getWhereSql() {

        return $this->getConditionStr();
    }

    /**
     * @return string
     */
    public function getGroupByStr() {

        $sResult = '';
        if (!empty($this->aQueryData['group_by'])) {
            $sResult = implode(',', $this->aQueryData['group_by']);
        }
        return $sResult;
    }

    /**
     * @return string
     */
    public function getOrderByStr() {

        $sResult = '';
        if (!empty($this->aQueryData['order_by'])) {
            $sResult = implode(',', $this->aQueryData['order_by']);
        }
        return $sResult;
    }

    /**
     * @return string
     */
    public function getLimitStr() {

        $aLimit = $this->aQueryData['limit'];
        if ($aLimit) {
            return implode(',', $aLimit);
        }
        return '';
    }

    /**
     * @return string
     */
    public function getQueryStr($bSubQuery = false) {

        $sColumnsStr = $this->getColumnsStr($bSubQuery);
        $sTableListStr = $this->getTableStr($bSubQuery);
        $sJoinTablesStr = $this->getJoinTablesStr($bSubQuery);

        $sSql = "SELECT $sColumnsStr\n FROM $sTableListStr";
        if ($sJoinTablesStr) {
            $sSql .= "\n" . $sJoinTablesStr;
        }
        if ($sWhereStr = $this->getWhereSql()) {
            $sSql .= "\n WHERE " . $sWhereStr;
        }
        if ($sGroupStr = $this->getGroupByStr()) {
            $sSql .= "\n GROUP BY " . $sGroupStr;
        }
        if ($sOrderStr = $this->getOrderByStr()) {
            $sSql .= "\n ORDER BY " . $sOrderStr;
        }
        if ($sLimitStr = $this->getLimitStr()) {
            $sSql .= "\n LIMIT " . $sLimitStr;
        }

        return $sSql;
    }

    /**
     * @return array
     */
    public function getQueryParams() {

        return parent::getQueryParams();
    }

    /**
     * @return mixed
     */
    public function getHash() {

        return md5($this->serialize());
    }

    /**
     * @return array
     */
    public function query() {

        if (!$this->oMapper) {
            $this->oMapper = new ArMapper(E::ModuleDatabase()->GetConnect());
        }
        $aResult = $this->oMapper->getRowsByQuery($this);

        return $aResult;
    }

    /**
     * @return array
     */
    public function queryRow() {

        $aResult = $this->query();
        if (is_array($aResult) && !empty($aResult)) {
            return reset($aResult);
        }
        return [];
    }

    /**
     * @return mixed
     */
    public function queryScalar() {

        $aResult = $this->queryRow();
        if (is_array($aResult) && !empty($aResult)) {
            return reset($aResult);
        }
        return null;
    }
}

// EOF