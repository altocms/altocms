<?php

/**
 * Используйте константу DBSIMPLE_SKIP в качестве подстановочного значения чтобы пропустить опцональный SQL блок.
 */
define('DBSIMPLE_SKIP', log(0));
/**
 * Имена специализированных колонок в резальтате,
 * которые используются как ключи в результирующем массиве
 */
define('DBSIMPLE_ARRAY_KEY', 'ARRAY_KEY'); // hash-based resultset support
define('DBSIMPLE_PARENT_KEY', 'PARENT_KEY'); // forrest-based resultset support

require_once __DIR__ . '/Database.php';

/**
 * Класс обертка для DbSimple
 *
 * <br>нужен для ленивой инициализации коннекта к базе
 *
 * @package DbSimple
 *
 * Methods:
 *      mixed transaction(string $mode=null)
 *      mixed commit()
 *      mixed rollback()
 *      mixed select(string $query [, $arg1] [,$arg2] ...)
 *      mixed selectRow(string $query [, $arg1] [,$arg2] ...)
 *      array selectCol(string $query [, $arg1] [,$arg2] ...)
 *      string selectCell(string $query [, $arg1] [,$arg2] ...)
 *      mixed query(string $query [, $arg1] [,$arg2] ...)
 *      string escape(mixed $s, bool $isIdent=false)
 *      DbSimple_SubQuery subquery(string $query [, $arg1] [,$arg2] ...)
 *      callback setLogger(callback $logger)
 *      callback setCacher(callback $cacher)
 *      string setIdentPrefix($prx)
 *      string setCachePrefix($prx)
 */
class DbSimple_Connect
{
    /** @var callback обработчик ошибок */
    protected $errorHandler = null;

    protected $_cachePrefix = '';
    protected $_identPrefix = null;
    protected $_logger = null;
    protected $_cacher = null;
    protected $_preformatter = null;
    protected $_tablefunc = null;

    /** @var DbSimple_Database База данных */
    protected $DbSimple;
    /** @var string DSN подключения */
    protected $DSN;
    /** @var string Тип базы данных */
    protected $shema;
    /** @var array Что выставить при коннекте */
    protected $init;
    /** @var integer код ошибки */
    public $error = null;
    /** @var string сообщение об ошибке */
    public $errmsg = null;

    /**
     * Конструктор только запоминает переданный DSN
     * создание класса и коннект происходит позже
     *
     * @param string $dsn DSN строка БД
     */
    public function __construct($dsn)
    {
        $this->DbSimple = null;
        $this->DSN = $dsn;
        $this->init = [];
        $this->shema = ucfirst(substr($dsn, 0, strpos($dsn, ':')));
    }

    /**
     * Взять базу из пула коннектов
     *
     * @param string $dsn DSN строка БД
     *
     * @return DbSimple_Connect
     */
    public static function get($dsn)
    {
        static $pool = [];
        return isset($pool[$dsn]) ? $pool[$dsn] : $pool[$dsn] = new self($dsn);
    }

    /**
     * Возвращает тип базы данных
     *
     * @return string имя типа БД
     */
    public function getShema()
    {
        return $this->shema;
    }

    /**
     * Коннект при первом запросе к базе данных
     */
    public function __call($method, $params)
    {
        if ($this->DbSimple === null) {
            $this->connect($this->DSN);
        }
        return call_user_func_array(array($this->DbSimple, $method), $params);
    }

    /**
     * mixed selectPage(int &$total, string $query [, $arg1] [,$arg2] ...)
     * Функцию нужно вызвать отдельно из-за передачи по ссылке
     */
    public function selectPage(&$total, $query)
    {
        if ($this->DbSimple === null) {
            $this->connect($this->DSN);
        }
        $args = func_get_args();
        $args[0] = & $total;
        return call_user_func_array(array($this->DbSimple, 'selectPage'), $args);
    }

    /**
     * Подключение к базе данных
     * @param string $dsn DSN строка БД
     */
    protected function connect($dsn)
    {
        $parsed = $this->parseDSN($dsn);
        if (!$parsed) {
            $this->errorHandler('Parsing error of DSN', $dsn);
        }
        if (!isset($parsed['scheme'])) {
            $this->errorHandler('Database driver not define', $parsed);
        }
        $this->shema = ucfirst($parsed['scheme']);
        require_once __DIR__ . '/Driver/' . $this->shema . '.php';
        $class = 'DbSimple_Driver_' . $this->shema;

        $nErrorFlag = error_reporting();
        error_reporting($nErrorFlag & ~E_WARNING & ~E_USER_WARNING);
        $this->DbSimple = new $class($parsed);
        $this->errmsg = & $this->DbSimple->errmsg;
        $this->error = & $this->DbSimple->error;
        error_reporting($nErrorFlag);

        if (!$this->DbSimple || $this->error) {
            // Error of database initialization
            if ($this->DbSimple && $this->DbSimple->errmsg) {
                error_reporting($nErrorFlag & ~E_WARNING & ~E_USER_WARNING);
                F::SysWarning($this->DbSimple->errmsg);
                error_reporting($nErrorFlag);
            }
            die("<br><br>\n\n Cannot connect to database");
        }
        $prefix = isset($parsed['prefix'])
            ? $parsed['prefix']
            : ($this->_identPrefix ? $this->_identPrefix : false);
        if ($prefix) {
            $this->DbSimple->setIdentPrefix($prefix);
        }

        if ($this->_cachePrefix) {
            $this->DbSimple->setCachePrefix($this->_cachePrefix);
        }
        if ($this->_cacher) {
            $this->DbSimple->setCacher($this->_cacher);
        }
        if ($this->_logger) {
            $this->DbSimple->setLogger($this->_logger);
        }
        if ($this->_preformatter) {
            $this->DbSimple->setPreFormatter($this->_preformatter);
        }
        if ($this->_tablefunc) {
            $this->DbSimple->setTableNameFunc($this->_tablefunc);
        }

        $this->DbSimple->setErrorHandler(
            $this->errorHandler !== null ? $this->errorHandler : [&$this, 'errorHandler']
        );
        // Eval init queries if they are exist
        foreach ($this->init as $query) {
            call_user_func_array(array($this->DbSimple, 'query'), $query);
        }
        $this->init = [];
    }

