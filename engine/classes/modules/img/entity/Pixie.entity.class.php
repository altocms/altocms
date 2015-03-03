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
 * Class ModuleImg_EntityPixie
 *
 * @since 1.1
 */
class ModuleImg_EntityPixie extends LsObject {

    public $config;

    public function __construct() {

        $this->config = new ModuleImg_EntityConfig();
    }
}

// EOF