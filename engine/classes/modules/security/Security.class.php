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
 * Модуль безопасности
 * Необходимо использовать перед обработкой отправленной формы:
 * <pre>
 * if (F::GetRequest('submit_add')) {
 *    E::ModuleSecurity()->ValidateSendForm();
 *    // далее код обработки формы
 *  ......
 * }
 * </pre>
 *
 * @package engine.modules
 * @since   1.0
 */
class ModuleSecurity extends Module {

    protected $sSecurityKeyName;
    protected $sSecurityKeyLen;

    /**
     * Initializes the module
     *
     */
    public function Init() {

        $this->sSecurityKeyName = 'ALTO_SECURITY_KEY';
        $this->sSecurityKeyLen = 32;
    }

    /**
     * Производит валидацию отправки формы/запроса от пользователя, позволяет избежать атаки CSRF
     *
     * @param   bool $bBreak - немедленно прекратить работу
     *
     * @return  bool
     */
    public function ValidateSendForm($bBreak = true) {

        if (!($this->ValidateSecurityKey())) {
            if ($bBreak) {
                die('Hacking attempt!');
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Проверка на соотвествие реферала
     *
     * @return bool
     */
    public function ValidateReferal() {

        if (isset($_SERVER['HTTP_REFERER'])) {
            $aUrl = parse_url($_SERVER['HTTP_REFERER']);
            if (strcasecmp($aUrl['host'], $_SERVER['HTTP_HOST']) == 0) {
                return true;
            } elseif (preg_match("/\." . quotemeta($_SERVER['HTTP_HOST']) . "$/i", $aUrl['host'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifies security key from argument or from request
     *
     * @param   string|null $sKey  - Security key for verifying. If it is ommited then it extracts from request
     *
     * @return  bool
     */
    public function ValidateSecurityKey($sKey = null) {

        if (!$sKey) {
            if (isset($_SERVER['HTTP_X_ALTO_AJAX_KEY'])) {
                $sKey = (string)$_SERVER['HTTP_X_ALTO_AJAX_KEY'];
            } else {
                if (!($sKey = F::GetRequestStr('security_key'))) {
                    // LS-compatibility
                    $sKey = F::GetRequestStr('security_ls_key');
                }
            }
        }
        return ($sKey == $this->GetSecurityKey());
    }

    /**
     * Returns security key from the session
     *
     * @return string
     */
    public function GetSecurityKey() {

        $sSecurityKey = E::ModuleSession()->Get($this->sSecurityKeyName);
        if (is_null($sSecurityKey)) {
            $sSecurityKey = $this->_generateSecurityKey();
        }
        return $sSecurityKey;
    }

    /**
     * Set security key in the session
     *
     * @return string
     */
    public function SetSecurityKey() {

        $sSecurityKey = $this->_generateSecurityKey();

        E::ModuleSession()->Set($this->sSecurityKeyName, $sSecurityKey);
        E::ModuleViewer()->Assign($this->sSecurityKeyName, $sSecurityKey);

        return $sSecurityKey;
    }

    /**
     * Generates security key for the current session
     *
     * @return string
     */
    protected function _generateSecurityKey() {

        // Сохраняем текущий ключ для ajax-запросов
        if (F::AjaxRequest() && ($sKey = E::ModuleSession()->Get($this->sSecurityKeyName))) {
            return $sKey;
        }
        if (Config::Get('module.security.randomkey')) {
            return F::RandomStr($this->sSecurityKeyLen);
        } else {
            //return md5(E::ModuleSession()->GetId().Config::Get('module.security.hash'));
            return md5($this->GetUniqKey() . $this->GetClientHash() . Config::Get('module.security.hash'));
        }
    }

    /**
     * Returns hash of salted string
     *
     * @param   string      $sData
     * @param   string|null $sType
     *
     * @return  string
     */
    public function Salted($sData, $sType = null) {

        $sSalt = Config::Get('security.salt_' . $sType);
        if ($sSalt !== false && !$sSalt) {
            $sSalt = $sType;
        }
        return F::DoSalt($sData, $sSalt);
    }

    /**
     * Checks salted hash and original string
     *
     * @param   string $sSalted    - "соленый" хеш
     * @param   string $sData      - проверяемые данные
     * @param   string $sType      - тип "соли"
     *
     * @return  bool
     */
    public function CheckSalted($sSalted, $sData, $sType = null) {

        if (substr($sSalted, 0, 3) == '0x:') {
            return $sSalted == $this->Salted($sData, $sType);
        } elseif (substr($sSalted, 0, 3) == 'Jx:' && $sType == 'pass') {
            list($sHash, $sSalt) = explode(':', substr($sSalted, 3), 2);
            if ($sHash && $sSalt && is_string($sData)) {
                return $sHash === md5($sData . $sSalt);
            }
            return false;
        } else {
            return $sSalted === md5($sData);
        }
    }

    /**
     * Calcs hash of user agent
     *
     * @return string
     */
    public function GetUserAgentHash() {

        $sUserAgent = ((isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '');
        return $this->Salted($sUserAgent, 'auth');
    }

    /**
     * Calcs hash of client
     *
     * @return string
     */
    public function GetClientHash() {

        $sClientHash = $this->GetUserAgentHash();
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $sClientHash .= $_SERVER['REMOTE_ADDR'];
        }
        //if ($sVizId = E::ModuleSession()->GetCookie('visitor_id')) $sClientHash .= $sVizId;

        return $this->Salted($sClientHash, 'auth');
    }

    /**
     * Generates depersonalized unique key of the site
     *
     * @return string
     */
    public function GenerateUniqKey() {

        $sData = serialize(Config::Get('path.root'));
        if (isset($_SERVER['SERVER_ADDR'])) {
            $sData .= $_SERVER['SERVER_ADDR'];
        }
        return $this->Salted(md5($sData), 'auth');
    }

    /**
     * Returns depersonalized unique key of the site
     *
     * @return string
     */
    public function GetUniqKey() {

        $sUniqKey = Config::Get('alto.uniq_key');
        if (!$sUniqKey) {
            $sUniqKey = $this->GenerateUniqKey();
            Config::Set('alto.uniq_key', $sUniqKey);
            Config::WriteCustomConfig(array('alto.uniq_key' => $sUniqKey));
        }
        return $sUniqKey;
    }

    /**
     * Shutdowns the module
     */
    public function Shutdown() {

        // nothing
    }
}

// EOF