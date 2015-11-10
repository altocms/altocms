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
 * Сущность блога
 *
 * @method setOldType($sParam)
 *
 * @package modules.blog
 * @since   1.0
 */
class ModuleBlog_EntityBlog extends Entity {
    
    const DEFAULT_AVATAR_SIZE = 48;

    // Типы ресурсов, загружаемые в профайле пользователя
    protected $aMResourceTypes = array('blog_avatar');

    /**
     * Возвращает ID блога
     *
     * @return int|null
     */
    public function getId() {

        return $this->getProp('blog_id');
    }

    /**
     * Возвращает ID владельца блога
     *
     * @return int|null
     */
    public function getOwnerId() {

        return $this->getProp('user_owner_id');
    }

    /**
     * Возвращает название блога
     *
     * @return string|null
     */
    public function getTitle() {

        return $this->getProp('blog_title');
    }

    /**
     * Возвращает описание блога
     *
     * @return string|null
     */
    public function getDescription() {

        return $this->getProp('blog_description');
    }

    /**
     * Возвращает тип блога
     *
     * @return string|null
     */
    public function getType() {

        return $this->getProp('blog_type');
    }

    /**
     * Возвращает сущность типа блога
     *
     * @return ModuleBlog_EntityBlogType|null
     */
    public function getBlogType() {

        $oBlogType = $this->getProp('blog_type_obj');
        if (!$oBlogType && ($sType = $this->getType())) {
            $oBlogType = E::ModuleBlog()->GetBlogTypeByCode($sType);
        }
        return $oBlogType;
    }

    /**
     * Возвращает дату создания блога
     *
     * @return string|null
     */
    public function getDateAdd() {

        return $this->getProp('blog_date_add');
    }

    /**
     * Возвращает дату редактирования блога
     *
     * @return string|null
     */
    public function getDateEdit() {

        return $this->getProp('blog_date_edit');
    }

    /**
     * Возвращает рейтинг блога
     *
     * @return string
     */
    public function getRating() {

        return number_format(round($this->getProp('blog_rating'), 2), 2, '.', '');
    }

    /**
     * Возврщает количество проголосовавших за блог
     *
     * @return int|null
     */
    public function getCountVote() {

        return $this->getProp('blog_count_vote');
    }

    /**
     * Возвращает количество пользователей в блоге
     *
     * @return int|null
     */
    public function getCountUser() {

        return $this->getProp('blog_count_user');
    }

    /**
     * Возвращает количество топиков в блоге
     *
     * @return int|null
     */
    public function getCountTopic() {

        return $this->getProp('blog_count_topic');
    }

    /**
     * Возвращает ограничение по рейтингу для постинга в блог
     *
     * @return int|null
     */
    public function getLimitRatingTopic() {

        return $this->getProp('blog_limit_rating_topic');
    }

    /**
     * Возвращает URL блога
     *
     * @return string|null
     */
    public function getUrl() {

        $sUrl = $this->getProp('blog_url');
        if (!$sUrl && $this->getType() == 'personal') {
            $sUrl = F::TranslitUrl($this->getOwner()->getLogin());
            if (!$sUrl) {
                $sUrl = 'user-' . $this->getOwnerId();
            }
        }
        return $sUrl;
    }

    /**
     * Возвращает полный серверный путь до аватара блога
     *
     * @return string|null
     */
    public function getAvatar() {

//        return $this->getProp('blog_avatar');

        // Если объект ещё не создан, то через него нельзя получить аватар,
        // работа с временными изображениями только через модуль Mresource.
        if (!$this->getId()) {
            return null;
        }

        return E::ModuleUploader()->GetTargetImageUrl('blog_avatar', $this->getId());
    }

    /**
     * Возвращает расширения аватра блога
     *
     * @return string|null
     */
    public function getAvatarType() {

        return ($sPath = $this->getAvatarUrl()) ? pathinfo($sPath, PATHINFO_EXTENSION) : null;
    }


