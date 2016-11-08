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
                    if (method_exists($this, $sMethod)) {
                        call_user_func_array(array($this, $sMethod), $aParams);
                    }
                }
            }

            // * Хардкодим некоторые параметры
            unset($this->entities['&']); // разрешаем в параметрах символ &
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
     * @param string       $tag
     * @param array|string $params
     *
     * @throws Exception
     */
    public function cfgAllowTagParams($tag, $params) {

        if (is_array($params) && count($params)) {
            foreach ($params as $attr => $rule) {
                if (is_array($rule) && isset($rule['#domain'])) {
                    $params[$attr]['#link'] = $params[$attr]['#domain'];
                    unset($params[$attr]['#domain']);
                }
            }
        }
        parent::cfgAllowTagParams($tag, $params);
    }

    /**
     * @param string $sText
     * @param array  $aErrors
     *
     * @return string
     */
    public function parse($sText, &$aErrors) {

        return parent::parse($sText, $aErrors);
    }

    /**
     * @return mixed|string
     */
    protected function makeText() {

        $sText = parent::makeText();
        if (!empty($this->aAutoReplace)) {
            if(isset($this->aAutoReplace['from']) && isset($this->aAutoReplace['to'])) {
                $sText = str_replace($this->aAutoReplace['from'], $this->aAutoReplace['to'], $sText);
            }
            if(isset($this->aAutoReplace['0']) && isset($this->aAutoReplace['1'])) {
                $sText = str_replace($this->aAutoReplace[0], $this->aAutoReplace[1], $sText);
            }
        }
        return $sText;
    }

}

// EOF
