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

        /*
         * Инклудим все *.php файлы из каталога {path.root.engine}/include/ - это файлы ядра
         */
        $sDirInclude = Config::Get('path.dir.engine') . '/include/';
        self::_includeAllFiles($sDirInclude);

        // Load main config level
        self::_loadConfigFiles($sConfigDir, Config::LEVEL_MAIN);

        // Load application config level
        $sAppConfigDir = Config::Get('path.dir.app') . '/config/';
        Config::ResetLevel(Config::LEVEL_APP);
        if ($aConfigLoad) {
            self::_loadConfigSections($sAppConfigDir, $aConfigLoad, Config::LEVEL_APP);
        }
        self::_loadConfigFiles($sAppConfigDir, Config::LEVEL_APP);

        self::_checkRequiredDirs();

        $aSeekDirClasses = array(
            Config::Get('path.dir.app'),
            Config::Get('path.dir.common'),
            Config::Get('path.dir.engine'),
        );
        Config::Set('path.root.seek', $aSeekDirClasses);

        // Подгружаем конфиг из файлового кеша, если он есть
        Config::ResetLevel(Config::LEVEL_CUSTOM);
        $aConfig = Config::ReadCustomConfig(null, true);
        if ($aConfig) {
            Config::Load($aConfig, false);
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

    static protected function _includeAllFiles($sDirInclude) {

        $aIncludeFiles = glob($sDirInclude . '*.php');
        if ($aIncludeFiles) {
            foreach ($aIncludeFiles as $sPath) {
                F::IncludeFile($sPath);
            }
        }
    }

    static protected function _loadConfigFiles($sConfigDir, $nConfigLevel) {

        // * Загружаем конфиги модулей вида /config/modules/[module_name]/config.php
        $sDirConfig = $sConfigDir . '/modules/';
        $aFiles = glob($sDirConfig . '*/config.php');
        if ($aFiles) {
            foreach ($aFiles as $sFileConfig) {
                $sDirModule = basename(dirname($sFileConfig));
                $aConfig = F::IncludeFile($sFileConfig, true, true);
                if (!empty($aConfig) && is_array($aConfig)) {
                    $sKey = 'module.' . $sDirModule;
                    Config::Load(array($sKey => $aConfig), false, null, $nConfigLevel);
                }
            }
        }

        /**
         * Ищет routes-конфиги модулей вида /config/modules/[module_name]/config.route.php и объединяет их с текущим
         *
         * @see Router.class.php
         */
        $sDirConfig = $sConfigDir . '/modules/';
        $aFiles = glob($sDirConfig . '*/config.route.php');
        if ($aFiles) {
            foreach ($aFiles as $sFileConfig) {
                $aConfig = F::IncludeFile($sFileConfig, true, true);
                if (!empty($aConfig) && is_array($aConfig)) {
                    $sKey = 'router.' . $sDirModule;
                    Config::Load(array($sKey => $aConfig), false, null, $nConfigLevel);
                }
            }
        }

        /*
         * LS-compatible
         * Подгружаем файлы локального и продакшн-конфига
         */
        $sFile = $sConfigDir . '/config.local.php';
        if (F::File_Exists($sFile)) {
            if ($aConfig = F::File_IncludeFile($sFile, true, Config::Get())) {
                Config::Load($aConfig, true, null, $nConfigLevel);
            }
        }

        /*
         * Загружает конфиг-файлы плагинов вида /plugins/[plugin_name]/config/*.php
         * и include-файлы вида /plugins/[plugin_name]/include/*.php
         */
        $sPluginsDir = F::GetPluginsDir($nConfigLevel == Config::LEVEL_APP);
        if ($aPluginsList = F::GetPluginsList()) {
            $aPluginsList = array_map('trim', $aPluginsList);
            foreach ($aPluginsList as $sPlugin) {
                // Загружаем все конфиг-файлы плагина
                $aConfigFiles = glob($sPluginsDir . '/' . $sPlugin . '/config/*.php');
                if ($aConfigFiles) {
                    foreach ($aConfigFiles as $sPath) {
                        $aConfig = F::IncludeFile($sPath, true, true);
                        if (!empty($aConfig) && is_array($aConfig)) {
                            // Если конфиг этого плагина пуст, то загружаем массив целиком
                            $sKey = 'plugin.' . $sPlugin;
                            if (!Config::isExist($sKey)) {
                                Config::Set($sKey, $aConfig, null, $nConfigLevel);
                            } else {
                                // Если уже существую привязанные к плагину ключи,
                                // то сливаем старые и новое значения ассоциативно
                                Config::Set($sKey, F::Array_MergeCombo(Config::Get($sKey), $aConfig), null, $nConfigLevel);
                            }
                        }
                    }
                }

                // Подключаем include-файлы плагина
                $aIncludeFiles = glob($sPluginsDir . '/' . $sPlugin . '/include/*.php');
                if ($aIncludeFiles) {
                    foreach ($aIncludeFiles as $sPath) {
                        F::IncludeFile($sPath);
                    }
                }
            }
        }
    }

    /**
     * @param $sConfigDir
     * @param $aConfigSections
     * @param $nConfigLevel
     */
    protected static function _loadConfigSections($sConfigDir, $aConfigSections, $nConfigLevel = 0) {

        foreach ($aConfigSections as $sName) {
            $sFile = $sConfigDir . '/' . $sName . '.php';
            self::_loadSectionFile($sFile, $sName, $nConfigLevel);

            $sFile = $sConfigDir . '/' . $sName . '.local.php';
            if (F::File_Exists($sFile)) {
                self::_loadSectionFile($sFile, $sName, $nConfigLevel);
            }
        }
    }

    /**
     * Load subconfig file
     *
     * @param $sFile
     * @param $sName
     * @param $nConfigLevel
     */
    static protected function _loadSectionFile($sFile, $sName, $nConfigLevel = 0) {

        $aConfig = array();
        if (F::File_Exists($sFile)) {
            $aCfg = F::File_IncludeFile($sFile, true, true);
            if ($aCfg) {
                if (isset($aCfg[$sName])) {
                    $aConfig = $aCfg[$sName];
                } else {
                    $aConfig = $aCfg;
                }
            }
        }
        Config::Load(array($sName => $aConfig), false, null, $nConfigLevel);
    }

    static protected function _checkRequiredDirs() {

        if (!F::File_CheckDir(Config::Get('path.dir.app'), false)) {
            die('Application folder "' . F::File_LocalDir(Config::Get('path.dir.app')) . '" does not exist');
        }
        if (!F::File_CheckDir(Config::Get('path.tmp.dir'), false)) {
            die('Required folder "' . F::File_LocalDir(Config::Get('path.tmp.dir')) . '" does not exist');
        }
        if (!F::File_CheckDir(Config::Get('path.runtime.dir'), false)) {
            die('Required folder "' . F::File_LocalDir(Config::Get('path.runtime.dir')) . '" does not exist');
        }
    }

    /**
     * Автоопределение класса или файла экшена
     *
     * @param   string      $sAction
     * @param   string|null $sEvent
     * @param   bool        $bFullPath
     *
     * @return  string
     */
    static public function SeekActionClass($sAction, $sEvent = null, $bFullPath = false) {

        $bOk = false;
        $sFileName = 'Action' . ucfirst($sAction) . '.class.php';

        // Сначала проверяем файл экшена среди стандартных
        $aSeekDirs = array(Config::Get('path.dir.app'), Config::Get('path.dir.common'));
        if ($sActionFile = F::File_Exists('/classes/actions/' . $sFileName, $aSeekDirs)) {
            $sActionClass = 'Action' . ucfirst($sAction);
            $bOk = true;
        } else {
            // Если нет, то проверяем файл экшена среди плагинов
            $aPlugins = F::GetPluginsList();
            foreach ($aPlugins as $sPlugin) {
                if ($sActionFile = F::File_Exists('plugins/' . $sPlugin . '/classes/actions/' . $sFileName, $aSeekDirs)
                ) {
                    $sActionClass = 'Plugin' . ucfirst($sPlugin) . '_Action' . ucfirst($sAction);
                    $bOk = true;
                    break;
                }
            }
        }
        if ($bOk) {
            return $bFullPath ? $sActionFile : $sActionClass;
        }
    }

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

        if (Config::Get('classes') && self::_autoloadDefinedClass($sClassName)) {
            return true;
        }
        if (class_exists('Engine', false) && (Engine::GetStage() >= Engine::STAGE_INIT)) {
            $aInfo = Engine::GetClassInfo($sClassName, Engine::CI_CLASSPATH | Engine::CI_INHERIT);
            if ($aInfo[Engine::CI_INHERIT]) {
                $sInheritClass = $aInfo[Engine::CI_INHERIT];
                $sParentClass = Engine::getInstance()->Plugin_GetParentInherit($sInheritClass);
                if (!class_alias($sParentClass, $sClassName)) {
                    return false;
                } else {
                    return true;
                }
            } elseif ($aInfo[Engine::CI_CLASSPATH]) {
                return self::_includeFile($aInfo[Engine::CI_CLASSPATH], $sClassName);
            }
        }
        if (self::_autoloadPSR0($sClassName)) {
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
                        return self::_autoloadPSR0($sClassName, $sPath);
                    }
                }
            }
        }
        return false;
    }

    static protected $_aFailedClasses = array();

    /**
     * Try to load class using PRS-0 naming standard
     *
     * @param string       $sClassName
     * @param string|array $sPath
     *
     * @return bool
     */
    static protected function _autoloadPSR0($sClassName, $sPath = null) {

        if (!$sPath) {
            $sPath = Config::Get('path.dir.libs');
        }

        $sCheckKey = serialize(array($sClassName, $sPath));
        if (!isset(self::$_aFailedClasses[$sCheckKey])) {
            if (strpos($sClassName, '\\')) {
                // Namespaces
                $sClassName = str_replace('\\', DIRECTORY_SEPARATOR, $sClassName);
            } elseif (strpos($sClassName, '_')) {
                // Old style with '_'
                $sClassName = str_replace('_', DIRECTORY_SEPARATOR, $sClassName);
            } else {
                return false;
            }
            if ($sFile = F::File_Exists($sClassName . '.php', $sPath)) {
                return self::_includeFile($sFile, $sClassName);
            } elseif ($sFile = F::File_Exists($sClassName . '.class.php', $sPath)) {
                return self::_includeFile($sFile, $sClassName);
            }
        }
        self::$_aFailedClasses[$sCheckKey] = false;
        return false;
    }

}

// EOF