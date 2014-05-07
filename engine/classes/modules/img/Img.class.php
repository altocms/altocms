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
    protected $aParams = array();

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
     * @param $sDriver
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
                    $aInfo = Gmagick::getVersion();
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
     * @param null $sConfigKey
     *
     * @return string
     */
    public function GetDriver($sConfigKey = null) {

        $aParams = $this->GetParams($sConfigKey);
        if (isset($aParams['driver'])) {
            $sDriver = strtolower($aParams['driver']);
        } else {
            $sDriver = strtolower($this->sDefaultDriver);
        }
        $aDrivers = F::Str2Array($sDriver);
        foreach($aDrivers as $sDriver) {
            if (isset($this->aDrivers[$sDriver])) {
                $sDriver = $this->aDrivers[$sDriver];
            }
            if (isset($this->aAvailableDrivers[$sDriver])) {
                return $sDriver;
            }
        }
        return $this->sDefaultDriver;
    }

    public function SetConfig($sConfigKey) {

        if (Config::Get('module.image.preset.' . $sConfigKey)) {
            $this->sConfig = $sConfigKey;
        }
    }

    public function GetConfigKey() {

        return $this->sConfig;
    }

    public function LoadParams($sConfigKey) {

        $aParams = Config::Get('module.image.preset.default');
        if ($sConfigKey != 'default') {
            $aParams = F::Array_Merge($aParams, Config::Get('module.image.preset.' . $sConfigKey));
        }
        return $aParams;
    }

    public function GetParams($sConfigKey = null) {

        if (!$sConfigKey) {
            $sConfigKey = $this->GetConfigKey();
        }
        if (!Config::Get('module.image.preset.' . $sConfigKey)) {
            $sConfigKey = 'default';
        }
        return $this->LoadParams($sConfigKey);
    }

    /* ********************************************************************************
     * Image manipulations
     *
     */

    /**
     * Creates image
     *
     * @param int        $nWidth
     * @param int        $nHeight
     * @param int|string $sColor
     * @param int        $nOpacity
     *
     * @return ModuleImg_EntityImage
     */
    public function Create($nWidth, $nHeight, $sColor = 0xffffff, $nOpacity = 0) {

        $aParams = array(
            'width' => $nWidth,
            'height' => $nHeight,
            'color' => $sColor,
            'opacity' => $nOpacity,
        );
        $oImage  = Engine::GetEntity('Img_Image', $aParams);
        return $oImage->Create($nWidth, $nHeight, $sColor, $nOpacity);
    }

    /**
     * Read image
     *
     * @param string $sFile
     * @param string $sConfigKey
     *
     * @return ModuleImg_EntityImage
     */
    public function Read($sFile, $sConfigKey = null) {

        if (!$sConfigKey) {
            $sConfigKey = $this->GetConfigKey();
        }
        $aParams = $this->GetParams($sConfigKey);
        $oImage  = Engine::GetEntity('Img_Image', $aParams);
        $oImage->Read($sFile, $sConfigKey);
        return $oImage;
    }

    /**
     * @param string|object $xImage
     * @param null          $nWidth
     * @param null          $nHeight
     * @param bool          $bFit     - вписывать новое изображение в заданные рамки
     *
     * @return ModuleImg_EntityImage
     */
    public function Resize($xImage, $nWidth = null, $nHeight = null, $bFit = true) {

        if (!$xImage || (!$nWidth && !$nHeight)) {
            return false;
        }
        if (!is_object($xImage)) {
            $oImg = $this->Read($xImage);
        } else {
            $oImg = $xImage;
        }
        return $oImg->Resize($nWidth, $nHeight, $bFit);
    }

    /**
     * Crop image
     *
     * @param string|object $xImage
     * @param int           $nWidth
     * @param int           $nHeight
     * @param int           $nPosX
     * @param int           $nPosY
     *
     * @return bool|ModuleImg_EntityImage|object
     */
    public function Crop($xImage, $nWidth, $nHeight = null, $nPosX = null, $nPosY = null) {

        if (!$xImage) {
            return false;
        }
        if (!is_object($xImage)) {
            $oImg = $this->Read($xImage);
        } else {
            $oImg = $xImage;
        }
        $nW = $oImg->getWidth();
        $nH = $oImg->getHeight();

        if (!$nHeight) {
            $nHeight = $nWidth;
        }

        if ($nW < $nWidth) {
            $nWidth = $nW;
        }

        if ($nH < $nHeight) {
            $nHeight = $nH;
        }

        if ($nHeight == $nH && $nWidth == $nW) {
            return $oImg;
        }

        $oImg->Crop($nWidth, $nHeight, $nPosX, $nPosY);

        return $oImg;
    }

    /**
     * Crop image from center
     *
     * @param string|object $xImage
     * @param int           $nWidth
     * @param int           $nHeight
     *
     * @return bool|ModuleImg_EntityImage|object
     */
    public function CropCenter($xImage, $nWidth, $nHeight = null) {

        if (!$xImage) {
            return false;
        }
        if (!is_object($xImage)) {
            $oImg = $this->Read($xImage);
        } else {
            $oImg = $xImage;
        }
        $nW = $oImg->getWidth();
        $nH = $oImg->getHeight();

        if (!$nHeight) {
            $nHeight = $nWidth;
        }

        if ($nW < $nWidth) {
            $nWidth = $nW;
        }

        if ($nH < $nHeight) {
            $nHeight = $nH;
        }

        if ($nHeight == $nH && $nWidth == $nW) {
            return $oImg;
        }

        $oImg->Crop($nWidth, $nHeight, round(($nW - $nWidth) / 2), round(($nH - $nHeight) / 2));

        return $oImg;
    }

    /**
     * @param string|object $xImage
     * @param bool          $bCenter
     *
     * @return bool
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
     * @param int           $nW        - Ширина для определения пропорции
     * @param int           $nH        - Высота для определения пропорции
     * @param bool          $bCenter   - Вырезать из центра
     *
     * @return object
     */
    public function CropProportion($xImage, $nW, $nH, $bCenter = true) {

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
        $nProp = round($nW / $nH, 2);
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
     * Duplicates image file with other sizes
     *
     * @param $sFile
     *
     * @return string|bool
     */
    public function Duplicate($sFile) {

        $this->nError = 0;
        $sOriginal = $this->OriginalFile($sFile, $aOptions);
        if ($aOptions) {
            if (!F::File_Exists($sOriginal)) {
                return false;
            }
            $nW = $aOptions['width'];
            $nH = $aOptions['height'];
            $sModifier = $aOptions['mod'];
            if ($sModifier == 'fit') {
                $sResultFile = $this->Copy($sOriginal, $sFile, $nW, $nH, true);
            } elseif ($sModifier == 'pad') {
                $sResultFile = $this->Copy($sOriginal, $sFile, $nW, $nH, false);
            } elseif ($sModifier == 'crop') {
                if ($oImg = $this->Resize($sOriginal, $nW, $nH, false)) {
                    $oImg = $this->CropCenter($oImg, $nW, $nH);
                    $sResultFile = $oImg->Save($sFile);
                }
            } else {
                $oImg = $this->Resize($sOriginal, $nW, $nH, true);
                // real size can differ from request size, so we need change canvas size
                $nDX = ($nW ? $nW - $oImg->GetWidth() : 0);
                $nDY = ($nH ? $nH - $oImg->GetHeight() : 0);
                if ($nDX < 0 || $nDY < 0) {
                    $oImg = $this->CropCenter($oImg, $oImg->GetWidth() + $nDX, $oImg->GetHeight() + $nDY);
                    $nDX = ($nW ? $nW - $oImg->GetWidth() : 0);
                    $nDY = ($nH ? $nH - $oImg->GetHeight() : 0);
                }
                if ($nDX || $nDY) {
                    $oImg->CanvasSize($nW, $nH);
                    $sResultFile = $oImg->Save($sFile);
                } else {
                    $sResultFile = $oImg->Save($sFile);
                }
            }
            return $sResultFile;
        }
        if (!F::File_Exists($sFile)) {
            return false;
        }
        if (!$this->nError) {
            return $sFile;
        }
    }

    /**
     * Copy image file with other sizes
     *
     * @param string $sFile        - full path of source image file
     * @param string $sDestination - full path or newname only
     * @param int    $nWidth       - new width
     * @param int    $nHeight      - new height
     * @param bool   $bFit         - to fit image's sizes into new sizes
     *
     * @return string|bool
     */
    public function Copy($sFile, $sDestination, $nWidth = null, $nHeight = null, $bFit = true) {

        if (basename($sDestination) == $sDestination) {
            $sDestination = dirname($sFile) . '/' . $sDestination;
        }
        try {
            if (F::File_Exists($sFile) && ($oImg = $this->Read($sFile))) {
                $oImg->Resize($nWidth, $nHeight, $bFit);
                $oImg->Save($sDestination);
                return $sDestination;
            }
        } catch(ErrorException $oE) {
            $this->nError = -1;
        }
    }

    /**
     * Rename image file and set new sizes
     *
     * @param string $sFile        - full path of source image file
     * @param string $sDestination - full path or newname only
     * @param int    $nWidth       - new width
     * @param int    $nHeight      - new height
     * @param bool   $bFit         - to fit image's sizes into new sizes
     *
     * @return string|bool
     */
    public function Rename($sFile, $sDestination, $nWidth = null, $nHeight = null, $bFit = true) {

        if ($sDestination = $this->Copy($sFile, $sDestination, $nWidth, $nHeight, $bFit)) {
            F::File_Delete($sFile);
            return $sDestination;
        }
    }

    /**
     * Set new image's sises and save to source file
     *
     * @param string $sFile        - full path of source image file
     * @param int    $nWidth       - new width
     * @param int    $nHeight      - new height
     * @param bool   $bFit         - to fit image's sizes into new sizes
     *
     * @return string|bool
     */
    public function ResizeFile($sFile, $nWidth = null, $nHeight = null, $bFit = true) {

        if ($sDestination = $this->Copy($sFile, basename($sFile), $nWidth, $nHeight, $bFit)) {
            return $sDestination;
        }
    }

    /**
     * Renders image from file to browser
     *
     * @param $sFile
     * @param $sImageFormat
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
    public function TransformFile($sFile, $sPreset, $aOptions = array()) {

        if (is_array($sPreset)) {
            $aOptions = $sPreset;
            $sPreset = null;
        }
        if ($sPreset) {
            $sOldKey = $this->GetConfigKey();
            $this->SetConfig($sPreset);
            $aParams = $this->GetParams();
        } else {
            $sOldKey = null;
            $aParams = array();
        }
        if ($aOptions) {
            $aParams = F::Array_Merge($aParams, $aOptions);
        }
        $bResult = false;

        if ($oImg = $this->Read($sFile)) {
            $nW = (isset($aParams['size']['width']) ? $aParams['size']['width'] : null);
            $nH = (isset($aParams['size']['height']) ? $aParams['size']['height'] : null);
            if (($nW && $nW < $oImg->GetWidth()) || ($nH && $nH < $oImg->GetHeight())) {
                $oImg->Resize($nW, $nH, true);
                $oImg->Save($sFile);
            }
            $bResult = true;
        }
        if ($sOldKey) {
            $this->SetConfig($sOldKey);
        }

        return $bResult ? $sFile : false;
    }

    /**
     * Delete image file and its duplicates
     *
     * @param $sFile
     *
     * @return bool
     */
    public function Delete($sFile) {

        return F::File_Delete($sFile) && $this->DeleteDuplicates($sFile);
    }

    public function DeleteDuplicates($sFile) {

        return F::File_DeleteAs($sFile . '-*.*');
    }

    public function OriginalFile($sFile, &$aOptions) {

        if (preg_match('~^(.+)-(\d*x\d+)(\-([a-z]+))?\.[a-z]+$~i', $sFile, $aMatches)) {
            $sOriginal = $aMatches[1];
            list($nW, $nH) = explode('x', $aMatches[2]);
            $sModifier = (isset($aMatches[4]) ? $aMatches[4] : '');
            $aOptions = array(
                'width' => ($nW ? intval($nW) : null),
                'height' => ($nH ? intval($nH) : null),
                'mod' => $sModifier,
            );
        } else {
            $aOptions = array();
            $sOriginal = $sFile;
        }
        return $sOriginal;
    }

    /**
     * Возвращает валидный Html код тега <img>
     *
     * @param $sUrl
     * @param $aParams
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
     * @param $sFile
     *
     * @return mixed
     */
    static public function MimeType($sFile) {

        $sMimeType = F::File_MimeType($sFile);
        if (strpos($sMimeType, 'image/') === 0) {
            return $sMimeType;
        }
    }

    /**
     * Makes new avatar or profile photo from skin default image
     *
     * @param $sFile
     * @param $sPrefix
     * @param $nSize
     *
     * @return string|bool
     */
    public function AutoresizeSkinImage($sFile, $sPrefix, $nSize) {

        $sImageFile = $this->_getDefaultSkinImage($sFile, $sPrefix, $nSize);
        if ($sImageFile) {
            if ($nSize) {
                $oImg = $this->Resize($sImageFile, $nSize, $nSize);
                $xResult = $oImg->SaveUpload($sFile);
            } else {
                $this->Copy($sImageFile, $sFile);
            }
        } else {
            // Файла нет, создаем пустышку, чтоб в дальнейшем не было пустых запросов
            //$oImg = $this->Create($nSize, $nSize);
            //$xResult = $oImg->SaveUpload($sFile);
            $xResult = false;
        }
        return $xResult;
    }

    /**
     * Gets default avatar or profile photo for the skin
     *
     * @param $sFile
     * @param $sPrefix
     * @param $nSize
     *
     * @return bool|string
     */
    protected function _getDefaultSkinImage($sFile, $sPrefix, $nSize) {

        $sImageFile = '';
        $sName = basename($sFile);
        if (preg_match('/^' . preg_quote($sPrefix) . '([a-z0-9]+)?_([a-z0-9\-]+)(_(male|female))?([\-0-9a-z\.]+)?(\.[a-z]+)$/i', $sName, $aMatches)) {
            $sName = $aMatches[1];
            $sSkin = $aMatches[2];
            $sType = $aMatches[4];
            $sExtension = $aMatches[6];
            if ($sExtension && substr($sSkin, -strlen($sExtension)) == $sExtension) {
                $sSkin = substr($sSkin, 0, strlen($sSkin)-strlen($sExtension));
            }
            if ($sSkin) {
                // Определяем путь до аватар скина
                $sPath = Config::Get('path.skins.dir') . $sSkin . '/assets/images/avatars/';
                if (!is_dir($sPath)) {
                    // старая структура скина
                    $sPath = Config::Get('path.skins.dir') . $sSkin . '/images/';
                }

                // Если задан тип male/female, то ищем сначала с ним
                if ($sType) {
                    $sImageFile = $this->_seekDefaultSkinImage($sPath, $sPrefix . '_' . $sType, $nSize);
                }
                // Если аватар не найден
                if (!$sImageFile) {
                    $sImageFile = $this->_seekDefaultSkinImage($sPath, $sPrefix, $nSize);
                }
            }
        }
        return $sImageFile ? $sImageFile : false;
    }

    /**
     * Seeks default avatar or profile photo in the skin's image area
     *
     * @param $sPath
     * @param $sName
     * @param $nSize
     *
     * @return bool|string
     */
    protected function _seekDefaultSkinImage($sPath, $sName, $nSize) {

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
            while (list($nImgSize, $sImgFile) = each($aFoundFiles)) {
                if ($nImgSize >= $nSize) {
                    $sImageFile = $sImgFile;
                    break;
                }
            }
        }
        return $sImageFile ? $sImageFile : false;
    }

}

// EOF