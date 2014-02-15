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
            F::IncludeLib('JSMin-1.1.1/jsmin.php');
            // * Получаем параметры из конфигурации
            return true;
        }
        return false;
    }

    public function Compress($sContents) {

        $sContents = JSMin::minify($sContents);
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

    public function Process() {

        $bResult = true;
        foreach ($this->aLinks as $nIdx => $aLinkData) {
            if ((!isset($aLinkData['throw']) || !$aLinkData['throw']) && $aLinkData['compress']) {
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
                    }
                } else {
                    $this->aLinks[$nIdx]['link'] = F::File_SetExtension($this->aLinks[$nIdx]['link'], $sExtension);
                }
            }
        }
        return $bResult;
    }


}

// EOF