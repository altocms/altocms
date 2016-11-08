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
 * Модуль управления плагинами
 *
 * @package engine.modules
 * @since   1.0
 */
class ModulePlugin extends Module {

    /**
     * Файл описания плагина
     *
     * @var string
     */
    const PLUGIN_XML_FILE = 'plugin.xml';

    /**
     * Путь к директории с плагинами
     *
     * @var string
     */
    protected $sPluginsCommonDir;

    /** @var  ModulePlugin_EntityPlugin[] List of plugins' enities */
    protected $aPluginsList;

    /** @var  array List of active plugins from PLUGINS.DAT */
    protected $aActivePlugins;

    protected $aEncodeIdChars = array(
        '.' => '-__-',
        '/' => '-_--',
        '\\' => '--_-',
    );

    /**
     * Список engine-rewrite`ов (модули, экшены, сущности, шаблоны)
     * Определяет типы объектов, которые может переопределить/унаследовать плагин
     *
     * @var array
     */
    protected $aDelegates
        = array(
            'module'   => array(),
            'mapper'   => array(),
            'action'   => array(),
            'entity'   => array(),
            'template' => array(),
            'block'    => array(),
        );

    /**
     * Стек наследований
     *
     * @var array
     */
    protected $aInherits = array();

    protected $aReverseMap = array();

    /**
     * Инициализация модуля
     */
    public function Init() {

        $this->sPluginsCommonDir = F::GetPluginsDir();
    }

    /**
     * Возвращает путь к папке с плагинами
     *
     * @return string
     */
    public function GetPluginsDir() {

        return $this->sPluginsCommonDir;
    }

    /**
     * Возвращает XML-манифест плагина
     *
     * @param string $sPluginId
     *
     * @return string|bool
     */
    public function GetPluginManifest($sPluginId) {

        $aPlugins = F::GetPluginsList(true, false);
        if (!empty($aPlugins[$sPluginId]['manifest'])) {
            $sXmlFile = $aPlugins[$sPluginId]['manifest'];
        } else {
            if (!empty($aPlugins[$sPluginId]['dirname'])) {
                $sPluginDir = $aPlugins[$sPluginId]['dirname'];
            } else {
                $sPluginDir = $sPluginId;
            }
            $sXmlFile = $this->sPluginsCommonDir . $sPluginDir . '/' . self::PLUGIN_XML_FILE;
        }
        return $this->GetPluginManifestFrom($sXmlFile);
    }

    /**
     * @param string $sPluginId
     *
     * @return string
     */
    public function GetPluginManifestFile($sPluginId) {

        $aPlugins = F::GetPluginsList(true, false);
        if (!empty($aPlugins[$sPluginId]['manifest'])) {
            $sXmlFile = $aPlugins[$sPluginId]['manifest'];
        } else {
            if (!empty($aPlugins[$sPluginId]['dirname'])) {
                $sPluginDir = $aPlugins[$sPluginId]['dirname'];
            } else {
                $sPluginDir = $sPluginId;
            }
            $sXmlFile = $this->sPluginsCommonDir . $sPluginDir . '/' . self::PLUGIN_XML_FILE;
        }
        return $sXmlFile;
    }

    /**
     * @param string $sPluginXmlFile
     *
     * @return string|bool
     */
    public function GetPluginManifestFrom($sPluginXmlFile) {

        if ($sPluginXmlFile && ($sXml = F::File_GetContents($sPluginXmlFile))) {
            return $sXml;
        }
        return false;
    }

