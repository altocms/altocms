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
 * Class ModuleMresource_EntityMresource
 *
 * @method setUserId(int $iParam)
 * @method setTargetId(int $iParam)
 * @method setLink(string $sParam)
 * @method setHashFile(string $sParam)
 * @method setHashUrl(string $sParam)
 * @method setPathFile(string $sParam)
 * @method setPathUrl(string $sParam)
 * @method setType(string $sParam)
 * @method setStorage(string $sParam)
 *
 * @method int getMresourceId()
 * @method int getUserId()
 * @method int getTargetId()
 * @method string getLink()
 * @method string getHashFile()
 * @method string getHashUrl()
 * @method string getPathFile()
 * @method string getPathUrl()
 * @method int getType()
 * @method string getStorage()
 */
class ModuleMresource_EntityMresource extends Entity {

    /**
     * Массив параметров ресурса
     *
     * @var array
     */
    protected $aParams = null;

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
     * Checks if resource is image
     *
     * @return bool
     */
    public function IsGraphicFile() {

        return $this->IsType(ModuleMresource::TYPE_IMAGE | ModuleMresource::TYPE_PHOTO | ModuleMresource::TYPE_PHOTO_PRIMARY);
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
                $sResult = ModuleMresource::CreateUuid($this->GetStorage(), $this->GetPathFile(), $this->GetHashFile(), $this->GetUserId());
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
            $sHashUrl = E::ModuleMresource()->CalcUrlHash($sPathUrl);
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
                $sOriginal = E::ModuleImg()->OriginalFile($sUrl, $aOptions);
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
                    $sHash = E::ModuleMresource()->CalcUrlHash($sOriginalUrl);
                }
            }
            $this->setProp($sPropKey, $sHash);
        }
        return $this->getProp($sPropKey);
    }

    /**
     * Returns image URL with requested size
     *
     * @param string|int $xSize
     *
     * @return string
     */
    public function GetImgUrl($xSize = null) {

        $sUrl = $this->GetUrl();
        if (!$xSize) {
            return $sUrl;
        }

        $sModSuffix = F::File_ImgModSuffix($xSize, pathinfo($sUrl, PATHINFO_EXTENSION));

        $sPropKey = '_img-url-' . ($sModSuffix ? $sModSuffix : $xSize);
        $sResultUrl = $this->getProp($sPropKey);
        if ($sResultUrl) {
            return $sResultUrl;
        }

        if (!$this->IsLink() && $this->IsType(ModuleMresource::TYPE_IMAGE | ModuleMresource::TYPE_PHOTO)) {
            if (F::File_IsLocalUrl($sUrl) && $sModSuffix) {
                $sUrl = $sUrl . $sModSuffix;
                if (Config::Get('module.image.autoresize')) {
                    $sFile = E::ModuleUploader()->Url2Dir($sUrl);
                    if (!F::File_Exists($sFile)) {
                        E::ModuleImg()->Duplicate($sFile);
                    }
                }
            }
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
        return E::ModuleUploader()->Exists($sCheckUuid);
    }

    public function getWebPath($xSize=FALSE) {

        $sUrl = str_replace('@', Config::Get('path.root.web'), $this->getPathUrl());

        if (!$xSize) {
            return $sUrl;
        }

        return E::ModuleUploader()->ResizeTargetImage($sUrl, $xSize);

    }

    /**
     * Возвращает сериализованную строку дополнительных данных ресурса
     *
     * @return string
     */
    public function getParams() {

        $sResult = $this->getProp('params');
        return !is_null($sResult) ? $sResult : serialize('');
    }

    /**
     * Устанавливает сериализованную строчку дополнительных данных
     *
     * @param string $data
     */
    public function setParams($data) {

        $this->setProp('params', serialize($data));
    }

    /**
     * Получает описание ресурса
     *
     * @return mixed|null
     */
    public function getDescription() {
        return $this->getParamValue('description');
    }

    /**
     * Устанавливает описание ресурса
     * @param $sValue
     */
    public function setDescription($sValue) {
        $this->setParamValue('description', $sValue);
    }


    public function IsCover() {
        return $this->getType() == ModuleMresource::TYPE_PHOTO_PRIMARY;
    }
    /* ****************************************************************************************************************
 * методы расширения типов топика
 * ****************************************************************************************************************
 */

    /**
     * Извлекает сериализованные данные топика
     */
    protected function extractParams() {

        if (is_null($this->aParams)) {
            $this->aParams = @unserialize($this->getParams());
        }
    }

    /**
     * Устанавливает значение нужного параметра
     *
     * @param string $sName    Название параметра/данных
     * @param mixed  $data     Данные
     */
    protected function setParamValue($sName, $data) {

        $this->extractParams();
        $this->aParams[$sName] = $data;
        $this->setParams($this->aParams);
    }

    /**
     * Извлекает значение параметра
     *
     * @param string $sName    Название параметра
     *
     * @return null|mixed
     */
    public function getParamValue($sName) {

        $this->extractParams();
        if (isset($this->aParams[$sName])) {
            return $this->aParams[$sName];
        }
        return null;
    }
}

// EOF