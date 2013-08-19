<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class ModuleMresource_EntityMresourceRel extends ModuleMresource_EntityMresource {

    public function GetId() {

        return $this->getProp('id');
    }

}

// EOF