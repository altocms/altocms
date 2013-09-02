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
    protected $aDrivers = array(
        'gd' => 'GD',
        'imagick' => 'Imagick',
        'gmagick' => 'Gmagick',
    );
    protected $sDefaultDriver = 'GD';
    protected $aParams = array();

    public function Init() {

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
                    $aInfo = Imagick::getVersion();
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
        if (!isset($aParams['driver'])) {
            $sDriver = strtolower($aParams['driver']);
        } else {
            $sDriver = strtolower($this->sDefaultDriver);
        }
        if (isset($this->aDrivers[$sDriver])) {
            return $this->aDrivers[$sDriver];
        }
        return $this->sDefaultDriver;
    }

    public function SetConfig($sConfigKey) {

        if (Config::Get('images.settings.' . $sConfigKey)) {
            $this->sConfig = $sConfigKey;
        }
    }

    public function GetConfigKey() {

        return $this->sConfig;
    }

    public function LoadParams($sConfigKey) {

        $aParams = Config::Get('default');
        if ($sConfigKey != 'default') {
            $aParams = F::Array_Merge($aParams, Config::Get($sConfigKey));
        }
        return $aParams;
    }

    public function GetParams($sConfigKey = null) {

        if (!$sConfigKey) {
            $sConfigKey = $this->GetConfigKey();
        }
        if (!Config::Get('images.settings.' . $sConfigKey)) {
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
     * @return \PHPixie\Image
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
     * @return \PHPixie\Image
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
     * @return \PHPixie\Image
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
        return $oImg->resize($nWidth, $nHeight, $bFit);
    }

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

        if ($nW < $nWidth) {
            $nWidth = $nW;
        }

        if ($nH < $nHeight) {
            $nHeight = $nH;
        }

        if ($nHeight == $nH && $nWidth == $nW) {
            return $oImg;
        }

        $oImg->crop($nWidth, $nHeight, $nPosX, $nPosY);

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
            $oImg->crop($nNewSize, $nNewSize, ($nWidth - $nNewSize) / 2, ($nHeight - $nNewSize) / 2);
        } else {
            $oImg->crop($nNewSize, $nNewSize, 0, 0);
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
            $oImg->crop($nNewWidth, $nNewHeight, ($nWidth - $nNewWidth) / 2, ($nHeight - $nNewHeight) / 2);
        } else {
            $oImg->crop($nNewWidth, $nNewHeight, 0, 0);
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
        if (preg_match('~^(.+)-(\d+x\d+)\.[a-z]+$~i', $sFile, $aMatches)) {
            $sOriginal = $aMatches[1];
            list($nW, $nH) = explode('x', $aMatches[2]);
            return $this->Copy($sOriginal, $sFile, $nW, $nH, false);
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
                $oImg->save($sDestination);
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
     * Delete image file and its duplicates
     *
     * @param $sFile
     *
     * @return bool
     */
    public function Delete($sFile) {

        return F::File_Delete($sFile) && F::File_DeleteAs($sFile . '-*.*');
    }

}

// EOF