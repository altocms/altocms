<?php

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
    public function Clean($sMode, $aTags);

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