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
 * Объект связи пользователя с разовором
 *
 * @package modules.talk
 * @since   1.0
 */
class ModuleTalk_EntityTalkUser extends Entity {
    /**
     * Возвращает ID разговора
     *
     * @return int|null
     */
    public function getTalkId() {

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
     * Возвращает дату последнего сообщения
     *
     * @return string|null
     */
    public function getDateLast() {

        return $this->getProp('date_last');
    }

    /**
     * Возвращает ID последнего комментария
     *
     * @return int|null
     */
    public function getCommentIdLast() {

        return $this->getProp('comment_id_last');
    }

    /**
     * Возвращает количество новых сообщений
     *
     * @return int|null
     */
    public function getCommentCountNew() {

        return $this->getProp('comment_count_new');
    }

    /**
     * Возвращает статус активности пользователя
     *
     * @return int
     */
    public function getUserActive() {

        return $this->getProp('talk_user_active', ModuleTalk::TALK_USER_ACTIVE);
    }

    /**
     * Возвращает соответствующий пользователю объект
     *
     * @return ModuleUser_EntityUser | null
     */
    public function getUser() {

        return $this->getProp('user');
    }


    /**
     * Устанавливает ID разговора
     *
     * @param int $data
     */
    public function setTalkId($data) {

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
     * Устанавливает последнюю дату
     *
     * @param string $data
     */
    public function setDateLast($data) {

        $this->setProp('date_last', $data);
    }

    /**
     * Устанавливает ID последнее комментария
     *
     * @param int $data
     */
    public function setCommentIdLast($data) {

        $this->setProp('comment_id_last', $data);
    }

    /**
     * Устанавливает количество новых комментариев
     *
     * @param int $data
     */
    public function setCommentCountNew($data) {

        $this->setProp('comment_count_new', $data);
    }

    /**
     * Устанавливает статус связи
     *
     * @param int $data
     */
    public function setUserActive($data) {

        $this->setProp('talk_user_active', $data);
    }

    /**
     * Устанавливает объект пользователя
     *
     * @param ModuleUser_EntityUser $data
     */
    public function setUser($data) {

        $this->setProp('user', $data);
    }

}

// EOF