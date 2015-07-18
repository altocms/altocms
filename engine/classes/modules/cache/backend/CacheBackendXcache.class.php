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
F::IncludeFile(LS_DKCACHE_PATH . 'Zend/Cache/Backend/Xcache.php');

/**
 * Class CacheBackendXcache
 *
 * Кеш на основе XCache
 */
class CacheBackendXcache extends Dklab_Cache_Backend_TagEmuWrapper implements ICacheBackend {

    static public function IsAvailable() {

        return extension_loaded('xcache');
    }

    static public function Init($sFuncStats) {

        if (!self::IsAvailable()) {
            return false;
        }

        $aConfigMem = Config::Get('xcache');

        $oCahe = new Zend_Cache_Backend_Xcache(is_array($aConfigMem) ? $aConfigMem : array());
        return new self(new Dklab_Cache_Backend_Profiler($oCahe, $sFuncStats));
    }

    public function IsMultiLoad() {

        return false;
    }

    public function IsСoncurrent() {

        return true;
    }

    public function Remove($sName) {

        return parent::remove($sName);
    }

    public function Clean($sMode = Zend_Cache::CLEANING_MODE_ALL, $aTags = array()) {

        return parent::clean($sMode, $aTags);
    }

}

// EOF