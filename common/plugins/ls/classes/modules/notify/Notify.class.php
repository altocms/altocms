<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class PluginLs_ModuleNotify extends PluginLs_Inherits_ModuleNotify {

    /**
     * Returns full path to email templates by name and plugin
     *
     * @param  string        $sName   Template name
     * @param  string|object $xPlugin Name or class of plugin
     *
     * @return string
     */
    public function GetTemplatePath($sName, $xPlugin = null) {

        $sResult = parent::GetTemplatePath($sName, $xPlugin);
        if (!is_dir($sResult)) {
            $sSubDir = $this->sDir;
            $this->sDir = 'notify';
            $sFileName = parent::GetTemplatePath($sName, $xPlugin);
            $this->sDir = $sSubDir;
            $sResult = $sFileName;
            if (!F::File_Exists($sFileName) && strpos(basename($sFileName), 'email.notify.') === 0) {
                if (F::File_Exists($sFileName = str_replace('email.notify.', 'notify.', $sFileName))) {
                    $sResult = $sFileName;
                } elseif (F::File_Exists($sFileName = str_replace('email.notify.', 'email.', $sFileName))) {
                    $sResult = $sFileName;
                }
            }
        }
        return $sResult;
    }
}

// EOF