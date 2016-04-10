<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * Модуль управления виджетами
 */
class ModuleWidget extends Module {

    const WIDGET_TYPE_UNKNOWN = 0;
    const WIDGET_TYPE_TEMPLATE = 1;
    const WIDGET_TYPE_EXEC = 2;

    protected $aWidgets = array();
    protected $aConfig = array();

    /**
     * Сопоставление заданных путей с текущим
     *
     * @param   string|array    $aPaths
     * @param   bool            $bDefault
     *
     * @return  bool
     */
    protected function _checkPath($aPaths, $bDefault = true) {

        if ($aPaths) {
            return R::CompareWithLocalPath($aPaths);
        }
        return $bDefault;
    }

    /**
     * Инициализация модуля
     */
    public function Init() {

    }

    /**
     * Returns full widget data (extends other widget or config dataset if needs)
     *
     * @param string|null $sWidgetId
     * @param array       $aWidgetData
     * @param array       $aWidgets
     *
     * @return array
     */
    protected function _getWidgetData($sWidgetId, $aWidgetData, $aWidgets) {

        if (!empty($aWidgetData[C::KEY_REPLACE])) {
            unset($aWidgetData[C::KEY_EXTENDS]);
            return $aWidgetData;
        }

        $xExtends = true;
        $bReset = false;
        if (!empty($aWidgetData[C::KEY_EXTENDS])) {
            $xExtends = $aWidgetData[C::KEY_EXTENDS];
            unset($aWidgetData[C::KEY_EXTENDS]);
        }
        if (!empty($aWidgetData[C::KEY_RESET])) {
            $bReset = $aWidgetData[C::KEY_RESET];
            unset($aWidgetData[C::KEY_RESET]);
        }
        if ($xExtends) {
            if (($xExtends === true) && $sWidgetId && isset($aWidgets[$sWidgetId])) {
                if ($bReset) {
                    $aWidgetData = F::Array_Merge($aWidgets[$sWidgetId], $aWidgetData);
                } else {
                    $aWidgetData = F::Array_MergeCombo($aWidgets[$sWidgetId], $aWidgetData);
                }
            } elseif(is_string($xExtends)) {
                if ($bReset) {
                    $aWidgetData = F::Array_Merge(C::Get($xExtends), $aWidgetData);
                } else {
                    $aWidgetData = F::Array_MergeCombo(C::Get($xExtends), $aWidgetData);
                }
            }
        }
        return $aWidgetData;
    }

    /**
     * Загружает список виджетов и конфигурирует их
     *
     * @return array
     */
    protected function _loadWidgetsList() {

        // Список виджетов из основного конфига
        $aWidgets = (array)C::Get('widgets');

        // Добавляем списки виджетов из конфигов плагинов
        $aPlugins = F::GetPluginsList();
        if ($aPlugins) {
            foreach($aPlugins as $sPlugin) {
                if ($aPluginWidgets = C::Get('plugin.' . $sPlugin . '.widgets', null, null, true)) {
                    foreach ($aPluginWidgets as $xKey => $aWidgetData) {
                        // ID виджета может задаваться либо ключом элемента массива, либо параметром 'id'
                        if (isset($aWidgetData['id'])) {
                            $sWidgetId = $aWidgetData['id'];
                        } elseif (!is_integer($xKey)) {
                            $sWidgetId = $aWidgetData['id'] = $xKey;
                        } else {
                            $sWidgetId = null;
                        }
                        if (!empty($aWidgetData['plugin']) && $aWidgetData['plugin'] === true) {
                            $aWidgetData['plugin'] = $sPlugin;
                        }
                        if (!empty($aWidgets[$sWidgetId])) {
                            $aWidgetData = $this->_getWidgetData($sWidgetId, $aWidgetData, $aWidgets);
                        }
                        if ($sWidgetId) {
                            $aWidgets[$sWidgetId] = $aWidgetData;
                        } else {
                            $aWidgets[] = $aWidgetData;
                        }
                    }
                    //$aWidgets = F::Array_MergeCombo($aWidgets, $aPluginWidgets);
                }
            }
        }
        $aResult = array();
        if ($aWidgets) {
            // формируем окончательный список виджетов
            foreach ($aWidgets as $sKey => $aWidgetData) {
                if ($aWidgetData) {
                    // Если ID виджета не задан, то он формируется автоматически
                    if (!isset($aWidgetData['id']) && !is_numeric($sKey)) {
                        $aWidgetData['id'] = $sKey;
                    }
                    $oWidget = $this->MakeWidget($aWidgetData);
                    $aResult[$oWidget->getId()] = $oWidget;
                }
            }
        }
        return $aResult;
    }

    /**
     * Создает сущность виджета по переданным свойствам
     *
     * @param   array                       $aWidgetData
     *
     * @return  ModuleWidget_EntityWidget
     */
    public function MakeWidget($aWidgetData) {

        $oWidget = E::GetEntity('Widget', $aWidgetData);

        return $oWidget;
    }

    /**
     * Возвращает массив виджетов
     *
     * @param   bool    $bAll   - если true, то все виджеты, иначе - только те, что должны быть отображены
     *
     * @return  array
     */
    public function GetWidgets($bAll = false) {

        $aWidgets = $this->_loadWidgetsList();

        // Если массив пустой или фильтровать не нужно, то возвращаем, как есть
        if (!$aWidgets || $bAll) {
            return $aWidgets;
        }
        /** @var ModuleWidget_EntityWidget $oWidget */
        foreach ($aWidgets as $oWidget) {
            if ($oWidget->isDisplay()) {
                if (R::AllowLocalPath($oWidget->getIncludePaths(), $oWidget->getExcludePaths())) {
                    $this->aWidgets[$oWidget->getId()] = $oWidget;
                }
            }
        }
        return $this->aWidgets;
    }

    /**
     * Проверяет существование файла класса исполняемого виджета
     *
     * @param   string      $sName
     * @param   string|null $sPlugin
     * @param   bool        $bReturnClassName
     * 
     * @return  string|bool
     */
    public function FileClassExists($sName, $sPlugin = null, $bReturnClassName = false) {

        $sName = ucfirst($sName);
        if (!$sPlugin) {
            $aPathSeek = C::Get('path.root.seek');
            $sFile = '/classes/widgets/Widget' . $sName . '.class.php';
            $sClass = 'Widget' . $sName;
        } else {
            $aPathSeek = array(Plugin::GetPath($sPlugin));
            $sFile = '/classes/widgets/Widget' . $sName . '.class.php';
            $sClass = 'Plugin' . ucfirst($sPlugin) . '_Widget' . $sName;
        }
        if (F::File_Exists($sFile, $aPathSeek)) {
            return $bReturnClassName ? $sClass : $sFile;
        }
        return false;
    }

}

// EOF