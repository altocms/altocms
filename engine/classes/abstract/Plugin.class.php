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
 * Абстракция плагина, от которой наследуются все плагины
 * Файл плагина должен находиться в каталоге /plugins/plgname/ и иметь название PluginPlgname.class.php
 *
 * @package engine
 * @since   1.0
 */
abstract class Plugin extends LsObject {
    /**
     * Список скинов плагинов
     *
     * @var array
     */
    static protected $aSkins = array();
    /**
     * Путь к шаблонам с учетом наличия соответствующего skin`a
     *
     * @var array
     */
    static protected $aTemplateDir = array();
    /**
     * Web-адреса шаблонов с учетом наличия соответствующего skin`a
     *
     * @var array
     */
    static protected $aTemplateUrl = array();
    /**
     * Массив делегатов плагина
     *
     * @var array
     */
    protected $aDelegates = array();
    /**
     * Массив наследуемых классов плагина
     *
     * @var array
     */
    protected $aInherits = array();

    protected $oPluginEntity;

    public function __construct() {

        $this->oPluginEntity = $this->GetPluginEntity();
    }

    /**
     * Метод инициализации плагина
     *
     */
    public function Init() {
    }

    /**
     * Передает информацию о делегатах в модуль ModulePlugin
     * Вызывается Engine перед инициализацией плагина
     *
     * @see Engine::LoadPlugins
     */
    final function Delegate() {

        $aDelegates = $this->GetDelegates();
        foreach ($aDelegates as $sObjectName => $aParams) {
            foreach ($aParams as $sFrom => $sTo) {
                $this->Plugin_Delegate($sObjectName, $sFrom, $sTo, get_class($this));
            }
        }

        $aInherits = $this->GetInherits();
        foreach ($aInherits as $aParams) {
            foreach ($aParams as $sFrom => $sTo) {
                $this->Plugin_Inherit($sFrom, $sTo, get_class($this));
            }
        }
    }

    /**
     * Возвращает массив наследников
     *
     * @return array
     */
    final function GetInherits() {

        $aReturn = array();
        if (is_array($this->aInherits) && count($this->aInherits)) {
            foreach ($this->aInherits as $sObjectName => $aParams) {
                if (is_array($aParams) && count($aParams)) {
                    foreach ($aParams as $sFrom => $sTo) {
                        if (is_int($sFrom)) {
                            $sFrom = $sTo;
                            $sTo = null;
                        }
                        list($sFrom, $sTo) = $this->MakeDelegateParams($sObjectName, $sFrom, $sTo);
                        $aReturn[$sObjectName][$sFrom] = $sTo;
                    }
                }
            }
        }
        return $aReturn;
    }

    /**
     * Возвращает массив делегатов
     *
     * @return array
     */
    final function GetDelegates() {

        $aReturn = array();
        if (is_array($this->aDelegates) && count($this->aDelegates)) {
            foreach ($this->aDelegates as $sObjectName => $aParams) {
                if (is_array($aParams) && count($aParams)) {
                    foreach ($aParams as $sFrom => $sTo) {
                        if (is_int($sFrom)) {
                            $sFrom = $sTo;
                            $sTo = null;
                        }
                        list($sFrom, $sTo) = $this->MakeDelegateParams($sObjectName, $sFrom, $sTo);
                        $aReturn[$sObjectName][$sFrom] = $sTo;
                    }
                }
            }
        }
        return $aReturn;
    }

