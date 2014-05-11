<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

/**
 * Модуль для работы с базой данных
 * Создаёт объект БД библиотеки DbSimple Дмитрия Котерова
 * Модуль используется в основном для создания коннекта к БД и передачи его в маппер
 *
 * @see     Mapper::__construct
 * Так же предоставляет методы для быстрого выполнения запросов/дампов SQL, актуально для плагинов
 * @see     Plugin::ExportSQL
 *
 * @package engine.modules
 * @since   1.0
 */

/**
 * TODO: подготовить описание, включая патчи:
 *
 * мультиинсерт (?a и двумерный массив в "('1','2','3') ('4','5','6') ..."
 * разворот двумерных массивов в группы условий AND и OR
 * поддержка плейсхолдера ?s — подзапрос с проверкой типов, тоесть неэкранированные данные вставить нельзя, а кусок запроса с подстановкой параметров можно
 * поддержка конструкций {?… } — условная вставка и {… |… } — аналог else
 */
class ModuleDatabase extends Module {
    /**
     * Массив инстанцируемых объектов БД, или проще говоря уникальных коннектов к БД
     *
     * @var array
     */
    protected $aInstance = array();

    static protected $sLastQuery;

    static protected $sLastResult;

    protected $aInitSql
        = array(
            "set character_set_client='utf8', character_set_results='utf8', collation_connection='utf8_bin' ",
        );

    /**
     * Инициализация модуля
     *
     */
    public function Init() {

    }

    protected function _getDbConnect($sDsn) {

        if (Config::Get('db.params.lazy')) {
            // lazy connection
            //F::IncludeLib('DbSimple3/lib/DbSimple/Connect.php');
            $oDbSimple = new DbSimple_Connect($sDsn);
            foreach ($this->aInitSql as $sSql) {
                $oDbSimple->addInit($sSql);
            }
        } else {
            // immediate connection
            //F::IncludeLib('DbSimple3/lib/DbSimple/Generic.php');
            $oDbSimple = DbSimple_Generic::connect($sDsn);
            foreach ($this->aInitSql as $sSql) {
                $oDbSimple->query($sSql);
            }
        }
        return $oDbSimple;
    }

    /**
     * Returns DB object
     *
     * @param   array|null $aConfig     - конфиг подключения к БД(хост, логин, пароль, тип бд, имя бд),
     *                                  если null, то используются параметры из конфига Config::Get('db.params')
     *
     * @return  DbSimple_Connect|null
     */
    public function GetConnect($aConfig = null) {

        // * Если конфиг не передан то используем главный конфиг БД из config.php
        if (is_null($aConfig)) {
            $aConfig = Config::Get('db.params');
        }
        $sDsn = $aConfig['type'] . '://' . $aConfig['user'] . ':' . $aConfig['pass'] . '@' . $aConfig['host'] . ':'
            . $aConfig['port'] . '/' . $aConfig['dbname'];

        // * Проверяем создавали ли уже коннект с такими параметрами подключения(DSN)
        if (isset($this->aInstance[$sDsn])) {
            return $this->aInstance[$sDsn];
        } else {
            // * Если такого коннекта еще не было то создаём его
            $oDbSimple = $this->_getDbConnect($sDsn);

            // * Устанавливаем хук на перехват ошибок при работе с БД
            $oDbSimple->setErrorHandler(array($this, 'ErrorHandler'));

            // * Если нужно логировать все SQL запросы то подключаем логгер
            if (Config::Get('sys.logs.sql_query')) {
                $oDbSimple->setLogger(array($this, 'Logger'));
            } else {
                $oDbSimple->setLogger(array($this, '_internalLogger'));
            }
            $oDbSimple->setTableNameFunc(array($this, 'TableNameTransformer'));

            // Задаем префикс таблиц
            $oDbSimple->setIdentPrefix(Config::Get('db.table.prefix'));

            // * Сохраняем коннект
            $this->aInstance[$sDsn] = $oDbSimple;

            // * Возвращаем коннект
            return $oDbSimple;
        }
    }

    /**
     * Возвращает статистику использования БД - время и количество запросов
     *
     * @return array
     */
    public function GetStats() {

        // не считаем тот самый костыльный запрос, который устанавливает настройки DB соединения
        $aQueryStats = array(
            'time'  => 0,
            'count' => -1,
        );
        foreach ($this->aInstance as $oDb) {
            $aStats = $oDb->getStatistics();
            $aQueryStats['time'] += $aStats['time'];
            $aQueryStats['count'] += $aStats['count'];
        }
        $aQueryStats['time'] = round($aQueryStats['time'], 3);
        return $aQueryStats;
    }

    public function SetLoggerOn() {

        foreach ($this->aInstance as $sDsn => $oDb) {
            $oDb->setLogger(array($this, 'Logger'));
            $this->aInstance[$sDsn] = $oDb;
        }
    }

