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
 * Class ModuleSkin_EntitySkin
 *
 * @method GetId()
 */
class ModuleSkin_EntitySkin extends Entity {

    public function __construct($aParams = false) {

        if (!is_array($aParams)) {
            $aParams['id'] = (string)$aParams;
        }
        $this->setProps($aParams);
        if (!$this->isProp('property') && $this->getId()) {
            $this->LoadFromXmlFile($this->getId(), $aParams);
        }
        $this->Init();
    }

    /**
     * @param string $sSkinId
     * @param array $aData
     */
    public function LoadFromXmlFile($sSkinId, $aData = null) {

        if (isset($aData['dir'])) {
            $sSkinDir = $aData['dir'];
        } else {
            $sSkinDir = null;
        }
        $sSkinXML = E::ModuleSkin()->GetSkinManifest($sSkinId, $sSkinDir);
        if (!is_array($aData)) {
            $aData = array(
                'id' => $sSkinId,
            );
        } elseif (!isset($aData['id'])) {
            $aData['id'] = $sSkinId;
        }
        $this->LoadFromXml($sSkinXML, $aData);
    }

    /**
     * @param string $sSkinXML
     * @param array $aData
     */
    public function LoadFromXml($sSkinXML, $aData = null) {

        if (Is_null($aData)) {
            $aData = array();
        }
        $oXml = @simplexml_load_string($sSkinXML);
        if (!$oXml) {
            $sXml = '<?xml version="1.0" encoding="UTF-8"?>
                <skin>
                    <name><lang name="default">' . (isset($aData['id']) ? $aData['id'] : '') . '</lang></name>'
                . '</skin>';
            $oXml = @simplexml_load_string($sXml);
        }

        // Обрабатываем данные манифеста
        $oXml->homepage = filter_var((string)$oXml->homepage, FILTER_SANITIZE_URL);

        if ($sId = (string)$oXml->id) {
            $aData['id'] = $sId;
        }
        $aData['property'] = $oXml;
        $this->setProps($aData);
    }

    /**
     * Получает значение параметра из XML на основе языковой разметки
     *
     * @param SimpleXMLElement $oXml       XML узел
     * @param string           $sProperty  Свойство, которое нужно вернуть
     * @param string           $sLang      Название языка
     * @param bool             $bParseText
     */
    protected function _xlang($oXml, $sProperty, $sLang, $bParseText = false) {

        $sProperty = trim($sProperty);

        if (!count($data = $oXml->xpath("{$sProperty}/lang[@name='{$sLang}']"))) {
            $data = $oXml->xpath("{$sProperty}/lang[@name='default']");
        }
        if ($bParseText) {
            $oXml->$sProperty->data = E::ModuleText()->Parse(trim((string)array_shift($data)));
        } else {
            $oXml->$sProperty->data = trim((string)array_shift($data));
        }
    }

    /**
     * @param string $sKey
     *
     * @return string|null
     */
    protected function _getDataItem($sKey) {

        if (isset($this->_aData[$sKey]))
            return $this->_aData[$sKey];
        else
            return null;
    }

    /**
     * @param string $sProp
     *
     * @return mixed
     */
    public function _getDataProperty($sProp = null) {

        if (is_null($sProp)) {
            return $this->_aData['property'];
        } else {
            return $this->_aData['property']->$sProp;
        }
    }

    /**
     * @param string $sProp
     * @param bool   $bParseText
     *
     * @return string
     */
    protected function _getLangProp($sProp, $bParseText = false) {

        $sResult = $this->getProp('_' . $sProp);
        if (is_null($sResult)) {
            $sLang = E::ModuleLang()->GetLang();
            $this->_xlang($this->_aData['property'], 'author', $sLang, $bParseText);
            $xProp = $this->_getDataProperty($sProp);
            if ($xProp->data) {
                $sResult = (string)$xProp->data;
            }
            else {
                $sResult = (string)$xProp->lang;
            }
            $this->setProp('_' . $sProp, $sResult);
        }

        return $sResult;
    }

    /**
     * @return string
     */
    public function GetName() {

        return $this->_getLangProp('name');
    }

    /**
     * @return string
     */
    public function GetDescription() {

        return $this->_getLangProp('description');
    }

    /**
     * @return string
     */
    public function GetAuthor() {

        return $this->_getLangProp('author', true);
    }

    /**
     * @return string
     */
    public function GetVersion() {

        return (string)$this->_getDataProperty('version');
    }

    /**
     * @return string
     */
    public function GetHomepage() {

        return (string)$this->_getDataProperty('homepage');
    }

    /**
     * @return string
     */
    public function GetEmail() {

        return (string)$this->_getDataProperty('author')->email;
    }