    /**
     * Преобразовывает краткую форму имен делегатов в полную
     *
     * @param string $sObjectName Название типа объекта делегата
     *
     * @see ModulePlugin::aDelegates
     *
     * @param string $sFrom       Что делегируется
     * @param string $sTo         Кому делегируется
     *
     * @return array
     */
    public function MakeDelegateParams($sObjectName, $sFrom, $sTo) {
        /**
         * Если не указан делегат то, считаем, что делегатом является
         * одноименный объект текущего плагина
         */
        if ($sObjectName == 'template') {
            if (!$sTo) {
                $sTo = self::GetTemplateFile(get_class($this), $sFrom);
            } else {
                if (strpos($sTo, '_') === 0) {
                    $sTo = self::GetTemplateFile(get_class($this), substr($sTo, 1));
                }
            }
        } else {
            if (!$sTo) {
                $sTo = get_class($this) . '_' . $sFrom;
            } else {
                if (strpos($sTo, '_') === 0) {
                    $sTo = get_class($this) . $sTo;
                }
            }
        }
        return array($sFrom, $sTo);
    }

    /**
     * Метод активации плагина
     *
     * @return bool
     */
    public function Activate() {

        return true;
    }

    /**
     * Метод деактивации плагина
     *
     * @return bool
     */
    public function Deactivate() {

        return true;
    }

    /**
     * Метод удаления плагина
     *
     * @return bool
     */
    public function Remove() {

        return true;
    }

    /**
     * Транслирует на базу данных запросы из указанного файла
     * @see ModuleDatabase::ExportSQL
     *
     * @param  string $sFilePath    Полный путь до файла с SQL
     *
     * @return array
     */
    protected function ExportSQL($sFilePath) {

        return $this->Database_ExportSQL($sFilePath);
    }

    /**
     * Выполняет SQL
     *
     * @see ModuleDatabase::ExportSQLQuery
     *
     * @param string $sSql    Строка SQL запроса
     *
     * @return array
     */
    protected function ExportSQLQuery($sSql) {

        return $this->Database_ExportSQLQuery($sSql);
    }

    /**
     * Проверяет наличие таблицы в БД
     * @see ModuleDatabase::isTableExists
     *
     * @param string $sTableName    - Название таблицы, необходимо перед именем таблицы добавлять "prefix_",
     *                                это позволит учитывать произвольный префикс таблиц у пользователя
     * <pre>
     *                              prefix_topic
     * </pre>
     *
     * @return bool
     */
    protected function isTableExists($sTableName) {

        return $this->Database_isTableExists($sTableName);
    }

    /**
     * Проверяет наличие поля в таблице
     * @see ModuleDatabase::isFieldExists
     *
     * @param string $sTableName    - Название таблицы, необходимо перед именем таблицы добавлять "prefix_",
     *                                это позволит учитывать произвольный префикс таблиц у пользователя
     * @param string $sFieldName    - Название поля в таблице
     *
     * @return bool
     */
    protected function isFieldExists($sTableName, $sFieldName) {

        return $this->Database_isFieldExists($sTableName, $sFieldName);
    }

    /**
     * Добавляет новый тип в поле enum(перечисление)
     *
     * @see ModuleDatabase::addEnumType
     *
     * @param string $sTableName       - Название таблицы, необходимо перед именем таблицы добавлять "prefix_",
     *                                   это позволит учитывать произвольный префикс таблиц у пользователя
     * @param string $sFieldName       - Название поля в таблице
     * @param string $sType            - Название типа
     */
    protected function addEnumType($sTableName, $sFieldName, $sType) {

        $this->Database_addEnumType($sTableName, $sFieldName, $sType);
    }

    /**
     * Returns name of plugin
     *
     * @param bool $bSkipPrefix
     *
     * @return string
     */
    public function GetName($bSkipPrefix = true) {

        $sName = get_class($this);
        return $bSkipPrefix ? substr($sName, 6) : $sName;
    }

    public function GetPluginEntity() {

        if (!$this->oPluginEntity) {
            $sPluginId = F::StrUnderscore($this->GetName());
            $this->oPluginEntity = Engine::GetEntity('Plugin', $sPluginId);
        }
        return $this->oPluginEntity;
    }

    /**
     * Возвращает версию плагина
     *
     * @return string|null
     */
    public function GetVersion() {

        if ($oPluginEntity = $this->GetPluginEntity()) {
            return $oPluginEntity->GetVersion();
        }
    }

