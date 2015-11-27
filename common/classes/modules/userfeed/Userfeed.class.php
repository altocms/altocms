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
 * Модуль пользовательских лент контента (топиков)
 *
 * @package modules.userfeed
 * @since   1.0
 */
class ModuleUserfeed extends Module {
    /**
     * Подписки на топики по блогу
     */
    const SUBSCRIBE_TYPE_BLOG = 1;
    /**
     * Подписки на топики по юзеру
     */
    const SUBSCRIBE_TYPE_USER = 2;
    /**
     * Объект маппера
     *
     * @var ModuleUserfeed_MapperUserfeed|null
     */
    protected $oMapper = null;

    /**
     * Инициализация модуля
     */
    public function Init() {

        $this->oMapper = E::GetMapper(__CLASS__);
    }

    /**
     * Подписать пользователя
     *
     * @param int $iUserId        ID подписываемого пользователя
     * @param int $iSubscribeType Тип подписки (см. константы класса)
     * @param int $iTargetId      ID цели подписки
     *
     * @return bool
     */
    public function SubscribeUser($iUserId, $iSubscribeType, $iTargetId) {

        return $this->oMapper->subscribeUser($iUserId, $iSubscribeType, $iTargetId);
    }

    /**
     * Отписать пользователя
     *
     * @param int $iUserId        ID подписываемого пользователя
     * @param int $iSubscribeType Тип подписки (см. константы класса)
     * @param int $iTargetId      ID цели подписки
     *
     * @return bool
     */
    public function UnsubscribeUser($iUserId, $iSubscribeType, $iTargetId) {

        return $this->oMapper->unsubscribeUser($iUserId, $iSubscribeType, $iTargetId);
    }

    /**
     * Получить ленту топиков по подписке
     *
     * @param int $iUserId ID пользователя, для которого получаем ленту
     * @param int $iCount  Число получаемых записей (если null, из конфига)
     * @param int $iFromId Получить записи, начиная с указанной
     *
     * @return array
     */
    public function Read($iUserId, $iCount = null, $iFromId = null) {

        if (!$iCount) {
            $iCount = Config::Get('module.userfeed.count_default');
        }
        $aUserSubscribes = $this->oMapper->getUserSubscribes($iUserId);
        if (E::IsAdmin()) {
            $aFilter = array();
        } else {
            $aOpenBlogTypes = E::ModuleBlog()->GetOpenBlogTypes();
            $aFilter = array(
                'include_types' => $aOpenBlogTypes,
            );
        }
        $aTopicsIds = $this->oMapper->readFeed($aUserSubscribes, $iCount, $iFromId, $aFilter);
        if ($aTopicsIds) {
            return E::ModuleTopic()->GetTopicsAdditionalData($aTopicsIds);
        }
        return array();
    }

    /**
     * Получить ленту топиков по подписке
     *
     * @param int  $iUserId ID пользователя, для которого получаем ленту
     * @param int  $iPage
     * @param int  $iPerPage
     * @param bool $iOnlyNew
     *
     * @return mixed
     */
    public function Trackread($iUserId, $iPage = 1, $iPerPage = 10, $iOnlyNew = false) {

        $aTopicTracks = E::ModuleSubscribe()->GetTracks(
            array('user_id' => $iUserId, 'target_type' => 'topic_new_comment', 'status' => 1, 'only_new' => $iOnlyNew),
            array('date_add' => 'desc'), $iPage, $iPerPage
        );
        $aTopicsIds = array();
        /** @var ModuleSubscribe_EntityTrack $oTrack */
        foreach ($aTopicTracks['collection'] as $oTrack) {
            $aTopicsIds[] = $oTrack->getTargetId();
        }
        $aTopicTracks['collection'] = E::ModuleTopic()->GetTopicsAdditionalData($aTopicsIds);
        return $aTopicTracks;
    }

    /**
     * Получает число новых тем и комментов где есть юзер
     *
     * @param int $iUserId    ID пользователя
     *
     * @return int
     */
    public function GetCountTrackNew($iUserId) {

        $iUserId = (int)$iUserId;
        if (!$iUserId) {
            return false;
        }

        $sCacheKey = E::ModuleCache()->Key('track_count_new_user_', $iUserId);
        if (false === ($data = E::ModuleCache()->Get($sCacheKey, 'tmp,'))) {
            $data = $this->oMapper->GetCountTrackNew($iUserId);
            if ($iUserId == E::UserId()) {
                $sCacheType = ',tmp';
            } else {
                $sCacheType = null;
            }
            E::ModuleCache()->Set(
                $data, $sCacheKey,
                array('topic_update', 'topic_new', "topic_read_user_{$iUserId}"), 'P1D', $sCacheType
            );
        }
        return $data;
    }


    /**
     * Получить список подписок пользователя
     *
     * @param int $iUserId ID пользователя, для которого загружаются подписки
     *
     * @return array
     */
    /**
     * @param int        $iUserId
     * @param string|int $xTargetType
     * @param array      $aTargetsId
     * @param bool       $bIdOnly
     *
     * @return array
     */
    public function GetUserSubscribes($iUserId, $xTargetType = null, $aTargetsId = array(), $bIdOnly = false) {

        $aUserSubscribes = $this->oMapper->getUserSubscribes($iUserId, $xTargetType, $aTargetsId);

        if ($bIdOnly) {
            return $aUserSubscribes;
        }

        $aResult = array(
            'blogs' => array(),
            'blog' => array(),
            'users' => array(),
            'user' => array(),
        );
        if (count($aUserSubscribes['blogs'])) {
            $aBlogs = E::ModuleBlog()->GetBlogsByArrayId($aUserSubscribes['blogs']);
            foreach ($aBlogs as $oBlog) {
                $aResult['blogs'][$oBlog->getId()] = $oBlog;
                $aResult['blog'][$oBlog->getId()] = $oBlog;
            }
        }
        if (count($aUserSubscribes['users'])) {
            $aUsers = E::ModuleUser()->GetUsersByArrayId($aUserSubscribes['users']);
            foreach ($aUsers as $oUser) {
                $aResult['users'][$oUser->getId()] = $oUser;
                $aResult['user'][$oUser->getId()] = $oUser;
            }
        }

        return $aResult;
    }

}

// EOF