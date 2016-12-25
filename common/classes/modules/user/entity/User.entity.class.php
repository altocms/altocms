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
 * Сущность пользователя
 *
 * @package modules.user
 * @since   1.0
 */
class ModuleUser_EntityUser extends Entity {

    const DEFAULT_AVATAR_SIZE = 100;
    const DEFAULT_PHOTO_SIZE = 250;

    /**
     * Определяем правила валидации
     * Правила валидации нужно определять только здесь!
     *
     * @var array
     */
    public function Init() {

        parent::Init();
        $this->aValidateRules[] = array(
            'login',
            'login',
            'on' => array('registration', ''), // '' - означает дефолтный сценарий
        );
        $this->aValidateRules[] = array(
            'login',
            'login_exists',
            'on' => array('registration'),
        );
        $this->aValidateRules[] = array(
            'mail',
            'email',
            'allowEmpty' => false,
            'on'         => array('registration', ''),
        );
        $this->aValidateRules[] = array(
            'mail',
            'mail_exists',
            'on' => array('registration'),
        );
        $this->aValidateRules[] = array(
            'password',
            'password',
            'on'         => array('registration'),
        );
        $this->aValidateRules[] = array(
            'password_confirm',
            'compare',
            'compareField' => 'password',
            'on'           => array('registration'),
        );

        // Определяем дополнительные правила валидации
        if (Config::Get('module.user.captcha_use_registration')) {
            $this->aValidateRules[] = array('captcha', 'captcha', 'on' => array('registration'));
        }
    }

    /**
     * Определяем дополнительные правила валидации
     *
     * @param   array|null $aParam
     */
    public function __construct($aParam = null) {

        parent::__construct($aParam);
    }

    /**
     * Типы ресурсов, загружаемые в профайле пользователя
     *
     * @return array
     */
    protected function _getDefaultMediaTypes() {

        return array('profile_avatar', 'profile_photo');
    }

    /**
     * Валидация пользователя
     *
     * @param string $sValue     Валидируемое значение
     * @param array  $aParams    Параметры
     *
     * @return bool|string
     */
    public function ValidateLogin($sValue, $aParams) {

        $xResult = true;
        if ($sValue) {
            $nError = E::ModuleUser()->InvalidLogin($sValue);
            if (!$nError) {
                return $xResult;
            } else {
                $xResult = E::ModuleUser()->GetLoginErrorMessage($nError);
            }
        } else {
            $xResult = E::ModuleLang()->Get('registration_login_error');
        }
        return $xResult;
    }

    /**
     * Проверка логина на существование
     *
     * @param string $sValue     Валидируемое значение
     * @param array  $aParams    Параметры
     *
     * @return bool
     */
    public function ValidateLoginExists($sValue, $aParams) {

        if (!E::ModuleUser()->GetUserByLogin($sValue)) {
            return true;
        }
        return E::ModuleLang()->Get('registration_login_error_used');
    }

    /**
     * Проверка емайла на существование
     *
     * @param string $sValue     Валидируемое значение
     * @param array  $aParams    Параметры
     *
     * @return bool
     */
    public function ValidateMailExists($sValue, $aParams) {

        if (!E::ModuleUser()->GetUserByMail($sValue)) {
            return true;
        }
        return E::ModuleLang()->Get('registration_mail_error_used');
    }

    public function ValidatePassword($sValue, $aParams) {

        $iMinLength = Config::Val('module.security.password_len', 3);
        if ($sValue && $this->getLogin() && $sValue === $this->getLogin()) {
            return E::ModuleLang()->Get('registration_password_error', array('min' => $iMinLength));
        }
        if (mb_strlen($sValue, 'UTF-8') < $iMinLength) {
            return E::ModuleLang()->Get('registration_password_error', array('min' => $iMinLength));
        }
        return true;
    }

    /**
     * Возвращает ID пользователя
     *
     * @return int|null
     */
    public function getId() {

        $iUserId = $this->getProp('user_id');
        return $iUserId ? intval($iUserId) : null;
    }

    /**
     * Возвращает логин
     *
     * @return string|null
     */
    public function getLogin() {

        return $this->getProp('user_login');
    }

