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

    const TMP_TIME = 60;

    protected $aAssetTypes
        = array(
            'less',
            'js',
            'css',
        );

    protected $aAssets = array();

    protected $aFiles = array();
public function getFiles() { return $this->aFiles; }
    /**
     * Converts file path into path to asset-resource
     *
     * @param   string $sFile
     * @return  string
     */
    public function AssetFileDir($sFile) {

        return F::File_NormPath(F::File_GetAssetDir() . $this->Hash(dirname($sFile)) . '/' . basename($sFile));
    }

    /**
     * Convert file path into URL to asset-resource
     *
     * @param   string $sFile
     * @return  string
     */
    public function AssetFileUrl($sFile) {

        return F::File_NormPath(F::File_GetAssetUrl() . $this->Hash(dirname($sFile)) . '/' . basename($sFile));
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

    public function AddAssetFiles($aFiles) {

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

    public function AddFiles($sType, $aFiles, $sAssetName = null, $aOptions = array()) {

        if (!is_array($aFiles)) {
            $aFiles = array(
                array('file' => (string)$aFiles),
            );
        }
        $aAssetFiles = array();
        foreach ($aFiles as $sFileName => $aFileParams) {
            // extract file path
            if (is_numeric($sFileName)) {
                // single file name or array of options
                if (!is_array($aFileParams)) {
                    $sName = $sFile = (string)$aFileParams;
                } else {
                    $sFile = isset($aFileParams['file']) ? $aFileParams['file'] : null;
                    $sName = isset($aFileParams['name']) ? $aFileParams['name'] : $sFile;
                }
            } else {
                // filename => array of options
                if (isset($aFileParams['file'])) {
                    $sFile = $aFileParams['file'];
                } else {
                    $sFile = (string)$sFileName;
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
        // Appends files for future preparation
        foreach ($aAssetFiles as $sName => $aFileParams) {
            $aFileParams['options'] = $aOptions;
            $this->aFiles[$sType]['files'][$sName] = $aFileParams;
        }
    }

    /**
     * @param string $sType
     * @param array  $aFiles
     * @param array  $aOptions
     */
    public function AddFilesToAssets($sType, $aFiles, $aOptions = array()) {

        if (!is_array($aFiles)) {
            $aFiles = array(
                array('file' => (string)$aFiles),
            );
        }
        $aAssetFiles = array();
        foreach ($aFiles as $sFileName => $aFileParams) {
            // if name hase '*' then add files by pattern
            if (strpos($sFileName, '*')) {
                $aFiles = F::File_ReadFileList($sFileName, 0, true);
                if ($aFiles) {
                    $aAddFiles = array();
                    foreach($aFiles as $sAddFile) {
                        $sAddType = F::File_GetExtension($sAddFile);
                        $aFileParams['name'] = $sAddFile;
                        $aFileParams['file'] = $sAddFile;
                        $aAddFiles[$sAddType][$sAddFile] = $aFileParams;
                    }
                    foreach ($aAddFiles as $sAddType=>$aAdditionalFiles) {
                        $this->AddFilesToAssets($sAddType, $aAdditionalFiles, $aOptions);
                    }
                }
                continue;
            }
            // extract & normalize full file path
            if (isset($aFileParams['file'])) {
                $sFile = F::File_NormPath($aFileParams['file']);
            } else {
                $sFile = F::File_NormPath((string)$sFileName);
            }
            $sName = isset($aFileParams['name']) ? $aFileParams['name'] : $sFile;
            if (!is_array($aFileParams)) {
                $aFileParams = array();
            }
            $aFileParams['file'] = F::File_NormPath($sFile);
            $aFileParams['name'] = F::File_NormPath($sName);
            $aAssetFiles[$sName] = $aFileParams;
        }
        return $this->_add($sType, $aAssetFiles, $aOptions);
    }

    public function AddLinksToAssets($sType, $aLinks) {

        foreach ($aLinks as $sLink => $aParams) {
            // Add links to assets
            if ($oAssetPackage = $this->_getAssetPackage($sType)) {
                $oAssetPackage->AddLink($sType, $sLink, $aParams);
            }
        }
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

        $this->aFiles[$sType] = array();
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

        foreach ($aFiles as $aFileParams) {
            if (is_array($aFileParams)) {
                if (isset($aFileParams['name'])) {
                    $sName = $aFileParams['name'];
                } else {
                    $sName = $aFileParams['file'];
                }
            } else {
                $sName = (string)$aFileParams;
            }
        }
        if (isset($this->aFiles[$sType]['files'][$sName])) {
            unset($this->aFiles[$sType]['files'][$sName]);
        } elseif (isset($this->aFiles[$sType]['links'][$sName])) {
            unset($this->aFiles[$sType]['links'][$sName]);
        }
    }

    public function AddLink($sType, $sLink, $aParams = array()) {

        $this->aFiles[$sType]['links'][$sLink] = $aParams;
    }

    public function GetHash() {

        return md5(serialize($this->aFiles));
    }

    protected function _getAssetsCacheName() {

        return Config::Get('sys.cache.dir') . 'data/' . $this->GetHash() . '.assets';
    }

    protected function _checkAssets() {

        $xResult = 0;
        $sFile = $this->_getAssetsCacheName();
        $sTmpFile = $sFile . '.tmp';
        if (is_file($sTmpFile)) {
            // tmp file cannot live more than 1 minutes
            $nTime = filectime($sTmpFile);
            if (!$nTime) {
                $nTime = F::File_GetContents($sFile);
            }
            if (time() < $nTime + self::TMP_TIME) {
                $xResult = 1;
            }
        } elseif (is_file($sFile)) {
            if ($xData = F::File_GetContents($sFile)) {
                $xResult = F::Unserialize($xData);
            }
        }
        return $xResult;
    }

    protected function _saveAssets() {

        $sFile = $this->_getAssetsCacheName();
        F::File_PutContents($sFile, F::Serialize($this->aAssets));
        F::File_Delete($sFile . '.tmp', $this->aAssets);
    }

    public function Prepare() {

        $xData = $this->_checkAssets();
        if ($xData) {
            if (is_array($xData)) {
                // loads assets from cache
                $this->aAssets = $xData;
                return;
            } else {
                // assets are making right now
                // may be need to wait?
                return;
            }
        }
        // makes assets here
        $sFile = $this->_getAssetsCacheName();
        F::File_PutContents($sFile . '.tmp', time());

        // Add files & links to assets
        foreach ($this->aFiles as $sType => $aData) {
            if (isset($aData['files'])) {
                $this->AddFilesToAssets($sType, $aData['files']);
            }
            if (isset($aData['links'])) {
                $this->AddLinksToAssets($sType, $aData['links']);
            }
        }

        $nStage = 0;
        $bDone = true;
        foreach($this->aAssets as $oAssetPackage) {
            if ($oAssetPackage->PreProcessBegin()) {
                $bDone = ($bDone && $oAssetPackage->PreProcess());
                $oAssetPackage->PreProcessEnd();
            }
        }
        if ($bDone) {
            $nStage += 1;
        }
        foreach($this->aAssets as $oAssetPackage) {
            if ($oAssetPackage->ProcessBegin()) {
                $bDone = ($bDone && $oAssetPackage->Process());
                $oAssetPackage->ProcessEnd();
            }
        }
        if ($bDone) {
            $nStage += 1;
        }
        foreach($this->aAssets as $oAssetPackage) {
            if ($oAssetPackage->PostProcessBegin()) {
                $bDone = ($bDone && $oAssetPackage->PostProcess());
                $oAssetPackage->PostProcessEnd();
            }
        }
        if ($bDone) {
            $nStage += 1;
        }

        if ($nStage == 3) {
            $this->_saveAssets();
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