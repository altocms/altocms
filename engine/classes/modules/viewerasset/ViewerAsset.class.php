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
    const SLEEP_TIME = 5;
    const SLEEP_COUNT = 4;

    protected $aAssetTypes
        = array(
            'less',
            'js',
            'css',
        );

    /**
     * @var ModuleViewerAsset_EntityPackage[]
     */
    protected $aAssets = array();

    protected $aFiles = array();

    /**
     * Returns set of asset files
     *
     * @return array
     */
    public function getFiles() {

        return $this->aFiles;
    }

    /**
     * Calculate hash of file's dirname
     *
     * @param  string $sFile
     *
     * @return string
     */
    public function AssetFileHashDir($sFile) {

        if (substr($sFile, -1) == '/') {
            $sDir = $sFile;
        } else {
            $sDir = dirname($sFile);
        }
        return F::Crc32($sDir, true);
    }

    /**
     * Make path of asset file
     *
     * @param  string $sLocalFile
     * @param  string $sParentDir
     *
     * @return string
     */
    public function AssetFilePath($sLocalFile, $sParentDir = null) {

        if ($n = strpos($sLocalFile, '?')) {
            $sBasename = basename(substr($sLocalFile, 0, $n)) . '-' . F::Crc32(substr($sLocalFile, $n));
            $sExtension = F::File_GetExtension($sLocalFile);
            if ($sExtension) {
                $sBasename .= '.' . $sExtension;
            }
        } else {
            $sBasename = basename($sLocalFile);
        }
        $sResult = $this->AssetFileHashDir($sLocalFile) . '/' . $sBasename;
        if ($sParentDir) {
            if (substr($sParentDir, -1) != '/') {
                $sParentDir .= '/';
            }
            $sResult = $sParentDir . $sResult;
        }
        return $sResult;
    }

    /**
     * Converts file path into path to asset-resource
     *
     * @param  string $sLocalFile
     * @param  string $sParentDir
     *
     * @return string
     */
    public function AssetFileDir($sLocalFile, $sParentDir = null) {

        return F::File_GetAssetDir() . $this->AssetFilePath($sLocalFile, $sParentDir);
    }

    /**
     * Convert file path into URL to asset-resource
     *
     * @param  string $sLocalFile
     * @param  string $sParentDir
     *
     * @return string
     */
    public function AssetFileUrl($sLocalFile, $sParentDir = null) {

        return F::File_GetAssetUrl() . $this->AssetFilePath($sLocalFile, $sParentDir);
    }

    public function AssetFileDir2Url($sAssetFile) {

        $sFilePath = F::File_LocalPath($sAssetFile, F::File_GetAssetDir());
        return F::File_GetAssetUrl() . $sFilePath;
    }

    public function AssetFileUrl2Dir($sAssetFile) {

        $sFilePath = F::File_LocalPathUrl($sAssetFile, F::File_GetAssetUrl());
        return F::File_GetAssetDir() . $sFilePath;
    }

    /**
     * @param  string $sLocalFile
     * @param  string $sParentDir
     *
     * @return bool|string
     */
    public function File2Link($sLocalFile, $sParentDir = null) {

        $sAssetFile = $this->AssetFileDir($sLocalFile, $sParentDir);
        $aInfo = F::File_PathInfo($sLocalFile);
        if (F::File_Exists($sAssetFile) || F::File_Copy($aInfo['dirname'] . '/' . $aInfo['basename'], $sAssetFile)) {
            return $this->AssetFileUrl($sLocalFile, $sParentDir);
        }
        return false;
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
                $this->aAssets[$sType] = E::GetEntity('ViewerAsset_Package' . ucfirst($sType), $aParams);
                $oResult = $this->aAssets[$sType];
            } else {
                if (!isset($this->aAssets['*'])) {
                    $this->aAssets['*'] = E::GetEntity('ViewerAsset_Package');
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
                    isset($aOptions['replace']) ? $aOptions['replace'] : null
                );
            }
        }
    }

    /**
     * @param $aFiles
     */
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

    /**
     * @param        $sType
     * @param        $aFiles
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
            $sName = F::File_NormPath($sName);
            $aFileParams['file'] = F::File_NormPath($sFile);
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
        $aFileList = array();

        // seek wildcards - if name hase '*' then add files by pattern
        foreach ($aFiles as $sFileName => $aFileParams) {
            if (strpos($sFileName, '*')) {
                unset($aFiles[$sFileName]);
                $aFoundFiles = F::File_ReadFileList($sFileName, 0, true);
                if ($aFoundFiles) {
                    foreach($aFoundFiles as $sAddFile) {
                        $sAddType = F::File_GetExtension($sAddFile, true);
                        $aFileParams['name'] = $sAddFile;
                        $aFileParams['file'] = $sAddFile;
                        if ($sAddType == $sType) {
                            $aFileList[$sAddFile] = $aFileParams;
                        } else {
                            $this->AddFilesToAssets($sAddType, array($sAddFile => $aFileParams), $aOptions);
                        }
                    }
                }
            } else {
                $aFileList[$sFileName] = $aFileParams;
            }
        }

        foreach ($aFileList as $sFileName => $aFileParams) {
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

    /**
     * Add link to current asset pack
     *
     * @param $sType
     * @param $aLinks
     */
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
     * Clear file set of requested type
     *
     * @param string $sType
     */
    public function Clear($sType) {

        $this->aFiles[$sType] = array();
    }

    /**
     * Clear js-file set
     */
    public function ClearJs() {

        $this->Clear('js');
    }

    /**
     * Clear css-file set
     */
    public function ClearCss() {

        $this->Clear('css');
    }

    /**
     * LS-compatibility
     *
     * @param array $aFiles
     */
    public function ExcludeJs($aFiles) {

        $this->Exclude('js', $aFiles);
    }

    /**
     * LS-compatibility
     *
     * @param array $aFiles
     */
    public function ExcludeCss($aFiles) {

        $this->Exclude('css', $aFiles);
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

    /**
     * @param       $sType
     * @param       $sLink
     * @param array $aParams
     */
    public function AddLink($sType, $sLink, $aParams = array()) {

        $this->aFiles[$sType]['links'][$sLink] = $aParams;
    }

    /**
     * Returns hash for current asset pack
     *
     * @return string
     */
    public function GetHash() {

        $aData = array($this->aFiles, Config::Get('compress'), Config::Get('assets.version'));
        return md5(serialize($aData));
    }

    /**
     * Returns file name for cache of current asset pack
     *
     * @return string
     */
    public function GetAssetsCacheName() {

        return Config::Get('sys.cache.dir') . 'data/assets/' . $this->GetHash() . '.assets.dat';
    }

    /**
     * Returns name for check-file of current asset pack
     *
     * @return string
     */
    public function GetAssetsCheckName() {

        return F::File_GetAssetDir() . '_check/' . $this->GetHash() . '.assets.chk';
    }

    public function ClearAssetsCache() {

        $sDir = Config::Get('sys.cache.dir') . 'data/assets/';
        F::File_RemoveDir($sDir);
    }

    /**
     * Checks cache for current asset pack
     * If cache is present then returns one
     *
     * @return int|array
     */
    protected function _checkAssets() {

        $xResult = 0;
        $sFile = $this->GetAssetsCacheName();
        $sTmpFile = $sFile . '.tmp';

        if (is_file($sTmpFile)) {
            // tmp file cannot live more than 1 minutes
            $nTime = filectime($sTmpFile);
            if (!$nTime) {
                $nTime = F::File_GetContents($sTmpFile);
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

    protected function _resetAssets() {

        $sFile = $this->GetAssetsCacheName();
        F::File_PutContents($sFile . '.tmp', time(), LOCK_EX, true);
        F::File_Delete($sFile);
        F::File_Delete($this->GetAssetsCheckName());
    }

    /**
     * Save cache and check-file of current asset pack
     */
    protected function _saveAssets() {

        $sCheckFileName = $this->GetAssetsCheckName();
        F::File_PutContents($sCheckFileName, time(), LOCK_EX, true);
        $sCacheFileName = $this->GetAssetsCacheName();
        F::File_PutContents($sCacheFileName, F::Serialize($this->aAssets), LOCK_EX, true);
        F::File_Delete($sCacheFileName . '.tmp');
    }

    /**
     * Checks whether a set of files empty
     *
     * @return bool
     */
    protected function _isEmpty() {

        $aFiles = $this->getFiles();
        if (!empty($aFiles) && is_array($aFiles)) {
            foreach($aFiles as $sType => $aFileSet) {
                if (!empty($aFileSet)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Prepare current asset pack
     */
    public function Prepare() {

        if ($this->_isEmpty()) {
            return;
        }

        $bForcePreparation = Config::Get('compress.css.force') || Config::Get('compress.js.force');
        $xData = $this->_checkAssets();
        if ($xData) {
            if (is_array($xData)) {
                if (F::File_GetContents($this->GetAssetsCheckName())) {
                    // loads assets from cache
                    $this->aAssets = $xData;
                    if (!$bForcePreparation) {
                        return;
                    }
                }
            } else {
                // assets are making right now
                // may be need to wait?
                for ($i=0; $i<self::SLEEP_COUNT; $i++) {
                    sleep(self::SLEEP_TIME);
                    $xData = $this->_checkAssets();
                    if (is_array($xData)) {
                        $this->aAssets = $xData;
                        return;
                    }
                }
                // something wrong
                return;
            }
        }
        // May be assets are not complete
        if (!$this->aAssets && $this->aFiles && !$bForcePreparation) {
            $bForcePreparation = true;
        }

        if (!F::File_GetContents($this->GetAssetsCheckName()) || $bForcePreparation) {

            // reset assets here
            $this->_resetAssets();

            $this->aAssets = array();

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
            // PreProcess
            foreach($this->aAssets as $oAssetPackage) {
                if ($oAssetPackage->PreProcessBegin()) {
                    $bDone = ($bDone && $oAssetPackage->PreProcess());
                    $oAssetPackage->PreProcessEnd();
                }
            }
            if ($bDone) {
                $nStage += 1;
            }
            // Process
            foreach($this->aAssets as $oAssetPackage) {
                if ($oAssetPackage->ProcessBegin()) {
                    $bDone = ($bDone && $oAssetPackage->Process());
                    $oAssetPackage->ProcessEnd();
                }
            }
            if ($bDone) {
                $nStage += 1;
            }
            // PostProcess
            foreach($this->aAssets as $oAssetPackage) {
                if ($oAssetPackage->PostProcessBegin()) {
                    $bDone = ($bDone && $oAssetPackage->PostProcess());
                    $oAssetPackage->PostProcessEnd();
                }
            }
            if ($bDone) {
                $nStage += 1;
            }
        } else {
            $nStage = 3;
        }

        if ($nStage == 3) {
            $this->_saveAssets();
        }
    }

    /**
     * @param string $sType
     *
     * @return array
     */
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
        foreach ($aLinks as $sType => $aTypeLinks) {
            $aLinks[$sType] = array_unique($aTypeLinks);
        }
        return $aLinks;
    }

    /**
     * @return array
     */
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