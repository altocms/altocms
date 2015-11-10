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
F::IncludeLib('PHPixie/Image/Driver.php');

class ModuleImg extends Module {

    protected $nError = 0;
    protected $sConfig = 'default';
    protected $aDrivers
        = array(
            'gmagick' => 'Gmagick',
            'imagick' => 'Imagick',
            'gd'      => 'GD',
        );
    protected $sDefaultDriver = 'GD';
    protected $aAvailableDrivers = array();
    protected $aInitDrivers = array();

    protected $aOptions = array();

    public function Init() {

        $this->aAvailableDrivers = $this->GetDriversInfo();
        $this->sDefaultDriver = F::Array_FirstKey($this->aAvailableDrivers);
    }

    /**
     * Info about all drivers
     *
     * @return array
     */
    public function GetDriversInfo() {

        $aInfo = array();
        foreach ($this->aDrivers as $sDriver) {
            $sVersion = $this->GetDriverVersion($sDriver);
            if ($sVersion) {
                $aInfo[$sDriver] = $sVersion;
            }
        }
        return $aInfo;
    }

    /**
     * Info about driver's version
     *
     * @param string $sDriver
     *
     * @return bool
     */
    public function GetDriverVersion($sDriver) {

        $sVersion = false;
        $sDriver = strtolower($sDriver);
        if (isset($this->aDrivers[$sDriver])) {
            if ($this->aDrivers[$sDriver] == 'Imagick') {
                if (class_exists('Imagick')) {
                    $img = new \Imagick();
                    $aInfo = $img->getVersion();
                    $sVersion = $aInfo['versionString'];
                    if (preg_match('/\w+\s\d+\.[\d\.\-]+/', $sVersion, $aMatches)) {
                        $sVersion = $aMatches[0];
                    }
                }
            } elseif ($this->aDrivers[$sDriver] == 'Gmagick') {
                if (class_exists('Gmagick')) {
                    $img = new \Gmagick();
                    $aInfo = $img->getVersion();
                    $sVersion = $aInfo['versionString'];
                    if (preg_match('/\w+\s\d+\.[\d\.\-]+/', $sVersion, $aMatches)) {
                        $sVersion = $aMatches[0];
                    }
                }
            } else {
                if (function_exists('gd_info')) {
                    $aInfo = gd_info();
                    $sVersion = $aInfo['GD Version'];
                    if (preg_match('/\d+\.[\d\.]+/', $sVersion, $aMatches)) {
                        $sVersion = $aMatches[0];
                    }
                }
            }
        }
        return $sVersion;
    }

    /**
     * Returns driver name by key
     *
     * @return string
     */
    public function GetDriver() {

        $sResult = '';

        $aDrivers = F::Str2Array(Config::Get('module.image.libs'));
        if ($aDrivers) {
            foreach($aDrivers as $sDriver) {
                if (isset($this->aDrivers[$sDriver])) {
                    $sDriver = $this->aDrivers[$sDriver];
                }
                if (isset($this->aAvailableDrivers[$sDriver])) {
                    $sResult = $sDriver;
                    break;
                }
            }
        }
        if (!$sResult) {
            $sResult = $this->sDefaultDriver;
        }
        if (!isset($this->aInitDrivers[$sResult])) {
            $this->InitDriver($sResult);
            $this->aInitDrivers[$sResult] = true;
        }

        return $sResult;
    }

    /**
     * @param $sDriver
     */
    public function InitDriver($sDriver) {

        // nothing
    }

    /**
     * @param string $sFileExtension
     * @param string $sPreset
     * @param array  $aOptions
     *
     * @return array
     */
    public function GetOptions($sFileExtension = '*', $sPreset = null, $aOptions = array()) {

        if (is_array($sPreset) && empty($aOptions)) {
            $aOptions = $sPreset;
            $sPreset = null;
        }
        if (!$sPreset && $sPreset !== 'default') {
            $sPreset = 'images.' . $sPreset;
        }
        $aConfigOptions = E::ModuleUploader()->GetConfig($sFileExtension, $sPreset);
        if ($aConfigOptions && $aOptions) {
            /** @var DataArray $aParams */
            $aOptions = F::Array_Merge($aConfigOptions, $aOptions);
        } elseif (!$aOptions) {
            $aOptions = $aConfigOptions;
        }
        return $aOptions;
    }

