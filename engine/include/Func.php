<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * Static class of engine functions
 */
class Func {
    const ERROR_LOG = 'error.log';

    static protected $aExtsions = array();

    static public function init() {

        static::_loadExtension('File');
        register_shutdown_function('F::done');
        set_error_handler('F::_errorHandler');
        set_exception_handler('F::_exceptionHandler');
    }

    static public function done() {

        if ($aError = error_get_last()) {
            //while (ob_get_level()) ob_end_clean();
            static::_errorHandler($aError['type'], $aError['message'], $aError['file'], $aError['line']);
        }
    }

    /**
     * @return string
     */
    static public function _errorLogFile() {

        return static::_getConfig('sys.logs.error_file', static::ERROR_LOG);
    }

    /**
     * @return bool
     */
    static public function _errorLogExtInfo() {

        return (bool)static::_getConfig('sys.logs.error_extinfo', true);
    }

    /**
     * @param $sError
     */
    static public function _errorLog($sError) {

        $sText = '';
        if (static::_errorLogExtInfo() && isset($_SERVER) && is_array($_SERVER)) {
            foreach ($_SERVER as $sKey => $sVal) {
                if (!in_array($sKey, array('PATH', 'SystemRoot', 'COMSPEC', 'PATHEXT', 'WINDIR')))
                    $sText .= "  _SERVER['$sKey']=$sVal\n";
            }
            $sText = "$sError\n---\n$sText\n---\n";
        } else {
            $sText = $sError;
        }

        if (!static::_log($sText, static::_errorLogFile(), 'ERROR')) {
            // Если не получилось вывести в лог-файл, то выводим ошибку на экран
            echo $sError;
        }
    }

    /**
     * @param string $sText
     * @param string $sLogFile
     * @param string $sLevel
     *
     * @return bool
     */
    static public function _log($sText, $sLogFile, $sLevel = null) {

        if (class_exists('ModuleLogger', false) || Loader::Autoload('ModuleLogger')) {
            // Если загружен модуль Logger, то логгируем ошибку с его помощью
            return E::Logger_Dump($sLogFile, $sText, $sLevel);
        } elseif (class_exists('Config', false)) {
            // Если логгера нет, но есть конфиг, то самостоятельно пишем в файл
            $sFile = Config::Get('sys.logs.dir') . $sLogFile;
            $sText = '[' . date('Y-m-d H:i:s') . ']' . "\n" . $sText;
            return F::File_PutContents($sFile, $sText, FILE_APPEND | LOCK_EX);
        }
        return false;
    }

    /**
     * @param string $sError
     * @param string $sLogFile
     */
    static public function _errorDisplay($sError, $sLogFile = null) {

        if (!static::AjaxRequest()) {
            echo '<span style="display:none;">"></span><br/></div>' . $sError . '<br/>';
            if ($sLogFile) {
                echo 'See details in ' . $sLogFile;
            }
        }
    }

    /**
     * @param int    $nErrNo
     * @param string $sErrMsg
     * @param string $sErrFile
     * @param int    $nErrLine
     *
     * @return bool
     */
    static public function _errorHandler($nErrNo, $sErrMsg, $sErrFile, $nErrLine) {

        $aErrors = array(
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        );

        if ($nErrNo & error_reporting()) {
            if (F::IsDebug()) {
                static::_errorDisplay("{$aErrors[$nErrNo]} [$nErrNo] $sErrMsg ($sErrFile on line $nErrLine)");
            } else {
                static::_errorDisplay("{$aErrors[$nErrNo]} [$nErrNo] $sErrMsg", static::_errorLogFile());
                static::_errorLog("{$aErrors[$nErrNo]} [$nErrNo] $sErrMsg ($sErrFile on line $nErrLine)");
            }
        }

        /* Don't execute PHP internal error handler */
        return true;
    }

    /**
     * @param $oException
     */
    static public function _exceptionHandler($oException) {

        static::_errorDisplay('Exception: ' . $oException->getMessage());
        $sLogMsg = 'Exception: ' . $oException->getMessage();
        if (property_exists($oException, 'sAdditionalInfo')) {
            $sLogMsg .= "\n" . $oException->sAdditionalInfo;
        }
        static::_errorLog($sLogMsg);
    }