    public function EngineCompatible() {

        if ($oPluginEntity = $this->GetPluginEntity()) {
            return $oPluginEntity->EngineCompatible();
        }
    }

    /**
     * Returns normalized name of plugin
     *
     * @param object|string $xPlugin
     *
     * @return string
     */
    static protected function _pluginName($xPlugin) {

        if (is_object($xPlugin)) {
            return get_class($xPlugin);
        }
        if (substr($xPlugin, 0, 6) == 'Plugin') {
            if ($nUnderPos = strpos($xPlugin, '_')) {
                $sResult = strtolower(substr($xPlugin, 6, $nUnderPos - 6));
            } else {
                $sResult = strtolower(substr($xPlugin, 6));
            }
        } else {
            $sResult = strtolower($xPlugin);
        }
        return $sResult;
    }

    static public function GetPluginName($sPluginName) {
        return self::_pluginName($sPluginName);
    }

    /**
     * Возвращает полный серверный путь до плагина
     *
     * @param string $sPluginName
     *
     * @return string
     */
    static public function GetDir($sPluginName) {

        $sPluginName = self::_pluginName($sPluginName);

        $aDirs = Config::Get('path.root.seek');
        foreach($aDirs as $sDir) {
            $sPluginDir = $sDir . '/plugins/' . $sPluginName . '/';
            if (is_file($sPluginDir . 'plugin.xml')) {
                return F::File_NormPath($sPluginDir);
            }
        }
    }

    /**
     * Возвращает полный web-адрес до плагина
     *
     * @param string $sPluginName
     *
     * @return string
     */
    static public function GetUrl($sPluginName) {

        $sPluginName = self::_pluginName($sPluginName);

        return F::File_Dir2Url(self::GetDir($sPluginName));
    }

    /**
     * @param string $sPluginName
     *
     * @return array
     */
    static public function GetSkins($sPluginName) {

        $sPluginName = self::_pluginName($sPluginName);
        if (!isset(self::$aSkins[$sPluginName])) {
            $sPluginDir = self::GetDir($sPluginName);
            $aPaths = glob($sPluginDir . '/templates/skin/*', GLOB_ONLYDIR);
            if ($aPaths) {
                $aDirs = array_map('basename', $aPaths);
            } else {
                $aDirs = array();
            }
            self::$aSkins[$sPluginName] = $aDirs;
        }
        return self::$aSkins[$sPluginName];
    }

    /**
     * Returns default skin name
     *
     * @param string $sPluginName
     * @param string $sCompatibility
     *
     * @return string
     */
    static public function GetDefaultSkin($sPluginName, $sCompatibility = null) {

        $sPluginName = self::_pluginName($sPluginName);
        if (!$sCompatibility) {
            $sCompatibility = Config::Val('view.compatible', 'alto');
        }
        $sResult = Config::Get('plugin.' . $sPluginName . '.default.skin.' . $sCompatibility);
        if (!$sResult) {
            $sResult = 'default';
        }
        return $sResult;
    }

    /**
     * Возвращает правильный серверный путь к директории шаблонов с учетом текущего скина
     * Если используется скин, которого нет в плагине, то возвращается путь до скина плагина 'default'
     *
     * @param string $sPluginName    Название плагина или его класс
     * @param string $sCompatibility
     *
     * @return string|null
     */
    static public function GetTemplateDir($sPluginName, $sCompatibility = null) {

        $sPluginName = self::_pluginName($sPluginName);
        if (!isset(self::$aTemplateDir[$sPluginName][''])) {
            $aSkins = self::GetSkins($sPluginName);
            $sSkinName = ($aSkins && in_array(Config::Get('view.skin'), $aSkins))
                ? Config::Get('view.skin')
                : self::GetDefaultSkin($sPluginName, $sCompatibility);

            $sDir = self::GetDir($sPluginName) . '/templates/skin/' . $sSkinName . '/';
            self::$aTemplateDir[$sPluginName][''] = is_dir($sDir) ? F::File_NormPath($sDir) : null;
        }
        return self::$aTemplateDir[$sPluginName][''];
    }

