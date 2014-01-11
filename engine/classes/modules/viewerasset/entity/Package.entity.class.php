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

class ModuleViewerAsset_EntityPackage extends Entity {

    protected $sOutType = '*';
    protected $sAssetType = '';

    protected $bMerge = false;
    protected $bCompress = false;

    protected $aFiles = array();

    protected $aAssetNames = array();
    protected $aAssets = array();

    protected $aLinks = array();
    protected $aHtmlLinkParams = array();

    protected $oCompressor;

    public function __construct($aParams = array()) {

        if (isset($aParams['out_type'])) {
            $this->sOutType = $aParams['out_type'];
        }
        if (isset($aParams['asset_type'])) {
            $this->sAssetType = $aParams['asset_type'];
        }
        if ($this->sOutType) {
            $this->bMerge = (bool)Config::Get('compress.' . $this->sOutType . '.merge');
            $this->bCompress = (bool)Config::Get('compress.' . $this->sOutType . '.use');
        }
    }

    public function Init() {

        $this->aHtmlLinkParams = array();
    }

    protected function _crc($sPath) {

        return str_pad(dechex(F::Crc32($sPath)), 8, '0', STR_PAD_LEFT);
    }

    public function GetHash() {

        return $this->sAssetType . '-' . md5(serialize($this->aFiles));
    }

    /**
     * Добавляет ссылку в набор
     *
     * @param       $sOutType
     * @param       $sLink
     * @param array $aParams
     */
    public function AddLink($sOutType, $sLink, $aParams = array()) {

        if ($sOutType != $this->sOutType) {
            $this->ViewerAsset_AddLinksToAssets('*', array($sLink => $aParams));
        } else {
            $this->aLinks[] = array_merge($aParams, array('link' => $sLink));
        }
    }

    /**
     * Сжатие контента
     *
     * @param $sContents
     *
     * @return mixed
     */
    public function Compress($sContents) {

        return $sContents;
    }

    /**
     * Обработка файла
     *
     * @param $sFile
     * @param $sDestination
     *
     * @return mixed
     */
    public function PrepareFile($sFile, $sDestination) {

        return F::File_Copy($sFile, $sDestination);
    }

    /**
     * Обработка контента
     *
     * @param $sContents
     * @param $sSource
     *
     * @return string
     */
    public function PrepareContents($sContents, $sSource) {

        return $sContents;
    }

    /**
     * Создание ресурса из одиночного файла
     *
     * @param $sAsset
     * @param $aFileParams
     *
     * @return bool
     */
    public function MakeSingle($sAsset, $aFileParams) {

        $sFile = $aFileParams['file'];
        if (isset($aFileParams['dir_from'])) {
            $sLocalPath = F::File_LocalPath(dirname($sFile), $aFileParams['dir_from']);
            $sDir = $aFileParams['dir_from'];
        } else {
            $sLocalPath = '';
            $sDir = dirname($sFile);
        }
        if ($aFileParams['merge']) {
            $sSubdir = $this->_crc($sAsset . $sDir);
        } else {
            $sSubdir = $this->_crc($sDir);
        }
        if ($sLocalPath) {
            $sSubdir .= '/' . $sLocalPath;
        }
        $sDestination = F::File_GetAssetDir() . $sSubdir . '/' . basename($sFile);
        if (!$this->CheckDestination($sDestination)) {
            if ($sDestination = $this->PrepareFile($sFile, $sDestination)) {
                $this->AddLink($aFileParams['info']['extension'], F::File_Dir2Url($sDestination), $aFileParams);
            } else {
                F::SysWarning('Can not prepare asset file "' . $sFile . '"');
                return false;
            }
        } else {
            $this->AddLink($aFileParams['info']['extension'], F::File_Dir2Url($sDestination), $aFileParams);
        }
        return true;
    }

