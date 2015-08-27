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
 * @method int GetMresourceId()
 * @methos string getTargetType()
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
            if (strpos($sUrl, '@') === 0) {
                $sUrl = Config::Get('path.root.url') . substr($sUrl, 1);
            }

            if (!$xSize) {
                return $sUrl;
            }

            return E::ModuleUploader()->ResizeTargetImage($sUrl, $xSize);
        }
        return null;
    }

}

// EOF