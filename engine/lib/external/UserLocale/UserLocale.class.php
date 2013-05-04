<?php
/* -------------------------------------------------------
 * Aloha CMS -- Advanced Community Engine
 *
 * Dual License - BSD and GNU GPL v.2
 * See details on license.txt
 * --------------------------------------------------------
 * Engine based on LiveStreet v.0.4.1 by Maxim Mzhelskiy
 * from http://www.livestreetcms.com/
 * --------------------------------------------------------
 * @link www.aloha-cms.com
 * @version v.0.5
 * @copyright Copyright: 2010 Aloha-CMS Team
 * @access public
 * @package Aloha
 * -------------------------------------------------------
 */

class UserLocale {

    static $sDefaultLanguage = 'ru';
    static $aDefaultLocale = array(
        'name' => 'Russian',
        'xml:lang' => 'ru',
        'lang' => 'ru',
        'charset' => 'utf-8',
        'locale' => 'ru_RU.UTF-8',
        'timezone' => 'Europe/Moscow',
        'date_format' => array(
            'full' => 'd.m.Y',
        ),
        'week_start' => 1,
    );
    static $sCurrentLanguage = 'ru';
    static $aLocales = array();

    static function initLocales($aLangs = null) {
        if (!$aLangs) {
            self::$aLocales[self::$aDefaultLocale] = self::$aDefaultLocale;
        } else {
            if (!is_array($aLangs)) $aLangs = array($aLangs);
            foreach ($aLangs as $sLang) {
                $locale = self::$aDefaultLocale;
                $sFileName = __DIR__ . '/i18n/' . $sLang . '.php';
                if (file_exists($sFileName)) {
                    include $sFileName;
                    self::$aLocales[$sLang] = $locale;
                }
            }
        }
    }

    static function getLocale($sLang = '', $sItem = '') {
        $xResult = self::$aLocales;
        if (!$sLang) $sLang = self::$sCurrentLanguage;
        if ($sLang AND isset($xResult[$sLang])) $xResult = $xResult[$sLang];
        if ($sItem AND isset($xResult[$sItem])) $xResult = $xResult[$sItem];
        return $xResult;
    }

    static function setLocaleSys($sLocale) {
        if (is_string($sLocale) AND strpos($sLocale, ',')) {
            $aLocales = array_map('trim', explode(',', $sLocale));
            return setlocale(LC_ALL, $aLocales);
        }
        return setlocale(LC_ALL, $sLocale);
    }

    static function setLocale($sLang = null, $aParam = array()) {
        if (!$sLang) $sLang = self::$aDefaultLocale;
        if (!isset(self::$aLocales[$sLang])) self::initLocales($sLang);
        if (isset(self::$aLocales[$sLang])) {
            // Set locale
            if (isset($aParam['locale']) AND $aParam['locale'])
                self::$aLocales[$sLang]['locale'] = $aParam['locale'];
            self::setLocaleSys(LC_ALL, self::$aLocales[$sLang]['locale']);
            // Set timezone
            if (isset($aParam['timezone']) AND $aParam['timezone'])
                self::$aLocales[$sLang]['timezone'] = $aParam['timezone'];
            date_default_timezone_set(self::$aLocales[$sLang]['timezone']);
        }
        self::$sCurrentLanguage = $sLang;
    }

    static function getAvailableLanguages($bScanDir = false) {
        $aLanguages = array();
        $sFile = __DIR__ . '/i18n/_languages.php';
        if (is_file($sFile)) {
            $aLanguages = include($sFile);
            if (!$aLanguages || !is_array($aLanguages)) {
                $aLanguages = array();
            }
        }
        if ($bScanDir) {
            $aFiles = glob(__DIR__ . '/i18n/*.php');
            foreach ($aFiles as $sFile) {
                if (preg_match('/^([a-z]\w+)\.php$/', basename($sFile), $m)) {
                    $locale = array();
                    if (isset($aLanguages[$m[1]])) {
                        $locale = $aLanguages[$m[1]];
                        include $sFile;
                        $aLanguages[$m[1]] = $locale;
                    } else {
                        include $sFile;
                        if (!isset($locale['lang'])) {
                            $locale['lang'] = $m[1];
                        }
                        $aLanguages[$locale['lang']] = $locale;
                    }
                }
            }
        }
        return $aLanguages;
    }

}

// EOF