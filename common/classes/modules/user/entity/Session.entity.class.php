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
 * Сущность сессии
 *
 * @package modules.user
 * @since   1.0
 */
class ModuleUser_EntitySession extends Entity {
    /**
     * Возвращает ключ сессии
     *
     * @return string|null
     */
    public function getKey() {

        return $this->getProp('session_key');
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
     * Возвращает IP создания сессии
     *
     * @return string|null
     */
    public function getIpCreate() {

        return $this->getProp('session_ip_create');
    }

    /**
     * Возвращает последний IP сессии
     *
     * @return string|null
     */
    public function getIpLast() {

        return $this->getProp('session_ip_last');
    }

    /**
     * Возвращает дату создания сессии
     *
     * @return string|null
     */
    public function getDateCreate() {

        return $this->getProp('session_date_create');
    }

    /**
     * Возвращает последную дату сессии
     *
     * @return string|null
     */
    public function getDateLast() {

        return $this->getProp('session_date_last');
    }

    /**
     * Возвращает дату завершения сессии
     *
     * @return string|null
     */
    public function getDateExit() {

        return $this->getProp('session_exit');
    }

    /**
     * Returns hash of user agent of saved session
     *
     * @return mixed|null
     */
    public function getUserAgentHash() {

        return $this->getProp('session_agent_hash');
    }


    /**
     * Устанавливает ключ сессии
     *
     * @param string $data
     */
    public function setKey($data) {

        $this->setProp('session_key', $data);
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
     * Устанавливает IP создания сессии
     *
     * @param string $data
     */
    public function setIpCreate($data) {

        $this->setProp('session_ip_create', $data);
    }

    /**
     * Устанавливает последний IP сессии
     *
     * @param string $data
     */
    public function setIpLast($data) {

        $this->setProp('session_ip_last', $data);
    }

    /**
     * Устанавливает дату создания сессии
     *
     * @param string $data
     */
    public function setDateCreate($data) {

        $this->setProp('session_date_create', $data);
    }

    /**
     * Устанавливает последную дату сессии
     *
     * @param string $data
     */
    public function setDateLast($data) {

        $this->setProp('session_date_last', $data);
    }

    /**
     * Устанавливает дату завершения сессии
     *
     * @param string $data
     */
    public function setDateExit($data) {

        $this->setProp('session_exit', $data);
    }

    /**
     * Sets hash of user agent of saved session
     *
     * @param string|null $data
     */
    public function setUserAgentHash($data = null) {

        if (is_null($data)) {
            $data = $this->Security_GetUserAgentHash();
        }
        $this->setProp('session_agent_hash', $data);
    }

}

// EOF