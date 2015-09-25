<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class TextParserJevix extends Jevix implements ITextParser {

    /**
     * @param string $sType
     * @param bool   $bClear
     *
     * @throws Exception
     */
    public function loadConfig($sType = 'default', $bClear = true) {

        if ($bClear) {
            $this->tagsRules = array();
        }
        $aConfig = C::Get('jevix.' . $sType);
        if (is_array($aConfig)) {
            foreach ($aConfig as $sMethod => $aExec) {
                foreach ($aExec as $aParams) {
                    if (in_array(
                        strtolower($sMethod),
                        array_map('strtolower', array('cfgSetTagCallbackFull', 'cfgSetTagCallback'))
                    )
                    ) {
                        if (isset($aParams[1][0]) && $aParams[1][0] == '_this_') {
                            $aParams[1][0] = E::ModuleText();
                        }
                    }
                    call_user_func_array(array($this, $sMethod), $aParams);
                }
            }

            // * Хардкодим некоторые параметры
            unset($this->entities1['&']); // разрешаем в параметрах символ &
            if (C::Get('view.noindex') && isset($this->tagsRules['a'])) {
                $this->cfgSetTagParamDefault('a', 'rel', 'nofollow', true);
            }
        }
    }

    /**
     * @param string   $sTag
     * @param callable $xCallBack
     *
     * @throws Exception
     */
    public function tagBuilder($sTag, $xCallBack) {

        $this->cfgSetTagCallbackFull($sTag, $xCallBack);
    }

    /**
     * @param string $sText
     * @param array  $aErrors
     *
     * @return string
     */
    public function Parse($sText, &$aErrors) {

        return parent::parse($sText, $aErrors);
    }

}

// EOF
