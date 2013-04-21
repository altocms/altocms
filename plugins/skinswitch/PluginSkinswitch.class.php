<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   Skin switcher for LiveStreet by Sergey S Yaglov
 *   Site: http://yaglov.ru/
 *----------------------------------------------------------------------------
 */

if (!class_exists('Plugin')) {
    die('Hacking attempt!');
}

class PluginSkinswitch extends Plugin {
    public function Activate() {
        return true;
    }

    public function Init() {
        return true;
    }

}

// EOF