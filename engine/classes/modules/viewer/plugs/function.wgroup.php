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
 * Eval widget groups
 *
 * @param   array $aParams
 * @param   Smarty_Internal_Template $oSmartyTemplate
 *
 * @return  string
 */
function smarty_function_wgroup($aParams, $oSmartyTemplate) {

    if (empty($aParams['group']) && empty($aParams['name'])) {
        $sError = 'Parameter "group" does not define in {wgroup ...} function';
        if ($oSmartyTemplate->template_resource) {
            $sError .= ' (template: ' . $oSmartyTemplate->template_resource . ')';
        }
        F::SysWarning($sError);
        return null;
    }

    if (empty($aParams['group']) && !empty($aParams['name'])) {
        $aParams['group'] = $aParams['name'];
        unset($aParams['name']);
    }

    $sWidgetGroup = $aParams['group'];
    $aWidgetParams = (isset($aParams['params']) ? array_merge($aParams['params'], $aParams): $aParams);

    // group parameter required
    if (!$sWidgetGroup) {
        return null;
    }

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
        if (isset($aWidgetParams['command'])) {
            unset($aWidgetParams['command']);
        }
        return smarty_function_wgroup_show(
            array('group' => $sWidgetGroup, 'params' => $aWidgetParams), $oSmartyTemplate
        );
    } elseif ($sWidgetCommand == 'add') {
        if (!isset($aWidgetParams['widget'])) {
            F::SysWarning('Parameter "widget" does not define in {wgroup ...} function');
            return null;
        }
        if (!function_exists('smarty_function_wgroup_add')) {
            F::IncludeFile('function.wgroup_add.php');
        }
        $sWidgetName = $aWidgetParams['widget'];
        unset($aWidgetParams['group']);
        unset($aWidgetParams['widget']);
        if (isset($aWidgetParams['command'])) {
            unset($aWidgetParams['command']);
        }
        return smarty_function_wgroup_add(
            array('group' => $sWidgetGroup, 'widget' => $sWidgetName, 'params' => $aWidgetParams), $oSmartyTemplate
        );
    }
    return '';
}

// EOF