    /**
     * Создание ресурса из множества файлов
     *
     * @param $sAsset
     * @param $aFiles
     *
     * @return bool
     */
    public function MakeMerge($sAsset, $aFiles) {

        $sDestination = F::File_GetAssetDir() . md5($sAsset . serialize($aFiles)) . '.' . $this->sOutType;
        if (!$this->CheckDestination($sDestination)) {
            $sContents = '';
            $bCompress = true;
            $bPrepare = null;
            foreach ($aFiles as $aFileParams) {
                $sFileContents = F::File_GetContents($aFileParams['file']);
                $sContents .= $this->PrepareContents($sFileContents, $aFileParams['file']) . PHP_EOL;
                if (isset($aFileParams['compress'])) {
                    $bCompress = $bCompress && (bool)$aFileParams['compress'];
                }
                // Если хотя бы один файл из набора нужно выводить, то весь набор выводится
                if ((is_null($bPrepare) || $bPrepare === true) && isset($aFileParams['prepare']) && !$aFileParams['prepare']) {
                    $bPrepare = false;
                }
            }
            if (F::File_PutContents($sDestination, $sContents)) {
                $aParams = array(
                    'file' => $sDestination,
                    'asset' => $sAsset,
                    'compress' => $bCompress,
                    'prepare' => is_null($bPrepare) ? false : $bPrepare,
                );
                $this->AddLink($this->sOutType, F::File_Dir2Url($sDestination), $aParams);
            } else {
                F::SysWarning('Can not write asset file "' . $sDestination . '"');
                return false;
            }
        } else {
            $aParams = array(
                'file' => $sDestination,
                'asset' => $sAsset,
                'compress' => $this->bCompress,
                'prepare' => false,
            );
            $this->AddLink($this->sOutType, F::File_Dir2Url($sDestination), $aParams);
        }
        return true;
    }

    /**
     * Проверка итогового файла назначения
     *
     * @param $sDestination
     *
     * @return bool
     */
    public function CheckDestination($sDestination) {

        // Проверка минифицированного файла
        if (substr($sDestination, -strlen($this->sOutType) - 5) == '.min.' . $this->sOutType) {
            return F::File_Exists($sDestination);
        }
        $sDestinationMin = F::File_SetExtension($sDestination, 'min.' . $this->sOutType);
        if ($this->bCompress) {
            return F::File_Exists($sDestinationMin) || F::File_Exists($sDestination);
        }
        return F::File_Exists($sDestination);
    }

    /**
     * Препроцессинг
     */
    public function PreProcess() {

        $bResult = true;

        // Создаем окончательные наборы, сливая prepend и append
        $this->aAssets = array();
        if ($this->aFiles) {
            foreach ($this->aAssetNames as $sAsset) {
                $aFileStacks = $this->aFiles[$sAsset];
                if (isset($aFileStacks['_prepend_']) && isset($aFileStacks['_append_'])) {
                    if ($aFileStacks['_prepend_'] && $aFileStacks['_append_']) {
                        $this->aAssets[$sAsset] = array_merge(
                            array_reverse($aFileStacks['_prepend_']), $aFileStacks['_append_']
                        );
                    } else {
                        if (!$aFileStacks['_append_']) {
                            $this->aAssets[$sAsset] = array_reverse($aFileStacks['_prepend_']);
                        } else {
                            $this->aAssets[$sAsset] = $aFileStacks['_append_'];
                        }
                    }
                }
            }
        }

        // Обрабатываем наборы
        foreach ($this->aAssets as $sAsset => $aFiles) {
            if (count($aFiles) == 1) {
                // Одиночный файл
                $aFileParams = array_shift($aFiles);
                if ($aFileParams['throw']) {
                    // Throws without prepare
                    $this->AddLink($aFileParams['info']['extension'], $aFileParams['file'], $aFileParams);
                } else {
                    // Prepares single file
                    $this->MakeSingle($sAsset, $aFileParams);
                }
            } else {
                // Prepares set of several files
                $this->MakeMerge($sAsset, $aFiles);
            }
        }

        return $bResult;
    }

    public function Process() {

        return true;
    }

    public function PostProcess() {

        return true;
    }

