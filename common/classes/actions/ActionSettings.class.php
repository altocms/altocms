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
 * Экшен обработки настроек профиля юзера (/settings/)
 *
 * @package actions
 * @since   1.0
 */
class ActionSettings extends Action {

    const PREVIEW_RESIZE = 200;

    /**
     * Какое меню активно
     *
     * @var string
     */
    protected $sMenuItemSelect = 'settings';
    /**
     * Какое подменю активно
     *
     * @var string
     */
    protected $sMenuSubItemSelect = 'profile';
    /**
     * Текущий юзер
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent = null;

    /**
     * Инициализация
     *
     */
    public function Init() {

        // * Проверяем авторизован ли юзер
        if (!$this->User_IsAuthorization()) {
            $this->Message_AddErrorSingle($this->Lang_Get('not_access'), $this->Lang_Get('error'));
            return Router::Action('error');
        }

        // * Получаем текущего юзера
        $this->oUserCurrent = $this->User_GetUserCurrent();
        $this->SetDefaultEvent('profile');

        // * Устанавливаем title страницы
        $this->Viewer_AddHtmlTitle($this->Lang_Get('settings_menu'));
    }

    /**
     * Регистрация евентов
     */
    protected function RegisterEvent() {

        $this->AddEventPreg('/^profile$/i', '/^upload-avatar/i', '/^$/i', 'EventUploadAvatar');
        $this->AddEventPreg('/^profile$/i', '/^resize-avatar/i', '/^$/i', 'EventResizeAvatar');
        $this->AddEventPreg('/^profile$/i', '/^remove-avatar/i', '/^$/i', 'EventRemoveAvatar');
        $this->AddEventPreg('/^profile$/i', '/^cancel-avatar/i', '/^$/i', 'EventCancelAvatar');
        $this->AddEventPreg('/^profile$/i', '/^upload-foto/i', '/^$/i', 'EventUploadFoto');
        $this->AddEventPreg('/^profile$/i', '/^resize-foto/i', '/^$/i', 'EventResizeFoto');
        $this->AddEventPreg('/^profile$/i', '/^remove-foto/i', '/^$/i', 'EventRemoveFoto');
        $this->AddEventPreg('/^profile$/i', '/^cancel-foto/i', '/^$/i', 'EventCancelFoto');
        $this->AddEvent('profile', 'EventProfile');
        $this->AddEvent('invite', 'EventInvite');
        $this->AddEvent('tuning', 'EventTuning');
        $this->AddEvent('account', 'EventAccount');
    }


    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */

