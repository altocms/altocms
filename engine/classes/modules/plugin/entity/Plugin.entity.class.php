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
 * Class ModulePlugin_EntityPlugin
 *
 * @method bool GetIsActive()
 *
 * @method SetNum($iParam)
 * @method SetIsActive($bParam)
 */
class ModulePlugin_EntityPlugin extends Entity {

    /** @var SimpleXMLElement */
    protected $oXml = null;

    /**
     * Constructor of entity
     *
     * @param bool $aParams
     */
    public function __construct($aParams = false) {

        if (!is_array($aParams)) {
            // передан ID плагина
            $aParams = array(
                'id' => (string)$aParams,
            );
        }

        $this->setProps($aParams);

        if(empty($aParams['manifest']) && !empty($aParams['id'])) {
            $aParams['manifest'] = E::ModulePlugin()->GetPluginManifestFile($aParams['id']);
        }
        if(!empty($aParams['manifest'])) {
            $this->LoadFromXmlFile($aParams['manifest'], $aParams);
        }
        $this->Init();
        if (!$this->GetNum()) {
            $this->SetNum(-1);
        }
    }

    /**
     * Load data from XML file
     *
     * @param string $sPluginXmlFile
     * @param array  $aData
     */
    public function LoadFromXmlFile($sPluginXmlFile, $aData = null) {

        $sPluginXmlString = E::ModulePlugin()->GetPluginManifestFrom($sPluginXmlFile);
        $this->LoadFromXml($sPluginXmlString, $aData);
    }

    /**
     * Load data from XML string
     *
     * @param string $sPluginXmlString
     * @param array  $aData
     */
    public function LoadFromXml($sPluginXmlString, $aData = null) {

        if ($this->oXml = @simplexml_load_string($sPluginXmlString)) {
            if (is_null($aData)) {
                $aData = array(
                    'priority' => 0,
                );
            }

            if ($sId = (string)$this->oXml->id) {
                $aData['id'] = $sId;
            }
            $sPriority = trim($this->oXml->priority);
            if ($sPriority) {
                if (is_numeric($sPriority)) {
                    $sPriority = intval($sPriority);
                } else {
                    $sPriority = strtolower($sPriority);
                }
            } else {
                $sPriority = 0;
            }
            $aData['priority'] = $sPriority;
            $aData['property'] = $this->oXml;

            $this->setProps($aData);
        }
    }

    /**
     * Получает значение параметра из XML на основе языковой разметки
     *
     * @param SimpleXMLElement $oXml         - XML узел
     * @param string           $sProperty    - Свойство, которое нужно вернуть
     * @param string|array     $xLang        - Название языка
     * @param bool             $bHtml        - HTML или текст
     */
    protected function _xlang($oXml, $sProperty, $xLang, $bHtml = false) {

        $sProperty = trim($sProperty);

        if (is_array($xLang)) {
            foreach($xLang as $sLang) {
                if (count($data = $oXml->xpath("{$sProperty}/lang[@name='{$sLang}']"))) {
                    break;
                }
                if (!count($data)) {
                    $data = $oXml->xpath("{$sProperty}/lang[@name='default']");
                }
            }
        } else {
            if (!count($data = $oXml->xpath("{$sProperty}/lang[@name='{$xLang}']"))) {
                $data = $oXml->xpath("{$sProperty}/lang[@name='default']");
            }
        }

        $sText = trim((string)array_shift($data));
        if ($sText) {
            $oXml->$sProperty->data = ($bHtml ? E::ModuleText()->Parser($sText) : strip_tags($sText));
        } else {
            $oXml->$sProperty->data = '';
        }
    }

    protected function _getXmlProperty($sProp = null) {

        if (is_null($sProp)) {
            return $this->_aData['property'];
        } else {
            return $this->_aData['property']->$sProp;
        }
    }

    protected function _getXmlLangProperty($sName) {

        $sResult = $this->getProp($sName);
        if (is_null($sResult)) {
            $aLangs = E::ModuleLang()->GetLangAliases(true);
            $this->_xlang($this->oXml, $sName, $aLangs);
            $xProp = $this->_getXmlProperty($sName);
            if ($xProp->data) {
                $sResult = (string)$xProp->data;
            } else {
                $sResult = (string)$xProp->lang;
            }
            $this->setProp($sName, $sResult);
        }
        return $sResult;
    }

    /**
     * @param bool $bEncode
     *
     * @return mixed|null
     */
    public function GetId($bEncode = false) {

        $sResult = $this->getProp('id');
        if ($bEncode) {
            $sResult = E::ModulePlugin()->EncodeId($sResult);
        }

        return $sResult;
    }

    /**
     * @return string|null
     */
    public function GetManifestFile() {

        return $this->getProp('manifest');
    }

    /**
     * @return string
     */
    public function GetName() {

        return $this->_getXmlLangProperty('name');
    }

