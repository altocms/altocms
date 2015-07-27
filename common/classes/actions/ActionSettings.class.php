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
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('not_access'), E::ModuleLang()->Get('error'));
            return R::Action('error');
        }

        // * Получаем текущего юзера
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
        $this->SetDefaultEvent('profile');

        // * Устанавливаем title страницы
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('settings_menu'));
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

        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('settings_menu_tuning'));
        $aTimezoneList = array('-12', '-11', '-10', '-9.5', '-9', '-8', '-7', '-6', '-5', '-4.5', '-4', '-3.5', '-3',
                               '-2', '-1', '0', '1', '2', '3', '3.5', '4', '4.5', '5', '5.5', '5.75', '6', '6.5', '7',
                               '8', '8.75', '9', '9.5', '10', '10.5', '11', '11.5', '12', '12.75', '13', '14');
        E::ModuleViewer()->Assign('aTimezoneList', $aTimezoneList);
        /**
         * Если отправили форму с настройками - сохраняем
         */
        if (F::isPost('submit_settings_tuning')) {
            E::ModuleSecurity()->ValidateSendForm();

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
            E::ModuleHook()->Run('settings_tuning_save_before', array('oUser' => $this->oUserCurrent));
            if (E::ModuleUser()->Update($this->oUserCurrent)) {
                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('settings_tuning_submit_ok'));
                E::ModuleHook()->Run('settings_tuning_save_after', array('oUser' => $this->oUserCurrent));
            } else {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
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
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('settings_menu_invite'));
        /**
         * Если отправили форму
         */
        if (isPost('submit_invite')) {
            E::ModuleSecurity()->ValidateSendForm();

            $bError = false;
            /**
             * Есть права на отправку инфайтов?
             */
            if (!E::ModuleACL()->CanSendInvite($this->oUserCurrent) && !$this->oUserCurrent->isAdministrator()) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('settings_invite_available_no'), E::ModuleLang()->Get('error'));
                $bError = true;
            }
            /**
             * Емайл корректен?
             */
            if (!F::CheckVal(F::GetRequestStr('invite_mail'), 'mail')) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('settings_invite_mail_error'), E::ModuleLang()->Get('error'));
                $bError = true;
            }
            /**
             * Запускаем выполнение хуков
             */
            E::ModuleHook()->Run('settings_invate_send_before', array('oUser' => $this->oUserCurrent));
            /**
             * Если нет ошибок, то отправляем инвайт
             */
            if (!$bError) {
                $oInvite = E::ModuleUser()->GenerateInvite($this->oUserCurrent);
                E::ModuleNotify()->SendInvite($this->oUserCurrent, F::GetRequestStr('invite_mail'), $oInvite);
                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('settings_invite_submit_ok'));
                E::ModuleHook()->Run('settings_invate_send_after', array('oUser' => $this->oUserCurrent));
            }
        }

        E::ModuleViewer()->Assign('iCountInviteAvailable', E::ModuleUser()->GetCountInviteAvailable($this->oUserCurrent));
        E::ModuleViewer()->Assign('iCountInviteUsed', E::ModuleUser()->GetCountInviteUsed($this->oUserCurrent->getId()));
    }

    /**
     * Форма смены пароля, емайла
     */
    protected function EventAccount() {
        /**
         * Устанавливаем title страницы
         */
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('settings_menu_profile'));
        $this->sMenuSubItemSelect = 'account';
        /**
         * Если нажали кнопку "Сохранить"
         */
        if (isPost('submit_account_edit')) {
            E::ModuleSecurity()->ValidateSendForm();

            $bError = false;
            /**
             * Проверка мыла
             */
            if (F::CheckVal(F::GetRequestStr('mail'), 'mail')) {
                if (($oUserMail = E::ModuleUser()->GetUserByMail(F::GetRequestStr('mail')))
                    && $oUserMail->getId() != $this->oUserCurrent->getId()
                ) {
                    E::ModuleMessage()->AddError(
                        E::ModuleLang()->Get('settings_profile_mail_error_used'), E::ModuleLang()->Get('error')
                    );
                    $bError = true;
                }
            } else {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('settings_profile_mail_error'), E::ModuleLang()->Get('error'));
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
                        if (E::ModuleSecurity()->CheckSalted(
                            $this->oUserCurrent->getPassword(), $this->GetPost('password_now'), 'pass'
                        )
                        ) {
                            $this->oUserCurrent->setPassword($sPassword, true);
                        } else {
                            $bError = true;
                            E::ModuleMessage()->AddError(
                                E::ModuleLang()->Get('settings_profile_password_current_error'), E::ModuleLang()->Get('error')
                            );
                        }
                    } else {
                        $bError = true;
                        E::ModuleMessage()->AddError(
                            E::ModuleLang()->Get('settings_profile_password_confirm_error'), E::ModuleLang()->Get('error')
                        );
                    }
                } else {
                    $bError = true;
                    E::ModuleMessage()->AddError(
                        E::ModuleLang()->Get('settings_profile_password_new_error', array('num' => $nMinLen)),
                        E::ModuleLang()->Get('error')
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
            E::ModuleHook()->Run(
                'settings_account_save_before', array('oUser' => $this->oUserCurrent, 'bError' => &$bError)
            );
            /**
             * Сохраняем изменения
             */
            if (!$bError) {
                if (E::ModuleUser()->Update($this->oUserCurrent)) {
                    E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('settings_account_submit_ok'));
                    /**
                     * Подтверждение смены емайла
                     */
                    if (F::GetRequestStr('mail') && F::GetRequestStr('mail') != $this->oUserCurrent->getMail()) {
                        if ($oChangemail = E::ModuleUser()->MakeUserChangemail($this->oUserCurrent, F::GetRequestStr('mail'))) {
                            if ($oChangemail->getMailFrom()) {
                                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('settings_profile_mail_change_from_notice'));
                            } else {
                                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('settings_profile_mail_change_to_notice'));
                            }
                        }
                    }

                    E::ModuleHook()->Run('settings_account_save_after', array('oUser' => $this->oUserCurrent));
                } else {
                    E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
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
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('settings_menu_profile'));
        E::ModuleViewer()->Assign('aUserFields', E::ModuleUser()->GetUserFields(''));
        E::ModuleViewer()->Assign('aUserFieldsContact', E::ModuleUser()->GetUserFields(array('contact', 'social')));

        // * Загружаем в шаблон JS текстовки
        E::ModuleLang()->AddLangJs(
            array(
                 'settings_profile_field_error_max'
            )
        );

        // * Если нажали кнопку "Сохранить"
        if ($this->isPost('submit_profile_edit')) {
            E::ModuleSecurity()->ValidateSendForm();

            $bError = false;
            /**
             * Заполняем профиль из полей формы
             */

            // * Определяем гео-объект
            if (F::GetRequest('geo_city')) {
                $oGeoObject = E::ModuleGeo()->GetGeoObject('city', F::GetRequestStr('geo_city'));
            } elseif (F::GetRequest('geo_region')) {
                $oGeoObject = E::ModuleGeo()->GetGeoObject('region', F::GetRequestStr('geo_region'));
            } elseif (F::GetRequest('geo_country')) {
                $oGeoObject = E::ModuleGeo()->GetGeoObject('country', F::GetRequestStr('geo_country'));
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
                $this->oUserCurrent->setProfileAbout(E::ModuleText()->Parser(F::GetRequestStr('profile_about')));
            } else {
                $this->oUserCurrent->setProfileAbout(null);
            }

            // * Ставим дату последнего изменения профиля
            $this->oUserCurrent->setProfileDate(F::Now());

            // * Запускаем выполнение хуков
            E::ModuleHook()->Run('settings_profile_save_before', array('oUser' => $this->oUserCurrent, 'bError' => &$bError));

            // * Сохраняем изменения профиля
            if (!$bError) {
                if (E::ModuleUser()->Update($this->oUserCurrent)) {

                    // * Обновляем название личного блога
                    $oBlog = $this->oUserCurrent->getBlog();
                    if (F::GetRequestStr('blog_title') && $this->checkBlogFields($oBlog)) {
                        $oBlog->setTitle(strip_tags(F::GetRequestStr('blog_title')));
                        E::ModuleBlog()->UpdateBlog($oBlog);
                    }

                    // * Создаем связь с гео-объектом
                    if ($oGeoObject) {
                        E::ModuleGeo()->CreateTarget($oGeoObject, 'user', $this->oUserCurrent->getId());
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
                        E::ModuleGeo()->DeleteTargetsByTarget('user', $this->oUserCurrent->getId());
                        $this->oUserCurrent->setProfileCountry(null);
                        $this->oUserCurrent->setProfileRegion(null);
                        $this->oUserCurrent->setProfileCity(null);
                    }
                    E::ModuleUser()->Update($this->oUserCurrent);

                    // * Обрабатываем дополнительные поля, type = ''
                    $aFields = E::ModuleUser()->GetUserFields('');
                    $aData = array();
                    foreach ($aFields as $iId => $aField) {
                        if (isset($_REQUEST['profile_user_field_' . $iId])) {
                            $aData[$iId] = F::GetRequestStr('profile_user_field_' . $iId);
                        }
                    }
                    E::ModuleUser()->SetUserFieldsValues($this->oUserCurrent->getId(), $aData);

                    // * Динамические поля контактов, type = array('contact','social')
                    $aType = array('contact', 'social');
                    $aFields = E::ModuleUser()->GetUserFields($aType);

                    // * Удаляем все поля с этим типом
                    E::ModuleUser()->DeleteUserFieldValues($this->oUserCurrent->getId(), $aType);
                    $aFieldsContactType = F::GetRequest('profile_user_field_type');
                    $aFieldsContactValue = F::GetRequest('profile_user_field_value');
                    if (is_array($aFieldsContactType)) {
                        $iMax = Config::Get('module.user.userfield_max_identical');
                        foreach ($aFieldsContactType as $iFieldNum => $iFieldType) {
                            $iFieldType = intval($iFieldType);
                            if (!empty($aFieldsContactValue[$iFieldNum])) {
                                $sFieldValue = (string)$aFieldsContactValue[$iFieldNum];
                                if (isset($aFields[$iFieldType]) && $sFieldValue) {
                                    E::ModuleUser()->SetUserFieldsValues($this->oUserCurrent->getId(), array($iFieldType => $sFieldValue), $iMax);
                                }
                            }
                        }
                    }
                    E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('settings_profile_submit_ok'));
                    E::ModuleHook()->Run('settings_profile_save_after', array('oUser' => $this->oUserCurrent));
                } else {
                    E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
                }
            }
        }

        // * Загружаем гео-объект привязки
        $oGeoTarget = E::ModuleGeo()->GetTargetByTarget('user', $this->oUserCurrent->getId());
        E::ModuleViewer()->Assign('oGeoTarget', $oGeoTarget);

        // * Загружаем в шаблон список стран, регионов, городов
        $aCountries = E::ModuleGeo()->GetCountries(array(), array('sort' => 'asc'), 1, 300);
        E::ModuleViewer()->Assign('aGeoCountries', $aCountries['collection']);
        if ($oGeoTarget) {
            if ($oGeoTarget->getCountryId()) {
                $aRegions = E::ModuleGeo()->GetRegions(
                    array('country_id' => $oGeoTarget->getCountryId()), array('sort' => 'asc'), 1, 500
                );
                E::ModuleViewer()->Assign('aGeoRegions', $aRegions['collection']);
            }
            if ($oGeoTarget->getRegionId()) {
                $aCities = E::ModuleGeo()->GetCities(
                    array('region_id' => $oGeoTarget->getRegionId()), array('sort' => 'asc'), 1, 500
                );
                E::ModuleViewer()->Assign('aGeoCities', $aCities['collection']);
            }
        }
        E::ModuleLang()->AddLangJs(
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
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('blog_create_title_error'), E::ModuleLang()->Get('error'));
            $bOk = false;
        } else {

            // * Проверяем есть ли уже блог с таким названием
            if ($oBlogExists = E::ModuleBlog()->GetBlogByTitle(F::GetRequestStr('blog_title'))) {
                if (!$oBlog || $oBlog->getId() != $oBlogExists->getId()) {
                    E::ModuleMessage()->AddError(
                        E::ModuleLang()->Get('blog_create_title_error_unique'), E::ModuleLang()->Get('error')
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

        $iUserId = E::UserId();

        // Get stats of various user publications topics, comments, images, etc. and stats of favourites
        $aProfileStats = E::ModuleUser()->GetUserProfileStats($iUserId);

        // Получим информацию об изображениях пользователя
        /** @var ModuleMresource_EntityMresourceCategory[] $aUserImagesInfo */
        $aUserImagesInfo = E::ModuleMresource()->GetAllImageCategoriesByUserId($iUserId);

        E::ModuleViewer()->Assign('oUserProfile', E::User());
        E::ModuleViewer()->Assign('aProfileStats', $aProfileStats);
        E::ModuleViewer()->Assign('aUserImagesInfo', $aUserImagesInfo);

        // Old style skin compatibility
        E::ModuleViewer()->Assign('iCountTopicUser', $aProfileStats['count_topics']);
        E::ModuleViewer()->Assign('iCountCommentUser', $aProfileStats['count_comments']);
        E::ModuleViewer()->Assign('iCountTopicFavourite', $aProfileStats['favourite_topics']);
        E::ModuleViewer()->Assign('iCountCommentFavourite', $aProfileStats['favourite_comments']);
        E::ModuleViewer()->Assign('iCountNoteUser', $aProfileStats['count_usernotes']);
        E::ModuleViewer()->Assign('iCountWallUser', $aProfileStats['count_wallrecords']);

        E::ModuleViewer()->Assign('iPhotoCount', $aProfileStats['count_images']);
        E::ModuleViewer()->Assign('iCountCreated', $aProfileStats['count_created']);

        E::ModuleViewer()->Assign('iCountFavourite', $aProfileStats['count_favourites']);
        E::ModuleViewer()->Assign('iCountFriendsUser', $aProfileStats['count_friends']);

        // * Загружаем в шаблон необходимые переменные
        E::ModuleViewer()->Assign('sMenuItemSelect', $this->sMenuItemSelect);
        E::ModuleViewer()->Assign('sMenuSubItemSelect', $this->sMenuSubItemSelect);

        E::ModuleHook()->Run('action_shutdown_settings');
    }

}

// EOF