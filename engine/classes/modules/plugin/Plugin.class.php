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
    public function init() {

        $this->sPluginsCommonDir = F::GetPluginsDir();
    }

    /**
     * Возвращает путь к папке с плагинами
     *
     * @return string
     */
    public function getPluginsDir() {

        return $this->sPluginsCommonDir;
    }

    /**
     * Возвращает XML-манифест плагина
     *
     * @param string $sPluginId
     *
     * @return string|bool
     */
    public function getPluginManifest($sPluginId) {

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
        return $this->getPluginManifestFrom($sXmlFile);
    }

    /**
     * @param string $sPluginId
     *
     * @return string
     */
    public function getPluginManifestFile($sPluginId) {

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
    public function getPluginManifestFrom($sPluginXmlFile) {

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
    public function getList($aFilter = array(), $bAsArray = true) {

        if (is_null($this->aPluginsList)) {
            // Если списка плагинов нет, то создаем его
            $aAllPlugins = F::GetPluginsList(true, false);
            $aActivePlugins = $this->getActivePlugins();
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
                    if ($oPluginEntity->getId()) {
                        // Если сущность плагина создана, то...
                        $oPluginEntity->setNum($nNum);
                        $oPluginEntity->setActive($bActive);
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
                uasort($aPlugins, array($this, '_pluginCompareByName'));
            } elseif ($aFilter['order'] == 'priority') {
                uasort($aPlugins, array($this, '_pluginCompareByPriority'));
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
    public function getPluginsList($bActive = null) {

        $aFilter = array('order' => 'priority');
        if (!is_null($bActive)) {
            $aFilter['active'] = (bool)$bActive;
        }
        $aPlugins = $this->getList($aFilter, false);
        
        return $aPlugins;
    }

    /**
     * @param $aPlugin1
     * @param $aPlugin2
     *
     * @return int
     */
    public function _pluginCompareByName($aPlugin1, $aPlugin2) {

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
    public function _pluginCompareByPriority($aPlugin1, $aPlugin2) {

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

        $aPlugins = $this->getPluginsList($bActive);
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
            $sClassName = $oPluginEntity->getPluginClass();
            $sPluginClassFile = $oPluginEntity->getPluginClassFile();
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
    public function activate($sPluginId) {

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
            $oPluginEntity = $oPlugin->getPluginEntity();

            // Проверяем совместимость с версией Alto
            if (!$oPluginEntity->engineCompatible()) {
                E::ModuleMessage()->addError(
                    E::ModuleLang()->get(
                        'action.admin.plugin_activation_version_error',
                        array(
                             'version' => $oPluginEntity->requiredAltoVersion(),
                        )
                    ),
                    E::ModuleLang()->get('error'),
                    true
                );
                return false;
            }

            // * Проверяем системные требования
            if ($oPluginEntity->requiredPhpVersion()) {
                // Версия PHP
                if (!version_compare(PHP_VERSION, $oPluginEntity->requiredPhpVersion(), '>=')) {
                    E::ModuleMessage()->addError(
                        E::ModuleLang()->get(
                            'action.admin.plugin_activation_error_php',
                            array(
                                 'version' => $oPluginEntity->requiredPhpVersion(),
                            )
                        ),
                        E::ModuleLang()->get('error'),
                        true
                    );
                    return false;
                }
            }

            // * Проверяем наличие require-плагинов
            if ($aRequiredPlugins = $oPluginEntity->requiredPlugins()) {
                $aActivePlugins = array_keys($this->getActivePlugins());
                $iError = 0;
                foreach ($aRequiredPlugins as $oReqPlugin) {

                    // * Есть ли требуемый активный плагин
                    if (!in_array((string)$oReqPlugin, $aActivePlugins)) {
                        $iError++;
                        E::ModuleMessage()->addError(
                            E::ModuleLang()->get(
                                'action.admin.plugin_activation_requires_error',
                                array(
                                     'plugin' => ucfirst($oReqPlugin),
                                )
                            ),
                            E::ModuleLang()->get('error'),
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
                            $sReqPluginVersion = $oReqPluginInstance->getVersion();

                            if (!$sReqPluginVersion) {
                                $iError++;
                                E::ModuleMessage()->addError(
                                    E::ModuleLang()->get(
                                        'action.admin.plugin_havenot_getversion_method',
                                        array('plugin' => $sReqPluginName)
                                    ),
                                    E::ModuleLang()->get('error'),
                                    true
                                );
                            } else {
                                // * Если требуемый плагин возвращает версию, то проверяем ее
                                if (!version_compare($sReqPluginVersion, $sReqVersion, $sReqCondition)) {
                                    $sTextKey = 'action.admin.plugin_activation_reqversion_error_' . $sReqCondition;
                                    $iError++;
                                    E::ModuleMessage()->addError(
                                        E::ModuleLang()->get(
                                            $sTextKey,
                                            array(
                                                 'plugin'  => $sReqPluginName,
                                                 'version' => $sReqVersion
                                            )
                                        ),
                                        E::ModuleLang()->get('error'),
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
            $aPluginDelegates = $oPlugin->getDelegates();
            $iError = 0;
            foreach ($this->aDelegates as $sGroup => $aReplaceList) {
                $iCount = 0;
                if (isset($aPluginDelegates[$sGroup])
                    && is_array($aPluginDelegates[$sGroup])
                    && $iCount = sizeof($aOverlap = array_intersect_key($aReplaceList, $aPluginDelegates[$sGroup]))
                ) {
                    $iError += $iCount;
                    foreach ($aOverlap as $sResource => $aConflict) {
                        E::ModuleMessage()->addError(
                            E::ModuleLang()->get(
                                'action.admin.plugin_activation_overlap',
                                array(
                                    'resource' => $sResource,
                                    'delegate' => $aConflict['delegate'],
                                    'plugin'   => $aConflict['sign']
                                )
                            ),
                            E::ModuleLang()->get('error'), true
                        );
                    }
                }
                if ($iCount) {
                    return false;
                }
            }
            $bResult = $oPlugin->activate();
            if ($bResult && ($sVersion = $oPlugin->getVersion())) {
                $oPlugin->writeStorageVersion($sVersion);
                $oPlugin->writeStorageDate();
            }
        } else {
            // * Исполняемый файл плагина не найден
            $sPluginClassFile = Plugin::GetPluginClass($sPluginId) . '.class.php';
            E::ModuleMessage()->addError(
                E::ModuleLang()->get('action.admin.plugin_file_not_found', array('file' => $sPluginClassFile)),
                E::ModuleLang()->get('error'),
                true
            );
            return false;
        }

        if ($bResult) {
            // Запрещаем кеширование
            E::ModuleCache()->setDesabled(true);
            // Надо обязательно очистить кеш здесь
            E::ModuleCache()->clean();
            E::ModuleViewer()->clearAll();

            // Переопределяем список активированных пользователем плагинов
            if (!$this->_addActivePlugins($oPluginEntity)) {
                E::ModuleMessage()->addError(
                    E::ModuleLang()->get('action.admin.plugin_write_error', array('file' => F::GetPluginsDatFile())),
                    E::ModuleLang()->get('error'), true
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

        $aPluginsList = $this->getPluginsList(true);
        $oPluginEntity->setActive(true);
        $aPluginsList[$oPluginEntity->getId()] = $oPluginEntity;
        if (sizeof($aPluginsList)) {
            uasort($aPluginsList, array($this, '_pluginCompareByPriority'));
        }
        $aActivePlugins = array();
        /** @var ModulePlugin_EntityPlugin $oPluginEntity */
        foreach($aPluginsList as $sPlugin => $oPluginEntity) {
            $aActivePlugins[$sPlugin] = array(
                'id' => $oPluginEntity->getId(),
                'dirname' => $oPluginEntity->getDirname(),
                'name' => $oPluginEntity->getName(),
            );
        }
        $this->setActivePlugins($aActivePlugins);
        
        return $aPluginsList;
    }

    /**
     * Деактивация
     *
     * @param  string $sPluginId
     * @param  bool   $bRemove
     *
     * @return  null|bool
     */
    public function deactivate($sPluginId, $bRemove = false) {

        // get activated plugin by ID
        $oPlugin = $this->_getPluginById($sPluginId, true);

        if ($oPlugin) {
            /**
             * TODO: Проверять зависимые плагины перед деактивацией
             */
            $bResult = $oPlugin->deactivate();
            if ($bRemove) {
                $oPlugin->remove();
            }
        } else {
            // Исполняемый файл плагина не найден
            $sPluginClassFile = Plugin::GetPluginClass($sPluginId) . '.class.php';
            E::ModuleMessage()->addError(
                E::ModuleLang()->get('action.admin.plugin_file_not_found', array('file' => $sPluginClassFile)),
                E::ModuleLang()->get('error'),
                true
            );
            return false;
        }

        if ($bResult) {
            // * Переопределяем список активированных пользователем плагинов
            $aActivePlugins = $this->getActivePlugins();

            // * Вносим данные в файл о деактивации плагина
            unset($aActivePlugins[$sPluginId]);

            // * Сбрасываем весь кеш, т.к. могут быть закешированы унаследованые плагинами сущности
            E::ModuleCache()->setDesabled(true);
            E::ModuleCache()->clean();
            if (!$this->SetActivePlugins($aActivePlugins)) {
                E::ModuleMessage()->addError(
                    E::ModuleLang()->get('action.admin.plugin_activation_file_write_error'),
                    E::ModuleLang()->get('error'),
                    true
                );
                return false;
            }

            // * Очищаем компилированные шаблоны Smarty
            E::ModuleViewer()->clearSmartyFiles();
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
    public function getActivePlugins($bIdOnly = false) {

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
    public function isActivePlugin($sPlugin) {

        $aPlugins = $this->getActivePlugins();

        return isset($aPlugins[$sPlugin]);
    }

    /**
     * Записывает список активных плагинов в файл PLUGINS.DAT
     *
     * @param array|string $aPlugins    Список плагинов
     *
     * @return bool
     */
    public function setActivePlugins($aPlugins) {

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
    public function delete($aPlugins) {

        if (!is_array($aPlugins)) {
            $aPlugins = array($aPlugins);
        }

        $aActivePlugins = $this->getActivePlugins();
        foreach ($aPlugins as $sPluginId) {
            if (!is_string($sPluginId)) {
                continue;
            }

            // * Если плагин активен, деактивируем его
            if (in_array($sPluginId, $aActivePlugins)) {
                $this->deactivate($sPluginId);
            }
            $oPlugin = $this->_getPluginById($sPluginId, false);
            if ($oPlugin) {
                $oPlugin->remove();
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
    public function delegate($sType, $sFrom, $sTo, $sSign = __CLASS__) {

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
    public function inherit($sFrom, $sTo, $sSign = __CLASS__) {

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
    public function getInheritances() {

        return $this->aInherits;
    }

    /**
     * Return all delegation rules
     *
     * @return array
     */
    public function getDelegations() {

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
    public function getParentInherit($sFrom) {

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
    public function getInherits($sFrom) {

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
    public function getLastInherit($sFrom) {

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
    public function getDelegate($sType, $sFrom) {

        return $this->getLastOf($sType, $sFrom);
    }

    /**
     * @param string $sType
     * @param string $sFrom
     *
     * @return array|null
     */
    public function getDelegates($sType, $sFrom) {

        if (isset($this->aDelegates[$sType][$sFrom]['delegate'])) {
            return array($this->aDelegates[$sType][$sFrom]['delegate']);
        } else {
            if ($aInherits = $this->getInherits($sFrom)) {
                $aReturn = array();
                foreach (array_reverse($aInherits) as $aData) {
                    $aReturn[] = $aData['inherit'];
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
    public function getDelegationChain($sType, $sTo) {

        $sRootDelegator = $this->getRootDelegator($sType, $sTo);
        return $this->collectAllDelegatesRecursive($sType, array($sRootDelegator));
    }

    /**
     * Возвращает делегируемый класс
     *
     * @param string $sType
     * @param string $sTo
     *
     * @return string
     */
    public function getRootDelegator($sType, $sTo) {

        if ($sTo) {
            $sItem = $sTo;
            $sItemDelegator = $this->getDelegator($sType, $sTo);
            while (empty($sRootDelegator)) {
                if ($sItem == $sItemDelegator) {
                    $sRootDelegator = $sItem;
                }
                $sItem = $sItemDelegator;
                $sItemDelegator = $this->getDelegator($sType, $sItemDelegator);
            }
            return $sRootDelegator;
        }
        return $sTo;
    }

    /**
     * LS-compatibility
     *
     * @param $sType
     * @param $sTo
     *
     * @return string
     */
    public function getRootDelegater($sType, $sTo) {

        return $this->getRootDelegator($sType, $sTo);
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
            if ($aNewDelegates = $this->getDelegates($sType, $sClass)) {
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
    public function getDelegator($sType, $sTo) {

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
     * LS-compatibility
     *
     * @param $sType
     * @param $sTo
     *
     * @return string
     */
    public function getDelegater($sType, $sTo) {

        return $this->getDelegator($sType, $sTo);
    }

    /**
     * Возвращает подпись делегата модуля, экшена, сущности.
     *
     * @param  string $sType
     * @param  string $sFrom
     *
     * @return string|null
     */
    public function getDelegateSign($sType, $sFrom) {

        if (isset($this->aDelegates[$sType][$sFrom]['sign'])) {
            return $this->aDelegates[$sType][$sFrom]['sign'];
        }
        if ($aInherit = $this->getLastInherit($sFrom)) {
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
    public function isDelegator($sType, $sFrom) {

        if (isset($this->aDelegates[$sType][$sFrom]['delegate'])) {
            return true;
        } elseif ($aInherit = $this->getLastInherit($sFrom)) {
            return true;
        }
        return false;
    }

    /**
     * LS-compatible
     *
     * @param $sType
     * @param $sFrom
     *
     * @return bool
     */
    public function isDelegater($sType, $sFrom) {

        return $this->isDelegator($sType, $sFrom);
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
    public function getDelegateObjectList() {

        return array_keys($this->aDelegates);
    }

    /**
     * Returns first class from chain of inheritance
     *
     * @param string $sType
     * @param string $sClass
     *
     * @return string
     */
    public function getFirstOf($sType, $sClass) {

        return $this->getRootDelegator($sType, $sClass);
    }

    /**
     * Returns last class from chain of inheritance
     *
     * @param string $sType
     * @param string $sClass
     *
     * @return string
     */
    public function getLastOf($sType, $sClass) {

        if (isset($this->aDelegates[$sType][$sClass]['delegate'])) {
            return $this->aDelegates[$sType][$sClass]['delegate'];
        } elseif ($aInherit = $this->getLastInherit($sClass)) {
            return $aInherit['inherit'];
        }
        return $sClass;
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
    public function encodeId($xPluginId) {

        if (is_array($xPluginId)) {
            $aResult = array();
            foreach($xPluginId as $iIdx => $sPluginId) {
                $aResult = $this->encodeId($sPluginId);
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
    public function decodeId($xPluginId) {

        if (is_array($xPluginId)) {
            $aResult = array();
            foreach($xPluginId as $xKey => $sPluginId) {
                $aResult[$xKey] = $this->decodeId($sPluginId);
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
    public function unpackPlugin($sPackFile) {

        $zip = new ZipArchive;
        if ($zip->open($sPackFile) === true) {
            $sUnpackDir = F::File_NormPath(dirname($sPackFile) . '/_unpack/');
            if (!$zip->extractTo($sUnpackDir)) {
                E::ModuleMessage()->addError(E::ModuleLang()->get('action.admin.err_extract_zip_file'), E::ModuleLang()->get('error'));
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
                    E::ModuleMessage()->addError(
                        E::ModuleLang()->get('action.admin.file_not_found', array('file' => self::PLUGIN_XML_FILE)),
                        E::ModuleLang()->get('error')
                    );
                    return false;
                }
                $sPluginSrc = dirname($sXmlFile);

                // try to define plugin's dirname
                $oXml = @simplexml_load_file($sXmlFile);
                if (!$oXml) {
                    E::ModuleMessage()->addError(
                        E::ModuleLang()->get('action.admin.err_read_xml', array('file' => $sXmlFile)),
                        E::ModuleLang()->get('error')
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

                $sPluginPath = $this->getPluginsDir() . '/' . $sPluginDir . '/';
                if (F::File_CopyDir($sPluginSrc, $sPluginPath)) {
                    E::ModuleMessage()->addNotice(E::ModuleLang()->get('action.admin.plugin_added_ok'));
                } else {
                    E::ModuleMessage()->addError(E::ModuleLang()->get('action.admin.plugin_added_err'), E::ModuleLang()->get('error'));
                }
            }
            $zip->close();
        } else {
            E::ModuleMessage()->addError(E::ModuleLang()->get('action.admin.err_open_zip_file'), E::ModuleLang()->get('error'));
        }
        return true;
    }
}

// EOF
