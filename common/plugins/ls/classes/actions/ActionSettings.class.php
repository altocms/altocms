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
 * Экшен обработки настроек профиля юзера (/settings/)
 *
 * @package actions
 * @since   1.0
 */
class PluginLs_ActionSettings extends PluginLs_Inherits_ActionSettings {

    protected function RegisterEvent() {

        $this->AddEventPreg('/^profile$/i', '/^upload-avatar/i', '/^$/i', 'EventUploadAvatar');
        $this->AddEventPreg('/^profile$/i', '/^resize-avatar/i', '/^$/i', 'EventResizeAvatar');
        $this->AddEventPreg('/^profile$/i', '/^remove-avatar/i', '/^$/i', 'EventRemoveAvatar');
        $this->AddEventPreg('/^profile$/i', '/^cancel-avatar/i', '/^$/i', 'EventCancelAvatar');

        $this->AddEventPreg('/^profile$/i', '/^upload-photo/i', '/^$/i', 'EventUploadPhoto');
        $this->AddEventPreg('/^profile$/i', '/^resize-photo/i', '/^$/i', 'EventResizePhoto');
        $this->AddEventPreg('/^profile$/i', '/^remove-photo/i', '/^$/i', 'EventRemovePhoto');
        $this->AddEventPreg('/^profile$/i', '/^cancel-photo/i', '/^$/i', 'EventCancelPhoto');

        $this->AddEventPreg('/^profile$/i', '/^upload-foto/i', '/^$/i', 'EventUploadPhoto');
        $this->AddEventPreg('/^profile$/i', '/^resize-foto/i', '/^$/i', 'EventResizePhoto');
        $this->AddEventPreg('/^profile$/i', '/^remove-foto/i', '/^$/i', 'EventRemovePhoto');
        $this->AddEventPreg('/^profile$/i', '/^cancel-foto/i', '/^$/i', 'EventCancelPhoto');

        parent::RegisterEvent();
    }

    /**
     * Получение размеров изображения после ресайза
     *
     * @param string $sParam
     *
     * @return array
     */
    protected function _getImageSize($sParam) {

        if ($aSize = F::GetRequest($sParam)) {
            if (isset($aSize['x']) && is_numeric($aSize['x']) && isset($aSize['y']) && is_numeric($aSize['y'])
                && isset($aSize['x2']) && is_numeric($aSize['x2']) && isset($aSize['y2']) && is_numeric($aSize['y2'])
            ) {
                foreach ($aSize as $sKey => $sVal) {
                    $aSize[$sKey] = intval($sVal);
                }
                if ($aSize['x'] < $aSize['x2']) {
                    $aSize['x1'] = $aSize['x'];
                } else {
                    $aSize['x1'] = $aSize['x2'];
                    $aSize['x2'] = $aSize['x'];
                }
                $aSize['w'] = $aSize['x2'] - $aSize['x1'];
                unset($aSize['x']);
                if ($aSize['y'] < $aSize['y2']) {
                    $aSize['y1'] = $aSize['y'];
                } else {
                    $aSize['y1'] = $aSize['y2'];
                    $aSize['y2'] = $aSize['y'];
                }
                $aSize['h'] = $aSize['y2'] - $aSize['y1'];
                unset($aSize['y']);

                return $aSize;
            }
        }

        return array();
    }

    /**
     * Загрузка временной картинки для аватара
     */
    protected function EventUploadAvatar() {

        // * Устанавливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax(F::AjaxRequest(true)?'json':'jsonIframe', false);

        $aUploadedFile = $this->GetUploadedFile('avatar');
        if (!$aUploadedFile) {
            $this->Message_AddError($this->Lang_Get('settings_profile_avatar_upload_error'), $this->Lang_Get('error'));
            return;
        }
        $sError = '';

        // Загружаем файл
        $sTmpFile = $this->Uploader_UploadLocal($aUploadedFile);
        if ($sTmpFile && $this->Img_MimeType($sTmpFile)) {
            /**
             * Ресайзим и сохраняем уменьшенную копию
             * Храним две копии - мелкую для показа пользователю и крупную в качестве исходной для ресайза
             */
            $sPreviewFile = $this->Uploader_GetUserAvatarDir($this->oUserCurrent->getId()) . 'original.' . F::File_GetExtension($sTmpFile, true);
            if ($sPreviewFile = $this->Img_Copy($sTmpFile, $sPreviewFile, self::PREVIEW_RESIZE, self::PREVIEW_RESIZE)) {
                // * Сохраняем в сессии временный файл с изображением
                $this->Session_Set('sAvatarTmp', $sTmpFile);
                $this->Session_Set('sAvatarPreview', $sPreviewFile);
                $this->Viewer_AssignAjax('sTmpFile', $this->Uploader_Dir2Url($sPreviewFile));
                return;
            }
        } else {
            $sError = $this->Uploader_GetErrorMsg();
            if (!$sError) {
                $sError = $this->Lang_Get('settings_profile_avatar_upload_error');
            }
        }
        if (!$sError) {
            $sError = 'Image loading error';
        }
        $this->Message_AddError($sError, $this->Lang_Get('error'));
    }

