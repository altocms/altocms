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

F::IncludeFile('../abstract/Plugin.class.php');
F::IncludeFile('../abstract/Hook.class.php');
F::IncludeFile('../abstract/Module.class.php');
F::IncludeFile('Decorator.class.php');

F::IncludeFile('../abstract/Entity.class.php');
F::IncludeFile('../abstract/Mapper.class.php');

F::IncludeFile('../abstract/ModuleORM.class.php');
F::IncludeFile('../abstract/EntityORM.class.php');
F::IncludeFile('../abstract/MapperORM.class.php');

F::IncludeFile('ManyToManyRelation.class.php');


/**
 * Основной класс движка. Ядро.
 *
 * Производит инициализацию плагинов, модулей, хуков.
 * Через этот класс происходит выполнение методов всех модулей,
 * которые вызываются так:
 * <pre>
 * E::ModuleName()->Method();
 * </pre>
 * Также отвечает за автозагрузку остальных классов движка.
 *
 *
 * @method static ModuleAcl ModuleAcl()
 * @method static ModuleAdmin ModuleAdmin()
 * @method static ModuleBlog ModuleBlog()
 * @method static ModuleCaptcha ModuleCaptcha()
 * @method static ModuleComment ModuleComment()
 * @method static ModuleFavourite ModuleFavourite()
 * @method static ModuleGeo ModuleGeo()
 * @method static ModuleMresource ModuleMresource()
 * @method static ModuleNotify ModuleNotify()
 * @method static ModulePage ModulePage()
 * @method static ModuleRating ModuleRating()
 * @method static ModuleSearch ModuleSearch()
 * @method static ModuleStream ModuleStream()
 * @method static ModuleSubscribe ModuleSubscribe()
 * @method static ModuleTalk ModuleTalk()
 * @method static ModuleTools ModuleTools()
 * @method static ModuleTopic ModuleTopic()
 * @method static ModuleUploader ModuleUploader()
 * @method static ModuleUser ModuleUser()
 * @method static ModuleUserfeed ModuleUserfeed()
 * @method static ModuleVote ModuleVote()
 * @method static ModuleWall ModuleWall()
 * @method static ModuleCache ModuleCache()
 * @method static ModuleDatabase ModuleDatabase()
 * @method static ModuleHook ModuleHook()
 * @method static ModuleImg ModuleImg()
 * @method static ModuleLang ModuleLang()
 * @method static ModuleLess ModuleLess()
 * @method static ModuleLogger ModuleLogger()
 * @method static ModuleMail ModuleMail()
 * @method static ModuleMenu ModuleMenu()
 * @method static ModuleMessage ModuleMessage()
 * @method static ModulePlugin ModulePlugin()
 * @method static ModuleRequest ModuleRequest()
 * @method static ModuleSecurity ModuleSecurity()
 * @method static ModuleSession ModuleSession()
 * @method static ModuleSkin ModuleSkin()
 * @method static ModuleText ModuleText()
 * @method static ModuleValidate ModuleValidate()
 * @method static ModuleViewer ModuleViewer()
 * @method static ModuleViewerasset ModuleViewerasset()
 * @method static ModuleWidget ModuleWidget()
 * @method static ModuleApi ModuleApi()
 *
 * @package engine
 * @since 1.0
 */
class Engine extends LsObject {

    /**
     * Имя плагина
     * @var int
     */
    const CI_PLUGIN = 1;

    /**
     * Имя экшна
     * @var int
     */
    const CI_ACTION = 2;

    /**
     * Имя модуля
     * @var int
     */
    const CI_MODULE = 4;

    /**
     * Имя сущности
     * @var int
     */
    const CI_ENTITY = 8;

    /**
     * Имя маппера
     * @var int
     */
    const CI_MAPPER = 16;

    /**
     * Имя метода
     * @var int
     */
    const CI_METHOD = 32;

    /**
     * Имя хука
     * @var int
     */
    const CI_HOOK = 64;

    /**
     * Имя класс наследования
     * @var int
     */
    const CI_INHERIT = 128;

    /**
     * Имя блока
     * @var int
     */
    const CI_BLOCK = 256;

    /**
     * Имя виджета
     * @var int
     */
    const CI_WIDGET = 512;

    /**
     * Префикс плагина
     * @var int
     */
    const CI_PPREFIX = 8192;

    /**
     * Разобранный класс наследования
     * @var int
     */
    const CI_INHERITS = 16384;

    /**
     * Путь к файлу класса
     * @var int
     */
    const CI_CLASSPATH = 32768;

    /**
     * Все свойства класса
     * @var int
     */
    const CI_ALL = 65535;

    /**
     * Свойства по-умолчанию
     * CI_ALL ^ (CI_CLASSPATH | CI_INHERITS | CI_PPREFIX)
     * @var int
     */
    const CI_DEFAULT = 8191;

    /**
     * Объекты
     * CI_ACTION | CI_MAPPER | CI_HOOK | CI_PLUGIN | CI_ACTION | CI_MODULE | CI_ENTITY | CI_BLOCK | CI_WIDGET
     * @var int
     */
    const CI_OBJECT = 863;

    const CI_AREA_ENGINE = 1;
    const CI_AREA_COMMON = 2;
    const CI_AREA_WITHOUT_PLUGINS = 3;
    const CI_AREA_ACTIVE_PLUGINS = 4;
    const CI_AREA_ACTUAL = 7;
    const CI_AREA_ALL_PLUGINS = 8;
    const CI_AREA_ANYWHERE = 15;

    const STAGE_INIT = 1;
    const STAGE_RUN = 2;
    const STAGE_SHUTDOWN = 3;
    const STAGE_DONE = 4;

    /** @var int - Stage of Engine */
    static protected $nStage = 0;

    /**
     * Текущий экземпляр движка, используется для синглтона.
     * @see getInstance использование синглтона
     *
     * @var Engine
     */
    static protected $oInstance = null;

    /**
     * Internal cache of resolved class names
     *
     * @var array
     */
    static protected $aClasses = array();