    /**
     * Возвращает пароль (ввиде хеша)
     *
     * @return string|null
     */
    public function getPassword() {

        return $this->getProp('user_password');
    }

    /**
     * Возвращает емайл
     *
     * @return string|null
     */
    public function getMail() {

        return $this->getProp('user_mail');
    }

    /**
     * Возвращает силу
     *
     * @return string
     */
    public function getSkill() {

        return number_format(round($this->getProp('user_skill'), 3), 3, '.', '');
    }

    /**
     * Возвращает дату регистрации
     *
     * @return string|null
     */
    public function getDateRegister() {

        return $this->getProp('user_date_register');
    }

    /**
     * Возвращает дату активации
     *
     * @return string|null
     */
    public function getDateActivate() {

        return $this->getProp('user_date_activate');
    }

    /**
     * Возвращает дату последнего комментирования
     *
     * @return mixed|null
     */
    public function getDateCommentLast() {

        return $this->getProp('user_date_comment_last');
    }

    /**
     * Возвращает IP регистрации
     *
     * @return string|null
     */
    public function getIpRegister() {

        return $this->getProp('user_ip_register');
    }

    /**
     * Возвращает рейтинг
     *
     * @return string
     */
    public function getRating() {

        return number_format(round($this->getProp('user_rating'), 2), 2, '.', '');
    }

    /**
     * Вовзращает количество проголосовавших
     *
     * @return int|null
     */
    public function getCountVote() {

        return $this->getProp('user_count_vote');
    }

    /**
     * Возвращает статус активированности
     *
     * @return int|null
     */
    public function getActivate() {

        return $this->getProp('user_activate');
    }

    /**
     * LS-compatibility
     * @deprecated since 1.1
     * @see getActivationKey()
     */
    public function getActivateKey() {

        return $this->getActivationKey();
    }

    /**
     * Return activation key
     *
     * @return string|null
     */
    public function getActivationKey() {

        return $this->getProp('user_activate_key');
    }

    /**
     * Возвращает имя
     *
     * @return string|null
     */
    public function getProfileName() {

        return $this->getProp('user_profile_name');
    }

    /**
     * Возвращает пол
     *
     * @return string|null
     */
    public function getProfileSex() {

        $sSex = $this->getProp('user_profile_sex');
        return $sSex ? $sSex : 'other';
    }

    /**
     * Возвращает название страны
     *
     * @return string|null
     */
    public function getProfileCountry() {

        return $this->getProp('user_profile_country');
    }

    /**
     * Возвращает название региона
     *
     * @return string|null
     */
    public function getProfileRegion() {

        return $this->getProp('user_profile_region');
    }

    /**
     * Возвращает название города
     *
     * @return string|null
     */
    public function getProfileCity() {

        return $this->getProp('user_profile_city');
    }

    /**
     * Возвращает дату рождения
     *
     * @return string|null
     */
    public function getProfileBirthday() {

        return $this->getProp('user_profile_birthday');
    }

    /**
     * Возвращает информацию о себе
     *
     * @return string|null
     */
    public function getProfileAbout() {

        return $this->getProp('user_profile_about');
    }

    /**
     * Возвращает дату редактирования профиля
     *
     * @return string|null
     */
    public function getProfileDate() {

        return $this->getProp('user_profile_date');
    }

    /**
     * Возвращает полный веб путь до аватра
     *
     * @return string|null
     */
    public function getProfileAvatar() {

        return $this->getProp('user_profile_avatar');
    }

    /**
     * Возвращает расширение автара
     *
     * @return string|null
     */
    public function getProfileAvatarType() {

        return ($sPath = $this->getAvatarUrl()) ? pathinfo($sPath, PATHINFO_EXTENSION) : null;
    }

    /**
     * Возвращает полный веб путь до фото
     *
     * @return string|null
     */
    public function getProfilePhoto() {

        return $this->getProp('user_profile_foto');
    }

    public function getProfileFoto() {

        return $this->getProfilePhoto();
    }

