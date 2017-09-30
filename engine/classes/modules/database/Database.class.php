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

    protected $sLogFile;

    protected $aSqlErrors = array();

    protected $aInitSql
        = array(
            "set character_set_client='%%charset%%', character_set_results='%%charset%%', collation_connection='utf8_bin' ",
        );

    /**
     * Инициализация модуля
     *
     */
    public function Init() {

        $sCharset = C::Get('db.params.charset');
        if (!$sCharset) {
            $sCharset = 'utf8';
        }
        $this->aInitSql = str_replace('%%charset%%', $sCharset, $this->aInitSql);
        $this->sLogFile = Config::Get('sys.logs.sql_query_file');
    }

    /**
     * @param $sDsn
     *
     * @return DbSimple_Connect
     */
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
                if (Config::Get('sys.logs.sql_query_rewrite')) {
                    $oLog = E::ModuleLogger()->Reset($this->sLogFile);
                    F::File_DeleteAs($oLog->getFileDir() . pathinfo($oLog->getFileName(), PATHINFO_FILENAME) . '*');
                }
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
     * @param object $oDb
     * @param array  $xSql
     */
    public function Logger($oDb, $xSql) {

        $this->_internalLogger($oDb, $xSql);

        // Получаем информацию о запросе и сохраняем её в лог
        $sMsg = print_r($xSql, true);

        $oLog = E::ModuleLogger()->Reset($this->sLogFile);
        if (0 === strpos(trim($sMsg), '--')) {
            // это результат запроса
            if (DEBUG) {
                $aStack = debug_backtrace(false);
                $i = 0;
                while (empty($aStack[$i]['file']) || (isset($aStack[$i]['file']) && strpos($aStack[$i]['file'], 'DbSimple') === false)) {
                    $i += 1;
                }
                while (empty($aStack[$i]['file']) || (isset($aStack[$i]['file']) && strpos($aStack[$i]['file'], 'DbSimple') !== false)) {
                    $i += 1;
                }
                $sCaller = '';
                if (isset($aStack[$i]['file'])) {
                    $sCaller .= $aStack[$i]['file'];
                }
                if (isset($aStack[$i]['line'])) {
                    $sCaller .= ' (' . $aStack[$i]['line'] . ')';
                }
                $oLog->DumpAppend(trim($sMsg));
                $oLog->DumpEnd('-- [src]' . $sCaller);
            } else {
                $oLog->DumpEnd(trim($sMsg));
            }
        } else {
            // это сам запрос
            if (DEBUG) {
                $aLines = array_map('trim', explode("\n", $sMsg));
                foreach ($aLines as $iIndex => $sLine) {
                    if (!$sLine) {
                        unset($aLines[$iIndex]);
                    } else {
                        $aLines[$iIndex] = '    ' . $sLine;
                    }
                }
                $sMsg = implode(PHP_EOL, $aLines);
                $sMsg = '-- [id]' . md5($sMsg) . PHP_EOL . $sMsg;
            }
            $oLog->DumpBegin($sMsg);
        }
    }

    /**
     * @param bool $bAsText
     *
     * @return array|string
     */
    protected static function _getCallStack($bAsText = false) {

        $aStack = debug_backtrace(false);
        $i = 0;
        while (empty($aStack[$i]['file']) || (isset($aStack[$i]['file']) && strpos($aStack[$i]['file'], 'DbSimple') === false)) {
            ++$i;
        }
        while (empty($aStack[$i]['file']) || (isset($aStack[$i]['file']) && strpos($aStack[$i]['file'], 'DbSimple') !== false)) {
            ++$i;
        }
        $aCallStack = array_slice($aStack, $i);
        if ($bAsText) {
            $sResult = '';
            foreach ($aCallStack as $aCallerPoint) {
                $sCaller = '';
                if (isset($aCallerPoint['file'])) {
                    $sCaller .= '   ' . $aCallerPoint['file'];
                }
                if (isset($aCallerPoint['line'])) {
                    $sCaller .= ' (' . $aCallerPoint['line'] . ')';
                }
                if ($sResult) {
                    $sResult .= PHP_EOL;
                }
                $sResult .= $sCaller;
            }
            return $sResult;
        }

        return $aCallStack;
    }

    /**
     * @param $sTable
     *
     * @return bool|mixed|string
     */
    public function TableNameTransformer($sTable) {

        if (substr($sTable, 0, 2) === '?_') {
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
    public function ErrorHandler($sMessage, $aInfo) {

        // * Формируем текст сообщения об ошибке
        $sMsg = "SQL Error: $sMessage\n---\n";
        $sMsg .= print_r($aInfo, true);

        $this->aSqlErrors[] = $sMsg;

        // * Если нужно логировать SQL ошибки то пишем их в лог
        if (Config::Get('sys.logs.sql_error')) {
            if (Config::Get('sys.logs.error_callstack')) {
                $sErrorStack = self::_getCallStack(true);
                $sMsg .= PHP_EOL . 'Callstack:' . PHP_EOL . $sErrorStack;
            }
            E::ModuleLogger()->Dump(Config::Get('sys.logs.sql_error_file'), $sMsg, 'ERROR');
        }

        // * Если стоит вывод ошибок то выводим ошибку на экран (в браузер)
        if (Config::Get('sys.logs.sql_error_display')) {
            exit($sMsg);
        }
    }

    /**
     * @param $oDb
     * @param $sSql
     */
    public function _internalLogger($oDb, $sSql) {

        if (substr($sSql, 0, 5) === '  -- ') {
            self::$sLastResult = $sSql;
        } else {
            self::$sLastQuery = $sSql;
        }
    }

    /**
     * @return string
     */
    public function GetLastQuery() {

        return self::$sLastQuery;
    }

    /**
     * @return string
     */
    public function GetLastResult() {

        return self::$sLastResult;
    }

    /**
     * @return string|null
     */
    public function GetLastError() {

        if (!empty($this->aSqlErrors)) {
            return end($this->aSqlErrors);
        }
        return null;
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
            return array('result' => false, 'errors' => array("can't find file '$sFilePath'"));
        }
        if (!is_readable($sFilePath)) {
            return array('result' => false, 'errors' => array("can't read file '$sFilePath'"));
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
        $aQuery = preg_split('/;\\r?\\n/', $sFileQuery);

        // * Выполняем запросы по очереди
        foreach ($aQuery as $sQuery) {
            $sQuery = trim($sQuery);

            if ($sQuery != '') {
                // * Заменяем движок базы данных, если таковой указан в запросе
                if (Config::Get('db.tables.engine') !== 'InnoDB') {
                    $sQuery = str_ireplace('ENGINE=InnoDB', "ENGINE=" . Config::Get('db.tables.engine'), $sQuery);
                }

                $bResult = $this->GetConnect($aConfig)->query($sQuery);
                if ($bResult === false) {
                    $aErrors[] = $this->GetLastError();
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

        $sTableName = $this->TableNameTransformer($sTableName);
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

        $sTableName = $this->TableNameTransformer($sTableName);
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
                return false;
            }
            if (strpos($aRow['Type'], "'{$sType}'") === false) {
                $aRow['Type'] = str_ireplace('enum(', "enum('{$sType}',", $aRow['Type']);
                $sQuery = "ALTER TABLE {$sTableName} MODIFY {$sFieldName} " . $aRow['Type'];
                $sQuery .= ($aRow['Null'] === 'NO') ? ' NOT NULL ' : ' NULL ';
                $sQuery .= is_null($aRow['Default']) ? ' DEFAULT NULL ' : " DEFAULT '{$aRow['Default']}' ";
                $this->GetConnect($aConfig)->select($sQuery);
            }
        }
        return true;
    }

    /**
     * @param        $sTableName
     * @param        $sFieldName
     * @param        $sFieldType
     * @param null   $sDefault
     * @param null   $bNull
     * @param string $sAdditional
     * @param null   $aConfig
     */
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

    /**
     * @param      $sTableName
     * @param      $aIndexFields
     * @param null $sIndexType
     * @param null $sIndexName
     * @param null $aConfig
     */
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
        if (!$sIndexName || $sIndexName === 'PRIMARY') {
            $sIndexName = '';
        }
        $sQuery = "ALTER TABLE {$sTableName} ADD {$sIndexType} {$sIndexName} ({$sFields})";
        $this->GetConnect($aConfig)->query($sQuery);
    }

}

// EOF
