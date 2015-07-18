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
 * Class Smarty_Resource_File
 */
class Smarty_Resource_File extends Smarty_Internal_Resource_File {

    /**
     * populate Source Object with meta data from Resource
     *
     * @param Smarty_Template_Source   $source    source object
     * @param Smarty_Internal_Template $_template template object
     */
    public function populate(Smarty_Template_Source $source, Smarty_Internal_Template $_template = null) {

        $source->name = E::ModulePlugin()->GetDelegate('template', $source->name);
        parent::populate($source, $_template);
    }

}

// EOF