    /**
     * @param $sName
     * @param $aArguments
     *
     * @return mixed
     */
    static public function __callStatic($sName, $aArguments) {

        if ($nPos = strpos($sName, '_')) {
            $sExtension = substr($sName, 0, $nPos);
            $sMethod = substr($sName, $nPos + 1);
        } else {
            $sExtension = 'Main';
            $sMethod = $sName;
        }
        if (!isset(static::$aExtsions[$sExtension])) {
            static::_loadExtension($sExtension);
        }
        if (isset(static::$aExtsions[$sExtension]) && method_exists(static::$aExtsions[$sExtension], $sMethod)) {
            return call_user_func_array(static::$aExtsions[$sExtension] . '::' . $sMethod, $aArguments);
        }

        // Function not found
        $aCaller = static::_Caller(2);
        $sCallerStr = 'Func::' . $sName . '()';
        $sPosition = '';
        if ($aCaller) {
            if (isset($aCaller['class']) && isset($aCaller['function']) && isset($aCaller['type'])) {
                $sCallerStr = $aCaller['class'] . $aCaller['type'] . $aCaller['function'] . '()';
            }
            if (isset($aCaller['file']) && isset($aCaller['line'])) {
                $sPosition = ' in ' . $aCaller['file'] . ' on line ' . $aCaller['line'];
            }
        }
        $sMsg = 'Call to undefined method ' . $sCallerStr . $sPosition;
        static::_FatalError($sMsg);
    }

    /**
     * @param $sExtension
     */
    static protected function _loadExtension($sExtension) {

        if (!isset(static::$aExtsions[$sExtension])) {
            // сначала проверяем кастомные функции
            if (is_file($sFile = dirname(dirname(__DIR__)) . '/include/functions/' . $sExtension . '.php')) {
                static::IncludeFile($sFile);
                static::$aExtsions[$sExtension] = 'AppFunc_' . $sExtension;
            } elseif (is_file($sFile = __DIR__ . '/functions/' . $sExtension . '.php')) {
                static::IncludeFile($sFile);
                static::$aExtsions[$sExtension] = 'AltoFunc_' . $sExtension;
            } else {
                static::_FatalError('Cannot found functions set (extension) "' . $sExtension . '"');
            }
        }
    }

    /**
     * TODO: Не выводить полностью текст ошибки на экран, а логгировать
     *
     * @param $sMessage
     * @throws Exception
     */
    static protected function _FatalError($sMessage) {

        echo '<p>Fatal error: ' . $sMessage . '</p>';
        throw new Exception('Fatal error');
        exit;
    }

    static protected function _CallerStr($nOffset = 2) {

        $aCaller = static::_Caller($nOffset);
        $sCallerStr = '';
        $sPosition = '';
        if ($aCaller) {
            if (isset($aCaller['class']) && isset($aCaller['function']) && isset($aCaller['type'])) {
                $sCallerStr = $aCaller['class'] . $aCaller['type'] . $aCaller['function'] . '()';
            }
            if (isset($aCaller['file']) && isset($aCaller['line'])) {
                $sPosition = ' in ' . $aCaller['file'] . ' on line ' . $aCaller['line'];
            }
        }
        return $sCallerStr . $sPosition;
    }

    /**
     * @param int  $nOffset
     * @param bool $bString
     *
     * @return string
     */
    static protected function _Caller($nOffset = 1, $bString = false) {

        $aData = static::_CallStack($nOffset + 1, 1);
        if (sizeof($aData)) {
            if ($bString) {
                if ($aCaller = $aData[0]) {
                    $sCallerStr = '';
                    $sPosition = '';
                    if (isset($aCaller['class']) && isset($aCaller['function']) && isset($aCaller['type'])) {
                        $sCallerStr = $aCaller['class'] . $aCaller['type'] . $aCaller['function'] . '()';
                    }
                    if (isset($aCaller['file']) && isset($aCaller['line'])) {
                        $sPosition = ' in ' . $aCaller['file'] . ' on line ' . $aCaller['line'];
                    }
                }
                return $sCallerStr . $sPosition;
            }
            return ($aData[0]);
        }
    }

