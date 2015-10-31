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
 * Обработка главной страницы, т.е. УРЛа вида /index/
 *
 * @package actions
 * @since   1.0
 */
class ActionIndex extends Action {
    /**
     * Главное меню
     *
     * @var string
     */
    protected $sMenuHeadItemSelect = 'index';
    /**
     * Меню
     *
     * @var string
     */
    protected $sMenuItemSelect = 'index';
    /**
     * Субменю
     *
     * @var string
     */
    protected $sMenuSubItemSelect = 'good';
    /**
     * Число новых топиков
     *
     * @var int
     */
    protected $iCountTopicsNew = 0;
    /**
     * Число новых топиков в коллективных блогах
     *
     * @var int
     */
    protected $iCountTopicsCollectiveNew = 0;
    /**
     * Число новых топиков в персональных блогах
     *
     * @var int
     */
    protected $iCountTopicsPersonalNew = 0;

    /**
     * Named filter for topic list
     *
     * @var string
     */
    protected $sTopicFilter = '';

    protected $sTopicFilterPeriod;

    /**
     * Инициализация
     *
     */
    public function Init() {
        /**
         * Подсчитываем новые топики
         */
        $this->iCountTopicsCollectiveNew=E::ModuleTopic()->GetCountTopicsCollectiveNew();
        $this->iCountTopicsPersonalNew=E::ModuleTopic()->GetCountTopicsPersonalNew();
        $this->iCountTopicsNew=$this->iCountTopicsCollectiveNew+$this->iCountTopicsPersonalNew;
    }

    /**
     * Регистрация евентов
     *
     */
    protected function RegisterEvent() {
        $this->AddEventPreg('/^(page([1-9]\d{0,5}))?$/i', 'EventIndex');
        $this->AddEventPreg('/^new$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventNew');
        $this->AddEventPreg('/^all$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventAll');
        $this->AddEventPreg('/^newall$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventNewAll');
        $this->AddEventPreg('/^discussed/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventDiscussed');
        if (C::Get('rating.enabled')) {
            $this->AddEventPreg('/^top/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventTop');
        }
    }


    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */

