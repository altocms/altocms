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
        return $this->PrepareContents($sContents, $sDestination);
    }

}

// EOF