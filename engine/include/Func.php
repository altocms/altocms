<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
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
        self::_loadExtension('File');
        register_shutdown_function('F::done');
        set_error_handler('F::_errorHandler');
        set_exception_handler('F::_exceptionHandler');
    }

    static public function done() {
        if ($aError = error_get_last()) {
            //while (ob_get_level()) ob_end_clean();
            self::_errorHandler($aError['type'], $aError['message'], $aError['file'], $aError['line']);
        }
    }

    /**
     * @return string
     */
    static public function _errorLogFile() {
        return self::_getConfig('sys.logs.error_file', self::ERROR_LOG);
    }

    /**
     * @return bool
     */
    static public function _errorLogExtInfo() {
        return (bool)self::_getConfig('sys.logs.error_extinfo', true);
    }

    static public function _errorLog($sError) {
        $sText = '';
        if (self::_errorLogExtInfo() && isset($_SERVER) && is_array($_SERVER)) {
            foreach ($_SERVER as $sKey => $sVal) {
                if (!in_array($sKey, array('PATH', 'SystemRoot', 'COMSPEC', 'PATHEXT', 'WINDIR')))
                    $sText .= "  _SERVER['$sKey']=$sVal\n";
            }
            $sText = "$sError\n---\n$sText\n---\n";
        } else {
            $sText = $sError;
        }

        if (!self::_log($sText, self::_errorLogFile())) {
            // Если не получилось вывести в лог-файл, то выводим ошибку на экран
            echo $sError;
        }
        if (class_exists('ModuleLogger', false) || Loader::Autoload('ModuleLogger')) {
            // Если загружен модуль Logger, то логгируем ошибку с его помощью
            Engine::getInstance()->Logger_Dump(self::_errorLogFile(), $sText);
        } elseif (class_exists('Config', false)) {
            // Если логгера нет, но есть конфиг, то самостоятельно пишем в файл
            $sFile = Config::Get('sys.logs.dir') . self::_errorLogFile();
            $sText = '[' . date('Y-m-d H:i:s') . ']' . "\n" . $sText;
            F::File_PutContents($sFile, $sText, FILE_APPEND | LOCK_EX);
        } else {
            // В противном случае выводим ошибку на экран
            echo $sError;
        }
    }

    static public function _log($sText, $sLogFile) {
        if (class_exists('ModuleLogger', false) || Loader::Autoload('ModuleLogger')) {
            // Если загружен модуль Logger, то логгируем ошибку с его помощью
            return E::Logger_Dump($sLogFile, $sText);
        } elseif (class_exists('Config', false)) {
            // Если логгера нет, но есть конфиг, то самостоятельно пишем в файл
            $sFile = Config::Get('sys.logs.dir') . $sLogFile;
            $sText = '[' . date('Y-m-d H:i:s') . ']' . "\n" . $sText;
            return F::File_PutContents($sFile, $sText, FILE_APPEND | LOCK_EX);
        }
        return false;
    }

    static public function _errorDisplay($sError) {
        if (!self::AjaxRequest())
            echo '<span style="display:none;">"></span><br/></div>' . $sError . '<br/>See details in ' . self::_errorLogFile();
    }

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
            self::_errorDisplay("{$aErrors[$nErrNo]} [$nErrNo] $sErrMsg");
            self::_errorLog("{$aErrors[$nErrNo]} [$nErrNo] $sErrMsg ($sErrFile on line $nErrLine)");
        }

        /* Don't execute PHP internal error handler */
        return true;
    }

    static public function _exceptionHandler($oException) {
        self::_errorDisplay('Exception: ' . $oException->getMessage());
        $sLogMsg = 'Exception: ' . $oException->getMessage();
        if (property_exists($oException, 'sAdditionalInfo')) {
            $sLogMsg .= "\n" . $oException->sAdditionalInfo;
        }
        self::_errorLog($sLogMsg);
    }


    static public function __callStatic($sName, $aArguments) {
        if ($nPos = strpos($sName, '_')) {
            $sExtension = substr($sName, 0, $nPos);
            $sMethod = substr($sName, $nPos + 1);
        } else {
            $sExtension = 'Main';
            $sMethod = $sName;
        }
        if (!isset(self::$aExtsions[$sExtension])) {
            self::_loadExtension($sExtension);
        }
        if (isset(self::$aExtsions[$sExtension]) && method_exists(self::$aExtsions[$sExtension], $sMethod)) {
            return call_user_func_array(self::$aExtsions[$sExtension] . '::' . $sMethod, $aArguments);
        }

        // Function not found
        $aCaller = self::_Caller(2);
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
        self::_FatalError($sMsg);
    }

    static protected function _loadExtension($sExtension) {
        if (!isset(self::$aExtsions[$sExtension])) {
            // сначала проверяем кастомные функции
            if (is_file($sFile = dirname(dirname(__DIR__)) . '/include/functions/' . $sExtension . '.php')) {
                self::IncludeFile($sFile);
                self::$aExtsions[$sExtension] = 'AppFunc_' . $sExtension;
            } elseif (is_file($sFile = __DIR__ . '/functions/' . $sExtension . '.php')) {
                self::IncludeFile($sFile);
                self::$aExtsions[$sExtension] = 'AltoFunc_' . $sExtension;
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

    static protected function _Caller($nOffset = 1) {
        $aData = self::_CallStack($nOffset + 1, 1);
        if (sizeof($aData)) {
            return ($aData[0]);
        }
    }

    static protected function _CallStack($nOffset = 1, $nLength = null) {
        $aStack = array_slice(debug_backtrace(), $nOffset, $nLength);
        return $aStack;
    }

    static public function _getConfig($sParam, $xDefault = null) {
        if (class_exists('Config', false)) {
            $xResult = Config::Get($sParam);
        } else {
            $xResult = $xDefault;
        }
        return $xResult;
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
        if ($sDir == '.' || $sDir == '..') {
            $aCaller = self::_Caller();
            if (isset($aCaller['file'])) {
                $sFile = dirname($aCaller['file']) . '/' . $sFile;
            }
        }
        if (isset(self::$aExtsions['File'])) {
            return call_user_func_array(self::$aExtsions['File'] . '::IncludeFile', array($sFile, $bOnce, $bConfig));
        } else {
            if ($bOnce) {
                return include($sFile);
            } else {
                return include_once($sFile);
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
            return self::IncludeFile(Config::Get('path.dir.lib') . 'external/' . $sFile, $bOnce);
        } else {
            return self::IncludeFile(dirname(__DIR__) . '/lib/external/' . $sFile, $bOnce);
        }
    }

    static public function GetPluginsDir() {
        return F::File_RootDir() . 'plugins/';
    }
    /**
     * Получить список плагинов
     *
     * @param   bool    $bAll   - все плагины (иначе - только активные)
     * @return  array
     */
    static public function GetPluginsList($bAll = false) {
        $sPluginsDir = self::GetPluginsDir();
        if (class_exists('Config', false)) {
            $sPluginsListFile = $sPluginsDir . Config::Get('sys.plugins.activation_file');
        } else {
            $sPluginsListFile = $sPluginsDir . 'plugins.dat';
        }
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
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
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
     * Переход по заданному адресу
     *
     * Путь в $sLocation может быть как абсолютным, так и относительным.
     * Абсолютный путь определяется по наличию хоста
     *
     * Если задан относительный путь, итоговый URL определяется в зависимости от второго парамтера.
     * Если $bRealUrl == false (по умолчанию), то за основу берется root-адрес сайта, который задан в конфигурации.
     * В противном случае основа адреса - это реальный адрес хоста из $_SERVER['SERVER_NAME']
     *
     * @param   string  $sLocation  - адрес перехода (напр., 'http://ya.ru/demo/', '/123.html', 'blog/add/')
     * @param   bool    $bRealUrl   - в случае относительной адресации брать адрес хоста из конфига или реальный
     */
    static public function HeaderLocation($sLocation, $bRealUrl = false) {
        $sProtocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
        $aParts = parse_url($sLocation);
        if (!isset($aParts['host'])) {
            if (!$bRealUrl) {
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
        $sUrl = F::File_NormPath($sUrl);
        if (!headers_sent()) {
            session_commit();

            header('Expires: Fri, 01 Jan 2010 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s').' GMT');
            header('Cache-Control: no-cache, must-revalidate');
            header('Cache-Control: post-check=0,pre-check=0', false);
            header('Cache-Control: max-age=0', false);
            header('Pragma: no-cache');

            header($sProtocol . ' 303 See Other');

            header('Location: ' . $sUrl, true);
            exit;
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
        exit;
    }

}

class F extends Func {

}

/***
 * Аналоги ф-ций preg_*, корректно обрабатывающие нелатиницу в UTF-8 при использовании флага PREG_OFFSET_CAPTURE
 */
if (!function_exists('mb_preg_match_all')) {
    function mb_preg_match_fix($bFuncAll, $sPattern, $sSubject, &$aMatches,
                               $nFlags = PREG_OFFSET_CAPTURE, $nOffset = 0, $sEncoding = NULL)
    {
        if (is_null($sEncoding))
            $sEncoding = mb_internal_encoding();

        $nOffset = strlen(mb_substr($sSubject, 0, $nOffset, $sEncoding));
        if ($bFuncAll) {
            $ret = preg_match_all($sPattern, $sSubject, $aMatches, $nFlags, $nOffset);
            if ($ret && ($nFlags & PREG_OFFSET_CAPTURE))
                foreach ($aMatches as &$ha_match)
                    foreach ($ha_match as &$ha_match)
                        $ha_match[1] = mb_strlen(substr($sSubject, 0, $ha_match[1]), $sEncoding);
        } else {
            $ret = preg_match($sPattern, $sSubject, $aMatches, $nFlags, $nOffset);
            if ($ret && ($nFlags & PREG_OFFSET_CAPTURE))
                foreach ($aMatches as &$ha_match)
                    $ha_match[1] = mb_strlen(substr($sSubject, 0, $ha_match[1]), $sEncoding);
        }

        return $ret;
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