    /**
     * Returns display name according with pattern from configuration
     * If pattern is missed or if result string is empty then returns login
     *
     * @return string
     */
    public function getDisplayName() {

        $sDisplayName = $this->getProp('_display_name');
        if (!$sDisplayName) {
            $sDisplayName = Config::Get('module.user.display_name');
            if (!$sDisplayName) {
                $sDisplayName = $this->getLogin();
            } else {
                $sDisplayName = str_replace(array('%%login%%', '%%profilename%%'), array($this->getLogin(), $this->getProfileName()), $sDisplayName);
                if (!$sDisplayName) {
                    $sDisplayName = $this->getLogin();
                }
            }
            $this->setProp('_display_name', $sDisplayName);
        }
        return $sDisplayName;
    }

    /**
     * Возвращает статус уведомления о новых топиках
     *
     * @return int|null
     */
    public function getSettingsNoticeNewTopic() {

        return $this->getProp('user_settings_notice_new_topic');
    }

    /**
     * Возвращает статус уведомления о новых комментариях
     *
     * @return int|null
     */
    public function getSettingsNoticeNewComment() {

        return $this->getProp('user_settings_notice_new_comment');
    }

    /**
     * Возвращает статус уведомления о новых письмах
     *
     * @return int|null
     */
    public function getSettingsNoticeNewTalk() {

        return $this->getProp('user_settings_notice_new_talk');
    }

    /**
     * Возвращает статус уведомления о новых ответах в комментариях
     *
     * @return int|null
     */
    public function getSettingsNoticeReplyComment() {

        return $this->getProp('user_settings_notice_reply_comment');
    }

    /**
     * Возвращает статус уведомления о новых друзьях
     *
     * @return int|null
     */
    public function getSettingsNoticeNewFriend() {

        return $this->getProp('user_settings_notice_new_friend');
    }

    /**
     * @return mixed|null
     */
    public function getLastSession() {

        return $this->getProp('user_last_session');
    }

    /**
     * Возвращает значения пользовательских полей
     *
     * @param bool         $bNotEmptyOnly Возвращать или нет только не пустые
     * @param string|array $xType         Тип полей
     *
     * @return ModuleUser_EntityField[]
     */
    public function getUserFieldValues($bNotEmptyOnly = true, $xType = array()) {

        $aUserFields = $this->getProp('_user_fields');
        if (is_null($aUserFields)) {
            $aUserFields = E::ModuleUser()->GetUserFieldsValues($this->getId(), false);
            $this->setProp('_user_fields', $aUserFields);
        }
        $aResult = array();
        if (!is_array($xType)) {
            $aType = array($xType);
        } else {
            $aType = $xType;
        }
        if ($aUserFields) {
            foreach($aUserFields as $iIndex => $oUserField) {
                if (!$bNotEmptyOnly || $oUserField->getValue()) {
                    if (empty($aType) || in_array($oUserField->getType(), $aType)) {
                        $aResult[$iIndex] = $oUserField;
                    }
                }
            }
        }

        return $aResult;
    }

    /**
     * Возвращает объект сессии
     *
     * @return ModuleUser_EntitySession|null
     */
    public function getSession() {

        if (!$this->getProp('session')) {
            $this->_aData['session'] = E::ModuleUser()->GetSessionByUserId($this->getId());
        }
        return $this->getProp('session');
    }

    /**
     * Returns current session of user
     *
     * @return ModuleUser_EntitySession|null
     */
    public function getCurrentSession() {

        if (!$this->getProp('_current_session')) {
            $this->_aData['_current_session'] = E::ModuleUser()->GetSessionByUserId($this->getId(), E::ModuleSession()->GetKey());
        }
        return $this->getProp('_current_session');
    }

    /**
     * Возвращает роль пользователя
     *
     * @return int|null
     */
    public function getRole() {

        return $this->getProp('user_role');
    }

    /**
     * Return online status of user
     *
     * @return bool
     */
    public function isOnline() {

        if ($oSession = $this->getSession()) {
            if ($oSession->getDateExit()) {
                // User has logout
                return false;
            }
            if ($iTime = C::Get('module.user.online_time')) {
                if (time() - strtotime($oSession->getDateLast()) < $iTime) {
                    // Last session time less then $iTime seconds ago
                    return true;
                }
            } else {
                return false;
            }
        }
        return false;
    }


