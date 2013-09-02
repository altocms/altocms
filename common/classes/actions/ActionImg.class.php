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

    public function EventUploads() {

        // Раз оказались здесь, то нет соответствующего изображения. Пробуем его создать
        $sUrl = F::File_RootUrl() . '/' . $this->sCurrentEvent . '/' . implode('/', $this->GetParams());
        $sFile = F::File_Url2Dir($sUrl);
        $sNewFile = $this->Img_Duplicate($sFile);

        if (!$sNewFile) {
            if (strpos(basename($sFile), 'avatar') === 0) {
                // Запрашивается аватара
                $sNewFile = $this->_makeAvatar($sFile);
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

    protected function _makeAvatar($sFile) {

        $sAvatarFile = $this->_getDefaultAvatar($sFile);
        if ($sAvatarFile) {
            $oImg = $this->Img_Read($sAvatarFile);
        } else {
            // Файла нет, создаем пустышку, чтоб в дальнейшем не было пустых запросов
            $oImg = $this->Img_Create(100, 100);
        }
        $oImg->SaveUpload($sFile);
        return $sFile;
    }

    protected function _getDefaultAvatar($sFile) {

        $sAvatarFile = '';
        if (strpos(basename($sFile), 'avatar') === 0) {
            $aPaths = explode('_', basename($sFile));
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
                    $sAvatarFile = $this->_seekDefaultAvatar($sPath, 'avatar_' . $sType);
                }
                // Если аватара не найдена
                if (!$sAvatarFile) {
                    $sAvatarFile = $this->_seekDefaultAvatar($sPath, 'avatar_' . $sType);
                }
            }
        }
        return $sAvatarFile ? $sAvatarFile : false;
    }

    protected function _seekDefaultAvatar($sPath, $sName) {

        $sAvatarFile = '';
        if ($aFiles = glob($sPath . $sName . '.*')) {
            // Найдена аватара вида avatar_male.png
            $sAvatarFile = array_shift($aFiles);
        } elseif ($aFiles = glob($sPath . $sName . '_*.*')) {
            // Найдены аватары вида avatar_male_100x100.png
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
            $sAvatarFile = array_shift($aFiles);
        }
        return $sAvatarFile ? $sAvatarFile : false;
    }

}

// EOF