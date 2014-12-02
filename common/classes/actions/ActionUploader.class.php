<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

/**
 * ActionUploader.class.php
 * Файл экшена загрузчика файлов
 *
 * @package actions
 * @since   1.1
 */
class ActionUploader extends Action {

    const PREVIEW_RESIZE = 222;

    /**
     * Абстрактный метод инициализации экшена
     *
     */
    public function Init() {
        // TODO: Implement Init() method.
    }

    /**
     * Абстрактный метод регистрации евентов.
     * В нём необходимо вызывать метод AddEvent($sEventName,$sEventFunction)
     * Например:
     *      $this->AddEvent('index', 'EventIndex');
     *      $this->AddEventPreg('/^admin$/i', '/^\d+$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventAdminBlog');
     */
    protected function RegisterEvent() {

        $this->AddEventPreg('/^upload-image/i', '/^$/i', 'EventUploadImage'); // Загрузка изображения на сервер
        $this->AddEventPreg('/^resize-image/i', '/^$/i', 'EventResizeImage'); // Ресайз изображения
        $this->AddEventPreg('/^remove-image/i', '/^$/i', 'EventRemoveImage'); // Удаление изображения
        $this->AddEventPreg('/^cancel-image/i', '/^$/i', 'EventCancelImage'); // Отмена ресайза в окне, закрытие окна ресайза
        $this->AddEventPreg('/^direct-image/i', '/^$/i', 'EventDirectImage'); // Прямая загрузка изображения без открытия окна ресайза

    }