    /**
     * @param int  $nOffset
     * @param null $nLength
     *
     * @return array
     */
    static protected function _CallStack($nOffset = 1, $nLength = null) {

        $aStack = array_slice(debug_backtrace(), $nOffset, $nLength);
        return $aStack;
    }

    /**
     * @param      $sParam
     * @param null $xDefault
     *
     * @return mixed|null
     */
    static public function _getConfig($sParam, $xDefault = null) {

        if (class_exists('Config', false)) {
            $xResult = Config::Get($sParam);
        } else {
            $xResult = $xDefault;
        }
        return $xResult;
    }

    static public function SysWarning($sMessage) {

        $aCaller = self::_Caller();
        self::_errorHandler(
            E_USER_WARNING, $sMessage,
            isset($aCaller['file']) ? $aCaller['file'] : 'Unknown',
            isset($aCaller['line']) ? $aCaller['line'] : 0
        );
    }

    static public function IsDebug() {

        return defined('DEBUG') && DEBUG;
    }

    /**
     * @param $sMsg
     *
     * @return bool
     */
    static public function LogError($sMsg) {

        return static::_log($sMsg, static::_errorLogFile(), 'ERROR');
    }

    /**
     * Includes PHP-file with statistics
     *
     * @param   string  $sFile      - file name and path
     * @param   bool    $bOnce      - once include
     * @param   bool    $bConfig    - include as config-file
     * @return  mixed
     */
    static public function IncludeFile($sFile, $bOnce = true, $bConfig = false) {

        $sDir = dirname($sFile);
        if ($sDir == '.' || $sDir == '..' || substr($sDir, 0, 2) == './' || substr($sDir, 0, 3) == '../') {
            $aCaller = static::_Caller();
            if (isset($aCaller['file'])) {
                $sRealPath = realpath(dirname($aCaller['file']) . '/' . $sFile);
            }
        } else {
            $sRealPath = realpath($sFile);
        }
        if ($sRealPath) {
            $sFile = $sRealPath;
        }
        if (isset(static::$aExtsions['File']) && is_callable($sFunc = static::$aExtsions['File'] . '::IncludeFile')) {
            return call_user_func_array($sFunc, array($sFile, $bOnce, $bConfig));
        } else {
            if ($bOnce) {
                return include_once($sFile);
            } else {
                return include($sFile);
            }
        }
    }

    /**
     * Includes PHP-file from library dir
     *
     * @param   string  $sFile
     * @param   bool $bOnce
     * @return  mixed
     */
    static public function IncludeLib($sFile, $bOnce = true) {

        if (class_exists('Config', false)) {
            return static::IncludeFile(Config::Get('path.dir.libs') . '/' . $sFile, $bOnce);
        } else {
            return static::IncludeFile(dirname(__DIR__) . '/libs/' . $sFile, $bOnce);
        }
    }

    /**
     * Returns full dir path to plugins folder
     *
     * @param bool $bApplication
     * @return string
     */
    static public function GetPluginsDir($bApplication = false) {

        if (class_exists('Config', false)) {
            if ($bApplication) {
                $sPluginsDir = Config::Get('path.dir.app') . 'plugins/';
            } else {
                $sPluginsDir = Config::Get('path.dir.common') . 'plugins/';
            }
        } else {
            if ($bApplication) {
                $sPluginsDir = F::File_RootDir() . 'app/plugins/';
            } else {
                $sPluginsDir = F::File_RootDir() . 'common/plugins/';
            }
        }
        return $sPluginsDir;
    }

    /**
     * Returns full dir path to plugins dat file
     *
     * @return string
     */
    static public function GetPluginsDatDir() {

        if (class_exists('Config', false)) {
            return Config::Get('path.dir.app') . 'plugins/';
        } else {
            return F::File_RootDir() . 'app/plugins/';
        }
    }

