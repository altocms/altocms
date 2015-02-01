<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
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
     * @var ModuleTopic_EntityContentType|null
     */
    protected $oType = null;

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();


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
        if (!$this->oType = E::ModuleTopic()->GetContentType($this->sCurrentEvent)) {
            return parent::EventNotFound();
        }

        /**
         * Устанавливаем title страницы
         */
        E::ModuleViewer()->AddHtmlTitle($this->oType->getContentTitleDecl());
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(0, 2) ? $this->GetParamEventMatch(0, 2) : 1;
        /**
         * Получаем список топиков
         */
        $aResult = E::ModuleTopic()->GetTopicsByType(
            $iPage, Config::Get('module.topic.per_page'), $this->oType->getContentUrl()
        );
        $aTopics = $aResult['collection'];
        /**
         * Формируем постраничность
         */
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.topic.per_page'), Config::Get('pagination.pages.count'),
            R::GetPath('filter') . $this->sCurrentEvent
        );
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aTopics', $aTopics);
        $this->SetTemplateAction('index');
    }

    /**
     * При завершении экшена загружаем необходимые переменные
     *
     */
    public function EventShutdown() {
        E::ModuleViewer()->Assign('sMenuHeadItemSelect', $this->sMenuHeadItemSelect);
        E::ModuleViewer()->Assign('sMenuItemSelect', $this->sMenuItemSelect);
        E::ModuleViewer()->Assign('sMenuSubItemSelect', $this->sMenuSubItemSelect);
    }
}

// EOF