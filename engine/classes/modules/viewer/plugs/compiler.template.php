<?php

class Smarty_Compiler_Template extends Smarty_Internal_SmartyTemplateCompiler {

    public function compileTemplateSource(Smarty_Internal_Template $template, $nocache = null, Smarty_Internal_TemplateCompilerBase $parent_compiler = null) {

        $_compiled_code = parent::compileTemplateSource($template, $nocache, $parent_compiler);
        /*
         * https://github.com/smarty-php/smarty/commit/2ebacc3b545e987bbe768bd0bed3594a4c71123e
         */
        $this->template = $template;
        return $_compiled_code;
    }
}

// EOF