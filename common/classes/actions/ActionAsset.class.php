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
 * @package actions
 * @since 1.0
 */
class ActionAsset extends Action {

    public function Init() {

    }

    protected function RegisterEvent() {

        $this->AddEvent('skin', 'EventSkin');
    }

    protected function EventSkin() {

        $aParams = $this->GetParams();
        $sSkinName = array_shift($aParams);
        $sRelPath = implode('/', $aParams);

        $sOriginalFile = Config::Get('path.skins.dir') . $sSkinName . '/' . $sRelPath;
        if (F::File_Exists($sOriginalFile)) {
            $sAssetFile = F::File_GetAssetDir() . 'skin/' . $sSkinName . '/' . $sRelPath;
            if (F::File_Copy($sOriginalFile, $sAssetFile)) {
                if (headers_sent($sFile, $nLine)) {
                    $sUrl = F::File_GetAssetUrl() . 'skin/' . $sSkinName . '/' . $sRelPath;
                    if (strpos($sUrl, '?')) {
                        $sUrl .= '&' . uniqid();
                    } else {
                        $sUrl .= '?' . uniqid();
                    }
                    Router::Location($sUrl);
                } else {
                    header_remove();
                    if ($sMimeType = F::File_MimeType($sAssetFile)) {
                        header('Content-Type: ' . $sMimeType);
                    }
                    echo file_get_contents($sAssetFile);
                    exit;
                }
            }
        }
        F::HttpHeader('404 Not Found');
        exit;
    }

}

// EOF