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
 * Adds widget into widget group
 *
 * @param   array                    $aParams
 * @param   Smarty_Internal_Template $oSmartyTemplate
 *
 * @return  string
 */
function smarty_function_wgroup_add($aParams, $oSmartyTemplate) {

    if (isset($aParams['name'])) {
        if (!isset($aParams['group'])) {
            $aParams['group'] = $aParams['name'];
        } elseif (!isset($aParams['widget'])) {
            $aParams['widget'] = $aParams['name'];
        }
    }
    if (!isset($aParams['group']) && !isset($aParams['name'])) {
        $sError = 'Parameter "group" does not define in {wgroup_add ...} function';
        if ($oSmartyTemplate->template_resource) {
            $sError .= ' (template: ' . $oSmartyTemplate->template_resource . ')';
        }
        F::SysWarning($sError);
        return null;
    }
    if (!isset($aParams['widget'])) {
        $sError = 'Parameter "widget" does not define in {wgroup_add ...} function';
        if ($oSmartyTemplate->template_resource) {
            $sError .= ' (template: ' . $oSmartyTemplate->template_resource . ')';
        }
        F::SysWarning($sError);
        return null;
    }

    $aWidgetParams = (isset($aParams['params']) ? (array)$aParams['params'] : array());
    if (array_key_exists('priority', $aWidgetParams)) {
        $nPriority = $aWidgetParams['priority'];
    } elseif (array_key_exists('priority', $aParams)) {
        $nPriority = $aParams['priority'];
    } else {
        $nPriority = 0;
    }

    foreach ($aParams as $sKey => $sVal) {
        if (!in_array($sKey, array('group', 'name', 'widget', 'params', 'priority'))) {
            $aWidgetParams[$sKey] = $sVal;
        }
    }

    E::ModuleViewer()->AddWidget($aParams['group'], $aParams['widget'], $aWidgetParams, $nPriority);
    $aWidgets = E::ModuleViewer()->GetWidgets();

    $oSmartyTemplate->assign('aWidgets', $aWidgets);
    $oSmartyTemplate->parent->assign('aWidgets', $aWidgets);

    return '';
}

// EOF