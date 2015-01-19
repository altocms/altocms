<?php

/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class ModuleMresource_EntityMresourceCategory extends Entity {

    public function getId() {
        return $this->getProp('id');
    }

    public function getLabel() {
        return $this->getProp('label');
    }

    public function getCount() {
        return $this->getProp('count');
    }
}