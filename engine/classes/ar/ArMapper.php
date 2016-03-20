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
 * Системный класс мапера ORM для работы с БД
 *
 * @package engine.orm
 * @since   1.2
 */
class ArMapper extends \Mapper {

    static protected $aEntityTables = [];

    protected $sCachePrefix = 'schema_table_';

    protected function _getCachedTableInfo($sTableName, $sInfo) {

        $sCacheKey = E::ModuleCache()->Key($this->sCachePrefix, $sTableName);
        $aData = E::ModuleCache()->GetTmp($sCacheKey);
        if (isset($aData[$sInfo])) {
            return $aData[$sInfo];
        }
        return false;
    }

    protected function _setCachedTableInfo($sTableName, $sInfo, $xValue) {

        $sCacheKey = E::ModuleCache()->Key($this->sCachePrefix, $sTableName);
        $aData = E::ModuleCache()->GetTmp($sCacheKey);
        if (empty($aData)) {
            $aData[$sInfo] = array($sInfo => $xValue);
        } else {
            $aData[$sInfo] = $xValue;
        }
        return false;
    }

    /**
     * Primary индекс таблицы
     *
     * @param string $sTableName    Название таблицы
     *
     * @return array
     */
    public function readIndexesFromTable($sTableName) {

        $aIndexes = $this->_getCachedTableInfo($sTableName, 'indexes');
        if (false === $aIndexes) {
            $sql = "SHOW INDEX FROM " . $sTableName;
            $aIndexes = [];
            $aPrimary = [];
            /**
             * TODO: Унести в DbSimple, т.к. может отличаться в разных движках баз
             */
            if ($aRows = $this->oDb->select($sql)) {
                foreach ($aRows as $aRow) {
                    if (strtolower($aRow['Key_name']) == 'primary') {
                        $aIndexes['primary'][$aRow['Seq_in_index']] = $aRow['Column_name'];
                    }
                    $aIndexes[strtolower($aRow['Key_name'])][$aRow['Seq_in_index']] = $aRow['Column_name'];
                }
            }
            $this->_setCachedTableInfo($sTableName, 'primary', $aPrimary);
            $this->_setCachedTableInfo($sTableName, 'indexes', $aIndexes);
        }
        return $aIndexes;
    }

    /**
     * Primary индекс таблицы
     *
     * @param string $sTableName    Название таблицы
     *
     * @return array|bool
     */
    public function readPrimaryIndexFromTable($sTableName) {

        $aPrimary = $this->_getCachedTableInfo($sTableName, 'primary');
        if (false === $aPrimary) {
            $aIndexes = $this->readIndexesFromTable($sTableName);
            if (!empty($aIndexes['primary'])) {
                return $aIndexes['primary'];
            }
        }

        return false;
    }

    /**
     * Список колонок/полей таблицы
     *
     * @param string $sTableName    Название таблицы
     *
     * @return array
     */
    public function readColumnsFromTable($sTableName) {

        $aColumns = $this->_getCachedTableInfo($sTableName, 'columns');
        if (false === $aColumns) {
            $sql = "SHOW COLUMNS FROM " . $sTableName;
            $aColumns = [];
            /**
             * TODO: Унести в DbSimple, т.к. может отличаться в разных движках баз
             */
            if ($aRows = $this->oDb->select($sql)) {
                $aPrimaryKey = [];
                foreach ($aRows as $aRow) {
                    $aColumns[$aRow['Field']] = array(
                        'field' => strtolower($aRow['Field']),
                        'type' => strtolower($aRow['Type']),
                        'null' => ($aRow['Null'] == 'YES'),
                        'default' => $aRow['Default'],
                        'key' => $aRow['Key'],
                        'extra' => $aRow['Extra'],
                    );
                    if ($aRow['Key'] == 'PRI') {
                        $aPrimaryKey[] = $aRow['Field'];
                    }
                }
                if (count($aPrimaryKey) == 1) {
                    // save primary key if it single only
                    $this->_setCachedTableInfo($sTableName, 'primary', $aPrimaryKey);
                }
            }
            $this->_setCachedTableInfo($sTableName, 'columns', $aColumns);
        }
        return $aColumns;
    }

    /**
     * Insert entity into DB
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return int    Если есть primary индекс с автоинкрементом, то возвращает его для новой записи
     */
    public function insertEntity($oEntity) {

        $sTableName = $this->oDb->escape(static::GetTableName($oEntity), true);
        $aFields = $oEntity->getInsertData();
        $aNames = array_keys($aFields);
        $aValues = array_values($aFields);

        $sql = "INSERT INTO " . $sTableName . " (?#) VALUES (?a)";

        return $this->oDb->query($sql, $aNames, $aValues);
    }

