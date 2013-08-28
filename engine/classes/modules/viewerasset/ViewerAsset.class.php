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
 * @package engine.modules
 * @since   1.0
 */
class ModuleViewerAsset extends Module {

    protected $aAssetTypes
        = array(
            'less',
            'js',
            'css',
            'img',
        );

    protected $aAssets = array();

    /**
     * Преобразует путь к файлу в путь к asset-ресурсу
     *
     * @param   string $sFile
     * @return  string
     */
    public function AssetFileDir($sFile) {

        return F::File_NormPath($this->GetAssetDir() . $this->Hash(dirname($sFile)) . '/' . basename($sFile));
    }

    /**
     * Преобразует URL к файлу в URL к asset-ресурсу
     *
     * @param   string $sFile
     * @return  string
     */
    public function AssetFileUrl($sFile) {

        return F::File_NormPath($this->GetAssetUrl() . $this->Hash(dirname($sFile)) . '/' . basename($sFile));
    }

    /**
     *
     */
    public function  Init() {

        foreach ($this->aAssetTypes as $sType) {
            $this->aAssets[$sType] = Engine::GetEntity('ViewerAsset_Package' . ucfirst($sType));
        }
    }

    /**
     * @param $sType
     *
     * @return mixed
     */
    protected function _getAssetPackage($sType) {

        if (isset($this->aAssets[$sType])) {
            return $this->aAssets[$sType];
        }
    }

    /**
     * @param      $sType
     * @param      $aFiles
     * @param null $sAssetName
     * @param bool $bAppend
     * @param bool $bReplace
     */
    protected function _add($sType, $aFiles, $sAssetName = null, $bAppend = true, $bReplace = false) {

        if ($oAssetPackage = $this->_getAssetPackage($sType)) {
            if (!is_array($aFiles)) {
                $aFiles = array((string)$aFiles);
            }
            $aAddFiles = array();
            foreach ($aFiles as $sFileName => $aFileParams) {
                // extract & normalize full file path
                if (is_numeric($sFileName)) {
                    // single file name or array of options
                    if (!is_array($aFileParams)) {
                        $sFilePath = F::File_NormPath((string)$aFileParams);
                    } elseif (isset($aFileParams['name'])) {
                        $sFilePath = F::File_NormPath($aFileParams['name']);
                    } else {
                        $sFilePath = '';
                    }
                } else {
                    // filename => array of options
                    $sFilePath = F::File_NormPath((string)$sFileName);
                }
                // if file path defined
                if ($sFilePath) {
                    if (!is_array($aFileParams)) {
                        $aFileParams = array('file' => $sFilePath);
                    } else {
                        $aFileParams['file'] = $sFilePath;
                    }
                    if (!isset($aFileParams['name'])) {
                        $aFileParams['name'] = $aFileParams['file'];
                    }
                    if ($sAssetName) {
                        $aFileParams['asset'] = $sAssetName;
                    }
                    $aAddFiles[$aFileParams['name']] = $aFileParams;
                } else {
                    // TODO: Писать в лог ошибок
                }
            }
            if ($aAddFiles) {
                $oAssetPackage->AddFiles($aAddFiles, null, $bAppend, $bReplace);
            }
        }
    }

    /**
     * @param      $sType
     * @param      $aFiles
     * @param null $sAssetName
     */
    public function AddFiles($sType, $aFiles, $sAssetName = null) {

        return $this->_add($sType, $aFiles, $sAssetName);
    }

    /**
     * @param      $aFiles
     * @param null $sAssetName
     */
    public function AddJsFiles($aFiles, $sAssetName = null) {

        return $this->AddFiles('js', $aFiles, $sAssetName);
    }

    /**
     * @param      $aFiles
     * @param null $sAssetName
     */
    public function AddCssFiles($aFiles, $sAssetName = null) {

        return $this->AddFiles('css', $aFiles, $sAssetName);
    }

    /**
     * @param      $aFiles
     * @param null $sAssetName
     */
    public function AddLessFiles($aFiles, $sAssetName = null) {

        return $this->AddFiles('less', $aFiles, $sAssetName);
    }

    /**
     * @param      $aFiles
     * @param null $sAssetName
     */
    public function AddImgFiles($aFiles, $sAssetName = null) {

        return $this->AddFiles('img', $aFiles, $sAssetName);
    }

    /**
     * @param       $sFile
     * @param array $aParams
     * @param bool  $bReplace
     */
    public function AppendJs($sFile, $aParams = array(), $bReplace = false) {

        $this->_add('js', array($sFile => $aParams), null, true, $bReplace);
    }

    /**
     * @param       $sFile
     * @param array $aParams
     * @param bool  $bReplace
     */
    public function PrependJs($sFile, $aParams = array(), $bReplace = false) {

        $this->_add('js', array($sFile => $aParams), null, false, $bReplace);
    }

    /**
     * @param       $sFile
     * @param array $aParams
     * @param bool  $bReplace
     */
    public function AppendCss($sFile, $aParams = array(), $bReplace = false) {

        $this->_add('css', array($sFile => $aParams), null, true, $bReplace);
    }

    /**
     * @param       $sFile
     * @param array $aParams
     * @param bool  $bReplace
     */
    public function PrependCss($sFile, $aParams = array(), $bReplace = false) {

        $this->_add('css', array($sFile => $aParams), null, false, $bReplace);
    }

    /**
     * @param $sType
     */
    public function Clear($sType) {

        if ($oAssetPackage = $this->_getAssetPackage($sType)) {
            $oAssetPackage->Clear();
        }
    }

    /**
     *
     */
    public function ClearJs() {

        return $this->Clear('js');
    }

    /**
     *
     */
    public function ClearCss() {

        return $this->Clear('css');
    }

    /**
     * @param $sType
     * @param $aFiles
     */
    public function Exclude($sType, $aFiles) {

        if ($oAssetPackage = $this->_getAssetPackage($sType)) {
            $oAssetPackage->Exclude($aFiles);
        }
    }

    public function AddLink($sType, $sLink, $aParams = array()) {

        if ($oAssetPackage = $this->_getAssetPackage($sType)) {
            $oAssetPackage->AddLink($sType, $sLink, $aParams);
        }
    }

    public function Prepare() {

        foreach($this->aAssets as $oAssetPackage) {
            $oAssetPackage->Prepare();
        }
    }

    public function BuildHtmlLinks($sType = null) {

        $aLinks = array();
        if (!$sType) {
            foreach($this->aAssets as $oAssetPackage) {
                $aLinks = array_merge($aLinks, $oAssetPackage->BuildHtmlLinks());
            }
        } else {
            if ($oAssetPackage = $this->_getAssetPackage($sType)) {
                $aLinks = $oAssetPackage->BuildHtmlLinks();
            }
        }
        return $aLinks;
    }

}
// EOF