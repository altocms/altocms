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

    if (isset($aParams['name'])) {
        if (!isset($aParams['group'])) {
            $aParams['group'] = $aParams['name'];
        } elseif (!isset($aParams['widget'])) {
            $aParams['widget'] = $aParams['name'];
        }
    }
    if (!isset($aParams['group']) && !isset($aParams['name'])) {
        $sError = 'Parameter "group" does not define in {wgroup ...} function';
        if ($oSmartyTemplate->template_resource) {
            $sError .= ' (template: ' . $oSmartyTemplate->template_resource . ')';
        }
        trigger_error($sError, E_USER_WARNING);
        return;
    }
    $sWidgetGroup = $aParams['group'];
    $aWidgetParams = (isset($aParams['params']) ? $aParams['params'] : $aParams);

    // group parameter required
    if (!$sWidgetGroup) {
        return '';
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
            trigger_error('Parameter "widget" does not define in {wgroup ...} function', E_USER_WARNING);
            return;
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