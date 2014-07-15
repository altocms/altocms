<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class PluginLs_ModuleSecurity extends PluginLs_Inherit_ModuleSecurity {
    /**
     * Устанавливает security-ключ в сессию
     *
     * @return string
     */
    public function SetSessionKey() {

        return $this->SetSecurityKey();
    }

    public function SetSecurityKey() {

        $sCode = parent::SetSecurityKey();

        // LS-compatible
        $this->Viewer_Assign('LIVESTREET_SECURITY_KEY', $sCode);

        return $sCode;
    }

    /**
     * LS-compatibility
     *
     * @return string
     */
    public function GetSessionKey() {

        return parent::GetSecurityKey();
    }

    public function ValidateSessionKey($sCode = null) {

        return parent::ValidateSecurityKey($sCode);
    }
}

// EOF