    /**
     * Функция обработки ошибок - стандартный обработчик
     * Все вызовы без @ прекращают выполнение скрипта
     *
     * @param string $msg  Сообщение об ошибке
     * @param mixed  $info Подробная информация о контексте ошибки
     */
    public function errorHandler($msg, $info)
    {
        // Если использовалась @, ничего не делать.
        if (!error_reporting()) {
            return;
        }
        // Выводим подробную информацию об ошибке.
        echo "SQL Error: $msg<br><pre>";
        print_r($info);
        echo "</pre>";
        exit();
    }

    /**
     * Выставляет запрос для инициализации
     *
     */
    public function addInit()
    {
        $args = func_get_args();
        if ($this->DbSimple !== null) {
            return call_user_func_array([$this->DbSimple, 'query'], $args);
        }
        $this->init[] = $args;
    }

    /**
     * Устанавливает новый обработчик ошибок
     * Обработчик получает 2 аргумента:
     * - сообщение об ошибке
     * - массив (код, сообщение, запрос, контекст)
     *
     * @param callback|null|false $handler обработчик ошибок
     * <br>  null - по умолчанию
     * <br>  false - отключен
     *
     * @return callback|null|false предыдущий обработчик
     */
    public function setErrorHandler($handler)
    {
        $prev = $this->errorHandler;
        $this->errorHandler = $handler;
        if ($this->DbSimple) {
            $this->DbSimple->setErrorHandler($handler);
        }
        return $prev;
    }

    /**
     * callback setLogger(callback $logger)
     * Set query logger called before each query is executed.
     * Returns previous logger.
     */
    public function setLogger($logger)
    {
        $prev = $this->_logger;
        $this->_logger = $logger;
        if ($this->DbSimple) {
            $this->DbSimple->setLogger($logger);
        }
        return $prev;
    }

    /**
     * callback setCacher(callback $cacher)
     * Set cache mechanism called during each query if specified.
     * Returns previous handler.
     */
    public function setCacher(Zend_Cache_Backend_Interface $cacher = null)
    {
        $prev = $this->_cacher;
        $this->_cacher = $cacher;
        if ($this->DbSimple) {
            $this->DbSimple->setCacher($cacher);
        }
        return $prev;
    }

    /**
     * string setIdentPrefix($prx)
     * Set identifier prefix used for $_ placeholder.
     */
    public function setIdentPrefix($prx)
    {
        $old = $this->_identPrefix;
        if ($prx !== null) {
            $this->_identPrefix = $prx;
        }
        if ($this->DbSimple) {
            $this->DbSimple->setIdentPrefix($prx);
        }
        return $old;
    }

    /**
     * string setCachePrefix($prx)
     * Set cache prefix used in key caclulation.
     */
    public function setCachePrefix($prx)
    {
        $old = $this->_cachePrefix;
        if ($prx !== null) {
            $this->_cachePrefix = $prx;
        }
        if ($this->DbSimple) {
            $this->DbSimple->setCachePrefix($prx);
        }
        return $old;
    }

    public function setPreFormatter($func)
    {
        $prev = $this->_preformatter;
        $this->_preformatter = $func;
        if ($this->DbSimple) {
            $this->DbSimple->setPreFormatter($func);
        }
        return $prev;
    }

    public function setTableNameFunc($func)
    {
        $prev = $this->_tablefunc;
        $this->_tablefunc = $func;
        if ($this->DbSimple) {
            $this->DbSimple->setTableNameFunc($func);
        }
        return $prev;
    }

    /**
     * Разбирает строку DSN в массив параметров подключения к базе
     *
     * @param string $dsn строка DSN для разбора
     *
     * @return array Параметры коннекта
     */
    protected function parseDSN($dsn)
    {
        $parsed = parse_url($dsn);
        if (!$parsed) {
            return null;
        }
        $params = null;
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $params);
            $parsed += $params;
        }
        $parsed['dsn'] = $dsn;

        return $parsed;
    }
}

