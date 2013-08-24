<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

F::IncludeFile('./ICacheBackend.php');

/**
 * Class CacheBackendFile
 *
 * Кеш в памяти
 *
 * Рекомендуется для хранения небольших объемов данных, к которым возможно частое обращение
 * в течение обработки одного запроса. Может привести к увеличению требуемой памяти, но самый быстрый
 * из всех видов кеша
 *
 * Крайне НЕ рекомендуется использовать, как кеш всего приложения!!!
 */

class CacheBackendTmp extends Dklab_Cache_Backend_Profiler implements ICacheBackend {

    static protected $aStore = array();

    public function __construct() {

    }

    static public function init($sFuncStats) {

        self::$aStore = array();
        $oCache = new self();
        return new self($oCache, $sFuncStats);
    }

    public function IsMultiLoad() {

        return false;
    }

    public function IsAvailable() {

        return true;
    }

    public function IsСoncurrent() {

        return false;
    }

    public function Load($sName, $bNotTestCacheValidity = false) {

        if (isset(self::$aStore[$sName])) {
            $aData = self::$aStore[$sName];
            if (is_array($aData) && array_key_exists('data', $aData)) {
                return F::Unserialize($aData['data'], false);
            }
        }
        return false;
    }

    public function Save($xData, $sName, $aTags = array(), $nTimeLife = false) {

        $xValue = F::Serialize($xData);
        self::$aStore[$sName] = array(
            'tags' => (array)$aTags,
            'data' => $xValue,
            'time' => $nTimeLife ? time() + intval($nTimeLife) : false,
        );
    }

    public function Remove($sName) {

        if (isset(self::$aStore[$sName])) {
            unset(self::$aStore[$sName]);
        }
    }

    public function Clean($sMode = Zend_Cache::CLEANING_MODE_ALL, $aTags = array()) {

        if ($sMode == Zend_Cache::CLEANING_MODE_ALL) {
            // Удаление всех значений
            self::$aStore = array();
        } elseif ($sMode == Zend_Cache::CLEANING_MODE_OLD) {
            // Удаление устаревших значений
            $nTime = time();
            foreach (self::$aStore as $sName=>$aData) {
                if ($aData['time'] && $aData['time'] < $nTime) {
                    unset(self::$aStore[$sName]);
                }
            }
        } elseif ($aTags) {
            // Удаление по тегам
            foreach (self::$aStore as $sName=>$aData) {
                if (Zend_Cache::CLEANING_MODE_MATCHING_TAG && $aData['tags'] && array_intersect($aTags, $aData['tags'])) {
                    unset(self::$aStore[$sName]);
                } elseif (Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG && $aData['tags'] && !array_intersect($aTags, $aData['tags'])) {
                    unset(self::$aStore[$sName]);
                }
            }
        }
    }

    public function setDirectives($directives) {

    }

    public function test($id) {

    }

}

// EOF