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
 * Модуль работы с АПИ
 *
 * @package modules.api
 * @since   1.1
 */
class ModuleApi extends Module {

    // Неизвестные ошибки
    public $ERROR_CODE_9001 = array('code' => '9001', 'description' => 'Unknown error');
    public $ERROR_CODE_9002 = array('code' => '9002', 'description' => 'Unknown resource');

    // Ошибка отсутствия ресурса
    public $ERROR_CODE_9003 = array('code' => '9003', 'description' => 'Resource is not found');
    public $ERROR_CODE_9004 = array('code' => '9004', 'description' => 'Access denied');
    public $ERROR_CODE_9005 = array('code' => '9005', 'description' => 'Wrong parameters');

    // Ошибки типа запроса
    public $ERROR_CODE_9010 = array('code' => '9010', 'description' => 'Request method POST is not allowed');
    public $ERROR_CODE_9011 = array('code' => '9011', 'description' => 'Request method GET is not allowed');
    public $ERROR_CODE_9012 = array('code' => '9012', 'description' => 'Request method PUT is not allowed');
    public $ERROR_CODE_9013 = array('code' => '9013', 'description' => 'Request method DELETE is not allowed');
    public $ERROR_CODE_9014 = array('code' => '9014', 'description' => 'Ajax request is not allowed');

    /**
     * Инициализация модуля
     */
    public function Init() {

        return TRUE;
    }

    /**
     * Сохранение последней ошибки
     * @param $aError
     */
    public function SetLastError($aError) {

        E::ModuleCache()->SetTmp($aError, 'MODULE_API_LAST_ERROR');
    }

    /**
     * Получение последней ошибки
     */
    public function GetLastError() {

        return E::ModuleCache()->GetTmp('MODULE_API_LAST_ERROR');
    }

    /**
     * Подготавливает данные для передачи в экшен
     *
     * @param [] $aData Данные для передачи в шаблон
     * @param [] $aJsonData Данные для преобразования в json
     * @return array
     */
    private function _PrepareResult($aData, $aJsonData) {

        return array(
            'data' => $aData,
            'json' => $aJsonData
        );
    }

    /**
     * Получение сведений о пользователе
     * @param string $aParams Идентификатор пользователя
     * @return bool|array
     */
    public function ApiUserIdInfo($aParams) {

        /** @var ModuleUser_EntityUser $oUser */
        if (!($oUser = E::ModuleUser()->GetUserById($aParams['uid']))) {
            return FALSE;
        }

        return $this->_PrepareResult(array('oUser' => $oUser), array(
            'id'        => $oUser->getId(),
            'login'     => $oUser->getLogin(),
            'name'      => $oUser->getDisplayName(),
            'sex'       => $oUser->getProfileSex(),
            'role'      => $oUser->getRole(),
            'avatar'    => $oUser->getProfileAvatar(),
            'photo'     => $oUser->getProfilePhoto(),
            'about'     => $oUser->getProfileAbout(),
            'birthday'  => $oUser->getProfileBirthday(),
            'vote'      => $oUser->getVote(),
            'skill'     => $oUser->getSkill(),
            'rating'    => $oUser->getRating(),
            'is_friend' => $oUser->getUserIsFriend(),
            'profile'   => $oUser->getProfileUrl(),
            'country'   => $oUser->getProfileCountry(),
            'city'      => $oUser->getProfileCity(),
            'region'    => $oUser->getProfileRegion(),
        ));

    }

    /**
     * Получение сведений о блоге
     * @param string $aParams Идентификатор пользователя
     * @return bool|array
     */
    public function ApiBlogIdInfo($aParams) {

        /** @var ModuleBlog_EntityBlog $oBlog */
        if (!($oBlog = E::ModuleBlog()->GetBlogById($aParams['uid']))) {
            return FALSE;
        }

        return $this->_PrepareResult(array('oBlog' => $oBlog), array(
            'id'          => $oBlog->getId(),
            'title'       => $oBlog->getTitle(),
            'description' => $oBlog->getDescription(),
            'logo'        => $oBlog->getAvatarUrl(),
            'date'        => $oBlog->getDateAdd(),
            'users'       => $oBlog->getCountUser(),
            'topics'      => $oBlog->getCountTopic(),
            'rating'      => $oBlog->getRating(),
            'votes'       => $oBlog->getCountVote(),
            'link'        => $oBlog->getUrl(),
            'rss'         => C::Get('path.root.web') . "rss/blog/{$oBlog->getUrl()}/",
        ));

    }

    /**
     * Получение сведений о рейтинге топика
     * @param string $aParams Идентификатор пользователя
     * @return bool|array
     */
    public function ApiTopicIdRating($aParams) {

        /** @var ModuleTopic_EntityTopic $oTopic */
        if (!($oTopic = E::ModuleTopic()->GetTopicById($aParams['tid']))) {
            return FALSE;
        }

        return $this->_PrepareResult(array('oTopic' => $oTopic), array(
            'id'      => $oTopic->getId(),
            'vote'    => $oTopic->getVote(),
            'count'   => $oTopic->getCountVote(),
            'up'      => $oTopic->getCountVoteUp(),
            'down'    => $oTopic->getCountVoteDown(),
            'abstain' => $oTopic->getCountVoteAbstain(),
        ));

    }
}

