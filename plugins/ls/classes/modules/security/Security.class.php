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

class PluginLs_ModuleSecurity extends PluginLs_Inherit_ModuleSecurity {
    /**
     * Устанавливает security-ключ в сессию
     *
     * @return string
     */
    public function SetSessionKey() {
        $sCode = parent::SetSessionKey();

        // LS-compatible
        $this->Viewer_Assign('LIVESTREET_SECURITY_KEY', $sCode);

        return $sCode;
    }

}

// EOF