    /**
     * Возвращает объект пользователя владельца блога
     *
     * @return ModuleUser_EntityUser|null
     */
    public function getOwner() {

        return $this->getProp('owner');
    }

    /**
     * Возвращает объект голосования за блог
     *
     * @return ModuleVote_EntityVote|null
     */
    public function getVote() {

        return $this->getProp('vote');
    }

    /**
     * @param string $sType
     *
     * @return ModuleMresource_EntityMresourceRel[]
     */
    public function getProfileMedia($sType) {

        $aMedia = $this->getProp('_media');
        if (is_null($aMedia)) {
            // Если медиаресурсы профайла не загружены, то загружаем, включая требуемый тип
            $aTargetTypes = $this->aMResourceTypes;
            if (!in_array($sType, $aTargetTypes)) {
                $aTargetTypes[] = $sType;
            }
            $aImages = E::ModuleUploader()->GetImagesByUserAndTarget($this->getId(), $aTargetTypes);
            $aMedia = array_fill_keys($aTargetTypes, array());
            if (!empty($aImages[$this->getId()])) {
                /** @var ModuleMresource_EntityMresourceRel $oImage */
                foreach($aImages[$this->getId()] as $oImage) {
                    $aMedia[$oImage->getTargetType()][$oImage->getId()] = $oImage;
                }
            }
            $this->setProp('_media', $aMedia);
        } elseif (!array_key_exists($sType, $aMedia)) {
            $aImages = E::ModuleUploader()->GetImagesByUserAndTarget($this->getId(), $sType);
            if (!empty($aImages[$this->getId()])) {
                $aMedia[$sType] = $aImages[$this->getId()];
            } else {
                $aMedia[$sType] = array();
            }
            $this->setProp('_media', $aMedia);
        }
        return $aMedia[$sType];
    }

    /**
     * @param string $sType
     * @param string $xSize
     *
     * @return string
     */
    public function getImageUrl($sType, $xSize = null) {

        $sUrl = '';
        $aImages = $this->getProfileMedia($sType);
        if (!empty($aImages)) {
            /** @var ModuleMresource_EntityMresourceRel $oImage */
            $oImage = reset($aImages);
            $sUrl = $oImage->getImageUrl($xSize);
        }
        return $sUrl;
    }

    /**
     * Возвращает полный серверный путь до аватара блога определенного размера
     *
     * @param int $xSize    Размер аватара
     *
     * @return string
     */
    public function getAvatarUrl($xSize = 48) {

        if (!$xSize) {
            if (Config::Get('module.user.profile_avatar_size')) {
                $xSize = Config::Get('module.user.profile_avatar_size');
            } else {
                $xSize = self::DEFAULT_AVATAR_SIZE;
            }
        }

        $sPropKey = '_avatar_url_' . $xSize;
        $sUrl = $this->getProp($sPropKey);
        if (is_null($sUrl)) {
            if ($sRealSize = C::Get('module.uploader.images.profile_avatar.size.' . $xSize)) {
                $xSize = $sRealSize;
            }
            $aImages = $this->getMediaResources('blog_avatar');
            if (!empty($aImages)) {

                /** @var ModuleMresource_EntityMresourceRel $oImage */
                $oImage = reset($aImages);
                $sUrl = $oImage->getImageUrl($xSize);
            } else {
                $sUrl = null;
            }
            if (!$sUrl) {
                // Old version compatibility
                $sUrl = $this->getProp('blog_avatar');
                if ($sUrl) {
                    if ($xSize) {
                        $sUrl = E::ModuleUploader()->ResizeTargetImage($sUrl, $xSize);
                    }
                } else {
                    $sUrl = $this->getDefaultAvatarUrl($xSize);
                }
            }
            $this->setProp($sPropKey, $sUrl);
        }
        return $sUrl;
    }

