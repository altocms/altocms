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
 * Eval widgets
 *
 * @param   array                    $aParams
 * @param   Smarty_Internal_Template $oSmartyTemplate
 *
 * @return  string
 */
function smarty_function_widget($aParams, $oSmartyTemplate) {

    if (!isset($aParams['name']) && !isset($aParams['object']) && !isset($aParams['group']) && !isset($aParams['id'])) {
        $sError = 'Parameter "name" or "object" or "id" not define in {widget ...} function';
        if ($oSmartyTemplate->template_resource) {
            $sError .= ' (template: ' . $oSmartyTemplate->template_resource . ')';
        }
        trigger_error($sError, E_USER_WARNING);
        return;
    }
    if (isset($aParams['group'])) {
        if (!function_exists('smarty_function_wgroup')) {
            F::IncludeFile('function.wgroup.php');
        }
        return smarty_function_wgroup($aParams, $oSmartyTemplate);
    }
    $oWidget = null;
    if (isset($aParams['name'])) {
        $sWidgetName = $aParams['name'];
        $aWidgetParams = (isset($aParams['params']) ? $aParams['params'] : array());
        $sWidgetType = 'exec';
    } elseif (isset($aParams['id'])) {
        $aWidgets = $oSmartyTemplate->getTemplateVars('aWidgets');
        if (is_array($aWidgets) && isset($aWidgets['_all_'][$aParams['id']])) {
            $oWidget = $aWidgets['_all_'][$aParams['id']];
        }
    } else {
        $oWidget = $aParams['object'];
    }
    if ($oWidget) {
        $sWidgetName = $oWidget->GetName();
        $sWidgetTemplate = $oWidget->GetTemplate();
        $aWidgetParams = $oWidget->getParams();
        $sWidgetType = $oWidget->getType();
    }
    if ($sWidgetType == 'exec') {
        if (!function_exists('smarty_function_widget_exec')) {
            F::IncludeFile('function.widget_exec.php');
        }
        return smarty_function_widget_exec(array('name' => $sWidgetName, 'params' => $aWidgetParams), $oSmartyTemplate);
    } elseif ($sWidgetType == 'block') {
        // * LS-compatible * //
        if (!function_exists('smarty_function_widget_exec')) {
            F::IncludeFile('function.widget_exec.php');
        }
        return smarty_function_widget_exec(array('name' => $sWidgetName, 'params' => $aWidgetParams), $oSmartyTemplate);
    } elseif ($sWidgetType == 'template') {
        if (!function_exists('smarty_function_widget_template')) {
            F::IncludeFile('function.widget_template.php');
        }
        return smarty_function_widget_template(
            array('name' => ($sWidgetTemplate ? $sWidgetTemplate : $sWidgetName), 'params' => $aWidgetParams),
            $oSmartyTemplate
        );
    }
    return '';
}


// EOF