    /**
     * @param string $sType
     * @param string $xSize
     *
     * @return string
     */
    protected function _getProfileImageUrl($sType, $xSize = null) {

        $sPropKey = '_profile_imge_url_' . $sType . '-' . $xSize;
        $sUrl = $this->getProp($sPropKey);
        if ($sUrl === null) {
            $sUrl = '';
            $aImages = $this->getMediaResources($sType);
            if (!empty($aImages)) {
                /** @var ModuleMresource_EntityMresourceRel $oImage */
                $oImage = reset($aImages);
                $sUrl = $oImage->getImageUrl($xSize);
            }
            $this->setProp($sPropKey, $sUrl);
        }
        return $sUrl;
    }

    /**
     * Возвращает полный URL до аватары нужного размера
     *
     * @param int|string $xSize - Размер (120 | '120x100')
     *
     * @return  string
     */
    public function getAvatarUrl($xSize = null) {

        // Gets default size from config or sets it to 100
        if (!$xSize) {
            $xSize = Config::Get('module.user.profile_avatar_size');
            if (!$xSize) {
                $xSize = self::DEFAULT_AVATAR_SIZE;
            }
        }

        $sPropKey = '_avatar_url_' . $xSize;
        $sUrl = $this->getProp($sPropKey);
        if (is_null($sUrl)) {
            $sSize = C::Val('module.uploader.images.profile_avatar.size.' . $xSize, $xSize);
            $sUrl = $this->_getProfileImageUrl('profile_avatar', $sSize);
            if (!$sUrl) {
                // Old version compatibility
                $sUrl = $this->getProfileAvatar();
                if ($sUrl && ($sUrl[0] == '@') && $sSize) {
                    $sUrl = E::ModuleUploader()->ResizeTargetImage($sUrl, $sSize);
                } elseif (empty($sUrl)) {
                    $sUrl = $this->getDefaultAvatarUrl($sSize);
                }
            }
            $this->setProp($sPropKey, $sUrl);
        }
        return $sUrl;
    }

    /**
     * @param string     $sImageType
     * @param string|int $xSize
     *
     * @return array
     */
    protected function _defineImageSize($sImageType, $xSize) {

        $sSize = C::Val('module.uploader.images.' . $sImageType . '.size.' . $xSize, $xSize);
        $aResult = F::File_ImgModAttr($sSize);
        if (empty($aResult['width']) && empty($aResult['height'])) {
            $sSize = C::Val('module.uploader.images.default.size.' . $xSize, $xSize);
            $aResult = F::File_ImgModAttr($sSize);
        }
        return $aResult;
    }

    /**
     * @param int|string $xSize
     *
     * @return string
     */
    public function getAvatarImageSizeAttr($xSize = null) {

        // Gets default size from config or sets it to default
        if (empty($xSize)) {
            $xSize = Config::Val('module.user.profile_avatar_size', self::DEFAULT_AVATAR_SIZE);
        }
        $aImgSize = $this->_defineImageSize('profile_avatar', $xSize);

        return $aImgSize['attr'];
    }

    /**
     * @param int|string $xSize
     *
     * @return string
     */
    public function getAvatarImageSizeStyle($xSize = null) {

        // Gets default size from config or sets it to default
        if (empty($xSize)) {
            $xSize = Config::Val('module.user.profile_avatar_size', self::DEFAULT_AVATAR_SIZE);
        }
        $aImgSize = $this->_defineImageSize('profile_avatar', $xSize);

        return $aImgSize['style'];
    }

