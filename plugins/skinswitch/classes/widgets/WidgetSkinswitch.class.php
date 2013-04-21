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

class PluginSkinswitch_WidgetSkinswitch extends Widget {
    public function Exec() {
        $this->Viewer_Assign('aSkinswitchTemplates', $this->Skin_GetSkinsArray('site'));
        $this->Viewer_Assign('aSkinswitchGetParam', Config::Get('plugin.skinswitch.get_param'));
        $this->Viewer_Assign('aSkinswitchCurrent', Config::Get('view.skin'));
        return $this->Viewer_Fetch(Plugin::GetTemplatePath(__CLASS__) . 'toolbar.skinswitch.tpl');
    }
}

// EOF