    /**
     * Returns full dir path of plugins dat file
     *
     * @return string
     */
    static public function GetPluginsDatFile() {

        if (class_exists('Config', false)) {
            return static::GetPluginsDatDir() . Config::Get('sys.plugins.activation_file');
        } else {
            return static::GetPluginsDatDir() . 'plugins.dat';
        }
    }

    /**
     * Получить список плагинов
     *
     * @param   bool    $bAll   - все плагины (иначе - только активные)
     * @return  array
     */
    static public function GetPluginsList($bAll = false) {

        $sPluginsDir = static::GetPluginsDir();
        $sPluginsListFile = static::GetPluginsDatFile();
        $aPlugins = array();
        if ($bAll) {
            $aPluginRaw = array();
            $aPaths = glob($sPluginsDir . '*', GLOB_ONLYDIR);
            if ($aPaths)
                foreach ($aPaths as $sPath) {
                    $aPluginRaw[] = basename($sPath);
                }
        } else {
            if ($aPluginRaw = @file($sPluginsListFile)) {
                $aPluginRaw = array_map('trim', $aPluginRaw);
                $aPluginRaw = array_unique($aPluginRaw);
            }
        }
        if ($aPluginRaw)
            foreach ($aPluginRaw as $sPlugin) {
                $sPluginXML = "$sPluginsDir/$sPlugin/plugin.xml";
                if (is_file($sPluginXML)) {
                    $aPlugins[] = $sPlugin;
                }
            }
        return $aPlugins;
    }

    /**
     * функция доступа к REQUEST/GET/POST параметрам
     *
     * @param   string  $sName
     * @param   mixed   $xDefault
     * @param   string  $sType
     * @return  mixed
     */
    static public function GetRequest($sName, $xDefault = null, $sType = null) {
        /**
         * Выбираем в каком из суперглобальных искать указанный ключ
         */
        switch (strtolower($sType)) {
            default:
            case null:
                $aStorage = $_REQUEST;
                break;
            case 'get':
                $aStorage = $_GET;
                break;
            case 'post':
                $aStorage = $_POST;
                break;
        }

        if (isset($aStorage[$sName])) {
            if (is_string($aStorage[$sName])) {
                return trim($aStorage[$sName]);
            } else {
                return $aStorage[$sName];
            }
        }
        return $xDefault;
    }

    /**
     * @param      $sName
     * @param null $xDefault
     * @param null $sType
     *
     * @return string
     */
    static public function GetRequestStr($sName, $xDefault = null, $sType = null) {

        return (string)static::GetRequest($sName, $xDefault, $sType);
    }

    /**
     * Возвращает значение параметра, переданого методом POST
     *
     * @param   string  $sName
     * @param   mixed   $xDefault
     * @return  bool
     */
    static function GetPost($sName, $xDefault = null) {

        return static::GetRequest($sName, $xDefault, 'post');
    }

    /**
     * Возвращает значение параметра, переданого методом POST
     *
     * @param   string  $sName
     * @param   string  $sDefault
     * @return  bool
     */
    static function GetPostStr($sName, $sDefault = null) {

        if (!is_null($sDefault)) $sDefault = (string)$sDefault;
        return static::GetRequestStr($sName, $sDefault, 'post');
    }

    /**
     * Определяет, был ли передан указанный параметр методом POST
     *
     * @param   string  $sName
     * @return  bool
     */
    static function isPost($sName) {

        return (static::GetPost($sName) !== null);
    }