    protected function _getImageSize($sParam) {

        if ($aSize = getRequest($sParam)) {
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
     * Загрузка временной картинки фото для последущего ресайза
     */
    protected function EventUploadFoto() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        $this->Viewer_SetResponseAjax('jsonIframe', false);

        $sDir = Config::Get('path.uploads.images') . '/tmp/fotos/' . $this->oUserCurrent->getId();
        if (!($aUploadedFile = $this->GetUploadedFile('foto')) || !($sFileTmp = $this->Upload_UploadLocal($aUploadedFile))) {
            return false;
        }

        /**
         * Ресайзим и сохраняем именьшенную копию
         * Храним две копии - мелкую для показа пользователю и крупную в качестве исходной для ресайза
         */
        if ($sFile = $this->Image_Resize(
            $sFileTmp, $sDir, 'original', Config::Get('view.img_max_width'), Config::Get('view.img_max_height'), 1000,
            null, true
        )
        ) {
            if ($sFilePreview = $this->Image_Resize(
                $sFileTmp, $sDir, 'preview', Config::Get('view.img_max_width'), Config::Get('view.img_max_height'), self::PREVIEW_RESIZE,
                null, true
            )
            ) {
                /**
                 * Сохраняем в сессии временный файл с изображением
                 */
                $this->Session_Set('sFotoFileTmp', $sFile);
                $this->Session_Set('sFotoFilePreviewTmp', $sFilePreview);
                $this->Viewer_AssignAjax('sTmpFile', $this->Image_GetWebPath($sFilePreview));
                unlink($sFileTmp);
                return;
            }
        }
        $this->Message_AddError($this->Image_GetLastError(), $this->Lang_Get('error'));
        unlink($sFileTmp);
    }

    /**
     * Вырезает из временной фотки область нужного размера, ту что задал пользователь
     */
    protected function EventResizeFoto() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        $this->Viewer_SetResponseAjax('json');
        /**
         * Достаем из сессии временный файл
         */
        $sFile = $this->Session_Get('sFotoFileTmp');
        $sFilePreview = $this->Session_Get('sFotoFilePreviewTmp');
        if (!file_exists($sFile)) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'));
            return;
        }
        /**
         * Определяем размер большого фото для подсчета множителя пропорции
         */
        $fRation = 1;
        if ($aSizeFile = getimagesize($sFile) && isset($aSizeFile[0])) {
            $fRation
                = $aSizeFile[0] / self::PREVIEW_RESIZE; // 200 - размер превью по которой пользователь определяет область для ресайза
            if ($fRation < 1) {
                $fRation = 1;
            }
        }
        /**
         * Получаем размер области из параметров
         */
        $aSize = $this->_getImageSize('size');
        if ($aSize) {
            $aSize = array('x1' => round($fRation * $aSize['x']), 'y1' => round($fRation * $aSize['y']),
                           'x2' => round($fRation * $aSize['x2']), 'y2' => round($fRation * $aSize['y2']));
        }
        /**
         * Вырезаем аватарку
         */
        if ($sFileWeb = $this->User_UploadFoto($sFile, $this->oUserCurrent, $aSize)) {
            /**
             * Удаляем старые аватарки
             */
            $this->oUserCurrent->setProfileFoto($sFileWeb);
            $this->User_Update($this->oUserCurrent);

            $this->Image_RemoveFile($sFilePreview);
            /**
             * Удаляем из сессии
             */
            $this->Session_Drop('sFotoFileTmp');
            $this->Session_Drop('sFotoFilePreviewTmp');
            $this->Viewer_AssignAjax('sFile', $this->oUserCurrent->getProfileFoto());
            $this->Viewer_AssignAjax('sTitleUpload', $this->Lang_Get('settings_profile_photo_change'));
        } else {
            $this->Message_AddError($this->Lang_Get('settings_profile_avatar_error'), $this->Lang_Get('error'));
        }
    }

    /**
     * Удаляет фото
     */
    protected function EventRemoveFoto() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        $this->Viewer_SetResponseAjax('json');
        /**
         * Удаляем
         */
        $this->User_DeleteFoto($this->oUserCurrent);
        $this->oUserCurrent->setProfileFoto(null);
        $this->User_Update($this->oUserCurrent);
        /**
         * Возвращает дефолтную аватарку
         */
        $this->Viewer_AssignAjax('sFile', $this->oUserCurrent->getProfileFotoDefault());
        $this->Viewer_AssignAjax('sTitleUpload', $this->Lang_Get('settings_profile_photo_upload'));
    }

    /**
     * Отмена ресайза фотки, необходимо удалить временный файл
     */
    protected function EventCancelFoto() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        $this->Viewer_SetResponseAjax('json');
        /**
         * Достаем из сессии файл и удаляем
         */
        $sFile = $this->Session_Get('sFotoFileTmp');
        $this->Image_RemoveFile($sFile);

        $sFile = $this->Session_Get('sFotoFilePreviewTmp');
        $this->Image_RemoveFile($sFile);
        /**
         * Удаляем из сессии
         */
        $this->Session_Drop('sFotoFileTmp');
        $this->Session_Drop('sFotoFilePreviewTmp');
    }

    /**
     * Загрузка временной картинки для аватара
     */
    protected function EventUploadAvatar() {

        // * Устанавливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax('jsonIframe', false);

        $aUploadedFile = $this->GetUploadedFile('avatar');
        if (!$aUploadedFile) {
            return false;
        }
        $sError = '';

        // Загружаем файл
        $sUploadedFile = $this->Upload_UploadLocal($aUploadedFile);
        if ($sUploadedFile) {
            if ($this->Img_ResizeFile($sUploadedFile, self::PREVIEW_RESIZE, self::PREVIEW_RESIZE)) {
                // Сохраняем аватару в оригинале
                $sAvatarFile = $this->Upload_GetUserAvatarDir($this->oUserCurrent->getId()) . 'original.' . F::File_GetExtension($sUploadedFile);
                if ($sAvatarFile = $this->Upload_Move($sUploadedFile, $sAvatarFile, true)) {
                    // Зачем-то сохраняем в сессии
                    $this->Session_Set('sAvatarFileTmp', $sAvatarFile);
                    $this->Viewer_AssignAjax('sTmpFile', $this->Upload_Dir2Url($sAvatarFile));
                    return;
                }
            }
        } else {
            $sError = $this->Lang_Get('settings_profile_avatar_upload_error');
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

        // * Получаем файл из сессии
        $sFileAvatar = $this->Session_Get('sAvatarFileTmp');
        if (!F::File_Exists($sFileAvatar)) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'));
            return;
        }

        // * Получаем размер области из параметров
        $aSize = $this->_getImageSize('size');

        // * Вырезаем аватару
        if ($sFileWeb = $this->User_UploadAvatar($sFileAvatar, $this->oUserCurrent, $aSize)) {
            /**
             * Удаляем старые аватарки
             */
            if ($sFileWeb != $this->oUserCurrent->getProfileAvatar()) {
                $this->User_DeleteAvatar($this->oUserCurrent);
            }
            $this->oUserCurrent->setProfileAvatar($sFileWeb);

            $this->User_Update($this->oUserCurrent);
            $this->Session_Drop('sAvatarFileTmp');
            $this->Viewer_AssignAjax('sFile', $this->oUserCurrent->getProfileAvatarPath(100));
            $this->Viewer_AssignAjax('sTitleUpload', $this->Lang_Get('settings_profile_avatar_change'));
        } else {
            $this->Message_AddError($this->Lang_Get('settings_profile_avatar_error'), $this->Lang_Get('error'));
        }
    }

    /**
     * Удаляет аватар
     */
    protected function EventRemoveAvatar() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        $this->Viewer_SetResponseAjax('json');
        /**
         * Удаляем
         */
        $this->User_DeleteAvatar($this->oUserCurrent);
        $this->oUserCurrent->setProfileAvatar(null);
        $this->User_Update($this->oUserCurrent);
        /**
         * Возвращает дефолтную аватарку
         */
        $this->Viewer_AssignAjax('sFile', $this->oUserCurrent->getProfileAvatarPath(100));
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
        $this->Image_RemoveFile($sFileAvatar);
        $this->Session_Drop('sAvatarFileTmp');
    }

    /**
     * Дополнительные настройки сайта
     */
    protected function EventTuning() {

        $this->sMenuItemSelect = 'settings';
        $this->sMenuSubItemSelect = 'tuning';

        $this->Viewer_AddHtmlTitle($this->Lang_Get('settings_menu_tuning'));
        $aTimezoneList = array('-12', '-11', '-10', '-9.5', '-9', '-8', '-7', '-6', '-5', '-4.5', '-4', '-3.5', '-3',
                               '-2', '-1', '0', '1', '2', '3', '3.5', '4', '4.5', '5', '5.5', '5.75', '6', '6.5', '7',
                               '8', '8.75', '9', '9.5', '10', '10.5', '11', '11.5', '12', '12.75', '13', '14');
        $this->Viewer_Assign('aTimezoneList', $aTimezoneList);
        /**
         * Если отправили форму с настройками - сохраняем
         */
        if (isPost('submit_settings_tuning')) {
            $this->Security_ValidateSendForm();

            if (in_array(getRequestStr('settings_general_timezone'), $aTimezoneList)) {
                $this->oUserCurrent->setSettingsTimezone(getRequestStr('settings_general_timezone'));
            }

            $this->oUserCurrent->setSettingsNoticeNewTopic(getRequest('settings_notice_new_topic') ? 1 : 0);
            $this->oUserCurrent->setSettingsNoticeNewComment(getRequest('settings_notice_new_comment') ? 1 : 0);
            $this->oUserCurrent->setSettingsNoticeNewTalk(getRequest('settings_notice_new_talk') ? 1 : 0);
            $this->oUserCurrent->setSettingsNoticeReplyComment(getRequest('settings_notice_reply_comment') ? 1 : 0);
            $this->oUserCurrent->setSettingsNoticeNewFriend(getRequest('settings_notice_new_friend') ? 1 : 0);
            $this->oUserCurrent->setProfileDate(F::Now());

            // * Запускаем выполнение хуков
            $this->Hook_Run('settings_tuning_save_before', array('oUser' => $this->oUserCurrent));
            if ($this->User_Update($this->oUserCurrent)) {
                $this->Message_AddNoticeSingle($this->Lang_Get('settings_tuning_submit_ok'));
                $this->Hook_Run('settings_tuning_save_after', array('oUser' => $this->oUserCurrent));
            } else {
                $this->Message_AddErrorSingle($this->Lang_Get('system_error'));
            }
        } else {
            if (is_null($this->oUserCurrent->getSettingsTimezone())) {
                $_REQUEST['settings_general_timezone']
                    = (strtotime(date('Y-m-d H:i:s')) - strtotime(gmdate('Y-m-d H:i:s'))) / 3600 - date('I');
            } else {
                $_REQUEST['settings_general_timezone'] = $this->oUserCurrent->getSettingsTimezone();
            }
        }
    }

    /**
     * Показ и обработка формы приглаешний
     *
     */
    protected function EventInvite() {
        /**
         * Только при активном режиме инвайтов
         */
        if (!Config::Get('general.reg.invite')) {
            return parent::EventNotFound();
        }

        $this->sMenuItemSelect = 'invite';
        $this->sMenuSubItemSelect = '';
        $this->Viewer_AddHtmlTitle($this->Lang_Get('settings_menu_invite'));
        /**
         * Если отправили форму
         */
        if (isPost('submit_invite')) {
            $this->Security_ValidateSendForm();

            $bError = false;
            /**
             * Есть права на отправку инфайтов?
             */
            if (!$this->ACL_CanSendInvite($this->oUserCurrent) && !$this->oUserCurrent->isAdministrator()) {
                $this->Message_AddError($this->Lang_Get('settings_invite_available_no'), $this->Lang_Get('error'));
                $bError = true;
            }
            /**
             * Емайл корректен?
             */
            if (!F::CheckVal(getRequestStr('invite_mail'), 'mail')) {
                $this->Message_AddError($this->Lang_Get('settings_invite_mail_error'), $this->Lang_Get('error'));
                $bError = true;
            }
            /**
             * Запускаем выполнение хуков
             */
            $this->Hook_Run('settings_invate_send_before', array('oUser' => $this->oUserCurrent));
            /**
             * Если нет ошибок, то отправляем инвайт
             */
            if (!$bError) {
                $oInvite = $this->User_GenerateInvite($this->oUserCurrent);
                $this->Notify_SendInvite($this->oUserCurrent, getRequestStr('invite_mail'), $oInvite);
                $this->Message_AddNoticeSingle($this->Lang_Get('settings_invite_submit_ok'));
                $this->Hook_Run('settings_invate_send_after', array('oUser' => $this->oUserCurrent));
            }
        }

        $this->Viewer_Assign('iCountInviteAvailable', $this->User_GetCountInviteAvailable($this->oUserCurrent));
        $this->Viewer_Assign('iCountInviteUsed', $this->User_GetCountInviteUsed($this->oUserCurrent->getId()));
    }

    /**
     * Форма смены пароля, емайла
     */
    protected function EventAccount() {
        /**
         * Устанавливаем title страницы
         */
        $this->Viewer_AddHtmlTitle($this->Lang_Get('settings_menu_profile'));
        $this->sMenuSubItemSelect = 'account';
        /**
         * Если нажали кнопку "Сохранить"
         */
        if (isPost('submit_account_edit')) {
            $this->Security_ValidateSendForm();

            $bError = false;
            /**
             * Проверка мыла
             */
            if (F::CheckVal(getRequestStr('mail'), 'mail')) {
                if (($oUserMail = $this->User_GetUserByMail(getRequestStr('mail')))
                    && $oUserMail->getId() != $this->oUserCurrent->getId()
                ) {
                    $this->Message_AddError(
                        $this->Lang_Get('settings_profile_mail_error_used'), $this->Lang_Get('error')
                    );
                    $bError = true;
                }
            } else {
                $this->Message_AddError($this->Lang_Get('settings_profile_mail_error'), $this->Lang_Get('error'));
                $bError = true;
            }
            /**
             * Проверка на смену пароля
             */
            if ($sPassword = $this->GetPost('password')) {
                if (($nMinLen = Config::Get('module.security.password_len')) < 3) {
                    $nMinLen = 3;
                }
                if (F::CheckVal($sPassword, 'password', $nMinLen)) {
                    if ($sPassword == $this->GetPost('password_confirm')) {
                        if ($this->Security_CheckSalted(
                            $this->oUserCurrent->getPassword(), $this->GetPost('password_now'), 'pass'
                        )
                        ) {
                            $this->oUserCurrent->setPassword($sPassword, true);
                        } else {
                            $bError = true;
                            $this->Message_AddError(
                                $this->Lang_Get('settings_profile_password_current_error'), $this->Lang_Get('error')
                            );
                        }
                    } else {
                        $bError = true;
                        $this->Message_AddError(
                            $this->Lang_Get('settings_profile_password_confirm_error'), $this->Lang_Get('error')
                        );
                    }
                } else {
                    $bError = true;
                    $this->Message_AddError(
                        $this->Lang_Get('settings_profile_password_new_error', array('num' => $nMinLen)),
                        $this->Lang_Get('error')
                    );
                }
            }
            /**
             * Ставим дату последнего изменения
             */
            $this->oUserCurrent->setProfileDate(F::Now());
            /**
             * Запускаем выполнение хуков
             */
            $this->Hook_Run(
                'settings_account_save_before', array('oUser' => $this->oUserCurrent, 'bError' => &$bError)
            );
            /**
             * Сохраняем изменения
             */
            if (!$bError) {
                if ($this->User_Update($this->oUserCurrent)) {
                    $this->Message_AddNoticeSingle($this->Lang_Get('settings_account_submit_ok'));
                    /**
                     * Подтверждение смены емайла
                     */
                    if (getRequestStr('mail') && getRequestStr('mail') != $this->oUserCurrent->getMail()) {
                        if ($oChangemail = $this->User_MakeUserChangemail($this->oUserCurrent, getRequestStr('mail'))) {
                            if ($oChangemail->getMailFrom()) {
                                $this->Message_AddNotice($this->Lang_Get('settings_profile_mail_change_from_notice'));
                            } else {
                                $this->Message_AddNotice($this->Lang_Get('settings_profile_mail_change_to_notice'));
                            }
                        }
                    }

                    $this->Hook_Run('settings_account_save_after', array('oUser' => $this->oUserCurrent));
                } else {
                    $this->Message_AddErrorSingle($this->Lang_Get('system_error'));
                }
            }
        }
    }

    /**
     * Выводит форму для редактирования профиля и обрабатывает её
     *
     */
    protected function EventProfile() {

        // * Устанавливаем title страницы
        $this->Viewer_AddHtmlTitle($this->Lang_Get('settings_menu_profile'));
        $this->Viewer_Assign('aUserFields', $this->User_getUserFields(''));
        $this->Viewer_Assign('aUserFieldsContact', $this->User_getUserFields(array('contact', 'social')));

        // * Загружаем в шаблон JS текстовки
        $this->Lang_AddLangJs(
            array(
                 'settings_profile_field_error_max'
            )
        );

        // * Если нажали кнопку "Сохранить"
        if ($this->isPost('submit_profile_edit')) {

            $bError = false;
            /**
             * Заполняем профиль из полей формы
             */

            // * Определяем гео-объект
            if (getRequest('geo_city')) {
                $oGeoObject = $this->Geo_GetGeoObject('city', getRequestStr('geo_city'));
            } elseif (getRequest('geo_region')) {
                $oGeoObject = $this->Geo_GetGeoObject('region', getRequestStr('geo_region'));
            } elseif (getRequest('geo_country')) {
                $oGeoObject = $this->Geo_GetGeoObject('country', getRequestStr('geo_country'));
            } else {
                $oGeoObject = null;
            }

            // * Проверяем имя
            if (F::CheckVal(getRequestStr('profile_name'), 'text', 2, Config::Get('module.user.name_max'))) {
                $this->oUserCurrent->setProfileName(getRequestStr('profile_name'));
            } else {
                $this->oUserCurrent->setProfileName(null);
            }

            // * Проверяем пол
            if (in_array(getRequestStr('profile_sex'), array('man', 'woman', 'other'))) {
                $this->oUserCurrent->setProfileSex(getRequestStr('profile_sex'));
            } else {
                $this->oUserCurrent->setProfileSex('other');
            }

            // * Проверяем дату рождения
            if (F::CheckVal(getRequestStr('profile_birthday_day'), 'id', 1, 2)
                && F::CheckVal(
                    getRequestStr('profile_birthday_month'), 'id', 1, 2
                )
                && F::CheckVal(getRequestStr('profile_birthday_year'), 'id', 4, 4)
            ) {
                $this->oUserCurrent->setProfileBirthday(
                    date(
                        'Y-m-d H:i:s', mktime(
                            0, 0, 0, getRequestStr('profile_birthday_month'), getRequestStr('profile_birthday_day'),
                            getRequestStr('profile_birthday_year')
                        )
                    )
                );
            } else {
                $this->oUserCurrent->setProfileBirthday(null);
            }

            // * Проверяем информацию о себе
            if (F::CheckVal(getRequestStr('profile_about'), 'text', 1, 3000)) {
                $this->oUserCurrent->setProfileAbout($this->Text_Parser(getRequestStr('profile_about')));
            } else {
                $this->oUserCurrent->setProfileAbout(null);
            }

            // * Ставим дату последнего изменения профиля
            $this->oUserCurrent->setProfileDate(F::Now());

            // * Запускаем выполнение хуков
            $this->Hook_Run(
                'settings_profile_save_before', array('oUser' => $this->oUserCurrent, 'bError' => &$bError)
            );

            // * Сохраняем изменения профиля
            if (!$bError) {
                if ($this->User_Update($this->oUserCurrent)) {

                    // * Обновляем название личного блога
                    $oBlog = $this->oUserCurrent->getBlog();
                    if (getRequestStr('blog_title') && $this->checkBlogFields($oBlog)) {
                        $oBlog->setTitle(strip_tags(getRequestStr('blog_title')));
                        $this->Blog_UpdateBlog($oBlog);
                    }

                    // * Создаем связь с гео-объектом
                    if ($oGeoObject) {
                        $this->Geo_CreateTarget($oGeoObject, 'user', $this->oUserCurrent->getId());
                        if ($oCountry = $oGeoObject->getCountry()) {
                            $this->oUserCurrent->setProfileCountry($oCountry->getName());
                        } else {
                            $this->oUserCurrent->setProfileCountry(null);
                        }
                        if ($oRegion = $oGeoObject->getRegion()) {
                            $this->oUserCurrent->setProfileRegion($oRegion->getName());
                        } else {
                            $this->oUserCurrent->setProfileRegion(null);
                        }
                        if ($oCity = $oGeoObject->getCity()) {
                            $this->oUserCurrent->setProfileCity($oCity->getName());
                        } else {
                            $this->oUserCurrent->setProfileCity(null);
                        }
                    } else {
                        $this->Geo_DeleteTargetsByTarget('user', $this->oUserCurrent->getId());
                        $this->oUserCurrent->setProfileCountry(null);
                        $this->oUserCurrent->setProfileRegion(null);
                        $this->oUserCurrent->setProfileCity(null);
                    }
                    $this->User_Update($this->oUserCurrent);

                    // * Обрабатываем дополнительные поля, type = ''
                    $aFields = $this->User_getUserFields('');
                    $aData = array();
                    foreach ($aFields as $iId => $aField) {
                        if (isset($_REQUEST['profile_user_field_' . $iId])) {
                            $aData[$iId] = getRequestStr('profile_user_field_' . $iId);
                        }
                    }
                    $this->User_setUserFieldsValues($this->oUserCurrent->getId(), $aData);

                    // * Динамические поля контактов, type = array('contact','social')
                    $aType = array('contact', 'social');
                    $aFields = $this->User_getUserFields($aType);

                    // * Удаляем все поля с этим типом
                    $this->User_DeleteUserFieldValues($this->oUserCurrent->getId(), $aType);
                    $aFieldsContactType = getRequest('profile_user_field_type');
                    $aFieldsContactValue = getRequest('profile_user_field_value');
                    if (is_array($aFieldsContactType)) {
                        foreach ($aFieldsContactType as $k => $v) {
                            $v = (string)$v;
                            if (isset($aFields[$v]) && isset($aFieldsContactValue[$k])
                                && is_string(
                                    $aFieldsContactValue[$k]
                                )
                            ) {
                                $this->User_setUserFieldsValues(
                                    $this->oUserCurrent->getId(), array($v => $aFieldsContactValue[$k]),
                                    Config::Get('module.user.userfield_max_identical')
                                );
                            }
                        }
                    }
                    $this->Message_AddNoticeSingle($this->Lang_Get('settings_profile_submit_ok'));
                    $this->Hook_Run('settings_profile_save_after', array('oUser' => $this->oUserCurrent));
                } else {
                    $this->Message_AddErrorSingle($this->Lang_Get('system_error'));
                }
            }
        }

        // * Загружаем гео-объект привязки
        $oGeoTarget = $this->Geo_GetTargetByTarget('user', $this->oUserCurrent->getId());
        $this->Viewer_Assign('oGeoTarget', $oGeoTarget);

        // * Загружаем в шаблон список стран, регионов, городов
        $aCountries = $this->Geo_GetCountries(array(), array('sort' => 'asc'), 1, 300);
        $this->Viewer_Assign('aGeoCountries', $aCountries['collection']);
        if ($oGeoTarget) {
            if ($oGeoTarget->getCountryId()) {
                $aRegions = $this->Geo_GetRegions(
                    array('country_id' => $oGeoTarget->getCountryId()), array('sort' => 'asc'), 1, 500
                );
                $this->Viewer_Assign('aGeoRegions', $aRegions['collection']);
            }
            if ($oGeoTarget->getRegionId()) {
                $aCities = $this->Geo_GetCities(
                    array('region_id' => $oGeoTarget->getRegionId()), array('sort' => 'asc'), 1, 500
                );
                $this->Viewer_Assign('aGeoCities', $aCities['collection']);
            }
        }

    }

    /**
     * Проверка полей блога
     *
     * @param ModuleBlog_EntityBlog|null $oBlog
     *
     * @return bool
     */
    protected function checkBlogFields($oBlog = null) {

        $bOk = true;

        // * Проверяем есть ли название блога
        if (!F::CheckVal(getRequestStr('blog_title'), 'text', 2, 200)) {
            $this->Message_AddError($this->Lang_Get('blog_create_title_error'), $this->Lang_Get('error'));
            $bOk = false;
        } else {

            // * Проверяем есть ли уже блог с таким названием
            if ($oBlogExists = $this->Blog_GetBlogByTitle(getRequestStr('blog_title'))) {
                if (!$oBlog || $oBlog->getId() != $oBlogExists->getId()) {
                    $this->Message_AddError(
                        $this->Lang_Get('blog_create_title_error_unique'), $this->Lang_Get('error')
                    );
                    $bOk = false;
                }
            }
        }

        return $bOk;
    }

    /**
     * Выполняется при завершении работы экшена
     *
     */
    public function EventShutdown() {

        $iCountTopicFavourite = $this->Topic_GetCountTopicsFavouriteByUserId($this->oUserCurrent->getId());
        $iCountTopicUser = $this->Topic_GetCountTopicsPersonalByUser($this->oUserCurrent->getId(), 1);
        $iCountCommentUser = $this->Comment_GetCountCommentsByUserId($this->oUserCurrent->getId(), 'topic');
        $iCountCommentFavourite = $this->Comment_GetCountCommentsFavouriteByUserId($this->oUserCurrent->getId());
        $iCountNoteUser = $this->User_GetCountUserNotesByUserId($this->oUserCurrent->getId());

        $this->Viewer_Assign('oUserProfile', $this->oUserCurrent);
        $this->Viewer_Assign(
            'iCountWallUser',
            $this->Wall_GetCountWall(array('wall_user_id' => $this->oUserCurrent->getId(), 'pid' => null))
        );

        // * Общее число публикация и избранного
        $this->Viewer_Assign('iCountTopicUser', $iCountTopicUser);
        $this->Viewer_Assign('iCountCommentUser', $iCountCommentUser);
        $this->Viewer_Assign('iCountTopicFavourite', $iCountTopicFavourite);
        $this->Viewer_Assign('iCountCommentFavourite', $iCountCommentFavourite);
        $this->Viewer_Assign('iCountNoteUser', $iCountNoteUser);

        $this->Viewer_Assign('iCountCreated', $iCountNoteUser + $iCountTopicUser + $iCountCommentUser);
        $this->Viewer_Assign('iCountFavourite', $iCountCommentFavourite + $iCountTopicFavourite);
        $this->Viewer_Assign('iCountFriendsUser', $this->User_GetCountUsersFriend($this->oUserCurrent->getId()));

        // * Загружаем в шаблон необходимые переменные
        $this->Viewer_Assign('sMenuItemSelect', $this->sMenuItemSelect);
        $this->Viewer_Assign('sMenuSubItemSelect', $this->sMenuSubItemSelect);

        $this->Hook_Run('action_shutdown_settings');
    }

}

// EOF