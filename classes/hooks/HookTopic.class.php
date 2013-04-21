<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * Регистрация хука для вывода меню страниц
 *
 */
class HookTopic extends Hook {
    public function RegisterHook() {
        if (Config::Get('module.topic.draft_link')) {
            $this->AddHook('template_topic_show_end', 'TemplateTopicShowEnd');
        }
    }

    public function TemplateTopicShowEnd() {
        if (Config::Get('module.topic.draft_link')) {
            return $this->Viewer_Fetch('hook.draft_link.tpl');
        }
        return '';
    }

}

// EOF