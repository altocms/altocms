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
 * Class ModuleMresource_EntityMresourceRel
 *
 * @method setType($xParam)
 * @method setUserId($iUserId)
 *
 * @method int GetMresourceId()
 * @method string getTargetType()
 *
 */
class ModuleMresource_EntityMresourceRel extends ModuleMresource_EntityMresource {

    public function GetId() {

        return $this->getProp('id');
    }

    /**
     * @param null $xSize
     *
     * @return string|null
     */
    public function getImageUrl($xSize = null) {

        $sUrl = $this->getPathUrl();
        if ($sUrl) {
            $sUrl = E::ModuleUploader()->CompleteUrl($sUrl);
            if (!$xSize) {
                return $sUrl;
            }

            return E::ModuleUploader()->ResizeTargetImage($sUrl, $xSize);
        }
        return null;
    }

    /**
     * @return bool|ModuleImg_EntityImage|null
     */
    protected function _getImageObject() {

        if ($this->isImage()) {
            $oImg = $this->getProp('__image');
            if ($oImg === null) {
                if (!($sFile = $this->GetFile()) || !($oImg = E::ModuleImg()->Read($sFile))) {
                    $oImg = false;
                }
            }
            return $oImg;
        }
        return null;
    }

    /**
     * @return int|null
     */
    public function getSizeWidth() {

        $oImg = $this->_getImageObject();
        if ($oImg) {
            return $oImg->GetWidth();
        }
        return null;
    }

    /**
     * @return int|null
     */
    public function getSizeHeight() {

        $oImg = $this->_getImageObject();
        if ($oImg) {
            return $oImg->GetHeight();
        }
        return null;
    }

}

// EOF