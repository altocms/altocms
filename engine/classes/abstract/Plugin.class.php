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
                E::ModulePlugin()->Delegate($sObjectName, $sFrom, $sTo, get_class($this));
            }
        }

        $aInherits = $this->GetInherits();
        foreach ($aInherits as $aParams) {
            foreach ($aParams as $sFrom => $sTo) {
                E::ModulePlugin()->Inherit($sFrom, $sTo, get_class($this));
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

        return E::ModuleDatabase()->ExportSQL($sFilePath);
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

        return E::ModuleDatabase()->ExportSQLQuery($sSql);
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

        return E::ModuleDatabase()->IsTableExists($sTableName);
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

        return E::ModuleDatabase()->IsFieldExists($sTableName, $sFieldName);
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

        E::ModuleDatabase()->AddEnumType($sTableName, $sFieldName, $sType);
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
            $this->oPluginEntity = E::GetEntity('Plugin', $sPluginId);
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
        return null;
    }

    /**
     * @return string|null
     */
    public function EngineCompatible() {

        if ($oPluginEntity = $this->GetPluginEntity()) {
            return $oPluginEntity->EngineCompatible();
        }
        return null;
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
            $sPlugin = get_class($xPlugin);
        } else {
            $sPlugin = (string)$xPlugin;
        }
        if (substr($sPlugin, 0, 6) == 'Plugin') {
            if ($nUnderPos = strpos($sPlugin, '_')) {
                $sPluginName = substr($sPlugin, 6, $nUnderPos - 6);
            } else {
                $sPluginName = substr($sPlugin, 6);
            }
        } else {
            $sPluginName = $sPlugin;
        }

        return $sPluginName;
    }

    /**
     * Returns normalized dirname of plugin
     *
     * @param object|string $xPlugin
     *
     * @return string
     */
    static protected function _pluginDirName($xPlugin) {

        $sPluginName = self::_pluginName($xPlugin);
        if (strpbrk($sPluginName, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ')) {
            return F::StrUnderscore($sPluginName);
        }
        return $sPluginName;
    }

    /**
     * Returns normalized name of plugin
     *
     * @param object|string $xPlugin
     *
     * @return string
     */
    static public function GetPluginName($xPlugin) {

        return self::_pluginName($xPlugin);
    }

    /**
     * Returns decamelized name of plugin
     *
     * @param object|string $xPlugin
     *
     * @return string
     */
    static public function GetPluginDirName($xPlugin) {

        return self::_pluginDirName($xPlugin);
    }

    /**
     * Returns full dir path of plugin
     *
     * @param object|string $xPlugin
     *
     * @return string
     */
    static public function GetDir($xPlugin) {

        $aSeekDirs = Config::Get('path.root.seek');

        $sPluginDirName = self::_pluginDirName($xPlugin);
        $aPluginList = F::GetPluginsList(true, false);
        $sManifestFile = null;
        if (isset($aPluginList[$sPluginDirName]['dirname'])) {
            $sManifestFile = F::File_Exists('plugins/' . $aPluginList[$sPluginDirName]['dirname'] . '/plugin.xml', $aSeekDirs);
        }
        if (!$sManifestFile) {
            $sManifestFile = F::File_Exists('plugins/' . $sPluginDirName . '/plugin.xml', $aSeekDirs);
        }

        if ($sManifestFile) {
            return dirname($sManifestFile) . '/';
        }

        return null;
    }

    /**
     * Returns array of dirs with language files of the plugin
     *
     * @param object|string $xPlugin
     *
     * @return array
     */
    static public function GetDirLang($xPlugin) {

        $aResult = array();

        $aSeekDirs = Config::Get('path.root.seek');

        $sPluginDirName = self::_pluginDirName($xPlugin);
        $aPluginList = F::GetPluginsList(true, false);
        $sManifestFile = null;
        if (isset($aPluginList[$sPluginDirName]['dirname'])) {
            $sPluginDirName = $aPluginList[$sPluginDirName]['dirname'];
        }
        foreach($aSeekDirs as $sDir) {
            $sPluginDir = $sDir . '/plugins/' . $sPluginDirName . '/templates/language/';
            if (is_dir($sPluginDir)) {
                $aResult[] = F::File_NormPath($sPluginDir);
            }
        }
        return $aResult;
    }

    /**
     * Returns full URL path to plugin
     *
     * @param object|string $xPlugin
     *
     * @return string
     */
    static public function GetUrl($xPlugin) {

        $sPluginName = self::_pluginName($xPlugin);

        return F::File_Dir2Url(self::GetDir($sPluginName));
    }

    /**
     * @param object|string $xPlugin
     *
     * @return array
     */
    static public function GetSkins($xPlugin) {

        $sPluginName = self::_pluginName($xPlugin);
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

        $sPluginDirName = self::_pluginDirName($sPluginName);
        if (!$sCompatibility) {
            $sCompatibility = Config::Val('view.compatible', 'alto');
        }
        $sResult = Config::Get('plugin.' . $sPluginDirName . '.default.skin.' . $sCompatibility);
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
        $sViewSkin = Config::Get('view.skin');
        if (!isset(self::$aTemplateDir[$sViewSkin][$sPluginName][''])) {
            $aSkins = self::GetSkins($sPluginName);
            if ($aSkins && in_array(Config::Get('view.skin'), $aSkins)) {
                $sSkinName = Config::Get('view.skin');
            } else {
                $sSkinName = self::GetDefaultSkin($sPluginName, $sCompatibility);
            }

            $sDir = self::GetDir($sPluginName) . '/templates/skin/' . $sSkinName . '/';
            self::$aTemplateDir[$sViewSkin][$sPluginName][''] = is_dir($sDir) ? F::File_NormPath($sDir) : null;
        }
        return self::$aTemplateDir[$sViewSkin][$sPluginName][''];
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
        $sViewSkin = Config::Get('view.skin');
        if (!isset(self::$aTemplateDir[$sViewSkin][$sPluginName][$sTemplateName])) {
            $sPluginDir = self::GetDir($sPluginName);
            $aDirs = array(
                self::GetTemplateDir($sPluginName),
                $sPluginDir . '/templates/skin/' . Config::Get('view.skin'),
                $sPluginDir . '/templates/skin/' . self::GetDefaultSkin($sPluginName),
            );
            if (substr($sTemplateName, -4) == '.tpl') {
                $aSeekDirs = array();
                foreach ($aDirs as $sDir) {
                    $aSeekDirs[] = $sDir . '/tpls/';
                }
                $aSeekDirs = array_merge($aSeekDirs, $aDirs);
            } else {
                $aSeekDirs = $aDirs;
            }
            $sFile = F::File_Exists($sTemplateName, $aSeekDirs);
            if ($sFile) {
                self::$aTemplateDir[$sViewSkin][$sPluginName][$sTemplateName] = $sFile;
            } else {
                self::$aTemplateDir[$sViewSkin][$sPluginName][$sTemplateName] = $sPluginDir . '/templates/skin/' . self::GetDefaultSkin($sPluginName) . '/' . $sTemplateName;
            }
        }
        return self::$aTemplateDir[$sViewSkin][$sPluginName][$sTemplateName];
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
        $sViewSkin = Config::Get('view.skin');
        self::$aTemplateDir[$sViewSkin][$sPluginName][''] = $sTemplateDir;
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