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
        trigger_error('Asset: missing "file" parameter', E_USER_WARNING);
        return '';
    }

    if (isset($aParams['file'])) {
        if ((stripos($aParams['file'], 'http://') === 0)
            || (stripos($aParams['file'], 'https://') === 0)
            || (stripos($aParams['file'], 'http://') === 0)) {
            $sUrl = $aParams['file'];
        } else {
            $sSkin = (!empty($aParams['skin']) ? $aParams['skin'] : E::ModuleViewer()->GetConfigSkin());
            // File name has full local path
            if (F::File_LocalDir($aParams['file'])) {
                $sFile = $aParams['file'];
            } else {
                // Need URL to asset file
                if (isset($aParams['theme'])) {
                    if (is_bool($aParams['theme'])) {
                        $sTheme = E::ModuleViewer()->GetConfigTheme();
                    } else {
                        $sTheme = $aParams['theme'];
                    }
                } else {
                    $sTheme = '';
                }
                if ($sTheme) {
                    $sTheme = 'themes/' . $sTheme . '/';
                }
                if (isset($aParams['plugin'])) {
                    $sFile = Plugin::GetTemplateFile($aParams['plugin'], $aParams['file']);
                } else {
                    $sFile = Config::Get('path.skins.dir') . '/' . $sSkin . '/' . $sTheme . $aParams['file'];
                }
            }
            if (isset($aParams['prepare'])) {
                $sAssetName = (empty($aParams['asset']) ? $sFile : $aParams['asset']);
                // Грязноватый хак, но иначе нам не получить ссылку
                $aFileData = array(
                    $sFile => array(
                        'name' => md5($sFile),
                        'prepare' => true,
                    ),
                );

                /** @var ModuleViewerAsset $oLocalViewerAsset */
                $oLocalViewerAsset = new ModuleViewerAsset();
                $oLocalViewerAsset->AddFiles(F::File_GetExtension($sFile, true), $aFileData, $sAssetName);
                $oLocalViewerAsset->Prepare();
                //$sUrl = $oLocalViewerAsset->AssetFileUrl(F::File_NormPath($sFile));
                $aLinks = $oLocalViewerAsset->GetPreparedAssetLinks();
                $sUrl = reset($aLinks);
            } else {
                $sUrl = E::ModuleViewerAsset()->File2Link($sFile, 'skin/' . $sSkin . '/');
            }
        }
    } else {
        // Need URL to asset dir
        $sUrl = E::ModuleViewer()->GetAssetUrl() . 'skin/' . $aParams['skin'] . '/';
    }

    return $sUrl;
}

// EOF