    /**
     * @param string $sFile
     *
     * @return string
     */
    public function GetFormat($sFile) {

        $sFormat = strtolower(pathinfo($sFile, PATHINFO_EXTENSION));
        if ($sFormat == 'jpg') {
            $sFormat = 'jpeg';
        }
        return $sFormat;
    }

    /* ********************************************************************************
     * Image manipulations
     *
     */

    /**
     * Creates image
     *
     * @param int        $iWidth
     * @param int        $iHeight
     * @param int|string $sColor
     * @param float      $fOpacity
     *
     * @return ModuleImg_EntityImage
     */
    public function Create($iWidth, $iHeight, $sColor = 0xffffff, $fOpacity = 0.0) {

        $aParams = array(
            'width' => $iWidth,
            'height' => $iHeight,
            'color' => $sColor,
            'opacity' => $fOpacity,
        );

        /** @var ModuleImg_EntityImage $oImage */
        $oImage  = Engine::GetEntity('Img_Image', $aParams);
        $oImage->Create($iWidth, $iHeight, $sColor, $fOpacity);
        $oImage->SetOptions($this->GetOptions());

        return $oImage;
    }

    /**
     * Read image
     *
     * @param string $sFile
     * @param array  $aOptions
     *
     * @return ModuleImg_EntityImage
     */
    public function Read($sFile, $aOptions = array()) {

        $aOptions = $this->GetOptions($sFile, $aOptions);

        /** @var ModuleImg_EntityImage $oImage */
        $oImage  = Engine::GetEntity('Img_Image', isset($aOptions['params']) ? (array)$aOptions['params'] : array());
        $oImage->Read($sFile);
        $oImage->SetOptions($aOptions);

        return $oImage;
    }

    /**
     * @param string|object $xImage
     * @param int           $iWidth
     * @param int           $iHeight
     * @param bool          $bFit - вписывать новое изображение в заданные рамки
     *
     * @return ModuleImg_EntityImage
     */
    public function Resize($xImage, $iWidth = null, $iHeight = null, $bFit = true) {

        if (!$xImage || (!$iWidth && !$iHeight) || (!is_numeric($iWidth) && !is_numeric($iHeight))) {
            return false;
        }
        if (!is_object($xImage)) {
            $oImg = $this->Read($xImage);
        } else {
            $oImg = $xImage;
        }
        return $oImg->Resize($iWidth, $iHeight, $bFit);
    }

    /**
     * Crop image
     *
     * @param string|object $xImage
     * @param int           $iWidth
     * @param int           $iHeight
     * @param int           $iPosX
     * @param int           $iPosY
     *
     * @return bool|ModuleImg_EntityImage|object
     */
    public function Crop($xImage, $iWidth, $iHeight = null, $iPosX = null, $iPosY = null) {

        if (!$xImage) {
            return false;
        }
        if (!is_object($xImage)) {
            $oImg = $this->Read($xImage);
        } else {
            $oImg = $xImage;
        }
        if (!$iWidth && !$iHeight) {
            return $oImg;
        }
        $nW = $oImg->getWidth();
        $nH = $oImg->getHeight();

        if (!$iHeight) {
            $iHeight = $iWidth;
        }

        if ($nW < $iWidth) {
            $iWidth = $nW;
        }

        if ($nH < $iHeight) {
            $iHeight = $nH;
        }

        if ($iHeight == $nH && $iWidth == $nW) {
            return $oImg;
        }

        $oImg->Crop($iWidth, $iHeight, $iPosX, $iPosY);

        return $oImg;
    }