    /**
     * Вывод рейтинговых топиков
     */
    protected function EventTop() {
        $this->sTopicFilterPeriod = 1; // по дефолту 1 день
        if (in_array(F::GetRequestStr('period'), array(1, 7, 30, 'all'))) {
            $this->sTopicFilterPeriod = F::GetRequestStr('period');
        }
        /**
         * Меню
         */
        $this->sTopicFilter = $this->sMenuSubItemSelect = 'top';
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(0, 2) ? $this->GetParamEventMatch(0, 2) : 1;
        if ($iPage == 1 && !F::GetRequest('period')) {
            E::ModuleViewer()->SetHtmlCanonical(R::GetPath('index') . 'top/');
        }
        /**
         * Получаем список топиков
         */
        $aResult = E::ModuleTopic()->GetTopicsTop(
            $iPage, Config::Get('module.topic.per_page'), $this->sTopicFilterPeriod == 'all' ? null : $this->sTopicFilterPeriod * 60 * 60 * 24
        );
        /**
         * Если нет топиков за 1 день, то показываем за неделю (7)
         */
        if (!$aResult['count'] && $iPage == 1 && !F::GetRequest('period')) {
            $this->sTopicFilterPeriod = 7;
            $aResult = E::ModuleTopic()->GetTopicsTop(
                $iPage, Config::Get('module.topic.per_page'), $this->sTopicFilterPeriod == 'all' ? null : $this->sTopicFilterPeriod * 60 * 60 * 24
            );
        }
        $aTopics = $aResult['collection'];
        /**
         * Вызов хуков
         */
        E::ModuleHook()->Run('topics_list_show', array('aTopics' => $aTopics));
        /**
         * Формируем постраничность
         */
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.topic.per_page'), Config::Get('pagination.pages.count'),
            R::GetPath('index') . 'top', array('period' => $this->sTopicFilterPeriod)
        );

        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('blog_menu_all_top') . ($iPage>1 ? (' (' . $iPage . ')') : ''));
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('aTopics', $aTopics);
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('sPeriodSelectCurrent', $this->sTopicFilterPeriod);
        E::ModuleViewer()->Assign('sPeriodSelectRoot', R::GetPath('index') . 'top/');
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('index');
    }

    /**
     * Вывод обсуждаемых топиков
     */
    protected function EventDiscussed() {
        $this->sTopicFilterPeriod = 1; // по дефолту 1 день
        if (in_array(F::GetRequestStr('period'), array(1, 7, 30, 'all'))) {
            $this->sTopicFilterPeriod = F::GetRequestStr('period');
        }
        /**
         * Меню
         */
        $this->sTopicFilter = $this->sMenuSubItemSelect = 'discussed';
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(0, 2) ? $this->GetParamEventMatch(0, 2) : 1;
        if ($iPage == 1 && !F::GetRequest('period')) {
            E::ModuleViewer()->SetHtmlCanonical(R::GetPath('index') . 'discussed/');
        }
        /**
         * Получаем список топиков
         */
        $aResult = E::ModuleTopic()->GetTopicsDiscussed(
            $iPage, Config::Get('module.topic.per_page'), $this->sTopicFilterPeriod == 'all' ? null : $this->sTopicFilterPeriod * 60 * 60 * 24
        );
        /**
         * Если нет топиков за 1 день, то показываем за неделю (7)
         */
        if (!$aResult['count'] && $iPage == 1 && !F::GetRequest('period')) {
            $this->sTopicFilterPeriod = 7;
            $aResult = E::ModuleTopic()->GetTopicsDiscussed(
                $iPage, Config::Get('module.topic.per_page'), $this->sTopicFilterPeriod == 'all' ? null : $this->sTopicFilterPeriod * 60 * 60 * 24
            );
        }
        $aTopics = $aResult['collection'];
        /**
         * Вызов хуков
         */
        E::ModuleHook()->Run('topics_list_show', array('aTopics' => $aTopics));
        /**
         * Формируем постраничность
         */
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.topic.per_page'), Config::Get('pagination.pages.count'),
            R::GetPath('index') . 'discussed', array('period' => $this->sTopicFilterPeriod)
        );

        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('blog_menu_collective_discussed') . ($iPage>1 ? (' (' . $iPage . ')') : ''));
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('aTopics', $aTopics);
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('sPeriodSelectCurrent', $this->sTopicFilterPeriod);
        E::ModuleViewer()->Assign('sPeriodSelectRoot', R::GetPath('index') . 'discussed/');
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('index');
    }

    /**
     * Вывод новых топиков
     */
    protected function EventNew() {

        E::ModuleViewer()->SetHtmlRssAlternate(R::GetPath('rss') . 'index/new/', Config::Get('view.name'));
        /**
         * Меню
         */
        $this->sTopicFilter = $this->sMenuSubItemSelect = 'new';
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(0, 2) ? $this->GetParamEventMatch(0, 2) : 1;
        /**
         * Получаем список топиков
         */
        $aResult = E::ModuleTopic()->GetTopicsNew($iPage, Config::Get('module.topic.per_page'));
        $aTopics = $aResult['collection'];
        /**
         * Вызов хуков
         */
        E::ModuleHook()->Run('topics_list_show', array('aTopics' => $aTopics));
        /**
         * Формируем постраничность
         */
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.topic.per_page'), Config::Get('pagination.pages.count'),
            R::GetPath('index') . 'new'
        );
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('aTopics', $aTopics);
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('index');
    }

    /**
     * Вывод ВСЕХ новых топиков
     */
    protected function EventNewAll() {

        $this->EventAll();
    }

    /**
     * Вывод ВСЕХ топиков
     */
    protected function EventAll() {

        E::ModuleViewer()->SetHtmlRssAlternate(R::GetPath('rss') . 'index/all/', Config::Get('view.name'));
        /**
         * Меню
         */
        $this->sTopicFilter = $this->sMenuSubItemSelect = 'new';
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(0, 2) ? $this->GetParamEventMatch(0, 2) : 1;
        /**
         * Получаем список топиков
         */
        $aResult = E::ModuleTopic()->GetTopicsNewAll($iPage, Config::Get('module.topic.per_page'));
        $aTopics = $aResult['collection'];
        /**
         * Вызов хуков
         */
        E::ModuleHook()->Run('topics_list_show', array('aTopics' => $aTopics));
        /**
         * Формируем постраничность
         */
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.topic.per_page'), Config::Get('pagination.pages.count'),
            R::GetPath('index') . 'newall'
        );

        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('blog_menu_all_new')  . ($iPage>1 ? (' (' . $iPage . ')') : ''));
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('aTopics', $aTopics);
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('index');
    }

    /**
     * Вывод интересных на главную
     *
     */
    protected function EventIndex() {

        E::ModuleViewer()->SetHtmlRssAlternate(R::GetPath('rss') . 'index/', Config::Get('view.name'));
        /**
         * Меню
         */
        $this->sTopicFilter = $this->sMenuSubItemSelect = 'good';
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetEventMatch(2) ? $this->GetEventMatch(2) : 1;
        /**
         * Устанавливаем основной URL для поисковиков
         */
        if ($iPage == 1) {
            E::ModuleViewer()->SetHtmlCanonical(trim(Config::Get('path.root.url'), '/') . '/');
        }
        /**
         * Получаем список топиков
         */
        $aResult = E::ModuleTopic()->GetTopicsGood($iPage, Config::Get('module.topic.per_page'));
        $aTopics = $aResult['collection'];
        /**
         * Вызов хуков
         */
        E::ModuleHook()->Run('topics_list_show', array('aTopics' => $aTopics));
        /**
         * Формируем постраничность
         */
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.topic.per_page'), Config::Get('pagination.pages.count'),
            R::GetPath('index')
        );
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('aTopics', $aTopics);
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('index');
    }

    /**
     * При завершении экшена загружаем переменные в шаблон
     *
     */
    public function EventShutdown() {
        E::ModuleViewer()->Assign('sMenuHeadItemSelect', $this->sMenuHeadItemSelect);
        E::ModuleViewer()->Assign('sMenuItemSelect', $this->sMenuItemSelect);
        E::ModuleViewer()->Assign('sMenuSubItemSelect', $this->sMenuSubItemSelect);
        E::ModuleViewer()->Assign('sTopicFilter', $this->sTopicFilter);
        E::ModuleViewer()->Assign('sTopicFilterPeriod', $this->sTopicFilterPeriod);
        E::ModuleViewer()->Assign('iCountTopicsNew', $this->iCountTopicsNew);
        E::ModuleViewer()->Assign('iCountTopicsCollectiveNew', $this->iCountTopicsCollectiveNew);
        E::ModuleViewer()->Assign('iCountTopicsPersonalNew', $this->iCountTopicsPersonalNew);
    }
}

// EOF