    /**
     * @return string
     */
    public function GetDescription() {

        return $this->_getXmlLangProperty('description');
    }

    /**
     * @return string
     */
    public function GetAuthor() {

        return $this->_getXmlLangProperty('author');
    }

    /**
     * @return string
     */
    public function GetPluginClass() {

        return Plugin::GetPluginClass($this->GetId());
    }

    /**
     * @return null|string
     */
    public function GetPluginClassFile() {

        $sManifest = $this->GetManifestFile();
        $sClassName = $this->GetPluginClass();
        if ($sManifest && $sClassName) {
            return dirname($sManifest) . '/' . $sClassName . '.class.php';
        }
        return null;
    }

    /**
     * @return string
     */
    public function GetAdminClass() {

        $aAdminPanel = $this->getProp('adminpanel');
        if (isset($aAdminPanel['class']))
            return $aAdminPanel['class'];
        else {
            return 'Plugin' . ucfirst($this->GetId()) . '_ActionAdmin';
        }
    }

    /**
     * @return bool
     */
    public function HasAdminpanel() {

        $sClass = $this->GetAdminClass();
        try {
            if (class_exists($sClass, true)) {
                return true;
            }
        } catch (Exception $e) {
            //if (class_exists())
        }
        return false;
    }

    /**
     * @return array|bool
     */
    public function GetAdminMenuEvents() {

        if ($this->IsActive()) {
            $aEvents = array();
            $sPluginClass = $this->GetPluginClass();
            $aProps = (array)(new $sPluginClass);
            if (isset($aProps['aAdmin']) && is_array($aProps['aAdmin']) && isset($aProps['aAdmin']['menu'])) {
                foreach ((array)$aProps['aAdmin']['menu'] as $sEvent => $sClass) {
                    if (substr($sClass, 0, 1) == '_') {
                        $sClass = $sPluginClass . $sClass;
                    }
                    if (!preg_match('/Plugin([A-Z][a-z0-9]+)_(\w+)/', $sClass)) {
                        // nothing
                    }
                    $aEvents[$sEvent] = $sClass;
                }
            }
            return $aEvents;
        }
        return false;
    }

    /**
     * @return string
     */
    public function GetVersion() {

        return (string)$this->_getXmlProperty('version');
    }

    /**
     * @return string
     */
    public function GetHomepage() {

        $sResult = $this->getProp('homepage');
        if (is_null($sResult)) {
            $sResult = E::ModuleText()->Parser((string)$this->_getXmlProperty('homepage'));
            $this->setProp('homepage', $sResult);
        }
        return $sResult;
    }

    /**
     * @return string
     */
    public function GetSettings() {

        $sResult = $this->getProp('settings');
        if (is_null($sResult)) {
            $sResult = preg_replace('/{([^}]+)}/', R::GetPath('$1'), $this->oXml->settings);
            $this->setProp('settings', $sResult);
        }
        return $sResult;
    }

    /**
     * @return string
     */
    public function GetDirname() {

        $sResult = (string)$this->_getXmlProperty('dirname');
        return $sResult ? $sResult : $this->GetId();
    }

    /**
     * @return string
     */
    public function GetEmail() {

        return (string)$this->_getXmlProperty('author')->email;
    }

    /**
     * @return bool
     */
    public function IsActive() {

        return (bool)$this->getProp('is_active');
    }

    /**
     * @return bool
     */
    public function isTop() {

        return ($sVal = $this->GetPriority()) && strtolower($sVal) == 'top';
    }

    /**
     * @return array
     */
    public function Requires() {

        return $this->_getXmlProperty('requires');
    }

    /**
     * @return string
     */
    public function RequiredAltoVersion() {

        $oRequires = $this->Requires();
        $sAltoVersion = (string)$oRequires->alto->version;
        if (!$sAltoVersion) {
            $sAltoVersion = (string)$oRequires->alto;
        }
        return $sAltoVersion;
    }

    /**
     * @return string
     */
    public function RequiredPhpVersion() {

        $oRequires = $this->Requires();
        if ($oRequires->system && $oRequires->system->php) {
            return (string)$oRequires->system->php;
        }
        return '';
    }

    /**
     * @return array|SimpleXmlElement
     */
    public function RequiredPlugins() {

        $oRequires = $this->Requires();
        if ($oRequires->plugins) {
            return $oRequires->plugins->children();
        }
        return array();
    }

    /**
     * @return bool
     */
    public function EngineCompatible() {

        $oRequires = $this->Requires();

        $sLsVersion = (string)$oRequires->livestreet;
        $sAltoVersion = (string)$oRequires->alto->version;
        if (!$sAltoVersion)
            $sAltoVersion = (string)$oRequires->alto;

        if ($sAltoVersion) {
            return version_compare($sAltoVersion, ALTO_VERSION, '<=');
        } else {
            return version_compare($sLsVersion, LS_VERSION, '<=');
        }
    }
}

// EOF