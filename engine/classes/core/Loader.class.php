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
            foreach ($aConfigLoad as $sName) {
                $sFile = $sConfigDir . '/' . $sName . '.php';
                self::_loadConfigSection($sFile, $sName);

                $sFile = $sConfigDir . '/' . $sName . '.local.php';
                if (F::File_Exists($sFile)) {
                    self::_loadConfigSection($sFile, $sName);
                }
            }
        }

        /*
         * Загружает конфиги модулей вида /config/modules/[module_name]/config.php
         */
        $sDirConfig = $sConfigDir . '/modules/';
        $aFiles = glob($sDirConfig . '*/config.php');
        if ($aFiles) {
            foreach ($aFiles as $sFileConfig) {
                $sDirModule = basename(dirname($sFileConfig));
                $aConfig = F::IncludeFile($sFileConfig, true, true);
                if (!empty($aConfig) && is_array($aConfig)) {
                    // Если конфиг этого модуля пуст, то загружаем массив целиком
                    $sKey = 'module.' . $sDirModule;
                    if (!Config::isExist($sKey)) {
                        Config::Set($sKey, $aConfig);
                    } else {
                        // Если уже существуют привязанные к модулю ключи,
                        // то сливаем старые и новое значения ассоциативно
                        Config::Set($sKey, F::Array_Merge(Config::Get($sKey), $aConfig));
                    }
                }
            }
        }

        /*
         * Инклудим все *.php файлы из каталога {path.root.engine}/include/ - это файлы ядра
         */
        $sDirInclude = Config::Get('path.dir.engine') . '/include/';
        $aIncludeFiles = glob($sDirInclude . '*.php');
        if ($aIncludeFiles) {
            foreach ($aIncludeFiles as $sPath) {
                F::IncludeFile($sPath);
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
                    // Если конфиг этого модуля пуст, то загружаем массив целиком
                    $sKey = 'router';
                    if (!Config::isExist($sKey)) {
                        Config::Set($sKey, $aConfig);
                    } else {
                        // Если уже существую привязанные к модулю ключи,
                        // то сливаем старые и новое значения ассоциативно
                        Config::Set(
                            $sKey,
                            func_array_merge_assoc(Config::Get($sKey), $aConfig)
                        );
                    }
                }
            }
        }

        if (isset($_SERVER['HTTP_APP_ENV']) && $_SERVER['HTTP_APP_ENV'] == 'test') {
            /*
             * Подгружаем файл тестового конфига
             */
            /*
            if (file_exists(Config::Get('path.root.dir') . '/config/config.test.php')) {
                Config::LoadFromFile(Config::Get('path.root.dir') . '/config/config.test.php', false);
            } else {
                throw new Exception('Config for test envirenment is not found.
                    Rename /config/config.test.php.dist to /config/config.test.php and rewrite DB settings.
                    After that check base_url in /test/behat/behat.yml it option must be correct site url.');
            }
            */
        } else {
            /*
             * LS-compatible
             * Подгружаем файлы локального и продакшн-конфига
             */
            $sFile = $sConfigDir . '/config.local.php';
            if (F::File_Exists($sFile)) {
                if ($aConfig = F::File_IncludeFile($sFile, true, Config::Get())) {
                    Config::Load($aConfig, true);
                }
            }
            $sFile = $sConfigDir . '/config.stable.php';
            if (F::File_Exists($sFile)) {
                if ($aConfig = F::File_IncludeFile($sFile, true, Config::Get())) {
                    Config::Load($aConfig, true);
                }
            }
        }

        /*
         * Загружает конфиг-файлы плагинов вида /plugins/[plugin_name]/config/*.php
         * и include-файлы вида /plugins/[plugin_name]/include/*.php
         */
        $sPluginsDir = F::GetPluginsDir();
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
                            $sKey = "plugin.$sPlugin";
                            if (!Config::isExist($sKey)) {
                                Config::Set($sKey, $aConfig);
                            } else {
                                // Если уже существую привязанные к плагину ключи,
                                // то сливаем старые и новое значения ассоциативно
                                Config::Set($sKey, F::Array_Merge(Config::Get($sKey), $aConfig));
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
        self::_checkRequiredDirs();

        $aSeekDirClasses = array(
            Config::Get('path.dir.app'),
            Config::Get('path.dir.common'),
            Config::Get('path.dir.engine'),
        );
        Config::Set('path.root.seek', $aSeekDirClasses);

        // Подгружаем конфиг из файлового кеша, если он есть
        Config::SetLevel(Config::LEVEL_CUSTOM);
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
    }

    /**
     * Load subconfig file
     *
     * @param $sFile
     * @param $sName
     */
    static protected function _loadConfigSection($sFile, $sName) {

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
        Config::Load(array($sName => $aConfig), false);
    }

    static protected function _checkRequiredDirs() {

        if (!F::File_CheckDir(Config::Get('path.dir.app'), false)) {
            die('Application folder "'. F::LocalDir(Config::Get('path.dir.app')) . '" does not exist');
        }
        if (!F::File_CheckDir(Config::Get('path.tmp.dir'), false)) {
            die('Required folder "'. F::LocalDir(Config::Get('path.tmp.dir')) . '" does not exist');
        }
        if (!F::File_CheckDir(Config::Get('path.runtime.dir'), false)) {
            die('Required folder "'. F::LocalDir(Config::Get('path.runtime.dir')) . '" does not exist');
        }
    }

    /**
     * Автоопределение класса или фала экшена
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
                if ($sActionFile = F::File_Exists('plugins/' . $sPlugin . '/classes/actions/' . $sFileName, $aSeekDirs)) {
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

    static protected function _includeFile($sFile) {

        if (class_exists('F', false)) {
            return F::IncludeFile($sFile);
        } else {
            return include_once $sFile;
        }
    }

    /**
     * Автозагрузка классов
     *
     * @param string $sClassName    Название класса
     *
     * @return bool
     */
    static public function Autoload($sClassName) {

        if (($aClasses = Config::Get('classes')) && isset($aClasses[$sClassName])) {
            if ($sClassFile = F::File_Exists($aClasses[$sClassName])) {
                self::_includeFile($sClassFile);
                return true;
            }
        }
        if (!class_exists('Engine', false) || (Engine::GetStage() < Engine::STAGE_INIT)) {
            //self::_includeFile(Config::Get('path.dir.engine') . '/classes/Engine.class.php');
            return false;
        }
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
            self::_includeFile($aInfo[Engine::CI_CLASSPATH]);
            return true;
        }
        return false;
    }


}

// EOF