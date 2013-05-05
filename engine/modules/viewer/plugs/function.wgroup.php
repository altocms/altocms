<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
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
 * Плагин для Smarty
 * Работа с группой виджетов
 *
 * @param   array $aParams
 * @param   Smarty_Internal_Template $oSmartyTemplate
 * @return  string
 */
function smarty_function_wgroup($aParams, $oSmartyTemplate) {

    if (!isset($aParams['group'])) {
        trigger_error('Parameter "group" does not define in {wgroup ...} function', E_USER_WARNING);
        return;
    }
    $sWidgetGroup = $aParams['group'];
    $aWidgetParams = (isset($aParams['params']) ? $aParams['params'] : $aParams);

    if (isset($aParams['command'])) {
        $sWidgetCommand = $aParams['command'];
    } else {
        $sWidgetCommand = 'show';
    }
    if ($sWidgetCommand == 'show') {
        if (!function_exists('smarty_function_wgroup_show')) {
            F::IncludeFile('function.wgroup_show.php');
        }
        unset($aWidgetParams['group']);
        if (isset($aWidgetParams['command'])) unset($aWidgetParams['command']);
        return smarty_function_wgroup_show(array('group' => $sWidgetGroup, 'params' => $aWidgetParams), $oSmartyTemplate);
    } elseif ($sWidgetCommand == 'add') {
        if (!isset($aWidgetParams['name'])) {
            trigger_error('Parameter "name" does not define in {wgroup ...} function', E_USER_WARNING);
            return;
        }
        if (!function_exists('smarty_function_wgroup_add')) {
            F::IncludeFile('function.wgroup_add.php');
        }
        $sWidgetName = $aWidgetParams['name'];
        unset($aWidgetParams['group']);
        unset($aWidgetParams['name']);
        if (isset($aWidgetParams['command'])) unset($aWidgetParams['command']);
        return smarty_function_wgroup_add(array('group' => $sWidgetGroup, 'name' => $sWidgetName, 'params' => $aWidgetParams), $oSmartyTemplate);
    }
    return '';
}

// EOF