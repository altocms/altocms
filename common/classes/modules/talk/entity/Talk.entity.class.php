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
 * Объект сущности сообщения
 *
 * @package modules.talk
 * @since   1.0
 */
class ModuleTalk_EntityTalk extends Entity {
    /**
     * Возвращает ID сообщения
     *
     * @return int|null
     */
    public function getId() {

        return $this->getProp('talk_id');
    }

    /**
     * Возвращает ID пользователя
     *
     * @return int|null
     */
    public function getUserId() {

        return $this->getProp('user_id');
    }

    /**
     * Вовзращает заголовок сообщения
     *
     * @return string|null
     */
    public function getTitle() {

        return $this->getProp('talk_title');
    }

    /**
     * Возвращает текст сообщения
     *
     * @return string|null
     */
    public function getText() {

        return $this->getProp('talk_text');
    }

    /**
     * Возвращает дату сообщения
     *
     * @return string|null
     */
    public function getDate() {

        return $this->getProp('talk_date');
    }

    /**
     * Возвращает дату последнего сообщения
     *
     * @return string|null
     */
    public function getDateLast() {

        return $this->getProp('talk_date_last');
    }

    /**
     * Возвращает ID последнего пользователя
     *
     * @return int|null
     */
    public function getUserIdLast() {

        return $this->getProp('talk_user_id_last');
    }

    /**
     * Вовзращает IP пользователя
     *
     * @return string|null
     */
    public function getUserIp() {

        return $this->getProp('talk_user_ip');
    }

    /**
     * Возвращает ID последнего комментария
     *
     * @return int|null
     */
    public function getCommentIdLast() {

        return $this->getProp('talk_comment_id_last');
    }

    /**
     * Возвращает количество комментариев
     *
     * @return int|null
     */
    public function getCountComment() {

        return $this->getProp('talk_count_comment');
    }


    /**
     * Возвращает последний текст(коммент) из письма, если комментов нет, то текст исходного сообщения
     *
     * @return string
     */
    public function getTextLast() {

        if ($oComment = $this->getCommentLast()) {
            return $oComment->getText();
        }
        return $this->getText();
    }

    /**
     * Возвращает список пользователей
     *
     * @return array|null
     */
    public function getUsers() {

        return $this->getProp('users');
    }

    /**
     * Возвращает объект пользователя
     *
     * @return ModuleUser_EntityUser|null
     */
    public function getUser() {

        return $this->getProp('user');
    }

    /**
     * Возвращает объект связи пользователя с сообщением
     *
     * @return ModuleTalk_EntityTalkUser|null
     */
    public function getTalkUser() {

        return $this->getProp('talk_user');
    }

    /**
     * Возращает true, если разговор занесен в избранное
     *
     * @return bool
     */
    public function getIsFavourite() {

        return $this->getProp('talk_is_favourite');
    }

    /**
     * Возращает пользователей разговора
     *
     * @return array
     */
    public function getTalkUsers() {

        return $this->getProp('talk_users');
    }


    /**
     * Устанавливает ID сообщения
     *
     * @param int $data
     */
    public function setId($data) {

        $this->setProp('talk_id', $data);
    }

    /**
     * Устанавливает ID пользователя
     *
     * @param int $data
     */
    public function setUserId($data) {

        $this->setProp('user_id', $data);
    }

    /**
     * Устанавливает заголовок сообщения
     *
     * @param string $data
     */
    public function setTitle($data) {

        $this->setProp('talk_title', $data);
    }

    /**
     * Устанавливает текст сообщения
     *
     * @param string $data
     */
    public function setText($data) {

        $this->setProp('talk_text', $data);
    }

    /**
     * Устанавливает дату разговора
     *
     * @param string $data
     */
    public function setDate($data) {

        $this->setProp('talk_date', $data);
    }

    /**
     * Устанавливает дату последнего сообщения в разговоре
     *
     * @param string $data
     */
    public function setDateLast($data) {

        $this->setProp('talk_date_last', $data);
    }

    /**
     * Устанавливает ID последнего пользователя
     *
     * @param int $data
     */
    public function setUserIdLast($data) {

        $this->setProp('talk_user_id_last', $data);
    }

    /**
     * Устанавливает IP пользователя
     *
     * @param string $data
     */
    public function setUserIp($data) {

        $this->setProp('talk_user_ip', $data);
    }

    /**
     * Устанавливает ID последнего комментария
     *
     * @param string $data
     */
    public function setCommentIdLast($data) {

        $this->setProp('talk_comment_id_last', $data);
    }

    /**
     * Устанавливает количество комментариев
     *
     * @param int $data
     */
    public function setCountComment($data) {

        $this->setProp('talk_count_comment', $data);
    }

    /**
     * Устанавливает список пользователей
     *
     * @param array $data
     */
    public function setUsers($data) {

        $this->setProp('users', $data);
    }

    /**
     * Устанавливает объект пользователя
     *
     * @param ModuleUser_EntityUser $data
     */
    public function setUser($data) {

        $this->setProp('user', $data);
    }

    /**
     * Устанавливает объект связи
     *
     * @param ModuleTalk_EntityTalkUser $data
     */
    public function setTalkUser($data) {

        $this->setProp('talk_user', $data);
    }

    /**
     * Устанавливает факт налиция разговора в избранном текущего пользователя
     *
     * @param bool $data
     */
    public function setIsFavourite($data) {

        $this->setProp('talk_is_favourite', $data);
    }

    /**
     * Устанавливает список связей
     *
     * @param array $data
     */
    public function setTalkUsers($data) {

        $this->setProp('talk_users', $data);
    }

}

// EOF