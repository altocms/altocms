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
 * @since   1.0
 */
class PluginLs_ActionContent extends PluginLs_Inherits_ActionContent {

    protected function SubmitAdd() {

        if (isset($_REQUEST['topic_link_url'])) {
            $_REQUEST['topic_field_link'] = strip_tags($_REQUEST['topic_link_url']);
        }
        if (isset($_REQUEST['question_title'])) {
            $_REQUEST['topic_field_question'] = $_REQUEST['question_title'];
        }
        if (isset($_REQUEST['answer'])) {
            $_REQUEST['topic_field_answers'] = $_REQUEST['answer'];
        }
        return parent::SubmitAdd();
    }

    protected function EventEdit() {

        $_REQUEST['topic_link_url'] = (isset($_REQUEST['topic_field_link']) ? $_REQUEST['topic_field_link'] : '');
        $_REQUEST['topic_tags'] = (isset($_REQUEST['topic_field_tags']) ? $_REQUEST['topic_field_tags'] : '');
        $_REQUEST['question_title'] = (isset($_REQUEST['topic_field_question']) ? $_REQUEST['topic_field_question'] : '');
        $_REQUEST['answer'] = (isset($_REQUEST['topic_field_answers']) ? $_REQUEST['topic_field_answers'] : '');
        return parent::EventEdit();
    }

    protected function SubmitEdit($oTopic) {

        if (isset($_REQUEST['topic_link_url'])) {
            $_REQUEST['topic_field_link'] = strip_tags($_REQUEST['topic_link_url']);
        }
        if (isset($_REQUEST['topic_tags'])) {
            $_REQUEST['topic_field_tags'] = strip_tags($_REQUEST['topic_tags']);
        }
        if (isset($_REQUEST['question_title'])) {
            $_REQUEST['topic_field_question'] = $_REQUEST['question_title'];
        }
        if (isset($_REQUEST['answer'])) {
            $_REQUEST['topic_field_answers'] = $_REQUEST['answer'];
        }
        return parent::SubmitEdit($oTopic);
    }

}

// EOF