    /**
     * Returns default avatar of the blog
     *
     * @param int|string $xSize
     *
     * @return string
     */
    public function getDefaultAvatarUrl($xSize = null) {

        if (!$xSize) {
            if (Config::Get('module.user.profile_avatar_size')) {
                $xSize = Config::Get('module.user.profile_avatar_size');
            } else {
                $xSize = self::DEFAULT_AVATAR_SIZE;
            }
        }

        $sPath = E::ModuleUploader()->GetUserAvatarDir(0) . 'avatar_blog_' . Config::Get('view.skin', Config::LEVEL_CUSTOM) . '.png';
        $sResizePath = null;
        if ($xSize) {
            if ($sRealSize = C::Get('module.uploader.images.profile_avatar.size.' . $xSize)) {
                $xSize = $sRealSize;
            }
            if (is_string($xSize) && $xSize[0] == 'x') {
                $xSize = substr($xSize, 1);
            }
            if ($iSize = intval($xSize)) {
                $sResizePath = $sPath . '-' . $iSize . 'x' . $iSize . '.' . strtolower(pathinfo($sPath, PATHINFO_EXTENSION));
                if (Config::Get('module.image.autoresize') && !F::File_Exists($sResizePath)) {
                    $sResizePath = E::ModuleImg()->AutoresizeSkinImage($sResizePath, 'avatar_blog', $iSize ? $iSize : null);
                }
            }
        }
        if ($sResizePath) {
            $sPath = $sResizePath;
        } elseif (!F::File_Exists($sPath)) {
            $sPath = E::ModuleImg()->AutoresizeSkinImage($sPath, 'avatar_blog', null);
        }

        return E::ModuleUploader()->Dir2Url($sPath);
    }

    /**
     * @deprecated LS-compatibility
     *
     * @param int $nSize
     *
     * @return string
     */
    public function getAvatarPath($nSize = 48) {

        return $this->getAvatarUrl($nSize);
    }

    /**
     * Возвращает факт присоединения пользователя к блогу
     *
     * @return bool|null
     */
    public function getUserIsJoin() {

        return $this->getUserIsSubscriber();
    }

    public function getCurrentUserRole() {

        return intval($this->getProp('_user_role'));
    }

    /**
     * If the current user is an subscriber of the blog
     *
     * @return bool|null
     */
    public function getUserIsSubscriber() {

        $bResult = $this->getProp('_user_is_subscriber');
        if (is_null($bResult)) {
            $iRole = $this->getCurrentUserRole();
            if ($iRole && $iRole > ModuleBlog::BLOG_USER_ROLE_GUEST) {
                $bResult = $iRole & ModuleBlog::BLOG_USER_ROLE_SUBSCRIBER;
            }
            $this->setProp('_user_is_subscriber', $bResult);
        }

        return $bResult;
    }

    /**
     * Проверяет является ли текущий пользователь администратором блога
     *
     * @return bool|null
     */
    public function getUserIsAdministrator() {

        $bResult = $this->getProp('_user_is_administrator');
        if (is_null($bResult)) {
            $iRole = $this->getCurrentUserRole();
            if ($iRole && $iRole > ModuleBlog::BLOG_USER_ROLE_GUEST) {
                $bResult = $iRole & ModuleBlog::BLOG_USER_ROLE_ADMINISTRATOR;
            }
            $this->setProp('_user_is_administrator', $bResult);
        }

        return $bResult;
    }

    /**
     * Проверяет является ли текущий пользователь модератором блога
     *
     * @return bool|null
     */
    public function getUserIsModerator() {

        $bResult = $this->getProp('_user_is_moderator');
        if (is_null($bResult)) {
            $iRole = $this->getCurrentUserRole();
            if ($iRole && $iRole > ModuleBlog::BLOG_USER_ROLE_GUEST) {
                $bResult = $iRole & ModuleBlog::BLOG_USER_ROLE_MODERATOR;
            }
            $this->setProp('_user_is_moderator', $bResult);
        }

        return $bResult;
    }

