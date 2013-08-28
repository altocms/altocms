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

abstract class ModuleViewerAsset_EntityPackage extends Entity {

    protected $sOutType = '';

    protected $bMerge = false;
    protected $bCompress = false;

    protected $aFiles = array();
    protected $aAssets = array();
    protected $aLinks = array();
    protected $aHtmlLinkParams = array();

    public function __construct($sType = null) {

        $this->sOutType = ($sType ? $sType : $this->sOutType);
        if ($this->sOutType) {
            $this->bMerge = (bool)Config::Get('compress.' . $this->sOutType . '.merge');
            $this->bCompress = (bool)Config::Get('compress.' . $this->sOutType . '.use');
        }
    }

    public function Init() {

        $this->aHtmlLinkParams = array();
    }

    protected function _hash($sPath) {

        return sprintf('%x', crc32($sPath));
    }

    /**
     * Преобразует путь к файлу в путь к asset-ресурсу
     *
     * @param   string $sFile
     *
     * @return  string
     */
    public function AssetFileDir($sFile) {

        return F::File_NormPath($this->GetAssetDir() . $this->Hash(dirname($sFile)) . '/' . basename($sFile));
    }

    /**
     * Преобразует URL к файлу в URL к asset-ресурсу
     *
     * @param   string $sFile
     *
     * @return  string
     */
    public function AssetFileUrl($sFile) {

        return F::File_NormPath($this->GetAssetUrl() . $this->Hash(dirname($sFile)) . '/' . basename($sFile));
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
            $this->ViewerAsset->AddLink($sOutType, $sLink, $aParams);
        } else {
            $this->aLinks[] = array_merge($aParams, array('link'=> $sLink));
        }
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
     * @param $sDestination
     *
     * @return mixed
     */
    public function PrepareContents($sContents, $sDestination) {

        if (F::File_PutContents($sDestination, $sContents)) {
            return $sDestination;
        }
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
        if ($aFileParams['merge']) {
            $sSubdir = $this->_hash($sAsset . dirname($sFile));
        } else {
            $sSubdir = $this->_hash(dirname($sFile));
        }
        $sDestination = $this->Viewer_GetAssetDir() . $sSubdir . '/' . basename($sFile);
        if ($sDestination = $this->PrepareFile($sFile, $sDestination)) {
            $this->AddLink($aFileParams['info']['extension'], F::File_Dir2Url($sDestination), $aFileParams);
        } else {
            // TODO: Писать в лог ошибок
            return false;
        }
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

        $sFileName = $this->Viewer_GetAssetDir() . md5($sAsset . serialize($aFiles)) . '.' . $this->sOutType;
        $sContents = '';
        foreach($aFiles as $aFileParams) {
            $sContents .= F::File_GetContents($aFileParams['file']) . PHP_EOL;
        }
        if ($sDestination = $this->PrepareContents($sContents, $sFileName)) {
            $this->AddLink($aFileParams['info']['extension'], F::File_Dir2Url($sDestination), $aFileParams);
        } else {
            // TODO: Писать в лог ошибок
            return false;
        }
    }

    public function PreProcess() {

        // Создаем окончательные наборы, сливая prepend и append
        $this->aAssets = array();
        if ($this->aFiles) {
            foreach ($this->aFiles as $sAsset => $aFileStack) {
                if (isset($aFileStack['_prepend_']) && $aFileStack['_append_']) {
                    if ($aFileStack['_prepend_'] && $aFileStack['_append_']) {
                        $this->aAssets[$sAsset] = array_merge(
                            array_reverse($aFileStack['_prepend_']), $aFileStack['_append_']
                        );
                    } else {
                        if (!$aFileStack['_append_']) {
                            $this->aAssets[$sAsset] = array_reverse($aFileStack['_prepend_']);
                        } else {
                            $this->aAssets[$sAsset] = $aFileStack['_append_'];
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
                    $this->AddLink($aFileParams['info']['extension'], $aFileParams['file'], $aFileParams['browser']);
                } else {
                    $this->MakeSingle($sAsset, $aFileParams);
                }
            } else {
                // В наборе несколько файлов
                $this->MakeMerge($sAsset, $aFiles);
            }
        }
    }

    public function Process() {

    }

    public function PostProcess() {

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
        $aFileParams['name'] = F::File_NormPath($aFileParams['name']);
        return $aFileParams;
    }

    protected function _add($sFileName, $aFileParams, $sAssetName = null, $bAppend = true, $bReplace = false) {

        $aFileParams = $this->_prepareParams($sFileName, $aFileParams, $sAssetName);
        $sName = $aFileParams['name'];
        $sAssetName = $aFileParams['asset'];
        if (!isset($this->aFiles[$sAssetName])) {
            $this->aFiles[$sAssetName] = array('_append_' => array(), '_prepend_' => array());
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
        $this->aFiles[$sAssetName][$bAppend ? '_append_' : '_prepend_'][$sName] = $aFileParams;
        return 1;
    }

    public function Append($sFile, $aFileParams) {

    }

    public function AddFiles($aFiles, $sAssetName = null, $bAppend = true, $bReplace = false) {

        foreach ($aFiles as $sName => $aFileParams) {
            $this->_add($sName, $aFileParams, $sAssetName, $bAppend, $bReplace);
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

    public function Prepare() {

        $this->PreProcess();
        $this->Process();
        $this->PostProcess();
    }

    public function GetLinks() {

        return $this->aLinks;
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
        if ($aLink['browser']) {
            return "<!--[if {$aLink['browser']}]>$sResult<![endif]-->";
        }
        return $sResult;
    }

    public function BuildHtmlLinks() {

        $aResult = array();
        foreach ($this->aLinks as $aLinkData) {
            $aResult[$this->sOutType][] = $this->BuildLink($aLinkData);
        }
        return $aResult;
    }
}

// EOF