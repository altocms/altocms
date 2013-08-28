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
 * @package engine.modules
 * @since   1.0
 */
class ModuleViewerAsset_EntityPackageLess extends ModuleViewerAsset_EntityPackageCss {

    public function Init() {

        parent::Init();
        if (!$this->sAssetType) {
            $this->sAssetType = 'less';
        }
    }

}

// EOF