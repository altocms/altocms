<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class ModuleImg_EntityImage extends Entity {

    public function __construct($aParams) {

        $aDefault = array(
            'driver' => $this->GetDriver(),
            'width' => 0,
            'height' => 0,
            'color' => 0xffffff,
            'opacity' => 0,
            'file' => '',
            'image' => null,
        );
        $aParams = F::Array_Merge($aDefault, $aParams);
        parent::__construct($aParams);
        $this->oPxImage = new \PHPixie\Image(new ModuleImgPx());
    }

    protected function _color($xColor) {

        if (is_string($xColor)) {
            if (substr($xColor, 0, 2) == '0x') {
                $nColor = hexdec(substr($xColor, 2));
            } elseif (strlen($xColor) == 6) {
                $nColor = hexdec($xColor);
            } else {
                $nColor = intval($xColor);
            }
        } else {
            $nColor = intval($xColor);
        }
        return $nColor;
    }

    public function GetMime() {

        $sMime = $this->getProp('mime');
        if (!$sMime && ($sFormat = $this->GetFormat())) {
            $sMime = 'image/' . $sFormat;
        }
        return $sMime;
    }

    public function GetFormat() {

        $sMime = $this->getProp('mime');
        if ($sMime) {
            list(, $sFormat) = explode('/', $sMime);
        } elseif ($sFile = $this->GetFilename()) {
            $sFormat = strtolower(pathinfo($sFile, PATHINFO_EXTENSION));
            if ($sFormat == 'jpg') {
                $sFormat = 'jpeg';
            }
        }
        return $sFormat;
    }

    /**
     * Creates new image
     *
     * @param int        $nWidth
     * @param int        $nHeight
     * @param int|string $xColor
     * @param int        $nOpacity
     *
     * @return $this
     */
    public function Create($nWidth, $nHeight, $xColor = 0xffffff, $nOpacity = 0) {

        $oPxImage = new \PHPixie\Image(new ModuleImgPx());
        $oImage = $oPxImage->create($nWidth, $nHeight, $this->_color($xColor), $nOpacity);
        if ($oImage) {
            $this->SetImage($oImage);
        }
        return $this;
    }

    public function Read($sFile, $sConfigKey = null) {

        $oPxImage = new \PHPixie\Image(new ModuleImgPx());
        if ($aSize = getimagesize($sFile, $aImageInfo)) {
            $oImage = $oPxImage->read($sFile, $sConfigKey);
            if ($oImage) {
                $this->SetImage($oImage);
                $this->SetWidth($aSize[0]);
                $this->SetHeight($aSize[1]);
                $this->SetMime($aSize['mime']);
                $this->SetInfo($aImageInfo);
                $this->SetFilename($sFile);
            }
        }
        return $this;
    }

    public function Resize($nWidth = null, $nHeight = null, $bFit = true) {

        if ($oImage = $this->GetImage()) {
            $oImage->resize($nWidth, $nHeight, $bFit);
            if ($nWidth) {
                $this->SetWidth($nWidth);
            }
            if ($nHeight) {
                $this->SetHeight($nHeight);
            }
        }
        return $this;
    }

    public function Crop($nWidth, $nHeight, $nPosX = 0, $nPosY = 0) {

        if ($oImage = $this->GetImage()) {
            $oImage->crop($nWidth, $nHeight, $nPosX, $nPosY);
            if ($nWidth) {
                $this->SetWidth($nWidth);
            }
            if ($nHeight) {
                $this->SetHeight($nHeight);
            }
        }
        return $this;
    }

    public function Rotate($nAngle, $xColor = 0xffffff, $nOpacity = 0) {

        if ($oImage = $this->GetImage()) {
            $oImage->rotate($nAngle, $this->_color($xColor), $nOpacity);
        }
        return $this;
    }

    public function Flip($bHorizontally = false, $bVertically = false) {

        if ($oImage = $this->GetImage()) {
            if ($bHorizontally || $bVertically) {
                $oImage->flip($bHorizontally, $bVertically);
            }
        }
        return $this;
    }

    public function Save($sFile) {

        if ($oImage = $this->GetImage()) {
            $oImage->save($sFile);
            return $sFile;
        }
    }

    public function Render($sImageFormat = null) {

        if ($oImage = $this->GetImage()) {
            if ($sImageFormat) {
                $oImage->render($sImageFormat);
            } else {
                $oImage->render();
            }
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

        if (substr($sProp, -7) == '.driver') {
            $aStr = explode('.', $sProp);
            if (isset($aStr[1])) {
                $sConfigKey = $aStr[1];
            } else {
                $sConfigKey = 'default';
            }
            $sDriver = $this->Img_GetDriver($sConfigKey);
            F::IncludeLib('PHPixie/Image/' . $sDriver . '.php');
            return $sDriver;
        }
    }
}


// EOF