<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
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

    public function Init() {
        $this->oMapper = Engine::GetMapper(__CLASS__);
    }

    public function GetSiteStat() {
        $sCacheKey = 'adm_site_stat';
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetSiteStat();
            $this->Cache_Set($data, $sCacheKey, array('user_new', 'blog_new', 'topic_new', 'comment_new'), 60 * 15);
        }
        return $data;
    }

    public function BanUsers($aUsers, $nDays = null, $sComment = null) {
        $aUserIds = $this->_entitiesId($aUsers);
        // для все юзеров, добавляемых в бан, закрываются сессии
        foreach ($aUserIds as $nUserId) {
            if ($nUserId) {
                $this->Session_Drop($nUserId);
                $this->User_CloseAllSessions($nUserId);
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
        $this->Cache_CleanByTags(array('user_update'));
        return $bOk;
    }

    public function UnbanUsers($aUsers) {
        $aUserIds = $this->_entitiesId($aUsers);
        $bOk = $this->oMapper->UnbanUsers($aUserIds);
        $this->Cache_CleanByTags(array('user_update'));
        return $bOk;
    }

    public function GetUsersBanList($nCurrPage, $nPerPage) {
        $sCacheKey = 'adm_banlist_' . $nCurrPage . '_' . $nPerPage;
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $aUsersId = $this->oMapper->GetBannedUsersId($iCount, $nCurrPage, $nPerPage);
            if ($aUsersId) {
                $aUsers = $this->User_GetUsersByArrayId($aUsersId);
                $data = array('collection' => $aUsers, 'count' => $iCount);
            } else {
                $data = array('collection' => array(), 'count' => 0);
            }
            $this->Cache_Set($data, $sCacheKey, array('adm_banlist', 'user_update'), 60 * 15);
        }
        return $data;
    }

    public function GetIpsBanList($iCurrPage, $iPerPage) {
        $sCacheKey = 'adm_banlist_ips_' . $iCurrPage . '_' . $iPerPage;
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = array('collection' => $this->oMapper->GetIpsBanList($iCount, $iCurrPage, $iPerPage), 'count' => $iCount);
            $this->Cache_Set($data, $sCacheKey, array('adm_banlist_ip'), 60 * 15);
        }
        return $data;
    }

    public function SetBanIp($sIp1, $sIp2, $nDays = null, $sComment = null) {
        if (!$nDays) {
            $nUnlim = 1;
            $dDate = null;
        } else {
            $nUnlim = 0;
            $dDate = date('Y-m-d H:i:s', time() + 3600 * 24 * $nDays);
        }

        //чистим зависимые кеши
        $bResult = $this->oMapper->SetBanIp($sIp1, $sIp2, $dDate, $nUnlim, $sComment);
        $this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('adm_banlist_ip'));
        return $bResult;
    }

    public function UnsetBanIp($aIds) {
        if (!is_array($aIds)) $aIds = intval($aIds);
        //чистим зависимые кеши
        $this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('adm_banlist_ip'));
        return $this->oMapper->UnsetBanIp($aIds);
    }


    /**
     * Получить все инвайты
     *
     * @param   integer $nCurrPage
     * @param   integer $nPerPage
     * @return  array
     */
    public function GetInvites($nCurrPage, $nPerPage) {
        // Инвайты не кешируются, поэтому работаем напрямую с БД
        $data = array('collection' => $this->oMapper->GetInvites($iCount, $nCurrPage, $nPerPage), 'count' => $iCount);
        return $data;
    }

    public function DeleteInvites($aIds) {
        return $this->oMapper->DeleteInvites($aIds);
    }

    /**
     * Сохранение пользовательской конфигурации в базе
     *
     * @param   array $aConfig
     * @return  bool
     */
    public function UpdateCustomConfig($aConfig) {
        $bResult = $this->oMapper->UpdateCustomConfig($aConfig);
        $this->Cache_CleanByTags(array('config_update'));
        return $bResult;
    }

    /**
     * Читает пользовательскую конфигурацию из базы
     *
     * @param   string  $sKeyPrefix
     * @return  array
     */
    public function GetCustomConfig($sKeyPrefix = null) {
        $sCacheKey = 'config_' . $sKeyPrefix;
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetCustomConfig($sKeyPrefix);
            $this->Cache_Set($data, $sCacheKey, array('config_update'), 'P1M');
        }
        return $data;
    }

    /**
     * Удаляет кеш кастмной конфигурации
     */
    public function DelCustomCfg() {
        if ($sFile = $this->_customCfgFile()) {
            F::File_Delete($sFile);
        }
    }

    public function GetUnlinkedBlogsForUsers() {
        return $this->oMapper->GetUnlinkedBlogsForUsers();
    }

    public function DelUnlinkedBlogsForUsers($aBlogIds) {
        $bResult = $this->oMapper->DelUnlinkedBlogsForUsers($aBlogIds);
        $this->Cache_Clean();
        return $bResult;
    }

    public function GetUnlinkedBlogsForCommentsOnline() {
        return $this->oMapper->GetUnlinkedBlogsForCommentsOnline();
    }

    public function DelUnlinkedBlogsForCommentsOnline($aBlogIds) {
        $bResult = $this->oMapper->DelUnlinkedBlogsForCommentsOnline($aBlogIds);
        $this->Cache_Clean();
        return $bResult;
    }

    public function GetUnlinkedTopicsForCommentsOnline() {
        return $this->oMapper->GetUnlinkedTopicsForCommentsOnline();
    }

    public function DelUnlinkedTopicsForCommentsOnline($aTopicIds) {
        $bResult = $this->oMapper->DelUnlinkedTopicsForCommentsOnline($aTopicIds);
        $this->Cache_Clean();
        return $bResult;
    }

    public function SetAdministrator($nUserId) {
        $bOk = $this->oMapper->SetAdministrator($nUserId);
        if ($bOk) {
            $oUser = $this->User_GetUserById($nUserId);
            if ($oUser) $this->User_Update($oUser);
        }
        return $bOk;
    }

    public function UnsetAdministrator($nUserId) {
        $bOk = $this->oMapper->UnsetAdministrator($nUserId);
        if ($bOk) {
            $oUser = $this->User_GetUserById($nUserId);
            if ($oUser) $this->User_Update($oUser);
        }
        return $bOk;
    }

}

// EOF