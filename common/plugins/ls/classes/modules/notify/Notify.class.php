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
        if (!file_exists($sResult)) {
            if ($xPlugin) {
                $sResult = $this->_getTemplatePathByPlugin($sName, $xPlugin, 'notify');
            }
            if (!file_exists($sResult) && strpos($sResult, '/notify/russian/email.')) {
                $sLsTemplate = str_replace('/notify/russian/email.', '/notify/russian/', $sResult);
                if (file_exists($sLsTemplate)) {
                    $sResult = $sLsTemplate;
                }
            }
        }
        return $sResult;
    }

    protected function _getTemplatePathByPlugin($sName, $xPlugin, $sSubDir) {

        $sSavedSubDir = $this->sDir;
        $this->sDir = $sSubDir;
        $sFileName = parent::GetTemplatePath($sName, $xPlugin);
        $this->sDir = $sSavedSubDir;
        $sResult = $sFileName;
        if (!F::File_Exists($sFileName)) {
            if (strpos(basename($sFileName), 'email.notify.') === 0) {
                if (F::File_Exists($sFileName = str_replace('email.notify.', 'notify.', $sFileName))) {
                    $sResult = $sFileName;
                } elseif (F::File_Exists($sFileName = str_replace('email.notify.', 'email.', $sFileName))) {
                    $sResult = $sFileName;
                } elseif (F::File_Exists($sFileName = str_replace('email.notify.', '', $sFileName))) {
                    $sResult = $sFileName;
                }
            } elseif (strpos(basename($sFileName), 'notify.notify.') === 0) {
                if (F::File_Exists($sFileName = str_replace('notify.notify.', 'notify.', $sFileName))) {
                    $sResult = $sFileName;
                } elseif (F::File_Exists($sFileName = str_replace('notify.notify.', 'email.', $sFileName))) {
                    $sResult = $sFileName;
                } elseif (F::File_Exists($sFileName = str_replace('notify.notify.', '', $sFileName))) {
                    $sResult = $sFileName;
                }
            }
        }
        return $sResult;
    }
}

// EOF