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
 * Class ModuleImg_EntityImage
 *
 * @method SetImage($oParam)
 * @method SetWidth($iParam)
 * @method SetHeight($iParam)
 * @method SetMime($sParam)
 * @method SetInfo($aParam)
 * @method SetFilename($sPaam)
 *
 * @method object GetImage()
 * @method string GetFilename()
 * @method string GetDriver()
 */
class ModuleImg_EntityImage extends Entity {

    protected $_fResizeScaleLimit = 0.5;
    protected $aOptions;

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

        $this->oPxImage = $this->_createPixieImage();
    }

    protected function _createPixieImage() {

        $oFakePixie = new ModuleImg_EntityPixie();
        $oPixieImage = new \PHPixie\Image($oFakePixie);

        return $oPixieImage;
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
                $iColor = hexdec(substr($xColor, 2));
            } elseif ($xColor[0] == '#' && strlen($xColor) == 7) {
                $iColor = hexdec(substr($xColor, 1));
            } elseif (strlen($xColor) == 6) {
                $iColor = hexdec($xColor);
            } else {
                $iColor = intval($xColor);
            }
        } else {
            $iColor = intval($xColor);
        }
        return $iColor;
    }

    /**
     * @param DataArray|array $aOptions
     *
     * @return $this
     */
    public function SetOptions($aOptions) {

        if (!($aOptions instanceof DataArray)) {
            $this->aOptions = new DataArray($aOptions);
        } else {
            $this->aOptions = $aOptions;
        }
        return $this;
    }

    public function GetOptions() {

        return $this->aOptions;
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
        $sFormat = '';
        if ($sMime) {
            list(, $sFormat) = explode('/', $sMime);
        } elseif ($sFile = $this->GetFilename()) {
            $sFormat = E::ModuleImg()->GetFormat($sFile);
        }
        return $sFormat;
    }

    /**
     * Gets image width (or null)
     *
     * @return int|null
     */
    public function GetWidth() {

        $iWidth = $this->getProp('width');
        if (!$iWidth) {
            if ($oImage = $this->GetImage()) {
                return $oImage->width;
            }
        }
        return $iWidth;
    }

    /**
     * Gets image height (or null)
     *
     * @return int|null
     */
    public function GetHeight() {

        $iHeight = $this->getProp('height');
        if (!$iHeight) {
            if ($oImage = $this->GetImage()) {
                return $oImage->height;
            }
        }
        return $iHeight;
    }

    /**
     * @return bool
     */
    public function IsMultiframe() {

        if (($oImage = $this->GetImage()) && (E::ModuleImg()->GetDriver() != 'GD')) {
            return $oImage->image->getImageIterations();
        }
        return false;
    }

    /**
     * @param int $iFrame
     *
     * @return $this
     */
    public function KillAnimation($iFrame = 0) {

        if (($oImage = $this->GetImage()) && ($this->IsMultiframe())) {
            foreach ($oImage->image as $iIndex => $oFrame) {
                if ($iIndex == $iFrame) {
                    $oImage->image = $oFrame->getImage();
                }
            }
        }
        return $this;
    }

    /**
     * Creates new image
     *
     * @param int        $iWidth
     * @param int        $iHeight
     * @param int|string $xColor
     * @param int|float  $nOpacity
     *
     * @return ModuleImg_EntityImage
     */
    public function Create($iWidth, $iHeight, $xColor = 0xffffff, $nOpacity = 0.0) {

        $oPxImage = $this->_createPixieImage();
        $oImage = $oPxImage->create($iWidth, $iHeight, $this->_color($xColor), $nOpacity);
        if ($oImage) {
            $this->SetImage($oImage);
        }
        return $this;
    }

    /**
     * Read image from file
     *
     * @param string      $sFile
     *
     * @return ModuleImg_EntityImage
     */
    public function Read($sFile) {

        $oPxImage = $this->_createPixieImage();
        if ($aSize = getimagesize($sFile, $aImageInfo)) {
            $oImage = $oPxImage->read($sFile);
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
     * @param int  $iWidth  - New width
     * @param int  $iHeight - New size
     * @param bool $bFit    - Fit image to new sizes
     *
     * @return ModuleImg_EntityImage
     * @throws Exception
     */
    public function ResizeByScale($iWidth = null, $iHeight = null, $bFit = true) {

        if ($oImage = $this->GetImage()) {
            if ($iWidth && $iHeight) {
                $fWScale = $iWidth / $this->GetWidth();
                $fHScale = $iHeight / $this->GetHeight();
                $fScale = ($bFit ? min($fWScale, $fHScale) : max($fWScale, $fHScale));
            } elseif ($iWidth) {
                $fScale = $iWidth / $this->GetWidth();
            } elseif ($iHeight) {
                $fScale = $iHeight / $this->GetHeight();
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
            $this->SetWidth($iWidth);
            $this->SetHeight($iHeight);
        }
        return $this;
    }

    /**
     * Resize image
     *
     * @param null $iWidth
     * @param null $iHeight
     * @param bool $bFit
     *
     * @return $this
     * @throws Exception
     */
    public function Resize($iWidth = null, $iHeight = null, $bFit = true) {

        if ($oImage = $this->GetImage()) {
            if ($iWidth && $iHeight) {
                $fWScale = $iWidth / $this->GetWidth();
                $fHScale = $iHeight / $this->GetHeight();
                $fScale = ($bFit ? min($fWScale, $fHScale) : max($fWScale, $fHScale));
                $iWidth = ceil($this->GetWidth() * $fScale);
                $iHeight = ceil($this->GetHeight() * $fScale);
            } elseif ($iWidth) {
                $fScale = $iWidth / $this->GetWidth();
                $iHeight = ceil($this->GetHeight() * $fScale);
            } elseif ($iHeight) {
                $fScale = $iHeight / $this->GetHeight();
                $iWidth = ceil($this->GetWidth() * $fScale);
            } else {
                throw new \Exception('Either width or height must be set');
            }

            $oImage->resize($iWidth, $iHeight, $bFit);
            $this->SetWidth($iWidth);
            $this->SetHeight($iHeight);
        }
        return $this;
    }

    /**
     * Crop image
     *
     * @param int $iWidth  - Width to crop to
     * @param int $iHeight - Height to crop to
     * @param int $iPosX   - X coordinate of crop start position
     * @param int $iPosY   - Y coordinate of crop start position
     *
     * @return ModuleImg_EntityImage
     */
    public function Crop($iWidth, $iHeight, $iPosX = 0, $iPosY = 0) {

        if ($oImage = $this->GetImage()) {
            $oImage->crop($iWidth, $iHeight, $iPosX, $iPosY);
            if ($iWidth) {
                $this->SetWidth($iWidth);
            }
            if ($iHeight) {
                $this->SetHeight($iHeight);
            }
        }
        return $this;
    }

    /**
     * Rotate image
     *
     * @param int|float  $nAngle   - Rotation angle in degrees
     * @param int|string $xColor   - Background color
     * @param int|float  $nOpacity - Background opacity
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
     * @param bool $bHorizontally - Whether to flip image horizontally
     * @param bool $bVertically   - Whether to flip image vertically
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
     * @param ModuleImg_EntityImage $oOverlay - Image to overlay over the current one
     * @param int                   $iX       - X coordinate of the overlay
     * @param int                   $iY       - Y coordinate of the overlay
     *
     * @return ModuleImg_EntityImage
     */
    public function Overdraw($oOverlay, $iX = 0, $iY = 0) {

        if ($oImage = $this->GetImage()) {
            if ($oOverImage = $oOverlay->GetImage()) {
                $oImage->overlay($oOverImage, $iX, $iY);
            }
        }
        return $this;
    }

    /**
     * Changes canvas size via overdraw method
     *
     * @param int        $iWidth
     * @param int        $iHeight
     * @param int        $iDX
     * @param int        $iDY
     * @param int|string $xBgColor
     * @param int|float  $nOpacity
     */
    protected function _canvasSizeOrdinary($iWidth, $iHeight, $iDX, $iDY, $xBgColor = 0xffffff, $nOpacity = 0) {

        if ($oImage = $this->GetImage()) {
            $iColor = $this->_color($xBgColor);
            $oBackImg = $this->Create($iWidth, $iHeight, $iColor, $nOpacity);
            $iX = round($iDX / 2);
            $iY = round($iDY / 2);
            $oBackImg->GetImage()->overlay($oImage, $iX, $iY);
            $this->SetImage($oBackImg->GetImage());
            $this->SetWidth($iWidth);
            $this->SetHeight($iHeight);
        }
    }

    /**
     * Changes canvas size with multiframe support
     *
     * @param int $iWidth
     * @param int $iHeight
     * @param int $iDX
     * @param int $iDY
     */
    protected function _canvasSizeMultiframe($iWidth, $iHeight, $iDX, $iDY) {

        if ($oImage = $this->GetImage()) {
            if ($iDX >= 0 && $iDY >= 0) {
                $nX = round($iDX / 2);
                $nY = round($iDY / 2);
                $oImage->image->setPage($iWidth, $iHeight, $nX, $nY);
                foreach ($oImage->image as $frame) {
                    $frame->setImagePage($iWidth, $iHeight, $nX, $nY);
                }
            } elseif ($iDX < 0 && $iDY >= 0) {
                $this->Crop($iWidth, $this->GetHeight(), -round($iDX / 2), 0);
                $this->_canvasSizeMultiframe($iWidth, $iHeight, 0, $iDY);
            } elseif ($iDX >= 0 && $iDY < 0) {
                $this->Crop($this->GetWidth(), $iHeight, 0, -round($iDY / 2));
                $this->_canvasSizeMultiframe($iWidth, $iHeight, $iDX, 0);
            } else {
                $this->Crop($iWidth, $iHeight, -round($iDX / 2), round($iDY / 2));
            }
            $this->SetWidth($iWidth);
            $this->SetHeight($iHeight);
        }
    }

    /**
     * Changes canvas size (from center)
     *
     * @param int        $iWidth
     * @param int        $iHeight
     * @param int|string $xBgColor
     * @param int|float  $nOpacity
     *
     * @return $this
     */
    public function CanvasSize($iWidth, $iHeight, $xBgColor = 0xffffff, $nOpacity = 0.0) {

        if ($oImage = $this->GetImage()) {
            $nDX = $iWidth - $this->GetWidth();
            $nDY = $iHeight - $this->GetHeight();
            if ($nDX || $nDY) {
                if (E::ModuleImg()->GetDriver() != 'GD' && $this->IsMultiframe()) {
                    $this->_canvasSizeMultiframe($iWidth, $iHeight, $nDX, $nDY);
                } else {
                    $this->_canvasSizeOrdinary($iWidth, $iHeight, $nDX, $nDY, $xBgColor, $nOpacity);
                }
            }
        }
        return $this;
    }

    /**
     * Write text on image
     *
     * @param string     $sText        - Text to write
     * @param int        $iSize        - Font size
     * @param string     $sFontFile    - Path to font file
     * @param int        $iX           - X coordinate of the baseline of the first line of text (from left border)
     * @param int        $iY           - Y coordinate of the baseline of the first line of text (from top border)
     * @param int|string $xColor       - Text color (e.g 0xffffff or 32842 or 'ff9933')
     * @param int|float  $nOpacity     - Text opacity
     * @param int|float  $nAngle       - Counter clockwise text rotation angle
     * @param int        $iWrapWidth   - Width to wrap text at. Null means no wrapping.
     * @param int        $iLineSpacing - Line spacing multiplier
     *
     * @return ModuleImg_EntityImage
     */
    public function Text($sText, $iSize, $sFontFile, $iX, $iY, $xColor = 0x000000, $nOpacity = 1.0, $nAngle = 0.0, $iWrapWidth = null, $iLineSpacing = 1) {

        if ($oImage = $this->GetImage()) {
            $oImage->text($sText, $iSize, $sFontFile, $iX, $iY, $this->_color($xColor), $nOpacity, $iWrapWidth, $iLineSpacing, $nAngle);
        }
        return $this;
    }

    /**
     * Save image to file
     *
     * @param string          $sFile    - Filename to save
     * @param array|DataArray $aOptions - Options
     *
     * @return string|bool
     */
    public function Save($sFile, $aOptions = array()) {

        $aOptions = F::Array_Merge($this->GetOptions(), $aOptions);
        if ($oImage = $this->GetImage()) {
            F::File_CheckDir(dirname($sFile));
            $sFormat = (isset($aOptions['save_as']) ? $aOptions['save_as'] : $this->GetFormat());
            if ($sFormat == 'jpeg') {
                $nQuality = (isset($aOptions['quality']) ? $aOptions['quality'] : 100);
                $oImage->save($sFile, $sFormat, $nQuality);
            } else {
                $oImage->save($sFile, $sFormat);
            }
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

        /** @var PHPixie\Image $oImage */
        if ($oImage = $this->GetImage()) {
            if ($sImageFormat) {
                $sImageFormat = E::ModuleImg()->GetFormat($sImageFormat);
                $oImage->render($sImageFormat);
            } else {
                $oImage->render();
            }
        }
    }

}


// EOF