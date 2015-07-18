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
 * @package engine.modules
 * @since   1.0
 */
class ModuleViewerAsset_EntityPackageJs extends ModuleViewerAsset_EntityPackage {

    protected $sOutType = 'js';

    public function Init() {

        $this->aHtmlLinkParams = array(
            'tag'  => 'script',
            'attr' => array(
                'type' => 'text/javascript',
                'src'  => '@link',
            ),
            'pair' => true,
        );
    }

    protected function InitCompressor() {

        if (Config::Get('compress.js.use')) {
            F::IncludeLib('JShrink-1.0.1/src/JShrink/Minifier.php');
            // * Получаем параметры из конфигурации
            return true;
        }
        return false;
    }

    public function Compress($sContents) {

        if (strpos($sContents, $this->sMarker)) {
            $sContents = preg_replace_callback(
                '|\/\*\[' . preg_quote($this->sMarker) . '\s(?P<file>[\w\-\.\/]+)\sbegin\]\*\/(?P<content>.+)\/\*\[' . preg_quote($this->sMarker) . '\send\]\*\/\s*|sU',
                function($aMatches){
                    if (substr($aMatches['file'], -7) != '.min.js') {
                        $sResult = \JShrink\Minifier::minify($aMatches['content']);
                    } else {
                        $sResult = $aMatches['content'];
                    }
                    return $sResult;
                },
                $sContents
            );
        } else {
            $sContents = \JShrink\Minifier::minify($sContents);
        }

        return $sContents;
    }

    public function PrepareFile($sFile, $sDestination) {

        $sContents = F::File_GetContents($sFile);
        if ($sContents !== false) {
            $sContents = $this->PrepareContents($sContents, $sFile, $sDestination);
            if (F::File_PutContents($sDestination, $sContents) !== false) {
                return $sDestination;
            }
        }
        F::SysWarning('Can not prepare asset file "' . $sFile . '"');
    }

    public function PreProcess() {

        if ($this->aFiles) {
            $this->InitCompressor();
        }
        return parent::PreProcess();
    }

    /**
     * @param string $sDestination
     *
     * @return bool
     */
    public function CheckDestination($sDestination) {

        if (Config::Get('compress.js.force')) {
            return false;
        }
        return parent::CheckDestination($sDestination);
    }

    public function BuildLink($aLink) {

        if (empty($aLink['throw']) && !empty($aLink['compress']) && C::Get('compress.js.gzip') && C::Get('compress.js.merge') && C::Get('compress.js.use')) {
            $aLink['link'] = $aLink['link']
                . ((isset($_SERVER['HTTP_ACCEPT_ENCODING']) && stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'GZIP') !== FALSE) ? '.gz.js' : '');
        }

        return parent::BuildLink($aLink);

    }


    public function Process() {

        $bResult = true;
        foreach ($this->aLinks as $nIdx => $aLinkData) {
            if (empty($aLinkData['throw']) && !empty($aLinkData['compress'])) {
                $sAssetFile = $aLinkData['asset_file'];
                $sExtension = 'min.' . F::File_GetExtension($sAssetFile);
                $sCompressedFile = F::File_SetExtension($sAssetFile, $sExtension);
                if (!$this->CheckDestination($sCompressedFile)) {
                    if (($sContents = F::File_GetContents($sAssetFile))) {
                        $sContents = $this->Compress($sContents);
                        if (F::File_PutContents($sCompressedFile, $sContents)) {
                            F::File_Delete($sAssetFile);
                            $this->aLinks[$nIdx]['link'] = F::File_SetExtension($this->aLinks[$nIdx]['link'], $sExtension);
                        }
                        if (C::Get('compress.js.gzip') && C::Get('compress.js.merge') && C::Get('compress.js.use')) {
                            // Сохраним gzip
                            $sCompressedContent = gzencode($sContents, 9);
                            F::File_PutContents($sCompressedFile . '.gz.js', $sCompressedContent);
                        }
                    }
                } else {
                    $this->aLinks[$nIdx]['link'] = F::File_SetExtension($this->aLinks[$nIdx]['link'], $sExtension);
                }
            }
        }
        return $bResult;
    }

    /**
     * Обработка контента
     *
     * @param string $sContents
     * @param string $sSource
     *
     * @return string
     */
    public function PrepareContents($sContents, $sSource) {

        if (C::Get('compress.js.use')) {
            $sFile = F::File_LocalDir($sSource);
            $sContents = '/*[' . $this->sMarker . ' ' . $sFile . ' begin]*/' . PHP_EOL
                . $sContents
                . PHP_EOL . '/*[' . $this->sMarker . ' end]*/' . PHP_EOL;
        }

        return $sContents;
    }


}

// EOF