    /**
     * Internal cache for info of used classes
     *
     * @var array
     */
    static protected $aClassesInfo = array();

    /**
     * Список загруженных модулей
     *
     * @var array
     */
    protected $aModules = array();

    /**
     * Map of relations Name => Class
     *
     * @var array
     */
    protected $aModulesMap = array();

    /**
     * Список загруженных плагинов
     *
     * @var array
     */
    protected $aPlugins = array();

    /**
     * Время загрузки модулей в микросекундах
     *
     * @var int
     */
    public $nTimeLoadModule = 0;
    /**
     * Текущее время в микросекундах на момент инициализации ядра(движка).
     * Определается так:
     * <pre>
     * $this->iTimeInit=microtime(true);
     * </pre>
     *
     * @var int|null
     */
    protected $nTimeInit = null;


    /**
     * Вызывается при создании объекта ядра.
     * Устанавливает время старта инициализации и обрабатывает входные параметры PHP
     *
     */
    public function __construct() {

        $this->nTimeInit = microtime(true);
    }

    public function __destruct() {

    }

    /**
     * Ограничиваем объект только одним экземпляром.
     * Функционал синглтона.
     *
     * @return Engine
     */
    static public function getInstance() {

        if (empty(self::$oInstance)) {
            self::$oInstance = new static();
        }
        return self::$oInstance;
    }

    /**
     * Инициализация ядра движка
     *
     */
    public function Init() {

        if (self::$nStage >= self::STAGE_RUN) return;

        self::$nStage = self::STAGE_INIT;
        /**
         * Загружаем плагины
         */
        $this->LoadPlugins();
        /**
         * Инициализируем хуки
         */
        $this->InitHooks();
        /**
         * Загружаем модули автозагрузки
         */
        $this->LoadModules();
        /**
         * Инициализируем загруженные модули
         */
        $this->InitModules();
        /**
         * Инициализируем загруженные плагины
         */
        $this->InitPlugins();
        /**
         * Запускаем хуки для события завершения инициализации Engine
         */
        //E::ModuleHook()->Run('engine_init_complete');
        //$aArgs = array('engine_init_complete');
        //$this->_CallModule('Hook_Run', $aArgs);
        $this->GetModule('Hook')->Run('engine_init_complete');
        self::$nStage = self::STAGE_RUN;
    }

    /**
     * Завершение работы движка
     * Завершает все модули.
     *
     */
    public function Shutdown() {

        if (self::$nStage < self::STAGE_SHUTDOWN) {
            self::$nStage = self::STAGE_SHUTDOWN;
            $this->ShutdownModules();
            self::$nStage = self::STAGE_DONE;
        }
    }

    static public function GetStage() {

        return self::$nStage;
    }

    /**
     * Производит инициализацию всех модулей
     *
     */
    protected function InitModules() {

        /** @var Decorator $oModule */
        foreach ($this->aModules as $oModule) {
            if (!$oModule->isInit()) {
                $this->InitModule($oModule);
            }
        }
    }

    /**
     * Инициализирует модуль
     *
     * @param Decorator $oModule - Объект модуля
     *
     * @throws Exception
     */
    protected function InitModule($oModule) {

        if ($oModule->InInitProgress()) {
            // Нельзя запускать инициализацию модуля в процессе его инициализации
            throw new Exception('Recursive initialization of module "' . get_class($oModule) . '"');
        }
        $oModule->SetInit(true);
        $oModule->Init();
        $oModule->SetInit();
    }

    /**
     * Проверяет модуль на инициализацию
     *
     * @param string $sModuleClass    Класс модуля
     * @return bool
     */
    public function isInitModule($sModuleClass) {

        if ($sModuleClass !== 'ModulePlugin' && $sModuleClass !== 'ModuleHook') {
            //$sModuleClass = E::ModulePlugin()->GetDelegate('module', $sModuleClass);
            $sModuleClass = $this->GetModule('Plugin')->GetDelegate('module', $sModuleClass);
        }
        if (isset($this->aModules[$sModuleClass]) && $this->aModules[$sModuleClass]->isInit()) {
            return true;
        }
        return false;
    }

    /**
     * Завершаем работу всех модулей
     *
     */
    protected function ShutdownModules() {

        $aModules = $this->aModules;
        array_reverse($aModules);
        // Сначала shutdown модулей, загруженных в процессе работы
        /** @var Module $oModule */
        foreach ($aModules as $oModule) {
            if (!$oModule->GetPreloaded()) {
                $this->ShutdownModule($oModule);
            }
        }
        // Затем предзагруженные модули
        foreach ($aModules as $oModule) {
            if ($oModule->GetPreloaded()) {
                $this->ShutdownModule($oModule);
            }
        }
    }

    /**
     * @param Module $oModule
     *
     * @throws Exception
     */
    protected function ShutdownModule($oModule) {

        if ($oModule->InShudownProgress()) {
            // Нельзя запускать shutdown модуля в процессе его shutdown`a
            throw new Exception('Recursive shutdown of module "' . get_class($oModule) . '"');
        }
        $oModule->SetDone(false);
        $oModule->Shutdown();
        $oModule->SetDone();
    }

    /**
     * Выполняет загрузку модуля по его названию
     *
     * @param  string $sModuleClass    Класс модуля
     * @param  bool $bInit Инициализировать модуль или нет
     *
     * @throws RuntimeException если класс $sModuleClass не существует
     *
     * @return Module
     */
    public function LoadModule($sModuleClass, $bInit = false) {

        $tm1 = microtime(true);

        if (!class_exists($sModuleClass)) {
            throw new RuntimeException(sprintf('Class "%s" not found!', $sModuleClass));
        }

        // * Создаем объект модуля
        $oModule = new $sModuleClass();
        $oModuleDecorator = Decorator::Create($oModule);
        $this->aModules[$sModuleClass] = $oModuleDecorator;
        if ($bInit || $sModuleClass == 'ModuleCache') {
            $this->InitModule($oModuleDecorator);
        }
        $tm2 = microtime(true);
        $this->nTimeLoadModule += $tm2 - $tm1;

        return $oModuleDecorator;
    }

