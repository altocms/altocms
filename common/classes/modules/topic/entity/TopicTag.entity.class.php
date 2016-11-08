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
 * Объект сущности тега топика
 *
 * @package modules.topic
 * @since   1.0
 */
class ModuleTopic_EntityTopicTag extends Entity {
    /**
     * Возвращает ID тега
     *
     * @return int|null
     */
    public function getId() {

        return $this->getProp('topic_tag_id');
    }

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
     * Возвращает ID блога
     *
     * @return int|null
     */
    public function getBlogId() {

        return $this->getProp('blog_id');
    }

    /**
     * Возвращает текст тега
     *
     * @return string|null
     */
    public function getText() {

        return $this->getProp('topic_tag_text');
    }

    /**
     * Возвращает количество тегов
     *
     * @return int|null
     */
    public function getCount() {

        return $this->getProp('count');
    }

    /**
     * Возвращает просчитанный размер тега для облака тегов
     *
     * @return int|null
     */
    public function getSize() {

        return $this->getProp('size');
    }


    /**
     * Устанавливает ID тега
     *
     * @param int $data
     */
    public function setId($data) {

        $this->setProp('topic_tag_id', $data);
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
     * Устанавливает ID блога
     *
     * @param int $data
     */
    public function setBlogId($data) {

        $this->setProp('blog_id', $data);
    }

    /**
     * Устанавливает текст тега
     *
     * @param string $data
     */
    public function setText($data) {

        $this->setProp('topic_tag_text', $data);
    }

    /**
     * Устанавливает просчитанный размер тега для облака тегов
     *
     * @param int $data
     */
    public function setSize($data) {

        $this->setProp('size', $data);
    }

    /**
     * @return string
     */
    public function getLink() {

        $sLink = R::GetPath('tag') . F::UrlEncode($this->getText(), true) . '/';
        return $sLink;
    }

}

// EOF