    /**
     * Обновление сущности
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return int|bool    Возвращает число измененых записей в БД
     */
    public function updateEntity($oEntity) {

        $sTableName = $this->oDb->escape(static::GetTableName($oEntity), true);
        $aFields = $oEntity->getUpdateData();

        $aPrimaryKey = $oEntity->getPrimaryKeyValue(true);
        // Возможен составной ключ
        $sWhere = ' (1 = 1) ';
        foreach ($aPrimaryKey as $sField => $xValue) {
            $sWhere .= " AND (" . $this->oDb->escape($sField, true) . " = " . $this->oDb->escape($xValue) . ")";
        }
        $sql = "UPDATE " . $sTableName . " SET ?a WHERE {$sWhere} LIMIT 1";

        return $this->oDb->query($sql, $aFields);
    }

    /**
     * Удаление сущности
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return int|bool    Возвращает число удаленных записей в БД
     */
    public function deleteEntity($oEntity) {

        $sTableName = $this->oDb->escape(static::GetTableName($oEntity), true);

        $aPrimaryKey = $oEntity->getPrimaryKeyValue(true);
        // Возможен составной ключ
        $sWhere = ' (1 = 1) ';
        foreach ($aPrimaryKey as $sField => $xValue) {
            $sWhere .= " AND (" . $this->oDb->escape($sField, true) . " = " . $this->oDb->escape($xValue) . ")";
        }
        $sql = "DELETE FROM " . $sTableName . " WHERE {$sWhere} LIMIT 1";

        return $this->oDb->query($sql);
    }

    /**
     * @param Builder $oBuilder
     * @return array
     */
    public function getItemsByCriteria($oBuilder) {

        $aRows = $this->getRowsByQuery($oBuilder);
        $aItems = [];
        if ($aRows) {
            /** @var EntityRecord $oEntity */
            $oEntity = $oBuilder->getEntity();
            foreach($aRows as $xKey => $aRow) {
                $aItems[$xKey] = clone $oEntity;
                $aItems[$xKey]->setProps($aRow);
                $aItems[$xKey]->init();
            }
        }
        return $aItems;
    }

    /**
     * @param Query $oQuery
     *
     * @return array
     */
    public function getRowsByQuery($oQuery) {

        $sSql = $oQuery->getQueryStr();
        $aParams = $oQuery->getQueryParams();
        //var_dump($sSql, $aParams);

        /** @var DbSimple_Command $oSql */
        $oSql = $this->oDb->sql($sSql);

        if (!empty($aParams)) {
            $oSql->bind($aParams);
        }

        $aRows = $oSql->query();

        return $aRows;
    }

    /**
     * Получение числа сущностей по фильтру
     *
     * @param array  $aFilter        Фильтр
     * @param string $sEntityFull    Название класса сущности
     *
     * @return int
     */
    public function getCountItemsByFilter($aFilter, $sEntityFull) {

        $oEntitySample = E::GetEntity($sEntityFull);
        $sTableName = static::GetTableName($sEntityFull);

        list($aFilterFields, $sFilterFields) = $this->BuildFilter($aFilter, $oEntitySample);

        $sql = "SELECT count(*) as c FROM " . $sTableName . " WHERE 1=1 {$sFilterFields} ";
        $aQueryParams = array_merge(array($sql), array_values($aFilterFields));
        if ($aRow = call_user_func_array(array($this->oDb, 'selectRow'), $aQueryParams)) {
            return $aRow['c'];
        }
        return 0;
    }

    /**
     * Получение сущностей по связанной таблице
     *
     * @param array  $aFilter        Фильтр
     * @param string $sEntityFull    Название класса сущности
     *
     * @return array
     */
    public function GetItemsByJoinTable($aFilter, $sEntityFull) {

        $oEntitySample = E::GetEntity($sEntityFull);
        $sTableName = static::GetTableName($sEntityFull);
        $sPrimaryKey = $oEntitySample->_getPrimaryKey();

        list($aFilterFields, $sFilterFields) = $this->BuildFilter($aFilter, $oEntitySample);
        list($sOrder, $sLimit) = $this->BuildFilterMore($aFilter, $oEntitySample);

        $sql = "SELECT a.*, b.* FROM ?# a LEFT JOIN " . $sTableName
            . " b ON b.?# = a.?# WHERE a.?#=? {$sFilterFields} {$sOrder} {$sLimit}";
        $aQueryParams = array_merge(
            array($sql, $aFilter['#join_table'], $sPrimaryKey, $aFilter['#relation_key'], $aFilter['#by_key'],
                  $aFilter['#by_value']), array_values($aFilterFields)
        );

        $aItems = [];
        if ($aRows = call_user_func_array(array($this->oDb, 'select'), $aQueryParams)) {
            foreach ($aRows as $aRow) {
                $oEntity = E::GetEntity($sEntityFull, $aRow);
                $aItems[] = $oEntity;
            }
        }
        return $aItems;
    }