    /**
     * Возвращает дефолтный аватар пользователя
     *
     * @param int|string $xSize
     * @param string     $sSex
     *
     * @return string
     */
    public function getDefaultAvatarUrl($xSize = null, $sSex = null) {

        if (!$sSex) {
            $sSex = ($this->getProfileSex() === 'woman' ? 'female' : 'male');
        }
        if ($sSex !== 'female' && $sSex !== 'male') {
            $sSex = 'male';
        }

        $sPath = E::ModuleUploader()->GetUserAvatarDir(0)
            . 'avatar_' . Config::Get('view.skin', Config::LEVEL_CUSTOM) . '_'
            . $sSex . '.png';

        if (!$xSize) {
            if (Config::Get('module.user.profile_avatar_size')) {
                $xSize = Config::Get('module.user.profile_avatar_size');
            } else {
                $xSize = self::DEFAULT_AVATAR_SIZE;
            }
        }

        if ($sRealSize = C::Get('module.uploader.images.profile_avatar.size.' . $xSize)) {
            $xSize = $sRealSize;
        }
        if (is_string($xSize) && strpos($xSize, 'x')) {
            list($nW, $nH) = array_map('intval', explode('x', $xSize));
        } else {
            $nW = $nH = intval($xSize);
        }

        $sResizePath = $sPath . '-' . $nW . 'x' . $nH . '.' . pathinfo($sPath, PATHINFO_EXTENSION);
        if (Config::Get('module.image.autoresize') && !F::File_Exists($sResizePath)) {
            $sResizePath = E::ModuleImg()->AutoresizeSkinImage($sResizePath, 'avatar', max($nH, $nW));
        }
        if ($sResizePath) {
            $sPath = $sResizePath;
        } elseif (!F::File_Exists($sPath)) {
            $sPath = E::ModuleImg()->AutoresizeSkinImage($sPath, 'avatar', null);
        }

        return E::ModuleUploader()->Dir2Url($sPath);
    }

    /**
     * Возвращает информацию о том, есть ли вообще у пользователя аватар
     *
     * @return bool
     */
    public function hasAvatar() {

        $aImages = $this->getMediaResources('profile_avatar');
        return !empty($aImages);
    }

    /**
     * @deprecated Deprecated since 1.0
     */
    public function getProfileAvatarPath($xSize = null) {

        return $this->getAvatarUrl($xSize);
    }

    /**
     * Возвращает полный URL до фото профиля
     *
     * @param int|string $xSize - рвзмер (240 | '240x320')
     *
     * @return string
     */
    public function GetPhotoUrl($xSize = null) {

        $sPropKey = '_photo_url_' . $xSize;
        $sUrl = $this->getProp($sPropKey);
        if (is_null($sUrl)) {
            if (!$xSize) {
                if (Config::Get('module.user.profile_photo_size')) {
                    $xSize = Config::Get('module.user.profile_photo_size');
                } else {
                    $xSize = self::DEFAULT_PHOTO_SIZE;
                }
            }
            if ($sRealSize = C::Get('module.uploader.images.profile_photo.size.' . $xSize)) {
                $xSize = $sRealSize;
            }

            $sUrl = $this->_getProfileImageUrl('profile_photo', $xSize);
            if (!$sUrl) {
                // Old version compatibility
                $sUrl = $this->getProfilePhoto();
                if ($sUrl) {
                    if ($xSize) {
                        $sUrl = E::ModuleUploader()->ResizeTargetImage($sUrl, $xSize);
                    }
                } else {
                    $sUrl = $this->GetDefaultPhotoUrl($xSize);
                }
            }
            $this->setProp($sPropKey, $sUrl);
        }
        return $sUrl;
    }

    /**
     * @param int|string $xSize
     *
     * @return string
     */
    public function getPhotoImageSizeAttr($xSize = null) {

        // Gets default size from config or sets it to default
        if (empty($xSize)) {
            $xSize = self::DEFAULT_PHOTO_SIZE;
        }
        $aImgSize = $this->_defineImageSize('profile_photo', $xSize);

        return $aImgSize['attr'];
    }

    /**
     * Возвращает информацию о том, есть ли вообще у пользователя аватар
     *
     * @return bool
     */
    public function hasPhoto() {

        $aImages = $this->getMediaResources('profile_photo');
        return !empty($aImages);
    }

    /**
     * Returns URL for default photo of current skin
     *
     * @param int|string $xSize
     * @param string     $sSex
     *
     * @return string
     */
    public function GetDefaultPhotoUrl($xSize = null, $sSex = null) {

        if (!$sSex) {
            $sSex = ($this->getProfileSex() === 'woman' ? 'female' : 'male');
        }
        if (!$xSize) {
            $xSize = self::DEFAULT_PHOTO_SIZE;
        }
        if ($sRealSize = C::Get('module.uploader.images.profile_photo.size.' . $xSize)) {
            $xSize = $sRealSize;
        }
        if (is_numeric($xSize)) {
            $xSize = $xSize . 'x' . $xSize;
        }
        $sResult = E::ModuleUser()->GetDefaultPhotoUrl($xSize, $sSex);

        return $sResult;
    }