    /**
     * Загружает модули из авто-загрузки
     *
     */
    protected function LoadModules() {

        $aAutoloadModules = C::Get('module._autoLoad_');
        if (!empty($aAutoloadModules)) {
            foreach ($aAutoloadModules as $sModuleName) {
                $sModuleClass = 'Module' . $sModuleName;
                if ($sModuleName !== 'Plugin' && $sModuleName !== 'Hook') {
                    //$sModuleClass = E::ModulePlugin()->GetDelegate('module', $sModuleClass);
                    $sModuleClass = $this->GetModule('Plugin')->GetDelegate('module', $sModuleClass);
                }

                if (!isset($this->aModules[$sModuleClass])) {
                    $this->LoadModule($sModuleClass);
                    if (isset($this->aModules[$sModuleClass])) {
                        // Устанавливаем для модуля признак предзагрузки
                        $this->aModules[$sModuleClass]->SetPreloaded(true);
                    }
                }
            }
        }
    }

    public function GetLoadedModules() {

        return $this->aModules;
    }

    /**
     * Регистрирует хуки из /classes/hooks/
     *
     */
    protected function InitHooks() {

        $aPathSeek = array_reverse(Config::Get('path.root.seek'));
        $aHookFiles = array();
        foreach ($aPathSeek as $sDirHooks) {
            $aFiles = glob($sDirHooks . '/classes/hooks/Hook*.class.php');
            if ($aFiles) {
                foreach ($aFiles as $sFile) {
                    $aHookFiles[basename($sFile)] = $sFile;
                }
            }
        }

        if ($aHookFiles) {
            foreach ($aHookFiles as $sFile) {
                if (preg_match('/Hook([^_]+)\.class\.php$/i', basename($sFile), $aMatch)) {
                    $sClassName = 'Hook' . $aMatch[1];
                    /** @var Hook $oHook */
                    $oHook = new $sClassName;
                    $oHook->RegisterHook();
                }
            }
        }

        // * Подгружаем хуки активных плагинов
        $this->InitPluginHooks();
    }

    /**
     * Инициализация хуков активированных плагинов
     *
     */
    protected function InitPluginHooks() {

        if ($aPluginList = F::GetPluginsList(false, false)) {
            $sPluginsDir = F::GetPluginsDir();

            foreach ($aPluginList as $aPluginInfo) {
                $aFiles = glob($sPluginsDir . $aPluginInfo['dirname'] . '/classes/hooks/Hook*.class.php');
                if ($aFiles && count($aFiles)) {
                    foreach ($aFiles as $sFile) {
                        if (preg_match('/Hook([^_]+)\.class\.php$/i', basename($sFile), $aMatch)) {
                            //require_once($sFile);
                            $sPluginName = F::StrCamelize($aPluginInfo['id']);
                            $sClassName = "Plugin{$sPluginName}_Hook{$aMatch[1]}";
                            /** @var Hook $oHook */
                            $oHook = new $sClassName;
                            $oHook->RegisterHook();
                        }
                    }
                }
            }
        }
    }

    /**
     * Загрузка плагинов и делегирование
     *
     */
    protected function LoadPlugins() {

        if ($aPluginList = F::GetPluginsList()) {
            foreach ($aPluginList as $sPluginName) {
                $sClassName = 'Plugin' . F::StrCamelize($sPluginName);
                /** @var Plugin $oPlugin */
                $oPlugin = new $sClassName;
                $oPlugin->Delegate();
                $this->aPlugins[$sPluginName] = $oPlugin;
            }
        }
    }

    /**
     * Инициализация активированных(загруженных) плагинов
     *
     */
    protected function InitPlugins() {

        /** @var Plugin $oPlugin */
        foreach ($this->aPlugins as $oPlugin) {
            $oPlugin->Init();
        }
    }

    /**
     * Возвращает список активных плагинов
     *
     * @return array
     */
    public function GetPlugins() {

        return $this->aPlugins;
    }

    /**
     * Вызывает метод нужного модуля
     *
     * @param string $sName    Название метода в полном виде.
     * Например <pre>Module_Method</pre>
     * @param array $aArgs    Список аргументов
     *
     * @return mixed
     */
    public function _CallModule($sName, &$aArgs) {

        list($oModule, $sModuleName, $sMethod) = $this->GetModuleMethod($sName);
        $aArgsRef = array();
        foreach ($aArgs as $iKey => $xVal) {
            $aArgsRef[] =& $aArgs[$iKey];
        }
        if ($oModule instanceof Decorator) {
            $xResult = $oModule->CallMethod($sMethod, $aArgsRef);
        } else {
            $xResult = call_user_func_array(array($oModule, $sMethod), $aArgsRef);
        }

        // LS-compatibility
        if ($sName == 'Plugin_GetActivePlugins' && !empty($xResult) && is_array($xResult)) {
            $xResult = array_keys($xResult);
        }
        
        return $xResult;
    }

