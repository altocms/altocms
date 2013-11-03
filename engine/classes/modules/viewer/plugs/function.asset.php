<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * Plugin for Smarty
 * Returns URL for skin asset file
 *
 * @param   array $aParams
 * @param   Smarty_Internal_Template $oSmartyTemplate
 * @return  string
 */
function smarty_function_asset($aParams, $oSmartyTemplate) {

    if (empty($aParams['skin'])) {
        trigger_error('Config: missing "skin" parametr', E_USER_WARNING);
        return;
    }

    $sUrl = E::Viewer_GetAssetUrl() . 'skin/' . $aParams['skin'] . '/';

    return $sUrl;
}

// EOF