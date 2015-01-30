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
 * Экшен обработки УРЛа вида /comments/
 *
 * @package actions
 * @since   1.0
 */
class ActionComments extends Action {
    /**
     * Текущий юзер
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent = null;
    /**
     * Главное меню
     *
     * @var string
     */
    protected $sMenuHeadItemSelect = 'blog';

    /**
     * Инициализация
     */
    public function Init() {

        $this->oUserCurrent = $this->User_GetUserCurrent();
    }

    /**
     * Регистрация евентов
     */
    protected function RegisterEvent() {

        $this->AddEventPreg('/^(page([1-9]\d{0,5}))?$/i', 'EventComments');
        $this->AddEventPreg('/^\d+$/i', 'EventShowComment');
    }


    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */

    /**
     * Выводим список комментариев
     *
     */
    protected function EventComments() {
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetEventMatch(2) ? $this->GetEventMatch(2) : 1;
        /**
         * Исключаем из выборки идентификаторы закрытых блогов (target_parent_id)
         */
        $aCloseBlogs = ($this->oUserCurrent)
            ? $this->Blog_GetInaccessibleBlogsByUser($this->oUserCurrent)
            : $this->Blog_GetInaccessibleBlogsByUser();
        /**
         * Получаем список комментов
         */
        $aResult = $this->Comment_GetCommentsAll(
            'topic', $iPage, Config::Get('module.comment.per_page'), array(), $aCloseBlogs
        );
        $aComments = $aResult['collection'];
        /**
         * Формируем постраничность
         */
        $aPaging = $this->Viewer_MakePaging(
            $aResult['count'], $iPage, Config::Get('module.comment.per_page'), Config::Get('pagination.pages.count'),
            R::GetPath('comments')
        );
        /**
         * Загружаем переменные в шаблон
         */
        $this->Viewer_Assign('aPaging', $aPaging);
        $this->Viewer_Assign("aComments", $aComments);
        /**
         * Устанавливаем title страницы
         */
        $this->Viewer_AddHtmlTitle($this->Lang_Get('comments_all'));
        $this->Viewer_SetHtmlRssAlternate(R::GetPath('rss') . 'allcomments/', $this->Lang_Get('comments_all'));
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('index');
    }

    /**
     * Обрабатывает ссылку на конкретный комментарий, определят к какому топику он относится и перенаправляет на него
     * Актуально при использовании постраничности комментариев
     */
    protected function EventShowComment() {

        $iCommentId = $this->sCurrentEvent;
        /**
         * Проверяем к чему относится комментарий
         */
        if (!($oComment = $this->Comment_GetCommentById($iCommentId))) {
            return parent::EventNotFound();
        }
        if ($oComment->getTargetType() != 'topic' || !($oTopic = $oComment->getTarget())) {
            return parent::EventNotFound();
        }
        /**
         * Определяем необходимую страницу для отображения комментария
         */
        if (!Config::Get('module.comment.use_nested') || !Config::Get('module.comment.nested_per_page')) {
            R::Location($oTopic->getUrl() . '#comment' . $oComment->getId());
        }
        $iPage = $this->Comment_GetPageCommentByTargetId(
            $oComment->getTargetId(), $oComment->getTargetType(), $oComment
        );
        if ($iPage == 1) {
            R::Location($oTopic->getUrl() . '#comment' . $oComment->getId());
        } else {
            R::Location($oTopic->getUrl() . "?cmtpage={$iPage}#comment" . $oComment->getId());
        }
        exit();
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