    public function SetLoggerOff() {

        foreach ($this->aInstance as $sDsn => $oDb) {
            $oDb->setLogger(null);
            $this->aInstance[$sDsn] = $oDb;
        }
    }

    /**
     * Логгирование SQL запросов
     *
     * @param   object $oDb
     * @param   array  $sSql
     */
    function Logger($oDb, $sSql) {

        $this->_internalLogger($oDb, $sSql);

        // Получаем информацию о запросе и сохраняем её в лог
        $sMsg = print_r($sSql, true);
        //Engine::getInstance()->Logger_Dump(Config::Get('sys.logs.sql_query_file'), $sMsg);

        $oLog = Engine::getInstance()->Logger_Reset(Config::Get('sys.logs.sql_query_file'));
        if (substr(trim($sMsg), 0, 2) == '--') {
            // это результат запроса
            $oLog->DumpEnd(trim($sMsg));
        } else {
            // это сам запрос
            $oLog->DumpBegin($sMsg);
        }
    }

    public function TableNameTransformer($sTable) {

        if (substr($sTable, 0, 2) == '?_') {
            $sTable = substr($sTable, 2);
            if ($sTableName = Config::Get('db.table.' . $sTable)) {
                return $sTableName;
            }
            return Config::Get('db.table.prefix') . $sTable;
        }
        return $sTable;
    }

    /**
     * Функция для перехвата SQL ошибок
     *
     * @param   string $sMessage     Сообщение об ошибке
     * @param   array  $aInfo        Информация об ошибке
     */
    function ErrorHandler($sMessage, $aInfo) {

        // * Формируем текст сообщения об ошибке
        $sMsg = "SQL Error: $sMessage\n---\n";
        $sMsg .= print_r($aInfo, true);

        // * Если нужно логировать SQL ошибке то пишем их в лог
        if (Config::Get('sys.logs.sql_error')) {
            Engine::getInstance()->Logger_Dump(Config::Get('sys.logs.sql_error_file'), $sMsg, 'ERROR');
        }

        // * Если стоит вывод ошибок то выводим ошибку на экран(браузер)
        if (error_reporting() && ini_get('display_errors')) {
            exit($sMsg);
        }
    }

    public function _internalLogger($oDb, $sSql) {

        if (substr($sSql, 0, 5) == '  -- ') {
            self::$sLastResult = $sSql;
        } else {
            self::$sLastQuery = $sSql;
        }
    }

    public function GetLastQuery() {

        return self::$sLastQuery;
    }

    public function GetLastResult() {

        return self::$sLastResult;
    }

    /**
     * Экспорт SQL дампа в БД
     *
     * @see ExportSQLQuery
     *
     * @param string     $sFilePath    Полный путь до файла SQL
     * @param array|null $aConfig      Конфиг подключения к БД
     *
     * @return array
     */
    public function ExportSQL($sFilePath, $aConfig = null) {

        if (!is_file($sFilePath)) {
            return array('result' => false, 'errors' => array("cant find file '$sFilePath'"));
        } elseif (!is_readable($sFilePath)) {
            return array('result' => false, 'errors' => array("cant read file '$sFilePath'"));
        }
        $sFileQuery = file_get_contents($sFilePath);
        return $this->ExportSQLQuery($sFileQuery, $aConfig);
    }

    /**
     * Экспорт SQL в БД
     *
     * @param string     $sFileQuery    Строка с SQL запросом
     * @param array|null $aConfig       Конфиг подключения к БД
     *
     * @return array    Возвращает массив вида array('result'=>bool,'errors'=>array())
     */
    public function ExportSQLQuery($sFileQuery, $aConfig = null) {

        // * Замена префикса таблиц
        $sFileQuery = str_replace('prefix_', Config::Get('db.table.prefix'), $sFileQuery);

        // * Массивы запросов и пустой контейнер для сбора ошибок
        $aErrors = array();
        $aQuery = explode(';', $sFileQuery);

        // * Выполняем запросы по очереди
        foreach ($aQuery as $sQuery) {
            $sQuery = trim($sQuery);

            // * Заменяем движок базы данных, если таковой указан в запросе
            if (Config::Get('db.tables.engine') != 'InnoDB') {
                $sQuery = str_ireplace('ENGINE=InnoDB', "ENGINE=" . Config::Get('db.tables.engine'), $sQuery);
            }

            if ($sQuery != '') {
                $bResult = $this->GetConnect($aConfig)->query($sQuery);
                if ($bResult === false) {
                    $aErrors[] = mysql_error();
                }
            }
        }

        // * Возвращаем результат выполнения, в зависимости от количества ошибок
        if (count($aErrors) == 0) {
            return array('result' => true, 'errors' => null);
        }
        return array('result' => false, 'errors' => $aErrors);
    }

