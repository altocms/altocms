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
 * Class ModuleImg_EntityConfig
 *
 * @since 1.1
 */
class ModuleImg_EntityConfig extends LsObject {

    public function get($sProp) {

        if (substr($sProp, -7) == '.driver') {
            $sDriver = E::ModuleImg()->GetDriver();
            if (!class_exists('\PHPixie\Image\\' . $sDriver, false)) {
                F::IncludeLib('PHPixie/Image/' . $sDriver . '.php');
            }
            return $sDriver;
        }
        return null;
    }
}

// EOF