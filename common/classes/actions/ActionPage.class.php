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
     * Отображение страницы
     *
     * @return mixed
     */
    protected function EventShowPage() {

        if (!$this->sCurrentEvent) {
            // * Показывает дефолтную страницу (а это какая страница?)
        }

        // * Составляем полный URL страницы для поиска по нему в БД
        $sUrlFull = join('/', $this->GetParams());
        if ($sUrlFull != '') {
            $sUrlFull = $this->sCurrentEvent . '/' . $sUrlFull;
        } else {
            $sUrlFull = $this->sCurrentEvent;
        }

        // * Ищем страницу в БД
        if (!($oPage = $this->Page_GetPageByUrlFull($sUrlFull, 1))) {
            return $this->EventNotFound();
        }

        // * Заполняем HTML теги и SEO
        $this->Viewer_AddHtmlTitle($oPage->getTitle());
        if ($oPage->getSeoKeywords()) {
            $this->Viewer_SetHtmlKeywords($oPage->getSeoKeywords());
        }
        if ($oPage->getSeoDescription()) {
            $this->Viewer_SetHtmlDescription($oPage->getSeoDescription());
        }

        $this->Viewer_Assign('oPage', $oPage);

        // * Устанавливаем шаблон для вывода
        $this->SetTemplateAction('show');
    }


}

// EOF