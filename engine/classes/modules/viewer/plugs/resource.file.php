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

    public $compiler_class = 'Smarty_Compiler_Template';

    /**
     * populate Source Object with meta data from Resource
     *
     * @param Smarty_Template_Source   $source    source object
     * @param Smarty_Internal_Template $_template template object
     */
    public function populate(Smarty_Template_Source $source, Smarty_Internal_Template $_template = null) {

        $source->name = E::ModulePlugin()->getLastOf('template', $source->name);
        parent::populate($source, $_template);
    }

    /**
     * Fix filepath builder for Smarty 3.1.29
     *
     * @param Smarty_Template_Source        $source
     * @param Smarty_Internal_Template|null $_template
     *
     * @return string
     * @throws SmartyException
     */
    protected function buildFilepath(Smarty_Template_Source $source, Smarty_Internal_Template $_template = null) {

        $file = $source->name;
        if (($file[0] == '/' || $file[1] == ':') && is_file($file)) {
            return $file;
        }
        return parent::buildFilepath($source, $_template);
    }
}

// EOF