<?php
/*---------------------------------------------------------------------------
* @Project: Alto CMS
* @Project URI: http://altocms.com
* @Description: Advanced Community Engine
* @Copyright: Alto CMS Team
* @License: GNU GPL v2 & MIT
*----------------------------------------------------------------------------
* Based on
*   LiveStreet Engine Social Networking by Mzhelskiy Maxim
*   Site: www.livestreet.ru
*   E-mail: rus.engine@gmail.com
*----------------------------------------------------------------------------
*/

/**
 * @package engine.modules
 * @since 1.0
 */
class ModuleSkin extends Module {

    const SKIN_XML_FILE = 'skin.xml';

    public function Init() {

    }

    public function GetSkinManifest($sSkin) {

        $sXmlFile = Config::Get('path.skins.dir') . $sSkin . '/settings/' . self::SKIN_XML_FILE;
        if ($sXml = F::File_GetContents($sXmlFile)) {
            return $sXml;
        }
    }

    /**
     * Возвращает массив сущностей
     *
     * @param   array   $aFilter    - array('type' => 'site'|'admin')
     * @return  array(ModuleSkin_EntitySkin)
     */
    public function GetSkinsList($aFilter = array()) {

        $aSkinList = array();
        if (isset($aFilter['name'])) {
            $sPattern = Config::Get('path.skins.dir') . $aFilter['name'];
        } else {
            $sPattern = Config::Get('path.skins.dir') . '*';
        }
        $aList = glob($sPattern, GLOB_ONLYDIR);
        if ($aList) {
            if (!isset($aFilter['type'])) $aFilter['type'] = '';
            $sActiveSkin = Config::Get('view.skin', Config::DEFAULT_CONFIG_ROOT);
            foreach ($aList as $sSkinDir) {
                $sSkin = basename($sSkinDir);
                $oSkinEntity = Engine::GetEntity('Skin', $sSkin);
                if (!$aFilter['type'] || $aFilter['type'] == $oSkinEntity->GetType()) {
                    $oSkinEntity->SetIsActive($oSkinEntity->GetId() == $sActiveSkin);
                    $aSkinList[$oSkinEntity->GetId()] = $oSkinEntity;
                }
            }
        }
        return $aSkinList;
    }

    /**
     * Возвращает массив названий
     *
     * @param   string|null $sType
     * @return  array(string)
     */
    public function GetSkinsArray($sType = null) {

        if ($sType) {
            $aFilter = array('type' => $sType);
        } else {
            $aFilter = array();
        }
        $aSkins = $this->GetSkinsList($aFilter);
        return array_keys($aSkins);
    }

    /**
     * @param $sName
     *
     * @return ModuleSkin_EntitySkin
     */
    public function GetSkin($sName) {

        $aSkins = $this->GetSkinsList(array('name' => $sName));
        if (isset($aSkins[$sName])) {
            return $aSkins[$sName];
        }
    }

}

// EOF