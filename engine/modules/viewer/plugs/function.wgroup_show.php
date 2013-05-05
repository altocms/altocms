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
function smarty_function_wgroup_show($aParams, $oSmartyTemplate) {

    if (!isset($aParams['group'])) {
        trigger_error('Parameter "group" does not define in {wgroup ...} function', E_USER_WARNING);
        return;
    }
    $sWidgetGroup = $aParams['group'];
    $aWidgets = Engine::getInstance()->Viewer_GetWidgets();

    $sResult = '';
    if (isset($aWidgets[$sWidgetGroup])) {
        if (!function_exists('smarty_function_widget')) {
            F::IncludeFile('function.widget.php');
        }
        foreach ($aWidgets[$sWidgetGroup] as $oWidget) {
            $sResult .= smarty_function_widget(array('object' => $oWidget), $oSmartyTemplate);
        }
    }
    return $sResult;
}

// EOF