    /**
     * Check if request is ajax
     *
     * @return  bool
     */
    static public function AjaxRequest() {

        return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower(
                $_SERVER['HTTP_X_REQUESTED_WITH'] === 'xmlhttprequest'
            ))
        || (isset($_REQUEST['ALTO_AJAX']) && $_REQUEST['ALTO_AJAX']);
    }

    /**
     * Аналог ф-ции stripslashes, умеющая обрабатывать массивы
     *
     * @param   string|array    $xData
     */
    static public function StripSlashes(&$xData) {

        if (is_array($xData)) {
            array_walk($xData, 'static::StripSlashes');
        } else {
            $xData = stripslashes($xData);
        }
    }

    /**
     * Аналог ф-ции htmlspecialchars, умеющая обрабатывать массивы
     *
     * @param   mixed   $xData
     * @return  void
     */
    static public function HtmlSpecialChars(&$xData) {

        if (is_array($xData)) {
            array_walk($xData, 'static::HtmlSpecialChars');
        } else {
            $xData = htmlspecialchars($xData);
        }
    }

    /**
     * Приведение адреса к абсолютному виду
     *
     * Путь в $sLocation может быть как абсолютным, так и относительным.
     * Абсолютный путь определяется по наличию хоста
     *
     * Если задан относительный путь, то итоговый URL определяется в зависимости от второго парамтера.
     * Если $bRealHost == false (по умолчанию), то за основу берется root-адрес сайта, который задан в конфигурации.
     * В противном случае основа адреса - это реальный адрес хоста из $_SERVER['SERVER_NAME']
     *
     * @param   string  $sLocation  - адрес перехода (напр., 'http://ya.ru/demo/', '/123.html', 'blog/add/')
     * @param   bool    $bRealHost  - в случае относительной адресации брать адрес хоста из конфига или реальный
     *
     * @return  string
     */
    static public function RealUrl($sLocation, $bRealHost = false) {

        $sProtocol = static::HttpProtocol();

        $aParts = parse_url($sLocation);
        // если парсером хост не обнаружен, то задан относительный путь
        if (!isset($aParts['host'])) {
            if (!$bRealHost) {
                $sUrl = F::File_RootUrl() . $sLocation;
            } else {
                if (strpos($sProtocol, 'HTTPS') === 0) {
                    $sUrl = 'https://';
                } else {
                    $sUrl = 'http://';
                }
                if (isset($_SERVER['SERVER_NAME'])) $sUrl .= $_SERVER['SERVER_NAME'];
                if (substr($sLocation, 0, 1) == '/') {
                    $sUrl .= $sLocation;
                } else {
                    if (isset($_SERVER['REQUEST_URI'])) {
                        $sUri = $_SERVER['REQUEST_URI'];
                        if ($n = strpos($sUri, '?')) {
                            $sUri = substr($sUri, 0, $n);
                        }
                        if ($n = strpos($sUri, '#')) {
                            $sUri = substr($sUri, 0, $n);
                        }
                        $sUrl .= '/' . $sUri;
                    }
                    $sUrl .= '/' . $sLocation;
                }
            }
        } else {
            $sUrl = $sLocation;
        }

        return F::File_NormPath($sUrl);
    }

    /**
     * Определение текущего HTTP-протокола для заголовка
     *
     * @return string
     */
    static public function HttpProtocol() {

        $sProtocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
        return $sProtocol;
    }

    /**
     * Получает или устанавливает код ответа HTTP
     *
     * @param null $nResponseCode
     *
     * @return int|null
     */
    static public function HttpResponseCode($nResponseCode = null) {

        if (!is_null($nResponseCode)) {
            $nResponseCode = intval($nResponseCode);
        }
        // function http_response_code() added in PHP 5.4
        if (function_exists('http_response_code')) {
            return http_response_code($nResponseCode);
        } else {
            if (!is_null($nResponseCode)) {
                switch ($nResponseCode) {
                    case 100: $sText = 'Continue'; break;
                    case 101: $sText = 'Switching Protocols'; break;
                    case 200: $sText = 'OK'; break;
                    case 201: $sText = 'Created'; break;
                    case 202: $sText = 'Accepted'; break;
                    case 203: $sText = 'Non-Authoritative Information'; break;
                    case 204: $sText = 'No Content'; break;
                    case 205: $sText = 'Reset Content'; break;
                    case 206: $sText = 'Partial Content'; break;
                    case 300: $sText = 'Multiple Choices'; break;
                    case 301: $sText = 'Moved Permanently'; break;
                    case 302: $sText = 'Moved Temporarily'; break;
                    case 303: $sText = 'See Other'; break;
                    case 304: $sText = 'Not Modified'; break;
                    case 305: $sText = 'Use Proxy'; break;
                    case 400: $sText = 'Bad Request'; break;
                    case 401: $sText = 'Unauthorized'; break;
                    case 402: $sText = 'Payment Required'; break;
                    case 403: $sText = 'Forbidden'; break;
                    case 404: $sText = 'Not Found'; break;
                    case 405: $sText = 'Method Not Allowed'; break;
                    case 406: $sText = 'Not Acceptable'; break;
                    case 407: $sText = 'Proxy Authentication Required'; break;
                    case 408: $sText = 'Request Time-out'; break;
                    case 409: $sText = 'Conflict'; break;
                    case 410: $sText = 'Gone'; break;
                    case 411: $sText = 'Length Required'; break;
                    case 412: $sText = 'Precondition Failed'; break;
                    case 413: $sText = 'Request Entity Too Large'; break;
                    case 414: $sText = 'Request-URI Too Large'; break;
                    case 415: $sText = 'Unsupported Media Type'; break;
                    case 500: $sText = 'Internal Server Error'; break;
                    case 501: $sText = 'Not Implemented'; break;
                    case 502: $sText = 'Bad Gateway'; break;
                    case 503: $sText = 'Service Unavailable'; break;
                    case 504: $sText = 'Gateway Time-out'; break;
                    case 505: $sText = 'HTTP Version not supported'; break;
                    default:
                        exit('Unknown http status code "' . $nResponseCode . '"');
                        break;
                }

                header(static::HttpProtocol() . ' ' . $nResponseCode . ' ' . $sText);

                $GLOBALS['http_response_code'] = $nResponseCode;
            } else {
                $nResponseCode = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
            }

            return $nResponseCode;
        }
    }

    /**
     * @param      $sLocation
     * @param bool $bRealHost
     *
     * @return mixed
     */
    static public function HeaderLocation($sLocation, $bRealHost = false) {

        return HttpLocation($sLocation, $bRealHost);
    }

    /**
     * Переход по заданному адресу
     *
     * Путь в $sLocation может быть как абсолютным, так и относительным.
     * Абсолютный путь определяется по наличию хоста
     *
     * Если задан относительный путь, итоговый URL определяется в зависимости от второго парамтера.
     * Если $bRealHost == false (по умолчанию), то за основу берется root-адрес сайта, который задан в конфигурации.
     * В противном случае основа адреса - это реальный адрес хоста из $_SERVER['SERVER_NAME']
     *
     * @param   string  $sLocation  - адрес перехода (напр., 'http://ya.ru/demo/', '/123.html', 'blog/add/')
     * @param   bool    $bRealHost  - в случае относительной адресации брать адрес хоста из конфига или реальный
     */
    static public function HttpLocation($sLocation, $bRealHost = false) {

        // Приведение адреса к абсолютному виду
        $sUrl = static::RealUrl($sLocation, $bRealHost);

        $aHeaders = array(
            'Expires: Fri, 01 Jan 2010 05:00:00 GMT',
            'Last-Modified: ' . gmdate('D, d M Y H:i:s').' GMT',
            'Cache-Control: no-cache, must-revalidate',
            array('Cache-Control: post-check=0,pre-check=0', false),
            array('Cache-Control: max-age=0', false),
            array('Pragma: no-cache'),
        );

        static::HttpHeader(303, $aHeaders, $sUrl);
    }

    /**
     * Постоянный редирект (с кодом 301)
     *
     * @param      $sLocation
     * @param bool $bRealHost
     */
    static public function HttpRedirect($sLocation, $bRealHost = false) {

        // Приведение адреса к абсолютному виду
        $sUrl = static::RealUrl($sLocation, $bRealHost);

        static::HttpHeader(301, null, $sUrl);
    }

    /**
     * Отправляет HTTP-заголовки и (опционально) адрес для редиректа
     *
     * @param   int         $nHttpStatusCode
     * @param   array|null  $aHeaders
     * @param   string|null $sUrl
     */
    static public function HttpHeader($nHttpStatusCode, $aHeaders = array(), $sUrl = null) {

        // Можем работать с заголовком, только если он еще не отправлялся
        if (!headers_sent()) {
            session_commit();

            // Добавляем HTTP-заголовки
            if ($aHeaders && is_array($aHeaders)) {
                foreach ($aHeaders as $xHeaderParam) {
                    if (is_array($xHeaderParam)) {
                        if (sizeof($xHeaderParam) > 1) {
                            header((string)$xHeaderParam[0], (bool)$xHeaderParam[1]);
                        } else {
                            header((string)$xHeaderParam[0]);
                        }
                    } else {
                        header((string)$xHeaderParam);
                    }
                }
            }
            // Устанавливаем HTTP-код
            F::HttpResponseCode($nHttpStatusCode);
            if ($sUrl) {
                header('Location: ' . $sUrl, true);
            }
            exit;
        } elseif ($sUrl) {
            // Альтернативный редирект, если заголовок уже отправлен
            if (ob_get_level()) {
                @ob_end_clean();
            }
            echo '<!DOCTYPE HTML>
<html>
<head>
<script type="text/javascript">
<!--
location.replace("' . $sUrl . '");
//-->
</script>
<noscript>
<meta http-equiv="Refresh" content="0; URL=' . $sUrl . '">
</noscript>
</head>
<body>
Redirect to <a href="' . $sUrl . '">' . $sUrl . '</a>
</body>
</html>';
        }
        exit;
    }

}