    /**
     * Crop image from center
     *
     * @param string|object $xImage
     * @param int           $iWidth
     * @param int           $iHeight
     *
     * @return bool|ModuleImg_EntityImage
     */
    public function CropCenter($xImage, $iWidth, $iHeight = null) {

        if (!$xImage) {
            return false;
        }
        if (!is_object($xImage)) {
            $oImg = $this->Read($xImage);
        } else {
            $oImg = $xImage;
        }
        if (!$iWidth && !$iHeight) {
            return $oImg;
        }
        $nW = $oImg->getWidth();
        $nH = $oImg->getHeight();

        if (!$iHeight) {
            $iHeight = $iWidth;
        }

        if ($nW < $iWidth) {
            $iWidth = $nW;
        }

        if ($nH < $iHeight) {
            $iHeight = $nH;
        }

        if ($iHeight == $nH && $iWidth == $nW) {
            return $oImg;
        }

        $oImg->Crop($iWidth, $iHeight, round(($nW - $iWidth) / 2), round(($nH - $iHeight) / 2));

        return $oImg;
    }

    /**
     * @param string|object $xImage
     * @param bool          $bCenter
     *
     * @return ModuleImg_EntityImage
     */
    public function CropSquare($xImage, $bCenter = true) {

        if (!$xImage) {
            return false;
        }
        if (!is_object($xImage)) {
            $oImg = $this->Read($xImage);
        } else {
            $oImg = $xImage;
        }
        $nWidth = $oImg->getWidth();
        $nHeight = $oImg->getHeight();

        // * Если высота и ширина совпадают, то возвращаем изначальный вариант
        if ($nWidth == $nHeight) {
            return $oImg;
        }

        // * Вырезаем квадрат из центра
        $nNewSize = min($nWidth, $nHeight);

        if ($bCenter) {
            $oImg->Crop($nNewSize, $nNewSize, ($nWidth - $nNewSize) / 2, ($nHeight - $nNewSize) / 2);
        } else {
            $oImg->Crop($nNewSize, $nNewSize, 0, 0);
        }
        // * Возвращаем объект изображения
        return $oImg;
    }

    /**
     * Вырезает максимально возможный прямоугольный в нужной пропорции
     *
     * @param string|object $xImage    - Объект изображения
     * @param int           $iW        - Ширина для определения пропорции
     * @param int           $iH        - Высота для определения пропорции
     * @param bool          $bCenter   - Вырезать из центра
     *
     * @return object
     */
    public function CropProportion($xImage, $iW, $iH, $bCenter = true) {

        if (!$xImage ) {
            return false;
        }
        if (!is_object($xImage)) {
            $oImg = $this->Read($xImage);
        } else {
            $oImg = $xImage;
        }
        $nWidth = $oImg->getWidth();
        $nHeight = $oImg->getHeight();

        // * Если высота и ширина уже в нужных пропорциях, то возвращаем изначальный вариант
        $nProp = round($iW / $iH, 2);
        if (round($nWidth / $nHeight, 2) == $nProp) {
            return $oImg;
        }

        // * Вырезаем прямоугольник из центра
        if (round($nWidth / $nHeight, 2) <= $nProp) {
            $nNewWidth = $nWidth;
            $nNewHeight = round($nNewWidth / $nProp);
        } else {
            $nNewHeight = $nHeight;
            $nNewWidth = $nNewHeight * $nProp;
        }

        if ($bCenter) {
            $oImg->Crop($nNewWidth, $nNewHeight, ($nWidth - $nNewWidth) / 2, ($nHeight - $nNewHeight) / 2);
        } else {
            $oImg->Crop($nNewWidth, $nNewHeight, 0, 0);
        }

        // * Возвращаем объект изображения
        return $oImg;
    }

