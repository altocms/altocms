<?php

/**
 * ActionEstheme.class.php
 * Файл экшена плагина Estheme
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2015, Андрей Воронов
 *              Является частью плагина Estheme
 * @version     0.0.1 от 27.02.2015 08:56
 */
class PluginEstheme_ActionEstheme extends ActionPlugin {

    /**
     * Абстрактный метод инициализации экшена
     *
     */
    public function Init() {
        $this->SetDefaultEvent('index');
    }

    /**
     * Абстрактный метод регистрации евентов.
     * В нём необходимо вызывать метод AddEvent($sEventName,$sEventFunction)
     * Например:
     *      $this->AddEvent('index', 'EventIndex');
     *      $this->AddEventPreg('/^admin$/i', '/^\d+$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventAdminBlog');
     */
    protected function RegisterEvent() {
        $this->AddEvent('index', 'EventEsTheme');
    }

    public function EventEsTheme() {

        if (!E::User() || !C::Get('plugin.estheme.use_client')) {
            return R::Action('error');
        }

        $aProcessData = $this->PluginEstheme_Estheme_GetProcessData();

        if (getRequest('submit_estheme')) {
            $sCSSDownloadPath = F::File_Dir2Url(C::Get('plugin.estheme.path_for_download') . E::UserId() . '/theme.custom.css');

            $aCompiledData = $this->_processConfig($aProcessData, TRUE);
            $this->PluginEstheme_Estheme_CompileTheme($aCompiledData, TRUE);

        } else {
            $sCSSDownloadPath = FALSE;
            $this->_processConfig($aProcessData, FALSE);
        }

        E::ModuleViewer()->Assign('sCSSDownloadPath', $sCSSDownloadPath);

    }

    /**
     * Обрабатывает поля конфига и возвращает данные для компиляции
     *
     * @param $aFields
     * @param $bSave
     * @return array
     */
    private function _processConfig($aFields, $bSave) {

        if ($bSave) {
            // Компилируемые данные
            $aCompiledData = array();

            foreach ($aFields as $sFieldConfig => $aFieldDefault) {
                $sFieldName = str_replace('.', '_', $sFieldConfig);
                $_REQUEST[$sFieldName] = getRequest($sFieldName, $aFieldDefault[0]);
                if (isset($aFieldDefault[2])) {
                    // Если нужно, то используем форматированиеперед вставкой стилевого значения less
                    $aCompiledData[$aFieldDefault[1]] = str_replace('{{value}}', $_REQUEST[$sFieldName], $aFieldDefault[2]);
                } else {
                    $aCompiledData[$aFieldDefault[1]] = $_REQUEST[$sFieldName];
                }
            }

            return $aCompiledData;

        } else {
            foreach ($aFields as $sFieldConfig => $aFieldDefault) {
                $sFieldName = str_replace('.', '_', $sFieldConfig);
                $_REQUEST[$sFieldName] = C::Get("plugin.estheme.{$sFieldConfig}");
            }

            return array();
        }

    }

}