    /**
     * Получение числа сущностей по связанной таблице
     *
     * @param array  $aFilter        Фильтр
     * @param string $sEntityFull    Название класса сущности
     *
     * @return int
     */
    public function GetCountItemsByJoinTable($aFilter, $sEntityFull) {

        $oEntitySample = E::GetEntity($sEntityFull);
        list($aFilterFields, $sFilterFields) = $this->BuildFilter($aFilter, $oEntitySample);

        $sql = "SELECT count(*) as c FROM ?# a  WHERE a.?#=? {$sFilterFields}";
        $aQueryParams = array_merge(
            array($sql, $aFilter['#join_table'], $aFilter['#by_key'], $aFilter['#by_value']),
            array_values($aFilterFields)
        );

        if ($aRow = call_user_func_array(array($this->oDb, 'selectRow'), $aQueryParams)) {
            return $aRow['c'];
        }
        return 0;
    }

    /**
     * Построение фильтра
     *
     * @param array     $aFilter          Фильтр
     * @param EntityRecord $oEntitySample    Объект сущности
     *
     * @return array
     */
    public function BuildFilterX($aFilter, $oEntitySample) {

        $aFilterFields = [];
        foreach ($aFilter as $k => $v) {
            if (substr($k, 0, 1) == '#' || (is_string($v) && substr($v, 0, 1) == '#')) {

            } else {
                $aFilterFields[$oEntitySample->_getField($k)] = $v;
            }
        }

        $sFilterFields = '';
        foreach ($aFilterFields as $k => $v) {
            $aK = explode(' ', trim($k));
            $sFieldCurrent = $this->oDb->escape($aK[0], true);
            $sConditionCurrent = ' = ';
            if (count($aK) > 1) {
                $sConditionCurrent = strtolower($aK[1]);
            }
            if (strtolower($sConditionCurrent) == 'in') {
                $sFilterFields .= " and {$sFieldCurrent} {$sConditionCurrent} ( ?a ) ";
            } else {
                $sFilterFields .= " and {$sFieldCurrent} {$sConditionCurrent} ? ";
            }
        }
        if (isset($aFilter['#where']) && is_array($aFilter['#where'])) {
            // '#where' => array('id = ?d OR name = ?' => array(1,'admin'));
            foreach ($aFilter['#where'] as $sFilterKey => $aValues) {
                $aFilterFields = array_merge($aFilterFields, $aValues);
                $sFilterFields .= ' and ' . trim($sFilterKey) . ' ';
            }
        }
        return array($aFilterFields, $sFilterFields);
    }

    /**
     * Построение дополнительного фильтра
     * Здесь учитываются ключи фильтра вида #*
     *
     * @param array     $aFilter          Фильтр
     * @param EntityRecord $oEntitySample    Объект сущности
     *
     * @return array
     */
    public function BuildFilterMore($aFilter, $oEntitySample) {

        // Сортировка
        $sOrder = '';
        if (isset($aFilter['#order'])) {
            if (!is_array($aFilter['#order'])) {
                $aFilter['#order'] = array($aFilter['#order']);
            }
            foreach ($aFilter['#order'] as $key => $value) {
                if (is_numeric($key)) {
                    $key = $value;
                    $value = 'asc';
                } elseif (!in_array($value, array('asc', 'desc'))) {
                    $value = 'asc';
                }
                $key = $this->oDb->escape($oEntitySample->_getField($key), true);
                $sOrder .= " {$key} {$value},";
            }
            $sOrder = trim($sOrder, ',');
            if ($sOrder != '') {
                $sOrder = "ORDER BY {$sOrder}";
            }
        }

        // Постраничность
        if (isset($aFilter['#page']) && is_array($aFilter['#page']) && count($aFilter['#page']) == 2) {
            // array(2,15) - 2 - page, 15 - count
            $aFilter['#limit'] = array(($aFilter['#page'][0] - 1) * $aFilter['#page'][1], $aFilter['#page'][1]);
        }

        // Лимит
        $sLimit = '';
        if (isset($aFilter['#limit'])) {
            // допустимы варианты: limit=10 , limit=array(10) , limit=array(10,15)
            $aLimit = $aFilter['#limit'];
            if (is_numeric($aLimit)) {
                $iBegin = 0;
                $iEnd = $aLimit;
            } elseif (is_array($aLimit)) {
                if (count($aLimit) > 1) {
                    $iBegin = $aLimit[0];
                    $iEnd = $aLimit[1];
                } else {
                    $iBegin = 0;
                    $iEnd = $aLimit[0];
                }
            }
            $sLimit = "LIMIT {$iBegin}, {$iEnd}";
        }
        return array($sOrder, $sLimit);
    }

