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
class ModuleViewerAsset_EntityPackageCss extends ModuleViewerAsset_EntityPackage {

    protected $sOutType = 'css';
    protected $oCssCompressor;

    public function Init() {

        $this->aHtmlLinkParams = array(
            'tag'  => 'link',
            'attr' => array(
                'type' => 'text/css',
                'rel'  => 'stylesheet',
                'href' => '@link',
            ),
            'pair' => false,
        );
    }

    /**
     * Создает css-компрессор и инициализирует его конфигурацию
     *
     * @return bool
     */
    protected function InitCssCompressor() {

        if (Config::Get('compress.css.use')) {
            // * Получаем параметры из конфигурации
            $this->oCssCompressor = new csstidy();

            if ($this->oCssCompressor) {
                $aParams = Config::Get('compress.css.csstidy');
                // * Устанавливаем параметры
                foreach ($aParams as $sKey => $sVal) {
                    if ($sKey == 'template') {
                        $this->oCssCompressor->load_template($sVal);
                    } else {
                        $this->oCssCompressor->set_cfg('case_properties', $sVal);
                    }
                }
                return true;
            }
        }
        return false;
    }

    public function PreProcess() {

        if ($this->aFiles) {
            $this->InitCssCompressor();
        }
        parent::PreProcess();
    }

    public function Process() {

        foreach ($this->aLinks as $nIdx => $aLinkData) {
            if (isset($aLinkData['compress']) && $aLinkData['compress']) {
                $sFile = $aLinkData['file'];
                $sExtension = 'min.' . F::File_GetExtension($sFile);
                $sCompressedFile = F::File_SetExtension($sFile, $sExtension);
                if (!$this->CheckDestination($sCompressedFile)) {
                    if (($sContents = F::File_GetContents($sFile))) {
                        $this->oCssCompressor->parse($sContents);
                        $sContents = $this->oCssCompressor->print->plain();
                        if (F::File_PutContents($sCompressedFile, $sContents)) {
                            F::File_Delete($sFile);
                            $this->aLinks[$nIdx]['link'] = F::File_SetExtension($this->aLinks[$nIdx]['link'], $sExtension);
                        }
                    }
                } else {
                    $this->aLinks[$nIdx]['link'] = F::File_SetExtension($this->aLinks[$nIdx]['link'], $sExtension);
                }
            }
        }
    }

    public function PrepareFile($sFile, $sDestination) {

        $sContents = F::File_GetContents($sFile);
        $sContents = $this->PrepareContents($sContents, $sFile, $sDestination);
        if (F::File_Put_Contents($sDestination, $sContents)) {
            return $sDestination;
        }
    }

    public function PrepareContents($sContents, $sSource) {

        if ($sContents) {
            $sContents = $this->_convertUrlsInCss($sContents, dirname($sSource) . '/');
        }
        return $sContents;
    }

    protected function _convertUrlsInCss($sContent, $sSourceDir) {

        // Есть ли в файле URLs
        if (!preg_match_all('|url\((.*?)\)|is', $sContent, $aMatchedUrl, PREG_OFFSET_CAPTURE)) {
            return $sContent;
        }

        // * Обрабатываем список URLs
        $aUrls = array();
        foreach ($aMatchedUrl[1] as $aPart) {
            $sPath = $aPart[0];
            //$nPos = $aPart[1];

            // * Don't touch data URIs
            if (strstr($sPath, 'data:')) {
                continue;
            }
            $sPath = str_replace(array('\'', '"'), '', $sPath);

            // * Если путь является абсолютным, то не обрабатываем
            if (substr($sPath, 0, 1) == "/" || substr($sPath, 0, 5) == 'http:' || substr($sPath, 0, 6) == 'https:') {
                continue;
            }

            $sRealPath = realpath($sSourceDir . $sPath);
            $sDestination = $this->Viewer_GetAssetDir() . $this->_crc(dirname($sRealPath)) . '/' . basename($sRealPath);
            $aUrls[$sPath] = array(
                'source'      => $sRealPath,
                'destination' => $sDestination,
                'url'         => F::File_Dir2Url($sDestination),
            );
            F::File_Copy($sRealPath, $sDestination);
        }
        if ($aUrls) {
            $sContent = str_replace(array_keys($aUrls), F::Array_Column($aUrls, 'url'), $sContent);
        }

        return $sContent;
    }

}

// EOF