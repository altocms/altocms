<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

/**
 * Модуль безопасности
 * Необходимо использовать перед обработкой отправленной формы:
 * <pre>
 * if (F::GetRequest('submit_add')) {
 *    $this->Security_ValidateSendForm();
 *    // далее код обработки формы
 *  ......
 * }
 * </pre>
 *
 * @package engine.modules
 * @since   1.0
 */
class ModuleSecurity extends Module {

    protected $sKeyName;
    protected $sKeyLen;

    /**
     * Инициализируем модуль
     *
     */
    public function Init() {

        $this->sKeyName = 'ALTO_SECURITY_KEY';
        $this->sKeyLen = 32;
    }

    /**
     * Производит валидацию отправки формы/запроса от пользователя, позволяет избежать атаки CSRF
     *
     * @param   bool $bBreak - немедленно прекратить работу
     *
     * @return  bool
     */
    public function ValidateSendForm($bBreak = true) {

        if (!($this->ValidateSessionKey())) {
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
     * Проверяет наличие security-ключа в сессии
     *
     * @param   string|null $sCode  - Код для проверки, если нет то берется из реквеста
     *
     * @return  bool
     */
    public function ValidateSessionKey($sCode = null) {

        if (!$sCode) {
            if (isset($_SERVER['HTTP_X_ALTO_AJAX_KEY'])) {
                $sCode = (string)$_SERVER['HTTP_X_ALTO_AJAX_KEY'];
            } else {
                if (!($sCode = F::GetRequestStr('security_key'))) {
                    $sCode = F::GetRequestStr('security_ls_key');
                }
            }
        }
        return ($sCode == $this->GetSessionKey());
    }

    public function GetSessionKey() {

        $sSessionKey = $this->Session_Get($this->sKeyName);
        if (is_null($sSessionKey)) {
            $sSessionKey = $this->_generateSessionKey();
        }
        return $sSessionKey;
    }

    /**
     * Устанавливает security-ключ в сессию
     *
     * @return string
     */
    public function SetSessionKey() {

        $sSessionKey = $this->_generateSessionKey();

        $this->Session_Set($this->sKeyName, $sSessionKey);
        $this->Viewer_Assign($this->sKeyName, $sSessionKey);

        return $sSessionKey;
    }

    /**
     * Генерирует текущий security-ключ
     *
     * @return string
     */
    protected function _generateSessionKey() {

        // Сохраняем текущий ключ для ajax-запросов
        if (F::AjaxRequest() && ($sKey = $this->Session_Get($this->sKeyName))) {
            return $sKey;
        }
        if (Config::Get('module.security.randomkey')) {
            return F::RandomStr($this->sKeyLen);
        } else {
            //return md5($this->Session_GetId().Config::Get('module.security.hash'));
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
                return $sHash == md5($sData . $sSalt);
            }
            return false;
        } else {
            return $sSalted == md5($sData);
        }
    }

    public function GetUserAgentHash() {

        $sUserAgent = ((isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '');
        return $this->Salted($sUserAgent, 'auth');
    }

    public function GetClientHash() {

        $sClientHash = $this->GetUserAgentHash();
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $sClientHash .= $_SERVER['REMOTE_ADDR'];
        }
        //if ($sVizId = $this->Session_GetCookie('visitor_id')) $sClientHash .= $sVizId;

        return $this->Salted($sClientHash, 'auth');
    }

    public function GenerateUniqKey() {

        $sData = serialize(Config::Get('path.root'));
        if (isset($_SERVER['SERVER_ADDR'])) {
            $sData .= $_SERVER['SERVER_ADDR'];
        }
        return $this->Salted(md5($sData), 'auth');
    }

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
     * Завершение модуля
     */
    public function Shutdown() {
        // перенесено во Viewer, т.к. новый ключ нужно задавать только при рендеринге
        //$this->SetSessionKey();
    }
}

// EOF