    protected function _prepareParams($sFileName, $aFileParams, $sAssetName) {

        // Проверка набора параметров файла
        if (!$aFileParams) {
            $aFileParams = array('file' => F::File_NormPath($sFileName));
        } elseif (!isset($aFileParams['file'])) {
            $aFileParams['file'] = F::File_NormPath($sFileName);
        }
        $aFileParams['info'] = F::File_PathInfo($aFileParams['file']);

        // Ссылка или локальный файл
        if (isset($aFileParams['info']['scheme']) && $aFileParams['info']['scheme']) {
            $aFileParams['link'] = true;
        } else {
            $aFileParams['link'] = false;
        }
        // Ссылки пропускаются без обработки
        $aFileParams['throw'] = $aFileParams['link'];

        // По умолчанию файл сливается с остальными,
        // но хаки (с параметром 'browser') и внешние файлы (ссылки) не сливаются
        if (isset($aFileParams['browser']) || $aFileParams['throw']) {
            $aFileParams['merge'] = false;
        }
        if (!isset($aFileParams['merge'])) {
            $aFileParams['merge'] = true;
        }
        if (!isset($aFileParams['compress'])) {
            $aFileParams['compress'] = $this->bCompress;
        }
        if ($this->bMerge && $aFileParams['merge']) {
            // Определяем имя набора
            if (!$sAssetName) {
                if (isset($aFileParams['asset'])) {
                    $sAssetName = $aFileParams['asset'];
                } elseif (isset($aFileParams['block'])) {
                    $sAssetName = $aFileParams['block'];
                } // LS compatible
                else {
                    $sAssetName = 'default';
                }
            }
        } else {
            // Если слияние отключено, то каждый набор - это отдельный файл
            $sAssetName = F::File_NormPath($sFileName);
            $aFileParams['merge'] = false;
        }
        $aFileParams['asset'] = $sAssetName;
        if (!isset($aFileParams['name'])) {
            $aFileParams['name'] = $sFileName;
        }
        if (!isset($aFileParams['browser'])) {
            $aFileParams['browser'] = null;
        }
        $aFileParams['prepare'] = isset($aFileParams['prepare'])? (bool)isset($aFileParams['prepare']) : false;
        $aFileParams['name'] = F::File_NormPath($aFileParams['name']);

        return $aFileParams;
    }

    protected function _add($sFileName, $aFileParams, $sAssetName = null, $bPrepend = false, $bReplace = false) {

        $aFileParams = $this->_prepareParams($sFileName, $aFileParams, $sAssetName);
        $sName = $aFileParams['name'];
        $sAssetName = $aFileParams['asset'];
        // If this asset does not exist then add it into stack
        if (!isset($this->aFiles[$sAssetName])) {
            $this->aFiles[$sAssetName] = array('_append_' => array(), '_prepend_' => array());
            if ($bPrepend) {
                array_unshift($this->aAssetNames, $sAssetName);
            } else {
                $this->aAssetNames[] = $sAssetName;
            }
        }
        if (isset($this->aFiles[$sAssetName]['_append_'][$sName])) {
            if ($bReplace) {
                unset($this->aFiles[$sAssetName]['_append_'][$sName]);
            } else {
                return 0;
            }
        } elseif (isset($this->aFiles[$sAssetName]['_prepend_'][$sName])) {
            if ($bReplace) {
                unset($this->aFiles[$sAssetName]['_prepend_'][$sName]);
            } else {
                return 0;
            }
        }
        $this->aFiles[$sAssetName][$bPrepend ? '_prepend_' : '_append_'][$sName] = $aFileParams;
        return 1;
    }

    public function AddFiles($aFiles, $sAssetName = null, $bPrepend = false, $bReplace = false) {

        foreach ($aFiles as $sName => $aFileParams) {
            $this->_add($sName, $aFileParams, $sAssetName, $bPrepend, $bReplace);
        }
    }

    public function Clear($sAssetName = null) {

        if ($sAssetName) {
            if (isset($this->aFiles[$sAssetName])) {
                unset($this->aFiles[$sAssetName]);
            }
        } else {
            $this->aFiles = array();
        }
    }

    public function Exclude($aFiles, $sAssetName = null) {

        foreach ($aFiles as $sFileName => $aFileParams) {
            $aFileParams = $this->_prepareParams($sFileName, $aFileParams, $sAssetName);
            $sName = $aFileParams['name'];
            if (!isset($this->aFiles[$sAssetName])) {
                $this->aFiles[$sAssetName] = array('_append_' => array(), '_prepend_' => array());
            }
            if (isset($this->aFiles[$sAssetName]['_append_'][$sName])) {
                unset($this->aFiles[$sAssetName]['_append_'][$sName]);
            } elseif (isset($this->aFiles[$sAssetName]['_prepend_'][$sName])) {
                unset($this->aFiles[$sAssetName]['_prepend_'][$sName]);
            }
        }
    }