    /**
     * Получает список информации обо всех плагинах, загруженных в plugin-директорию
     *
     * @param   array   $aFilter
     * @param   bool    $bAsArray
     *
     * @return  array
     */
    public function GetList($aFilter = array(), $bAsArray = true) {

        if (is_null($this->aPluginsList)) {
            // Если списка плагинов нет, то создаем его
            $aAllPlugins = F::GetPluginsList(true, false);
            $aActivePlugins = $this->GetActivePlugins();
            if ($aAllPlugins) {
                $iCnt = 0;
                foreach ($aAllPlugins as $sPluginId => $aPluginInfo) {
                    if ($bActive = isset($aActivePlugins[$sPluginId])) {
                        $nNum = ++$iCnt;
                    } else {
                        $nNum = -1;
                    }

                    // Создаем сущность плагина по его манифесту
                    /** @var ModulePlugin_EntityPlugin $oPluginEntity */
                    $oPluginEntity = E::GetEntity('Plugin', $aPluginInfo);
                    if ($oPluginEntity->GetId()) {
                        // Если сущность плагина создана, то...
                        $oPluginEntity->SetNum($nNum);
                        $oPluginEntity->SetIsActive($bActive);
                        $this->aPluginsList[$sPluginId] = $oPluginEntity;
                    }
                }
            } else {
                $this->aPluginsList = array();
            }
        }

        // Формируем список на выдачу
        $aPlugins = array();
        if (isset($aFilter['active']) || $bAsArray) {
            foreach ($this->aPluginsList as $sPluginId => $oPluginEntity) {
                if (!isset($aFilter['active'])
                    || ($aFilter['active'] && $oPluginEntity->GetIsActive())
                    || (!$aFilter['active'] && !$oPluginEntity->GetIsActive())
                ) {

                    if ($bAsArray) {
                        $aPlugins[$sPluginId] = $oPluginEntity->getAllProps();
                    } else {
                        $aPlugins[$sPluginId] = $oPluginEntity;
                    }
                }
            }
        } else {
            $aPlugins = $this->aPluginsList;
        }
        // Если нужно, то сортируем плагины
        if ($aPlugins && isset($aFilter['order'])) {
            if ($aFilter['order'] == 'name') {
                uasort($aPlugins, array($this, '_PluginCompareByName'));
            } elseif ($aFilter['order'] == 'priority') {
                uasort($aPlugins, array($this, '_PluginCompareByPriority'));
            }
        }
        return $aPlugins;
    }

    /**
     * Возвращает список плагинов
     *
     * @param   bool|null   - $bActive
     *
     * @return  ModulePlugin_EntityPlugin[]
     */
    public function GetPluginsList($bActive = null) {

        $aFilter = array('order' => 'priority');
        if (!is_null($bActive)) {
            $aFilter['active'] = (bool)$bActive;
        }
        $aPlugins = $this->GetList($aFilter, false);
        return $aPlugins;
    }

    /**
     * @param $aPlugin1
     * @param $aPlugin2
     *
     * @return int
     */
    public function _PluginCompareByName($aPlugin1, $aPlugin2) {

        if ((string)$aPlugin1['property']->name->data == (string)$aPlugin2['property']->name->data) {
            return 0;
        }
        return ((string)$aPlugin1['property']->name->data < (string)$aPlugin2['property']->name->data) ? -1 : 1;
    }

    /**
     * @param ModulePlugin_EntityPlugin|array $aPlugin1
     * @param ModulePlugin_EntityPlugin|array $aPlugin2
     *
     * @return int
     */
    public function _PluginCompareByPriority($aPlugin1, $aPlugin2) {

        if (is_object($aPlugin1)) {
            $aPlugin1 = $aPlugin1->getAllProps();
        }
        if (is_object($aPlugin2)) {
            $aPlugin2 = $aPlugin2->getAllProps();
        }
        $aPlugin1['is_active'] = (isset($aPlugin1['is_active']) ? $aPlugin1['is_active'] : false);
        $aPlugin2['is_active'] = (isset($aPlugin2['is_active']) ? $aPlugin2['is_active'] : false);

        if ($aPlugin1['priority'] == $aPlugin2['priority']) {
            if (!$aPlugin1['is_active'] && !$aPlugin2['is_active']) {
                // оба плагина не активированы - сортировка по имени
                if (($aPlugin1['id'] == $aPlugin2['id'])) {
                    return 0;
                } else {
                    return ($aPlugin1['id'] < $aPlugin2['id']) ? -1 : 1;
                }
            } elseif (!$aPlugin1['is_active'] || !$aPlugin2['is_active']) {
                // неактивированные плагины идут ниже
                if (!$aPlugin1['is_active'] == -1) {
                    return 1;
                } elseif (!$aPlugin2['is_active'] == -1) {
                    return -1;
                }
                return ($aPlugin1['num'] < $aPlugin2['num']) ? -1 : 1;
            }
        }
        if (strtolower($aPlugin1['priority']) == 'top') {
            return -1;
        } elseif (strtolower($aPlugin2['priority']) == 'top') {
            return 1;
        }
        return (($aPlugin1['priority'] > $aPlugin2['priority']) ? -1 : 1);
    }

    /**
     * @param string $sPluginId
     * @param bool   $bActive
     *
     * @return ModulePlugin_EntityPlugin|null
     */
    protected function _getPluginEntityById($sPluginId, $bActive) {

        $aPlugins = $this->GetPluginsList($bActive);
        if (!isset($aPlugins[$sPluginId])) {
            return null;
        }
        return $aPlugins[$sPluginId];
    }

