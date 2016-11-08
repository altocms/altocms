<?php
/* -------------------------------------------------------
 * Aloha CMS -- Advanced Community Engine
 *
 * Dual License - BSD and GNU GPL v.2
 * See details on license.txt
 * --------------------------------------------------------
 * @link www.aloha-cms.com
 * @version v.0.5
 * @copyright Copyright: 2010 Aloha-CMS Team
 * @access public
 * @package Aloha
 * -------------------------------------------------------
 */

/**
 * Class Curl - wrapper under cURL functions
 *
 * Has a simple interface and supports external caching
 */
class Curl {

    const VERSION = '1.2.6';

    const CACHE_ENABLE = 1;
    const CACHE_TIME = 2;
    const CACHE_DIR = 3;

    protected $_hPointer = null;
    protected $_aOptions = array();
    protected $_aDefaults
        = array(
            CURLOPT_VERBOSE        => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => 0,
        );
    protected $_sUrl = '';

    protected $_sHttpMethod = 'GET';
    protected $_aHttpHeaders = array();
    protected $_aHttpParams = array();

    protected $_aResponseHeader = '';
    protected $_sLastRequest = '';

    protected $_bKeepAlive = true;

    protected $_nError = 0;
    protected $_sError = '';
    protected $_aInfo = array();

    protected $_aCacheCallbacks = null;
    protected $_aCacheOptions = array();
    protected $_aHistory = array();

    /**
     * Class constructor
     *
     * @param string|null $sUrl
     * @param array       $aOptions
     */
    public function __construct($sUrl = null, $aOptions = array()) {

        $this->_aCacheOptions = array(
            self::CACHE_ENABLE => false,
            self::CACHE_TIME   => 0,
            self::CACHE_DIR    => '',
        );
        $this->init($sUrl);
        if ($aOptions) {
            $this->_setOptions($aOptions);
        }
    }

    /**
     * Class destructor
     *
     */
    public function __destruct() {

        $this->close();
    }

    /**
     * @param $aOptions
     * @param $bReset
     */
    protected function _setOptions($aOptions, $bReset = false) {

        if ($bReset) {
            $this->_aOptions = array();
            $this->_aHttpParams = array();
            $this->_sHttpMethod = 'GET';
        }
        foreach ($aOptions as $nKey => $xVal) {
            $this->_aOptions[$nKey] = $xVal;
            if ($nKey == CURLOPT_URL) {
                $this->_sUrl = $xVal;
            } elseif ($nKey == CURLOPT_POST) {
                $this->_sHttpMethod = ($xVal ? 'POST' : 'GET');
            }
            if ($nKey == CURLOPT_POSTFIELDS) {
                $this->_aHttpParams = $xVal;
            }
        }
    }

