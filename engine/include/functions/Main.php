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
 * Common functions for Alto CMS
 */
class AltoFunc_Main {

    static protected $sRandChars = '0123456789_ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    static protected $aMemSizeUnits = array('B', 'K', 'M', 'G', 'T', 'P');

    static public function StrUnderscore($sStr) {

        return strtolower(preg_replace('/([^A-Z])([A-Z])/', "$1_$2", $sStr));
    }

    static public function StrCamelize($sStr) {

        $aParts = explode('_', $sStr);
        $sCamelized = '';
        foreach ($aParts as $sPart) {
            $sCamelized .= ucfirst($sPart);
        }
        return $sCamelized;
    }

    static public function Str2Array($sStr, $sSeparator = ',', $bSkipEmpty = false) {

        return F::Array_Str2Array($sStr, $sSeparator, $bSkipEmpty);
    }

    static public function Str2ArrayInt($sStr, $sSeparator = ',', $bUnique = true) {

        return F::Array_Str2ArrayInt($sStr, $sSeparator, $bUnique);
    }

    static public function Val2Array($xVal, $sSeparator = ',', $bSkipEmpty = false) {

        return F::Array_Val2Array($xVal, $sSeparator, $bSkipEmpty);
    }

    /**
     * Возвращает строку со случайным набором символов
     *
     * @param int         $nLen   - длина строка
     * @param bool|string $sChars - только шестнадцатиричные символы [0-9a-f], либо заданный набор символов
     *
     * @return  string
     */
    static public function RandomStr($nLen = 32, $sChars = true) {

        $sResult = '';
        if ($sChars === true) {
            while (strlen($sResult) < $nLen) {
                $sResult .= md5(uniqid(md5(rand()), true));
            }
            if (strlen($sResult) > $nLen) {
                $sResult = substr($sResult, 0, $nLen);
            }
        } else {
            if (!is_string($sChars)) {
                $sChars = self::$sRandChars;
            }
            $nMax = strlen($sChars) - 1;
            while (strlen($sResult) < $nLen) {
                $sResult .= $sChars[rand(0, $nMax)];
            }
        }
        return $sResult;
    }

    /**
     * @param   float $nValue
     * @param   int   $nDecimal
     *
     * @return  string
     */
    static public function MemSizeFormat($nValue, $nDecimal = 0) {

        $aUnits = self::$aMemSizeUnits;
        $nIndex = 0;
        $nResult = intval($nValue);
        while ($nResult >= 1024) {
            $nIndex += 1;
            $nResult = $nResult / 1024;
        }
        if (isset($aUnits[$nIndex])) {
            return number_format($nResult, $nDecimal, '.', '\'') . '&nbsp;' . $aUnits[$nIndex];
        }
        return $nValue;
    }

    /**
     * Converts string as memory size into number
     *      '256'   => 256 - just number
     *      '2K'    => 2 * 1024 = 2048 - in KB
     *      '4 KB'  => 4 * 1024 = 4096 - in KB
     *      '1.5M'  => 1.5 * 1024 * 1024 = 1572864 - in MB
     *      '187X'  => 187 - invalid unit
     *
     * @param $sNum
     *
     * @return int|number
     */
    static public function MemSize2Int($sNum) {

        $nValue = floatval($sNum);
        if (!is_numeric($sChar = strtoupper(substr($sNum, -1)))) {
            if ($sChar == 'B') {
                $sChar = substr($sNum, -1);
            }
            if (($nIdx = array_search(strtoupper($sChar), self::$aMemSizeUnits)) !== false) {
                $nValue *= pow(1024, $nIdx);
            }
        }
        return intval($nValue);
    }