    /**
     * @param string $sPluginId
     * @param bool   $bActive
     *
     * @return Plugin|null
     */
    protected function _getPluginById($sPluginId, $bActive) {

        $oPlugin = null;
        $oPluginEntity = $this->_getPluginEntityById($sPluginId, $bActive);

        if ($oPluginEntity) {
            $sClassName = $oPluginEntity->GetPluginClass();
            $sPluginClassFile = $oPluginEntity->GetPluginClassFile();
            if ($sClassName && $sPluginClassFile) {
                F::IncludeFile($sPluginClassFile);
                if (class_exists($sClassName, false)) {
                    /** @var Plugin $oPlugin */
                    $oPlugin = new $sClassName($oPluginEntity);
                }
            }
        }

        return $oPlugin;
    }

    /**
     * Активация плагина
     *
     * @param   string  $sPluginId  - код плагина
     *
     * @return  bool
     */
    public function Activate($sPluginId) {

        $aConditions = array(
            '<'  => 'lt', 'lt' => 'lt',
            '<=' => 'le', 'le' => 'le',
            '>'  => 'gt', 'gt' => 'gt',
            '>=' => 'ge', 'ge' => 'ge',
            '==' => 'eq', '=' => 'eq', 'eq' => 'eq',
            '!=' => 'ne', '<>' => 'ne', 'ne' => 'ne'
        );

        /** @var Plugin $oPlugin */
        $oPlugin = $this->_getPluginById($sPluginId, false);
        if ($oPlugin) {
            /** @var ModulePlugin_EntityPlugin $oPluginEntity */
            $oPluginEntity = $oPlugin->GetPluginEntity();

            // Проверяем совместимость с версией Alto
            if (!$oPluginEntity->EngineCompatible()) {
                E::ModuleMessage()->AddError(
                    E::ModuleLang()->Get(
                        'action.admin.plugin_activation_version_error',
                        array(
                             'version' => $oPluginEntity->RequiredAltoVersion(),
                        )
                    ),
                    E::ModuleLang()->Get('error'),
                    true
                );
                return false;
            }

            // * Проверяем системные требования
            if ($oPluginEntity->RequiredPhpVersion()) {
                // Версия PHP
                if (!version_compare(PHP_VERSION, $oPluginEntity->RequiredPhpVersion(), '>=')) {
                    E::ModuleMessage()->AddError(
                        E::ModuleLang()->Get(
                            'action.admin.plugin_activation_error_php',
                            array(
                                 'version' => $oPluginEntity->RequiredPhpVersion(),
                            )
                        ),
                        E::ModuleLang()->Get('error'),
                        true
                    );
                    return false;
                }
            }

            // * Проверяем наличие require-плагинов
            if ($aRequiredPlugins = $oPluginEntity->RequiredPlugins()) {
                $aActivePlugins = array_keys($this->GetActivePlugins());
                $iError = 0;
                foreach ($aRequiredPlugins as $oReqPlugin) {

                    // * Есть ли требуемый активный плагин
                    if (!in_array((string)$oReqPlugin, $aActivePlugins)) {
                        $iError++;
                        E::ModuleMessage()->AddError(
                            E::ModuleLang()->Get(
                                'action.admin.plugin_activation_requires_error',
                                array(
                                     'plugin' => ucfirst($oReqPlugin),
                                )
                            ),
                            E::ModuleLang()->Get('error'),
                            true
                        );
                    } // * Проверка требуемой версии, если нужно
                    else {
                        if (isset($oReqPlugin['name'])) {
                            $sReqPluginName = (string)$oReqPlugin['name'];
                        }
                        else {
                            $sReqPluginName = ucfirst($oReqPlugin);
                        }

                        if (isset($oReqPlugin['version'])) {
                            $sReqVersion = $oReqPlugin['version'];
                            if (isset($oReqPlugin['condition']) && array_key_exists((string)$oReqPlugin['condition'], $aConditions)) {
                                $sReqCondition = $aConditions[(string)$oReqPlugin['condition']];
                            } else {
                                $sReqCondition = 'eq';
                            }
                            $sClassName = "Plugin{$oReqPlugin}";
                            /** @var ModulePlugin_EntityPlugin $oReqPluginInstance */
                            $oReqPluginInstance = new $sClassName;

                            // Получаем версию требуемого плагина
                            $sReqPluginVersion = $oReqPluginInstance->GetVersion();

                            if (!$sReqPluginVersion) {
                                $iError++;
                                E::ModuleMessage()->AddError(
                                    E::ModuleLang()->Get(
                                        'action.admin.plugin_havenot_getversion_method',
                                        array('plugin' => $sReqPluginName)
                                    ),
                                    E::ModuleLang()->Get('error'),
                                    true
                                );
                            } else {
                                // * Если требуемый плагин возвращает версию, то проверяем ее
                                if (!version_compare($sReqPluginVersion, $sReqVersion, $sReqCondition)) {
                                    $sTextKey = 'action.admin.plugin_activation_reqversion_error_' . $sReqCondition;
                                    $iError++;
                                    E::ModuleMessage()->AddError(
                                        E::ModuleLang()->Get(
                                            $sTextKey,
                                            array(
                                                 'plugin'  => $sReqPluginName,
                                                 'version' => $sReqVersion
                                            )
                                        ),
                                        E::ModuleLang()->Get('error'),
                                        true
                                    );
                                }
                            }
                        }
                    }
                }
                if ($iError) {
                    return false;
                }
            }

            // * Проверяем, не вступает ли данный плагин в конфликт с уже активированными
            // * (по поводу объявленных делегатов)
            $aPluginDelegates = $oPlugin->GetDelegates();
            $iError = 0;
            foreach ($this->aDelegates as $sGroup => $aReplaceList) {
                $iCount = 0;
                if (isset($aPluginDelegates[$sGroup])
                    && is_array($aPluginDelegates[$sGroup])
                    && $iCount = sizeof($aOverlap = array_intersect_key($aReplaceList, $aPluginDelegates[$sGroup]))
                ) {
                    $iError += $iCount;
                    foreach ($aOverlap as $sResource => $aConflict) {
                        E::ModuleMessage()->AddError(
                            E::ModuleLang()->Get(
                                'action.admin.plugin_activation_overlap',
                                array(
                                    'resource' => $sResource,
                                    'delegate' => $aConflict['delegate'],
                                    'plugin'   => $aConflict['sign']
                                )
                            ),
                            E::ModuleLang()->Get('error'), true
                        );
                    }
                }
                if ($iCount) {
                    return false;
                }
            }
            $bResult = $oPlugin->Activate();
            if ($bResult && ($sVersion = $oPlugin->GetVersion())) {
                $oPlugin->WriteStorageVersion($sVersion);
                $oPlugin->WriteStorageDate();
            }
        } else {
            // * Исполняемый файл плагина не найден
            $sPluginClassFile = Plugin::GetPluginClass($sPluginId) . '.class.php';
            E::ModuleMessage()->AddError(
                E::ModuleLang()->Get('action.admin.plugin_file_not_found', array('file' => $sPluginClassFile)),
                E::ModuleLang()->Get('error'),
                true
            );
            return false;
        }

        if ($bResult) {
            // Запрещаем кеширование
            E::ModuleCache()->SetDesabled(true);
            // Надо обязательно очистить кеш здесь
            E::ModuleCache()->Clean();
            E::ModuleViewer()->ClearAll();

            // Переопределяем список активированных пользователем плагинов
            if (!$this->_addActivePlugins($oPluginEntity)) {
                E::ModuleMessage()->AddError(
                    E::ModuleLang()->Get('action.admin.plugin_write_error', array('file' => F::GetPluginsDatFile())),
                    E::ModuleLang()->Get('error'), true
                );
                $bResult = false;
            }
        }
        return $bResult;

    } // function Activate(...)

