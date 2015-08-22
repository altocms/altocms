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

    protected $sCurentPath;

    /**
     * Сопоставление заданных путей с текущим
     *
     * @param   string|array    $aPaths
     * @param   bool            $bDefault
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

        $this->sCurentPath = R::GetControllerPath();
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

        $xExtends = false;
        if (!empty($aWidgetData[Config::KEY_EXTENDS])) {
            $xExtends = $aWidgetData[Config::KEY_EXTENDS];
            unset($aWidgetData[Config::KEY_EXTENDS]);
        } elseif (($iKey = array_search(Config::KEY_EXTENDS, $aWidgetData)) !== false) {
            $xExtends = true;
            unset($aWidgetData[$iKey]);
        }
        if ($xExtends) {
            if (($xExtends === true) && $sWidgetId && isset($aWidgets[$sWidgetId])) {
                $aWidgetData = F::Array_MergeCombo($aWidgets[$sWidgetId], $aWidgetData);
            } elseif(is_string($xExtends)) {
                $aWidgetData = F::Array_MergeCombo(Config::Get($xExtends), $aWidgetData);
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
        $aWidgets = (array)Config::Get('widgets');

        // Добавляем списки виджетов из конфигов плагинов
        $aPlugins = F::GetPluginsList();
        if ($aPlugins) {
            foreach($aPlugins as $sPlugin) {
                if ($aPluginWidgets = Config::Get('plugin.' . $sPlugin . '.widgets')) {
                    foreach ($aPluginWidgets as $xKey => $aWidgetData) {
                        // ID виджета может задаваться либо ключом элемента массива, либо параметром 'id'
                        if (isset($aWidgetData['id'])) {
                            $sWidgetId = $aWidgetData['id'];
                        } elseif (!is_integer($xKey)) {
                            $sWidgetId = $aWidgetData['id'] = $xKey;
                        } else {
                            $sWidgetId = null;
                        }
                        $aWidgetData = $this->_getWidgetData($sWidgetId, $aWidgetData, $aWidgets);
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
                if (R::AllowLocalPath($oWidget->GetIncludePaths(), $oWidget->GetExcludePaths())) {
                    $this->aWidgets[$oWidget->GetId()] = $oWidget;
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
     * @return  string|bool
     */
    public function FileClassExists($sName, $sPlugin = null, $bReturnClassName = false) {

        $sName = ucfirst($sName);
        if (!$sPlugin) {
            $aPathSeek = Config::Get('path.root.seek');
            $sFile = '/classes/widgets/Widget' . $sName . '.class.php';
            $sClass = 'Widget' . $sName;
        } else {
            $aPathSeek = F::GetPluginsDir();
            $sFile = $sPlugin . '/classes/widgets/Widget' . $sName . '.class.php';
            $sClass = 'Plugin' . ucfirst($sPlugin) . '_Widget' . $sName;
        }
        if (F::File_Exists($sFile, $aPathSeek)) {
            return $bReturnClassName ? $sClass : $sFile;
        }
        return false;
    }

}

// EOF