    /**
     * @param string|object $xImage
     * @param int           $iCoordX
     * @param int           $iCoordY
     * @param string|object $xWatermark
     * @param bool          $bTopLeft
     *
     * @return object
     */
    public function WatermarkImg($xImage, $iCoordX, $iCoordY, $xWatermark, $bTopLeft = true) {

        if (!$xImage ) {
            return false;
        }
        if (!is_object($xImage)) {
            $oImg = $this->Read($xImage);
        } else {
            $oImg = $xImage;
        }
        if (!is_object($xWatermark)) {
            $oImgMark = $this->Read($xWatermark);
        } else {
            $oImgMark = $xWatermark;
        }
        if (!$bTopLeft) {
            $iCoordX = $oImg->GetWidth() - $oImgMark->GetWidth() - $iCoordX;
            $iCoordY = $oImg->GetHeight() - $oImgMark->GetHeight() - $iCoordY;
        }
        $oImg->Overdraw($oImgMark, $iCoordX, $iCoordY);

        return $oImg;
    }

    /**
     * @param ModuleImg_EntityImage $oImg
     * @param DataArray|array       $aOptions
     *
     * @return bool
     */
    public function Transform($oImg, $aOptions) {

        $bChanged = false;
        if (is_object($oImg) && $aOptions) {
            if (is_array($aOptions)) {
                $aOptions = new DataArray($aOptions);
            }
            if ($aOptions['animation'] === false) {
                $oImg->KillAnimation();
            }
            $iW = $aOptions['max_width'];
            $iH = $aOptions['max_height'];
            if (($iW && $iW < $oImg->GetWidth()) || ($iH && $iH < $oImg->GetHeight())) {
                $oImg->Resize($iW, $iH, true);
                $bChanged = true;
            }
            if ($aOptions['watermark.enable'] && $aOptions['watermark.image']) {
                $sMarkImg = F::File_Exists($aOptions['watermark.image.file'], $aOptions['watermark.image.path']);
                if ($sMarkImg) {
                    $bTopLeft = (bool)$aOptions['watermark.image.topleft'];
                    if ($aOptions['watermark.image.position']) {
                        list($iCoordX, $iCoordY) = explode(',', $aOptions['watermark.image.position']);
                    } else {
                        $iCoordX = $iCoordY = 0;
                    }
                    if ($oImg = $this->WatermarkImg($oImg, $iCoordX, $iCoordY, $sMarkImg, $bTopLeft)) {
                        $bChanged = true;
                    }
                }
            }
        }

        return $bChanged;
    }

    /**
     * Duplicates image file with other sizes
     *
     * @param string $sFile
     * @param bool   $bForceRewrite
     * @param array  $aOptions
     *
     * @return string|bool
     */
    public function Duplicate($sFile, $bForceRewrite = false, $aOptions = null) {

        if (func_num_args() == 2 && is_array($bForceRewrite)) {
            $aOptions = $bForceRewrite;
            $bForceRewrite = false;
        }
        $this->nError = 0;
        if (!F::File_Exists($sFile) || $bForceRewrite) {
            $sOriginal = $this->OriginalFile($sFile, $aOptions);
            if ($aOptions) {
                $sResultFile = false;
                if (F::File_Exists($sOriginal)) {
                    $iW = $aOptions['width'];
                    $iH = $aOptions['height'];
                    $sModifier = $aOptions['modifier'];
                    $aOptions['save_as'] = (isset($aOptions['save_as']) ? $aOptions['save_as'] : $this->GetFormat($sFile));

                    if (!F::File_Exists($sOriginal) || !($oImg = $this->Read($sOriginal))) {
                        return false;
                    }

                    if ($sModifier == 'max') {
                        if ($iW >= $oImg->GetWidth() && $iH >= $oImg->GetHeight()) {
                            $sResultFile = $this->Copy($sOriginal, $sFile, $oImg->GetWidth(), $oImg->GetHeight(), true);
                        } else {
                            $sResultFile = $this->Copy($sOriginal, $sFile, $iW, $iH, true);
                        }
                    } elseif ($sModifier == 'fit') {
                        $sResultFile = $this->Copy($sOriginal, $sFile, $iW, $iH, true);
                    } elseif ($sModifier == 'pad') {
                        $sResultFile = $this->Copy($sOriginal, $sFile, $iW, $iH, false);
                    } elseif ($sModifier == 'crop') {
                        if ($oImg = $this->Resize($oImg, $iW, $iH, false)) {
                            $oImg = $this->CropCenter($oImg, $iW, $iH);
                            $sResultFile = $oImg->Save($sFile, $aOptions);
                        }
                    } else {
                        $oImg = $this->Resize($oImg, $iW, $iH, true);
                        // real size can differ from request size, so we need change canvas size
                        $iDX = ($iW ? $iW - $oImg->GetWidth() : 0);
                        $iDY = ($iH ? $iH - $oImg->GetHeight() : 0);
                        if ($iDX < 0 || $iDY < 0) {
                            $oImg = $this->CropCenter($oImg, $oImg->GetWidth() + $iDX, $oImg->GetHeight() + $iDY);
                            $iDX = ($iW ? $iW - $oImg->GetWidth() : 0);
                            $iDY = ($iH ? $iH - $oImg->GetHeight() : 0);
                        }
                        if ($iDX || $iDY) {
                            $xBgColor = (isset($aOptions['bg_color']) ? $aOptions['bg_color'] : null);
                            if ($xBgColor) {
                                $oImg->CanvasSize($iW, $iH, $xBgColor, 1);
                            } else {
                                $oImg->CanvasSize($iW, $iH);
                            }
                            $sResultFile = $oImg->Save($sFile, $aOptions);
                        } else {
                            $sResultFile = $oImg->Save($sFile, $aOptions);
                        }
                    }
                }
                return $sResultFile;
            }
        }
        if (F::File_Exists($sFile) && !$this->nError) {
            return $sFile;
        }
        return false;
    }