    /**
     * Получение размеров изображения после ресайза
     *
     * @param $sParam
     * @return array|mixed
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
     * Добавляет связь между объектом и ресурсом
     *
     * @param $xStoredFile
     * @param $sTargetId
     * @param $sTargetType
     * @return bool
     */
    public function AddUploadedFileRelationInfo($xStoredFile, $sTargetId, $sTargetType) {

        $this->Mresource_UnlinkFile($sTargetType, $sTargetId, E::UserId());

        /** @var ModuleMresource_EntityMresource $oResource */
        $oResource = $this->Mresource_GetMresourcesByUuid($xStoredFile->getUuid());
        if ($oResource) {
//            $oRel = Engine::GetEntity('Mresource_MresourceRel');
            $oResource->setUrl($this->Mresource_NormalizeUrl($this->Uploader_GetTargetUrl($sTargetId, $sTargetType)));
            $oResource->setType($sTargetType);
            $oResource->setUserId(E::UserId());
            if ($sTargetId == '0') {
                $oResource->setTargetTmp($this->Session_GetCookie('uploader_target_tmp'));
            }
            $oResource = array($oResource);

            $this->Mresource_AddTargetRel($oResource, $sTargetType, $sTargetId);

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Прямая загрузка изображения без открытия окна ресайза
     */
    public function EventDirectImage() {

        // * Устанавливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax('json');

        // * Достаем из сессии временный файл
        $sTarget = $this->Session_Get('sTarget');
        $sTargetId = $this->Session_Get('sTargetId');
        $sTmpFile = $this->Session_Get("sTmp-{$sTarget}-{$sTargetId}");
        $sPreviewFile = $this->Session_Get("sPreview-{$sTarget}-{$sTargetId}");

        if ($sTargetId == '0'){
            if (!$this->Session_GetCookie('uploader_target_tmp')) {
                return FALSE;
            }
        }

        if (!F::File_Exists($sTmpFile)) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'));

            return;
        }

        // Проверяем, целевой объект и права на его редактирование
        if (!$oTarget = $this->Uploader_CheckAccessAndGetTarget($sTarget, $sTargetId)) {
            $this->Message_AddErrorSingle($this->Lang_Get('not_access'), $this->Lang_Get('error'));

            return;
        }

        $this->Mresource_UnlinkFile($sTarget, $sTargetId, E::UserId());

        $oImg = $this->Img_Read($sTmpFile);

        $sExtension = strtolower(pathinfo($sTmpFile, PATHINFO_EXTENSION));

        // Сохраняем фото во временный файл
        if ($sTmpFile = $oImg->Save(F::File_UploadUniqname($sExtension))) {

            // Файл, куда будет записано фото
            $sPhoto = $this->Uploader_Uniqname($this->Uploader_GetUploadDir($sTargetId, $sTarget), $sExtension);

            // Окончательная запись файла только через модуль Uploader
            if ($xStoredFile = $this->Uploader_Store($sTmpFile, $sPhoto)) {

                if (is_object($xStoredFile)) {

                    $this->AddUploadedFileRelationInfo($xStoredFile, $sTargetId, $sTarget);
                    $sFile = $xStoredFile->GetUrl();

                } else {
                    $sFile = $xStoredFile->GetUrl();
                }

                $sFilePreview = $sFile;
                if ($sSize = getRequest('crop_size', FALSE)) {
                    $sFilePreview = $this->Uploader_ResizeTargetImage($sFile, $sSize);
                }

                // Запускаем хук на действия после загрузки картинки
                $this->Hook_Run('uploader_upload_image_after', array(
                    'sFile'        => $sFile,
                    'sFilePreview' => $sFilePreview,
                    'sTargetId'    => $sTargetId,
                    'sTarget'      => $sTarget,
                    'oTarget'      => $oTarget,
                ));

                $this->Viewer_AssignAjax('sFile', $sFile);
                $this->Viewer_AssignAjax('sFilePreview', $sFilePreview);

                // Чистим
                $sTmpFile = $this->Session_Get("sTmp-{$sTarget}-{$sTargetId}");
                $sPreviewFile = $this->Session_Get("sPreview-{$sTarget}-{$sTargetId}");
                $this->Img_Delete($sTmpFile);
                $this->Img_Delete($sPreviewFile);

                // * Удаляем из сессии
                $this->Session_Drop('sTarget');
                $this->Session_Drop('sTargetId');
                $this->Session_Drop("sTmp-{$sTarget}-{$sTargetId}");
                $this->Session_Drop("sPreview-{$sTarget}-{$sTargetId}");

                return;
            }
        }

        // * В случае ошибки, возвращаем false
        $this->Message_AddErrorSingle($this->Lang_Get('system_error'));

        return;
    }

    /**
     * Загрузка изображения после его ресайза
     *
     * @param  string $sFile     - Серверный путь до временной фотографии
     * @param  string $sTargetId - Ид. целевого объекта
     * @param  string $sTarget   - Тип целевого объекта
     * @param  array  $aSize     - Размер области из которой нужно вырезать картинку - array('x1'=>0,'y1'=>0,'x2'=>100,'y2'=>100)
     *
     * @return string|bool
     */
    public function UploadImageAfterResize($sFile, $sTargetId, $sTarget, $aSize = array()) {

        if ($sTargetId == '0'){
            if (!$this->Session_GetCookie('uploader_target_tmp')) {
                return FALSE;
            }
        }

        if (!F::File_Exists($sFile)) {
            return FALSE;
        }
        if (!$aSize) {
            $oImg = $this->Img_CropSquare($sFile, TRUE);
        } else {
            if (!isset($aSize['w'])) {
                $aSize['w'] = $aSize['x2'] - $aSize['x1'];
            }
            if (!isset($aSize['h'])) {
                $aSize['h'] = $aSize['y2'] - $aSize['y1'];
            }
            $oImg = $this->Img_Crop($sFile, $aSize['w'], $aSize['h'], $aSize['x1'], $aSize['y1']);
        }
        $sExtension = strtolower(pathinfo($sFile, PATHINFO_EXTENSION));

        // Сохраняем фото во временный файл
        if ($sTmpFile = $oImg->Save(F::File_UploadUniqname($sExtension))) {

            // Файл, куда будет записано фото
            $sPhoto = $this->Uploader_Uniqname($this->Uploader_GetUploadDir($sTargetId, $sTarget), $sExtension);

            // Окончательная запись файла только через модуль Uploader
            if ($xStoredFile = $this->Uploader_Store($sTmpFile, $sPhoto)) {

                if (is_object($xStoredFile)) {

                    $this->AddUploadedFileRelationInfo($xStoredFile, $sTargetId, $sTarget);
                    $sFile = $xStoredFile->GetUrl();

                } else {
                    $sFile = $xStoredFile->GetUrl();
                }

                return $sFile;
            }
        }

        // * В случае ошибки, возвращаем false
        $this->Message_AddErrorSingle($this->Lang_Get('system_error'));

        return FALSE;
    }

    /**
     * Загружаем картинку
     */
    public function EventUploadImage() {

        // Устанавливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax('jsonIframe', FALSE);

        $this->Security_ValidateSendForm();

        // Проверяем, загружен ли файл
        if (!($aUploadedFile = $this->GetUploadedFile('uploader-upload-image'))) {
            $this->Message_AddError($this->Lang_Get('plugin.br.error_upload_image'), $this->Lang_Get('error'));

            return;
        }

        // Проверяем, целевой объект и права на его редактирование
        if (!$oTarget = $this->Uploader_CheckAccessAndGetTarget(
            $sTarget = getRequest('target', FALSE),
            $sTargetId = getRequest('target_id', FALSE))
        ) {
            // Здесь два варианта, либо редактировать нельзя, либо можно, но топика еще нет
            if ($oTarget === TRUE) {
                // Будем делать временную картинку

            } else {
                $this->Message_AddErrorSingle($this->Lang_Get('not_access'), $this->Lang_Get('error'));

                return;
            }

        }

        // Ошибок пока нет
        $sError = '';

        // Сделаем временный файд
        $sTmpFile = $this->Uploader_UploadLocal($aUploadedFile);

        // Вызовем хук перед началом загрузки картинки
        $this->Hook_Run('uploader_upload_before', array('oTarget' => $oTarget, 'sTmpFile' => $sTmpFile, 'sTarget' => $sTarget));

        // Если все ок, и по миме проходит, то
        if ($sTmpFile && $this->Img_MimeType($sTmpFile)) {

            // Ресайзим и сохраняем уменьшенную копию
            // Храним две копии - мелкую для показа пользователю и крупную в качестве исходной для ресайза
            $sPreviewFile = $this->Uploader_GetUploadDir($sTargetId, $sTarget) . '_preview.' . F::File_GetExtension($sTmpFile);

            if ($sPreviewFile = $this->Img_Copy($sTmpFile, $sPreviewFile, self::PREVIEW_RESIZE, self::PREVIEW_RESIZE)) {

                // * Сохраняем в сессии временный файл с изображением
                $this->Session_Set('sTarget', $sTarget);
                $this->Session_Set('sTargetId', $sTargetId);
                $this->Session_Set("sTmp-{$sTarget}-{$sTargetId}", $sTmpFile);
                $this->Session_Set("sPreview-{$sTarget}-{$sTargetId}", $sPreviewFile);
                $this->Viewer_AssignAjax('sPreview', $this->Uploader_Dir2Url($sPreviewFile));

                return;
            }
        } else {

            // Ошибки загрузки картинки
            $sError = $this->Uploader_GetErrorMsg();
            if (!$sError) {
                $sError = $this->Lang_Get('plugin.br.error_upload_image');
            }
        }

        // Выведем ошибки пользователю
        $this->Message_AddError($sError, $this->Lang_Get('error'));

        // Удалим ранее загруженый файл
        F::File_Delete($sTmpFile);

    }

    /**
     * Обработка обрезки изображения
     */
    public function EventResizeImage() {
        // * Устанавливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax('json');

        // * Достаем из сессии временный файл
        $sTarget = $this->Session_Get('sTarget');
        $sTargetId = $this->Session_Get('sTargetId');
        $sTmpFile = $this->Session_Get("sTmp-{$sTarget}-{$sTargetId}");
        $sPreviewFile = $this->Session_Get("sPreview-{$sTarget}-{$sTargetId}");

        if (!F::File_Exists($sTmpFile)) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'));

            return;
        }

        // Проверяем, целевой объект и права на его редактирование
        if (!$oTarget = $this->Uploader_CheckAccessAndGetTarget($sTarget, $sTargetId)) {
            $this->Message_AddErrorSingle($this->Lang_Get('not_access'), $this->Lang_Get('error'));

            return;
        }

        // * Определяем размер большого фото для подсчета множителя пропорции
        $fRation = 1;
        if (($aSizeFile = getimagesize($sTmpFile)) && isset($aSizeFile[0])) {
            // в self::PREVIEW_RESIZE задана максимальная сторона
            $fRation = max($aSizeFile[0], $aSizeFile[1]) / self::PREVIEW_RESIZE; // 222 - размер превью по которой пользователь определяет область для ресайза
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

        // * Вырезаем и сохраняем фото
        if ($sFileWeb = $this->UploadImageAfterResize($sTmpFile, $sTargetId, $sTarget, $aSize)) {

            $sFileWebPreview = $sFileWeb;
            if ($sSize = getRequest('crop_size', FALSE)) {
                $sFileWebPreview = $this->Uploader_ResizeTargetImage($sFileWeb, $sSize);
            }

            // Запускаем хук на действия после загрузки картинки
            $this->Hook_Run('uploader_upload_image_after', array(
                'sFile'        => $sFileWeb,
                'sFilePreview' => $sFileWebPreview,
                'sTargetId'    => $sTargetId,
                'sTarget'      => $sTarget,
                'oTarget'      => $oTarget,
            ));

            $this->Img_Delete($sTmpFile);
            $this->Img_Delete($sPreviewFile);

            // * Удаляем из сессии
            $this->Session_Drop('sTarget');
            $this->Session_Drop('sTargetId');
            $this->Session_Drop("sTmp-{$sTarget}-{$sTargetId}");
            $this->Session_Drop("sPreview-{$sTarget}-{$sTargetId}");

            $this->Viewer_AssignAjax('sFile', $sFileWeb);
            $this->Viewer_AssignAjax('sFilePreview', $sFileWebPreview);
            $this->Viewer_AssignAjax('sTitleUpload', $this->Lang_Get('plugin.br.uploader_upload_success'));
        } else {
            $this->Message_AddError($this->Lang_Get('plugin.br.error_upload_image'), $this->Lang_Get('error'));
        }
    }

    /**
     * Удаление картинки
     */
    public function EventRemoveImage() {

        // * Устанавливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax('json');

        // Проверяем, целевой объект и права на его редактирование
        if (!$oTarget = $this->Uploader_CheckAccessAndGetTarget(
            $sTargetType = getRequest('target', FALSE),
            $sTargetId = getRequest('target_id', FALSE))
        ) {
            $this->Message_AddErrorSingle($this->Lang_Get('not_access'), $this->Lang_Get('error'));

            return;
        }

        // * Удаляем картинку
        $this->Mresource_UnlinkFile($sTargetType, $sTargetId, E::UserId());

        // Запускаем хук на действия после загрузки картинки
        $this->Hook_Run('uploader_remove_image_after', array(
            'sTargetId' => $sTargetId,
            'sTarget'   => $sTargetType,
            'oTarget'   => $oTarget,
        ));

        // * Возвращает дефолтную аватарку
        $this->Viewer_AssignAjax('sTitleUpload', $this->Lang_Get('plugin.br.uploader_upload_success'));

    }

    /**
     * Отмена загрузки в окне ресайза
     */
    public function EventCancelImage() {

        // * Устанавливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax('json');

        // Проверяем, целевой объект и права на его редактирование
        if (!$oTarget = $this->Uploader_CheckAccessAndGetTarget(
            $sTarget = getRequest('target', FALSE),
            $sTargetId = getRequest('target_id', FALSE))
        ) {
            $this->Message_AddErrorSingle($this->Lang_Get('not_access'), $this->Lang_Get('error'));

            return;
        }
        $sTmpFile = $this->Session_Get("sTmp-{$sTarget}-{$sTargetId}");

        if (!F::File_Exists($sTmpFile)) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'));

            return;
        }

        $this->Img_Delete($sTmpFile);

        // * Удаляем из сессии
        $this->Session_Drop('sTarget');
        $this->Session_Drop('sTargetId');
        $this->Session_Drop("sTmp-{$sTarget}-{$sTargetId}");
        $this->Session_Drop("sPreview-{$sTarget}-{$sTargetId}");

    }

}