    /**
     * @deprecated Deprecated since 1.0
     */
    public function getProfileFotoPath() {

        return $this->GetPhotoUrl();
    }

    /**
     * @deprecated Deprecated since 1.0
     */
    public function getProfileFotoDefault() {

        return $this->GetDefaultPhotoUrl();
    }

    /**
     * Возвращает объект голосования за пользователя текущего пользователя
     *
     * @return ModuleVote_EntityVote|null
     */
    public function getVote() {

        return $this->getProp('vote');
    }

    /**
     * Возвращает статус дружбы
     *
     * @return bool|null
     */
    public function getUserIsFriend() {

        return $this->getProp('user_is_friend');
    }

    /**
     * Возвращает статус администратора сайта
     *
     * @return bool
     */
    public function isAdministrator() {

        return $this->HasRole(ModuleUser::USER_ROLE_ADMINISTRATOR);
    }

    /**
     * Возвращает статус модкратора сайта
     *
     * @return bool
     */
    public function isModerator() {

        return $this->HasRole(ModuleUser::USER_ROLE_MODERATOR);
    }

    public function isActivated() {

        return (bool)$this->getProp('user_activate');
    }

    /**
     * Возвращает веб путь до профиля пользователя
     *
     * @deprecated Deprecated since 1.0
     * @return string
     */
    public function getUserWebPath() {

        return $this->getProfileUrl();
    }

    /**
     * @return string
     */
    public function getUserUrl() {

        return $this->getProfileUrl();
    }

    /**
     * Возвращает URL до профиля пользователя
     *
     * @param   string|null $sUrlMask - еcли передан параметр, то формирует URL по этой маске
     * @param   bool        $bFullUrl - возвращать полный путь (или относительный, если false)
     *
     * @return string
     */
    public function getProfileUrl($sUrlMask = null, $bFullUrl = true) {

        $sKey = '-url-' . ($sUrlMask ? $sUrlMask : '') . ($bFullUrl ? '-1' : '-0');
        $sUrl = $this->getProp($sKey);
        if (!is_null($sUrl)) {
            return $sUrl;
        }

        if (!$sUrlMask) {
            $sUrlMask = R::GetUserUrlMask();
        }
        if (!$sUrlMask) {
            // формирование URL по умолчанию
            $sUrl = R::GetPath('profile/' . $this->getLogin());
            $this->setProp($sKey, $sUrl);
            return $sUrl;
        }
        $aReplace = array(
            '%user_id%' => $this->GetId(),
            '%login%'   => $this->GetLogin(),
        );
        $sUrl = strtr($sUrlMask, $aReplace);
        if (strpos($sUrl, '/')) {
            list($sAction, $sPath) = explode('/', $sUrl, 2);
            $sUrl = R::GetPath($sAction) . $sPath;
        } else {
            $sUrl = F::File_RootUrl() . $sUrl;
        }
        if (substr($sUrl, -1) !== '/') {
            $sUrl .= '/';
        }
        $this->setProp($sKey, $sUrl);

        return $sUrl;
    }

    /**
     * Возвращает объект дружбы с текущим пользователем
     *
     * @return ModuleUser_EntityFriend|null
     */
    public function getUserFriend() {

        return $this->getProp('user_friend');
    }

    /**
     * Проверяет подписан ли текущий пользователь на этого
     *
     * @return bool
     */
    public function isFollow() {

        if ($oUserCurrent = E::ModuleUser()->GetUserCurrent()) {
            return E::ModuleStream()->IsSubscribe($oUserCurrent->getId(), $this->getId());
        }
        return false;
    }

    /**
     * Возвращает объект заметки о подльзователе, которую оставил текущий пользователй
     *
     * @return ModuleUser_EntityNote|null
     */
    public function getUserNote() {

        $oUserCurrent = E::ModuleUser()->GetUserCurrent();
        if ($this->getProp('user_note') === null && $oUserCurrent) {
            $this->_aData['user_note'] = E::ModuleUser()->GetUserNote($this->getId(), $oUserCurrent->getId());
        }
        return $this->getProp('user_note');
    }

