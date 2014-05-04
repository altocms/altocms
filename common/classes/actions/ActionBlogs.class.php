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
 * @since   1.0
 */
class ActionBlogs extends Action {
    /**
     * Главное меню
     *
     * @var string
     */
    protected $sMenuHeadItemSelect = 'blogs';

    /**
     * Инициализация
     */
    public function Init() {

        // * Загружаем в шаблон JS текстовки
        $this->Lang_AddLangJs(
            array(
                 'blog_join', 'blog_leave'
            )
        );
    }

    /**
     * Регистрируем евенты
     */
    protected function RegisterEvent() {

        $this->AddEventPreg('/^personal$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventShowBlogsPersonal');
        $this->AddEventPreg('/^(page([1-9]\d{0,5}))?$/i', 'EventShowBlogs');
        $this->AddEventPreg('/^ajax-search$/i', 'EventAjaxSearch');
    }


    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */

    /**
     * Поиск блогов по названию
     */
    protected function EventAjaxSearch() {

        // * Устанавливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax('json');

        // * Получаем из реквеста первые буквы блога
        if ($sTitle = F::GetRequestStr('blog_title')) {
            $sTitle = str_replace('%', '', $sTitle);
        }
        if (!$sTitle) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'));
            return;
        }

        // * Ищем блоги
        if (F::GetRequestStr('blog_type') == 'personal') {
            $aFilter = array('include_type' => 'personal', 'title' => "%{$sTitle}%");
        } else {
            $aFilter = array('exclude_type' => 'personal', 'title' => "%{$sTitle}%");
        }
        $aResult = $this->Blog_GetBlogsByFilter($aFilter, array('blog_title' => 'asc'), 1, 100);

        // * Формируем и возвращает ответ
        $oViewer = $this->Viewer_GetLocalViewer();
        $oViewer->Assign('aBlogs', $aResult['collection']);
        $oViewer->Assign('oUserCurrent', $this->User_GetUserCurrent());
        $oViewer->Assign('sBlogsEmptyList', $this->Lang_Get('blogs_search_empty'));
        $this->Viewer_AssignAjax('sText', $oViewer->Fetch('commons/common.blog_list.tpl'));
    }

    /**
     * Отображение списка блогов
     */
    protected function EventShowBlogs() {

        // * По какому полю сортировать
        $sOrder = F::GetRequestStr('order', 'blog_rating');

        // * В каком направлении сортировать
        $sOrderWay = F::GetRequestStr('order_way', 'desc');

        // * Фильтр поиска блогов
        $aFilter = array(
            'include_type' => $this->Blog_GetAllowBlogTypes($this->User_GetUserCurrent(), 'list', true),
        );

        // * Передан ли номер страницы
        $iPage = preg_match("/^\d+$/i", $this->GetEventMatch(2)) ? $this->GetEventMatch(2) : 1;

        // * Получаем список блогов
        $aResult = $this->Blog_GetBlogsByFilter(
            $aFilter, array($sOrder => $sOrderWay), $iPage, Config::Get('module.blog.per_page')
        );
        $aBlogs = $aResult['collection'];

        // * Формируем постраничность
        $aPaging = $this->Viewer_MakePaging(
            $aResult['count'], $iPage, Config::Get('module.blog.per_page'), Config::Get('pagination.pages.count'),
            Router::GetPath('blogs'), array('order' => $sOrder, 'order_way' => $sOrderWay)
        );

        //  * Загружаем переменные в шаблон
        $this->Viewer_Assign('aPaging', $aPaging);
        $this->Viewer_Assign('aBlogs', $aBlogs);
        $this->Viewer_Assign('sBlogOrder', htmlspecialchars($sOrder));
        $this->Viewer_Assign('sBlogOrderWay', htmlspecialchars($sOrderWay));
        $this->Viewer_Assign('sBlogOrderWayNext', htmlspecialchars($sOrderWay == 'desc' ? 'asc' : 'desc'));
        $this->Viewer_Assign('sShow', 'collective');
        $this->Viewer_Assign('sBlogsRootPage', Router::GetPath('blogs'));

        // * Устанавливаем title страницы
        $this->Viewer_AddHtmlTitle($this->Lang_Get('blog_menu_all_list'));

        // * Устанавливаем шаблон вывода
        $this->SetTemplateAction('index');
    }

    /**
     * Отображение списка персональных блогов
     */
    protected function EventShowBlogsPersonal() {

        // * По какому полю сортировать
        $sOrder = 'blog_count_topic';

        // * В каком направлении сортировать
        $sOrderWay = F::GetRequestStr('order_way', 'desc');

        // * Фильтр поиска блогов
        $aFilter = array(
            'include_type' => 'personal'
        );

        // * Передан ли номер страницы
        $iPage = preg_match('/^\d+$/i', $this->GetEventMatch(2)) ? $this->GetEventMatch(2) : 1;

        // * Получаем список блогов
        $aResult = $this->Blog_GetBlogsByFilter(
            $aFilter, array($sOrder => $sOrderWay), $iPage, Config::Get('module.blog.per_page')
        );
        $aBlogs = $aResult['collection'];

        // * Формируем постраничность
        $aPaging = $this->Viewer_MakePaging(
            $aResult['count'], $iPage, Config::Get('module.blog.per_page'), Config::Get('pagination.pages.count'),
            Router::GetPath('blogs') . 'personal/', array('order' => $sOrder, 'order_way' => $sOrderWay)
        );

        // * Загружаем переменные в шаблон
        $this->Viewer_Assign('aPaging', $aPaging);
        $this->Viewer_Assign('aBlogs', $aBlogs);
        $this->Viewer_Assign('sBlogOrder', htmlspecialchars($sOrder));
        $this->Viewer_Assign('sBlogOrderWay', htmlspecialchars($sOrderWay));
        $this->Viewer_Assign('sBlogOrderWayNext', htmlspecialchars($sOrderWay == 'desc' ? 'asc' : 'desc'));
        $this->Viewer_Assign('sShow', 'personal');
        $this->Viewer_Assign('sBlogsRootPage', Router::GetPath('blogs') . 'personal/');

        // * Устанавливаем title страницы
        $this->Viewer_AddHtmlTitle($this->Lang_Get('blog_menu_all_list'));

        // * Устанавливаем шаблон вывода
        $this->SetTemplateAction('index');
    }

    public function EventShutdown() {

        $this->Viewer_Assign('sMenuHeadItemSelect', $this->sMenuHeadItemSelect);
    }
}

// EOF