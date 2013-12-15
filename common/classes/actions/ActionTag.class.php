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
 * Экшен обработки поиска по тегам
 *
 * @package actions
 * @since   1.0
 */
class ActionTag extends Action {
    /**
     * Главное меню
     *
     * @var string
     */
    protected $sMenuHeadItemSelect = 'blog';

    /**
     * Инициализация
     *
     */
    public function Init() {
    }

    /**
     * Регистрация евентов
     */
    protected function RegisterEvent() {

        $this->AddEventPreg('/^.+$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventTags');
    }


    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */

    /**
     * Отображение топиков
     *
     */
    protected function EventTags() {

        // * Gets tag from URL
        $sTag = F::UrlDecode($this->sCurrentEvent);

        // * Check page number
        $iPage = $this->GetParamEventMatch(0, 2) ? $this->GetParamEventMatch(0, 2) : 1;

        // * Gets topics list
        $aResult = $this->Topic_GetTopicsByTag($sTag, $iPage, Config::Get('module.topic.per_page'));
        $aTopics = $aResult['collection'];

        // * Calls hooks
        $this->Hook_Run('topics_list_show', array('aTopics' => $aTopics));

        // * Makes pages
        $aPaging = $this->Viewer_MakePaging(
            $aResult['count'], $iPage, Config::Get('module.topic.per_page'), Config::Get('pagination.pages.count'),
            Router::GetPath('tag') . htmlspecialchars($sTag)
        );

        // * Loads variables to template
        $this->Viewer_Assign('aPaging', $aPaging);
        $this->Viewer_Assign('aTopics', $aTopics);
        $this->Viewer_Assign('sTag', $sTag);
        $this->Viewer_AddHtmlTitle($this->Lang_Get('tag_title'));
        $this->Viewer_AddHtmlTitle($sTag);
        $this->Viewer_SetHtmlRssAlternate(Router::GetPath('rss') . 'tag/' . $sTag . '/', $sTag);

        // * Sets template for display
        $this->SetTemplateAction('index');
    }

    /**
     * Выполняется при завершении работы экшена
     *
     */
    public function EventShutdown() {
        /**
         * Загружаем в шаблон необходимые переменные
         */
        $this->Viewer_Assign('sMenuHeadItemSelect', $this->sMenuHeadItemSelect);
    }
}

// EOF