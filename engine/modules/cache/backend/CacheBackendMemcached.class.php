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

    static public function init($sFuncStats) {

        $aConfigMem = Config::Get('memcache');

        $oCache = new Dklab_Cache_Backend_MemcachedMultiload($aConfigMem);
        return new self(new Dklab_Cache_Backend_Profiler($oCache, $sFuncStats));
    }

    public function IsMultiLoad() {
        return true;
    }

    public function IsAvailable() {

        return extension_loaded('memcache');
    }

    public function IsСoncurrent() {
        return true;
    }

}

// EOF