    /**
     * Возвращает объект модуля, имя модуля и имя вызванного метода
     *
     * @param $sCallName - Имя метода модуля в полном виде
     * Например <pre>Module_Method</pre>
     *
     * @return array
     * @throws Exception
     */
    public function GetModuleMethod($sCallName) {

        if (isset($this->aModulesMap[$sCallName])) {
            list($sModuleClass, $sModuleName, $sMethod) = $this->aModulesMap[$sCallName];
        } else {
            $sName = $sCallName;
            if (strpos($sCallName, 'Module') !== false || strpos($sCallName, 'Plugin') !== false || substr_count($sCallName, '_') > 1) {
                // * Поддержка полного синтаксиса при вызове метода модуля
                $aInfo = static::GetClassInfo($sName, self::CI_MODULE | self::CI_PPREFIX | self::CI_METHOD);
                if ($aInfo[self::CI_MODULE]) {
                    $sName = $aInfo[self::CI_MODULE] . '_' . ($aInfo[self::CI_METHOD] ? $aInfo[self::CI_METHOD] : '');
                    if ($aInfo[self::CI_PPREFIX]) {
                        $sName = $aInfo[self::CI_PPREFIX] . $sName;
                    }
                }
            }

            $aName = explode('_', $sName);

            switch (count($aName)) {
                case 1:
                    $sModuleName = $sName;
                    $sModuleClass = 'Module' . $sName;
                    $sMethod = null;
                    break;
                case 2:
                    $sModuleName = $aName[0];
                    $sModuleClass = 'Module' . $aName[0];
                    $sMethod = $aName[1];
                    break;
                case 3:
                    $sModuleName = $aName[0] . '_' . $aName[1];
                    $sModuleClass = $aName[0] . '_Module' . $aName[1];
                    $sMethod = $aName[2];
                    break;
                default:
                    throw new Exception('Undefined method module: ' . $sName);
            }

            // * Получаем делегат модуля (в случае наличия такового)
            if ($sModuleName !== 'Plugin' && $sModuleName !== 'Hook') {
                //$sModuleClass = E::ModulePlugin()->GetDelegate('module', $sModuleClass);
                $sModuleClass = $this->GetModule('Plugin')->GetDelegate('module', $sModuleClass);
            }
            $this->aModulesMap[$sCallName] = array($sModuleClass, $sModuleName, $sMethod);
        }

        if (isset($this->aModules[$sModuleClass])) {
            $oModule = $this->aModules[$sModuleClass];
        } else {
            $oModule = $this->LoadModule($sModuleClass, true);
        }

        return array($oModule, $sModuleName, $sMethod);
    }

    /**
     * Возвращает объект модуля
     *
     * @param string $sModuleName Имя модуля
     *
     * @return object|null
     * @throws Exception
     */
    public function GetModule($sModuleName) {

        // $sCallName === 'User' or $sCallName === 'ModuleUser' or $sCallName === 'PluginUser\User' or $sCallName === 'PluginUser\ModuleUser'
        $sPrefix = substr($sModuleName, 0, 6);
        if ($sPrefix == 'Module' && preg_match('/^(Module)?([A-Z].*)$/', $sModuleName, $aMatches)) {
            $sModuleName = $aMatches[2];
        } elseif ($sPrefix === 'Plugin' && preg_match('/^Plugin([A-Z][\w]*)\\\\(Module)?([A-Z].*)$/', $sModuleName, $aMatches)) {
            $sModuleName = 'Plugin' . $aMatches[1] . '_Module' . $aMatches[3];
        }
        if ($sModuleName) {
            $aData = $this->GetModuleMethod($sModuleName);

            return $aData[0];
        }
        return null;
    }

