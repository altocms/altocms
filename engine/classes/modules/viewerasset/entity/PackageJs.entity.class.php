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
            if (F::File_PutContents($sDestination, $sContents)) {
                return $sDestination;
            }
        }
        F::SysWarning('Can not prepare asset file "' . $sFile . '"');
    }

    public function PreProcess() {

        if ($this->aFiles) {
            $this->InitCompressor();
        }
        parent::PreProcess();
    }

    public function Process() {

        foreach ($this->aLinks as $nIdx => $aLinkData) {
            if (isset($aLinkData['compress']) && $aLinkData['compress']) {
                $sFile = $aLinkData['file'];
                $sExtension = 'min.' . F::File_GetExtension($sFile);
                $sCompressedFile = F::File_SetExtension($sFile, $sExtension);
                if (!$this->CheckDestination($sCompressedFile)) {
                    if (($sContents = F::File_GetContents($sFile))) {
                        $sContents = $this->Compress($sContents);
                        if (F::File_PutContents($sCompressedFile, $sContents)) {
                            F::File_Delete($sFile);
                            $this->aLinks[$nIdx]['link'] = F::File_SetExtension($this->aLinks[$nIdx]['link'], $sExtension);
                        }
                    }
                } else {
                    $this->aLinks[$nIdx]['link'] = F::File_SetExtension($this->aLinks[$nIdx]['link'], $sExtension);
                }
            }
        }
    }


}

// EOF