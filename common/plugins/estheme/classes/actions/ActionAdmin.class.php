<?php

/**
 * ActionAdmin.class.php
 * Файл экшена плагина Estheme
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2015, Андрей Воронов
 *              Является частью плагина Estheme
 * @version     0.0.1 от 27.02.2015 08:56
 */
class PluginEstheme_ActionAdmin extends PluginEstheme_Inherit_ActionAdmin {

    /**
     * Абстрактный метод регистрации евентов.
     * В нём необходимо вызывать метод AddEvent($sEventName,$sEventFunction)
     * Например:
     *      $this->AddEvent('index', 'EventIndex');
     *      $this->AddEventPreg('/^admin$/i', '/^\d+$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventAdminBlog');
     */
    protected function RegisterEvent() {
        parent::RegisterEvent();
        $this->AddEvent('tools-estheme', 'EventEsTheme');
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
            // Сохраняемые данные
            $aData = array();
            // Компилируемые данные
            $aCompiledData = array();

            foreach ($aFields as $sFieldConfig => $aFieldDefault) {
                $sFieldName = str_replace('.', '_', $sFieldConfig);
                $aData["plugin.estheme.{$sFieldConfig}"] = getRequest($sFieldName, $aFieldDefault[0]);
                $_REQUEST[$sFieldName] = $aData["plugin.estheme.{$sFieldConfig}"];
                if (isset($aFieldDefault[2])) {
                    // Если нужно, то используем форматированиеперед вставкой стилевого значения less
                    $aCompiledData[$aFieldDefault[1]] = str_replace('{{value}}', $aData["plugin.estheme.{$sFieldConfig}"], $aFieldDefault[2]);
                } else {
                    $aCompiledData[$aFieldDefault[1]] = $aData["plugin.estheme.{$sFieldConfig}"];
                }
            }

            Config::WriteCustomConfig($aData);

            return $aCompiledData;

        } else {
            foreach ($aFields as $sFieldConfig => $aFieldDefault) {
                $sFieldName = str_replace('.', '_', $sFieldConfig);
                $_REQUEST[$sFieldName] = C::Get("plugin.estheme.{$sFieldConfig}");
            }
            return array();
        }

    }

    public function EventEsTheme() {

        $this->sMainMenuItem = 'tools';

        E::ModuleViewer()->Assign('sPageTitle', E::ModuleLang()->Get('plugin.estheme.admin_title'));
        E::ModuleViewer()->Assign('sMainMenuItem', 'tools');
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('plugin.estheme.admin_title'));

        $this->SetTemplateAction('tools/estheme');

        $aProcessData = $this->PluginEstheme_Estheme_GetProcessData();

        if (getRequest('submit_estheme')) {

            $aCompiledData = $this->_processConfig($aProcessData, TRUE);

            $this->PluginEstheme_Estheme_CompileTheme($aCompiledData);

            return FALSE;

        }

        $this->_processConfig($aProcessData, FALSE);

        return FALSE;

    }

}