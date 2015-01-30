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

    const PREVIEW_RESIZE = 250;

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
            return R::Action('error');
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

        $this->AddEvent('profile', 'EventProfile');
        $this->AddEvent('invite', 'EventInvite');
        $this->AddEvent('tuning', 'EventTuning');
        $this->AddEvent('account', 'EventAccount');
    }


    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */

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
        if (F::isPost('submit_settings_tuning')) {
            $this->Security_ValidateSendForm();

            if (in_array(F::GetRequestStr('settings_general_timezone'), $aTimezoneList)) {
                $this->oUserCurrent->setSettingsTimezone(F::GetRequestStr('settings_general_timezone'));
            }

            $this->oUserCurrent->setSettingsNoticeNewTopic(F::GetRequest('settings_notice_new_topic') ? 1 : 0);
            $this->oUserCurrent->setSettingsNoticeNewComment(F::GetRequest('settings_notice_new_comment') ? 1 : 0);
            $this->oUserCurrent->setSettingsNoticeNewTalk(F::GetRequest('settings_notice_new_talk') ? 1 : 0);
            $this->oUserCurrent->setSettingsNoticeReplyComment(F::GetRequest('settings_notice_reply_comment') ? 1 : 0);
            $this->oUserCurrent->setSettingsNoticeNewFriend(F::GetRequest('settings_notice_new_friend') ? 1 : 0);
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
            if (!F::CheckVal(F::GetRequestStr('invite_mail'), 'mail')) {
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
                $this->Notify_SendInvite($this->oUserCurrent, F::GetRequestStr('invite_mail'), $oInvite);
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
            if (F::CheckVal(F::GetRequestStr('mail'), 'mail')) {
                if (($oUserMail = $this->User_GetUserByMail(F::GetRequestStr('mail')))
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
                    if (F::GetRequestStr('mail') && F::GetRequestStr('mail') != $this->oUserCurrent->getMail()) {
                        if ($oChangemail = $this->User_MakeUserChangemail($this->oUserCurrent, F::GetRequestStr('mail'))) {
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
        $this->Viewer_Assign('aUserFields', $this->User_GetUserFields(''));
        $this->Viewer_Assign('aUserFieldsContact', $this->User_GetUserFields(array('contact', 'social')));

        // * Загружаем в шаблон JS текстовки
        $this->Lang_AddLangJs(
            array(
                 'settings_profile_field_error_max'
            )
        );

        // * Если нажали кнопку "Сохранить"
        if ($this->isPost('submit_profile_edit')) {
            $this->Security_ValidateSendForm();

            $bError = false;
            /**
             * Заполняем профиль из полей формы
             */

            // * Определяем гео-объект
            if (F::GetRequest('geo_city')) {
                $oGeoObject = $this->Geo_GetGeoObject('city', F::GetRequestStr('geo_city'));
            } elseif (F::GetRequest('geo_region')) {
                $oGeoObject = $this->Geo_GetGeoObject('region', F::GetRequestStr('geo_region'));
            } elseif (F::GetRequest('geo_country')) {
                $oGeoObject = $this->Geo_GetGeoObject('country', F::GetRequestStr('geo_country'));
            } else {
                $oGeoObject = null;
            }

            // * Проверяем имя
            if (F::CheckVal(F::GetRequestStr('profile_name'), 'text', 2, Config::Get('module.user.name_max'))) {
                $this->oUserCurrent->setProfileName(F::GetRequestStr('profile_name'));
            } else {
                $this->oUserCurrent->setProfileName(null);
            }

            // * Проверяем пол
            if (in_array(F::GetRequestStr('profile_sex'), array('man', 'woman', 'other'))) {
                $this->oUserCurrent->setProfileSex(F::GetRequestStr('profile_sex'));
            } else {
                $this->oUserCurrent->setProfileSex('other');
            }

            // * Проверяем дату рождения
            $nDay = intval(F::GetRequestStr('profile_birthday_day'));
            $nMonth = intval(F::GetRequestStr('profile_birthday_month'));
            $nYear = intval(F::GetRequestStr('profile_birthday_year'));
            if (checkdate($nMonth, $nDay, $nYear)) {
                $this->oUserCurrent->setProfileBirthday(date('Y-m-d H:i:s', mktime(0, 0, 0, $nMonth, $nDay, $nYear)));
            } else {
                $this->oUserCurrent->setProfileBirthday(null);
            }

            // * Проверяем информацию о себе
            if (F::CheckVal(F::GetRequestStr('profile_about'), 'text', 1, 3000)) {
                $this->oUserCurrent->setProfileAbout($this->Text_Parser(F::GetRequestStr('profile_about')));
            } else {
                $this->oUserCurrent->setProfileAbout(null);
            }

            // * Ставим дату последнего изменения профиля
            $this->oUserCurrent->setProfileDate(F::Now());

            // * Запускаем выполнение хуков
            $this->Hook_Run('settings_profile_save_before', array('oUser' => $this->oUserCurrent, 'bError' => &$bError));

            // * Сохраняем изменения профиля
            if (!$bError) {
                if ($this->User_Update($this->oUserCurrent)) {

                    // * Обновляем название личного блога
                    $oBlog = $this->oUserCurrent->getBlog();
                    if (F::GetRequestStr('blog_title') && $this->checkBlogFields($oBlog)) {
                        $oBlog->setTitle(strip_tags(F::GetRequestStr('blog_title')));
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
                    $aFields = $this->User_GetUserFields('');
                    $aData = array();
                    foreach ($aFields as $iId => $aField) {
                        if (isset($_REQUEST['profile_user_field_' . $iId])) {
                            $aData[$iId] = F::GetRequestStr('profile_user_field_' . $iId);
                        }
                    }
                    $this->User_SetUserFieldsValues($this->oUserCurrent->getId(), $aData);

                    // * Динамические поля контактов, type = array('contact','social')
                    $aType = array('contact', 'social');
                    $aFields = $this->User_GetUserFields($aType);

                    // * Удаляем все поля с этим типом
                    $this->User_DeleteUserFieldValues($this->oUserCurrent->getId(), $aType);
                    $aFieldsContactType = F::GetRequest('profile_user_field_type');
                    $aFieldsContactValue = F::GetRequest('profile_user_field_value');
                    if (is_array($aFieldsContactType)) {
                        foreach ($aFieldsContactType as $k => $v) {
                            $v = (string)$v;
                            if (isset($aFields[$v]) && isset($aFieldsContactValue[$k])
                                && is_string(
                                    $aFieldsContactValue[$k]
                                )
                            ) {
                                $this->User_SetUserFieldsValues(
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
        $this->Lang_AddLangJs(
            array(
                'settings_profile_avatar_resize_title',
                'settings_profile_avatar_resize_text',
                'settings_profile_photo_resize_title',
                'settings_profile_photo_resize_text',
            )
        );
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
        if (!F::CheckVal(F::GetRequestStr('blog_title'), 'text', 2, 200)) {
            $this->Message_AddError($this->Lang_Get('blog_create_title_error'), $this->Lang_Get('error'));
            $bOk = false;
        } else {

            // * Проверяем есть ли уже блог с таким названием
            if ($oBlogExists = $this->Blog_GetBlogByTitle(F::GetRequestStr('blog_title'))) {
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