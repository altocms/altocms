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
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetSiteStat();
            $this->Cache_Set($data, $sCacheKey, array('user_new', 'blog_new', 'topic_new', 'comment_new'), 60 * 15);
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
            $this->Cache_CleanByTags(array('user_update'));
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
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $aUsersId = $this->oMapper->GetBannedUsersId($iCount, $iCurrPage, $iPerPage);
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

    /**
     * @param int $iCurrPage
     * @param int $iPerPage
     *
     * @return array
     */
    public function GetIpsBanList($iCurrPage, $iPerPage) {

        $sCacheKey = 'adm_banlist_ips_' . $iCurrPage . '_' . $iPerPage;
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = array('collection' => $this->oMapper->GetIpsBanList($iCount, $iCurrPage, $iPerPage), 'count' => $iCount);
            $this->Cache_Set($data, $sCacheKey, array('adm_banlist_ip'), 60 * 15);
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
        $this->Cache_CleanByTags(array('adm_banlist_ip'));
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
        $this->Cache_CleanByTags(array('adm_banlist_ip'));
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
     * Удаляет пользовательскую конфигурацию из базы
     *
     * @param   string  $sKeyPrefix
     *
     * @return  bool
     */
    public function DelCustomConfig($sKeyPrefix = null) {

        $sCacheKey = 'config_' . $sKeyPrefix;
        // Удаляем в базе
        $bResult = $this->oMapper->DeleteCustomConfig($sKeyPrefix);
        // Чистим кеш
        $this->Cache_CleanByTags(array($sCacheKey));
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
        $this->Cache_Clean();
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
        $this->Cache_Clean();
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
        $this->Cache_Clean();
        return $bResult;
    }

    /**
     * @param int $nUserId
     *
     * @return bool
     */
    public function SetAdministrator($nUserId) {

        $bOk = $this->oMapper->SetAdministrator($nUserId);
        if ($bOk) {
            $oUser = $this->User_GetUserById($nUserId);
            if ($oUser) $this->User_Update($oUser);
        }
        return $bOk;
    }

    /**
     * @param int $nUserId
     *
     * @return bool
     */
    public function UnsetAdministrator($nUserId) {

        $bOk = $this->oMapper->UnsetAdministrator($nUserId);
        if ($bOk) {
            $oUser = $this->User_GetUserById($nUserId);
            if ($oUser) $this->User_Update($oUser);
        }
        return $bOk;
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
        $aBlogsId = $this->Blog_GetBlogsByOwnerId($nUserId, true);
        if ($aBlogsId) {
            $this->Blog_DeleteBlog($aBlogsId);
        }
        $oBlog = $this->Blog_GetPersonalBlogByUserId($nUserId);
        if ($oBlog) {
            $this->Blog_DeleteBlog($oBlog->getId());
        }

        // Удаляем переписку
        $iPerPage = 10000;
        do {
            $aTalks = $this->Talk_GetTalksByFilter(array('user_id' => $nUserId), 1, $iPerPage);
            if ($aTalks['count']) {
                $aTalksId = array();
                foreach ($aTalks['collection'] as $oTalk) {
                    $aTalksId[] = $oTalk->getId();
                }
                if ($aTalksId) {
                    $this->Talk_DeleteTalkUserByArray($aTalksId, $nUserId);
                }
            }
        } while ($aTalks['count'] > $iPerPage);

        $bOk = $this->oMapper->DelUser($nUserId);

        // Слишком много взаимосвязей, поэтому просто сбрасываем кеш
        $this->Cache_Clean();

        return $bOk;
    }


}

// EOF