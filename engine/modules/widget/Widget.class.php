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

/**
 * Модуль управления виджетами
 */
class ModuleWidget extends Module {

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
        $aPaths = F::Val2Array($aPaths);
        if ($aPaths) {
            foreach($aPaths as $nKey => $sPath) {
                if ($sPath == '*') {
                    $aPaths[$nKey] = Config::Get('router.config.action_default') . '/*';
                } elseif($sPath == '/') {
                    $aPaths[$nKey] = Config::Get('router.config.action_default') . '/';
                } elseif (!in_array(substr($sPath, -1), array('/', '*'))) {
                    $aPaths[$nKey] = $sPath . '/*';
                }
            }
            return F::File_InPath($this->sCurentPath, $aPaths);
        }
        return $bDefault;
    }

    /**
     * Инициализация модуля
     */
    public function Init() {
        $this->sCurentPath = Router::GetCurrentPath();
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
                    $aWidgets = array_merge($aWidgets, $aPluginWidgets);
                }
            }
        }
        $aResult = array();
        if ($aWidgets) {
            // формируем окончательный список виджетов
            foreach($aWidgets as $sKey=>$aWidgetData) {
                // ID виджета может задаваться либо ключом эелемента массива, либо пааметром 'id'
                if (!is_integer($sKey) && !isset($aWidgetData['id'])) {
                    $aWidgetData['id'] = $sKey;
                }
                // Если ID не задан, то формируется автоматически по хешу
                $oWidget = $this->MakeWidget($aWidgetData);
                $aResult[$oWidget->getId()] = $oWidget;
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
        $oWidget = Engine::GetEntity('Widget', $aWidgetData);
        /*
         * Перенесено в конструктор
        $aCfgData = Config::Get('widget.' . $oWidget->GetId() . '.config');
        if ($aCfgData) {
            $aCfgData = F::Array_Merge($oWidget->_getData(), $aCfgData);
            $oWidget->_setData($aCfgData);
        }
         */
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
        foreach ($aWidgets as $oWidget) {
            if ($oWidget->isDisplay()) {
                if ($this->_checkPath($oWidget->GetIncludePaths(), true) && !$this->_checkPath($oWidget->GetExcludePaths(), false)) {
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
            $sFile = Config::Get('path.root.dir') . '/classes/widgets/Widget' . $sName . '.class.php';
            $sClass = 'Widget' . $sName;
        } else {
            $sFile = F::GetPluginsDir() . $sPlugin . '/classes/widgets/Widget' . $sName . '.class.php';
            $sClass = 'Plugin' . ucfirst($sPlugin) . '_Widget' . $sName;
        }
        if (F::File_Exists($sFile)) {
            return $bReturnClassName ? $sClass : $sFile;
        }
        return false;
    }

}

// EOF