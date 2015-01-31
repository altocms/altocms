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
 * @param  $params
 * @param  $smarty
 *
 * @return array|null;
 */
function smarty_function_include_once($params, &$smarty) {

    if (!array_key_exists('file', $params)) {
        trigger_error('include_once: missing "name" parameter', E_USER_WARNING);
        return;
    }

    $sTemplate = E::Plugin_GetDelegate('template', $params['file']);
    $aIncluded = (array)$smarty->getTemplateVars('_included_files');

    if (!in_array($sTemplate, $aIncluded)) {
        unset($params['file']);
        if ($params) {
            $smarty->assign($params);
        }
        $sResult = $smarty->fetch($sTemplate);
        $smarty->append('_included_files', $sTemplate);
    } else {
        $sResult = '';
    }
    return $sResult;
}

// EOF