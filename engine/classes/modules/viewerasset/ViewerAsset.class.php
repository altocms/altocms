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
        );

    protected $aAssets = array();

    /**
     * Преобразует путь к файлу в путь к asset-ресурсу
     *
     * @param   string $sFile
     * @return  string
     */
    public function AssetFileDir($sFile) {

        return F::File_NormPath($this->Viewer_GetAssetDir() . $this->Hash(dirname($sFile)) . '/' . basename($sFile));
    }

    /**
     * Преобразует путь к файлу в URL к asset-ресурсу
     *
     * @param   string $sFile
     * @return  string
     */
    public function AssetFileUrl($sFile) {

        return F::File_NormPath($this->Viewer_GetAssetUrl() . $this->Hash(dirname($sFile)) . '/' . basename($sFile));
    }

    /**
     *
     */
    public function  Init() {

    }

    /**
     * @param string $sType
     *
     * @return ModuleViewerAsset_EntityPackage
     */
    protected function _getAssetPackage($sType) {

        $oResult = null;
        if (!isset($this->aAssets[$sType])) {
            if (in_array($sType, $this->aAssetTypes)) {
                $aParams = array('asset_type' => $sType);
                $this->aAssets[$sType] = Engine::GetEntity('ViewerAsset_Package' . ucfirst($sType), $aParams);
                $oResult = $this->aAssets[$sType];
            } else {
                if (!isset($this->aAssets['*'])) {
                    $this->aAssets['*'] = Engine::GetEntity('ViewerAsset_Package');
                }
                $oResult = $this->aAssets['*'];
            }
        } else {
            $oResult = $this->aAssets[$sType];
        }
        return $oResult;
    }

    /**
     * @param string       $sType
     * @param array|string $aFiles
     * @param array        $aOptions
     */
    protected function _add($sType, $aFiles, $aOptions = array()) {

        if ($oAssetPackage = $this->_getAssetPackage($sType)) {
            $aAddFiles = array();
            foreach ($aFiles as $sFileName => $aFileParams) {
                // extract & normalize full file path
                if (isset($aFileParams['file'])) {
                    $sFilePath = F::File_NormPath($aFileParams['file']);
                } else {
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
                    $aAddFiles[$aFileParams['name']] = $aFileParams;
                } else {
                    F::SysWarning('Can not define asset file path "' . $sFilePath . '"');
                }
            }
            if ($aAddFiles) {
                $oAssetPackage->AddFiles(
                    $aAddFiles, null,
                    isset($aOptions['prepend']) ? $aOptions['prepend'] : false,
                    isset($aOptions['replace']) ? $aOptions['replace'] : false
                );
            }
        }
    }

    public function AssetMake($aFiles) {

        $this->aAssets = array();

        //$sPakHash = md5(serialize($aFiles));
        if (isset($aFiles['js'])) {
            $this->AddJsFiles($aFiles['js']);
        }
        if (isset($aFiles['css'])) {
            $this->AddCssFiles($aFiles['css']);
        }
        if (isset($aFiles['less'])) {
            $this->AddLessFiles($aFiles['less']);
        }

    }

    /**
     * @param string $sType
     * @param array  $aFiles
     * @param string $sAssetName
     * @param array  $aOptions
     */
    public function AddFiles($sType, $aFiles, $sAssetName = null, $aOptions = array()) {

        if (!is_array($aFiles)) {
            $aFiles = array(
                array('file' => (string)$aFiles),
            );
        }
        $aAssetFiles = array();
        foreach ($aFiles as $sFileName => $aFileParams) {
            $sName = '';
            // if name hase '*' then add files by pattern
            if (strpos($sFileName, '*')) {
                $aFiles = F::File_ReadFileList($sFileName, 0, true);
                if ($aFiles) {
                    $aAddFiles = array();
                    foreach($aFiles as $sAddFile) {
                        $sAddType = F::File_GetExtension($sAddFile);
                        $aAddFiles[$sAddType][$sAddFile] = $aFileParams;
                    }
                    foreach ($aAddFiles as $sAddType=>$aAdditionalFiles) {
                        $this->AddFiles($sAddType, $aAdditionalFiles, $sAssetName, $aOptions);
                    }
                }
                continue;
            }
            // extract & normalize full file path
            if (is_numeric($sFileName)) {
                // single file name or array of options
                if (!is_array($aFileParams)) {
                    $sName = $sFile = F::File_NormPath((string)$aFileParams);
                } else {
                    $sFile = isset($aFileParams['file']) ? $aFileParams['file'] : null;
                    $sName = isset($aFileParams['name']) ? $aFileParams['name'] : $sFile;
                }
            } else {
                // filename => array of options
                if (isset($aFileParams['file'])) {
                    $sFile = F::File_NormPath($aFileParams['file']);
                } else {
                    $sFile = F::File_NormPath((string)$sFileName);
                }
                $sName = isset($aFileParams['name']) ? $aFileParams['name'] : $sFile;
            }
            if (!is_array($aFileParams)) {
                $aFileParams = array();
            }
            $aFileParams['file'] = $sFile;
            $aFileParams['name'] = $sName;
            if ($sAssetName) {
                $aFileParams['asset'] = $sAssetName;
            }
            $aAssetFiles[$sName] = $aFileParams;
        }
        return $this->_add($sType, $aAssetFiles, $aOptions);
    }

    /**
     * @param array  $aFiles
     * @param string $sAssetName
     * @param array  $aOptions
     */
    public function AddJsFiles($aFiles, $sAssetName = null, $aOptions = array()) {

        return $this->AddFiles('js', $aFiles, $sAssetName, $aOptions);
    }

    /**
     * @param array  $aFiles
     * @param string $sAssetName
     * @param array  $aOptions
     */
    public function AddCssFiles($aFiles, $sAssetName = null, $aOptions = array()) {

        return $this->AddFiles('css', $aFiles, $sAssetName, $aOptions);
    }

    /**
     * @param array  $aFiles
     * @param string $sAssetName
     * @param array  $aOptions
     */
    public function AddLessFiles($aFiles, $sAssetName = null, $aOptions = array()) {

        return $this->AddFiles('less', $aFiles, $sAssetName, $aOptions);
    }

    /**
     * @param string $sFile
     * @param array $aParams
     * @param bool  $bReplace
     */
    public function AppendJs($sFile, $aParams = array(), $bReplace = false) {

        $aOptions = array(
            'prepend' => false,
            'replace' => (bool)$bReplace,
        );
        return $this->AddFiles('js', array($sFile => $aParams), null, $aOptions);
    }

    /**
     * @param string $sFile
     * @param array $aParams
     * @param bool  $bReplace
     */
    public function PrependJs($sFile, $aParams = array(), $bReplace = false) {

        $aOptions = array(
            'prepend' => true,
            'replace' => (bool)$bReplace,
        );
        return $this->AddFiles('js', array($sFile => $aParams), null, $aOptions);
    }

    /**
     * @param string $sFile
     * @param array  $aParams
     * @param bool   $bReplace
     */
    public function PrepareJs($sFile, $aParams = array(), $bReplace = false) {

        $aOptions = array(
            'prepare' => true,
            'replace' => (bool)$bReplace,
        );
        return $this->AddFiles('js', array($sFile => $aParams), null, $aOptions);
    }

    /**
     * @param string $sFile
     * @param array $aParams
     * @param bool  $bReplace
     */
    public function AppendCss($sFile, $aParams = array(), $bReplace = false) {

        $aOptions = array(
            'prepend' => false,
            'replace' => (bool)$bReplace,
        );
        return $this->AddFiles('css', array($sFile => $aParams), null, $aOptions);
    }

    /**
     * @param string $sFile
     * @param array $aParams
     * @param bool  $bReplace
     */
    public function PrependCss($sFile, $aParams = array(), $bReplace = false) {

        $aOptions = array(
            'prepend' => true,
            'replace' => (bool)$bReplace,
        );
        return $this->AddFiles('css', array($sFile => $aParams), null, $aOptions);
    }

    /**
     * @param string $sFile
     * @param array $aParams
     * @param bool  $bReplace
     */
    public function PrepareCss($sFile, $aParams = array(), $bReplace = false) {

        $aOptions = array(
            'prepare' => true,
            'replace' => (bool)$bReplace,
        );
        return $this->AddFiles('css', array($sFile => $aParams), null, $aOptions);
    }

    /**
     * @param string $sType
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
     * @param string $sType
     * @param array $aFiles
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
            if ($oAssetPackage->PreProcessBegin()) {
                $oAssetPackage->PreProcess();
                $oAssetPackage->PreProcessEnd();
            }
        }
        foreach($this->aAssets as $oAssetPackage) {
            if ($oAssetPackage->ProcessBegin()) {
                $oAssetPackage->Process();
                $oAssetPackage->ProcessEnd();
            }
        }
        foreach($this->aAssets as $oAssetPackage) {
            if ($oAssetPackage->PostProcessBegin()) {
                $oAssetPackage->PostProcess();
                $oAssetPackage->PostProcessEnd();
            }
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

    public function GetPreparedAssetLinks() {

        $aResult = array();
        foreach($this->aAssets as $oAssetPackage) {
            if ($aLinks = $oAssetPackage->GetLinksArray(true, true)) {
                $aResult = F::Array_Merge($aResult, reset($aLinks));
            }
        }
        return $aResult;
    }

}
// EOF