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
 * Объект сущности факта прочтения топика
 *
 * @package modules.topic
 * @since   1.0
 */
class ModuleTopic_EntityTopicRead extends Entity {
    /**
     * Возвращает ID топика
     *
     * @return int|null
     */
    public function getTopicId() {

        return $this->getProp('topic_id');
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
     * Возвращает дату прочтения
     *
     * @return string|null
     */
    public function getDateRead() {

        return $this->getProp('date_read');
    }

    /**
     * Возвращает число комментариев в последнем прочтении топика
     *
     * @return int|null
     */
    public function getCommentCountLast() {

        return $this->getProp('comment_count_last');
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
     * Устанавливает ID топика
     *
     * @param int $data
     */
    public function setTopicId($data) {

        $this->setProp('topic_id', $data);
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
     * Устанавливает дату прочтения
     *
     * @param string $data
     */
    public function setDateRead($data) {

        $this->setProp('date_read', $data);
    }

    /**
     * Устанавливает число комментариев в последнем прочтении топика
     *
     * @param int $data
     */
    public function setCommentCountLast($data) {

        $this->setProp('comment_count_last', $data);
    }

    /**
     * Устанавливает ID последнего комментария
     *
     * @param int $data
     */
    public function setCommentIdLast($data) {

        $this->setProp('comment_id_last', $data);
    }
}

// EOF