    /**
     * @param   mixed $xData
     *
     * @return  string
     */
    static public function JsonEncode($xData) {

        if (function_exists('json_encode')) {
            return json_encode($xData);
        }
        if (is_null($xData)) {
            return 'null';
        }
        if ($xData === false) {
            return 'false';
        }
        if ($xData === true) {
            return 'true';
        }
        if (is_scalar($xData)) {
            if (is_float($xData)) {
                // Always use "." for floats.
                return floatval(str_replace(",", ".", strval($xData)));
            }

            if (is_string($xData)) {
                static $jsonReplaces
                = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
                        array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
                return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $xData) . '"';
            } else {
                return $xData;
            }
        }
        $bList = true;
        for ($i = 0, reset($xData); $i < count($xData); $i++, next($xData)) {
            if (key($xData) !== $i) {
                $bList = false;
                break;
            }
        }
        $aResult = array();
        if ($bList) {
            foreach ($xData as $sVal) {
                $aResult[] = self::jsonEncode($sVal);
            }
            return '[' . join(',', $aResult) . ']';
        } else {
            foreach ($xData as $sKey => $sVal) {
                $aResult[] = self::jsonEncode($sKey) . ':' . self::jsonEncode($sVal);
            }
            return '{' . join(',', $aResult) . '}';
        }

    }

    /**
     * Returns all IP of current user
     *
     * @param   array|string|null   $aTrusted
     * @param   array|string|null   $aNonTrusted
     *
     * @return  array
     */
    static public function GetAllUserIp($aTrusted = null, $aNonTrusted = null) {

        if (!$aTrusted) {
            if (class_exists('Config', false)) {
                $aTrusted = (array)Config::Get('sys.ip.trusted');
            }
            if (!$aTrusted)
                $aTrusted = array(
                    'REMOTE_ADDR',
                    'HTTP_X_REAL_IP',
                    'HTTP_CLIENT_IP',
                    'HTTP_X_FORWARDED_FOR',
                    'HTTP_VIA',
                );
        } else {
            $aTrusted = (array)$aTrusted;
        }

        if (!$aNonTrusted) {
            $aNonTrusted = F::_getConfig('sys.ip.non_trusted', array());
        } else {
            $aNonTrusted = (array)$aNonTrusted;
        }

        $aIp = array();
        foreach ($aTrusted as $sParam) {
            if (isset($_SERVER[$sParam]) && (!$aNonTrusted || !in_array($sParam, $aNonTrusted))) {
                // sometimes IPs separated by space
                $sIp = str_replace(' ', ',', trim($_SERVER[$sParam]));
                if (strpos($sIp, ',') === false) {
                    // several IPs
                    $aData = explode(',', $sIp);
                    $aIp[$sParam] = '';
                    foreach ($aData as $sData) {
                        if ($sData && filter_var($sData, FILTER_VALIDATE_IP)) {
                            if ($aIp[$sParam]) {
                                $aIp[$sParam] .= ',';
                            }
                            $aIp[$sParam] .= $sData;
                        }
                    }
                    if (!$aIp[$sParam]) {
                        unset($aIp[$sParam]);
                    }
                } else {
                    // single IP
                    if ($sIp && filter_var($sIp, FILTER_VALIDATE_IP)) {
                        $aIp[$sParam] = $sIp;
                    }
                }
            }
        }
        if (!$aIp) {
            $sIp = F::_getConfig('sys.ip.default');
            if (!$sIp || !filter_var($sIp, FILTER_VALIDATE_IP)) {
                $sIp = '127.0.0.1';
            }
            $aIp['FAKE_ADDR'] = $sIp;
        }
        return $aIp;
    }

    /**
     * Returns user's IP
     *
     * @param   array|string|null   $aTrusted
     * @param   array|string|null   $aNonTrusted
     *
     * @return  string
     */
    static public function GetUserIp($aTrusted = null, $aNonTrusted = null) {

        $aIpParams = self::GetAllUserIp($aTrusted, $aNonTrusted);
        $aExcludeIp = (array)F::_getConfig('sys.ip.exclude', array('127.0.0.1', 'fe80::1', '::1'));
        if (F::_getConfig('sys.ip.exclude_server', true) && isset($_SERVER['SERVER_ADDR'])) {
            $aExcludeIp[] = $_SERVER['SERVER_ADDR'];
        }

        $bSeekBackward = F::_getConfig('sys.ip.backward', true);
        $bExcludePrivate = F::_getConfig('sys.ip.exclude_private', true);
        // collect all ip
        $aIp = array();
        foreach ($aIpParams as $sIp) {
            if (strpos($sIp, ',')) {
                $aSeveralIps = explode(',', $sIp);
                if ($bSeekBackward) {
                    $aSeveralIps = array_reverse($aSeveralIps);
                }
                $aIp = array_merge($aIp, $aSeveralIps);
            } else {
                $aIp[] = $sIp;
            }
        }
        foreach ($aIp as $sIp) {
            if (!in_array($sIp, $aExcludeIp) && (!$bExcludePrivate || filter_var($sIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))) {
                return $sIp;
            }
        }
        $sIp = array_shift($aIp);
        return $sIp;
    }

    static public function CheckVal($sValue, $sParam, $nMin = 1, $nMax = 100) {

        if (!is_scalar($sValue)) {
            return false;
        }
        switch ($sParam) {
            case 'id':
                if (preg_match('/^\d{' . $nMin . ',' . $nMax . '}$/', $sValue)) {
                    return true;
                }
                break;
            case 'float':
                if (preg_match('/^[\-]?\d+[\.]?\d*$/', $sValue)) {
                    return true;
                }
                break;
            case 'mail':
                return filter_var($sValue, FILTER_VALIDATE_EMAIL) !== false;
                break;
            case 'url':
                // ф-ция неверно понимает URL без протокола
                if ((filter_var($sValue, FILTER_VALIDATE_URL) !== false)
                    || (filter_var('http:' . $sValue, FILTER_VALIDATE_URL) !== false)
                ) {
                    return true;
                }
                break;
            case 'login':
                if (preg_match('/^[\da-z\_\-]{' . $nMin . ',' . $nMax . '}$/i', $sValue)) {
                    return true;
                }
                break;
            case 'md5':
                if (preg_match('/^[\da-z]{32}$/i', $sValue)) {
                    return true;
                }
                break;
            case 'password':
                if (mb_strlen($sValue, 'UTF-8') >= $nMin) {
                    return true;
                }
                break;
            case 'text':
                if (mb_strlen($sValue, 'UTF-8') >= $nMin && mb_strlen($sValue, 'UTF-8') <= $nMax) {
                    return true;
                }
                break;
            default:
                return false;
        }
        return false;
    }

    /**
     * Вовзвращает "соленый" хеш
     *
     * @param   mixed  $xData  - хешируемая переменная
     * @param   string $sSalt  - "соль"
     *
     * @return  string
     */
    static public function DoSalt($xData, $sSalt) {

        if (!is_string($xData)) {
            $sData = serialize($xData);
        }
        else {
            $sData = (string)$xData;
        }
        return '0x:' . F::DoHashe($sData . '::' . $sSalt);
    }

    /**
     * Вовзвращает "чистый" хеш
     *
     * @param   mixed $xData  - хешируемая переменная
     *
     * @return  string
     */
    static public function DoHashe($xData) {

        if (!is_string($xData)) {
            $sData = serialize($xData);
        }
        else {
            $sData = (string)$xData;
        }
        return (md5(sha1($sData)));
    }

    /**
     * Даст одинаковый результат на 32-х и 64-х системах
     *
     * @param $sData
     *
     * @return string
     */
    static public function Crc32($sData) {

        $nCrc = abs(crc32((string)$sData));
        if( $nCrc & 0x80000000){
            $nCrc ^= 0xffffffff;
            $nCrc += 1;
        }
        return $nCrc;
    }

    static public function VarCrc32($xData) {

        return self::Crc32(serialize($xData));
    }

    /**
     * Возвращает текст, обрезанный по заданное число символов
     *
     * @param   string  $sText
     * @param   int     $nLen
     * @param   string  $sPostfix
     *
     * @return  string
     */
    static public function TruncateText($sText, $nLen, $sPostfix = '') {

        if (mb_strlen($sText, 'UTF-8') > $nLen) {
            $sText = mb_substr($sText, 0, $nLen - mb_strlen($sPostfix)) . $sPostfix;
        }
        return $sText;
    }

    /**
     * Возвращает текст, обрезанный по заданное число слов
     *
     * @param   string $sText
     * @param   int    $nCountWords
     *
     * @return  string
     */
    static public function CutText($sText, $nCountWords) {

        $aWords = preg_split('#[\s\r\n]+#um', $sText);
        if ($nCountWords < count($aWords)) {
            $aWords = array_slice($aWords, 0, $nCountWords);
        }
        return join(' ', $aWords);
    }

    /**
     * Аналог serialize() с контролем CRC32
     *
     * @param $xData
     *
     * @return string
     */
    static public function Serialize($xData) {

        $sData = serialize($xData);
        $sCrc32 = dechex(crc32($sData));
        return $sCrc32 . '|' . $sData;

    }

    /**
     * Аналог unserialize() с контролем CRC32
     *
     * @param $sData
     * @param $xDefaultOnError
     *
     * @return mixed|null
     */
    static public function Unserialize($sData, $xDefaultOnError = null) {

        if (is_string($sData) && strpos($sData, '|')) {
            list($sCrc32, $sData) = explode('|', $sData);
            if ($sCrc32 && $sData && $sCrc32 == dechex(crc32($sData))) {
                $xData = @unserialize($sData);
                return $xData;
            }
        }
        return $xDefaultOnError;
    }

    static public function IpRange($sIp) {

        $aIp = explode('.', $sIp) + array(0, 0, 0, 0);
        $aIp = array_map('intval', $aIp);

        if ($aIp[0] < 1 || $aIp[0] > 254) {
            // error - first part cannot be empty
            return array('0.0.0.0', '255.255.255.255');
        } else {
            $aIp1 = array();
            $aIp2 = array();
            foreach ($aIp as $nPart) {
                if ($nPart < 0 || $nPart >= 255) {
                    $aIp1[] = 0;
                } else {
                    $aIp1[] = $nPart;
                }
            }
            foreach ($aIp as $nPart) {
                if ($nPart <= 0 || $nPart > 255) {
                    $aIp2[] = 255;
                } else {
                    $aIp2[] = $nPart;
                }
            }
            return array(implode('.', $aIp1), implode('.', $aIp2));
        }
    }

    /**
     * Преобразует интервал в число секунд
     *
     * @param   string  $sInterval  - значение интервала по спецификации ISO 8601 или в человекочитаемом виде
     *
     * @return  int|null
     */
    static public function ToSeconds($sInterval) {

        if (is_numeric($sInterval)) {
            return intval($sInterval);
        }
        if (!is_string($sInterval)) {
            return null;
        }
        if (!class_exists('DateTimeInterval', false)) {
            if (!F::File_IncludeLib('DateTime/DateTimeInterval.php') || !class_exists('DateTimeInterval', false)) {
                return null;
            }
        }
        $oInterval = new DateTimeInterval($sInterval);
        return $oInterval->Seconds();
    }

    static public function DateTimeAdd($sDate, $sInterval) {

        $date = new DateTime($sDate);
        $date->add(new DateInterval('PT' . self::ToSeconds($sInterval) . 'S'));
        return $date->format('Y-m-d H:i:s');
    }

    static public function DateDiffSeconds($sDate1, $sDate2) {
        $oDatetime1 = date_create($sDate1);
        $oDatetime2 = date_create($sDate2);
        $nDiff = $oDatetime2->getTimestamp() - $oDatetime1->getTimestamp();
        return intval($nDiff);
    }

    static public function Now() {

        return date('Y-m-d H:i:s');
    }

    /**
     * Транслитерация строки
     *
     * @param   string      $sText
     * @param   string|bool $xLang  - true - использовать установки текущего языка/локали
     *                              - false - использовать системную локаль
     *                              - <string> - использовать установки заданного языка/локали
     *
     * @return  string
     */
    static public function Translit($sText, $xLang = true) {

        if ($xLang === true) {
            if (class_exists('ModuleLang', false)) {
                $xLang = E::Lang_GetLang();
            } elseif (class_exists('Config', false)) {
                $xLang = Config::Get('lang.current');
            } else {
                $xLang = false;
            }
        }

        $sText = strtolower($sText);
        if ($xLang !== false) {
            $aChars = UserLocale::getLocale($xLang, 'translit');
            if ($aChars) {
                $sText = str_replace(array_keys($aChars), array_values($aChars), $sText);
            }
            $sText = preg_replace('/[\-]{2,}/', '-', $sText);
        }
        $sResult = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $sText);
        // В некоторых случаях может возвращаться пустая строка
        if (!$sResult) {
            $sResult = $sText;
        }
        return $sResult;
    }

    static public function TranslitUrl($sText, $xLang = true) {

        $aSymbols = array(
            "_" => "-", "'" => "", "`" => "", "^" => "", " " => "-", '.' => '', ',' => '', ':' => '', '"' => '',
            "'" => '', '<' => '', '>' => '', '«' => '', '»' => '', ' ' => '-', '(' => '-', ')' => '-'
        );
        $sText = mb_strtolower(self::Translit($sText, $xLang), 'utf-8');
        $sText = str_replace(array_keys($aSymbols), array_values($aSymbols), $sText);
        $sText = preg_replace('/[^a-z0-9\-]/i', '-', $sText);
        $sText = preg_replace('/\s/', '-', $sText);
        $sText = preg_replace('/[\-]{2,}/', '-', $sText);
        $sText = trim($sText, '-');
        return $sText;
    }

    /**
     * URL encoding with double encoding for slashes
     *
     * @param string $sStr
     *
     * @return string
     */
    static function UrlEncode($sStr) {

        $s = urlencode($sStr);
        $s = str_replace(array('%2F', '%5C'), array('%252F', '%255C'), $s);
        return str_replace(array('%2F', '%5C'), array('%252F', '%255C'), urlencode($sStr));
    }

    /**
     * URL encoding with double encoding for slashes
     *
     * @param string $sStr
     *
     * @return string
     */
    static function UrlDecode($sStr) {

        return urldecode(str_replace(array('%252F', '%255C'), array('%2F', '%5C'), $sStr));
    }

}

// EOF