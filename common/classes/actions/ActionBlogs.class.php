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
        E::ModuleLang()->AddLangJs(
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
        E::ModuleViewer()->SetResponseAjax('json');

        // * Получаем из реквеста первые буквы блога
        if ($sTitle = F::GetRequestStr('blog_title')) {
            $sTitle = str_replace('%', '', $sTitle);
        }
        if (!$sTitle) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
            return;
        }

        // * Ищем блоги
        if (F::GetRequestStr('blog_type') == 'personal') {
            $aFilter = array('include_type' => 'personal', 'title' => "%{$sTitle}%");
        } else {
            $aFilter = array('exclude_type' => 'personal', 'title' => "%{$sTitle}%");
        }
        $aResult = E::ModuleBlog()->GetBlogsByFilter($aFilter, array('blog_title' => 'asc'), 1, 100);

        // * Формируем и возвращает ответ
        $aVars = array(
            'aBlogs'          => $aResult['collection'],
            'oUserCurrent'    => E::ModuleUser()->GetUserCurrent(),
            'sBlogsEmptyList' => E::ModuleLang()->Get('blogs_search_empty'),
        );
        E::ModuleViewer()->AssignAjax('sText', E::ModuleViewer()->Fetch('commons/common.blog_list.tpl', $aVars));
    }

    protected function EventIndex() {

        $this->EventShowBlogs();
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
            'include_type' => E::ModuleBlog()->GetAllowBlogTypes(E::ModuleUser()->GetUserCurrent(), 'list', true),
        );

        // * Передан ли номер страницы
        $iPage = preg_match('/^\d+$/i', $this->GetEventMatch(2)) ? $this->GetEventMatch(2) : 1;

        // * Получаем список блогов
        $aResult = E::ModuleBlog()->GetBlogsByFilter(
            $aFilter,
            ($sOrder == 'blog_title') ? array('blog_title' => $sOrderWay) : array($sOrder => $sOrderWay, 'blog_title' => 'asc'),
            $iPage, Config::Get('module.blog.per_page')
        );
        $aBlogs = $aResult['collection'];

        // * Формируем постраничность
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.blog.per_page'), Config::Get('pagination.pages.count'),
            R::GetPath('blogs'), array('order' => $sOrder, 'order_way' => $sOrderWay)
        );

        //  * Загружаем переменные в шаблон
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aBlogs', $aBlogs);
        E::ModuleViewer()->Assign('sBlogOrder', htmlspecialchars($sOrder));
        E::ModuleViewer()->Assign('sBlogOrderWay', htmlspecialchars($sOrderWay));
        E::ModuleViewer()->Assign('sBlogOrderWayNext', ($sOrderWay == 'desc' ? 'asc' : 'desc'));
        E::ModuleViewer()->Assign('sShow', 'collective');
        E::ModuleViewer()->Assign('sBlogsRootPage', R::GetPath('blogs'));

        // * Устанавливаем title страницы
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('blog_menu_all_list'));

        // * Устанавливаем шаблон вывода
        $this->SetTemplateAction('index');
    }

    /**
     * Отображение списка персональных блогов
     */
    protected function EventShowBlogsPersonal() {

        // * По какому полю сортировать
        $sOrder = F::GetRequestStr('order', 'blog_title');

        // * В каком направлении сортировать
        $sOrderWay = F::GetRequestStr('order_way', 'desc');

        // * Фильтр поиска блогов
        $aFilter = array(
            'include_type' => 'personal'
        );

        // * Передан ли номер страницы
        $iPage = preg_match('/^\d+$/i', $this->GetParamEventMatch(0, 2)) ? $this->GetParamEventMatch(0, 2) : 1;

        // * Получаем список блогов
        $aResult = E::ModuleBlog()->GetBlogsByFilter(
            $aFilter, array($sOrder => $sOrderWay), $iPage, Config::Get('module.blog.per_page')
        );
        $aBlogs = $aResult['collection'];

        // * Формируем постраничность
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.blog.per_page'), Config::Get('pagination.pages.count'),
            R::GetPath('blogs') . 'personal/', array('order' => $sOrder, 'order_way' => $sOrderWay)
        );

        // * Загружаем переменные в шаблон
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aBlogs', $aBlogs);
        E::ModuleViewer()->Assign('sBlogOrder', htmlspecialchars($sOrder));
        E::ModuleViewer()->Assign('sBlogOrderWay', htmlspecialchars($sOrderWay));
        E::ModuleViewer()->Assign('sBlogOrderWayNext', ($sOrderWay == 'desc' ? 'asc' : 'desc'));
        E::ModuleViewer()->Assign('sShow', 'personal');
        E::ModuleViewer()->Assign('sBlogsRootPage', R::GetPath('blogs') . 'personal/');

        // * Устанавливаем title страницы
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('blog_menu_all_list'));

        // * Устанавливаем шаблон вывода
        $this->SetTemplateAction('index');
    }

    public function EventShutdown() {

        E::ModuleViewer()->Assign('sMenuHeadItemSelect', $this->sMenuHeadItemSelect);
    }
}

// EOF
