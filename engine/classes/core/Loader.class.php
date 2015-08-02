<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);

class Loader {

    /**
     * @param array $aConfig
     */
    static public function Init($aConfig) {

        // Регистрация автозагрузчика классов
        spl_autoload_register('Loader::Autoload');

        Config::Load($aConfig);
        $sConfigDir = Config::Get('path.dir.config');

        // Load main config
        Config::LoadFromFile($sConfigDir . '/config.php', false);

        // Load additional config files if defined
        $aConfigLoad = F::Str2Array(Config::Get('config_load'));
        if ($aConfigLoad) {
            self::_loadConfigSections($sConfigDir, $aConfigLoad);
        }

        // Includes all *.php files from {path.root.engine}/include/
        $sIncludeDir = Config::Get('path.dir.engine') . '/include/';
        self::_includeAllFiles($sIncludeDir);

        // Load main config level (modules, local & plugins)
        self::_loadConfigFiles($sConfigDir, Config::LEVEL_MAIN);

        // Define app config dir
        $sAppConfigDir = Config::Get('path.dir.app') . '/config/';
        // Ups config level
        Config::ResetLevel(Config::LEVEL_APP);
        // Load application config level (modules, local & plugins)
        self::_loadConfigFiles($sAppConfigDir, Config::LEVEL_APP);

        // Load additional config files (the set could be changed in this point)
        $aConfigLoad = F::Str2Array(Config::Get('config_load'));
        if ($aConfigLoad) {
            self::_loadConfigSections($sAppConfigDir, $aConfigLoad, Config::LEVEL_APP);
        }

        // Load include files of plugins
        self::_loadIncludeFiles(Config::LEVEL_MAIN);
        self::_loadIncludeFiles(Config::LEVEL_APP);


        self::_checkRequiredDirs();

        $aSeekDirClasses = array(
            Config::Get('path.dir.app'),
            Config::Get('path.dir.common'),
            Config::Get('path.dir.engine'),
        );
        Config::Set('path.root.seek', $aSeekDirClasses);

        if (is_null(Config::Get('path.root.subdir'))) {
            if (isset($_SERVER['DOCUMENT_ROOT'])) {
                $sPathSubdir = '/' . F::File_LocalPath(ALTO_DIR, $_SERVER['DOCUMENT_ROOT']);
            } elseif ($iOffset = Config::Get('path.offset_request_url')) {
                $aParts = array_slice(explode('/', F::File_NormPath(ALTO_DIR)), -$iOffset);
                $sPathSubdir = '/' . implode('/', $aParts);
            } else {
                $sPathSubdir = '';
            }
            Config::Set('path.root.subdir', $sPathSubdir);
        }

        // Подгружаем конфиг из файлового кеша, если он есть
        Config::ResetLevel(Config::LEVEL_CUSTOM);
        $aConfig = Config::ReadCustomConfig(null, true);
        if ($aConfig) {
            Config::Load($aConfig, false, null, null, 'custom');
        }

        // Задаем локаль по умолчанию
        F::IncludeLib('UserLocale/UserLocale.class.php');
        // Устанавливаем признак того, является ли сайт многоязычным
        $aLangsAllow = (array)Config::Get('lang.allow');
        if (sizeof($aLangsAllow) > 1) {
            UserLocale::initLocales($aLangsAllow);
            Config::Set('lang.multilang', true);
        } else {
            Config::Set('lang.multilang', false);
        }
        UserLocale::setLocale(
            Config::Get('lang.current'),
            array('local' => Config::Get('i18n.locale'), 'timezone' => Config::Get('i18n.timezone'))
        );
        Config::Set('i18n', UserLocale::getLocale());

        F::IncludeFile((Config::Get('path.dir.engine') . '/classes/core/Engine.class.php'));
    }

    /**
     * @param string $sDirInclude
     */
    static protected function _includeAllFiles($sDirInclude) {

        $aIncludeFiles = glob($sDirInclude . '*.php');
        if ($aIncludeFiles) {
            foreach ($aIncludeFiles as $sPath) {
                F::IncludeFile($sPath);
            }
        }
    }

