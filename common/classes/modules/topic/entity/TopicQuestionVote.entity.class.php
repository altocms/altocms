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
 * Объект сущности голосования в топике-опросе
 *
 * @package modules.topic
 * @since   1.0
 */
class ModuleTopic_EntityTopicQuestionVote extends Entity {
    /**
     * Возвращает ID топика
     *
     * @return int|null
     */
    public function getTopicId() {

        return $this->getProp('topic_id');
    }

    /**
     * Возвращает ID опроса
     *
     * @return int|null
     */
    public function getQuestionId() {

        return $this->getProp('question_id');
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
     * Возвращает номер варианта
     *
     * @return int|null
     */
    public function getAnswer() {

        return $this->getProp('answer');
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
     * Устанавливает ID опроса
     *
     * @param int $data
     */
    public function setQuestionId($data) {

        $this->setProp('question_id', $data);
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
     * Устанавливает номер варианта
     *
     * @param int $data
     */
    public function setAnswer($data) {

        $this->setProp('answer', $data);
    }
}

// EOF