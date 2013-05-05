<?php
/*-------------------------------------------------------
*
*   LiveStreet Engine Social Networking
*   Copyright © 2008 Mzhelskiy Maxim
*
*--------------------------------------------------------
*
*   Official site: www.livestreet.ru
*   Contact e-mail: rus.engine@gmail.com
*
*   GNU General Public License, version 2:
*   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
---------------------------------------------------------
*/

/**
 * Регистрация основных хуков
 *
 * @package hooks
 * @since 1.0
 */
class HookMain extends Hook {
    /**
     * Регистрируем хуки
     */
    public function RegisterHook() {
        $this->AddHook('module_Session_init_after', 'SessionInitAfter', __CLASS__, PHP_INT_MAX);
        $this->AddHook('init_action', 'InitAction', __CLASS__, PHP_INT_MAX);

        $this->AddHook('template_form_add_content', 'insertfields', __CLASS__, -1);

        /*
         * Показывавем поля при просмотре топика
         */
        $this->AddHook('template_topic_content_end', 'showfields', __CLASS__, 150);
		$this->AddHook('template_topic_preview_content_end', 'showfields', __CLASS__, 150);

		/*
		 * Упрощенный вывод JS в футере, для проблемных файлов
		 */
		$this->AddHook('template_body_end', 'buildfooterJsCss', __CLASS__, -150);
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
        if (is_dir(rtrim(Config::Get('path.root.server'), '/') . '/install')
            && (!isset($_SERVER['HTTP_APP_ENV']) || $_SERVER['HTTP_APP_ENV'] != 'test')
        ) {
            $this->Message_AddErrorSingle($this->Lang_Get('install_directory_exists'));
            Router::Action('error');
        }
        /**
         * Проверка на закрытый режим
         */
        $oUserCurrent = $this->User_GetUserCurrent();
        if (!$oUserCurrent and Config::Get('general.close') and Router::GetAction() != 'registration' and Router::GetAction() != 'login') {
            Router::Action('login');
        }
    }

    public function insertfields() {
        return $this->Viewer_Fetch('inject.topic.fields.tpl');
    }

    public function showfields($aVars) {
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
                            $sReturn .= $this->Viewer_Fetch('forms/view_field_' . $oField->getFieldType() . '.tpl');

                        }
                    }
                }
            }

        }
        return $sReturn;
    }

    public function buildfooterJsCss(){

        $sCssFooter='';
        $sJsFooter ='';

        foreach (array('js', 'css') as $sType) {
			/**
             * Проверяем наличие списка файлов данного типа
             */
            $aFiles = Config::Get('footer.default.' . $sType);
            if (is_array($aFiles) && count($aFiles)) {
                foreach ($aFiles as $sFile) {
                    if ($sType == 'js') {
                        $sJsFooter.="<script type='text/javascript' src='".$sFile."'></script>";
                    } elseif ($sType == 'css') {
                        $sCssFooter.= "<link rel='stylesheet' type='text/css' href='".$sFile."' />";
                    }
                }
            }
        }

		return $sCssFooter.$sJsFooter;

	}
	
}

// EOF