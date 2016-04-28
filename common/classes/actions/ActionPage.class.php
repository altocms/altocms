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
 * @package actions
 * @since   0.9
 */
class ActionPage extends Action {

    protected $oCurrentPage;

    public function Init() {
    }

    /**
     * Регистрируем евенты
     *
     */
    protected function RegisterEvent() {

        $this->AddEventPreg('/^[\w\-\_]*$/i', 'EventShowPage');
    }


    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */

    /**
     * Returns page by requested URL
     *
     * @return ModulePage_EntityPage
     */
    protected function _getPageFromUrl() {

        // * Составляем полный URL страницы для поиска по нему в БД
        $sUrlFull = join('/', $this->GetParams());
        if ($sUrlFull != '') {
            $sUrlFull = $this->sCurrentEvent . '/' . $sUrlFull;
        } else {
            $sUrlFull = $this->sCurrentEvent;
        }

        // * Ищем страницу в БД
        $oPage = E::ModulePage()->GetPageByUrlFull($sUrlFull, 1);

        return $oPage;
    }

    /**
     * Отображение страницы
     *
     * @return mixed
     */
    protected function EventShowPage() {

        if (!$this->sCurrentEvent) {
            // * Показывает дефолтную страницу (а это какая страница?)
        }

        $this->oCurrentPage = $this->_getPageFromUrl();

        if (!$this->oCurrentPage) {
            return $this->EventNotFound();
        }

        // * Заполняем HTML теги и SEO
        E::ModuleViewer()->AddHtmlTitle($this->oCurrentPage->getTitle());
        if ($this->oCurrentPage->getSeoKeywords()) {
            E::ModuleViewer()->SetHtmlKeywords($this->oCurrentPage->getSeoKeywords());
        }
        if ($this->oCurrentPage->getSeoDescription()) {
            E::ModuleViewer()->SetHtmlDescription($this->oCurrentPage->getSeoDescription());
        }

        E::ModuleViewer()->assign('oPage', $this->oCurrentPage);

        // * Устанавливаем шаблон для вывода
        $this->SetTemplateAction('show');
    }


}

// EOF