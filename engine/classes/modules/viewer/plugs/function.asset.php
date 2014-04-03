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
 *
 * @return  string
 */
function smarty_function_asset($aParams, $oSmartyTemplate) {

    if (empty($aParams['skin']) && empty($aParams['file'])) {
        trigger_error('Asset: missing "file" parametr', E_USER_WARNING);
        return;
    }

    if (isset($aParams['file'])) {
        // Need URL to asset file
        if (empty($aParams['skin'])) {
            $sSkin = E::Viewer_GetConfigSkin();
        } else {
            $sSkin = $aParams['skin'];
        }
        if (isset($aParams['theme'])) {
            if (is_bool($aParams['theme'])) {
                $sTheme = E::Viewer_GetConfigTheme();
            } else {
                $sTheme = $aParams['theme'];
            }
        } else {
            $sTheme = '';
        }
        if ($sTheme) {
            $sTheme = 'themes/' . $sTheme . '/';
        }
        $sFile = Config::Get('path.skins.dir') . '/' . $sSkin . '/' . $sTheme . $aParams['file'];
        $sUrl = E::ViewerAsset_File2Link($sFile, 'skin/' . $sSkin . '/');
    } else {
        // Need URL to asset dir
        $sUrl = E::Viewer_GetAssetUrl() . 'skin/' . $aParams['skin'] . '/';
    }

    return $sUrl;
}

// EOF