    protected function _stageBegin($nStage) {

        $sFile = F::File_GetAssetDir() . '_check/' . $this->GetHash();
        if ($aCheckFiles = glob($sFile . '.{1,2,3}.begin.tmp', GLOB_BRACE)) {
            return false;
        } elseif (($nStage == 2) && ($aCheckFiles = glob($sFile . '.{2,3}.end.tmp', GLOB_BRACE))) {
            return false;
        } elseif (($nStage == 3) && F::File_Exists($sFile . '.3.end.tmp')) {
            return false;
        }
        return F::File_PutContents($sFile . '.' . $nStage . '.begin.tmp', time());
    }

    protected function _stageEnd($nStage, $bFinal = false) {

        $sFile = F::File_GetAssetDir() . '_check/' . $this->GetHash();
        F::File_PutContents($sFile . '.' . $nStage . '.end.tmp', time());
        for ($n = 1; $n <= $nStage; $n++) {
            F::File_Delete($sFile . '.' . $n . '.begin.tmp');
            if ($n < $nStage || $bFinal) {
                F::File_Delete($sFile . '.' . $n . '.end.tmp');
            }
        }
    }

    public function PreProcessBegin() {

        return $this->_stageBegin('1');
    }

    public function PreProcessEnd() {

        return $this->_stageEnd('1');
    }

    public function ProcessBegin() {

        return $this->_stageBegin('2');
    }

    public function ProcessEnd() {

        return $this->_stageEnd('2');
    }

    public function PostProcessBegin() {

        return $this->_stageBegin('3');
    }

    public function PostProcessEnd() {

        return $this->_stageEnd('3', true);
    }

    public function Prepare() {

        if ($this->PreProcessBegin()) {
            $this->PreProcess();
            $this->PreProcessEnd();
        }
        if ($this->ProcessBegin()) {
            $this->Process();
            $this->ProcessEnd();
        }
        if ($this->PostProcessBegin()) {
            $this->PostProcess();
            $this->PostProcessEnd();
        }
    }

    public function GetLinks($bPreparedOnly = null, $bSkipWithoutName = false) {

        if (is_null($bPreparedOnly)) {
            return $this->aLinks;
        } else {
            $aResult = array();
            foreach ($this->aLinks as $sIdx => $aLinkData) {
                if (($aLinkData['prepare'] == (bool)$bPreparedOnly) && (!$bSkipWithoutName || $aLinkData['file'] != $aLinkData['name'])) {
                    $aResult[$sIdx] = $aLinkData;
                }
            }
            return $aResult;
        }
    }

    public function GetBrowserLinks() {

        return $this->aBrowserLinks;
    }

    public function BuildLink($aLink) {

        $sResult = '<' . $this->aHtmlLinkParams['tag'] . ' ';
        foreach ($this->aHtmlLinkParams['attr'] as $sName => $sVal) {
            if ($sVal == '@link') {
                $sResult .= $sName . '="' . $aLink['link'] . '" ';
            } else {
                $sResult .= $sName . '="' . $sVal . '" ';
            }
        }
        if ($this->aHtmlLinkParams['pair']) {
            $sResult .= '></' . $this->aHtmlLinkParams['tag'] . '>';
        } else {
            $sResult .= '/>';
        }
        if (isset($aLink['browser'])) {
            return "<!--[if {$aLink['browser']}]>$sResult<![endif]-->";
        }
        return $sResult;
    }

    public function GetLinksArray($bPreparedOnly = null, $bSkipWithoutName = false) {

        $aLinks = $this->GetLinks($bPreparedOnly, $bSkipWithoutName);
        $aResult = array();
        foreach($aLinks as $aLinkData) {
            $aResult[$this->sOutType][$aLinkData['name']] = $aLinkData['link'];
        }
        return $aResult;
    }

    public function BuildHtmlLinks($bPreparedOnly = false) {

        $aResult = array();
        foreach ($this->aLinks as $aLinkData) {
            if ($aLinkData['prepare'] == (bool)$bPreparedOnly) {
                $aResult[$this->sOutType][] = $this->BuildLink($aLinkData);
            }
        }
        return $aResult;
    }

}

// EOF