    /**
     * Seek template for current or default skin
     *
     * @param string $sPluginName
     * @param string $sTemplateName
     *
     * @return string
     */
    static public function GetTemplateFile($sPluginName, $sTemplateName) {

        $sPluginName = self::_pluginName($sPluginName);
        if (!isset(self::$aTemplateDir[$sPluginName][$sTemplateName])) {
            $sPluginDir = self::GetDir($sPluginName);
            $aDirs = array(
                self::GetTemplateDir($sPluginName),
                $sPluginDir . '/templates/skin/' . Config::Get('view.skin'),
                $sPluginDir . '/templates/skin/' . self::GetDefaultSkin($sPluginName),
            );
            $sFile = F::File_Exists($sTemplateName, $aDirs);
            if ($sFile) {
                self::$aTemplateDir[$sPluginName][$sTemplateName] = $sFile;
            } else {
                self::$aTemplateDir[$sPluginName][$sTemplateName] = $sPluginDir . '/templates/skin/' . self::GetDefaultSkin($sPluginName) . '/' . $sTemplateName;
            }
        }
        return self::$aTemplateDir[$sPluginName][$sTemplateName];
    }

    /**
     * LS-compatible
     */
    static public function GetTemplatePath($sName) {

        return self::GetTemplateDir($sName);
    }

    /**
     * Возвращает правильный web-адрес директории шаблонов
     * Если пользователь использует шаблон которого нет в плагине, то возвращает путь до шабона плагина 'default'
     *
     * @param string $sPluginName Название плагина или его класс
     * @param string $sCompatibility
     *
     * @return string
     */
    static public function GetTemplateUrl($sPluginName, $sCompatibility = null) {

        $sPluginName = self::_pluginName($sPluginName);
        if (!isset(self::$aTemplateUrl[$sPluginName])) {
            if ($sTemplateDir = self::GetTemplateDir($sPluginName, $sCompatibility)) {
                self::$aTemplateUrl[$sPluginName] = F::File_Dir2Url($sTemplateDir);
            } else {
                self::$aTemplateUrl[$sPluginName] = null;
            }
        }
        return self::$aTemplateUrl[$sPluginName];
    }

    /**
     * Устанавливает значение серверного пути до шаблонов плагина
     *
     * @param  string $sPluginName  Имя плагина
     * @param  string $sTemplateDir Серверный путь до шаблона
     *
     * @return bool
     */
    static public function SetTemplateDir($sPluginName, $sTemplateDir) {

        if (!is_dir($sTemplateDir)) {
            return false;
        }
        self::$aTemplateDir[$sPluginName][''] = $sTemplateDir;
        return true;
    }

    /**
     * Устанавливает значение web-пути до шаблонов плагина
     *
     * @param  string $sPluginName  Имя плагина
     * @param  string $sTemplateUrl Серверный путь до шаблона
     */
    static public function SetTemplateUrl($sPluginName, $sTemplateUrl) {

        self::$aTemplateUrl[$sPluginName] = $sTemplateUrl;
    }

    /*************************************************************
     * LS-compatible
     */
    static public function GetTemplateWebPath($sName) {

        return self::GetTemplateUrl($sName);
    }

    static public function GetWebPath($sName) {

        return self::GetUrl($sName);
    }

    static public function GetPath($sName) {

        return self::GetDir($sName);
    }

    static public function SetTemplatePath($sName, $sTemplatePath) {

        return self::SetTemplateDir($sName, $sTemplatePath);
    }

    static public function SetTemplateWebPath($sName, $sTemplatePath) {

        return self::SetTemplateUrl($sName, $sTemplatePath);
    }

}

// EOF