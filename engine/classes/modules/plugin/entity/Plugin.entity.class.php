<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class ModulePlugin_EntityPlugin extends Entity {

    protected $oXml = null;

    public function __construct($aParams = false) {

        if (is_array($aParams)) {
            $this->_setData($aParams);
        } elseif($aParams) {
            $this->LoadFromXmlFile((string)$aParams);
        }
        $this->Init();
        if (!$this->GetNum()) $this->SetNum(-1);
    }

    public function LoadFromXmlFile($sPluginId, $aData = null) {

        $sPluginXML = $this->Plugin_GetPluginManifest($sPluginId);
        if (is_null($aData)) {
            $aData = array(
                'id' => $sPluginId,
                'priority' => 0,
            );
        }
        $this->LoadFromXml($sPluginXML, $aData);
    }

    public function LoadFromXml($sPluginXML, $aData = null) {

        if ($this->oXml = @simplexml_load_string($sPluginXML)) {
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

            $this->_setData($aData);
        }
    }

    /**
     * Получает значение параметра из XML на основе языковой разметки
     *
     * @param SimpleXMLElement $oXml         - XML узел
     * @param string           $sProperty    - Свойство, которое нужно вернуть
     * @param string           $sLang        - Название языка
     * @param bool             $bHtml        - HTML или текст
     */
    protected function _xlang($oXml, $sProperty, $sLang, $bHtml = false) {

        $sProperty = trim($sProperty);

        if (!count($data = $oXml->xpath("{$sProperty}/lang[@name='{$sLang}']"))) {
            $data = $oXml->xpath("{$sProperty}/lang[@name='default']");
        }
        $sText = trim((string)array_shift($data));
        if ($sText) {
            $oXml->$sProperty->data = ($bHtml ? $this->Text_Parser($sText) : strip_tags($sText));
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
            $sLang = $this->Lang_GetLang();
            $this->_xlang($this->oXml, $sName, $sLang);
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

    public function GetName() {

        return $this->_getXmlLangProperty('name');
    }

    public function GetDescription() {

        return $this->_getXmlLangProperty('description');
    }

    public function GetAuthor() {

        return $this->_getXmlLangProperty('author');
    }

    public function GetPluginClass() {

        return 'Plugin' . ucfirst($this->GetCode());
    }

    public function GetAdminClass() {

        $aAdminPanel = $this->getProp('adminpanel');
        if (isset($aAdminPanel['class']))
            return $aAdminPanel['class'];
        else {
            return 'Plugin' . ucfirst($this->GetId()) . '_ActionAdmin';
        }
    }

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

    public function GetVersion() {

        return (string)$this->_getXmlProperty('version');
    }

    public function GetHomepage() {

        $sResult = $this->getProp('homepage');
        if (is_null($sResult)) {
            $sResult = $this->Text_Parser((string)$this->_getXmlProperty('homepage'));
            $this->setProp('homepage', $sResult);
        }
        return $sResult;
    }

    public function GetSettings() {

        $sResult = $this->getProp('settings');
        if (is_null($sResult)) {
            $sResult = preg_replace('/{([^}]+)}/', Router::GetPath('$1'), $this->oXml->settings);
            $this->setProp('settings', $sResult);
        }
        return $sResult;
    }

    public function GetEmail() {

        return (string)$this->_getXmlProperty('author')->email;
    }

    public function IsActive() {

        return (bool)$this->getProp('is_active');
    }

    public function isTop() {

        return ($sVal = $this->GetPriority()) && strtolower($sVal) == 'top';
    }

    public function Requires() {

        return $this->_getXmlProperty('requires');
    }

    public function RequiredAltoVersion() {

        $oRequires = $this->Requires();
        $sAltoVersion = (string)$oRequires->alto->version;
        if (!$sAltoVersion)
            $sAltoVersion = (string)$oRequires->alto;
        return $sAltoVersion;
    }

    public function RequiredPhpVersion() {

        $oRequires = $this->Requires();
        if ($oRequires->system && $oRequires->system->php) {
            return (string)$oRequires->system->php;
        }
    }

    public function RequiredPlugins() {

        $oRequires = $this->Requires();
        if ($oRequires->plugins) {
            return $oRequires->plugins->children();
        }
    }

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
        return false;
    }
}

// EOF