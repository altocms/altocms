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

F::IncludeFile('LsObject.class.php');

class Loader extends LsObject {

    static public function init($sConfigDir) {
        // Load main config
        Config::LoadFromFile($sConfigDir . '/config.php');

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

        /**
         * Загружает конфиги модулей вида /config/modules/[module_name]/config.php
         */
        $sDirConfig = Config::get('path.root.dir') . '/config/modules/';
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
                        Config::Set(
                            $sKey,
                            func_array_merge_assoc(Config::Get($sKey), $aConfig)
                        );
                    }
                }
            }
        }

        /**
         * Инклудим все *.php файлы из каталога {path.root.engine}/include/ - это файлы ядра
         */
        $sDirInclude = Config::get('path.root.engine') . '/include/';
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
        $sDirConfig = Config::get('path.root.dir') . '/config/modules/';
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
            /**
             * Подгружаем файл тестового конфига
             */
            if (file_exists(Config::Get('path.root.server') . '/config/config.test.php')) {
                Config::LoadFromFile(Config::Get('path.root.server') . '/config/config.test.php', false);
            } else {
                throw new Exception('Config for test envirenment is not found.
                    Rename /config/config.test.php.dist to /config/config.test.php and rewrite DB settings.
                    After that check base_url in /test/behat/behat.yml it option must be correct site url.');
            }
        } else {
            /**
             * Подгружаем файлы локального и продакшн-конфига
             */
            $sFile = Config::Get('path.root.server') . '/config/config.local.php';
            if (F::File_Exists($sFile)) {
                if ($aConfig = F::File_IncludeFile($sFile, true, Config::Get())) {
                    Config::Load($aConfig, true);
                }
            }
            $sFile = Config::Get('path.root.server') . '/config/config.stable.php';
            if (F::File_Exists($sFile)) {
                if ($aConfig = F::File_IncludeFile($sFile, true, Config::Get())) {
                    Config::Load($aConfig, true);
                }
            }
        }

        /**
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
                                Config::Set(
                                    $sKey,
                                    func_array_merge_assoc(Config::Get($sKey), $aConfig)
                                );
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

        // Подгружаем конфиг из файлового кеша, если он есть
        $aConfig = Config::ReadCustomConfig(null, true);
        if ($aConfig) {
            Config::Load($aConfig, false);
        }

        if (!Config::Get('path.config.dir')) {
            Config::Set('path.config.dir', $sConfigDir . '/');
        }
        set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));

        // Регистрация автозагрузчика классов
        spl_autoload_register('Loader::Autoload');

        // Задаем локаль по умолчанию
        F::IncludeLib('UserLocale/UserLocale.class.php');
        UserLocale::setLocale(
            Config::Get('lang.current'),
            array('local' => Config::Get('i18n.locale'), 'timezone' => Config::Get('i18n.timezone'))
        );
        // Устанавливаем признак того, является ли сайт многоязычным
        if (sizeof((array)Config::Get('lang.allow')) > 1) {
            Config::Set('lang.multilang', true);
        } else {
            Config::Set('lang.multilang', false);
        }
    }

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
        if ($sActionFile = F::File_Exists(Config::Get('path.root.dir') . '/classes/actions/' . $sFileName)) {
            $sActionClass = 'Action' . ucfirst($sAction);
            $bOk = true;
        } else {
            // Если нет, то проверяем файл экшена среди плагинов
            $aPlugins = F::GetPluginsList();
            $sPluginsDir = F::File_RootDir() . 'plugins/';
            foreach ($aPlugins as $sPlugin) {
                if ($sActionFile = F::File_Exists($sPluginsDir . $sPlugin . '/classes/actions/' . $sFileName)) {
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
        if (!class_exists('Engine', false) || (Engine::GetStage() < Engine::STAGE_INIT)) {
            //self::_includeFile(Config::Get('path.dir.engine') . '/classes/Engine.class.php');
            return false;
        }
        $aInfo = Engine::GetClassInfo(
            $sClassName,
            Engine::CI_CLASSPATH | Engine::CI_INHERIT
        );
        if ($aInfo[Engine::CI_INHERIT]) {
            $sInheritClass = $aInfo[Engine::CI_INHERIT];
            $sParentClass = Engine::getInstance()->Plugin_GetParentInherit($sInheritClass);
            if (!class_alias($sParentClass, $sClassName)) {
                dump("(autoload $sParentClass) Can not load CLASS-file");
            } else {
                return true;
            }
        } elseif ($aInfo[Engine::CI_CLASSPATH]) {
            self::_includeFile($aInfo[Engine::CI_CLASSPATH]);
            return true;
        } elseif (!class_exists($sClassName)) {
            dump("(autoload $sClassName) Can not load CLASS-file");
            dump($aInfo);
            //throw new Exception("(autoload '$sClassName') Can not load CLASS-file");
        }
        return false;
    }


}

// EOF