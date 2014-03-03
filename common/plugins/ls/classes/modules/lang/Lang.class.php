<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class PluginLs_ModuleLang extends PluginLs_Inherit_ModuleLang {

    protected function LoadLangFiles($sLangName, $sLangFor = null) {

        parent::LoadLangFiles($sLangName, $sLangFor);
        $sLangFile = Plugin::GetDir('ls') . 'templates/language/' . $sLangFor . '.php';
        if (F::File_Exists($sLangFile)) {
            $this->AddMessages(F::File_IncludeFile($sLangFile, false, true), null, $sLangFor);
        }
    }

}

// EOF