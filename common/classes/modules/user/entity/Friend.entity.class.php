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
 * Сущность дружбу - связи пользователей друг с другом
 *
 * @package modules.user
 * @since   1.0
 */
class ModuleUser_EntityFriend extends Entity {
    /**
     * При переданном параметре $sUserId возвращает тот идентификатор,
     * который не равен переданному
     *
     * @param string|null $sUserId ID пользователя
     *
     * @return string
     */
    public function getFriendId($sUserId = null) {

        if (!$sUserId) {
            $sUserId = $this->getUserId();
        }
        if ($this->getProp('user_from') == $sUserId) {
            return $this->_aData['user_to'];
        }
        if ($this->getProp('user_to') == $sUserId) {
            return $this->_aData['user_from'];
        }
        return false;
    }

    /**
     * Получает идентификатор пользователя,
     * относительно которого был сделан запрос
     *
     * @return int
     */
    public function getUserId() {

        return $this->getProp('user');
    }

    /**
     * Возвращает ID пользователя, который приглашает в друзья
     *
     * @return int|null
     */
    public function getUserFrom() {

        return $this->getProp('user_from');
    }

    /**
     * Возвращает ID пользователя, которого пришлашаем в друзья
     *
     * @return int|null
     */
    public function getUserTo() {

        return $this->getProp('user_to');
    }

    /**
     * Возвращает статус заявки на добавления в друзья у отправителя
     *
     * @return int|null
     */
    public function getStatusFrom() {

        return $this->getProp('status_from');
    }

    /**
     * Возвращает статус заявки на добавления в друзья у получателя
     *
     * @return int|null
     */
    public function getStatusTo() {

        return $this->getProp('status_to') ? $this->getProp('status_to') : ModuleUser::USER_FRIEND_NULL;
    }

    /**
     * Возвращает статус дружбы
     *
     * @return int|null
     */
    public function getFriendStatus() {

        return $this->getStatusFrom() + $this->getStatusTo();
    }

    /**
     * Возвращает статус дружбы для конкретного пользователя
     *
     * @param int $sUserId ID пользователя
     *
     * @return bool|int
     */
    public function getStatusByUserId($sUserId) {

        if ($sUserId == $this->getUserFrom()) {
            return $this->getStatusFrom();
        }
        if ($sUserId == $this->getUserTo()) {
            return $this->getStatusTo();
        }
        return false;
    }

    /**
     * Устанавливает ID пользователя, который приглашает в друзья
     *
     * @param int $data
     */
    public function setUserFrom($data) {

        $this->setProp('user_from', $data);
    }

    /**
     * Устанавливает ID пользователя, которого пришлашаем в друзья
     *
     * @param int $data
     */
    public function setUserTo($data) {

        $this->setProp('user_to', $data);
    }

    /**
     * Устанавливает статус заявки на добавления в друзья у отправителя
     *
     * @param int $data
     */
    public function setStatusFrom($data) {

        $this->setProp('status_from', $data);
    }

    /**
     * Возвращает статус заявки на добавления в друзья у получателя
     *
     * @param int $data
     */
    public function setStatusTo($data) {

        $this->setProp('status_to', $data);
    }

    /**
     * Устанавливает ID пользователя
     *
     * @param int $data
     */
    public function setUserId($data) {

        $this->setProp('user', $data);
    }

    /**
     * Возвращает статус дружбы для конкретного пользователя
     *
     * @param int $data    Статус
     * @param int $sUserId ID пользователя
     *
     * @return bool
     */
    public function setStatusByUserId($data, $sUserId) {

        if ($sUserId == $this->getUserFrom()) {
            $this->setStatusFrom($data);
            return true;
        }
        if ($sUserId == $this->getUserTo()) {
            $this->setStatusTo($data);
            return true;
        }
        return false;
    }

    /**
     * User is real friend
     *
     * @return bool
     */
    public function isFriend() {

        return ($this->getFriendStatus() == ModuleUser::USER_FRIEND_ACCEPT + ModuleUser::USER_FRIEND_OFFER) OR
        ($this->getFriendStatus() == ModuleUser::USER_FRIEND_ACCEPT + ModuleUser::USER_FRIEND_ACCEPT);
    }

    /**
     * Wait for acception of friendship's request
     *
     * @return bool
     */
    public function AcceptionWait() {

        return ($this->getStatusTo() == ModuleUser::USER_FRIEND_REJECT AND
            $this->getStatusFrom() == ModuleUser::USER_FRIEND_OFFER AND $this->getUserTo() == E::UserId()) OR
        ($this->getFriendStatus() == ModuleUser::USER_FRIEND_OFFER + ModuleUser::USER_FRIEND_NULL AND
            $this->getUserTo() == E::UserId());
    }

    /**
     * Friendship's request was sent
     *
     * @return bool
     */
    public function RequestSent() {

        return ($this->getFriendStatus() == ModuleUser::USER_FRIEND_OFFER + ModuleUser::USER_FRIEND_NULL) AND
        ($this->getUserFrom() == E::UserId());
    }

    /**
     * Friendship's request was rejected
     *
     * @return bool
     */
    public function RequestRejected() {

        return ($this->getFriendStatus() == ModuleUser::USER_FRIEND_OFFER + ModuleUser::USER_FRIEND_REJECT) AND
        ($this->getUserTo() != E::UserId());
    }

    /**
     * Friendship was cancelled
     *
     * @return bool
     */
    public function isCancelled() {

        return ($this->getStatusFrom() == ModuleUser::USER_FRIEND_DELETE) AND ($this->getUserFrom() != E::UserId());
    }

    /**
     * User was deleted from friends list
     *
     * @return bool
     */
    public function isDeleted() {

        return ($this->getStatusFrom() == ModuleUser::USER_FRIEND_DELETE) AND ($this->getUserFrom() == E::UserId());
    }
}

// EOF