    /**
     * Copy image file with other sizes
     *
     * @param string $sFile        - full path of source image file
     * @param string $sDestination - full path or newname only
     * @param int    $iWidth       - new width
     * @param int    $iHeight      - new height
     * @param bool   $bFit         - to fit image's sizes into new sizes
     * @param array  $aOptions     - to fit image's sizes into new sizes
     *
     * @return string|bool
     */
    public function Copy($sFile, $sDestination, $iWidth = null, $iHeight = null, $bFit = true, $aOptions = array()) {

        if (basename($sDestination) == $sDestination) {
            $sDestination = dirname($sFile) . '/' . $sDestination;
        }
        try {
            if (F::File_Exists($sFile) && ($oImg = $this->Read($sFile))) {
                if ($iWidth || $iHeight) {
                    $oImg->Resize($iWidth, $iHeight, $bFit);
                }
                return $oImg->Save($sDestination, $aOptions);
            }
        } catch(ErrorException $oE) {
            $this->nError = -1;
        }
        return false;
    }

    /**
     * Rename image file and set new sizes
     *
     * @param string $sFile        - full path of source image file
     * @param string $sDestination - full path or newname only
     * @param int    $iWidth       - new width
     * @param int    $iHeight      - new height
     * @param bool   $bFit         - to fit image's sizes into new sizes
     *
     * @return string|bool
     */
    public function Rename($sFile, $sDestination, $iWidth = null, $iHeight = null, $bFit = true) {

        if ($sDestination = $this->Copy($sFile, $sDestination, $iWidth, $iHeight, $bFit)) {
            F::File_Delete($sFile);
            return $sDestination;
        }
        return false;
    }

    /**
     * Set new image's sises and save to source file
     *
     * @param string $sFile        - full path of source image file
     * @param int    $iWidth       - new width
     * @param int    $iHeight      - new height
     * @param bool   $bFit         - to fit image's sizes into new sizes
     *
     * @return string|bool
     */
    public function ResizeFile($sFile, $iWidth = null, $iHeight = null, $bFit = true) {

        if ($sDestination = $this->Copy($sFile, basename($sFile), $iWidth, $iHeight, $bFit)) {
            return $sDestination;
        }
        return false;
    }

    /**
     * Renders image from file to browser
     *
     * @param string $sFile
     * @param string $sImageFormat
     *
     * @return bool
     */
    public function RenderFile($sFile, $sImageFormat = null) {

        if ($oImg = $this->Read($sFile)) {
            if (!$sImageFormat) {
                $sImageFormat = $oImg->GetFormat();
                if (!in_array($sImageFormat, array('jpeg', 'png', 'gif'))) {
                    $sImageFormat = null;
                }
            }
            $oImg->Render($sImageFormat);
            return true;
        }
        return false;
    }

