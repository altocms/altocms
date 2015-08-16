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
 * @param   array                    $aParams
 * @param   Smarty_Internal_Template $oSmartyTemplate
 *
 * @return string|null;
 */
function smarty_function_include_once($aParams, $oSmartyTemplate) {

    if (!array_key_exists('file', $aParams)) {
        trigger_error('include_once: missing "name" parameter', E_USER_WARNING);
        return null;
    }

    $sTemplate = E::ModulePlugin()->GetDelegate('template', $aParams['file']);
    $aIncluded = (array)$oSmartyTemplate->smarty->getTemplateVars('_included_files');

    if (!in_array($sTemplate, $aIncluded)) {
        unset($aParams['file']);
        if ($aParams) {
            $oSmartyTemplate->smarty->assign($aParams);
        }
        $sResult = $oSmartyTemplate->smarty->fetch($sTemplate);
        $oSmartyTemplate->smarty->append('_included_files', $sTemplate);
    } else {
        $sResult = '';
    }
    return $sResult;
}

// EOF