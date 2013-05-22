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
 * Добавляет старые LS-методы для совместимости
 */
class PluginLs_ModuleWidget extends PluginLs_Inherit_ModuleWidget {

    /**
     * Загружает виджеты, указанные в правилах
     *
     * @return mixed
     */
    protected function _loadWidgetsList() {
        $aResult = parent::_loadWidgetsList();

        // добавляем LS-блоки по старым правилам, если они есть
        $aBlockRules = Config::Get('block');
        if ($aBlockRules) {
            $sAction = strtolower(Router::GetAction());
            $sEvent = strtolower(Router::GetActionEvent());
            $sEventName = strtolower(Router::GetActionEventName());
            foreach ($aBlockRules as $aRule) {
                $bUse = false;

                // * Если в правиле не указан список блоков, нам такое не нужно
                if (!array_key_exists('blocks', $aRule)) continue;
                /*
                 * Если не задан action для исполнения и нет ни одного шаблона path,
                 * или текущий не входит в перечисленные в правиле
                 * то выбираем следующее правило
                 */
                if (!array_key_exists('action', $aRule) && !array_key_exists('path', $aRule)) continue;

                if (isset($aRule['action'])) {
                    if (in_array($sAction, (array)$aRule['action'])) $bUse = true;
                    if (array_key_exists($sAction, (array)$aRule['action'])) {
                        /**
                         * Если задан список event`ов и текущий в него не входит,
                         * переходи к следующему действию.
                         */
                        foreach ((array)$aRule['action'][$sAction] as $sEventPreg) {
                            if (substr($sEventPreg, 0, 1) == '/') {
                                // * Это регулярное выражение
                                if (preg_match($sEventPreg, $sEvent)) {
                                    $bUse = true;
                                    break;
                                }
                            } elseif (substr($sEventPreg, 0, 1) == '{') {
                                // * Это имя event'a (именованный евент, если его нет, то совпадает с именем метода евента в экшене)
                                if (trim($sEventPreg, '{}') == $sEventName) {
                                    $bUse = true;
                                    break;
                                }
                            } else {
                                // * Это название event`a
                                if ($sEvent == $sEventPreg) {
                                    $bUse = true;
                                    break;
                                }
                            }
                        }
                    }
                }

                // * Если не найдено совпадение по паре Action/Event, то переходим к поиску по regexp путей.
                if (!$bUse && isset($aRule['path'])) {
                    $sPath = rtrim(Router::GetPathWebCurrent(), "/");

                    // * Проверяем последовательно каждый regexp
                    foreach ((array)$aRule['path'] as $sRulePath) {
                        $sPattern = "~" . str_replace(array('/', '*'), array('\/', '[\w\-]+'), $sRulePath) . "~";
                        if (preg_match($sPattern, $sPath)) {
                            $bUse = true;
                            break 1;
                        }
                    }
                }

                if ($bUse) {
                    // * Если задан режим очистки блоков, сначала чистим старые блоки
                    if (isset($aRule['clear'])) {
                        switch (true) {
                            // * Если установлен в true, значит очищаем все
                            case  ($aRule['clear'] === true):
                                //$this->ClearBlocksAll();
                                $aResult = array();
                                break;

                            case is_string($aRule['clear']):
                                //$this->ClearBlocks($aRule['clear']);
                                foreach ($aResult as $sId => $oWidget) {
                                    if ($oWidget->GetGroup() == $aRule['clear']) {
                                        unset($aResult[$sId]);
                                    }
                                }
                                break;

                            case is_array($aRule['clear']):
                                /*
                                foreach ($aRule['clear'] as $sGroup) {
                                    $this->ClearBlocks($sGroup);
                                }
                                */
                                foreach ($aResult as $sId => $oWidget) {
                                    if (in_array($oWidget->GetGroup(), $aRule['clear'])) {
                                        unset($aResult[$sId]);
                                    }
                                }
                                break;
                        }
                    }

                    // * Добавляем все блоки, указанные в параметре blocks
                    foreach ($aRule['blocks'] as $sGroup => $aBlocks) {
                        foreach ((array)$aBlocks as $sName => $aParams) {
                            // * Если название блока указывается в параметрах
                            if (is_int($sName)) {
                                if (is_array($aParams)) {
                                    $sName = $aParams['block'];
                                }
                            }

                            // * Если $aParams не являются массивом, значит передано только имя блока
                            if (!is_array($aParams)) {
                                $sName = $aParams;
                                $aParams = array();
                                $nPriority = isset($aParams['priority']) ? $aParams['priority'] : 5;
                            } else {
                                $aParams = isset($aParams['params']) ? $aParams['params'] : array();
                                $nPriority = isset($aParams['priority']) ? $aParams['priority'] : 5;
                            }
                            $aWidgetData = array(
                                'name' => $sName,
                                'group' => $sGroup,
                                'priority' => $nPriority,
                                'params' => $aParams,
                            );
                            $oWidget = $this->MakeWidget($aWidgetData);
                            $aResult[$oWidget->getId()] = $oWidget;
                        }
                    }
                }
            }
        }
        return $aResult;
    }
}

// EOF