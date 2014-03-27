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
 * Plugin for Smarty
 * Display widget group
 *
 * @param   array                    $aParams
 * @param   Smarty_Internal_Template $oSmartyTemplate
 *
 * @return  string
 */
function smarty_function_wgroup_show($aParams, $oSmartyTemplate) {

    if (isset($aParams['name'])) {
        if (!isset($aParams['group'])) {
            $aParams['group'] = $aParams['name'];
        } elseif (!isset($aParams['widget'])) {
            $aParams['widget'] = $aParams['name'];
        }
    }
    if (!isset($aParams['group'])) {
        $sError = 'Parameter "group" does not define in {wgroup_show ...} function';
        if ($oSmartyTemplate->template_resource) {
            $sError .= ' (template: ' . $oSmartyTemplate->template_resource . ')';
        }
        trigger_error($sError, E_USER_WARNING);
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