    /**
     * Returns link to the blog
     *
     * @return string
     */
    public function getLink() {

        if ($this->getType() == 'personal') {
            return $this->getOwner()->getProfileUrl() . 'created/topics/';
        } else {
            return R::GetPath('blog/' . $this->getUrl());
        }
    }

    /**
     * Alias of getLink()
     *
     * @return string
     */
    public function getUrlFull() {

        return $this->getLink();
    }

    /**
     * Устанавливает ID блога
     *
     * @param int $data
     */
    public function setId($data) {

        $this->setProp('blog_id', $data);
    }

    /**
     * Устанавливает ID владельца блога
     *
     * @param int $data
     */
    public function setOwnerId($data) {

        $this->setProp('user_owner_id', $data);
    }

    /**
     * Устанавливает заголовок блога
     *
     * @param string $data
     */
    public function setTitle($data) {

        $this->setProp('blog_title', $data);
    }

    /**
     * Устанавливает описание блога
     *
     * @param string $data
     */
    public function setDescription($data) {

        $this->setProp('blog_description', $data);
    }

    /**
     * Устанавливает тип блога
     *
     * @param string $data
     */
    public function setType($data) {

        $this->setProp('blog_type', $data);
    }

    /**
     * Устанавливает сущность типа блога
     *
     * @param ModuleBlog_EntityBlogType $data
     */
    public function setBlogType($data) {

        $this->setProp('blog_type_obj', $data);
        if (is_object($data) && $data instanceof ModuleBlog_EntityBlogType && $data->getTypeCode()) {
            $this->setType($data->getTypeCode());
        }
    }

    /**
     * Устанавливает дату создания блога
     *
     * @param string $data
     */
    public function setDateAdd($data) {

        $this->setProp('blog_date_add', $data);
    }

    /**
     * Устанавливает дату редактирования топика
     *
     * @param string $data
     */
    public function setDateEdit($data) {

        $this->setProp('blog_date_edit', $data);
    }

    /**
     * Устанавливает рейтинг блога
     *
     * @param float $data
     */
    public function setRating($data) {

        $this->setProp('blog_rating', $data);
    }

    /**
     * Устаналивает количество проголосовавших
     *
     * @param int $data
     */
    public function setCountVote($data) {

        $this->setProp('blog_count_vote', $data);
    }

    /**
     * Устанавливает количество пользователей блога
     *
     * @param int $data
     */
    public function setCountUser($data) {

        $this->setProp('blog_count_user', $data);
    }

    /**
     * Устанавливает количество топиков в блоге
     *
     * @param int $data
     */
    public function setCountTopic($data) {

        $this->setProp('blog_count_topic', $data);
    }

    /**
     * Устанавливает ограничение на постинг в блог
     *
     * @param float $data
     */
    public function setLimitRatingTopic($data) {

        $this->setProp('blog_limit_rating_topic', $data);
    }

    /**
     * Устанавливает URL блога
     *
     * @param string $data
     */
    public function setUrl($data) {

        $this->setProp('blog_url', $data);
    }

    /**
     * @param string                               $sType
     * @param ModuleMresource_EntityMresourceRel[] $data
     */
    public function setBlogMedia($sType, $data) {

        if (!is_array($data)) {
            $data = array($data);
        }
        $aMedia = $this->getProp('_media');
        if (is_null($aMedia)) {
            $aMedia = array();
        }
        $aMedia[$sType] = $data;
        $this->setProp('_media', $aMedia);
    }

    /**
     * Устанавливает полный серверный путь до аватара блога
     *
     * @param string $data
     */
    public function setAvatar($data) {

        $this->setProp('blog_avatar', $data);
    }

    /**
     * Устанавливает владельца блога
     *
     * @param ModuleUser_EntityUser $data
     */
    public function setOwner($data) {

        $this->setProp('owner', $data);
    }

    public function setCurrentUserRole($data) {

        $this->setProp('_user_role', $data);
    }

    /**
     * Устанавливает статус администратора блога для текущего пользователя
     *
     * @param bool $data
     */
    public function setUserIsAdministrator($data) {

        $this->setProp('_user_is_administrator', $data);
    }