    /**
     * Возвращает имя таблицы для сущности
     *
     * @param EntityRecord $oEntity    Объект сущности
     *
     * @return string
     */
    public static function GetTableName($oEntity) {
        /**
         * Варианты таблиц:
         *    prefix_user -> если модуль совпадает с сущностью
         *    prefix_user_invite -> если модуль не сопадает с сущностью
         */
        if (is_object($oEntity)) {
            $sTableName = $oEntity->getTableName();
        } else {
            $sClass = E::ModulePlugin()->GetDelegater(
                'entity', is_object($oEntity) ? get_class($oEntity) : $oEntity
            );
            if (empty(static::$aEntityTables[$sClass])) {
                static::$aEntityTables[$sClass] = $sClass::tableName();
            }
            $sTableName = static::$aEntityTables[$sClass];
        }

        return $sTableName;
    }

    /**
     * Загрузка данных из таблицы связи many_to_many
     *
     * @param string $sDbTableAlias Алиас имени таблицы связи, например, 'db.table.my_relation'
     * @param string $sEntityKey    Название поля в таблице связи с id сущности, для которой зегружаются объекты.
     * @param int    $iEntityId     Id сущнсоти, для который загружаются объекты
     * @param string $sRelationKey  Название поля в таблице связи с id сущности, объекты которой загружаются по связи.
     *
     * @return array Список id из столбца $sRelationKey, у которых столбец $sEntityKey = $iEntityId
     */
    public function getManyToManySet($sDbTableAlias, $sEntityKey, $iEntityId, $sRelationKey) {

        if (!C::Get($sDbTableAlias)) {
            return [];
        }
        $sql = 'SELECT ?# FROM ' . C::Get($sDbTableAlias) . ' WHERE ?# = ?d';
        return $this->oDb->selectCol($sql, $sRelationKey, $sEntityKey, $iEntityId);
    }

    /**
     * Обновление связи many_to_many
     *
     * @param string $sDbTableAlias Алиас имени таблицы связи
     * @param string $sEntityKey    Название поля в таблице связи с id сущности, для которой обновляются связи.
     * @param int    $iEntityId     Id сущнсоти, для который обновляются связи
     * @param string $sRelationKey  Название поля в таблице связи с id сущности, с объектами которой назначаются связи.
     * @param array  $aInsertSet    Массив id для $sRelationKey, которые нужно добавить
     * @param array  $aDeleteSet    Массив id для $sRelationKey, которые нужно удалить
     *
     * @return bool
     */
    public function updateManyToManySet($sDbTableAlias, $sEntityKey, $iEntityId, $sRelationKey, $aInsertSet, $aDeleteSet) {

        if (!C::Get($sDbTableAlias)) {
            return false;
        }
        if (count($aDeleteSet)) {
            $sql = 'DELETE FROM ' . C::Get($sDbTableAlias) . ' WHERE ?# = ?d AND ?# IN (?a)';
            $this->oDb->query($sql, $sEntityKey, $iEntityId, $sRelationKey, $aDeleteSet);
        }

        if (count($aInsertSet)) {
            $sql = 'INSERT INTO ' . C::Get($sDbTableAlias) . ' (?#,?#) VALUES ';
            $aParams = [];
            foreach ($aInsertSet as $iId) {
                $sql .= '(?d, ?d), ';
                $aParams[] = $iEntityId;
                $aParams[] = $iId;
            }
            $sql = substr($sql, 0, -2); // удаление последних ", "
            call_user_func_array(
                array($this->oDb, 'query'), array_merge(array($sql, $sEntityKey, $sRelationKey), $aParams)
            );
        }
        return true;
    }

    /**
     * Удаление связей many_to_many для объекта. Используется при удалении сущности,
     * чтобы не удалять большие коллекции связанных объектов через updateManyToManySet(),
     * где используется IN.
     *
     * @param string $sDbTableAlias Алиас имени таблицы связи
     * @param string $sEntityKey    Название поля в таблице связи с id сущности, для которой удаляются связи.
     * @param int    $iEntityId     Id сущнсоти, для который удаляются связи
     *
     * @return bool
     */
    public function deleteManyToManySet($sDbTableAlias, $sEntityKey, $iEntityId) {

        if (!C::Get($sDbTableAlias)) {
            return false;
        }
        $sql = 'DELETE FROM ' . C::Get($sDbTableAlias) . ' WHERE ?# = ?d';
        $this->oDb->query($sql, $sEntityKey, $iEntityId);
        return true;
    }
}

// EOF