<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */
F::IncludeLib('PHPixie/Image.php');

class ModuleImg extends Module {

    protected $oPxImage;

    protected $nError = 0;

    public function Init() {

        $oPx = new ModuleImgPx();
        $this->oPxImage = new \PHPixie\Image($oPx);
    }

    public function _create($nWidth, $nHeight, $sColor = 0xffffff, $nOpacity = 0) {

        return $this->oPxImage->image->_create($nWidth, $nHeight, $sColor, $nOpacity);
    }

    protected function _read($sFile) {

        return $this->oPxImage->read($sFile);
    }

    public function Duplicate($sFile) {

        $this->nError = 0;
        if (preg_match('~^(.+)-(\d+x\d+)\.[a-z]+$~i', $sFile, $aMatches)) {
            $sOriginal = $aMatches[1];
            list($nW, $nH) = explode('x', $aMatches[2]);
            try {
                if (F::File_Exists($sOriginal) && ($oImg = $this->_read($sOriginal))) {
                    $oImg->resize($nW, $nH, false)->save($sFile);
                }
            } catch(ErrorException $oE) {
                $this->nError = -1;
            }
        }
        if (!$this->nError) {
            return $sFile;
        }
    }

    public function Render($sFile) {

        if ($oImg = $this->_read($sFile)) {
            $oImg->render();
            return true;
        }
    }
}

class ModuleImgPx extends LsObject {

    public $config;

    public function __construct() {

        $this->config = new ModuleImgConfig();
    }
}

class ModuleImgConfig extends LsObject {

    public function get($sProp) {

        if ($sProp == 'image.default.driver') {
            F::IncludeLib('PHPixie/Image/Driver.php');
            F::IncludeLib('PHPixie/Image/GD.php');
            return 'GD';
        }
        $s = 1;
    }
}

// EOF