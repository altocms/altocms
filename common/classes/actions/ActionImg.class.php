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
 * @package actions
 * @since 1.0
 */
class ActionImg extends Action {

    const USER_AVATAR_SIZE = 100;
    const USER_PHOTO_SIZE = 250;
    const BLOG_AVATAR_SIZE = 100;

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->SetDefaultEvent('uploads');
    }

    protected function RegisterEvent() {

        $this->AddEvent('uploads', 'EventUploads');
    }

    /**
     * Makes image with new size
     */
    public function EventUploads() {

        // Раз оказались здесь, то нет соответствующего изображения. Пробуем его создать
        $sUrl = F::File_RootUrl() . '/' . $this->sCurrentEvent . '/' . implode('/', $this->GetParams());
        $sFile = F::File_Url2Dir($sUrl);
        $sNewFile = $this->Img_Duplicate($sFile);

        if (!$sNewFile) {
            if (preg_match('/\-(\d+)x(\d+)\.[a-z]{3}$/i', $sFile, $aMatches)) {
                $nSize = $aMatches[1];
            } else {
                $nSize = 0;
            }
            if (strpos(basename($sFile), 'avatar_blog') === 0) {
                // Запрашивается аватар блога
                $sNewFile = $this->_makeImage($sFile, 'avatar_blog', $nSize ? $nSize : self::BLOG_AVATAR_SIZE);
            } elseif (strpos(basename($sFile), 'avatar') === 0) {
                // Запрашивается аватар
                $sNewFile = $this->_makeImage($sFile, 'avatar', $nSize ? $nSize : self::USER_AVATAR_SIZE);
            } elseif (strpos(basename($sFile), 'user_photo') === 0) {
                // Запрашивается фото
                $sNewFile = $this->_makeImage($sFile, 'user_photo', $nSize ? $nSize : self::USER_PHOTO_SIZE);
            }
        }

        // Если файл успешно создан, то выводим его
        if ($sNewFile) {
            if (headers_sent($sFile, $nLine)) {
                Router::Location($sUrl . '?rnd=' . uniqid());
            } else {
                header_remove();
                $this->Img_RenderFile($sNewFile);
                exit;
            }
        }
        F::HttpHeader('404 Not Found');
        exit;
    }

    /**
     * Makes default avatar or profile photo
     *
     * @param $sFile
     * @param $sPrefix
     * @param $nSize
     *
     * @return mixed
     */
    protected function _makeImage($sFile, $sPrefix, $nSize) {

        $sImageFile = $this->_getDefaultImage($sFile, $sPrefix, $nSize);
        if ($sImageFile) {
            $oImg = $this->Img_Resize($sImageFile, $nSize, $nSize);
        } else {
            // Файла нет, создаем пустышку, чтоб в дальнейшем не было пустых запросов
            $oImg = $this->Img_Create($nSize, $nSize);
        }
        $oImg->SaveUpload($sFile);
        return $sFile;
    }

    /**
     * Gets default avatar or profile photo for the skin
     *
     * @param $sFile
     * @param $sPrefix
     * @param $nSize
     *
     * @return bool|mixed|string
     */
    protected function _getDefaultImage($sFile, $sPrefix, $nSize) {

        $sImageFile = '';
        $sName = basename($sFile);
        if (preg_match('/^' . preg_quote($sPrefix) . '([a-z0-9]+)?_([a-z0-9\.]+)(_(male|female))?([\-0-9a-z\.]+)?(\.[a-z]+)$/i', $sName, $aMatches)) {
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
                    $sImageFile = $this->_seekDefaultImage($sPath, $sPrefix . '_' . $sType, $nSize);
                }
                // Если аватар не найден
                if (!$sImageFile) {
                    $sImageFile = $this->_seekDefaultImage($sPath, $sPrefix, $nSize);
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
     * @return bool|mixed|string
     */
    protected function _seekDefaultImage($sPath, $sName, $nSize) {

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