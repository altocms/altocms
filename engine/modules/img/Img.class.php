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

    public function SetConfig($sConfigKey) {

        if (Config::Get('images.settings.' . $sConfigKey)) {
            $this->sConfig = $sConfigKey;
        }
    }

    public function GetConfigKey() {

        return $this->sConfig;
    }

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

    /**
     * Duplicates image file with other size
     *
     * @param $sFile
     *
     * @return mixed
     */
    public function Duplicate($sFile) {

        $this->nError = 0;
        if (preg_match('~^(.+)-(\d+x\d+)\.[a-z]+$~i', $sFile, $aMatches)) {
            $sOriginal = $aMatches[1];
            list($nW, $nH) = explode('x', $aMatches[2]);
            try {
                if (F::File_Exists($sOriginal) && ($oImg = $this->Read($sOriginal))) {
                    $oImg->Resize($nW, $nH, false);
                    $oImg->save($sFile);
                }
            } catch(ErrorException $oE) {
                $this->nError = -1;
            }
        }
        if (!$this->nError) {
            return $sFile;
        }
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
     * Renders image from file to browser
     *
     * @param $sFile
     *
     * @return bool
     */
    public function Render($sFile) {

        if ($oImg = $this->Read($sFile)) {
            $oImg->render();
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

        return F::File_Delete($sFile) && F::File_DeleteAs($sFile) . '-*.*';
    }

}

// EOF