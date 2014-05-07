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
 *
 * @param   array                    $aParams
 * @param   Smarty_Internal_Template $oSmartyTemplate
 *
 * @return  string|null
 */
function smarty_function_widget_template($aParams, $oSmartyTemplate) {

    if (!isset($aParams['name'])) {
        trigger_error('Parameter "name" does not define in {widget ...} function', E_USER_WARNING);
        return null;
    }
    $sWidgetName = $aParams['name'];
    $aWidgetParams = (isset($aParams['params']) ? $aParams['params'] : array());

    $oEngine = Engine::getInstance();

    // Проверяем делигирование
    $sTemplate = $oEngine->Plugin_GetDelegate('template', $sWidgetName);

    if ($sTemplate) {
        if ($aWidgetParams) {
            foreach ($aWidgetParams as $sKey => $sVal) {
                $oSmartyTemplate->assign($sKey, $sVal);
            }
            if (!isset($aWidgetParams['params'])) {
                /* LS-compatible */
                $oSmartyTemplate->assign('params', $aWidgetParams);
            }
            $oSmartyTemplate->assign('aWidgetParams', $aWidgetParams);
        }
        $sResult = $oSmartyTemplate->fetch($sTemplate);
    } else {
        $sResult = null;
    }

    return $sResult;
}

// EOF