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
    /**
     * Регистрируем хуки
     */
    public function RegisterHook() {

        $this->AddHook('module_Session_init_after', 'SessionInitAfter', __CLASS__, PHP_INT_MAX);
        $this->AddHook('init_action', 'InitAction', __CLASS__, PHP_INT_MAX);

        $this->AddHook('template_form_add_content', 'insertFields', __CLASS__, -1);

        /*
         * Показывавем поля при просмотре топика
         */
        $this->AddHook('template_topic_content_end', 'showFields', __CLASS__, 150);
        $this->AddHook('template_topic_preview_content_end', 'showFields', __CLASS__, 150);

        /*
         * Упрощенный вывод JS в футере, для проблемных файлов
         */
        $this->AddHook('template_body_end', 'buildfooterJsCss', __CLASS__, -150);
        $this->AddHook('template_layout_body_end', 'buildfooterJsCss', __CLASS__, -150);

        /*
         * Улучшенный share при просмотре топика
         */
        $this->AddHook('template_block_topic_share', 'addSharer');
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
        /**
         * Проверяем наличие директории install
         */
        if (is_dir(rtrim(Config::Get('path.root.dir'), '/') . '/install')
            && (!isset($_SERVER['HTTP_APP_ENV']) || $_SERVER['HTTP_APP_ENV'] != 'test')
        ) {
            $this->Message_AddErrorSingle($this->Lang_Get('install_directory_exists'));
            Router::Action('error');
        }
        /**
         * Проверка на закрытый режим
         */
        $oUserCurrent = $this->User_GetUserCurrent();
        if (!$oUserCurrent && Config::Get('general.close.mode')){
            $aEnabledActions = F::Str2Array(Config::Get('general.close.actions'));
            if (!in_array(Router::GetAction(), $aEnabledActions)) {
                return Router::Action('login');
            }
        }
    }

    public function insertFields() {

        return $this->Viewer_Fetch('inject.topic.fields.tpl');
    }

    public function showFields($aVars) {

        $oTopic = $aVars['topic'];
        $bTopicList = $aVars['bTopicList'];
        $sReturn = '';
        if (!$bTopicList) {
            //получаем данные о типе топика
            if ($oType = $oTopic->getContentType()) {
                //получаем поля для данного типа
                if ($aFields = $oType->getFields()) {
                    //вставляем поля, если они прописаны для топика
                    foreach ($aFields as $oField) {
                        if ($oTopic->getField($oField->getFieldId()) || $oField->getFieldType() == 'photoset') {
                            $this->Viewer_Assign('oField', $oField);
                            $this->Viewer_Assign('oTopic', $oTopic);
                            if ($this->Viewer_TemplateExists('forms/view_field_' . $oField->getFieldType() . '.tpl')) {
                                $sReturn .= $this->Viewer_Fetch('forms/view_field_' . $oField->getFieldType() . '.tpl');
                            }
                        }
                    }
                }
            }

        }
        return $sReturn;
    }

    public function buildfooterJsCss() {

        $sCssFooter = '';
        $sJsFooter = '';

        foreach (array('js', 'css') as $sType) {
            /**
             * Проверяем наличие списка файлов данного типа
             */
            $aFiles = Config::Get('footer.default.' . $sType);
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

    public function addSharer($aParams) {

        $oTopic = $aParams['topic'];
        $bList = $aParams['bTopicList'];

        if (!$bList) {
            //заменяем скрипт шарера на продвинутый со счетчиками
            $aFooterJs = Config::Get('footer.default.js');
            if (is_array($aFooterJs)) {
                if (($key = array_search('http://yandex.st/share/share.js', $aFooterJs)) !== false) {
                    unset($aFooterJs[$key]);
                }
            } else {
                $aFooterJs = array();
            }

            $aFooterJs[] = '//yandex.st/share/cnt.share.js';
            Config::Set('footer.default.js', $aFooterJs);
        }

        $oViewer = $this->Viewer_GetLocalViewer();
        $oViewer->Assign('oTopic', $oTopic);
        $oViewer->Assign('bTopicList', $bList);
        return $oViewer->Fetch('commons/common.sharer.tpl');
    }

}

// EOF