    /**
     * Возвращает личный блог пользователя
     *
     * @return ModuleBlog_EntityBlog|null
     */
    public function getBlog() {

        if (!$this->getProp('blog')) {
            $this->_aData['blog'] = E::ModuleBlog()->GetPersonalBlogByUserId($this->getId());
        }
        return $this->getProp('blog');
    }

    public function GetBanLine() {

        return $this->GetProp('banline');
    }

    public function IsBannedUnlim() {

        return ((bool)$this->GetProp('banunlim'));
    }

    public function GetBanComment() {

        return $this->GetProp('bancomment');
    }

    /**
     * @return bool
     */
    public function IsBannedByLogin() {
        $dBanline = $this->getBanLine();
        return ($this->IsBannedUnlim()
            || ($dBanline && ($dBanline > date('Y-m-d H:i:s')) && $this->GetProp('banactive')));
    }

    /**
     * @return bool
     */
    public function IsBannedByIp() {

        // return ($this->GetProp('ban_ip'));

        // issue 258 {@link https://github.com/altocms/altocms/issues/258}
        $bResult = $this->GetProp('ban_ip');
        if (is_null($bResult)) {
            $bResult = (bool)E::ModuleUser()->IpIsBanned(F::GetUserIp());
            $this->setProp('ban_ip', $bResult);
        }
        return $bResult;
    }

    /**
     * @return bool
     */
    public function IsBanned() {

        return ($this->IsBannedByLogin() || $this->IsBannedByIp());
    }


    /**
     * Устанавливает ID пользователя
     *
     * @param int $data
     */
    public function setId($data) {

        $this->setProp('user_id', $data);
    }

    /**
     * Устанавливает логин
     *
     * @param string $data
     */
    public function setLogin($data) {

        $this->setProp('user_login', trim($data));
    }

    /**
     * Устанавливает пароль
     *
     * @param   string $sPassword
     * @param   bool   $bEncrypt   - false, если пароль уже захеширован
     */
    public function setPassword($sPassword, $bEncrypt = false) {

        if ($bEncrypt) {
            $this->_aData['user_password'] = E::ModuleSecurity()->Salted($sPassword, 'pass');
        } else {
            $this->setProp('user_password', $sPassword);
        }
    }

    /**
     * Устанавливает емайл
     *
     * @param string $data
     */
    public function setMail($data) {

        $this->setProp('user_mail', trim($data));
    }

    /**
     * Устанавливает силу
     *
     * @param float $data
     */
    public function setSkill($data) {

        $this->setProp('user_skill', $data);
    }

    /**
     * Устанавливает дату регистрации
     *
     * @param string $data
     */
    public function setDateRegister($data) {

        $this->setProp('user_date_register', $data);
    }

    /**
     * Устанавливает дату активации
     *
     * @param string $data
     */
    public function setDateActivate($data) {

        $this->setProp('user_date_activate', $data);
    }

    /**
     * Устанавливает дату последнего комментирования
     *
     * @param string $data
     */
    public function setDateCommentLast($data) {

        $this->setProp('user_date_comment_last', $data);
    }

    /**
     * Устанавливает IP регистрации
     *
     * @param string $data
     */
    public function setIpRegister($data) {

        $this->setProp('user_ip_register', $data);
    }

    /**
     * Устанавливает рейтинг
     *
     * @param float $data
     */
    public function setRating($data) {

        $this->setProp('user_rating', $data);
    }

    /**
     * Устанавливает количество проголосовавших
     *
     * @param int $data
     */
    public function setCountVote($data) {

        $this->setProp('user_count_vote', $data);
    }

    /**
     * Устанавливает статус активированности
     *
     * @param int $data
     */
    public function setActivate($data) {

        $this->setProp('user_activate', $data);
    }

    /**
     * LS-compatibility
     * @deprecated since 1.1
     * @see setActivationKey()
     */
    public function setActivateKey($data) {

        $this->setActivationKey($data);
    }

    /**
     * Set activation key
     *
     * @param string $data
     */
    public function setActivationKey($data) {

        $this->setProp('user_activate_key', $data);
    }