    /**
     * Вырезает из временной аватарки область нужного размера, ту что задал пользователь
     */
    protected function EventResizeAvatar() {

        // * Устанавливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax('json');

        // * Достаем из сессии временный файл
        $sTmpFile = $this->Session_Get('sAvatarTmp');
        $sPreviewFile = $this->Session_Get('sAvatarPreview');
        if (!F::File_Exists($sTmpFile)) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'));
            return;
        }

        // * Определяем размер большого фото для подсчета множителя пропорции
        $fRation = 1;
        if (($aSizeFile = getimagesize($sTmpFile)) && isset($aSizeFile[0])) {
            // в self::PREVIEW_RESIZE задана максимальная сторона
            $fRation = max($aSizeFile[0], $aSizeFile[1]) / self::PREVIEW_RESIZE; // 200 - размер превью по которой пользователь определяет область для ресайза
            if ($fRation < 1) {
                $fRation = 1;
            }
        }

        // * Получаем размер области из параметров
        $aSize = $this->_getImageSize('size');
        if ($aSize) {
            $aSize = array(
                'x1' => round($fRation * $aSize['x1']), 'y1' => round($fRation * $aSize['y1']),
                'x2' => round($fRation * $aSize['x2']), 'y2' => round($fRation * $aSize['y2'])
            );
        }

