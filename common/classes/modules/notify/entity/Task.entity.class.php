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
 * Объект сущности задания на отправку емайла
 *
 * @package modules.notify
 * @since   1.0
 */
class ModuleNotify_EntityTask extends Entity {
    /**
     * Возвращает ID задания
     *
     * @return int|null
     */
    public function getTaskId() {

        return $this->getProp('notify_task_id');
    }

    /**
     * Возвращает емайл
     *
     * @return string|null
     */
    public function getUserMail() {

        return $this->getProp('user_mail');
    }

    /**
     * Возвращает логин пользователя
     *
     * @return string|null
     */
    public function getUserLogin() {

        return $this->getProp('user_login');
    }

    /**
     * Возвращает текст сообщения
     *
     * @return string|null
     */
    public function getNotifyText() {

        return $this->getProp('notify_text');
    }

    /**
     * Возвращает дату создания сообщения
     *
     * @return string|null
     */
    public function getDateCreated() {

        return $this->getProp('date_created');
    }

    /**
     * Возвращает статус отправки
     *
     * @return int|null
     */
    public function getTaskStatus() {

        return $this->getProp('notify_task_status');
    }

    /**
     * Возвращает тему сообщения
     *
     * @return string|null
     */
    public function getNotifySubject() {

        return $this->getProp('notify_subject');
    }


    /**
     * Устанавливает ID задания
     *
     * @param int $data
     */
    public function setTaskId($data) {

        $this->setProp('notify_task_id', $data);
    }

    /**
     * Устанавливает емайл
     *
     * @param string $data
     */
    public function setUserMail($data) {

        $this->setProp('user_mail', $data);
    }

    /**
     * Устанавливает логин
     *
     * @param string $data
     */
    public function setUserLogin($data) {

        $this->setProp('user_login', $data);
    }

    /**
     * Устанавливает текст уведомления
     *
     * @param string $data
     */
    public function setNotifyText($data) {

        $this->setProp('notify_text', $data);
    }

    /**
     * Устанавливает дату создания задания
     *
     * @param string $data
     */
    public function setDateCreated($data) {

        $this->setProp('date_created', $data);
    }

    /**
     * Устанавливает статус задания
     *
     * @param int $data
     */
    public function setTaskStatus($data) {

        $this->setProp('notify_task_status', $data);
    }

    /**
     * Устанавливает тему сообщения
     *
     * @param string $data
     */
    public function setNotifySubject($data) {

        $this->setProp('notify_subject', $data);
    }

}

// EOF