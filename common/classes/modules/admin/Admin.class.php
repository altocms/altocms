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
 * Модуль для работы с админпанелью
 *
 * @package modules.blog
 * @since 1.0
 */
class ModuleAdmin extends Module {
    /** @var ModuleAdmin_MapperAdmin */
    protected $oMapper;

    /**
     * Initialization
     */
    public function Init() {

        $this->oMapper = E::GetMapper(__CLASS__);
    }

    /**
     * Grt stats of the site
     *
     * @return array
     */
    public function GetSiteStat() {

        $sCacheKey = 'adm_site_stat';
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetSiteStat();
            E::ModuleCache()->Set($data, $sCacheKey, array('user_new', 'blog_new', 'topic_new', 'comment_new'), 60 * 15);
        }
        return $data;
    }

    /**
     * @param array  $aUsers
     * @param int    $nDays
     * @param string $sComment
     *
     * @return bool
     */
    public function BanUsers($aUsers, $nDays = null, $sComment = null) {

        $aUserIds = $this->_entitiesId($aUsers);
        $bOk = true;
        if ($aUserIds) {
            // для все юзеров, добавляемых в бан, закрываются сессии
            foreach ($aUserIds as $nUserId) {
                if ($nUserId) {
                    E::ModuleSession()->Drop($nUserId);
                    E::ModuleUser()->CloseAllSessions($nUserId);
                }
            }
            if (!$nDays) {
                $nUnlim = 1;
                $dDate = null;
            } else {
                $nUnlim = 0;
                $dDate = date('Y-m-d H:i:s', time() + 3600 * 24 * $nDays);
            }
            $bOk = $this->oMapper->BanUsers($aUserIds, $dDate, $nUnlim, $sComment);
            E::ModuleCache()->CleanByTags(array('user_update'));
        }
        return $bOk;
    }

    /**
     * @param array $aUsers
     *
     * @return bool
     */
    public function UnbanUsers($aUsers) {

        $aUserIds = $this->_entitiesId($aUsers);
        $bOk = true;
        if ($aUserIds) {
            $bOk = $this->oMapper->UnbanUsers($aUserIds);
            E::ModuleCache()->CleanByTags(array('user_update'));
        }
        return $bOk;
    }

    /**
     * @param int $iCurrPage
     * @param int $iPerPage
     *
     * @return array
     */
    public function GetUsersBanList($iCurrPage, $iPerPage) {

        $sCacheKey = 'adm_banlist_' . $iCurrPage . '_' . $iPerPage;
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $aUsersId = $this->oMapper->GetBannedUsersId($iCount, $iCurrPage, $iPerPage);
            if ($aUsersId) {
                $aUsers = E::ModuleUser()->GetUsersByArrayId($aUsersId);
                $data = array('collection' => $aUsers, 'count' => $iCount);
            } else {
                $data = array('collection' => array(), 'count' => 0);
            }
            E::ModuleCache()->Set($data, $sCacheKey, array('adm_banlist', 'user_update'), 60 * 15);
        }
        return $data;
    }

    /**
     * @param int $iCurrPage
     * @param int $iPerPage
     *
     * @return array
     */
    public function GetIpsBanList($iCurrPage, $iPerPage) {

        $sCacheKey = 'adm_banlist_ips_' . $iCurrPage . '_' . $iPerPage;
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = array('collection' => $this->oMapper->GetIpsBanList($iCount, $iCurrPage, $iPerPage), 'count' => $iCount);
            E::ModuleCache()->Set($data, $sCacheKey, array('adm_banlist_ip'), 60 * 15);
        }
        return $data;
    }

    /**
     * Бан диапазона IP-адресов
     *
     * @param string $sIp1
     * @param string $sIp2
     * @param int    $nDays
     * @param string $sComment
     *
     * @return bool
     */
    public function SetBanIp($sIp1, $sIp2, $nDays = null, $sComment = null) {

        $nDays = ($nDays ? intval($nDays) : null);
        if ($nDays) {
            $nUnlim = 0;
            $dDate = date('Y-m-d H:i:s', time() + 3600 * 24 * $nDays);
        } else {
            $nUnlim = 1;
            $dDate = null;
        }

        //чистим зависимые кеши
        $bResult = $this->oMapper->SetBanIp($sIp1, $sIp2, $dDate, $nUnlim, $sComment);
        E::ModuleCache()->CleanByTags(array('adm_banlist_ip'));
        return $bResult;
    }

    /**
     * Снятие бана с диапазона IP-адресов
     *
     * @param array $aIds
     *
     * @return bool
     */
    public function UnsetBanIp($aIds) {

        if (!is_array($aIds)) $aIds = intval($aIds);
        $bResult = $this->oMapper->UnsetBanIp($aIds);
        //чистим зависимые кеши
        E::ModuleCache()->CleanByTags(array('adm_banlist_ip'));
        return $bResult;
    }


    /**
     * Получить все инвайты
     *
     * @param integer $nCurrPage
     * @param integer $nPerPage
     * @param array   $aFilter
     *
     * @return array
     */
    public function GetInvites($nCurrPage, $nPerPage, $aFilter = array()) {

        // Инвайты не кешируются, поэтому работаем напрямую с БД
        $aResult = array('collection' => $this->oMapper->GetInvites($iCount, $nCurrPage, $nPerPage, $aFilter), 'count' => $iCount);

        return $aResult;
    }

    /**
     * @return array
     */
    public function GetInvitesCount() {

        return $this->oMapper->GetInvitesCount();
    }

    /**
     * Удаляет инвайты по списку ID
     *
     * @param array $aIds
     *
     * @return mixed
     */
    public function DeleteInvites($aIds) {

        return $this->oMapper->DeleteInvites($aIds);
    }

    /**
     * Update config data in database
     *
     * @param   array $aConfig
     *
     * @return  bool
     */
    public function UpdateStorageConfig($aConfig) {

        $bResult = $this->oMapper->UpdateStorageConfig($aConfig);
        E::ModuleCache()->CleanByTags(array('config_update'));

        return $bResult;
    }

    /**
     * Read config data by prefix from database
     *
     * @param   string   $sKeyPrefix
     *
     * @return  array
     */
    public function GetStorageConfig($sKeyPrefix = null) {

        $sCacheKey = 'config_' . $sKeyPrefix;
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetStorageConfig($sKeyPrefix);
            E::ModuleCache()->Set($data, $sCacheKey, array('config_update'), 'P1M');
        }
        return $data;
    }

    /**
     * Delete config data by prefix from database
     *
     * @param   string  $sKeyPrefix
     *
     * @return  bool
     */
    public function DeleteStorageConfig($sKeyPrefix = null) {

        // Удаляем в базе
        $bResult = $this->oMapper->DeleteStorageConfig($sKeyPrefix);
        // Чистим кеш
        E::ModuleCache()->CleanByTags(array('config_update'));

        return $bResult;
    }

    /**
     * @return array
     */
    public function GetUnlinkedBlogsForUsers() {

        return $this->oMapper->GetUnlinkedBlogsForUsers();
    }

    /**
     * @param array $aBlogIds
     *
     * @return mixed
     */
    public function DelUnlinkedBlogsForUsers($aBlogIds) {

        $bResult = $this->oMapper->DelUnlinkedBlogsForUsers($aBlogIds);
        E::ModuleCache()->Clean();
        return $bResult;
    }

    /**
     * @return array
     */
    public function GetUnlinkedBlogsForCommentsOnline() {

        return $this->oMapper->GetUnlinkedBlogsForCommentsOnline();
    }

    /**
     * @param array $aBlogIds
     *
     * @return mixed
     */
    public function DelUnlinkedBlogsForCommentsOnline($aBlogIds) {

        $bResult = $this->oMapper->DelUnlinkedBlogsForCommentsOnline($aBlogIds);
        E::ModuleCache()->Clean();
        return $bResult;
    }

    /**
     * @return array
     */
    public function GetUnlinkedTopicsForCommentsOnline() {

        return $this->oMapper->GetUnlinkedTopicsForCommentsOnline();
    }

    /**
     * @param array $aTopicIds
     *
     * @return mixed
     */
    public function DelUnlinkedTopicsForCommentsOnline($aTopicIds) {

        $bResult = $this->oMapper->DelUnlinkedTopicsForCommentsOnline($aTopicIds);
        E::ModuleCache()->Clean();
        return $bResult;
    }

    /**
     * @param int $nUserId
     *
     * @return bool
     */
    public function SetAdministrator($nUserId) {

        /** @var ModuleUser_EntityUser $oUser */
        $oUser = E::ModuleUser()->GetUserById($nUserId);
        $bOk = false;
        if ($oUser && $oUser->getRole() != ($oUser->getRole() | ModuleUser::USER_ROLE_ADMINISTRATOR)) {
            $bOk = $this->oMapper->UpdateRole($oUser, $oUser->getRole() | ModuleUser::USER_ROLE_ADMINISTRATOR);
        }

        return $bOk;

    }

    /**
     * @param int $nUserId
     *
     * @return bool
     */
    public function UnsetAdministrator($nUserId) {

        /** @var ModuleUser_EntityUser $oUser */
        $oUser = E::ModuleUser()->GetUserById($nUserId);
        if ($oUser) {
            return $this->oMapper->UpdateRole($oUser, $oUser->getRole() ^ ModuleUser::USER_ROLE_ADMINISTRATOR);
        }
        return false;

    }

    /**
     * @param int $nUserId
     *
     * @return bool
     */
    public function SetModerator($nUserId) {

        /** @var ModuleUser_EntityUser $oUser */
        $oUser = E::ModuleUser()->GetUserById($nUserId);
        $bOk = false;
        if ($oUser && $oUser->getRole() != ($oUser->getRole() | ModuleUser::USER_ROLE_MODERATOR)) {
            $bOk = $this->oMapper->UpdateRole($oUser, $oUser->getRole() | ModuleUser::USER_ROLE_MODERATOR);
        }

        return $bOk;

    }

    /**
     * @param int $nUserId
     *
     * @return bool
     */
    public function UnsetModerator($nUserId) {

        /** @var ModuleUser_EntityUser $oUser */
        $oUser = E::ModuleUser()->GetUserById($nUserId);
        if ($oUser) {
            return $this->oMapper->UpdateRole($oUser, $oUser->getRole() ^ ModuleUser::USER_ROLE_MODERATOR);
        }
        return false;

    }

    /**
     * Число топиков без URL
     */
    public function GetNumTopicsWithoutUrl() {

        return $this->oMapper->GetNumTopicsWithoutUrl();
    }

    /**
     * Генерация URL топиков. Процесс может быть долгим, поэтому стараемся предотвратить ошибку по таймауту
     */
    public function GenerateTopicsUrl() {
        $nRecLimit = 500;

        $nTimeLimit = F::ToSeconds(ini_get('max_execution_time')) * 0.8 - 5 + time();

        $nResult = -1;
        while (true) {
            $aData = $this->oMapper->GetTitleTopicsWithoutUrl($nRecLimit);
            if (!$aData) {
                $nResult = 0;
                break;
            }
            foreach ($aData as $nTopicId=>$sTopicTitle) {
                $aData[$nTopicId]['topic_url'] = substr(F::TranslitUrl($aData[$nTopicId]['topic_title']), 0, 240);
            }
            if (!$this->oMapper->SaveTopicsUrl($aData)) {
                return -1;
            }

            // если время на исходе, то завершаем
            if (time() >= $nTimeLimit) {
                break;
            }
        }
        if ($nResult == 0) {
            // нужно ли проверять ссылки на дубликаты
            $iOnDuplicateUrl = Config::Val('module.topic.on_duplicate_url', 1);
            if ($iOnDuplicateUrl) {
                $this->CheckDuplicateTopicsUrl();
            }
        } else {
            $nResult = $this->GetNumTopicsWithoutUrl();
        }
        return $nResult;
    }

    /**
     * Контроль дублей URL топиков и исправление, если нужно
     *
     * @return bool
     */
    public function CheckDuplicateTopicsUrl() {

        $aData = $this->oMapper->GetDuplicateTopicsUrl();
        if ($aData) {
            $aUrls = array();
            foreach ($aData as $aRec) {
                $aUrls[] = $aRec['topic_url'];
            }
            $aData = $this->oMapper->GetTopicsDataByUrl($aUrls);
        }
        $aUrls = array();
        $aUpdateData = array();
        foreach ($aData as $nKey => $aRec) {
            if (!isset($aUrls[$aRec['topic_url']])) {
                $aUrls[$aRec['topic_url']] = 1;
                unset($aData[$nKey]);
            } else {
                $aUpdateData[$aRec['topic_id']]['topic_url'] = $aRec['topic_url'] . '-' . (++$aUrls[$aRec['topic_url']]);
            }
        }
        if ($aUpdateData) {
            return $this->oMapper->SaveTopicsUrl($aUpdateData);
        }
        return true;
    }

    /**
     * @param   int|object $oUserId
     *
     * @return  bool
     */
    public function DelUser($oUserId) {

        if (is_object($oUserId)) {
            $nUserId = $oUserId->getId();
        } else {
            $nUserId = intval($oUserId);
        }

        // Удаляем блоги
        $aBlogsId = E::ModuleBlog()->GetBlogsByOwnerId($nUserId, true);
        if ($aBlogsId) {
            E::ModuleBlog()->DeleteBlog($aBlogsId);
        }
        $oBlog = E::ModuleBlog()->GetPersonalBlogByUserId($nUserId);
        if ($oBlog) {
            E::ModuleBlog()->DeleteBlog($oBlog->getId());
        }

        // Удаляем переписку
        $iPerPage = 10000;
        do {
            $aTalks = E::ModuleTalk()->GetTalksByFilter(array('user_id' => $nUserId), 1, $iPerPage);
            if ($aTalks['count']) {
                $aTalksId = array();
                foreach ($aTalks['collection'] as $oTalk) {
                    $aTalksId[] = $oTalk->getId();
                }
                if ($aTalksId) {
                    E::ModuleTalk()->DeleteTalkUserByArray($aTalksId, $nUserId);
                }
            }
        } while ($aTalks['count'] > $iPerPage);

        $bOk = $this->oMapper->DelUser($nUserId);

        // Слишком много взаимосвязей, поэтому просто сбрасываем кеш
        E::ModuleCache()->Clean();

        return $bOk;
    }

    /**
     * @param bool $bActive
     *
     * @return array
     */
    public function GetScriptsList($bActive = null) {

        $aResult = array();
        $aScripts = (array)C::Get('script');
        if ($aScripts) {
            if (is_null($bActive)) {
                return $aScripts;
            }
            foreach($aScripts as $sScriptName => $aScript) {
                if ($bActive) {
                    if (!isset($aScript['disable']) && !$aScript['disable']) {
                        $aResult[$sScriptName] = $aScript;
                    }
                } else {
                    if (isset($aScript['disable']) && $aScript['disable']) {
                        $aResult[$sScriptName] = $aScript;
                    }
                }
            }
        }
        return $aResult;
    }

    public function GetScriptById($sScriptId) {

        $aScript = C::Get('script.' . $sScriptId);
        return $aScript;
    }

    public function SaveScript($aScript) {

        $sConfigKey = 'script.' . $aScript['id'];
        Config::WriteCustomConfig(array($sConfigKey => $aScript));
    }

    public function DeleteScript($xScript) {

        if (is_array($xScript)) {
            $sScriptId = $xScript['id'];
        } else {
            $sScriptId = (string)$xScript;
        }
        $sConfigKey = 'script.' . $sScriptId;
        Config::ResetCustomConfig($sConfigKey);
    }
}

// EOF