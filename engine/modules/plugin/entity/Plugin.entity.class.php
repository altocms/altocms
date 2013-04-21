<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class ModulePlugin_EntityPlugin extends Entity
{
    public function __construct($aParams = false)
    {
        if (is_array($aParams)) {
            $this->_setData($aParams);
        } elseif($aParams) {
            $this->LoadFromXmlFile((string)$aParams);
        }
        $this->Init();
        if (!$this->GetNum()) $this->SetNum(-1);
    }

    public function LoadFromXmlFile($sPluginId, $aData = null)
    {
        $sPluginXML = $this->Plugin_GetPluginManifest($sPluginId);
        if (is_null($aData)) {
            $aData = array(
                'id' => $sPluginId,
                'priority' => 0,
            );
        }
        $this->LoadFromXml($sPluginXML, $aData);
    }

    public function LoadFromXml($sPluginXML, $aData = null)
    {
        if ($oXml = @simplexml_load_string($sPluginXML)) {
            if (is_null($aData)) {
                $aData = array(
                    'priority' => 0,
                );
            }

            // Обрабатываем данные манифеста
            $sLang = $this->Lang_GetLang();

            $this->_xlang($oXml, 'name', $sLang);
            $this->_xlang($oXml, 'author', $sLang);
            $this->_xlang($oXml, 'description', $sLang);
            $oXml->homepage = $this->Text_Parser((string)$oXml->homepage);
            $oXml->settings = preg_replace('/{([^}]+)}/', Router::GetPath('$1'), $oXml->settings);

            if ($sId = (string)$oXml->id) {
                $aData['id'] = $sId;
            }
            $aData['priority'] = intval($oXml->priority);
            $aData['property'] = $oXml;

            $this->_setData($aData);
        }
    }

    /**
     * Получает значение параметра из XML на основе языковой разметки
     *
     * @param SimpleXMLElement $oXml    XML узел
     * @param string           $sProperty    Свойство, которое нужно вернуть
     * @param string           $sLang    Название языка
     */
    protected function _xlang($oXml, $sProperty, $sLang)
    {
        $sProperty = trim($sProperty);

        if (!count($data = $oXml->xpath("{$sProperty}/lang[@name='{$sLang}']"))) {
            $data = $oXml->xpath("{$sProperty}/lang[@name='default']");
        }
        $oXml->$sProperty->data = $this->Text_Parser(trim((string)array_shift($data)));
    }

    protected function _getDataItem($sKey)
    {
        if (isset($this->_aData[$sKey]))
            return $this->_aData[$sKey];
        else
            return null;
    }

    public function _getDataProperty($sProp = null)
    {
        if (is_null($sProp))
            return $this->_aData['property'];
        else
            return $this->_aData['property']->$sProp;
    }

    public function GetName()
    {
        $xProp = $this->_getDataProperty('name');
        if ($xProp->data)
            return $xProp->data;
        else
            return $xProp->lang;
    }

    public function GetDescription()
    {
        $xProp = $this->_getDataProperty('description');
        if ($xProp->data)
            return $xProp->data;
        else
            return $xProp->lang;
    }

    public function GetAuthor()
    {
        $xProp = $this->_getDataProperty('author');
        if ($xProp->data)
            return $xProp->data;
        else
            return $xProp->lang;
    }

    public function GetPluginClass()
    {
        return 'Plugin' . ucfirst($this->GetCode());
    }

    public function GetAdminClass()
    {
        $aAdminPanel = $this->_getDataItem('adminpanel');
        if (isset($aAdminPanel['class']))
            return $aAdminPanel['class'];
        else {
            return 'Plugin' . ucfirst($this->GetId()) . '_ActionAdmin';
        }
    }

    public function HasAdminpanel()
    {
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

    public function GetAdminMenuEvents()
    {
        if ($this->IsActive()) {
            $aEvents = array();
            $sPluginClass = $this->GetPluginClass();
            $aProps = (array)(new $sPluginClass);
            if (isset($aProps['aAdmin']) AND is_array($aProps['aAdmin']) AND isset($aProps['aAdmin']['menu'])) {
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

    public function GetVersion()
    {
        return (string)$this->_getDataProperty('version');
    }

    public function GetHomepage()
    {
        return (string)$this->_getDataProperty('homepage');
    }

    public function GetEmail()
    {
        return (string)$this->_getDataProperty('author')->email;
    }

    public function IsActive()
    {
        return (bool)$this->_getDataItem('is_active');
    }

    public function isTop()
    {
        return ($sVal = $this->GetPriority()) && strtolower($sVal) == 'top';
    }

    public function Requires()
    {
        return $this->_getDataProperty('requires');
    }

    public function RequiredAltoVersion()
    {
        $oRequires = $this->Requires();
        $sAltoVersion = (string)$oRequires->alto->version;
        if (!$sAltoVersion)
            $sAltoVersion = (string)$oRequires->alto;
        return $sAltoVersion;
    }

    public function RequiredPhpVersion()
    {
        $oRequires = $this->Requires();
        if ($oRequires->system && $oRequires->system->php) {
            return (string)$oRequires->system->php;
        }
    }

    public function RequiredPlugins()
    {
        $oRequires = $this->Requires();
        if ($oRequires->plugins) {
            return $oRequires->plugins->children();
        }
    }

    public function EngineCompatible()
    {
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