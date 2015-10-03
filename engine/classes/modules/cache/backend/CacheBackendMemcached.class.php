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
F::IncludeFile(LS_DKCACHE_PATH . 'Zend/Cache/Backend/Memcached.php');

/**
 * Class CacheBackendMemcached
 *
 * Кеш на основе Memcached
 */
class CacheBackendMemcached extends Dklab_Cache_Backend_TagEmuWrapper implements ICacheBackend {

    static public function IsAvailable() {

        return extension_loaded('memcache');
    }

    static public function Init($sFuncStats) {

        if (!self::IsAvailable()) {
            return false;
        }

        $aConfigMem = C::Get('memcache');

        $oCache = new Dklab_Cache_Backend_MemcachedMultiload($aConfigMem);
        return new self(new Dklab_Cache_Backend_Profiler($oCache, $sFuncStats));
    }

    public function IsMultiLoad() {

        return true;
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