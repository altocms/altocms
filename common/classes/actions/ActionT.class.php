<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * @package actions
 * @since   0.9.7
 */
class ActionT extends Action {

    /**
     * Инициализация
     */
    public function Init() {
        $this->SetDefaultEvent('index');
    }

    /**
     * Регистрация евентов
     */
    protected function RegisterEvent() {
        $this->AddEventPreg('/^\d+$/i', array('EventIndex', 'index'));
    }

    public function EventIndex() {
        if ($nTopicId = intval($this->sCurrentEvent)) {
            $oTopic = E::ModuleTopic()->GetTopicById($nTopicId);
            if ($oTopic) {
                F::HttpRedirect($oTopic->getUrl());
                exit;
            }
        }
        return $this->EventNotFound();
    }
}

// EOF