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

    protected $aColumns = array();

    protected $aTables = array();

    protected $aJoinTables = array();

    /** @var Condition */
    protected $oWhere = null;

    protected $aOrderBy = array();

    protected $aGroupBy = array();

    /**
     * @param $xData
     *
     * @return array
     */
    protected function _arrayPair($xData) {

        if (is_array($xData)) {
            $xVal = reset($xData);
            $xKey = key($xData);
            return array($xKey, $xVal);
        }
        return array(null, $xData);
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

        $aResult = array();
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
     * ->select('aaa.bbb', 'aaa.ccc'])
     * ->select(['b' => 'aaa.bbb'])
     * ->select(['aaa.b as b']))
     * ->select(['COUNT(aaa.b) as c']))
     * ->select(['c' => ['COUNT(aaa.b)]]))
     *
     * @return Builder
     */
    public function select() {

        $this->aColumns = array();
        foreach(func_get_args() as $xArg) {
            $this->_addColumn($xArg);
        }

        return $this;
    }

    /**
     * @return Builder
     */
    public function addSelect() {

        if (empty($this->aColumns)) {
            $this->_addColumn('*');
        }
        foreach(func_get_args() as $xArg) {
            $this->_addColumn($xArg);
        }

        return $this;
    }

    /**
     * @param $xArg
     */
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


    public function from() {

        foreach(func_get_args() as $xTable) {
            list($sAlias, $sTable) = $this->_arrayPair($xTable);
            $this->aTables[] = array(
                'name' => $sTable,
                'alias' => $sAlias,
            );
        }
        return $this;
    }

    /**
     * ->joinTable('LEFT JOIN', ['post' => 'p'], 'p.user_id = user.id');
     *
     * @param $sJoin
     * @param $sTable
     * @param $sCondition
     *
     * @return Builder
     */
    public function joinTable($sJoin, $sTable, $sCondition) {

        list($sAlias, $sTable) = $this->_arrayPair($sTable);
        $this->aJoinTables[] = array(
            'join' => $sJoin,
            'name' => $sTable,
            'alias' => $sAlias,
            'on' => $sCondition,
        );
        return $this;
    }

    public function innerJoin($sTable, $sCondition) {

        return $this->joinTable('INNER JOIN', $sTable, $sCondition);
    }

    public function leftJoin($sTable, $sCondition) {

        return $this->joinTable('LEFT JOIN', $sTable, $sCondition);
    }

    public function rightJoin($sTable, $sCondition) {

        return $this->joinTable('RIGHT JOIN', $sTable, $sCondition);
    }

    /**
     * @param string $sExp
     * @param array  $aParams
     *
     * @return Builder
     */
    public function whereSql($sExp, $aParams = array()) {

        $this->oWhere->_addCondition(null, 'sql', $sExp);
        return $this->bind($aParams);
    }

    /**
     * @param string $sExp
     * @param array  $aParams
     *
     * @return Builder
     */
    public function andWhereSql($sExp, $aParams = array()) {

        $this->oWhere->_addCondition('AND', 'sql', $sExp);
        return $this->bind($aParams);
    }

    /**
     * @param string $sExp
     * @param array  $aParams
     *
     * @return Builder
     */
    public function orWhereSql($sExp, $aParams = array()) {

        $this->oWhere->_addCondition('OR', 'sql', $sExp);
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
     * @return Builder
     */
    public function where($xExp, $sOperator = null, $xValue = null, $aParams = array()) {

        if (func_num_args() == 2) {
            $aParams = $sOperator;
        }
        $this->oWhere->_condition(func_num_args(), null, $xExp, $sOperator, $xValue);
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
     * @return Builder
     */
    public function andWhere($xExp, $sOperator = null, $xValue = null, $aParams = array()) {

        if (func_num_args() == 2) {
            $aParams = $sOperator;
        }
        $this->oWhere->_condition(func_num_args(), 'AND', $xExp, $sOperator, $xValue);
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
     * @return Builder
     */
    public function orWhere($xExp, $sOperator = null, $xValue = null, $aParams = array()) {

        if (func_num_args() == 2) {
            $aParams = $sOperator;
        }
        $this->oWhere->_condition(func_num_args(), 'OR', $xExp, $sOperator, $xValue);
        return $this->bind($aParams);
    }

    /**
     * @return Builder
     */
    public function andWhereBegin() {

        $this->oWhere->andConditionBegin();
        return $this;
    }

    /**
     * @return Builder
     */
    public function orWhereBegin() {

        $this->oWhere->orConditionBegin();
        return $this;
    }

    /**
     * @return Builder
     */
    public function whereEnd() {

        $this->oWhere->conditionEnd();
        return $this;
    }

    /**
     * @param $xFields
     *
     * @return $this
     */
    public function groupBy($xFields) {

        if (is_string($xFields)) {
            $this->aGroupBy = array($xFields);
        }
        if (is_array($xFields)) {
            $aData = array();
            foreach($xFields as $sField) {
                $aData[] = array('name' => $sField);
            }
            $this->aGroupBy = $aData;
        }
        return $this;
    }

    /**
     * @param $xFields
     *
     * @return $this
     */
    public function orderBy($xFields) {

        if (is_string($xFields)) {
            $this->aOrderBy[$xFields] = '';
        } elseif (is_array($xFields)) {
            foreach($xFields as $sField => $sOrder) {
                if (is_numeric($sField)) {
                    $sField = $sOrder;
                    $sOrder = '';
                }
                if (isset($this->aOrderBy[$sField])) {
                    unset($this->aOrderBy[$sField]);
                }
                $sOrder = strtoupper($sOrder);
                if ($sOrder != 'ASC' && $sOrder != 'DESC') {
                    $sOrder = '';
                }
                $this->aOrderBy[$sField] = $sOrder;
            }
        }
        return $this;
    }

    /**
     * @param      $iOffset
     * @param null $iLimit
     *
     * @return $this
     */
    public function limit($iOffset, $iLimit = null) {

        if (is_null($iLimit)) {
            $iLimit = $iOffset;
            $iOffset = 0;
        }
        if (is_numeric($iLimit) && is_numeric($iOffset)) {
            $this->setProp('limit', array($iOffset, $iLimit));
        }
        return $this;
    }

    /**
     * @param array $aBindParams
     *
     * @return Builder
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
    protected function _getMainAlias() {

        return '';
    }

    /**
     * @return array
     */
    protected function _getColumns() {

        return $this->aColumns;
    }

    /**
     * @return string
     */
    public function getColumnsStr() {

        $sMainAlias = $this->_getMainAlias();
        if (empty($this->aColumns)) {
            if ($sMainAlias) {
                return $sMainAlias . '.*';
            } else {
                return '*';
            }
        }

        $aResult = array();
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
     * @return array
     */
    public function getTableNames() {

        return $this->aTables;
    }

    /**
     * @return string
     */
    public function getTablesStr() {

        if ($aTableNames = $this->getTableNames()) {
            return $this->_escapeNameList($aTableNames);
        }
        return '';
    }

    /**
     * @return string
     */
    public function getJoinTablesStr() {

        $aResult = array();
        foreach($this->aJoinTables as $aJoinTable) {
            $sJoinTable = $aJoinTable['join'] . ' ' . $this->_escapeName($aJoinTable['name']);
            if (!empty($aJoinTable['alias'])) {
                $sJoinTable .= ' AS ' . $aJoinTable['alias'];
            }
            if (is_string($aJoinTable['on'])) {
                $sJoinTable .= ' ON ' . $aJoinTable['on'];
            } elseif (is_array($aJoinTable['on'])) {
                list($sField1, $sField2) = $this->_arrayPair($aJoinTable['on']);
                if (!strpos($sField1, '.') && !strpos($sField2, '.')) {
                    $sAlias1 = ($aJoinTable['alias'] ? $aJoinTable['alias'] : $aJoinTable['name']);
                    $sField1 = $sAlias1 . '.' . $sField1;
                    $sAlias2 = $this->_getMainAlias();
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

        if ($this->oWhere) {
            $sResult = $this->oWhere->getConditionStr();
        } else {
            $sResult = '';
        }

        return $sResult;
    }

    /**
     * @return string
     */
    public function getGroupByStr() {

        $sResult = '';
        if (!empty($this->aGroupBy)) {
            $sResult = implode(',', $this->aGroupBy);
        }
        return $sResult;
    }

    /**
     * @return string
     */
    public function getOrderByStr() {

        $sResult = '';
        if (!empty($this->aOrderBy)) {
            $sResult = implode(',', $this->aOrderBy);
        }
        return $sResult;
    }

    /**
     * @return string
     */
    public function getLimitStr() {

        $aLimit = $this->getProp('limit');
        if ($aLimit) {
            return implode(',', $aLimit);
        }
        return '';
    }

    /**
     * @return string
     */
    public function getQueryStr() {

        $sColumnsStr = $this->getColumnsStr();
        $sTableListStr = $this->getTablesStr();
        $sJoinTablesStr = $this->getJoinTablesStr();

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

        return self::getParams();
    }


    public function query() {

        $oMapper = new ArMapper(E::ModuleDatabase()->GetConnect());
        $aResult = $oMapper->getRowsByQuery($this);

        return $aResult;
    }

    public function queryRow() {

        $aResult = $this->query();
        if (is_array($aResult) && !empty($aResult)) {
            return reset($aResult);
        }
        return array();
    }

    public function queryScalar() {

        $aResult = $this->queryRow();
        if (is_array($aResult) && !empty($aResult)) {
            return reset($aResult);
        }
        return null;
    }
}

// EOF