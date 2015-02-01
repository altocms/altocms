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

    public function IsMultiframe() {

        if (($oImage = $this->GetImage()) && (E::ModuleImg()->GetDriver() != 'GD')) {
            return $oImage->image->getImageIterations();
        }
    }

    /**
     * Creates new image
     *
     * @param int        $nWidth
     * @param int        $nHeight
     * @param int|string $xColor
     * @param float      $nOpacity
     *
     * @return ModuleImg_EntityImage
     */
    public function Create($nWidth, $nHeight, $xColor = 0xffffff, $nOpacity = 0.0) {

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
     * @param int  $nWidth  - New width
     * @param int  $nHeight - New size
     * @param bool $bFit    - Fit image to new sizes
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
            } elseif ($nWidth) {
                $fScale = $nWidth / $oImage->width;
            } elseif ($nHeight) {
                $fScale = $nHeight / $oImage->height;
            } else {
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
     * @param int $nWidth   - Width to crop to
     * @param int $nHeight  - Height to crop to
     * @param int $nPosX    - X coordinate of crop start position
     * @param int $nPosY    - Y coordinate of crop start position
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
     * @param float      $nAngle    - Rotation angle in degrees
     * @param int|string $xColor    - Background color
     * @param float      $nOpacity  - Background opacity
     *
     * @return ModuleImg_EntityImage
     */
    public function Rotate($nAngle, $xColor = 0xffffff, $nOpacity = 0.0) {

        if ($oImage = $this->GetImage()) {
            $oImage->rotate($nAngle, $this->_color($xColor), $nOpacity);
        }
        return $this;
    }

    /**
     * Flip image
     *
     * @param bool $bHorizontally   - Whether to flip image horizontally
     * @param bool $bVertically     - Whether to flip image vertically
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
     * @param ModuleImg_EntityImage $oOverlay   - Image to overlay over the current one
     * @param int                   $nX         - X coordinate of the overlay
     * @param int                   $nY         - Y coordinate of the overlay
     *
     * @return ModuleImg_EntityImage
     */
    public function Overdraw($oOverlay, $nX = 0, $nY = 0) {

        if ($oImage = $this->GetImage()) {
            if ($oOverImage = $oOverlay->GetImage()) {
                $oImage->overlay($oOverImage, $nX, $nY);
            }
        }
        return $this;
    }

    /**
     * Changes canvas size via overdraw method
     *
     * @param $nWidth
     * @param $nHeight
     * @param $nDX
     * @param $nDY
     */
    protected function _canvasSizeOrdinary($nWidth, $nHeight, $nDX, $nDY) {

        if ($oImage = $this->GetImage()) {
            $oBackImg = $this->Create($nWidth, $nHeight, 0xffffff, 0);
            $nX = round($nDX / 2);
            $nY = round($nDY / 2);
            $oBackImg->GetImage()->overlay($oImage, $nX, $nY);
            $this->SetImage($oBackImg->GetImage());
            $this->SetWidth($nWidth);
            $this->SetHeight($nHeight);
        }
    }

    /**
     * Changes canvas size with multiframe support
     *
     * @param $nWidth
     * @param $nHeight
     * @param $nDX
     * @param $nDY
     */
    protected function _canvasSizeMultiframe($nWidth, $nHeight, $nDX, $nDY) {

        if ($oImage = $this->GetImage()) {
            if ($nDX >= 0 && $nDY >= 0) {
                $nX = round($nDX / 2);
                $nY = round($nDY / 2);
                $oImage->image->setPage($nWidth, $nHeight, $nX, $nY);
                foreach($oImage->image as $frame) {
                    $frame->setImagePage($nWidth, $nHeight, $nX, $nY);
                }
            } elseif ($nDX < 0 && $nDY >= 0) {
                $this->Crop($nWidth, $this->GetHeight(), -round($nDX / 2), 0);
                $this->_canvasSizeMultiframe($nWidth, $nHeight, 0, $nDY);
            } elseif($nDX >= 0 && $nDY < 0) {
                $this->Crop($this->GetWidth(), $nHeight, 0, -round($nDY / 2));
                $this->_canvasSizeMultiframe($nWidth, $nHeight, $nDX, 0);
            } else {
                $this->Crop($nWidth, $nHeight, -round($nDX / 2), round($nDY / 2));
            }
            $this->SetWidth($nWidth);
            $this->SetHeight($nHeight);
        }
    }

    /**
     * Changes canvas size
     *
     * @param $nWidth
     * @param $nHeight
     *
     * @return $this
     */
    public function CanvasSize($nWidth, $nHeight) {

        if ($oImage = $this->GetImage()) {
            $nDX = $nWidth - $this->GetWidth();
            $nDY = $nHeight - $this->GetHeight();
            if ($nDX || $nDY) {
                if (E::ModuleImg()->GetDriver() == 'Imagick' && $this->IsMultiframe()) {
                    $this->_canvasSizeMultiframe($nWidth, $nHeight, $nDX, $nDY);
                } else {
                    $this->_canvasSizeOrdinary($nWidth, $nHeight, $nDX, $nDY);
                }
            }
        }
        return $this;
    }

    /**
     * Write text on image
     *
     * @param string     $sText        - Text to write
     * @param int        $nSize        - Font size
     * @param string     $sFontFile    - Path to font file
     * @param int        $nX           - X coordinate of the baseline of the first line of text (from left border)
     * @param int        $nY           - Y coordinate of the baseline of the first line of text (from top border)
     * @param int|string $xColor       - Text color (e.g 0xffffff or 32842 or 'ff9933')
     * @param float      $nOpacity     - Text opacity
     * @param float      $nAngle       - Counter clockwise text rotation angle
     * @param int        $nWrapWidth   - Width to wrap text at. Null means no wrapping.
     * @param int        $nLineSpacing - Line spacing multiplier
     *
     * @return ModuleImg_EntityImage
     */
    public function Text($sText, $nSize, $sFontFile, $nX, $nY, $xColor = 0x000000, $nOpacity = 1.0, $nAngle = 0.0, $nWrapWidth = null, $nLineSpacing = 1) {

        if ($oImage = $this->GetImage()) {
            $oImage->text($sText, $nSize, $sFontFile, $nX, $nY, $this->_color($xColor), $nOpacity, $nWrapWidth, $nLineSpacing, $nAngle);
        }
        return $this;
    }

    /**
     * Save image to file
     *
     * @param string $sFile - Filename to save
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
                if (F::File_CheckDir(dirname($sTmpFile))) {
                    $oImage->save($sTmpFile);
                    if (E::ModuleUploader()->Move($sTmpFile, $sFile)) {
                        return $sFile;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Renders and ouputs the image
     *
     * @param string $sImageFormat - Image format (gif, png or jpeg)
     */
    public function Render($sImageFormat = null) {

        if ($oImage = $this->GetImage()) {
            if ($sImageFormat) {
                $sImageFormat = strtolower($sImageFormat);
                if ($sImageFormat == 'jpg') {
                    $sImageFormat = 'jpeg';
                }
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
            $sDriver = E::ModuleImg()->GetDriver($sConfigKey);
            F::IncludeLib('PHPixie/Image/' . $sDriver . '.php');
            return $sDriver;
        }
    }
}


// EOF