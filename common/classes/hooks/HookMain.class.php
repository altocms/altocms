<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

/**
 * Регистрация основных хуков
 *
 * @package hooks
 * @since   1.0
 */
class HookMain extends Hook {

    protected $aScripts;

    /**
     * Регистрируем хуки
     */
    public function RegisterHook() {

        $this->AddHook('module_session_init_after', 'SessionInitAfter', __CLASS__, PHP_INT_MAX);
        $this->AddHook('init_action', 'InitAction', __CLASS__, PHP_INT_MAX);
        $this->AddHook('render_init_done', 'RenderInitDone', __CLASS__, PHP_INT_MAX);

        $this->AddHook('template_form_add_content', 'insertFields', __CLASS__, -1);

        // * Показывавем поля при просмотре топика
        $this->AddHook('template_topic_content_end', 'showFields', __CLASS__, 150);
        $this->AddHook('template_topic_preview_content_end', 'showFields', __CLASS__, 150);

        // * Упрощенный вывод JS в футере, для проблемных файлов
        $this->AddHook('template_body_end', 'BuildFooterJsCss', __CLASS__, -150);

        $this->AddHook('template_html_head_tags', 'InsertHtmlHeadTags', __CLASS__);

        $this->AddHookTemplate('layout_head_end', 'tplLayoutHeadEnd');
        $this->AddHookTemplate('layout_body_begin', 'tplLayoutBodyBegin');
        $this->AddHookTemplate('layout_body_end', 'tplLayoutBodyEnd');
    }

    public function SessionInitAfter() {

        if (!Config::Get('_db_')) {
            Config::ReReadCustomConfig();
        }
    }

    /**
     * Обработка хука инициализации экшенов
     */
    public function InitAction() {

        // * Проверяем наличие директории install
        if (is_dir(rtrim(Config::Get('path.root.dir'), '/') . '/install')
            && (!isset($_SERVER['HTTP_APP_ENV']) || $_SERVER['HTTP_APP_ENV'] != 'test')
        ) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('install_directory_exists'));
            R::Action('error');
        }

        // * Проверка на закрытый режим
        $oUserCurrent = E::ModuleUser()->GetUserCurrent();
        if (!$oUserCurrent && Config::Get('general.close.mode')){
            $aEnabledActions = F::Str2Array(Config::Get('general.close.actions'));
            if (!in_array(R::GetAction(), $aEnabledActions)) {
                return R::Action('login');
            }
        }
        return null;
    }

    public function RenderInitDone() {

        E::ModuleMenu()->PrepareMenus();
    }

    public function insertFields() {

        return E::ModuleViewer()->Fetch('inject.topic.fields.tpl');
    }

    public function showFields($aVars) {

        $sReturn = '';
        if (isset($aVars['topic']) && isset($aVars['bTopicList'])) {
            $oTopic = $aVars['topic'];
            $bTopicList = $aVars['bTopicList'];
            if (!$bTopicList) {
                //получаем данные о типе топика
                if ($oType = $oTopic->getContentType()) {
                    //получаем поля для данного типа
                    if ($aFields = $oType->getFields()) {
                        //вставляем поля, если они прописаны для топика
                        foreach ($aFields as $oField) {
                            if ($oTopic->getField($oField->getFieldId()) || $oField->getFieldType() == 'photoset') {
                                E::ModuleViewer()->Assign('oField', $oField);
                                E::ModuleViewer()->Assign('oTopic', $oTopic);
                                if (E::ModuleViewer()->TemplateExists('forms/view_field_' . $oField->getFieldType() . '.tpl')) {
                                    $sReturn .= E::ModuleViewer()->Fetch('forms/view_field_' . $oField->getFieldType() . '.tpl');
                                }
                            }
                        }
                    }
                }

            }
        }
        return $sReturn;
    }

    public function BuildFooterJsCss() {

        $sCssFooter = '';
        $sJsFooter = '';

        foreach (array('js', 'css') as $sType) {
            // * Проверяем наличие списка файлов данного типа
            $aFiles = Config::Get('assets.footer.' . $sType);
            if (is_array($aFiles) && count($aFiles)) {
                foreach ($aFiles as $sFile) {
                    if ($sType == 'js') {
                        $sJsFooter .= "<script type='text/javascript' src='" . $sFile . "'></script>";
                    } elseif ($sType == 'css') {
                        $sCssFooter .= "<link rel='stylesheet' type='text/css' href='" . $sFile . "' />";
                    }
                }
            }
        }

        return $sCssFooter . $sJsFooter;

    }

    public function InsertHtmlHeadTags() {

        $aTags = E::ModuleViewer()->GetHtmlHeadTags();
        $sResult = '';
        foreach($aTags as $sTag) {
            $sResult .= $sTag . "\n";
        }
        return $sResult;
    }

    protected function _scriptCode($aScripts, $sPlace) {

        $sResult = '';
        foreach($aScripts as $aScript) {
            if (!empty($aScript['place']) && $aScript['place'] == $sPlace && empty($aScript['disable']) && !empty($aScript['code'])) {
                $aIncludePaths = (!empty($aScript['on']) ? $aScript['on'] : array());
                $aExcludePaths = (!empty($aScript['off']) ? $aScript['off'] : array());
                if ((!$aIncludePaths && !$aExcludePaths) || R::AllowLocalPath($aIncludePaths, $aExcludePaths)) {
                    $sResult .= PHP_EOL . $aScript['code'] . PHP_EOL;
                }
            }
        }
        if ($sResult) {
            $sResult = '{literal}' . $sResult . '{/literal}';
        }
        return $sResult;
    }

    public function tplLayoutHeadEnd() {

        $sResult = '';
        $this->aScripts = C::Get('script');
        if ($this->aScripts) {
            $sResult = $this->_scriptCode($this->aScripts, 'head');
        }
        return $sResult;
    }

    public function tplLayoutBodyBegin() {

        $sResult = '';
        if ($this->aScripts) {
            $sResult = $this->_scriptCode($this->aScripts, 'body');
        }
        return $sResult;
    }

    public function tplLayoutBodyEnd() {

        $sResult = $this->BuildFooterJsCss();
        if ($this->aScripts) {
            $sResult .= PHP_EOL . $this->_scriptCode($this->aScripts, 'end');
        }
        return $sResult;
    }

}

// EOF