    /**
     * Transform image from file using config preset and/or options
     *
     * @param string $sFile
     * @param string $sPreset
     * @param array  $aOptions
     *
     * @return bool
     */
    public function TransformFile($sFile, $sPreset = 'default', $aOptions = array()) {

        if (is_array($sPreset)) {
            $aOptions = $sPreset;
            $sPreset = null;
        }
        if ($sPreset) {
            $aPresetOptions = $this->GetOptions($sFile, $sPreset, $aOptions);
        } else {
            $aPresetOptions = array();
        }
        if ($aPresetOptions && $aOptions) {
            /** @var DataArray $aParams */
            $aOptions = F::Array_Merge($aPresetOptions, $aOptions);
        } elseif (!$aOptions) {
            $aOptions = $aPresetOptions;
        }
        $bResult = false;

        if ($oImg = $this->Read($sFile)) {
            $bChanged = $this->Transform($oImg, $aOptions);
            if ($bChanged) {
                $oImg->Save($sFile, $aOptions);
            }
            $bResult = true;
        }

        return $bResult ? $sFile : false;
    }

    /**
     * Delete image file and its duplicates
     *
     * @param string $sFile
     *
     * @return bool
     */
    public function Delete($sFile) {

        return F::File_Delete($sFile) && $this->DeleteDuplicates($sFile);
    }

    /**
     * @param string $sFile
     *
     * @return bool
     */
    public function DeleteDuplicates($sFile) {

        return F::File_DeleteAs($sFile . '-*.*');
    }

    /**
     * @param string $sFile
     * @param array  $aOptions
     *
     * @return string
     */
    public function OriginalFile($sFile, &$aOptions) {

        if (!$aOptions) {
            $aOptions = array();
        }
        if (preg_match('~^(.+)-(\d*x\d*)(\-([a-z]+))?\.[a-z]+$~i', $sFile, $aMatches)) {
            $sOriginal = $aMatches[1];
            list($nW, $nH) = explode('x', $aMatches[2]);
            $sModifier = (isset($aMatches[4]) ? $aMatches[4] : '');
            $aOptions['width'] = ($nW ? intval($nW) : null);
            $aOptions['height'] = ($nH ? intval($nH) : null);
            $aOptions['modifier'] = $sModifier;
        } else {
            $sOriginal = $sFile;
        }
        return $sOriginal;
    }

    /**
     * Возвращает валидный Html код тега <img>
     *
     * @param string $sUrl
     * @param array  $aParams
     *
     * @return string
     */
    public function BuildHTML($sUrl, $aParams) {

        if (substr($sUrl, 0, 1) == '@') {
            $sUrl = F::File_RootUrl() . substr($sUrl, 1);
        }
        $sText = '<img src="' . $sUrl . '" ';
        if (isset($aParams['title']) && $aParams['title'] != '') {
            $sText .= ' title="' . htmlspecialchars($aParams['title']) . '" ';

            // * Если не определен ALT заполняем его тайтлом
            if (!isset($aParams['alt'])) {
                $aParams['alt'] = $aParams['title'];
            }
        }

        if (isset($aParams['img_width']) && is_numeric($aParams['img_width'])) {
            $sText .= " width=\"{$aParams['img_width']}%\"";
        }

        if (isset($aParams['align']) && in_array($aParams['align'], array('left', 'right', 'center'))) {
            if ($aParams['align'] == 'center') {
                $sText .= ' class="image-center"';
            } else {
                $sText .= ' align="' . htmlspecialchars($aParams['align']) . '" ';
            }
        }
        $sAlt = isset($aParams['alt'])
            ? ' alt="' . htmlspecialchars($aParams['alt']) . '"'
            : ' alt=""';
        $sText .= $sAlt . ' />';

        return $sText;
    }

