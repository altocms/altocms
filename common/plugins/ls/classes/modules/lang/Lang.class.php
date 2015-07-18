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

        if (!$sLangFor) {
            $sLangFor = $this->sCurrentLang;
        }
        parent::LoadLangFiles($sLangName, $sLangFor);

        $aDirs = Plugin::GetDirLang('ls');
        foreach ($aDirs as $sDir) {
            $aFiles = $this->_makeFileList($sDir, static::LANG_PATTERN . '.php', $sLangName);
            if ($aFiles) {
                foreach($aFiles as $sLangFile) {
                    $this->AddMessages(F::File_IncludeFile($sLangFile, false, false), null, $sLangFor);
                }
            }
        }
    }

}

// EOF