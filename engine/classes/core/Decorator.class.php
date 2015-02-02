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
 * Application class of CMS
 *
 * @package engine
 * @since 1.1
 */
class Decorator extends LsObject {

    protected $sType;
    protected $sName;
    protected $bHookEnable = true;
    protected $sHookPrefix;
    protected $oComponent;
    protected $oModuleHook;

    public function __construct($oComponent) {

        $this->oComponent = $oComponent;
    }

    public function CallMethod($sMethod, $aArgs) {

        $aArgsRef = array();
        foreach ($aArgs as $iKey => $xVal) {
            $aArgsRef[] =& $aArgs[$iKey];
        }
        $sHookName = '';
        if ($this->bHookEnable) {
            if (!$this->oModuleHook) {
                $this->oModuleHook = E::Module('Hook');
            }
            switch ($this->sType) {
                case 'action':
                    $this->hookBeforeAction();
                    break;
                case 'module':
                    $sHookName = $this->sHookPrefix . strtolower($sMethod);
                    $aHookArgs = array($sHookName . '_before', &$aArgs);
                    $xResultHook = call_user_func_array(array($this->oModuleHook, 'Run'), $aHookArgs);
                    /*
                     * if ($xResultHook === false) { return $xResultHook; }
                     */
                    break;
                default:
                    break;
            }
        }

        $xResult = call_user_func_array(array($this->oComponent, $sMethod), $aArgsRef);

        if ($this->bHookEnable) {
            switch ($this->sType) {
                case 'action':
                    $this->hookAfterAction();
                    break;
                case 'module':
                    $aHookParams = array('result' => &$xResult, 'params' => &$aArgs);
                    $aHookArgs = array($sHookName . '_after', &$aHookParams);
                    call_user_func_array(array($this->oModuleHook, 'Run'), $aHookArgs);
                    break;
                default:
                    break;
            }
        }

        return $xResult;
    }

    public function __call($sMethod, $aArgs) {

        return $this->CallMethod($sMethod, $aArgs);
    }

    protected function hookBeforeAction() {

    }

    protected function hookAfterAction() {

    }

    protected function hookBeforeModule($sHookName, $aArgs) {

    }

    protected function hookAfterModule($sHookName, $aArgs) {

    }

    /**
     * @param string $sType
     */
    public function setType($sType) {

        $this->sType = $sType;
    }

    /**
     * @param string $sName
     */
    public function setName($sName) {

        $this->sName = $sName;
        $this->sHookPrefix = 'module_' . strtolower($this->sName) . '_';
    }

    /**
     * @param bool $bHookEnable
     */
    public function setHookEnable($bHookEnable) {

        $this->bHookEnable = (bool)$bHookEnable;
    }

    /**
     * @param object $oComponent
     * @param bool   $bHookEnable
     *
     * @return object
     */
    static function Create($oComponent, $bHookEnable = true) {

        $sClassName = get_class($oComponent);
        if (!$bHookEnable || $sClassName == 'ModulePlugin' || $sClassName == 'ModuleHook') {
            return $oComponent;
        }
        $oComponentDecorator = new static($oComponent);
        $aClassInfo = E::GetClassInfo($oComponent, Engine::CI_ACTION | Engine::CI_MODULE);
        if ($aClassInfo[Engine::CI_ACTION]) {
            $oComponentDecorator->setType('action');
            $oComponentDecorator->setName($aClassInfo[Engine::CI_ACTION]);
            $oComponentDecorator->setHookEnable(true);
        } elseif($aClassInfo[Engine::CI_MODULE]) {
            $oComponentDecorator->setType('module');
            $oComponentDecorator->setName($aClassInfo[Engine::CI_MODULE]);
            $oComponentDecorator->setHookEnable(true);
        }
        return $oComponentDecorator;
    }
}

// EOF