    /**
     * Returns mime type for images only
     *
     * @param string $sFile
     *
     * @return string|null
     */
    static public function MimeType($sFile) {

        $sMimeType = F::File_MimeType($sFile);
        if (strpos($sMimeType, 'image/') === 0) {
            return $sMimeType;
        }
        return null;
    }

    /**
     * Makes new avatar or profile photo from skin default image
     *
     * @param string $sFile
     * @param string $sPrefix
     * @param int    $iSize
     *
     * @return string|bool
     */
    public function AutoresizeSkinImage($sFile, $sPrefix, $iSize) {

        $xResult = false;
        $iSize = intval($iSize);
        $sImageFile = $this->_getDefaultSkinImage($sFile, $sPrefix, $iSize);
        if ($sImageFile) {
            if ($iSize) {
                $oImg = $this->Resize($sImageFile, $iSize, $iSize);
                if ($oImg) {
                    $xResult = $oImg->SaveUpload($sFile);
                }
            } else {
                $xResult = $this->Copy($sImageFile, $sFile);
            }
        } else {
            // Файла нет, создаем пустышку, чтоб в дальнейшем не было пустых запросов
            //$oImg = $this->Create($nSize, $nSize);
            //$xResult = $oImg->SaveUpload($sFile);
        }
        return $xResult;
    }

    /**
     * Gets default avatar or profile photo for the skin
     *
     * @param string $sFile
     * @param string $sPrefix
     * @param int    $iSize
     *
     * @return bool|string
     */
    protected function _getDefaultSkinImage($sFile, $sPrefix, $iSize) {

        $sImageFile = '';
        $sName = basename($sFile);
        if (preg_match('/^' . preg_quote($sPrefix) . '_([a-z0-9-]+)(_(male|female))?(\.([\-0-9a-z]+))?(\.([a-z]+))$/i', $sName, $aMatches)) {
            $sSkin = $aMatches[1];
            $sType = $aMatches[3];
            if ($sSkin) {
                // Определяем путь до аватар скина
                $sPath = Config::Get('path.skins.dir') . $sSkin . '/assets/images/avatars/';
                if (!is_dir($sPath)) {
                    // старая структура скина
                    $sPath = Config::Get('path.skins.dir') . $sSkin . '/images/';
                }

                // Если задан тип male/female, то ищем сначала с ним
                if ($sType) {
                    $sImageFile = $this->_seekDefaultSkinImage($sPath, $sPrefix . '_' . $sType, $iSize);
                }
                // Если аватар не найден
                if (!$sImageFile) {
                    $sImageFile = $this->_seekDefaultSkinImage($sPath, $sPrefix, $iSize);
                }
            }
        }
        return $sImageFile ? $sImageFile : false;
    }

    /**
     * Seeks default avatar or profile photo in the skin's image area
     *
     * @param string $sPath
     * @param string $sName
     * @param int    $iSize
     *
     * @return bool|string
     */
    protected function _seekDefaultSkinImage($sPath, $sName, $iSize) {

        $sImageFile = '';
        if ($aFiles = glob($sPath . $sName . '.*')) {
            // Найден файл вида image_male.png
            $sImageFile = array_shift($aFiles);
        } elseif ($aFiles = glob($sPath . $sName . '_*.*')) {
            // Найдены файлы вида image_male_100x100.png
            $aFoundFiles = array();
            foreach ($aFiles as $sFile) {
                if (preg_match('/_(\d+)x(\d+)\./', basename($sFile), $aMatches)) {
                    $nI = intval(max($aMatches[1], $aMatches[2]));
                    $aFoundFiles[$nI] = $sFile;
                } else {
                    $aFoundFiles[0] = $sFile;
                }
            }
            ksort($aFoundFiles);
            $sImageFile = reset($aFoundFiles);
            while (list($iImgSize, $sImgFile) = each($aFoundFiles)) {
                if ($iImgSize >= $iSize) {
                    $sImageFile = $sImgFile;
                    break;
                }
            }
        }
        return $sImageFile ? $sImageFile : false;
    }

}

// EOF