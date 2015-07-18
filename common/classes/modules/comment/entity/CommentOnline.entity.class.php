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
 * Объект сущности прямого эфира
 *
 * @package modules.comment
 * @since   1.0
 */
class ModuleComment_EntityCommentOnline extends Entity {
    /**
     * Возвращает ID владельца
     *
     * @return int|null
     */
    public function getTargetId() {

        return $this->getProp('target_id');
    }

    /**
     * Возвращает тип владельца
     *
     * @return string|null
     */
    public function getTargetType() {

        return $this->getProp('target_type');
    }

    /**
     * Возвращает ID комментария
     *
     * @return int|null
     */
    public function getCommentId() {

        return $this->getProp('comment_id');
    }

    /**
     * Возвращает ID родителя владельца
     *
     * @return int
     */
    public function getTargetParentId() {

        return intval($this->getProp('target_parent_id'));
    }

    /**
     * Устанавливает ID владельца
     *
     * @param int $data
     */
    public function setTargetId($data) {

        $this->setProp('target_id', $data);
    }

    /**
     * Устанавливает тип владельца
     *
     * @param string $data
     */
    public function setTargetType($data) {

        $this->setProp('target_type', $data);
    }

    /**
     * Устанавливает ID комментария
     *
     * @param int $data
     */
    public function setCommentId($data) {

        $this->setProp('comment_id', $data);
    }

    /**
     * Устанавливает ID родителя владельца
     *
     * @param int $data
     */
    public function setTargetParentId($data) {

        $this->setProp('target_parent_id', $data);
    }
}

// EOF