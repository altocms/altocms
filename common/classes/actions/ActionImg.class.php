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
            if (strpos(basename($sFile), 'avatar') === 0) {
                // Запрашивается аватар
                $sNewFile = $this->_makeImage($sFile, 'avatar', 100);
            } elseif (strpos(basename($sFile), 'user_photo') === 0) {
                // Запрашивается фото
                $sNewFile = $this->_makeImage($sFile, 'user_photo', 250);
            }
        }

        // Если файл успешно создан, то выводим его
        if ($sNewFile) {
            if (headers_sent()) {
                Router::Location($sUrl);
            } else {
                header_remove();
                $this->Img_RenderFile($sNewFile);
                exit;
            }
        }
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

        $sImageFile = $this->_getDefaultImage($sFile, $sPrefix);
        if ($sImageFile) {
            $oImg = $this->Img_Read($sImageFile);
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
     *
     * @return bool|mixed|string
     */
    protected function _getDefaultImage($sFile, $sPrefix) {

        $sImageFile = '';
        $sName = basename($sFile);
        if (strpos($sName, $sPrefix) === 0) {
            $sName = substr($sName, strlen($sPrefix));
            $aPaths = explode('_', $sName);
            if (count($aPaths) >= 2) {
                // Определяем путь до аватар скина
                $sPath = Config::Get('path.skins.dir') . $aPaths[1] . '/assets/images/avatars/';
                if ($n = strpos($aPaths[2], '.')) {
                    $sType = substr($aPaths[2], 0, $n);
                } else {
                    $sType = '';
                }
                // Если задан тип male/female, то ищем сначала с ним
                if ($sType) {
                    $sImageFile = $this->_seekDefaultImage($sPath, $sPrefix . '_' . $sType);
                }
                // Если аватар не найден
                if (!$sImageFile) {
                    $sImageFile = $this->_seekDefaultImage($sPath, $sPrefix . '_' . $sType);
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
     *
     * @return bool|mixed|string
     */
    protected function _seekDefaultImage($sPath, $sName) {

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
            krsort($aFoundFiles);
            $sImageFile = array_shift($aFiles);
        }
        return $sImageFile ? $sImageFile : false;
    }

}

// EOF