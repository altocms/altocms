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

class PluginSkinswitch_HookSkinswitch extends Hook {
    public function RegisterHook() {
        if (Router::GetAction() !== 'admin') {
            $this->AddHook('viewer_init_start', 'Skinswitch');
        }
    }

    protected function _getSkins() {
        $aSkins = Config::Get('plugin.skinswitch.skins');
        if (is_null($aSkins)) {
            $aSkins = $this->Skin_GetSkinsArray('site');
            Config::Set('plugin.skinswitch.skins', $aSkins);
        }
        return $aSkins;
    }

    public function Skinswitch() {
        $aSkins = $this->_getSkins();
        if (!$aSkins) {
            return;
        }
        $sGetParam = Config::Get('plugin.skinswitch.get_param');
        $sGetValue = getRequest($sGetParam, null, 'get');
        @$sSessValue = & $_SESSION['skinswitch.skin'];
        $bSetSkin = false;
        if (in_array($sGetValue, $aSkins)) {
            $sSessValue = $sGetValue;
            $bSetSkin = true;
        } elseif (in_array($sSessValue, $aSkins)) {
            $bSetSkin = true;
        }
        if ($bSetSkin) {
            Config::Set('view.skin', $sSessValue);
        }
    }
}

// EOF