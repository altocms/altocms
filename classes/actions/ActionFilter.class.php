<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class ActionFilter extends Action {
    /**
     * Главное меню
     *
     * @var string
     */
    protected $sMenuHeadItemSelect = 'filter';
    /**
     * Меню
     *
     * @var string
     */
    protected $sMenuItemSelect = 'topic';
    /**
     * СубМеню
     *
     * @var string
     */
    protected $sMenuSubItemSelect = '';
    /**
     * Текущий юзер
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent = null;
    /**
     * Текущий тип контента
     *
     * @var ModuleTopic_EntityContent|null
     */
    protected $oType = null;

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->oUserCurrent = $this->User_GetUserCurrent();


    }

    /**
     * Регистрируем евенты
     *
     */
    protected function RegisterEvent() {

        $this->AddEventPreg('/^[\w\-\_]+$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventShowTopics');

    }


    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */


    /**
     * Выводит список топиков
     *
     */
    protected function EventShowTopics() {
        /**
         * Меню
         */
        $this->sMenuSubItemSelect = $this->sCurrentEvent;

        /*
         * Получаем тип контента
         */
        if (!$this->oType = $this->Topic_getContentType($this->sCurrentEvent)) {
            return parent::EventNotFound();
        }

        /**
         * Устанавливаем title страницы
         */
        $this->Viewer_AddHtmlTitle($this->oType->getContentTitleDecl());
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(0, 2) ? $this->GetParamEventMatch(0, 2) : 1;
        /**
         * Получаем список топиков
         */
        $aResult = $this->Topic_GetTopicsByType(
            $iPage, Config::Get('module.topic.per_page'), $this->oType->getContentUrl()
        );
        $aTopics = $aResult['collection'];
        /**
         * Формируем постраничность
         */
        $aPaging = $this->Viewer_MakePaging(
            $aResult['count'], $iPage, Config::Get('module.topic.per_page'), Config::Get('pagination.pages.count'),
            Router::GetPath('filter') . $this->sCurrentEvent
        );
        /**
         * Загружаем переменные в шаблон
         */
        $this->Viewer_Assign('aPaging', $aPaging);
        $this->Viewer_Assign('aTopics', $aTopics);
        $this->SetTemplateAction('index');
    }

    /**
     * При завершении экшена загружаем необходимые переменные
     *
     */
    public function EventShutdown() {
        $this->Viewer_Assign('sMenuHeadItemSelect', $this->sMenuHeadItemSelect);
        $this->Viewer_Assign('sMenuItemSelect', $this->sMenuItemSelect);
        $this->Viewer_Assign('sMenuSubItemSelect', $this->sMenuSubItemSelect);
    }
}

// EOF