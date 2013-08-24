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

class PluginDemo_ActionAdmin extends PluginDemo_Inherits_ActionAdmin {
    // Установка собственного обработчика главной страницы
    protected function _eventConfigLinks() {
        if (($sHomePage = $this->GetPost('homepage')) && ($sHomePage == 'demo_homepage')) {
            $aConfig = array(
                'router.config.action_default' => 'homepage',
                'router.config.homepage' => 'demo/index',
                'router.config.homepage_select' => 'demo_homepage',
            );
            Config::WriteCustomConfig($aConfig);
            Router::Location('admin/config/links');
            exit;
        }
        return parent::_eventConfigLinks();
    }
}

// EOF