    /**
     * Устанавливает имя
     *
     * @param string $data
     */
    public function setProfileName($data) {

        $this->setProp('user_profile_name', $data);
    }

    /**
     * Устанавливает пол
     *
     * @param string $data
     */
    public function setProfileSex($data) {

        $this->setProp('user_profile_sex', $data);
    }

    /**
     * Устанавливает название страны
     *
     * @param string $data
     */
    public function setProfileCountry($data) {

        $this->setProp('user_profile_country', $data);
    }

    /**
     * Устанавливает название региона
     *
     * @param string $data
     */
    public function setProfileRegion($data) {

        $this->setProp('user_profile_region', $data);
    }

    /**
     * Устанавливает название города
     *
     * @param string $data
     */
    public function setProfileCity($data) {

        $this->setProp('user_profile_city', $data);
    }

    /**
     * Устанавливает дату рождения
     *
     * @param string $data
     */
    public function setProfileBirthday($data) {

        $this->setProp('user_profile_birthday', $data);
    }

    /**
     * Устанавливает информацию о себе
     *
     * @param string $data
     */
    public function setProfileAbout($data) {

        $this->setProp('user_profile_about', $data);
    }

    /**
     * Устанавливает дату редактирования профиля
     *
     * @param string $data
     */
    public function setProfileDate($data) {

        $this->setProp('user_profile_date', $data);
    }

    /**
     * Устанавливает полный веб путь до аватра
     *
     * @param string $data
     */
    public function setProfileAvatar($data) {

        $this->setProp('user_profile_avatar', $data);
    }

    /**
     * Устанавливает полный веб путь до фото
     *
     * @param string $data
     */
    public function setProfilePhoto($data) {

        $this->setProp('user_profile_foto', $data);
    }

    /**
     * LS-compatibility
     *
     * @param $data
     */
    public function setProfileFoto($data) {

        $this->setProfilePhoto($data);
    }

    /**
     * Устанавливает статус уведомления о новых топиках
     *
     * @param int $data
     */
    public function setSettingsNoticeNewTopic($data) {

        $this->setProp('user_settings_notice_new_topic', $data);
    }

    /**
     * Устанавливает статус уведомления о новых комментариях
     *
     * @param int $data
     */
    public function setSettingsNoticeNewComment($data) {

        $this->setProp('user_settings_notice_new_comment', $data);
    }

    /**
     * Устанавливает статус уведомления о новых письмах
     *
     * @param int $data
     */
    public function setSettingsNoticeNewTalk($data) {

        $this->setProp('user_settings_notice_new_talk', $data);
    }

    /**
     * Устанавливает статус уведомления о новых ответах в комментариях
     *
     * @param int $data
     */
    public function setSettingsNoticeReplyComment($data) {

        $this->setProp('user_settings_notice_reply_comment', $data);
    }

    /**
     * Устанавливает статус уведомления о новых друзьях
     *
     * @param int $data
     */
    public function setSettingsNoticeNewFriend($data) {

        $this->setProp('user_settings_notice_new_friend', $data);
    }

    /**
     * Устанавливает объект сессии
     *
     * @param ModuleUser_EntitySession $data
     */
    public function setSession($data) {

        $this->setProp('session', $data);
    }

    /**
     * Устанавливает статус дружбы
     *
     * @param int $data
     */
    public function setUserIsFriend($data) {

        $this->setProp('user_is_friend', $data);
    }

    /**
     * Устанавливает объект голосования за пользователя текущего пользователя
     *
     * @param ModuleVote_EntityVote $data
     */
    public function setVote($data) {

        $this->setProp('vote', $data);
    }

    /**
     * Устанавливаем статус дружбы с текущим пользователем
     *
     * @param int $data
     */
    public function setUserFriend($data) {

        $this->setProp('user_friend', $data);
    }

    public function setLastSession($data) {

        $this->setProp('user_last_session', $data);
    }

    /**
     * Устанавливает роль пользователя
     *
     * @param $data
     */
    public function setRole($data) {

        $this->setProp('user_role', $data);
    }

    /**
     * Checks role of user
     *
     * @param int $iRole
     *
     * @return bool
     */
    public function hasRole($iRole) {

        return (bool)$this->getPropMask('user_role', $iRole);
    }

}

// EOF
