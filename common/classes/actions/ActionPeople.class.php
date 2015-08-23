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
 *
 * @package actions
 * @since   1.0
 */
class ActionPeople extends Action {
    /**
     * Главное меню
     *
     * @var string
     */
    protected $sMenuHeadItemSelect = 'people';
    /**
     * Меню
     *
     * @var string
     */
    protected $sMenuItemSelect = 'all';

    /**
     * Инициализация
     *
     */
    public function Init() {

        // Устанавливаем title страницы
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('people'));

        if (!E::ModuleSession()->GetCookie('view') && F::GetRequestStr('view')) {
            E::ModuleSession()->DelCookie('view');
        }
        E::ModuleSession()->SetCookie('view', F::GetRequestStr('view', '2'), 60 * 60 * 24 * 365);

    }

    /**
     * Регистрируем евенты
     *
     */
    protected function RegisterEvent() {

        $this->AddEvent('online', 'EventOnline');
        $this->AddEvent('new', 'EventNew');
        $this->AddEventPreg('/^(index)?$/i', '/^(page([1-9]\d{0,5}))?$/i', '/^$/i', 'EventIndex');
        $this->AddEventPreg('/^ajax-search$/i', 'EventAjaxSearch');

        $this->AddEventPreg('/^country$/i', '/^\d+$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventCountry');
        $this->AddEventPreg('/^city$/i', '/^\d+$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventCity');
    }


    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */

    /**
     * Поиск пользователей по логину
     *
     */
    protected function EventAjaxSearch() {

        // Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');
        // Получаем из реквеста первые быквы для поиска пользователей по логину
        $sTitle = F::GetRequest('user_login');
        if (is_string($sTitle) && mb_strlen($sTitle, 'utf-8')) {
            $sTitle = str_replace(array('_', '%'), array('\_', '\%'), $sTitle);
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
            return;
        }

        // Как именно искать: совпадение в любой части логина, или только начало или конец логина
        if (F::GetRequest('isPrefix')) {
            $sTitle .= '%';
        } elseif (F::GetRequest('isPostfix')) {
            $sTitle = '%' . $sTitle;
        } else {
            $sTitle = '%' . $sTitle . '%';
        }
        $aFilter = array('activate' => 1, 'login' => $sTitle);
        // Ищем пользователей
        $aResult = E::ModuleUser()->GetUsersByFilter($aFilter, array('user_rating' => 'desc'), 1, 50);

        // Формируем ответ
        $aVars = array(
            'aUsersList'     => $aResult['collection'],
            'oUserCurrent'   => E::ModuleUser()->GetUserCurrent(),
            'sUserListEmpty' => E::ModuleLang()->Get('user_search_empty'),
        );
        E::ModuleViewer()->AssignAjax('sText', E::ModuleViewer()->Fetch('commons/common.user_list.tpl', $aVars));
    }

    /**
     * Показывает юзеров по стране
     *
     */
    protected function EventCountry() {

        $this->sMenuItemSelect = 'country';

        // Страна существует?
        if (!($oCountry = E::ModuleGeo()->GetCountryById($this->getParam(0)))) {
            return parent::EventNotFound();
        }
        // Получаем статистику
        // Old skin compatibility
        $this->GetStats();

        // Передан ли номер страницы
        $iPage = $this->GetParamEventMatch(1, 2) ? $this->GetParamEventMatch(1, 2) : 1;

        // Получаем список связей пользователей со страной
        $aResult = E::ModuleGeo()->GetTargets(
            array('country_id' => $oCountry->getId(), 'target_type' => 'user'), $iPage,
            Config::Get('module.user.per_page')
        );
        $aUsersId = array();
        foreach ($aResult['collection'] as $oTarget) {
            $aUsersId[] = $oTarget->getTargetId();
        }
        $aUsersCountry = E::ModuleUser()->GetUsersAdditionalData($aUsersId);

        // Формируем постраничность
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.user.per_page'), Config::Get('pagination.pages.count'),
            R::GetPath('people') . $this->sCurrentEvent . '/' . $oCountry->getId()
        );
        // Загружаем переменные в шаблон
        if ($aUsersCountry) {
            E::ModuleViewer()->Assign('aPaging', $aPaging);
        }
        E::ModuleViewer()->Assign('oCountry', $oCountry);
        E::ModuleViewer()->Assign('aUsersCountry', $aUsersCountry);
    }

    /**
     * Показывает юзеров по городу
     *
     */
    protected function EventCity() {

        $this->sMenuItemSelect = 'city';
        // Город существует?
        if (!($oCity = E::ModuleGeo()->GetCityById($this->getParam(0)))) {
            return parent::EventNotFound();
        }
        // Получаем статистику
        $this->GetStats();

        // Передан ли номер страницы
        $iPage = $this->GetParamEventMatch(1, 2) ? $this->GetParamEventMatch(1, 2) : 1;

        // Получаем список юзеров
        $aResult = E::ModuleGeo()->GetTargets(
            array('city_id' => $oCity->getId(), 'target_type' => 'user'), $iPage, Config::Get('module.user.per_page')
        );
        $aUsersId = array();
        foreach ($aResult['collection'] as $oTarget) {
            $aUsersId[] = $oTarget->getTargetId();
        }
        $aUsersCity = E::ModuleUser()->GetUsersAdditionalData($aUsersId);

        // Формируем постраничность
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.user.per_page'), Config::Get('pagination.pages.count'),
            R::GetPath('people') . $this->sCurrentEvent . '/' . $oCity->getId()
        );

        // Загружаем переменные в шаблон
        if ($aUsersCity) {
            E::ModuleViewer()->Assign('aPaging', $aPaging);
        }
        E::ModuleViewer()->Assign('oCity', $oCity);
        E::ModuleViewer()->Assign('aUsersCity', $aUsersCity);
    }

    /**
     * Показываем последних на сайте
     *
     */
    protected function EventOnline() {

        $this->sMenuItemSelect = 'online';

        // Последние по визиту на сайт
        $aUsersLast = E::ModuleUser()->GetUsersByDateLast(C::Get('module.user.per_page'));
        E::ModuleViewer()->Assign('aUsersLast', $aUsersLast);

        // Получаем статистику
        $this->GetStats();
    }

    /**
     * Показываем новых на сайте
     *
     */
    protected function EventNew() {

        $this->sMenuItemSelect = 'new';

        // Последние по регистрации
        $aUsersRegister = E::ModuleUser()->GetUsersByDateRegister(C::Get('module.user.per_page'));
        E::ModuleViewer()->Assign('aUsersRegister', $aUsersRegister);

        // Получаем статистику
        $this->GetStats();
    }

    /**
     * Показываем юзеров
     *
     */
    protected function EventIndex() {

        // Получаем статистику
        $this->GetStats();
        // По какому полю сортировать
        $sOrder = 'user_rating';
        if (F::GetRequest('order')) {
            $sOrder = F::GetRequestStr('order');
        }
        // В каком направлении сортировать
        $sOrderWay = 'desc';
        if (F::GetRequest('order_way')) {
            $sOrderWay = F::GetRequestStr('order_way');
        }
        $aFilter = array(
            'activate' => 1
        );

        // Передан ли номер страницы
        $iPage = $this->GetParamEventMatch(0, 2) ? $this->GetParamEventMatch(0, 2) : 1;

        // Получаем список юзеров
        $aResult = E::ModuleUser()->GetUsersByFilter(
            $aFilter, array($sOrder => $sOrderWay), $iPage, Config::Get('module.user.per_page')
        );
        $aUsers = $aResult['collection'];

        // Формируем постраничность
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.user.per_page'), Config::Get('pagination.pages.count'),
            R::GetPath('people') . 'index', array('order' => $sOrder, 'order_way' => $sOrderWay)
        );

        // Получаем алфавитный указатель на список пользователей
        $aPrefixUser = E::ModuleUser()->GetGroupPrefixUser(1);

        // Загружаем переменные в шаблон
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aUsersRating', $aUsers);
        E::ModuleViewer()->Assign('aPrefixUser', $aPrefixUser);
        E::ModuleViewer()->Assign("sUsersOrder", htmlspecialchars($sOrder));
        E::ModuleViewer()->Assign("sUsersOrderWay", htmlspecialchars($sOrderWay));
        E::ModuleViewer()->Assign("sUsersOrderWayNext", htmlspecialchars($sOrderWay == 'desc' ? 'asc' : 'desc'));

        // Устанавливаем шаблон вывода
        $this->SetTemplateAction('index');
    }

    /**
     * Получение статистики
     *
     */
    protected function GetStats() {

        // Статистика кто, где и т.п.
        $aStat = E::ModuleUser()->GetStatUsers();

        // Загружаем переменные в шаблон
        E::ModuleViewer()->Assign('aStat', $aStat);
    }

    /**
     * Выполняется при завершении работы экшена
     *
     */
    public function EventShutdown() {

        // Загружаем в шаблон необходимые переменные
        E::ModuleViewer()->Assign('sMenuHeadItemSelect', $this->sMenuHeadItemSelect);
        E::ModuleViewer()->Assign('sMenuItemSelect', $this->sMenuItemSelect);
    }
}

// EOF