        // * Вырезаем фото
        if ($sFileWeb = $this->User_UploadAvatar($sTmpFile, $this->oUserCurrent, $aSize)) {

            // * Удаляем старые аватарки
            if ($sFileWeb != $this->oUserCurrent->getProfileAvatar()) {
                $this->User_DeleteAvatar($this->oUserCurrent);
            } else {
                $this->User_DeleteAvatarSizes($this->oUserCurrent);
            }

            $this->oUserCurrent->setProfileAvatar($sFileWeb);
            $this->User_Update($this->oUserCurrent);

            $this->Img_Delete($sTmpFile);
            $this->Img_Delete($sPreviewFile);

            // * Удаляем из сессии
            $this->Session_Drop('sAvatarTmp');
            $this->Session_Drop('sAvatarPreview');
            $this->Viewer_AssignAjax('sFile', $this->oUserCurrent->getAvatarUrl());
            $this->Viewer_AssignAjax('sTitleUpload', $this->Lang_Get('settings_profile_avatar_change'));
        } else {
            $this->Message_AddError($this->Lang_Get('settings_profile_avatar_error'), $this->Lang_Get('error'));
        }
    }

    /**
     * Удаляет аватар текущего пользователя
     */
    protected function EventRemoveAvatar() {

        // * Устанавливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax('json');

        // * Удаляем
        $this->User_DeleteAvatar($this->oUserCurrent);
        $this->oUserCurrent->setProfileAvatar(null);
        $this->User_Update($this->oUserCurrent);

        // * Возвращает дефолтную аватарку
        $this->Viewer_AssignAjax('sFile', $this->oUserCurrent->getAvatarUrl());
        $this->Viewer_AssignAjax('sTitleUpload', $this->Lang_Get('settings_profile_avatar_upload'));
    }

    /**
     * Отмена ресайза аватарки, необходимо удалить временный файл
     */
    protected function EventCancelAvatar() {

        // * Устанавливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax('json');

        // * Достаем из сессии файл и удаляем
        $sFileAvatar = $this->Session_Get('sAvatarFileTmp');
        $this->Img_Delete($sFileAvatar);
        $this->Session_Drop('sAvatarFileTmp');
    }

    /**
     * Загрузка временной картинки фото для последущего ресайза
     */
    protected function EventUploadPhoto() {

        if (isset($_FILES['foto']) && !isset($_FILES['photo'])) {
            $_FILES['photo'] = $_FILES['foto'];
        }

        // * Устанавливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax(F::AjaxRequest(true)?'json':'jsonIframe', false);

        if (!($aUploadedFile = $this->GetUploadedFile('photo')) && !($aUploadedFile = $this->GetUploadedFile('foto'))) {
            $this->Message_AddError($this->Lang_Get('settings_profile_photo_error'), $this->Lang_Get('error'));
            return;
        }
        $sError = '';

        $sTmpFile = $this->Uploader_UploadLocal($aUploadedFile);
        if ($sTmpFile && $this->Img_MimeType($sTmpFile)) {
            /**
             * Ресайзим и сохраняем уменьшенную копию
             * Храним две копии - мелкую для показа пользователю и крупную в качестве исходной для ресайза
             */
            $sPreviewFile = $this->Uploader_GetUserAvatarDir($this->oUserCurrent->getId()) . 'photo-preview.' . F::File_GetExtension($sTmpFile, true);
            if ($sPreviewFile = $this->Img_Copy($sTmpFile, $sPreviewFile, self::PREVIEW_RESIZE, self::PREVIEW_RESIZE)) {
                // * Сохраняем в сессии временный файл с изображением
                $this->Session_Set('sPhotoTmp', $sTmpFile);
                $this->Session_Set('sPhotoPreview', $sPreviewFile);
                $this->Viewer_AssignAjax('sTmpFile', $this->Uploader_Dir2Url($sPreviewFile));
                return;
            }
        } else {
            $sError = $this->Uploader_GetErrorMsg();
            if (!$sError) {
                $sError = $this->Lang_Get('settings_profile_photo_error');
            }
        }

        $this->Message_AddError($sError, $this->Lang_Get('error'));
        F::File_Delete($sTmpFile);
    }

    /**
     * Вырезает из временной фотки область нужного размера, ту что задал пользователь
     */
    protected function EventResizePhoto() {

        // * Устанавливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax('json');

        // * Достаем из сессии временный файл
        $sTmpFile = $this->Session_Get('sPhotoTmp');
        $sPreviewFile = $this->Session_Get('sPhotoPreview');
        if (!F::File_Exists($sTmpFile)) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'));
            return;
        }

        // * Определяем размер большого фото для подсчета множителя пропорции
        $fRation = 1;
        if (($aSizeFile = getimagesize($sTmpFile)) && isset($aSizeFile[0])) {
            // в self::PREVIEW_RESIZE задана максимальная сторона
            $fRation = max($aSizeFile[0], $aSizeFile[1]) / self::PREVIEW_RESIZE; // 200 - размер превью по которой пользователь определяет область для ресайза
            if ($fRation < 1) {
                $fRation = 1;
            }
        }

        // * Получаем размер области из параметров
        $aSize = $this->_getImageSize('size');
        if ($aSize) {
            $aSize = array(
                'x1' => round($fRation * $aSize['x1']), 'y1' => round($fRation * $aSize['y1']),
                'x2' => round($fRation * $aSize['x2']), 'y2' => round($fRation * $aSize['y2'])
            );
        }

        // * Вырезаем фото
        if ($sFileWeb = $this->User_UploadPhoto($sTmpFile, $this->oUserCurrent, $aSize)) {

            // * Удаляем старые аватарки
            $this->oUserCurrent->setProfilePhoto($sFileWeb);
            $this->User_Update($this->oUserCurrent);

            $this->Img_Delete($sTmpFile);
            $this->Img_Delete($sPreviewFile);

            // * Удаляем из сессии
            $this->Session_Drop('sPhotoTmp');
            $this->Session_Drop('sPhotoPreview');
            $this->Viewer_AssignAjax('sFile', $this->oUserCurrent->getPhotoUrl());
            $this->Viewer_AssignAjax('sTitleUpload', $this->Lang_Get('settings_profile_photo_change'));
        } else {
            $this->Message_AddError($this->Lang_Get('settings_profile_avatar_error'), $this->Lang_Get('error'));
        }
    }

    /**
     * Удаляет фото
     */
    protected function EventRemovePhoto() {

        // * Устанавливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax('json');

        // * Удаляем
        $this->User_DeletePhoto($this->oUserCurrent);
        $this->oUserCurrent->setProfilePhoto(null);
        $this->User_Update($this->oUserCurrent);

        // * Возвращает дефолтную аватарку
        $this->Viewer_AssignAjax('sFile', $this->oUserCurrent->GetPhotoUrl(250));
        $this->Viewer_AssignAjax('sTitleUpload', $this->Lang_Get('settings_profile_photo_upload'));
    }

    /**
     * Отмена ресайза фотки, необходимо удалить временный файл
     */
    protected function EventCancelPhoto() {

        // * Устанавливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax('json');

        // * Достаем из сессии файл и удаляем
        $sFile = $this->Session_Get('sPhotoTmp');
        $this->Img_Delete($sFile);

        // * Удаляем из сессии
        $this->Session_Drop('sPhotoTmp');
        $this->Session_Drop('sPhotoPreview');
    }


}

// EOF