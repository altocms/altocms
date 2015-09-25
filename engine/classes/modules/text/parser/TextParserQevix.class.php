<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class TextParserQevix extends Qevix implements ITextParser {

    protected $aAutoReplace = array();
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
        $aConfig = C::Get('qevix.' . $sType);
        if (is_array($aConfig)) {
            foreach ($aConfig as $sMethod => $aExec) {
                if ($sMethod == 'cfgSetAutoReplace') {
                    $this->aAutoReplace = $aExec;
                    continue;
                }
                foreach ($aExec as $aParams) {
                    call_user_func_array(array($this, $sMethod), $aParams);
                }
            }

            // * Хардкодим некоторые параметры
            unset($this->entities1['&']); // разрешаем в параметрах символ &
            if (C::Get('view.noindex') && isset($this->tagsRules['a'])) {
                $this->cfgSetTagParamDefault('a', 'rel', 'nofollow', true);
            }
        }

        if (C::Get('module.text.char.@')) {
            $this->cfgSetSpecialCharCallback('@', array(E::ModuleText(), 'CallbackTagAt'));
        }
        if ($aData = C::Get('module.text.autoreplace')) {
            $this->aAutoReplace = array(
                array_keys($aData),
                array_values($aData),
            );
        }
    }

    /**
     * @param string   $sTag
     * @param callable $xCallBack
     *
     * @throws Exception
     */
    public function tagBuilder($sTag, $xCallBack) {

        $this->cfgSetTagBuildCallback($sTag, $xCallBack);
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

    protected function makeText() {

        $sText = parent::makeText();
        if (!empty($this->aAutoReplace)) {
            $sText = str_replace($this->aAutoReplace[0], $this->aAutoReplace[1], $sText);
        }
        return $sText;
    }
}

// EOF
