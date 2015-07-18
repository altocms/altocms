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
 * Сущность голосования
 *
 * @package modules.vote
 * @since   1.0
 */
class ModuleVote_EntityVote extends Entity {
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
     * Возвращает ID проголосовавшего пользователя
     *
     * @return int|null
     */
    public function getVoterId() {

        return $this->getProp('user_voter_id');
    }

    /**
     * Возвращает направление голоса: 0, 1, -1
     *
     * @return int|null
     */
    public function getDirection() {

        return $this->getProp('vote_direction');
    }

    /**
     * Возвращает значение при голосовании
     *
     * @return float|null
     */
    public function getValue() {

        return $this->getProp('vote_value');
    }

    /**
     * Возвращает дату голосования
     *
     * @return string|null
     */
    public function getDate() {

        return $this->getProp('vote_date');
    }

    /**
     * Возвращает IP голосовавшего
     *
     * @return string|null
     */
    public function getIp() {

        return $this->getProp('vote_ip');
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
     * Устанавливает ID проголосовавшего пользователя
     *
     * @param int $data
     */
    public function setVoterId($data) {

        $this->setProp('user_voter_id', $data);
    }

    /**
     * Устанавливает направление голоса: 0, 1, -1
     *
     * @param int $data
     */
    public function setDirection($data) {

        $this->setProp('vote_direction', $data);
    }

    /**
     * Устанавливает значение при голосовании
     *
     * @param float $data
     */
    public function setValue($data) {

        $this->setProp('vote_value', $data);
    }

    /**
     * Устанавливает дату голосования
     *
     * @param string $data
     */
    public function setDate($data) {

        $this->setProp('vote_date', $data);
    }

    /**
     * Устанавливает IP голосовавшего
     *
     * @param string $data
     */
    public function setIp($data) {

        $this->setProp('vote_ip', $data);
    }

}

// EOF