    /**
     * @param ModulePlugin_EntityPlugin $oPluginEntity
     *
     * @return ModulePlugin_EntityPlugin[]
     */
    protected function _addActivePlugins($oPluginEntity) {

        $aPluginsList = $this->GetPluginsList(true);
        $oPluginEntity->setIsActive(true);
        $aPluginsList[$oPluginEntity->GetId()] = $oPluginEntity;
        if (sizeof($aPluginsList)) {
            uasort($aPluginsList, array($this, '_PluginCompareByPriority'));
        }
        $aActivePlugins = array();
        /** @var ModulePlugin_EntityPlugin $oPluginEntity */
        foreach($aPluginsList as $sPlugin => $oPluginEntity) {
            $aActivePlugins[$sPlugin] = array(
                'id' => $oPluginEntity->GetId(),
                'dirname' => $oPluginEntity->GetDirname(),
                'name' => $oPluginEntity->GetName(),
            );
        }
        $this->SetActivePlugins($aActivePlugins);
        return $aPluginsList;
    }

    /**
     * Деактивация
     *
     * @param   string  $sPluginId
     * @param   bool    $bRemove
     *
     * @return  null|bool
     */
    public function Deactivate($sPluginId, $bRemove = false) {

        // get activated plugin by ID
        $oPlugin = $this->_getPluginById($sPluginId, true);

        if ($oPlugin) {
            /**
             * TODO: Проверять зависимые плагины перед деактивацией
             */
            $bResult = $oPlugin->Deactivate();
            if ($bRemove) {
                $oPlugin->Remove();
            }
        } else {
            // Исполняемый файл плагина не найден
            $sPluginClassFile = Plugin::GetPluginClass($sPluginId) . '.class.php';
            E::ModuleMessage()->AddError(
                E::ModuleLang()->Get('action.admin.plugin_file_not_found', array('file' => $sPluginClassFile)),
                E::ModuleLang()->Get('error'),
                true
            );
            return false;
        }

        if ($bResult) {
            // * Переопределяем список активированных пользователем плагинов
            $aActivePlugins = $this->GetActivePlugins();

            // * Вносим данные в файл о деактивации плагина
            unset($aActivePlugins[$sPluginId]);

            // * Сбрасываем весь кеш, т.к. могут быть закешированы унаследованые плагинами сущности
            E::ModuleCache()->SetDesabled(true);
            E::ModuleCache()->Clean();
            if (!$this->SetActivePlugins($aActivePlugins)) {
                E::ModuleMessage()->AddError(
                    E::ModuleLang()->Get('action.admin.plugin_activation_file_write_error'),
                    E::ModuleLang()->Get('error'),
                    true
                );
                return false;
            }

            // * Очищаем компилированные шаблоны Smarty
            E::ModuleViewer()->ClearSmartyFiles();
        }
        return $bResult;
    }

