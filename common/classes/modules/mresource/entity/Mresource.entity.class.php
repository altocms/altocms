<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class ModuleMresource_EntityMresource extends Entity {

    public function __construct($aParam = null) {

        if ($aParam && $aParam instanceOf ModuleUploader_EntityItem) {
            $oUploaderItem = $aParam;
            $aParam = $oUploaderItem->getAllProps();
        } else {
            $oUploaderItem = null;
        }
        parent::__construct($aParam);
        if ($oUploaderItem) {
            $this->SetUrl($oUploaderItem->GetUrl());
            if ($oUploaderItem->GetFile()) {
                $this->SetFile($oUploaderItem->GetFile());
            }
            $this->SetType($oUploaderItem->getProp('is_image') ? ModuleMresource::TYPE_IMAGE : 0);
        }
    }

    /**
     * Checks if resource is external link
     *
     * @return bool
     */
    public function IsLink() {

        return (bool)$this->GetLink();
    }

    /**
     * Checks if resource is local file
     *
     * @return bool
     */
    public function IsFile() {

        return !$this->IsLink() && $this->GetHashFile();
    }

    public function IsType($nMask) {

        return $this->getPropMask('type', $nMask);
    }

    /**
     * Checks if resource is image
     *
     * @return bool
     */
    public function IsImage() {

        return $this->IsType(ModuleMresource::TYPE_IMAGE);
    }

    /**
     * Checks if resource can be deleted
     *
     * @return bool
     */
    public function CanDelete() {

        return (bool)$this->getProp('candelete');
    }

    /**
     * Sets full url of resource
     *
     * @param $sUrl
     */
    public function SetUrl($sUrl) {

        if (substr($sUrl, 0, 1) === '@') {
            $sPathUrl = substr($sUrl, 1);
            $sUrl = F::File_RootUrl() . $sPathUrl;
        } else {
            $sPathUrl = F::File_LocalUrl($sUrl);
        }
        if ($sPathUrl) {
            // Сохраняем относительный путь
            $this->SetPathUrl('@' . trim($sPathUrl, '/'));
            if (!$this->getPathFile()) {
                $this->SetFile(F::File_Url2Dir($sUrl));
            }
        } else {
            // Сохраняем абсолютный путь
            $this->SetPathUrl($sUrl);
        }
        if (is_null($this->GetPathFile())) {
            if (is_null($this->GetLink())) {
                $this->SetLink(true);
            }
            if (is_null($this->GetType())) {
                $this->SetType(ModuleMresource::TYPE_HREF);
            }
        }
        $this->RecalcHash();
    }

    /**
     * Sets full dir path of resource
     *
     * @param $sFile
     */
    public function SetFile($sFile) {

        if ($sFile) {
            if ($sPathDir = F::File_LocalDir($sFile)) {
                // Сохраняем относительный путь
                $this->SetPathFile('@' . $sPathDir);
                if (!$this->GetPathUrl()) {
                    $this->SetUrl(F::File_Dir2Url($sFile));
                }
            } else {
                // Сохраняем абсолютный путь
                $this->SetPathFile($sFile);
            }
            $this->SetLink(false);
            if (!$this->GetStorage()) {
                $this->SetStorage('file');
            }
        } else {
            $this->SetPathFile(null);
        }
        $this->RecalcHash();
    }

    /**
     * Returns ID of media resource
     *
     * @return string|null
     */
    public function GetId() {

        return $this->getProp('mresource_id');
    }

    /**
     * Returns full url to media resource
     *
     * @return string
     */
    public function GetUrl() {

        $sUrl = $this->GetPathUrl();
        if (substr($sUrl, 0, 1) == '@') {
            $sUrl = F::File_NormPath(F::File_RootUrl() . '/' . substr($sUrl, 1));
        }
        return $sUrl;
    }

    /**
     * Returns full dir path to media resource
     *
     * @return string
     */
    public function GetFile() {

        $sPathFile = $this->GetPathFile();
        if (substr($sPathFile, 0, 1) == '@') {
            $sPathFile = F::File_NormPath(F::File_RootDir() . '/' . substr($sPathFile, 1));
        }
        return $sPathFile;
    }

    /**
     * Returns uniq ID of mresource
     *
     * @return string
     */
    public function GetUuid() {

        $sResult = $this->getProp('uuid');
        if (!$sResult) {
            if ($this->GetStorage() == 'file') {
                $sResult = str_pad($this->GetUserId(), 8, '0', STR_PAD_LEFT) . '-' . $this->GetHashFile();
            } elseif (!$this->GetStorage()) {
                $sResult = $this->GetHashUrl();
            }
            $this->setProp('uuid', $sResult);
        }
        return $sResult;
    }

    /**
     * Returns storage name and uniq ID of mresource
     *
     * @return string
     */
    public function GetStorageUuid() {

        return '[' . $this->getStorage() . ']' . $this->GetUuid();
    }

    /**
     * Recalc both hashs (url & dir)
     */
    public function RecalcHash() {

        if (($sFile = $this->GetFile()) && F::File_Exists($sFile)) {
            $sHashFile = md5_file($sFile);
        } else {
            $sHashFile = null;
        }
        if ($sPathUrl = $this->GetPathUrl()) {
            $sHashUrl = $this->Mresource_CalcUrlHash($sPathUrl);
        } else {
            $sHashUrl = null;
        }
        $this->SetHashUrl($sHashUrl);
        $this->SetHashFile($sHashFile);
    }

    /**
     * Returns hash of mresoutce
     *
     * @return string
     */
    public function GetHash() {

        return $this->GetHashUrl();
    }

    /**
     * Checks if mresource local image and its derived from another image
     *
     * @return bool
     */
    public function isDerivedImage() {

        return $this->GetHash() !== $this->GetOriginalHash();
    }

    /**
     * Returns original image path (if mresoutce is local image)
     *
     * @return string
     */
    public function GetOriginalPathUrl() {

        $sPropKey = '-original-url';
        if (!$this->isProp($sPropKey)) {
            $sUrl = $this->GetPathUrl();
            if (!$this->IsLink() && $this->IsImage() && $sUrl) {
                $aOptions = array();
                $sOriginal = $this->Img_OriginalFile($sUrl, $aOptions);
                if ($sOriginal !== $sUrl) {
                    $sUrl = $sOriginal;
                }
            }
            $this->setProp($sPropKey, $sUrl);
        }
        return $this->getProp($sPropKey);
    }

    /**
     * Returns hash of original local image
     * If mresource isn't a local image then returns ordinary hash
     *
     * @return string
     */
    public function GetOriginalHash() {

        $sPropKey = '-original-hash';
        if (!$this->isProp($sPropKey)) {
            $sHash = $this->GetHash();
            if (($sPathUrl = $this->GetPathUrl()) && ($sOriginalUrl = $this->GetOriginalPathUrl())) {
                if ($sOriginalUrl !== $sPathUrl) {
                    $sHash = $this->Mresource_CalcUrlHash($sOriginalUrl);
                }
            }
            $this->setProp($sPropKey, $sHash);
        }
        return $this->getProp($sPropKey);
    }

    /**
     * Returns image URL with requested size
     *
     * @param $xSize
     *
     * @return string
     */
    public function GetImgUrl($xSize) {

        $sPropKey = '-img-url-' . $xSize;
        $sUrl = $this->getProp($sPropKey);
        if ($sUrl) {
            return $sUrl;
        }

        if (!$this->IsLink() && $this->IsType(ModuleMresource::TYPE_IMAGE)) {
            if (is_string($xSize)) {
                $xSize = strtolower($xSize);
                $aSize = explode('x', $xSize);
                if (count($aSize) > 1) {
                    $nW = array_shift($aSize);
                    $nH = array_shift($aSize);
                } else {
                    $nW = array_shift($aSize);
                    $nH = $nW;
                }
            } else {
                $nW = $nH = intval($xSize);
            }
            $sUrl = $this->GetUrl();
            if ($nW || $nH) {
                if ($nW) {
                    $nW = $nH;
                }
                if ($nH) {
                    $nH = $nW;
                }
                $sUrl .= '-' . $nW . 'x' . $nH . '.' . F::File_GetExtension($sUrl);
                if (Config::Get('module.image.autoresize')) {
                    $sFile = $this->Uploader_Url2Dir($sUrl);
                    if (!F::File_Exists($sFile)) {
                        $this->Img_Duplicate($sFile);
                    }
                }
            }
        } else {
            $sUrl = $this->GetUrl();
        }
        $this->setProp($sPropKey, $sUrl);

        return $sUrl;
    }

    /**
     * Check if current mresource exists in storage
     *
     * @return bool
     */
    public function Exists() {

        if ($this->GetStorage() == 'file') {
            $sCheckUuid = '[file]' . $this->GetFile();
        } else {
            $sCheckUuid = $this->GetStorageUuid();
        }
        return $this->Uploader_Exists($sCheckUuid);
    }

}

// EOF