<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * Модуль для статических страниц
 *
 * @package modules.captcha
 * @since   1.1
 */
class ModuleCaptcha extends Module {

    const ERR_KEYSTRING_EMPTY = 1;
    const ERR_KEYSTRING_NOT_STR = 2;
    const ERR_KEYSTRING_NOT_DEFINED = 3;
    const ERR_KEYSTRING_NOT_VALID = 4;
    const ERR_KEYSTRING_UNKNOWN = 9;

    protected $sKeyName = 'captcha_keystring';

    public function Init() {

    }

    /**
     * @return string
     */
    public function GetKeyName() {

        return $this->sKeyName;
    }

    /**
     * @param string $sKeyname
     */
    public function SetKeyName($sKeyname) {

        $this->sKeyName = $sKeyname;
    }

    /**
     * @param string $sKeyName
     *
     * @return ModuleCaptcha_EntityCaptcha
     */
    public function GetCaptcha($sKeyName = null) {

        /** @var ModuleCaptcha_EntityCaptcha $oCaptcha */
        $oCaptcha = E::GetEntity('Captcha_Captcha');
        if (!$sKeyName) {
            $sKeyName = $this->sKeyName;
        }
        E::ModuleSession()->Set($sKeyName, $oCaptcha->getKeyString());

        return $oCaptcha;
    }

    /**
     * @param string $sKeyString
     * @param string $sKeyName
     *
     * @return int
     */
    public function Verify($sKeyString, $sKeyName = null) {

        $iResult = 0;
        if (empty($sKeyString)) {
            $iResult = static::ERR_KEYSTRING_EMPTY;
        } elseif (!is_string($sKeyString)) {
            $iResult = static::ERR_KEYSTRING_NOT_STR;
        } else {
            if (!$sKeyName) {
                $sKeyName = $this->sKeyName;
            }
            $sSavedString = E::Session_Get($sKeyName);
            E::Session_Drop($sKeyName);
            if (empty($sSavedString) || !is_string($sSavedString)) {
                $iResult = static::ERR_KEYSTRING_NOT_DEFINED;
            } elseif ($sSavedString != $sKeyString) {
                $iResult = static::ERR_KEYSTRING_NOT_VALID;
            }
        }
        return $iResult;
    }
}

// EOF