    /**
     * Executes request
     *
     * @param string $sUrl
     * @param array  $aParams
     * @param string $sHttpMethod
     *
     * @return mixed|null|string
     */
    protected function _exec($sUrl, $aParams = array(), $sHttpMethod = null) {

        if (!$sUrl) {
            $sUrl = $this->_sUrl;
        }
        $aUrlInfo = parse_url($sUrl);
        $aOptions = $this->_aOptions;
        if ($aUrlInfo['scheme'] == 'http' || $aUrlInfo['scheme'] == 'https') {
            // this eval for HTTP-requests only
            if (!$sHttpMethod) {
                $sHttpMethod = $this->_sHttpMethod ? strtoupper($this->_sHttpMethod) : 'GET';
            } else {
                $sHttpMethod = strtoupper($sHttpMethod);
            }
            $this->_sLastRequest = $sHttpMethod . ' ' . $sUrl;
            $aHttpHeaders = $this->_aHttpHeaders;

            if ($sHttpMethod !== 'GET' && $sHttpMethod !== 'POST') {
                //$this->addHttpHeader('X-HTTP-Method-Override', $sHttpMethod);
                $aHttpHeaders[] = 'X-HTTP-Method-Override: ' . $sHttpMethod;
            }
            if ($this->_bKeepAlive) {
                //$this->addHttpHeader('Connection', 'keep-alive');
                $aHttpHeaders[] = 'Connection: keep-alive';
                if (is_numeric($this->_bKeepAlive)) {
                    //$this->addHttpHeader('Keep-Alive', intval($this->_bKeepAlive));
                    $aHttpHeaders[] = 'Keep-Alive: ' . intval($this->_bKeepAlive);
                }
            }
            if (!isset($aOptions[CURLOPT_HEADER])) {
                $aOptions[CURLOPT_HEADER] = 1;
            }
            // Default method is GET
            if ($sHttpMethod == 'GET') {
                $aOptions[CURLOPT_POST] = 0;
                $aOptions[CURLOPT_CUSTOMREQUEST] = 'GET';
            } elseif ($sHttpMethod == 'POST') {
                $aOptions[CURLOPT_CUSTOMREQUEST] = 'POST';
                $aOptions[CURLOPT_POST] = 1;
                $aOptions[CURLOPT_POSTFIELDS] = $aParams;
            } elseif ($sHttpMethod == 'PUT') {
                $aOptions[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $aOptions[CURLOPT_PUT] = 1;
                $aOptions[CURLOPT_POSTFIELDS] = $aParams;
            } elseif ($sHttpMethod == 'HEAD') {
                $aOptions[CURLOPT_NOBODY] = true;
            } else {
                $aOptions[CURLOPT_CUSTOMREQUEST] = $sHttpMethod;
            }
            $aOptions[CURLOPT_URL] = $sUrl;
            $aOptions[CURLOPT_HTTPHEADER] = $aHttpHeaders;
        } else {
            $this->_sLastRequest = $sUrl;
        }

        if ($bCache = $this->cacheEnable()) {
            $aData = $this->_getCache($aOptions);
        } else {
            $aData = null;
        }
        if ($aData && isset($aData['version']) && $aData['version'] == self::VERSION) {
            $sResponse = $aData['response'];
            $this->_aResponseHeader = $aData['header'];
            $this->_nError = $aData['error'];
            $this->_aInfo = $aData['info'];
        } else {
            if (!$this->_hPointer) {
                $this->_hPointer = curl_init();
            }

            curl_setopt_array($this->_hPointer, $aOptions);
            $sResponse = curl_exec($this->_hPointer);
            if ($sResponse === false) {
                $this->_nError = curl_errno($this->_hPointer);
                $this->_sError = curl_error($this->_hPointer);
            } else {
                $this->_nError = 0;
                $this->_sError = null;
            }
            $this->_aInfo = curl_getinfo($this->_hPointer);
            $this->_aResponseHeader = null;
            if (isset($aOptions[CURLOPT_HEADER]) && $aOptions[CURLOPT_HEADER]) {
                if ($this->_aInfo['header_size']) {
                    $this->_aResponseHeader = substr($sResponse, 0, $this->_aInfo['header_size']);
                    $sResponse = substr($sResponse, $this->_aInfo['header_size']);
                }
            }
            $this->_aHistory[] = array(
                'options' => $aOptions,
                'info' => $this->_aInfo,
            );

            if ($bCache) {
                $this->_setCache(
                    $aOptions, array(
                        'version'  => self::VERSION,
                        'header'   => $this->_aResponseHeader,
                        'response' => $sResponse,
                        'error'    => $this->_nError,
                        'info'     => $this->_aInfo,
                    )
                );
            }
            if (!$this->_bKeepAlive) {
                $this->close();
            }
        }

        return $sResponse;
    }

    /**
     * Calculates hash by options
     *
     * @param $aOptions
     *
     * @return string
     */
    protected function _hash($aOptions) {

        foreach ($aOptions as $xKey => $xVal) {
            if ($xVal === true) {
                $aOptions[$xKey] = 1;
            } elseif ($xVal === false) {
                $aOptions[$xKey] = 0;
            }
        }
        return md5(serialize($aOptions));
    }

    /**
     * Reads from cache
     *
     * @param $aOptions
     *
     * @return mixed|null|string
     */
    protected function _getCache($aOptions) {

        $sHash = $this->_hash($aOptions);
        if ($xFunc = $this->getCallbackFunc('get')) {
            $xResult = call_user_func_array($xFunc, array($sHash));
        } else {
            $xResult = $this->_getInternalCache($sHash);
        }
        return $xResult;
    }

    /**
     * Writes to cache
     *
     * @param $aOptions
     * @param $sData
     *
     * @return bool|int|mixed
     */
    protected function _setCache($aOptions, $sData) {

        $sHash = $this->_hash($aOptions);
        if ($xFunc = $this->getCallbackFunc('set')) {
            $xResult = call_user_func_array($xFunc, array($sHash, $sData));
        } else {
            $xResult = $this->_setInternalCache($sHash, $sData);
        }
        return $xResult;
    }

    /**
     * Internal cache getter
     *
     * @param $sHash
     *
     * @return null|string
     */
    public function _getInternalCache($sHash) {

        $sFileName = '';
        $sMask = $this->_aCacheOptions[self::CACHE_DIR] . $sHash . '-*.tmp';
        $aFiles = glob($sMask);
        if ($aFiles) {
            $nCurrentTime = time();
            foreach ($aFiles as $sFile) {
                list($sHash, $sTime) = explode('-', pathinfo($sFile, PATHINFO_FILENAME));
                if ($sTime < $nCurrentTime) {
                    @unlink($sFile);
                } else {
                    $sFileName = $sFile;
                    break;
                }
            }
        }
        if ($sFileName) {
            $sData = file_get_contents($sFileName);
            return unserialize($sData);
        }
        return null;
    }

    /**
     * Internal cache setter
     *
     * @param $sHash
     * @param $xData
     *
     * @return bool|int
     */
    public function _setInternalCache($sHash, $xData) {

        if ($this->_aCacheOptions[self::CACHE_TIME] && $this->_aCacheOptions[self::CACHE_DIR]) {
            $sFileName = $this->_aCacheOptions[self::CACHE_DIR] . $sHash . '-' . (time()
                    + $this->_aCacheOptions[self::CACHE_TIME]) . '.tmp';
            $xResult = file_put_contents($sFileName, serialize($xData));
            return $xResult;
        } else {
            return false;
        }
    }

    /**
     * Initialization
     *
     * Argument $sUrl may be missed
     *
     * @param string|array|null $sUrl
     * @param array             $aOptions
     */
    public function init($sUrl, $aOptions = array()) {

        if ($this->_hPointer) {
            $this->close();
        }

        if (is_array($sUrl)) {
            $aOptions = $sUrl;
            $sUrl = '';
        }
        $this->_setOptions($this->_aDefaults);
        if (!is_array($aOptions) || !$aOptions) {
            $aOptions = array();
        }
        if ($sUrl) {
            $aOptions[CURLOPT_URL] = $sUrl;
        }
        $this->_setOptions($aOptions);
    }

    /**
     * Sets parameters for request
     *
     * Parameters will be appended to URL for GET and added to request header for POST
     *
     * @param $aParams
     */
    public function setParams($aParams) {

        $this->_aHttpParams = (array)$aParams;
    }

    /**
     * Sets curl options
     *
     * @see http://www.php.net/manual/en/function.curl-setopt.php
     *
     * @param $aOptions
     */
    public function setOptions($aOptions) {

        if (!$this->_hPointer) {
            $this->init($aOptions);
        } else {
            $this->_setOptions($aOptions);
        }
    }

    /**
     * @param $aOptions
     */
    public function resetOptions($aOptions) {

        if ($this->_hPointer) {
            $this->close();
        }
        $this->_setOptions($aOptions, true);
    }

    public function setHttpHeaders($aHeaders) {

        $this->_aHttpHeaders = (array)$aHeaders;
    }

    public function addHttpHeader($sKey, $sValue = null) {

        if (!is_array($this->_aHttpHeaders)) {
            $this->_aHttpHeaders = array();
        }
        if (is_null($sValue)) {
            $this->_aHttpHeaders[] = $sKey;
        } else {
            $this->_aHttpHeaders[] = trim($sKey, ':') . ': ' . $sValue;
        }
    }

    /**
     * Turns on or off internal caching and sets options for one
     *
     * @param int   $nOption
     * @param mixed $xValue
     */
    public function setCacheOptions($nOption, $xValue = null) {

        if ($nOption === false) {
            $this->setCacheOff();
        } elseif ($nOption === true) {
            $this->setCacheOn();
        } elseif (is_array($nOption)) {
            foreach ($nOption as $sKey => $xVal) {
                if ($sKey == self::CACHE_DIR) {
                    $xVal = str_replace('/', DIRECTORY_SEPARATOR, $xVal);
                    $xVal = str_replace('\\', DIRECTORY_SEPARATOR, $xVal);
                    if (substr($xVal, -1) !== DIRECTORY_SEPARATOR) {
                        $xVal .= DIRECTORY_SEPARATOR;
                    }
                }
                $this->setCacheOptions($sKey, $xVal);
            }
        } else {
            if (is_numeric($nOption)) {
                $this->_aCacheOptions[intval($nOption)] = $xValue;
            } else {
                $this->_aCacheOptions[(string)$nOption] = $xValue;
            }
        }
    }

    /**
     * Returns cache options
     *
     * @return array
     */
    public function getCacheOptions() {

        return $this->_aCacheOptions;
    }

    /**
     * Turn cache on
     */
    public function setCacheOn() {

        if (!$this->getCallbackFunc()) {
            $this->setCacheFunc(array($this, '_setCache'), array($this, '_getCache'));
        }
        $this->_aCacheOptions[self::CACHE_ENABLE] = true;
    }

    /**
     * Turn cache off
     */
    public function setCacheOff() {

        $this->_aCacheOptions[self::CACHE_ENABLE] = false;
    }

    /**
     * Sets external cache function and turns on caching
     *
     * @param callable $xCallbackSet
     * @param callable $xCallbackGet
     */
    public function setCacheFunc($xCallbackSet = null, $xCallbackGet = null) {

        if (is_callable($xCallbackSet, true) && is_callable($xCallbackGet, true)) {
            $this->_aCacheCallbacks = array(
                'set' => $xCallbackSet,
                'get' => $xCallbackGet,
            );
            $this->setCacheOn();
        } elseif (!$xCallbackSet) {
            $this->_aCacheCallbacks = null;
            $this->setCacheOff();
        }
    }

    public function getCallbackFunc($sParam = null) {

        if ($sParam) {
            if (isset($this->_aCacheCallbacks[$sParam])) {
                return $this->_aCacheCallbacks[$sParam];
            } else {
                return null;
            }
        }
        return $this->_aCacheCallbacks;
    }

    public function cacheEnable() {

        return $this->_aCacheOptions[self::CACHE_ENABLE];
    }

    /**
     * Request by URL in 1st argument (or in options, it was set before)
     * Default method is GET
     *
     * @param string|null $sUrl
     * @param array       $aParams
     * @param string      $sMethod
     *
     * @return string|null
     */
    public function request($sUrl = null, $aParams = array(), $sMethod = null) {

        if (!$sUrl) {
            $sUrl = $this->_sUrl;
        }
        if (!$sMethod) {
            $sMethod = $this->_sHttpMethod;
        }
        // If the request is not the same as the previous one, then close the connection
        if ($this->_sLastRequest && $this->_sLastRequest != $this->_sHttpMethod . ' ' . $sUrl) {
            $this->close();
        }
        // If the connection is closed, then initialize it
        if (!$this->_hPointer) {
            $this->init($sUrl);
        }
        if (!$aParams) {
            $aParams = (array)$this->_aHttpParams;
        }

        if ($sMethod == 'GET') {
            if ($aParams) {
                if (!is_string($aParams)) {
                    $sParams = http_build_query((array)$aParams);
                } else {
                    $sParams = (string)$aParams;
                }
                $sUrl = trim($sUrl, '?&');
                if (strpos($sUrl, '?')) {
                    $sUrl .= '&' . $sParams;
                } else {
                    $sUrl .= '?' . $sParams;
                }
            }
            $aParams = null;
        } else {
            $aParams = (array)$aParams;
        }

        $xResult = $this->_exec($sUrl, $aParams, $sMethod);

        return $xResult;
    }

    /**
     * POST-request to the transferred (or previously given) URL
     *
     * @param null  $sUrl
     * @param array $aParams
     *
     * @return mixed
     */
    public function requestPost($sUrl = null, $aParams = array()) {

        $this->_sHttpMethod = 'POST';
        return $this->request($sUrl, $aParams);
    }

    /**
     * GET-request to the transferred (or previously given) URL
     *
     * @param null  $sUrl
     * @param array $aParams
     *
     * @return mixed
     */
    public function requestGet($sUrl = null, $aParams = array()) {

        $this->_sHttpMethod = 'GET';
        return $this->request($sUrl, $aParams);
    }

    /**
     * Get curl-information
     *
     * @return array
     */
    public function getInfo() {

        if ($this->_hPointer) {
            return curl_getinfo($this->_hPointer);
        } else {
            return $this->_aInfo;
        }
    }

    /**
     * @return int
     */
    public function getHttpCode() {

        return (isset($this->_aInfo['http_code']) ? $this->_aInfo['http_code'] : null);
    }

    /**
     * Get response header
     *
     * @param bool $bAsArray If true then return as an array otherwise returns as a plain text
     *
     * @return array|string
     */
    public function getResponseHeader($bAsArray = false) {

        $xResult = $this->_aResponseHeader;
        if ($bAsArray && $xResult) {
            $aLines = array_map('trim', explode("\n", $xResult));
            $aHeaders = array();
            foreach($aLines as $sLine) {
                if (strpos($sLine, ':')) {
                    list($sKey, $sVal) = array_map('trim', explode(':', $sLine, 2));
                    $aHeaders[$sKey] = $sVal;
                } else {
                    $aHeaders[] = $sLine;
                }
            }
            $xResult = $aHeaders;
        }
        return $xResult;
    }

    /**
     * Get error code
     *
     * @return int
     */
    public function getErrNo() {

        return $this->_nError;
    }

    /**
     * Get error text
     *
     * @return string
     */
    public function getError() {

        return $this->_sError;
    }

    /**
     * Close connection
     */
    public function close() {

        if ($this->_hPointer) {
            curl_close($this->_hPointer);
            $this->_hPointer = null;
        }
    }

}

// EOF