    /**
     * Проверяет существование таблицы
     *
     * @param   string     $sTableName    - Название таблицы, необходимо перед именем таблицы добавлять "prefix_",
     *                                      это позволит учитывать произвольный префикс таблиц у пользователя
     * @param   array|null $aConfig       - Конфиг подключения к БД
     *
     * @return  bool
     */
    public function isTableExists($sTableName, $aConfig = null) {

        $sTableName = str_replace('prefix_', Config::Get('db.table.prefix'), $sTableName);
        $sQuery = "SHOW TABLES LIKE '{$sTableName}'";
        if ($aRows = $this->GetConnect($aConfig)->select($sQuery)) {
            return true;
        }
        return false;
    }

    /**
     * Проверяет существование поля в таблице
     *
     * @param   string     $sTableName      - Название таблицы, необходимо перед именем таблицы добавлять "prefix_",
     *                                        это позволит учитывать произвольный префикс таблиц у пользователя
     * @param   string     $sFieldName      - Название поля в таблице
     * @param   array|null $aConfig         - Конфиг подключения к БД
     *
     * @return  bool
     */
    public function isFieldExists($sTableName, $sFieldName, $aConfig = null) {

        $sTableName = str_replace('prefix_', Config::Get('db.table.prefix'), $sTableName);
        $sQuery = "SHOW FIELDS FROM {$sTableName}";
        if ($aRows = $this->GetConnect($aConfig)->select($sQuery)) {
            foreach ($aRows as $aRow) {
                if ($aRow['Field'] == $sFieldName) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Доавляет новый тип в поле таблицы с типом enum
     *
     * @param   string     $sTableName      - Название таблицы, необходимо перед именем таблицы добавлять "prefix_",
     *                                        это позволит учитывать произвольный префикс таблиц у пользователя
     * @param   string     $sFieldName      - Название поля в таблице
     * @param   string     $sType           - Название типа
     * @param   array|null $aConfig         - Конфиг подключения к БД
     *
     * @return  null|bool
     */
    public function AddEnumType($sTableName, $sFieldName, $sType, $aConfig = null) {

        $sTableName = str_replace('prefix_', Config::Get('db.table.prefix'), $sTableName);
        $sQuery = "SHOW COLUMNS FROM  {$sTableName}";

        if ($aRows = $this->GetConnect($aConfig)->select($sQuery)) {
            foreach ($aRows as $aRow) {
                if ($aRow['Field'] == $sFieldName) {
                    break;
                }
            }
            if (substr($aRow['Type'], 0, 4) !== 'enum') {
                return true;
            }
            if (strpos($aRow['Type'], "'{$sType}'") === false) {
                $aRow['Type'] = str_ireplace('enum(', "enum('{$sType}',", $aRow['Type']);
                $sQuery = "ALTER TABLE {$sTableName} MODIFY {$sFieldName} " . $aRow['Type'];
                $sQuery .= ($aRow['Null'] == 'NO') ? ' NOT NULL ' : ' NULL ';
                $sQuery .= is_null($aRow['Default']) ? ' DEFAULT NULL ' : " DEFAULT '{$aRow['Default']}' ";
                $this->GetConnect($aConfig)->select($sQuery);
            }
        }
    }

    public function AddField($sTableName, $sFieldName, $sFieldType, $sDefault = null, $bNull = null, $sAdditional = '', $aConfig = null) {

        $sTableName = str_replace('prefix_', Config::Get('db.table.prefix'), $sTableName);
        if (is_null($sDefault) && is_null($bNull)) {
            $sNull = '';
            $sDefault = ' DEFAULT NULL ';
        } else {
            if (!is_null($bNull)) {
                $sNull = ($bNull ? ' NULL ' : ' NOT NULL ');
            }
            if (!is_null($sDefault)) {
                $sDefault = ' DEFAULT \'' . (string)$sDefault . '\'';
            }
        }
        $sQuery = "ALTER TABLE {$sTableName} ADD {$sFieldName} {$sFieldType} {$sNull} {$sDefault} {$sAdditional}";
        $this->GetConnect($aConfig)->query($sQuery);
    }

    public function AddIndex($sTableName, $aIndexFields, $sIndexType = null, $sIndexName = null, $aConfig = null) {

        $sTableName = str_replace('prefix_', Config::Get('db.table.prefix'), $sTableName);
        if (!is_array($aIndexFields)) {
            $aIndexFields = array($aIndexFields);
        }
        $sFields = implode(',', $aIndexFields);
        if (!$sIndexType || !in_array(strtoupper($sIndexType), array('PRIMARY', 'INDEX', 'UNIQUE'))) {
            $sIndexType = 'INDEX';
        } else {
            $sIndexType = strtoupper($sIndexType);
        }
        if (!$sIndexName || $sIndexName == 'PRIMARY') {
            $sIndexName = '';
        }
        $sQuery = "ALTER TABLE {$sTableName} ADD {$sIndexType} {$sIndexName} ({$sFields})";
        $this->GetConnect($aConfig)->query($sQuery);
    }

}

// EOF