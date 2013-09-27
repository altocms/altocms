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

    protected $_fResizeScaleLimit = 0.5;

    public function __construct($aParams) {

        $aDefault = array(
            'driver'   => $this->GetDriver(),
            'width'    => 0,
            'height'   => 0,
            'color'    => 0xffffff,
            'opacity'  => 0,
            'filename' => '',
            'image'    => null,
        );
        $aParams = F::Array_Merge($aDefault, $aParams);
        parent::__construct($aParams);
        $this->oPxImage = new \PHPixie\Image(new ModuleImgPx());
    }

    /**
     * Convert hex-decoded color into integer
     *
     * @param int|string $xColor
     *
     * @return int
     */
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

    /**
     * Gets mime type of image
     *
     * @return string
     */
    public function GetMime() {

        $sMime = $this->getProp('mime');
        if (!$sMime && ($sFormat = $this->GetFormat())) {
            $sMime = 'image/' . $sFormat;
        }
        return $sMime;
    }

    /**
     * Gets image format
     *
     * @return string
     */
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
     * Gets image width (or null)
     *
     * @return int|null
     */
    public function GetWidth() {

        if ($oImage = $this->GetImage()) {
            return $oImage->width;
        }
    }

    /**
     * Gets image height (or null)
     *
     * @return int|null
     */
    public function GetHeight() {

        if ($oImage = $this->GetImage()) {
            return $oImage->height;
        }
    }

    /**
     * Creates new image
     *
     * @param int        $nWidth
     * @param int        $nHeight
     * @param int|string $xColor
     * @param int        $nOpacity
     *
     * @return ModuleImg_EntityImage
     */
    public function Create($nWidth, $nHeight, $xColor = 0xffffff, $nOpacity = 0) {

        $oPxImage = new \PHPixie\Image(new ModuleImgPx());
        $oImage = $oPxImage->create($nWidth, $nHeight, $this->_color($xColor), $nOpacity);
        if ($oImage) {
            $this->SetImage($oImage);
        }
        return $this;
    }

    /**
     * Read image from file
     *
     * @param string      $sFile
     * @param string|null $sConfigKey
     *
     * @return ModuleImg_EntityImage
     */
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

    /**
     * Resize image
     *
     * @param int  $nWidth
     * @param int  $nHeight
     * @param bool $bFit
     *
     * @return ModuleImg_EntityImage
     * @throws Exception
     */
    public function Resize($nWidth = null, $nHeight = null, $bFit = true) {

        if ($oImage = $this->GetImage()) {
            if ($nWidth && $nHeight) {
                $fWScale = $nWidth / $oImage->width;
                $fHScale = $nHeight / $oImage->height;
                $fScale = ($bFit ? min($fWScale, $fHScale) : max($fWScale, $fHScale));
            }elseif($nWidth) {
                $fScale = $nWidth/$oImage->width;
            }elseif($nHeight) {
                $fScale = $nHeight/$oImage->height;
            }else {
                throw new \Exception('Either width or height must be set');
            }

            $fScale = round($fScale, 9);
            if ($fScale < 1 && $this->_fResizeScaleLimit && $this->_fResizeScaleLimit > $fScale) {
                $fResultScale = 1.0;
                while ($fResultScale > $fScale) {
                    $fStepScale = $fScale / $fResultScale;
                    if ($fStepScale < $this->_fResizeScaleLimit) {
                        $fStepScale = $this->_fResizeScaleLimit;
                    }
                    $oImage->scale($fStepScale);
                    $fResultScale = $fResultScale * $fStepScale;
                }
            } elseif ($fScale != 1.0) {
                $oImage->scale($fScale);
            }
        }
        return $this;
    }

    /**
     * Crop image
     *
     * @param int $nWidth
     * @param int $nHeight
     * @param int $nPosX
     * @param int $nPosY
     *
     * @return ModuleImg_EntityImage
     */
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

    /**
     * Rotate image
     *
     * @param int        $nAngle
     * @param int|string $xColor
     * @param int        $nOpacity
     *
     * @return ModuleImg_EntityImage
     */
    public function Rotate($nAngle, $xColor = 0xffffff, $nOpacity = 0) {

        if ($oImage = $this->GetImage()) {
            $oImage->rotate($nAngle, $this->_color($xColor), $nOpacity);
        }
        return $this;
    }

    /**
     * Flip image
     *
     * @param bool $bHorizontally
     * @param bool $bVertically
     *
     * @return ModuleImg_EntityImage
     */
    public function Flip($bHorizontally = false, $bVertically = false) {

        if ($oImage = $this->GetImage()) {
            if ($bHorizontally || $bVertically) {
                $oImage->flip($bHorizontally, $bVertically);
            }
        }
        return $this;
    }

    /**
     * @param ModuleImg_EntityImage $oOverlay
     * @param int                   $nX
     * @param int                   $nY
     *
     * @return ModuleImg_EntityImage
     */
    public function Overlay($oOverlay, $nX = 0, $nY = 0) {

        if ($oImage = $this->GetImage()) {
            if ($oOverImage = $oOverlay->GetImage()) {
                $oImage->overlay($oOverImage, $nX, $nY);
            }
        }
        return $this;
    }

    /**
     * @param string $sFile
     *
     * @return string|bool
     */
    public function Save($sFile) {

        if ($oImage = $this->GetImage()) {
            $oImage->save($sFile);
            return $sFile;
        }
        return false;
    }

    /**
     * @param string $sFile
     *
     * @return string|bool
     */
    public function SaveUpload($sFile) {

        if ($oImage = $this->GetImage()) {
            if ($sTmpFile = F::File_GetUploadDir() . F::RandomStr() . '.' . pathinfo($sFile, PATHINFO_EXTENSION)) {
                $oImage->save($sTmpFile);
                if ($this->Uploader_Move($sTmpFile, $sFile)) {
                    return $sFile;
                }
            }
        }
        return false;
    }

    /**
     * @param string $sImageFormat
     */
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