    /**
     * Возвращает статистику выполнения
     *
     * @return array
     */
    public function getStats() {
        /**
         * Подсчитываем время выполнения
         */
        $nTimeInit = $this->GetTimeInit();
        $nTimeFull = microtime(true) - $nTimeInit;
        if (!empty($_SERVER['REQUEST_TIME_FLOAT'])) {
            $nExecTime = round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3);
        } elseif (!empty($_SERVER['REQUEST_TIME'])) {
            $nExecTime = round(microtime(true) - $_SERVER['REQUEST_TIME'], 3);
        } else {
            $nExecTime = 0;
        }
        return array(
            'sql' => $this->GetModule('Database')->GetStats(),
            'cache' => $this->GetModule('Cache')->GetStats(),
            'engine' => array(
                'time_load_module' => number_format(round($this->nTimeLoadModule, 3), 3),
                'full_time' => number_format(round($nTimeFull, 3), 3),
                'exec_time' => number_format(round($nExecTime, 3), 3),
                'files_count' => F::File_GetIncludedCount(),
                'files_time' => number_format(round(F::File_GetIncludedTime(), 3), 3),
            ),
        );
    }

    /**
     * Возвращает время старта выполнения движка в микросекундах
     *
     * @return int
     */
    public function GetTimeInit() {

        return $this->nTimeInit;
    }

    /**
     * Блокируем копирование/клонирование объекта ядра
     *
     */
    protected function __clone() {

    }

    /**
     * Получает объект маппера
     *
     * @param string                 $sClassName Класс модуля маппера
     * @param string|null            $sName      Имя маппера
     * @param DbSimple_Driver_Mysqli $oConnect   Объект коннекта к БД,
     *                                           который может быть получен так <pre>E::ModuleDatabase()->GetConnect($aConfig);</pre>
     *
     * @return null|Mapper
     */
    public static function GetMapper($sClassName, $sName = null, $oConnect = null) {

        $sModuleName = static::GetClassInfo($sClassName, self::CI_MODULE, true);
        if ($sModuleName) {
            if (!$sName) {
                $sName = $sModuleName;
            }
            $sClass = $sClassName . '_Mapper' . $sName;
            if (!$oConnect) {
                $oConnect = static::Module('Database')->GetConnect();
            }
            $sClass = static::Module('Plugin')->GetDelegate('mapper', $sClass);
            return new $sClass($oConnect);
        }
        return null;
    }

    /**
     * Возвращает класс сущности, контролируя варианты кастомизации
     *
     * @param  string $sName Имя сущности, возможны сокращенные варианты.
     *                       Например, <pre>ModuleUser_EntityUser</pre> эквивалентно <pre>User_User</pre>
     *                       и эквивалентно <pre>User</pre>, т.к. имя сущности совпадает с именем модуля
     *
     * @return string
     * @throws Exception
     */
    public static function GetEntityClass($sName) {

        if (!isset(self::$aClasses[$sName])) {
            /*
             * Сущности, имеющие такое же название как модуль,
             * можно вызывать сокращенно. Например, вместо User_User -> User
             */
            switch (substr_count($sName, '_')) {
                case 0:
                    $sEntity = $sModule = $sName;
                    break;

                case 1:
                    // * Поддержка полного синтаксиса при вызове сущности
                    $aInfo = static::GetClassInfo($sName, self::CI_ENTITY | self::CI_MODULE | self::CI_PLUGIN);
                    if ($aInfo[self::CI_MODULE] && $aInfo[self::CI_ENTITY]) {
                        $sName = $aInfo[self::CI_MODULE] . '_' . $aInfo[self::CI_ENTITY];
                    }

                    list($sModule, $sEntity) = explode('_', $sName, 2);
                    /*
                     * Обслуживание короткой записи сущностей плагинов
                     * PluginTest_Test -> PluginTest_ModuleTest_EntityTest
                     */
                    if ($aInfo[self::CI_PLUGIN]) {
                        $sPlugin = $aInfo[self::CI_PLUGIN];
                        $sModule = $sEntity;
                    }
                    break;

                case 2:
                    // * Поддержка полного синтаксиса при вызове сущности плагина
                    $aInfo = static::GetClassInfo($sName, self::CI_ENTITY | self::CI_MODULE | self::CI_PLUGIN);
                    if ($aInfo[self::CI_PLUGIN] && $aInfo[self::CI_MODULE] && $aInfo[self::CI_ENTITY]) {
                        $sName = 'Plugin' . $aInfo[self::CI_PLUGIN]
                            . '_' . $aInfo[self::CI_MODULE]
                            . '_' . $aInfo[self::CI_ENTITY];
                    }
                    // * Entity плагина
                    if ($aInfo[self::CI_PLUGIN]) {
                        list(, $sModule, $sEntity) = explode('_', $sName);
                        $sPlugin = $aInfo[self::CI_PLUGIN];
                    } else {
                        throw new Exception("Unknown entity '{$sName}' given.");
                    }
                    break;

                default:
                    throw new Exception("Unknown entity '{$sName}' given.");
            }

            $sClass = isset($sPlugin)
                ? 'Plugin' . $sPlugin . '_Module' . $sModule . '_Entity' . $sEntity
                : 'Module' . $sModule . '_Entity' . $sEntity;

            // * If Plugin Entity doesn't exist, search among it's Module delegates
            if (isset($sPlugin) && !static::GetClassPath($sClass)) {
                $aModulesChain = static::Module('Plugin')->GetDelegationChain('module', 'Plugin' . $sPlugin . '_Module' . $sModule);
                foreach ($aModulesChain as $sModuleName) {
                    $sClassTest = $sModuleName . '_Entity' . $sEntity;
                    if (static::GetClassPath($sClassTest)) {
                        $sClass = $sClassTest;
                        break;
                    }
                }
                if (!static::GetClassPath($sClass)) {
                    $sClass = 'Module' . $sModule . '_Entity' . $sEntity;
                }
            }

            /**
             * Определяем наличие делегата сущности
             * Делегирование указывается только в полной форме!
             */
            //$sClass = static::getInstance()->Plugin_GetDelegate('entity', $sClass);
            if ($sClass != 'ModulePlugin_EntityPlugin') {
                $sClass = static::Module('Plugin')->GetDelegate('entity', $sClass);
            }

            self::$aClasses[$sName] = $sClass;
        } else {
            $sClass = self::$aClasses[$sName];
        }
        return $sClass;
    }

    /**
     * Создает объект сущности, контролируя варианты кастомизации
     *
     * @param  string $sName Имя сущности, возможны сокращенные варианты.
     *                       Например, <pre>ModuleUser_EntityUser</pre> эквивалентно <pre>User_User</pre>
     *                       и эквивалентно <pre>User</pre> т.к. имя сущности совпадает с именем модуля
     *
     * @param  array  $aParams
     *
     * @return Entity
     * @throws Exception
     */
    public static function GetEntity($sName, $aParams = array()) {

        $sClass = static::GetEntityClass($sName);
        /** @var Entity $oEntity */
        $oEntity = new $sClass($aParams);
        $oEntity->Init();

        return $oEntity;
    }

    /**
     * Returns array of entity objects
     *
     * @param string     $sName - Entity name
     * @param array      $aRows
     * @param array|null $aOrderIdx
     *
     * @return array
     */
    public static function GetEntityRows($sName, $aRows = array(), $aOrderIdx = null) {

        $aResult = array();
        if ($aRows) {
            $sClass = static::GetEntityClass($sName);
            if (is_array($aOrderIdx) && sizeof($aOrderIdx)) {
                foreach ($aOrderIdx as $iIndex) {
                    if (isset($aRows[$iIndex])) {
                        /** @var Entity $oEntity */
                        $oEntity = new $sClass($aRows[$iIndex]);
                        $oEntity->Init();
                        $aResult[$iIndex] = $oEntity;
                    }
                }
            } else {
                foreach ($aRows as $nI => $aRow) {
                    /** @var Entity $oEntity */
                    $oEntity = new $sClass($aRow);
                    $oEntity->Init();
                    $aResult[$nI] = $oEntity;
                }
            }
        }
        return $aResult;
    }

    /**
     * Возвращает имя плагина модуля, если модуль принадлежит плагину
     * Например, <pre>Openid</pre>
     *
     * @static
     *
     * @param Module|string $xModule - Объект модуля
     *
     * @return string|null
     */
    public static function GetPluginName($xModule) {

        return static::GetClassInfo($xModule, self::CI_PLUGIN, true);
    }

    /**
     * Возвращает префикс плагина
     * Например, <pre>PluginOpenid_</pre>
     *
     * @static
     *
     * @param Module|string $xModule Объект модуля
     *
     * @return string    Если плагина нет, возвращает пустую строку
     */
    public static function GetPluginPrefix($xModule) {

        return static::GetClassInfo($xModule, self::CI_PPREFIX, true);
    }

    /**
     * Возвращает имя модуля
     *
     * @static
     *
     * @param Module|string $xModule Объект модуля
     *
     * @return string|null
     */
    public static function GetModuleName($xModule) {

        return static::GetClassInfo($xModule, self::CI_MODULE, true);
    }

    /**
     * Возвращает имя сущности
     *
     * @static
     *
     * @param Entity|string $xEntity Объект сущности
     *
     * @return string|null
     */
    public static function GetEntityName($xEntity) {

        return static::GetClassInfo($xEntity, self::CI_ENTITY, true);
    }

    /**
     * Возвращает имя экшена
     *
     * @static
     *
     * @param Action|string $xAction    Объект экшена
     *
     * @return string|null
     */
    public static function GetActionName($xAction) {

        return static::GetClassInfo($xAction, self::CI_ACTION, true);
    }

    /**
     * Возвращает информацию об объекта или классе
     *
     * @static
     *
     * @param LsObject|string $oObject Объект или имя класса
     * @param int             $iBitmask   Маска по которой нужно вернуть рузультат. Доступные маски определены в константах CI_*
     *                                 Например, получить информацию о плагине и модуле:
     *                                 <pre>
     *                                 Engine::GetClassInfo($oObject,Engine::CI_PLUGIN | Engine::CI_MODULE);
     *                                 </pre>
     * @param bool            $bSingle Возвращать полный результат или только первый элемент
     *
     * @return array|string|null
     */
    public static function GetClassInfo($oObject, $iBitmask = self::CI_DEFAULT, $bSingle = false) {

        $aResult = array();
        $sClassName = is_string($oObject) ? $oObject : get_class($oObject);
        $aInfo = (!empty(self::$aClassesInfo[$sClassName]) ? self::$aClassesInfo[$sClassName] : array());

        $iTime = microtime(true);
        // The first call because it sets other parts in self::$aClassesInfo
        if ($iBitmask & self::CI_CLASSPATH) {
            if (!isset($aInfo[self::CI_CLASSPATH])) {
                $aInfo[self::CI_CLASSPATH] = static::GetClassPath($sClassName);
                self::$aClassesInfo[$sClassName][self::CI_CLASSPATH] = $aInfo[self::CI_CLASSPATH];
            }
            $aResult[self::CI_CLASSPATH] = $aInfo[self::CI_CLASSPATH];
        }
        // Flag of finalization
        $bBreak = false;
        if ($iBitmask & self::CI_PLUGIN) {
            if (!isset($aInfo[self::CI_PLUGIN])) {
                $aInfo[self::CI_PLUGIN] = preg_match('/^Plugin([^_]+)/', $sClassName, $aMatches)
                    ? $aMatches[1]
                    : false;
                self::$aClassesInfo[$sClassName][self::CI_PLUGIN] = $aInfo[self::CI_PLUGIN];
                // It's plugin class only
                if ($aInfo[self::CI_PLUGIN] && $aMatches[0] == $sClassName) {
                    $bBreak = true;
                }
            }
            $aResult[self::CI_PLUGIN] = $aInfo[self::CI_PLUGIN];
        }

        if ($iBitmask & self::CI_ACTION) {
            if ($bBreak) {
                $aInfo[self::CI_ACTION] = false;
            } elseif (!isset($aInfo[self::CI_ACTION])) {
                $aInfo[self::CI_ACTION] = preg_match('/^(?:Plugin[^_]+_|)Action([^_]+)/', $sClassName, $aMatches)
                    ? $aMatches[1]
                    : false;
                self::$aClassesInfo[$sClassName][self::CI_ACTION] = $aInfo[self::CI_ACTION];
                // it's an Action
                $bBreak = !empty($aInfo[self::CI_ACTION]);
            }
            $aResult[self::CI_ACTION] = $aInfo[self::CI_ACTION];
        }

        if ($iBitmask & self::CI_HOOK) {
            if ($bBreak) {
                $aInfo[self::CI_HOOK] = false;
            } elseif (!isset($aInfo[self::CI_HOOK])) {
                $aInfo[self::CI_HOOK] = preg_match('/^(?:Plugin[^_]+_|)Hook([^_]+)$/', $sClassName, $aMatches)
                    ? $aMatches[1]
                    : false;
                self::$aClassesInfo[$sClassName][self::CI_HOOK] = $aInfo[self::CI_HOOK];
                // it's a Hook
                $bBreak = !empty($aInfo[self::CI_HOOK]);
            }
            $aResult[self::CI_HOOK] = $aInfo[self::CI_HOOK];
        }

        // ** LS-compatibility
        if ($iBitmask & self::CI_BLOCK) {
            if ($bBreak) {
                $aInfo[self::CI_BLOCK] = false;
            } elseif (!isset($aInfo[self::CI_BLOCK])) {
                $aInfo[self::CI_BLOCK] = preg_match('/^(?:Plugin[^_]+_|)Block([^_]+)$/', $sClassName, $aMatches)
                    ? $aMatches[1]
                    : false;
                self::$aClassesInfo[$sClassName][self::CI_BLOCK] = $aInfo[self::CI_BLOCK];
                // it's a Block
                $bBreak = !empty($aInfo[self::CI_BLOCK]);
            }
            $aResult[self::CI_BLOCK] = $aInfo[self::CI_BLOCK];
        }

        if ($iBitmask & self::CI_WIDGET) {
            if ($bBreak) {
                $aInfo[self::CI_WIDGET] = false;
            } elseif (!isset($aInfo[self::CI_WIDGET])) {
                $aInfo[self::CI_WIDGET] = preg_match('/^(?:Plugin[^_]+_|)Widget([^_]+)$/', $sClassName, $aMatches)
                    ? $aMatches[1]
                    : false;
                self::$aClassesInfo[$sClassName][self::CI_WIDGET] = $aInfo[self::CI_WIDGET];
                // it's a Widget
                $bBreak = !empty($aInfo[self::CI_WIDGET]);
            }
            $aResult[self::CI_WIDGET] = $aInfo[self::CI_WIDGET];
        }

        if ($iBitmask & self::CI_MODULE) {
            if ($bBreak) {
                $aInfo[self::CI_MODULE] = false;
            } elseif (!isset($aInfo[self::CI_MODULE])) {
                $aInfo[self::CI_MODULE] = preg_match('/^(?:Plugin[^_]+_|)Module(?:ORM|)([^_]+)/', $sClassName, $aMatches)
                    ? $aMatches[1]
                    : false;
                self::$aClassesInfo[$sClassName][self::CI_MODULE] = $aInfo[self::CI_MODULE];
            }
            $aResult[self::CI_MODULE] = $aInfo[self::CI_MODULE];
        }

        if ($iBitmask & self::CI_ENTITY) {
            if ($bBreak) {
                $aInfo[self::CI_ENTITY] = false;
            } elseif (!isset($aInfo[self::CI_ENTITY])) {
                $aInfo[self::CI_ENTITY] = preg_match('/_Entity(?:ORM|)([^_]+)/', $sClassName, $aMatches)
                    ? $aMatches[1]
                    : false;
                self::$aClassesInfo[$sClassName][self::CI_ENTITY] = $aInfo[self::CI_ENTITY];
            }
            $aResult[self::CI_ENTITY] = $aInfo[self::CI_ENTITY];
        }

        if ($iBitmask & self::CI_MAPPER) {
            if ($bBreak) {
                $aInfo[self::CI_MAPPER] = false;
            } elseif (!isset($aInfo[self::CI_MAPPER])) {
                $aInfo[self::CI_MAPPER] = preg_match('/_Mapper(?:ORM|)([^_]+)/', $sClassName, $aMatches)
                    ? $aMatches[1]
                    : false;
                self::$aClassesInfo[$sClassName][self::CI_MAPPER] = $aInfo[self::CI_MAPPER];
            }
            $aResult[self::CI_MAPPER] = $aInfo[self::CI_MAPPER];
        }

        if ($iBitmask & self::CI_METHOD) {
            if (!isset($aInfo[self::CI_METHOD])) {
                $sModuleName = isset($aInfo[self::CI_MODULE])
                    ? $aInfo[self::CI_MODULE]
                    : static::GetClassInfo($sClassName, self::CI_MODULE, true);
                $aInfo[self::CI_METHOD] = preg_match('/_([^_]+)$/', $sClassName, $aMatches)
                    ? ($sModuleName && strtolower($aMatches[1]) == strtolower('module' . $sModuleName) ? null : $aMatches[1])
                    : false;
                self::$aClassesInfo[$sClassName][self::CI_METHOD] = $aInfo[self::CI_METHOD];
            }
            $aResult[self::CI_METHOD] = $aInfo[self::CI_METHOD];
        }
        if ($iBitmask & self::CI_PPREFIX) {
            if (!isset($aInfo[self::CI_PPREFIX])) {
                $sPluginName = isset($aInfo[self::CI_PLUGIN])
                    ? $aInfo[self::CI_PLUGIN]
                    : static::GetClassInfo($sClassName, self::CI_PLUGIN, true);
                $aInfo[self::CI_PPREFIX] = $sPluginName
                    ? "Plugin{$sPluginName}_"
                    : '';
                self::$aClassesInfo[$sClassName][self::CI_PPREFIX] = $aInfo[self::CI_PPREFIX];
            }
            $aResult[self::CI_PPREFIX] = $aInfo[self::CI_PPREFIX];
        }
        if ($iBitmask & self::CI_INHERIT) {
            if (!isset($aInfo[self::CI_INHERIT])) {
                $aInfo[self::CI_INHERIT] = preg_match('/_Inherits?_(\w+)$/', $sClassName, $aMatches)
                    ? $aMatches[1]
                    : false;
                self::$aClassesInfo[$sClassName][self::CI_INHERIT] = $aInfo[self::CI_INHERIT];
            }
            $aResult[self::CI_INHERIT] = $aInfo[self::CI_INHERIT];
        }
        if ($iBitmask & self::CI_INHERITS) {
            if (!isset($aInfo[self::CI_INHERITS])) {
                $sInherit = isset($aInfo[self::CI_INHERIT])
                    ? $aInfo[self::CI_INHERIT]
                    : static::GetClassInfo($sClassName, self::CI_INHERIT, true);
                $aInfo[self::CI_INHERITS] = $sInherit
                    ? static::GetClassInfo($sInherit, self::CI_OBJECT, false)
                    : false;
                self::$aClassesInfo[$sClassName][self::CI_INHERITS] = $aInfo[self::CI_INHERITS];
            }
            $aResult[self::CI_INHERITS] = $aInfo[self::CI_INHERITS];
        }

        self::$aClassesInfo[$sClassName]['calls'][] = array('flag' => $iBitmask, 'time' => round(microtime(true) - $iTime, 6));

        return $bSingle ? end($aResult) : $aResult;
    }


    /**
     * Возвращает информацию о пути до файла класса.
     * Используется в {@link autoload автозагрузке}
     *
     * @static
     *
     * @param LsObject|string $oObject Объект - модуль, экшен, плагин, хук, сущность
     * @param int             $iArea   В какой области проверять (классы движка, общие классы, плагины)
     *
     * @return null|string
     */
    public static function GetClassPath($oObject, $iArea = self::CI_AREA_ACTUAL) {

        $aInfo = static::GetClassInfo($oObject, self::CI_OBJECT);
        $sPluginDir = '';
        if ($aInfo[self::CI_PLUGIN]) {
            $sPlugin = F::StrUnderscore($aInfo[self::CI_PLUGIN]);
            $aPlugins = F::GetPluginsList($iArea & self::CI_AREA_ALL_PLUGINS, false);
            if (isset($aPlugins[$sPlugin]['dirname'])) {
                $sPluginDir = $aPlugins[$sPlugin]['dirname'];
            } else {
                $sPluginDir = $sPlugin;
            }
        }
        $aPathSeek = Config::Get('path.root.seek');
        if ($aInfo[self::CI_ENTITY]) {
            // Сущность
            if ($aInfo[self::CI_PLUGIN]) {
                // Сущность модуля плагина
                $sFile = 'plugins/' . $sPluginDir
                    . '/classes/modules/' . strtolower($aInfo[self::CI_MODULE])
                    . '/entity/' . $aInfo[self::CI_ENTITY] . '.entity.class.php';
            } else {
                // Сущность модуля ядра
                $sFile = 'classes/modules/' . strtolower($aInfo[self::CI_MODULE])
                    . '/entity/' . $aInfo[self::CI_ENTITY] . '.entity.class.php';
            }
        } elseif ($aInfo[self::CI_MAPPER]) {
            // Маппер
            if ($aInfo[self::CI_PLUGIN]) {
                // Маппер модуля плагина
                $sFile = 'plugins/' . $sPluginDir
                    . '/classes/modules/' . strtolower($aInfo[self::CI_MODULE])
                    . '/mapper/' . $aInfo[self::CI_MAPPER] . '.mapper.class.php';
            } else {
                // Маппер модуля ядра
                $sFile = 'classes/modules/' . strtolower($aInfo[self::CI_MODULE])
                    . '/mapper/' . $aInfo[self::CI_MAPPER] . '.mapper.class.php';
            }
        } elseif ($aInfo[self::CI_ACTION]) {
            // Экшн
            if ($aInfo[self::CI_PLUGIN]) {
                // Экшн плагина
                $sFile = 'plugins/' . $sPluginDir
                    . '/classes/actions/Action' . $aInfo[self::CI_ACTION] . '.class.php';
            } else {
                // Экшн ядра
                $sFile = 'classes/actions/Action' . $aInfo[self::CI_ACTION] . '.class.php';
            }
        } elseif ($aInfo[self::CI_MODULE]) {
            // Модуль
            if ($aInfo[self::CI_PLUGIN]) {
                // Модуль плагина
                $sFile = 'plugins/' . $sPluginDir
                    . '/classes/modules/' . strtolower($aInfo[self::CI_MODULE])
                    . '/' . $aInfo[self::CI_MODULE] . '.class.php';
                ;
            } else {
                // Модуль ядра
                $sFile = 'classes/modules/' . strtolower($aInfo[self::CI_MODULE])
                    . '/' . $aInfo[self::CI_MODULE] . '.class.php';
            }
        } elseif ($aInfo[self::CI_HOOK]) {
            // Хук
            if ($aInfo[self::CI_PLUGIN]) {
                // Хук плагина
                $sFile = 'plugins/' . $sPluginDir
                    . '/classes/hooks/Hook' . $aInfo[self::CI_HOOK]
                    . '.class.php';
            } else {
                // Хук ядра
                $sFile = 'classes/hooks/Hook' . $aInfo[self::CI_HOOK] . '.class.php';
            }
        } elseif ($aInfo[self::CI_BLOCK]) {
            // LS-compatible
            if ($aInfo[self::CI_PLUGIN]) {
                // Блок плагина
                $sFile = 'plugins/' . $sPluginDir
                    . '/classes/blocks/Block' . $aInfo[self::CI_BLOCK]
                    . '.class.php';
            } else {
                // Блок ядра
                $sFile = 'classes/blocks/Block' . $aInfo[self::CI_BLOCK] . '.class.php';
            }
        } elseif ($aInfo[self::CI_WIDGET]) {
            // Виджет
            if ($aInfo[self::CI_PLUGIN]) {
                // Виджет плагина
                $sFile = 'plugins/' . $sPluginDir
                    . '/classes/widgets/Widget' . $aInfo[self::CI_WIDGET]
                    . '.class.php';
            } else {
                // Блок ядра
                $sFile = 'classes/widgets/Widget' . $aInfo[self::CI_WIDGET] . '.class.php';
            }
        } elseif ($aInfo[self::CI_PLUGIN]) {
            // Плагин
            $sFile = 'plugins/' . $sPluginDir
                . '/Plugin' . $aInfo[self::CI_PLUGIN]
                . '.class.php';
        } else {
            $sClassName = is_string($oObject) ? $oObject : get_class($oObject);
            $sFile = $sClassName . '.class.php';
            $aPathSeek = array(
                Config::Get('path.dir.engine') . '/classes/core/',
                Config::Get('path.dir.engine') . '/classes/abstract/',
            );
        }
        $sPath = F::File_Exists($sFile, $aPathSeek);

        return $sPath ? $sPath : null;
    }

    /**
     * @param string $sName
     * @param array  $aArgs
     *
     * @return mixed
     */
    public static function __callStatic($sName, $aArgs = array()) {

        if (substr($sName, 0, 6) == 'Module') {
            $oModule = static::Module($sName);
            if ($oModule) {
                return $oModule;
            }
        }
        return call_user_func_array(array(Engine::getInstance(), $sName), $aArgs);
    }

    /**
     * @param $sModuleName
     *
     * @return null|object
     */
    public static function Module($sModuleName) {

        return Engine::getInstance()->GetModule($sModuleName);
    }

    /**
     * Returns current user
     *
     * @return ModuleUser_EntityUser
     */
    public static function User() {

        return static::ModuleUser()->GetUserCurrent();
    }

    /**
     * If user is authorized
     *
     * @return bool
     */
    public static function IsUser() {

        $oUser = static::User();
        return $oUser;
    }

    /**
     * If user is authorized && admin
     *
     * @return bool
     */
    public static function IsAdmin() {

        $oUser = static::User();
        return ($oUser && $oUser->isAdministrator());
    }

    /**
     * If user is authorized && moderator
     *
     * @return bool
     */
    public static function IsModerator() {

        $oUser = static::User();
        return ($oUser && $oUser->isModerator());
    }

    /**
     * Is the user an administrator or a moderator?
     *
     * @return bool
     */
    public static function IsAdminOrModerator() {

        $oUser = static::User();
        return ($oUser && ($oUser->isAdministrator() || $oUser->isModerator()));
    }

    /**
     * If user is authorized && not admin
     *
     * @return bool
     */
    public static function IsNotAdmin() {

        $oUser = static::User();
        return ($oUser && !$oUser->isAdministrator());
    }

    /**
     * Returns UserId if user is authorized
     *
     * @return int|null
     */
    public static function UserId() {

        if ($oUser = static::User()) {
            return $oUser->GetId();
        }
        return null;
    }

    /**
     * If plugin is activated
     *
     * @param   string  $sPlugin
     * @return  bool
     */
    public static function ActivePlugin($sPlugin) {

        return static::Module('Plugin')->IsActivePlugin($sPlugin);
    }

    public static function GetActivePlugins() {

        return static::getInstance()->GetPlugins();
    }
}

// EOF
