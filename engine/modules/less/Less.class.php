<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

F::File_Lib('lessphp/lessc.inc.php');

class ModuleLess extends Module {

    /** @var lessc */
    protected $oLessCompiler;

    /**
     * Инициализация модуля
     *
     */
    public function Init() {

        $this->oLessCompiler = new lessc();
    }

    /**
     * Compiles a string of LESS code to CSS
     * TODO: обработка ошибок
     *
     * @param   string  $sString
     * @return  string
     */
    public function Compile($sString) {

        $sResult = null;
        try {
            $sResult = $this->oLessCompiler->compile($sString);
        } catch (Exception $ex) {
            echo 'LESS string compile error: ' . $ex->getMessage();
        }
        return $sResult;
    }

    /**
     * Reads and compiles a file.
     * It will either return the result or write it to the path specified by an optional second argument.
     * TODO: обработка ошибок
     *
     * @param   string      $sFileName
     * @param   string|null $sOutFile
     * @return  string
     */
    public function CompileFile($sFileName, $sOutFile = null) {

        $sResult = null;
        try {
            $sResult = $this->oLessCompiler->compileFile($sFileName, $sOutFile);
        } catch (Exception $ex) {
            echo 'LESS file compile error: ' . $ex->getMessage();
        }
        return $sResult;
    }

    /**
     * Enable or disable comment preservation
     *
     * @param $bPreserve
     */
    public function SetPreserveComments($bPreserve) {

        $this->oLessCompiler->setPreserveComments($bPreserve);
    }

    /**
     * Sets initial LESS variables
     * It takes an associative array of names to values
     * The values must be strings, and will be parsed into correct CSS values
     *
     * @param $aVariables
     */
    public function setVariables($aVariables) {

        $this->oLessCompiler->setVariables($aVariables);
    }

    /**
     * Unsets LESS variable
     *
     * @param $sName
     */
    public function unsetVariable($sName) {

        $this->oLessCompiler->unsetVariable($sName);
    }

    /**
     * Overwrites the search paths for @import directive
     *
     * @param $aDirs
     */
    public function setImportDir($aDirs) {

        $this->oLessCompiler->setImportDir($aDirs);
    }

    /**
     * Appends the search path for @import directive
     *
     * @param $sDir
     */
    public function addImportDir($sDir) {

        $this->oLessCompiler->addImportDir($sDir);
    }

    /**
     * Returns all parsed files (including @import directives)
     *
     * @return array
     */
    public function allParsedFiles() {

        return $this->oLessCompiler->allParsedFiles();
    }

    public function setFormatter($sFormatter) {

        return $this->oLessCompiler->setFormatter($sFormatter);
    }
}

// EOF