class F extends Func {

}

/***
 * Аналоги ф-ций preg_*, корректно обрабатывающие нелатиницу в UTF-8 при использовании флага PREG_OFFSET_CAPTURE
 */
if (!function_exists('mb_preg_match_all')) {

    function mb_preg_match_fix(
        $bFuncAll, $sPattern, $sSubject, &$aMatches,
        $nFlags = PREG_OFFSET_CAPTURE, $nOffset = 0, $sEncoding = NULL
    ) {
        if (is_null($sEncoding)) {
            $sEncoding = mb_internal_encoding();
        }

        $nOffset = strlen(mb_substr($sSubject, 0, $nOffset, $sEncoding));
        if ($bFuncAll) {
            $bResult = preg_match_all($sPattern, $sSubject, $aMatches, $nFlags, $nOffset);
            if ($bResult && ($nFlags & PREG_OFFSET_CAPTURE)) {
                foreach ($aMatches as &$ha_match) {
                    foreach ($ha_match as &$ha_match) {
                        $ha_match[1] = mb_strlen(substr($sSubject, 0, $ha_match[1]), $sEncoding);
                    }
                }
            }
        } else {
            $bResult = preg_match($sPattern, $sSubject, $aMatches, $nFlags, $nOffset);
            if ($bResult && ($nFlags & PREG_OFFSET_CAPTURE)) {
                foreach ($aMatches as &$ha_match) {
                    $ha_match[1] = mb_strlen(substr($sSubject, 0, $ha_match[1]), $sEncoding);
                }
            }
        }

        return $bResult;
        /*
        if ($ret && ($nFlags & PREG_OFFSET_CAPTURE))
            foreach ($aMatches as &$ha_match)
                foreach ($ha_match as &$ha_match)
                    $ha_match[1] = mb_strlen(substr($sSubject, 0, $ha_match[1]), $sEncoding);
        return $ret;
        */
    }

    function mb_preg_match($sPattern, $sSubject, &$aMatches, $nFlags = PREG_OFFSET_CAPTURE, $nOffset = 0, $sEncoding = NULL) {

        return mb_preg_match_fix(0, $sPattern, $sSubject, $aMatches, $nFlags, $nOffset, $sEncoding);
    }

    function mb_preg_match_all($sPattern, $sSubject, &$aMatches, $nFlags = PREG_OFFSET_CAPTURE, $nOffset = 0, $sEncoding = NULL) {

        return mb_preg_match_fix(1, $sPattern, $sSubject, $aMatches, $nFlags, $nOffset, $sEncoding);
    }

}

F::init();

// EOF