    /**
     * @param string $sConfigDir
     * @param int    $nConfigLevel
     */
    static protected function _loadConfigFiles($sConfigDir, $nConfigLevel) {

        // * Загружаем конфиги модулей вида /config/modules/[module_name]/config.php
        $sDirConfig = $sConfigDir . '/modules/';
        $aFiles = glob($sDirConfig . '*/config.php');
        if ($aFiles) {
            foreach ($aFiles as $sConfigFile) {
                $sDirModule = basename(dirname($sConfigFile));
                $aConfig = F::IncludeFile($sConfigFile, true, true);
                if (!empty($aConfig) && is_array($aConfig)) {
                    $sKey = 'module.' . $sDirModule;
                    Config::Load(array($sKey => $aConfig), false, null, $nConfigLevel, $sConfigFile);
                }
            }
        }

        /*
         * Подгружаем файлы локального конфига
         */
        $sConfigFile = $sConfigDir . '/config.local.php';
        if (F::File_Exists($sConfigFile)) {
            if ($aConfig = F::File_IncludeFile($sConfigFile, true)) {
                Config::Set($aConfig, false, null, $nConfigLevel, $sConfigFile);
            }
        }

        /*
         * Загружает конфиг-файлы плагинов вида /plugins/[plugin_name]/config/*.php
         * и include-файлы вида /plugins/[plugin_name]/include/*.php
         */
        $sPluginsDir = F::GetPluginsDir($nConfigLevel == Config::LEVEL_APP);
        if ($aPluginsList = F::GetPluginsList(false, false)) {
            foreach ($aPluginsList as $sPlugin => $aPluginInfo) {
                // Paths to dirs of plugins
                Config::Set('path.dir.plugin.' . $aPluginInfo['id'], $aPluginInfo['path']);

                // Загружаем все конфиг-файлы плагина
                $aConfigFiles = glob($sPluginsDir . '/' . $aPluginInfo['dirname'] . '/config/*.php');
                if ($aConfigFiles) {
                    // move config.php to begin of array
                    if (sizeof($aConfigFiles) > 1) {
                        $sConfigFile = $sPluginsDir . '/' . $aPluginInfo['dirname'] . '/config/config.php';
                        $iIndex = array_search($sConfigFile, $aConfigFiles);
                        if ($iIndex) {
                            $aConfigFiles = array_merge(array_splice($aConfigFiles, $iIndex, 1), $aConfigFiles);
                        }
                    }

                    foreach ($aConfigFiles as $sConfigFile) {
                        $aConfig = F::IncludeFile($sConfigFile, true, true);
                        if (!empty($aConfig) && is_array($aConfig)) {
                            // Если конфиг этого плагина пуст, то загружаем массив целиком
                            $sKey = 'plugin.' . $sPlugin;
                            if (!Config::isExist($sKey)) {
                                Config::Set($sKey, $aConfig, null, $nConfigLevel, $sConfigFile);
                            } else {
                                // Если уже существуют привязанные к плагину ключи,
                                // то сливаем старые и новое значения ассоциативно-комбинированно
                                /** @see AltoFunc_Array::MergeCombo() */
                                Config::Set($sKey, F::Array_MergeCombo(Config::Get($sKey), $aConfig), null, $nConfigLevel, $sConfigFile);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Загружает include-файлы вида /plugins/[plugin_name]/include/*.php
     *
     * @param int $nConfigLevel
     */
    static protected function _loadIncludeFiles($nConfigLevel) {

        $sPluginsDir = F::GetPluginsDir($nConfigLevel == Config::LEVEL_APP);
        if ($aPluginsList = F::GetPluginsList(false, false)) {
            //$aPluginsList = array_map('trim', $aPluginsList);
            foreach ($aPluginsList as $sPlugin => $aPluginInfo) {
                // Подключаем include-файлы плагина
                $aIncludeFiles = glob($sPluginsDir . '/' . $aPluginInfo['dirname'] . '/include/*.php');
                if ($aIncludeFiles) {
                    foreach ($aIncludeFiles as $sPath) {
                        F::IncludeFile($sPath);
                    }
                }
            }
        }
    }

    /**
     * @param string $sConfigDir
     * @param array  $aConfigSections
     * @param int    $nConfigLevel
     */
    protected static function _loadConfigSections($sConfigDir, $aConfigSections, $nConfigLevel = 0) {

        foreach ($aConfigSections as $sName) {
            $sFile = $sConfigDir . '/' . $sName . '.php';
            if (F::File_Exists($sFile)) {
                self::_loadSectionFile($sFile, $sName, $nConfigLevel);
            }

            $sFile = $sConfigDir . '/' . $sName . '.local.php';
            if (F::File_Exists($sFile)) {
                self::_loadSectionFile($sFile, $sName, $nConfigLevel);
            }
        }
    }

    /**
     * Load subconfig file
     *
     * @param string $sFile
     * @param string $sName
     * @param int    $nConfigLevel
     */
    static protected function _loadSectionFile($sFile, $sName, $nConfigLevel = 0) {

        if (F::File_Exists($sFile)) {
            $aCfg = F::File_IncludeFile($sFile, true, true);
            if ($aCfg) {
                if (isset($aCfg[$sName])) {
                    $aConfig = $aCfg[$sName];
                } else {
                    $aConfig = $aCfg;
                }
                Config::Load(array($sName => $aConfig), false, null, $nConfigLevel, $sFile);
            }
        }
    }

    /**
     * Check required dirs
     */
    static protected function _checkRequiredDirs() {

        $sDir = Config::Get('path.dir.app');
        if (!$sDir) {
            die('Application directory not defined');
        } elseif (!F::File_CheckDir($sDir, false)) {
            die('Application directory "' . F::File_LocalDir(Config::Get('path.dir.app')) . '" is not exist');
        }

        $sDir = Config::Get('path.tmp.dir');
        if (!$sDir) {
            die('Directory for temporary files not defined');
        } elseif (!F::File_CheckDir($sDir, true)) {
            die('Directory for temporary files "' . $sDir . '" does not exist');
        } elseif (!is_writeable($sDir)) {
            die('Directory for temporary files "' . F::File_LocalDir($sDir) . '" is not writeable');
        }

        $sDir = Config::Get('path.runtime.dir');
        if (!$sDir) {
            die('Directory for runtime files not defined');
        } elseif (!F::File_CheckDir($sDir, true)) {
            die('Directory for runtime files "' . $sDir . '" does not exist');
        } elseif (!is_writeable($sDir)) {
            die('Directory for runtime files "' . F::File_LocalDir($sDir) . '" is not writeable');
        }
    }

    /**
     * Автоопределение класса или файла экшена
     *
     * @param   string $sAction
     * @param   string $sEvent
     * @param   bool   $bFullPath
     *
     * @return  string|null
     */
    static public function SeekActionClass($sAction, $sEvent = null, $bFullPath = false) {

        $bOk = false;
        $sActionClass = '';
        $sFileName = 'Action' . ucfirst($sAction) . '.class.php';

        // Сначала проверяем файл экшена среди стандартных
        $aSeekDirs = array(Config::Get('path.dir.app'), Config::Get('path.dir.common'));
        if ($sActionFile = F::File_Exists('/classes/actions/' . $sFileName, $aSeekDirs)) {
            $sActionClass = 'Action' . ucfirst($sAction);
            $bOk = true;
        } else {
            // Если нет, то проверяем файл экшена среди плагинов
            $aPlugins = F::GetPluginsList(false, false);
            foreach ($aPlugins as $sPlugin => $aPluginInfo) {
                if ($sActionFile = F::File_Exists('plugins/' . $aPluginInfo['dirname'] . '/classes/actions/' . $sFileName, $aSeekDirs)) {
                    $sActionClass = 'Plugin' . F::StrCamelize($sPlugin) . '_Action' . ucfirst($sAction);
                    $bOk = true;
                    break;
                }
            }
        }
        if ($bOk) {
            return $bFullPath ? $sActionFile : $sActionClass;
        }
        return null;
    }

    /**
     * @param string $sFile
     * @param string $sCheckClassname
     *
     * @return bool|mixed
     */
    static protected function _includeFile($sFile, $sCheckClassname = null) {

        if (class_exists('F', false)) {
            $xResult = F::IncludeFile($sFile);
        } else {
            $xResult = include_once $sFile;
        }
        if ($sCheckClassname) {
            return class_exists($sCheckClassname, false);
        }
        return $xResult;
    }

    /**
     * Автозагрузка классов
     *
     * @param string $sClassName    Название класса
     *
     * @return bool
     */
    static public function Autoload($sClassName) {

        if (Config::Get('classes')) {
            if ($sParentClass = Config::Get('classes.alias.' . $sClassName)) {
                return self::_classAlias($sParentClass, $sClassName);
            }
            if (self::_autoloadDefinedClass($sClassName)) {
                return true;
            }
        }

        if (class_exists('Engine', false) && (E::GetStage() >= E::STAGE_INIT)) {
            $aInfo = E::GetClassInfo($sClassName, E::CI_CLASSPATH | E::CI_INHERIT);
            if ($aInfo[E::CI_INHERIT]) {
                $sInheritClass = $aInfo[E::CI_INHERIT];
                $sParentClass = E::ModulePlugin()->GetParentInherit($sInheritClass);
                return self::_classAlias($sParentClass, $sClassName);
            } elseif ($aInfo[E::CI_CLASSPATH]) {
                return self::_includeFile($aInfo[E::CI_CLASSPATH], $sClassName);
            }
        }
        if (self::_autoloadPSR($sClassName)) {
            return true;
        }
        return false;
    }

    /**
     * Try to load class using config info
     *
     * @param string $sClassName
     *
     * @return bool
     */
    static protected function _autoloadDefinedClass($sClassName) {

        if ($sFile = Config::Get('classes.class.' . $sClassName)) {
            // defined file name for the class
            if (is_array($sFile)) {
                $sFile = isset($sFile['file']) ? $sFile['file'] : null;
            }
            if ($sFile) {
                return self::_includeFile($sFile, $sClassName);
            }
        }
        // May be Namespace_Package or Namespace\Package
        if (strpos($sClassName, '\\') || strpos($sClassName, '_')) {
            $aPrefixes = Config::Get('classes.prefix');
            foreach ($aPrefixes as $sPrefix => $aOptions) {
                if (strpos($sClassName, $sPrefix) === 0) {
                    // defined prefix for vendor/library
                    if (is_array($aOptions)) {
                        if (isset($aOptions['path'])) {
                            $sPath = $aOptions['path'];
                        } else {
                            $sPath = '';
                        }
                    } else {
                        $sPath = $aOptions;
                    }
                    if ($sPath) {
                        if (isset($aOptions['classmap'][$sClassName])) {
                            $sFile = $sPath . '/' . $aOptions['classmap'][$sClassName];
                            return self::_includeFile($sFile, $sClassName);
                        }
                        return self::_autoloadPSR($sClassName, $sPath);
                    }
                }
            }
        }
        return false;
    }

    static protected $_aFailedClasses = array();

    static protected function _autoloadPSR($sClassName, $xPath = null) {

        return self::_autoloadPSR4($sClassName) || self::_autoloadPSR0($sClassName, $xPath);
    }

    /**
     * Try to load class using PRS-0 naming standard
     *
     * @param string       $sClassName
     * @param string|array $xPath
     *
     * @return bool
     */
    static protected function _autoloadPSR0($sClassName, $xPath = null) {

        if (!$xPath) {
            $xPath = C::Get('path.dir.libs');
        }

        $sCheckKey = serialize(array($sClassName, $xPath));
        if (!isset(self::$_aFailedClasses[$sCheckKey])) {
            if (strpos($sClassName, '\\')) {
                // Namespaces
                $sFileName = str_replace('\\', DIRECTORY_SEPARATOR, $sClassName);
            } elseif (strpos($sClassName, '_')) {
                // Old style with '_'
                $sFileName = str_replace('_', DIRECTORY_SEPARATOR, $sClassName);
            } else {
                $sFileName = $sClassName . DIRECTORY_SEPARATOR . $sClassName;
            }
            if ($sFile = F::File_Exists($sFileName . '.php', $xPath)) {
                return self::_includeFile($sFile, $sClassName);
            } elseif ($sFile = F::File_Exists($sFileName . '.class.php', $xPath)) {
                return self::_includeFile($sFile, $sClassName);
            }
        }
        self::$_aFailedClasses[$sCheckKey] = false;
        return false;
    }

    /**
     * Try load class using PSR-4 standards
     * Used code from http://www.php-fig.org/psr/psr-4/examples/
     *
     * @param string $sClassName
     *
     * @return bool
     */
    static protected function _autoloadPSR4($sClassName) {

        // An associative array where the key is a namespace prefix and the value
        // is an array of base directories for classes in that namespace.
        $aVendorNamespaces = C::Get('classes.namespace');
        if (!strpos($sClassName, '\\') || !$aVendorNamespaces) {
            return false;

        }
        // the current namespace prefix
        $sPrefix = $sClassName;

        // work backwards through the namespace names of the fully-qualified
        // class name to find a mapped file name
        while (false !== $iPos = strrpos($sPrefix, '\\')) {

            // seeking namespace prefix
            $sPrefix = substr($sClassName, 0, $iPos);

            // the rest is the relative class name
            $sRelativeClass = substr($sClassName, $iPos + 1);
            $sFileName = str_replace('\\', DIRECTORY_SEPARATOR, $sRelativeClass) . '.php';

            // try to load a mapped file for the prefix and relative class
            if (isset($aVendorNamespaces[$sPrefix])) {
                if ($sFile = F::File_Exists($sFileName, $aVendorNamespaces[$sPrefix])) {
                    return self::_includeFile($sFile, $sClassName);
                }
            }
        }

        // файл так и не был найден
        return false;
    }

    /**
     * @var array Array of class aliases
     */
    static protected $_aClassAliases = array();

    /**
     * Creates an alias for a class
     *
     * @param string $sOriginal
     * @param string $sAlias
     * @param bool   $bAutoload
     *
     * @return bool
     */
    static protected function _classAlias($sOriginal, $sAlias, $bAutoload = TRUE) {

        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $bResult = class_alias($sOriginal, $sAlias, $bAutoload);
        } else {
            $bResult = class_alias($sOriginal, $sAlias);
        }

        if (defined('DEBUG') && DEBUG) {
            self::$_aClassAliases[$sAlias] = array(
                'original' => $sOriginal,
                'autoload' => $bAutoload,
                'result' => $bResult,
            );
        }

        return $bResult;
    }

    /**
     * Returns of class aliases
     *
     * @return array
     */
    static public function GetAliases() {

        return self::$_aClassAliases;
    }
}

// EOF