    /**
     * Возвращает список активированных плагинов в системе
     *
     * @param bool $bIdOnly
     *
     * @return array
     */
    public function GetActivePlugins($bIdOnly = false) {

        if (is_null($this->aActivePlugins)) {
            $this->aActivePlugins = F::GetPluginsList(false, $bIdOnly);
        }
        return $this->aActivePlugins;
    }

    /**
     * Активирован ли указанный плагин
     *
     * @param $sPlugin
     *
     * @return bool
     */
    public function IsActivePlugin($sPlugin) {

        $aPlugins = $this->GetActivePlugins();

        return isset($aPlugins[$sPlugin]);
    }

    /**
     * Записывает список активных плагинов в файл PLUGINS.DAT
     *
     * @param array|string $aPlugins    Список плагинов
     *
     * @return bool
     */
    public function SetActivePlugins($aPlugins) {

        if (!is_array($aPlugins)) {
            $sPlugin = (string)$aPlugins;
            $aPlugins = array(
                $sPlugin => array(
                    'id' => $sPlugin,
                    'dirname' => $sPlugin,
                ),
            );
        }
        //$aPlugins = array_unique(array_map('trim', $aPlugins));

        $aSaveData = array(
            date(';Y-m-d H:i:s'),
        );
        foreach($aPlugins as $sPlugin => $aPluginInfo) {
            $aSaveData[] = $sPlugin . ' '
                . (!empty($aPluginInfo['dirname']) ? $aPluginInfo['dirname'] : $sPlugin)
                . (!empty($aPluginInfo['name']) ? ' ;' . $aPluginInfo['name'] : '');
        }
        // * Записываем данные в файл PLUGINS.DAT
        $sFile = F::GetPluginsDatFile();
        if (F::File_PutContents($sFile, implode(PHP_EOL, $aSaveData)) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Удаляет плагины с сервера
     *
     * @param array $aPlugins    Список плагинов для удаления
     */
    public function Delete($aPlugins) {

        if (!is_array($aPlugins)) {
            $aPlugins = array($aPlugins);
        }

        $aActivePlugins = $this->GetActivePlugins();
        foreach ($aPlugins as $sPluginId) {
            if (!is_string($sPluginId)) {
                continue;
            }

            // * Если плагин активен, деактивируем его
            if (in_array($sPluginId, $aActivePlugins)) {
                $this->Deactivate($sPluginId);
            }
            $oPlugin = $this->_getPluginById($sPluginId, false);
            if ($oPlugin) {
                $oPlugin->Remove();
            }

            // * Удаляем директорию с плагином
            F::File_RemoveDir($this->sPluginsCommonDir . $sPluginId);
        }
    }

    /**
     * Перенаправление вызовов на модули, экшены, сущности
     *
     * @param  string $sType
     * @param  string $sFrom
     * @param  string $sTo
     * @param  string $sSign
     */
    public function Delegate($sType, $sFrom, $sTo, $sSign = __CLASS__) {

        // * Запрещаем неподписанные делегаты
        if (!is_string($sSign) || !strlen($sSign)) {
            return;
        }
        $sFrom = trim($sFrom);
        $sTo = trim($sTo);
        if (!in_array($sType, array_keys($this->aDelegates)) || !$sFrom || !$sTo) {
            return;
        }

        $this->aDelegates[$sType][$sFrom] = array(
            'delegate' => $sTo,
            'sign'     => $sSign
        );
        $this->aReverseMap['delegates'][$sTo] = $sFrom;
    }

    /**
     * Добавляет в стек наследника класса
     *
     * @param string $sFrom
     * @param string $sTo
     * @param string $sSign
     */
    public function Inherit($sFrom, $sTo, $sSign = __CLASS__) {

        if (!is_string($sSign) || !strlen($sSign)) {
            return;
        }
        $sFrom = trim($sFrom);
        $sTo = trim($sTo);
        if (!$sFrom || !$sTo) {
            return;
        }

        $this->aInherits[$sFrom]['items'][] = array(
            'inherit' => $sTo,
            'sign'    => $sSign
        );
        $this->aInherits[trim($sFrom)]['position'] = count($this->aInherits[trim($sFrom)]['items']) - 1;
        $this->aReverseMap['inherits'][$sTo][] = $sFrom;
    }

    /**
     * Return all inheritance rules
     *
     * @return array
     */
    public function GetInheritances() {

        return $this->aInherits;
    }

    /**
     * Return all delegation rules
     *
     * @return array
     */
    public function GetDelegations() {

        return $this->aDelegates;
    }

    /**
     * Получает следующего родителя у наследника.
     * ВНИМАНИЕ! Данный метод нужно вызвать только из __autoload()
     *
     * @param string $sFrom
     *
     * @return string
     */
    public function GetParentInherit($sFrom) {

        if (!isset($this->aInherits[$sFrom]['items']) || count($this->aInherits[$sFrom]['items']) <= 1
            || $this->aInherits[$sFrom]['position'] < 1
        ) {
            return $sFrom;
        }
        $this->aInherits[$sFrom]['position']--;
        return $this->aInherits[$sFrom]['items'][$this->aInherits[$sFrom]['position']]['inherit'];
    }

    /**
     * Возвращает список наследуемых классов
     *
     * @param string $sFrom
     *
     * @return null|array
     */
    public function GetInherits($sFrom) {

        if (isset($this->aInherits[trim($sFrom)])) {
            return $this->aInherits[trim($sFrom)]['items'];
        }
        return null;
    }

    /**
     * Возвращает последнего наследника в цепочке
     *
     * @param $sFrom
     *
     * @return null|string
     */
    public function GetLastInherit($sFrom) {

        if (isset($this->aInherits[trim($sFrom)])) {
            return $this->aInherits[trim($sFrom)]['items'][count($this->aInherits[trim($sFrom)]['items']) - 1];
        }
        return null;
    }

    /**
     * Возвращает делегат модуля, экшена, сущности.
     * Если делегат не определен, пытается найти наследника, иначе отдает переданный в качестве sender`a параметр
     *
     * @param  string $sType
     * @param  string $sFrom
     *
     * @return string
     */
    public function GetDelegate($sType, $sFrom) {

        if (isset($this->aDelegates[$sType][$sFrom]['delegate'])) {
            return $this->aDelegates[$sType][$sFrom]['delegate'];
        } elseif ($aInherit = $this->GetLastInherit($sFrom)) {
            return $aInherit['inherit'];
        }
        return $sFrom;
    }

    /**
     * @param string $sType
     * @param string $sFrom
     *
     * @return array|null
     */
    public function GetDelegates($sType, $sFrom) {

        if (isset($this->aDelegates[$sType][$sFrom]['delegate'])) {
            return array($this->aDelegates[$sType][$sFrom]['delegate']);
        } else {
            if ($aInherits = $this->GetInherits($sFrom)) {
                $aReturn = array();
                foreach (array_reverse($aInherits) as $v) {
                    $aReturn[] = $v['inherit'];
                }
                return $aReturn;
            }
        }
        return null;
    }

    /**
     * Возвращает цепочку делегатов
     *
     * @param string $sType
     * @param string $sTo
     *
     * @return array
     */
    public function GetDelegationChain($sType, $sTo) {

        $sRootDelegater = $this->GetRootDelegater($sType, $sTo);
        return $this->collectAllDelegatesRecursive($sType, array($sRootDelegater));
    }

    /**
     * Возвращает делегируемый класс
     *
     * @param string $sType
     * @param string $sTo
     *
     * @return string
     */
    public function GetRootDelegater($sType, $sTo) {

        if ($sTo) {
            $sItem = $sTo;
            $sItemDelegater = $this->GetDelegater($sType, $sTo);
            while (empty($sRootDelegater)) {
                if ($sItem == $sItemDelegater) {
                    $sRootDelegater = $sItem;
                }
                $sItem = $sItemDelegater;
                $sItemDelegater = $this->GetDelegater($sType, $sItemDelegater);
            }
            return $sRootDelegater;
        }
        return $sTo;
    }

    /**
     * Составляет цепочку делегатов
     *
     * @param string $sType
     * @param array  $aDelegates
     *
     * @return array
     */
    public function collectAllDelegatesRecursive($sType, $aDelegates) {

        foreach ($aDelegates as $sClass) {
            if ($aNewDelegates = $this->GetDelegates($sType, $sClass)) {
                $aDelegates = array_merge($this->collectAllDelegatesRecursive($sType, $aNewDelegates), $aDelegates);
            }
        }
        return $aDelegates;
    }

    /**
     * Возвращает делегирующий объект по имени делегата
     *
     * @param  string $sType Объект
     * @param  string $sTo   Делегат
     *
     * @return string
     */
    public function GetDelegater($sType, $sTo) {

        $aDelegateMapper = array();
        foreach ($this->aDelegates[$sType] as $sFrom => $aInfo) {
            if ($aInfo['delegate'] == $sTo) {
                $aDelegateMapper[$sFrom] = $aInfo;
            }
        }
        if ($aDelegateMapper) {
            $aKeys = array_keys($aDelegateMapper);
            return reset($aKeys);
        }
        foreach ($this->aInherits as $sFrom => $aInfo) {
            $aInheritMapper = array();
            foreach ($aInfo['items'] as $iOrder => $aData) {
                if ($aData['inherit'] == $sTo) {
                    $aInheritMapper[$iOrder] = $aData;
                }
            }
            if ($aInheritMapper) {
                return $sFrom;
            }
        }
        return $sTo;
    }

    /**
     * Возвращает подпись делегата модуля, экшена, сущности.
     *
     * @param  string $sType
     * @param  string $sFrom
     *
     * @return string|null
     */
    public function GetDelegateSign($sType, $sFrom) {

        if (isset($this->aDelegates[$sType][$sFrom]['sign'])) {
            return $this->aDelegates[$sType][$sFrom]['sign'];
        }
        if ($aInherit = $this->GetLastInherit($sFrom)) {
            return $aInherit['sign'];
        }
        return null;
    }

    /**
     * Возвращает true, если установлено правило делегирования
     * и класс является базовым в данном правиле
     *
     * @param  string $sType
     * @param  string $sFrom
     *
     * @return bool
     */
    public function isDelegater($sType, $sFrom) {

        if (isset($this->aDelegates[$sType][$sFrom]['delegate'])) {
            return true;
        } elseif ($aInherit = $this->GetLastInherit($sFrom)) {
            return true;
        }
        return false;
    }

    /**
     * Возвращает true, если устано
     *
     * @param  string $sType
     * @param  string $sTo
     *
     * @return bool
     */
    public function isDelegated($sType, $sTo) {

        // * Фильтруем маппер делегатов/наследников
        $aDelegateMapper = array();
        foreach ($this->aDelegates[$sType] as $sKey => $xVal) {
            if ($xVal['delegate'] == $sTo) {
                $aDelegateMapper[$sKey] = $xVal;
            }
        }
        if (is_array($aDelegateMapper) && count($aDelegateMapper)) {
            return true;
        }
        foreach ($this->aInherits as $k => $v) {
            $aInheritMapper = array();
            foreach ($v['items'] as $sKey => $xVal) {
                if ($xVal['inherit'] == $sTo) {
                    $aInheritMapper[$sKey] = $xVal;
                }
            }
            if (is_array($aInheritMapper) && count($aInheritMapper)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Возвращает список объектов, доступных для делегирования
     *
     * @return string[]
     */
    public function GetDelegateObjectList() {

        return array_keys($this->aDelegates);
    }

    /**
     * Рекурсивно ищет манифест плагина в подпапках
     *
     * @param   string  $sDir
     *
     * @return  string|bool
     */
    protected function _seekManifest($sDir) {

        if ($aFiles = glob($sDir . self::PLUGIN_XML_FILE)) {
            return array_shift($aFiles);
        } else {
            $aSubDirs = glob($sDir . '*', GLOB_ONLYDIR);
            foreach ($aSubDirs as $sSubDir) {
                if ($sFile = $this->_seekManifest($sSubDir . '/')) {
                    return $sFile;
                }
            }
        }
        return false;
    }

    /**
     * Encode plugin id
     *
     * @param $xPluginId
     *
     * @return array|mixed
     */
    public function EncodeId($xPluginId) {

        if (is_array($xPluginId)) {
            $aResult = array();
            foreach($xPluginId as $iIdx => $sPluginId) {
                $aResult = $this->EncodeId($sPluginId);
            }
            return $aResult;
        } else {
            return str_replace(array_keys($this->aEncodeIdChars), array_values($this->aEncodeIdChars), $xPluginId);
        }
    }

    /**
     * Decode plugin id
     *
     * @param $xPluginId
     *
     * @return array|mixed
     */
    public function DecodeId($xPluginId) {

        if (is_array($xPluginId)) {
            $aResult = array();
            foreach($xPluginId as $xKey => $sPluginId) {
                $aResult[$xKey] = $this->DecodeId($sPluginId);
            }
            return $aResult;
        } else {
            return str_replace(array_values($this->aEncodeIdChars), array_keys($this->aEncodeIdChars), $xPluginId);
        }
    }

    /**
     * Распаковывает архив с плагином и перемещает его в нужную папку
     *
     * @param $sPackFile
     *
     * @return  bool
     */
    public function UnpackPlugin($sPackFile) {

        $zip = new ZipArchive;
        if ($zip->open($sPackFile) === true) {
            $sUnpackDir = F::File_NormPath(dirname($sPackFile) . '/_unpack/');
            if (!$zip->extractTo($sUnpackDir)) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.err_extract_zip_file'), E::ModuleLang()->Get('error'));
                return false;
            } else {
                // Ищем в папках XML-манифест
                $aDirs = glob($sUnpackDir . '*', GLOB_ONLYDIR);
                $sXmlFile = '';
                if ($aDirs) {
                    foreach ($aDirs as $sDir) {
                        if ($sXmlFile = $this->_seekManifest($sDir . '/')) {
                            break;
                        }
                    }
                }
                if (!$sXmlFile) {
                    E::ModuleMessage()->AddError(
                        E::ModuleLang()->Get('action.admin.file_not_found', array('file' => self::PLUGIN_XML_FILE)),
                        E::ModuleLang()->Get('error')
                    );
                    return false;
                }
                $sPluginSrc = dirname($sXmlFile);

                // try to define plugin's dirname
                $oXml = @simplexml_load_file($sXmlFile);
                if (!$oXml) {
                    E::ModuleMessage()->AddError(
                        E::ModuleLang()->Get('action.admin.err_read_xml', array('file' => $sXmlFile)),
                        E::ModuleLang()->Get('error')
                    );
                    return false;
                }
                $sPluginDir = (string)$oXml->dirname;
                if (!$sPluginDir) {
                    $sPluginDir = (string)$oXml->id;
                }
                if (!$sPluginDir) {
                    $sPluginDir = basename($sPluginSrc);
                }
                // Old style compatible
                if ($sPluginDir && preg_match('/^alto-plugin-([a-z]+)-[\d\.]+$/', $sPluginDir, $aM)) {
                    $sPluginDir = $aM[1];
                }

                $sPluginPath = $this->GetPluginsDir() . '/' . $sPluginDir . '/';
                if (F::File_CopyDir($sPluginSrc, $sPluginPath)) {
                    E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.plugin_added_ok'));
                } else {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.plugin_added_err'), E::ModuleLang()->Get('error'));
                }
            }
            $zip->close();
        } else {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.err_open_zip_file'), E::ModuleLang()->Get('error'));
        }
        return true;
    }
}

// EOF