    /**
     * @return bool
     */
    public function IsActive() {

        return (bool)$this->_getDataItem('is_active');
    }

    public function Requires() {

        return $this->_getDataProperty('requires');
    }

    /**
     * Returns array of screenshots
     *
     * @return array
     */
    public function GetScreenshots() {

        $aResult = array();
        if ($this->_getDataProperty('info')->screenshots) {
            $aData = $this->_getDataProperty('info')->screenshots->screenshot;
            if (sizeof($aData)) {
                foreach ($aData as $oProp) {
                    $aResult[] = array(
                        'preview' => (strtolower($oProp['preview']) === 'yes'),
                        'file' => (string)$oProp['file'],
                    );
                }
            }
        }
        return $aResult;
    }

    /**
     * Returns preview from manifest
     *
     * @return string|null
     */
    public function GetPreview() {

        $aScreens=$this->GetScreenshots();
        foreach ($aScreens as $aScreen) {
            if ($aScreen['preview']) return $aScreen;
        }
        if (sizeof($aScreens)) {
            return array_shift($aScreens);
        }
        return null;
    }

    /**
     * Returns URL of preview if it exists
     *
     * @return string|null
     */
    public function GetPreviewUrl() {

        $aScreen = $this->GetPreview();
        if ($aScreen && isset($aScreen['file'])) {
            $sFile = ($this->getDir() ? $this->getDir() : Config::Get('path.skins.dir') . $this->GetId()) . '/settings/' . $aScreen['file'];
            $sUrl = F::File_Dir2Url($sFile);
            return $sUrl;
        }
        return null;
    }

    /**
     * Тип скина - 'adminpanel', 'site'
     */
    public function GetType() {

        $info = $this->_getDataProperty('info');
        $sType = strtolower($info['type']);
        if (strpos($sType, 'admin') !== false) {
            return 'adminpanel';
        } else {
            return 'site';
        }
    }

    /**
     * Return list of themes
     *
     * @return array
     */
    public function GetThemes() {

        $aResult = array();
        if ($this->_getDataProperty('info')->themes && ($aData = $this->_getDataProperty('info')->themes->theme) && sizeof($aData)) {
            foreach ($aData as $oProp) {
                $aResult[] = array(
                    'code' => (string)$oProp['code'],
                    'name' => (string)$oProp['name'],
                    'color' => (string)$oProp['color'],
                );
            }
        }
        return $aResult;
    }

    /**
     * Return value of attribute "compatible" of skin
     *
     * @return string
     */
    public function GetCompatible() {

        $sResult = '';
        $aProps = $this->getProp('property');
        if ($aProps) {
            $sResult = (string)$aProps['compatible'];
        }
        return $sResult;
    }

    /**
     * What minimal version Alto CMS required
     *
     * @return string
     */
    public function RequiredAltoVersion() {

        $oRequires = $this->Requires();
        $sAltoVersion = (string)$oRequires->alto->version;
        if (!$sAltoVersion)
            $sAltoVersion = (string)$oRequires->alto;
        return $sAltoVersion;
    }

    /**
     * What version of PHP required
     *
     * @return string
     */
    public function RequiredPhpVersion() {

        $oRequires = $this->Requires();
        if ($oRequires->system && $oRequires->system->php) {
            return (string)$oRequires->system->php;
        }
        return null;
    }

    /**What plugins required
     *
     * @return null
     */
    public function RequiredPlugins() {

        $oRequires = $this->Requires();
        if ($oRequires->Plugins) {
            return $oRequires->Plugins->children();
        }
        return null;
    }

    /**
     * Check compatibility of skin with current version of Alto CMS
     *
     * @return mixed
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

    /**
     * Check the skin compatibility
     *
     * @param string|null $sVersion
     * @param string|null $sOperator
     *
     * @return bool
     */
    public function SkinCompatible($sVersion = null, $sOperator = null) {

        $sValue = $this->GetCompatible();
        if ($sValue) {
            // version of skin edition
            if (!strpos($sValue, '-')) {
                $sAlto = $sValue;
                $sModVersion = null;
            } else {
                list($sAlto, $sModVersion) = explode('-', $sValue, 2);
            }
            if ($sAlto == 'alto') {
                if (!$sModVersion) {
                    $sModVersion = '1.0';
                }
                if (!$sVersion) {
                    return true;
                } elseif (!$sOperator && version_compare($sModVersion, $sVersion, '==')) {
                    return true;
                } elseif ($sOperator) {
                    return version_compare($sModVersion, $sVersion, $sOperator);
                }
            }
        }
        return false;
    }

}

// EOF