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
 * Сущность инвайта(приглашения)
 *
 * @package modules.user
 * @since   1.0
 */
class ModuleUser_EntityInvite extends Entity {
    /**
     * Возвращает ID инвайта
     *
     * @return int|null
     */
    public function getId() {

        return $this->getProp('invite_id');
    }

    /**
     * Возвращает код инвайта
     *
     * @return string|null
     */
    public function getCode() {

        return $this->getProp('invite_code');
    }

    /**
     * Возвращает ID пользователя, который отправляет инвайт
     *
     * @return int|null
     */
    public function getUserFromId() {

        return $this->getProp('user_from_id');
    }

    /**
     * Возвращает ID пользователя, которому отправляем инвайт
     *
     * @return int|null
     */
    public function getUserToId() {

        return $this->getProp('user_to_id');
    }

    /**
     * Возвращает дату выдачи инвайта
     *
     * @return string|null
     */
    public function getDateAdd() {

        return $this->getProp('invite_date_add');
    }

    /**
     * Возвращает дату использования инвайта
     *
     * @return string|null
     */
    public function getDateUsed() {

        return $this->getProp('invite_date_used');
    }

    /**
     * Возвращает статус использованости инвайта
     *
     * @return int|null
     */
    public function getUsed() {

        return $this->getProp('invite_used');
    }


    /**
     * Устанавливает ID инвайта
     *
     * @param int $data
     */
    public function setId($data) {

        $this->setProp('invite_id', $data);
    }

    /**
     * Устанавливает код инвайта
     *
     * @param string $data
     */
    public function setCode($data) {

        $this->setProp('invite_code', $data);
    }

    /**
     * Устанавливает ID пользователя, который отправляет инвайт
     *
     * @param int $data
     */
    public function setUserFromId($data) {

        $this->setProp('user_from_id', $data);
    }

    /**
     * Устанавливает ID пользователя, которому отправляем инвайт
     *
     * @param int $data
     */
    public function setUserToId($data) {

        $this->setProp('user_to_id', $data);
    }

    /**
     * Устанавливает дату выдачи инвайта
     *
     * @param string $data
     */
    public function setDateAdd($data) {

        $this->setProp('invite_date_add', $data);
    }

    /**
     * Устанавливает дату использования инвайта
     *
     * @param string $data
     */
    public function setDateUsed($data) {

        $this->setProp('invite_date_used', $data);
    }

    /**
     * Устанавливает статус использованости инвайта
     *
     * @param int $data
     */
    public function setUsed($data) {

        $this->setProp('invite_used', $data);
    }
}

// EOF