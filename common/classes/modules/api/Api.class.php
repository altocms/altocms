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

    /**
     * Инициализация модуля
     */
    public function Init() {

        return TRUE;
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
    public function ApiUserInfo($aParams) {

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
            'profile'   => $oUser->getUserWebPath(),
            'country'   => $oUser->getProfileCountry(),
            'city'      => $oUser->getProfileCity(),
            'region'    => $oUser->getProfileRegion(),
        ));

     }
}

