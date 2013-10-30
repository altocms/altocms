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
 * Экшен обработки настроек профиля юзера (/settings/)
 *
 * @package actions
 * @since   1.0
 */
class PluginLs_ActionSettings extends PluginLs_Inherits_ActionSettings {

    protected function RegisterEvent() {

        $this->AddEventPreg('/^profile$/i', '/^upload-foto/i', '/^$/i', 'EventUploadPhoto');
        $this->AddEventPreg('/^profile$/i', '/^resize-foto/i', '/^$/i', 'EventResizePhoto');
        $this->AddEventPreg('/^profile$/i', '/^remove-foto/i', '/^$/i', 'EventRemovePhoto');
        $this->AddEventPreg('/^profile$/i', '/^cancel-foto/i', '/^$/i', 'EventCancelPhoto');
        parent::RegisterEvent();
    }

    protected function EventUploadPhoto() {

        if (isset($_FILES['foto']) && !isset($_FILES['photo'])) {
            $_FILES['photo'] = $_FILES['foto'];
        }
        return parent::EventUploadPhoto();
    }

}

// EOF