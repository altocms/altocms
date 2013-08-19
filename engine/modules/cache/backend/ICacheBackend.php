<?php

F::IncludeFile(LS_DKCACHE_PATH . 'Zend/Cache.php');
F::IncludeFile(LS_DKCACHE_PATH . 'Zend/Cache/Backend/Interface.php');

interface ICacheBackend {

    /**
     * @param $sName
     *
     * @return mixed
     */
    public function Load($sName);

    /**
     * @param $data
     * @param $sName
     * @param $aTags
     * @param $nTimeLife
     *
     * @return mixed
     */
    public function Save($data, $sName, $aTags = array(), $nTimeLife = false);

    /**
     * @param $sName
     *
     * @return mixed
     */
    public function Remove($sName);

    /**
     * @param $sMode
     * @param $aTags
     *
     * @return mixed
     */
    public function Clean($sMode = Zend_Cache::CLEANING_MODE_ALL, $aTags = array());

    /**
     * @return bool
     */
    public function IsMultiLoad();

    /**
     * @return bool
     */
    public function IsAvailable();

    /**
     * @return bool
     */
    public function IsСoncurrent();

}

// EOF