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

    public function Init() {

        if (!$this->sOutType) {
            $this->sOutType = 'css';
        }
        if (!$this->sAssetType) {
            $this->sAssetType = 'css';
        }
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

        // * Получаем параметры из конфигурации
        $aParams = Config::Get('compress.css');
        $this->oCssCompressor = ($aParams['use']) ? new csstidy() : null;

        // * Если компрессор не создан, завершаем работу инициализатора
        if (!$this->oCssCompressor) {
            return false;
        }

        // * Устанавливаем параметры
        $this->oCssCompressor->set_cfg('case_properties', $aParams['case_properties']);
        $this->oCssCompressor->set_cfg('merge_selectors', $aParams['merge_selectors']);
        $this->oCssCompressor->set_cfg('optimise_shorthands', $aParams['optimise_shorthands']);
        $this->oCssCompressor->set_cfg('remove_last_;', $aParams['remove_last_;']);
        $this->oCssCompressor->set_cfg('css_level', $aParams['css_level']);
        $this->oCssCompressor->load_template($aParams['template']);

        return true;
    }

    public function PreProcess() {

        if ($this->aFiles) {
            $this->InitCssCompressor();
        }
        parent::PreProcess();
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
                'source' => $sRealPath,
                'destination' => $sDestination,
                'url' => F::File_Dir2Url($sDestination),
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