    /**
     * Устанавливает статус модератора блога для текущего пользователя
     *
     * @param bool $data
     */
    public function setUserIsModerator($data) {

        $this->setProp('_user_is_moderator', $data);
    }

    /**
     * Устаналивает статус присоединения пользователя к блогу
     *
     * @param bool $data
     */
    public function setUserIsJoin($data) {

        $this->setUserIsSubscriber($data);
    }

    /**
     * Set current user as subscriber of the blog
     *
     * @param bool $data
     */
    public function setUserIsSubscriber($data) {

        $this->setProp('_user_is_subscriber', (bool)$data);
    }

    /**
     * Устанавливает объект голосования за блог
     *
     * @param ModuleVote_EntityVote $data
     */
    public function setVote($data) {

        $this->setProp('vote', $data);
    }

    /* *** Properties of blog type *** */
    public function IsPrivate() {

        $oBlogType = $this->getBlogType();
        if ($oBlogType) {
            return $oBlogType->IsPrivate();
        }
        return null;
    }

    public function IsReadOnly() {

        $oBlogType = $this->getBlogType();
        if ($oBlogType) {
            return $oBlogType->IsReadOnly();
        }
        return null;
    }

    public function IsHidden() {

        $oBlogType = $this->getBlogType();
        if ($oBlogType) {
            return $oBlogType->IsHidden();
        }
        return null;
    }

    /**
     * Checks if allows requires content type in this blog
     *
     * @param $xContentType
     *
     * @return bool
     */
    public function IsContentTypeAllow($xContentType) {

        if (!$xContentType) {
            return true;
        }

        if ($this->getBlogType()) {
            return $this->getBlogType()->IsContentTypeAllow($xContentType);
        }
        return false;
    }

    /**
     * Can this blog be edited by the user?
     *
     * @param ModuleUser_EntityUser $oUser
     *
     * @return bool
     */
    public function CanEditedBy($oUser) {

        return $oUser && E::ModuleACL()->IsAllowEditBlog($this, $oUser);
    }

    /**
     * Can this blog be administarted by the user?
     *
     * @param ModuleUser_EntityUser $oUser
     *
     * @return bool
     */
    public function CanAdminBy($oUser) {

        return $oUser && E::ModuleACL()->IsAllowAdminBlog($this, $oUser);
    }

    /**
     * Can this blog be deleted by the user?
     *
     * @param ModuleUser_EntityUser $oUser
     *
     * @return bool
     */
    public function CanDeletedBy($oUser) {

        return $oUser && E::ModuleACL()->IsAllowDeleteBlog($this, $oUser);
    }

    /**
     * Can this blog be read by the user?
     *
     * @param ModuleUser_EntityUser $oUser
     *
     * @return bool
     */
    public function CanReadBy($oUser) {

        if ($this->GetBlogType() && $this->GetBlogType()->GetAclRead(ModuleBlog::BLOG_USER_ACL_GUEST)) {
            // anybody can read blog
            return true;
        }
        return $oUser && E::ModuleACL()->IsAllowShowBlog($this, $oUser);
    }

    /**
     * Creates RSS channel for the blog (without items)
     *
     * @return ModuleRss_EntityRssChannel
     */
    public function CreateRssChannel() {

        $aRssChannelData = array(
            'title' => C::Get('view.name') . '/' . $this->getTitle(),
            'description' => $this->getDescription(),
            'link' => $this->getLink(),
            'language' => C::Get('lang.current'),
            'managing_editor' => $this->getOwner() ? $this->getOwner()->getMail() : '',
            'web_master' => C::Get('general.rss_editor_mail'),
            'generator' => 'Alto CMS v.' . ALTO_VERSION,
        );

        $oRssChannel = E::GetEntity('ModuleRss_EntityRssChannel', $aRssChannelData);

        return $oRssChannel;
    }

}

// EOF