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
 * @package plugin Demo
 * @since 0.9.2
 */

class PluginDemo_ActionDemo extends ActionPlugin {
    public function Init() {
        $this->SetDefaultEvent('index');
    }

    /**
     * Регистрация евентов
     */
    protected function RegisterEvent() {
        $this->AddEvent('index', 'EventIndex');
    }

    protected function EventIndex() {
        $this->SetTemplateAction('index');
        $this->Viewer_AddBlock(
            'right',
            'Demoexec',
            array('plugin'=>'demo')
        );
        $this->Viewer_AddBlock(
            'right',
            'blocks/block.demo2.tpl',
            array('plugin'=>'demo')
        );
    }

}

// EOF