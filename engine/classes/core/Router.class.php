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

F::IncludeFile('Action.class.php');
F::IncludeFile('ActionPlugin.class.php');

/**
 * Класс роутинга
 * Инициализирует ядро, определяет какой экшен запустить согласно URL'у и запускает его.
 *
 * @package engine
 * @since 1.0
 */
class Router extends LsObject {

    const BACKWARD_COOKIE = 'route_backward';
    /**
     * Конфигурация роутинга, получается из конфига
     *
     * @var array
     */
    protected $aConfigRoute = array();

    /**
     * Текущий экшен
     *
     * @var string|null
     */
    static protected $sAction = null;

    /**
     * Текущий евент
     *
     * @var string|null
     */
    static protected $sActionEvent = null;

    /**
     * Имя текущего евента
     *
     * @var string|null
     */
    static protected $sActionEventName = null;

    /**
     * Класс текущего экшена
     *
     * @var string|null
     */
    static protected $sActionClass = null;

    /**
     * Текущий полный URL
     *
     * @var string|null
     */
    static protected $sPathWebCurrent = null;

    /**
     * Текущий обрабатываемый путь контроллера
     *
     * @var string|null
     */
    static protected $sControllerPath = null;

    /**
     * Текущий язык
     *
     * @var string|null
     */
    static protected $sLang = null;

    /**
     * Список параметров ЧПУ URL
     * <pre>/action/event/param0/param1/../paramN/</pre>
     *
     * @var array
     */
    static protected $aParams = array();

    static protected $aRequestURI = array();

    protected $aCurrentUrl = array();

    protected $aBackwardUrl = array();

    protected $aDefinedClasses = array();

    /**
     * Объект текущего экшена
     *
     * @var Action|null
     */
    protected $oAction = null;

    /**
     * Объект ядра
     *
     * @var Engine|null
     */
    protected $oEngine = null;

    /**
     * Покаывать или нет статистику выполнения
     *
     * @var bool
     */
    static protected $bShowStats = true;

    /**
     * Объект роутинга
     * @see getInstance
     *
     * @var Router|null
     */
    static protected $oInstance = null;

    /**
     * Маска фомирования URL топика
     *
     * @var string
     */
    static protected $sTopicUrlMask = null;

    /**
     * Маска фомирования URL профиля пользователя
     *
     * @var string
     */
    static protected $sUserUrlMask = null;

    /**
     * Делает возможным только один экземпляр этого класса
     *
     * @return Router
     */
    static public function getInstance() {

        if (isset(static::$oInstance) && (static::$oInstance instanceof self)) {
            return static::$oInstance;
        } else {
            static::$oInstance = new static();
            return static::$oInstance;
        }
    }

    /**
     * Загрузка конфига роутинга при создании объекта
     */
    public function __construct() {

        $this->LoadConfig();
    }

    /**
     * Запускает весь процесс :)
     *
     */
    public function Exec() {

        $this->ParseUrl();
        $this->DefineActionClass(); // Для возможности ДО инициализации модулей определить какой action/event запрошен
        $this->oEngine = E::getInstance();
        $this->oEngine->Init();

        // Подгружаем предыдущий URL, если он был
        $sData = E::ModuleSession()->GetCookie(static::BACKWARD_COOKIE);
        if ($sData) {
            $aData = F::Unserialize($sData);
            if (is_array($aData)) {
                $this->aBackwardUrl = $aData;
            }
        }
        // И сохраняем текущий, если это не ajax-запрос
        if (!F::AjaxRequest()) {
            E::ModuleSession()->SetCookie(static::BACKWARD_COOKIE, F::Serialize($this->aCurrentUrl, true));
        }

        $this->ExecAction();
        $this->Shutdown(false);
    }

    /**
     * Завершение работы роутинга
     *
     * @param bool $bExit    Принудительно завершить выполнение скрипта
     */
    public function Shutdown($bExit = true) {

        $this->AssignVars();
        $this->oEngine->Shutdown();
        E::ModuleViewer()->Display($this->oAction->GetTemplate());
        if ($bExit) {
            exit();
        }
    }

    /**
     * Парсим URL
     * Пример: http://site.ru/action/event/param1/param2/  на выходе получим:
     *  static::$sAction='action';
     *    static::$sActionEvent='event';
     *    static::$aParams=array('param1','param2');
     *
     */
    protected function ParseUrl() {

        $sReq = $this->GetRequestUri();
        $aRequestUrl = $this->GetRequestArray($sReq);

        // Только для мультиязычных сайтов
        if (Config::Get('lang.multilang')) {
            // Получаем список доступных языков
            $aLangs = (array)Config::Get('lang.allow');

            // Проверка языка в URL
            if ($aLangs && Config::Get('lang.in_url')) {
                if (sizeof($aLangs) && sizeof($aRequestUrl) && in_array($aRequestUrl[0], $aLangs)) {
                    static::$sLang = array_shift($aRequestUrl);
                }
            }
        }

        static::$aRequestURI = $aRequestUrl = $this->RewriteRequest($aRequestUrl);

        static::$sAction = array_shift($aRequestUrl);
        static::$sActionEvent = array_shift($aRequestUrl);
        static::$aParams = $aRequestUrl;

        // Только для мультиязычных сайтов
        if (Config::Get('lang.multilang')) {
            // Проверка языка в GET-параметрах
            if ($aLangs && Config::Get('lang.in_get')) {
                $sLangParam = (is_string(Config::Get('lang.in_get')) ? Config::Get('lang.in_get') : 'lang');
                $sLang = F::GetRequestStr($sLangParam, null, 'get');
                if ($sLang) {
                    static::$sLang = $sLang;
                }
            }
        }

        $this->aCurrentUrl = parse_url($_SERVER['REQUEST_URI']);
        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            list($this->aCurrentUrl['protocol']) = explode('/', $_SERVER['SERVER_PROTOCOL'], 1);
        } else {
            $this->aCurrentUrl['protocol'] = 'http';
        }
        if (!isset($this->aCurrentUrl['scheme']) && $this->aCurrentUrl['protocol']) {
            $this->aCurrentUrl['scheme'] = $this->aCurrentUrl['protocol'];
        }
        $this->aCurrentUrl['root'] = F::File_RootUrl();
        $this->aCurrentUrl['lang'] = static::$sLang;
        $this->aCurrentUrl['action'] = static::$sAction;
        $this->aCurrentUrl['event'] = static::$sActionEvent;
        $this->aCurrentUrl['params'] = implode('/', static::$aParams);
    }

    /**
     * Метод выполняет первичную обработку $_SERVER['REQUEST_URI']
     *
     * @return string
     */
    protected function GetRequestUri() {

        $sReq = preg_replace('/\/+/', '/', $_SERVER['REQUEST_URI']);
        if (substr($sReq, -1) == '/') {
            $sLastChar = '/';
        } else {
            $sLastChar = '';
        }
        $sReq = preg_replace('/^\/(.*)\/?$/U', '$1', $sReq);
        $sReq = preg_replace('/^(.*)\?.*$/U', '$1', $sReq);

        // * Формируем $sPathWebCurrent ДО применения реврайтов
        static::$sPathWebCurrent = F::File_RootUrl() . join('/', $this->GetRequestArray($sReq));
        return $sReq . $sLastChar;
    }

    /**
     * Возвращает массив реквеста
     *
     * @param string $sReq    Строка реквеста
     * @return array
     */
    protected function GetRequestArray($sReq) {

        $aRequestUrl = ($sReq == '') ? array() : explode('/', trim($sReq, '/'));
        for ($i = 0; $i < Config::Get('path.offset_request_url'); $i++) {
            array_shift($aRequestUrl);
        }
        $aRequestUrl = array_map('urldecode', $aRequestUrl);
        return $aRequestUrl;
    }

    /**
     * Returns router URI rules
     *
     * @return array
     */
    protected function GetRouterUriRules() {

        $aRewrite = (array)Config::Get('router.uri');
        /*
        $sTopicUrlPattern = static::GetTopicUrlPattern();
        if ($sTopicUrlPattern) {
            $aRewrite = array_merge($aRewrite, array($sTopicUrlPattern => 'blog/$1.html'));
        }
        $sUserUrlPattern = static::GetUserUrlPattern();
        if ($sUserUrlPattern) {
            if (strpos(static::GetUserUrlMask(), '%user_id%')) {
                $aRewrite = array_merge($aRewrite, array($sUserUrlPattern => 'profile/id-$1'));
            } elseif (strpos(static::GetUserUrlMask(), '%login%')) {
                $aRewrite = array_merge($aRewrite, array($sUserUrlPattern => 'profile/login-$1'));
            }
        }
        */
        return $aRewrite;
    }

    /**
     * Applies config rewrite rules to request URI array, uses Config::Get('router.uri')
     *
     * @param array $aRequestUrl Request URI array
     *
     * @return array
     */
    protected function RewriteRequest($aRequestUrl) {

        // * Правила Rewrite для REQUEST_URI
        $sReq = implode('/', $aRequestUrl);
        if ($aRewrite = $this->GetRouterUriRules()) {
            foreach($aRewrite as $sPattern => $sReplace) {
                if (preg_match($sPattern, $sReq)) {
                    $sReq = preg_replace($sPattern, $sReplace, $sReq);
                    break;
                }
            }
        }
        if (substr($sReq, 0, 1) == '@') {
            $this->SpecialAction($sReq);
        }
        return (trim($sReq, '/') == '') ? array() : explode('/', $sReq);
    }

    /**
     * Applies internal rewrite rules to request URI array, uses topics' and profiles' patterns
     *
     * @param array $aRequestUrl Request URI array
     *
     * @return array
     */
    protected function RewriteInternal($aRequestUrl) {

        $aRewrite = array();
        if ($sTopicUrlPattern = static::GetTopicUrlPattern()) {
            $aRewrite = array_merge($aRewrite, array($sTopicUrlPattern => 'blog/$1.html'));
        }
        if ($sUserUrlPattern = static::GetUserUrlPattern()) {
            if (strpos(static::GetUserUrlMask(), '%user_id%')) {
                $aRewrite = array_merge($aRewrite, array($sUserUrlPattern => 'profile/id-$1'));
            } elseif (strpos(static::GetUserUrlMask(), '%login%')) {
                $aRewrite = array_merge($aRewrite, array($sUserUrlPattern => 'profile/login-$1'));
            }
        }
        // * Правила Rewrite для REQUEST_URI
        if ($aRewrite) {
            $sReq = implode('/', $aRequestUrl);
            foreach($aRewrite as $sPattern => $sReplace) {
                if (preg_match($sPattern, $sReq)) {
                    $sReq = preg_replace($sPattern, $sReplace, $sReq);
                    break;
                }
            }
            return (trim($sReq, '/') == '') ? array() : explode('/', $sReq);
        }
        return $aRequestUrl;
    }

    /**
     * Специальное действие по REQUEST_URI
     *
     * @param string $sReq
     */
    protected function SpecialAction($sReq) {

        if (substr($sReq, 0, 4) == '@404') {
            F::HttpHeader('404 Not Found');
            exit;
        } elseif (preg_match('~@die(.*)~i', $sReq, $aMatches)) {
            if (isset($aMatches[1]) && $aMatches[1]) {
                $sMsg = trim($aMatches[1]);
                if (substr($sMsg, 0, 1) == '(' && substr($sMsg, -1) == ')') $sMsg = trim($sMsg, '()');
                if (substr($sMsg, 0, 1) == '"' && substr($sMsg, -1) == '"') $sMsg = trim($sMsg, '"');
                if (substr($sMsg, 0, 1) == '\'' && substr($sMsg, -1) == '\'') $sMsg = trim($sMsg, '\'');
                die($sMsg);
            }
        } else {
            exit;
        }
    }

    /**
     * Выполняет загрузку конфигов роутинга
     *
     */
    protected function LoadConfig() {

        //Конфиг роутинга, содержит соответствия URL и классов экшенов
        $this->aConfigRoute = Config::Get('router');
        // Переписываем конфиг согласно правилу rewrite
        foreach ((array)$this->aConfigRoute['rewrite'] as $sPage => $sRewrite) {
            if (isset($this->aConfigRoute['page'][$sPage])) {
                $this->aConfigRoute['page'][$sRewrite] = $this->aConfigRoute['page'][$sPage];
                unset($this->aConfigRoute['page'][$sPage]);
            }
        }
    }

    /**
     * Загружает в шаблонизатор необходимые переменные
     *
     */
    protected function AssignVars() {

        E::ModuleViewer()->Assign('sAction', $this->Standart(static::$sAction));
        E::ModuleViewer()->Assign('sEvent', static::$sActionEvent);
        E::ModuleViewer()->Assign('aParams', static::$aParams);
        E::ModuleViewer()->Assign('PATH_WEB_CURRENT', E::ModuleTools()->Urlspecialchars(static::$sPathWebCurrent));
    }

    /**
     * Запускает на выполнение экшен
     * Может запускаться рекурсивно если в одном экшене стоит переадресация на другой
     *
     */
    public function ExecAction() {

        $this->DefineActionClass();

        // * Сначала запускаем инициализирующий евент
        E::ModuleHook()->Run('init_action');

        $sActionClass = $this->DefineActionClass();

        // * Определяем наличие делегата экшена
        if ($aChain = E::ModulePlugin()->GetDelegationChain('action', $sActionClass)) {
            if (!empty($aChain)) {
                $sActionClass = $aChain[0];
            }
        }
        static::$sActionClass = $sActionClass;
        if (!class_exists($sActionClass)) {
            throw new Exception('Cannot load class "' . $sActionClass . '"');
        }
        $this->oAction = new $sActionClass(static::$sAction);

        // * Инициализируем экшен
        E::ModuleHook()->Run('action_init_' . strtolower($sActionClass) . '_before');
        $sInitResult = $this->oAction->Init();
        E::ModuleHook()->Run('action_init_' . strtolower($sActionClass) . '_after');

        if ($sInitResult === 'next') {
            $this->ExecAction();
        } else {
            // Если инициализация экшена прошла успешно и метод провеки доступа вернул
            // положительный результат то запускаем запрошенный ивент на исполнение.
            if ($sInitResult !== false && $this->oAction->Access(self::GetActionEvent()) !== false) {
                $res = $this->oAction->ExecEvent();
                static::$sActionEventName = $this->oAction->GetCurrentEventName();

                E::ModuleHook()->Run('action_shutdown_' . strtolower($sActionClass) . '_before');
                $this->oAction->EventShutdown();
                E::ModuleHook()->Run('action_shutdown_' . strtolower($sActionClass) . '_after');

                if ($res === 'next') {
                    $this->ExecAction();
                }
            } else {
                static::$sAction = $this->aConfigRoute['config']['action_not_found'];
                static::$sActionEvent = '404';
                $this->ExecAction();
            }
        }
    }

    /**
     * Tries to define action class in config and plugins
     *
     * @return null|string
     */
    protected function FindActionClass() {

        if (!static::$sAction) {
            $sActionClass = $this->DetermineClass($this->aConfigRoute['config']['action_default'], static::$sActionEvent);
            if ($sActionClass) {
                static::$sAction = $this->aConfigRoute['config']['action_default'];
            }
        } else {
            $sActionClass = $this->DetermineClass(static::$sAction, static::$sActionEvent);
        }
        return $sActionClass;
    }

    /**
     * Определяет какой класс соответствует текущему экшену
     *
     * @return string
     */
    protected function DefineActionClass() {

        if (isset($this->aDefinedClasses[static::$sAction][static::$sActionEvent])) {
            static::$sActionClass = $this->aDefinedClasses[static::$sAction][static::$sActionEvent];
        } else {
            $sActionClass = $this->FindActionClass();
            if (!$sActionClass && static::$aRequestURI) {
                //Если не находим нужного класса, то проверяем внутренний реврайтинг по паттернам, напр., топики
                $aRequestUrl = $this->RewriteInternal(static::$aRequestURI);
                if (static::$aRequestURI !== $aRequestUrl) {
                    static::$aRequestURI = $aRequestUrl;
                    static::$sAction = array_shift($aRequestUrl);
                    static::$sActionEvent = array_shift($aRequestUrl);
                    static::$aParams = $aRequestUrl;
                    $sActionClass = $this->FindActionClass();
                }
            }
            if (!$sActionClass) {
                //Если не находим нужного класса, то определяем класс экшена-обработчика ошибки
                static::$sAction = $this->aConfigRoute['config']['action_not_found'];
                static::$sActionEvent = '404';
                $sActionClass = $this->DetermineClass(static::$sAction, static::$sActionEvent);
            }
            if ($sActionClass) {
                static::$sActionClass = $sActionClass;
            } elseif (!$sActionClass && static::$sAction && isset($this->aConfigRoute['page'][static::$sAction])) {
                static::$sActionClass = $this->aConfigRoute['page'][static::$sAction];
            }

            // Если класс экшена так и не определен, то аварийное завершение
            if (!static::$sActionClass) {
                die('Action class does not define');
            }
            $this->aDefinedClasses[static::$sAction][static::$sActionEvent] = static::$sActionClass;
        }

        return static::$sActionClass;
    }

    /**
     * Determines action class by action (and optionally by event)
     *
     * @param string $sAction
     * @param string $sEvent
     *
     * @return null|string
     */
    protected function DetermineClass($sAction, $sEvent = null) {

        $sActionClass = null;

        if ($sAction && !$sEvent && strpos($sAction, '/')) {
            list($sAction, $sEvent, $sParams) = explode('/', $sAction, 3);
        }

        // Сначала ищем экшен по таблице роутинга
        if ($sAction && isset($this->aConfigRoute['page'][$sAction])) {
            $sActionClass = $this->aConfigRoute['page'][$sAction];
        }
        // Если в таблице нет и включено автоопределение роутинга, то ищем по путям и файлам
        if (!$sActionClass && Config::Get('router.config.autodefine')) {
            $sActionClass = Loader::SeekActionClass($sAction, $sEvent);
        }
        return $sActionClass;
    }

    /**
     * Функция переадресации на другой экшен
     * Если ею завершить евент в экшене то запустится новый экшен
     * Примеры:
     * <pre>
     * return R::Action('error');
     * return R::Action('error', '404');
     * return R::Action('error/404');
     * </pre>
     *
     * @param string $sAction    Экшен
     * @param string $sEvent    Евент
     * @param array $aParams    Список параметров
     * @return string
     */
    static public function Action($sAction, $sEvent = null, $aParams = null) {

        // если в $sAction передан путь вида action/event/param..., то обрабатываем его
        if (!$sEvent && !$aParams && ($n = substr_count($sAction, '/'))) {
            if ($n > 2) {
                list($sAction, $sEvent, $aParams) = explode('/', $sAction, 3);
                if ($aParams) $aParams = explode('/', $aParams);
            } else {
                list($sAction, $sEvent) = explode('/', $sAction);
                $aParams = array();
            }
        }
        static::$sAction = static::getInstance()->Rewrite($sAction);
        static::$sActionEvent = $sEvent;
        if (is_array($aParams)) {
            static::$aParams = $aParams;
        }
        return 'next';
    }

    /**
     * LS-compatible
     * Возвращает текущий ЧПУ url
     *
     * @return string
     */
    static public function GetPathWebCurrent() {

        return static::$sPathWebCurrent;
    }

    /**
     * Returns real URL (or path of URL) without rewrites
     *
     * @param bool $bPathOnly
     *
     * @return null|string
     */
    static public function RealUrl($bPathOnly = false) {

        $sResult = static::$sPathWebCurrent;
        if ($bPathOnly) {
            $sResult = F::File_LocalUrl($sResult);
        }
        return $sResult;
    }

    /**
     * Returns current language
     *
     * @return string
     */
    static public function GetLang() {

        return static::$sLang;
    }

    /**
     * Sets language
     *
     * @param string $sLang
     */
    static public function SetLang($sLang) {

        static::$sLang = $sLang;
    }

    /**
     * Returns current action
     *
     * @return string
     */
    static public function GetAction() {

        return static::getInstance()->Standart(static::$sAction);
    }

    /**
     * Returns current action's event
     *
     * @return string
     */
    static public function GetActionEvent() {

        return static::$sActionEvent;
    }

    /**
     * Sets event
     *
     * @param string $sEvent
     */
    static public function SetActionEvent($sEvent) {

        static::$sActionEvent = $sEvent;
    }

    /**
     * Returns current event name
     *
     * @return string
     */
    static public function GetActionEventName() {

        return static::$sActionEventName;
    }

    /**
     * Returns class name of current action
     *
     * @return string
     */
    static public function GetActionClass() {

        return static::$sActionClass;
    }

    /**
     * Возвращает параметры(те которые передаются в URL)
     *
     * @return array
     */
    static public function GetParams() {

        return static::$aParams;
    }

    /**
     * Возвращает параметр по номеру, если его нет то возвращается null
     * Нумерация параметров начинается нуля
     *
     * @param int $iOffset
     * @param string $sDefault
     *
     * @return string
     */
    static public function GetParam($iOffset, $sDefault = null) {

        $iOffset = (int)$iOffset;
        return isset(static::$aParams[$iOffset]) ? static::$aParams[$iOffset] : $sDefault;
    }

    /**
     * Возвращает текущий обрабатывемый путь контроллера
     *
     * @return string
     */
    static public function GetControllerPath() {

        if (is_null(static::$sControllerPath)) {
            static::$sControllerPath = static::GetAction() . '/';
            if (static::GetActionEvent()) static::$sControllerPath .= static::GetActionEvent() . '/';
            if (static::GetParams()) static::$sControllerPath .= implode('/', static::GetParams()) . '/';
        }
        return static::$sControllerPath;
    }

    /**
     * Устанавливает значение параметра
     *
     * @param int $iOffset Номер параметра, по идее может быть не только числом
     * @param string $sValue
     */
    static public function SetParam($iOffset, $sValue) {

        static::$aParams[$iOffset] = $sValue;
    }

    /**
     * Показывать или нет статистику выполение скрипта
     * Иногда бывает необходимо отключить показ, например, при выводе RSS ленты
     *
     * @param bool $bState
     */
    static public function SetIsShowStats($bState) {

        static::$bShowStats = $bState;
    }

    /**
     * Возвращает статус показывать или нет статистику
     *
     * @return bool
     */
    static public function GetIsShowStats() {

        return static::$bShowStats;
    }

    /**
     * Проверяет запрос послан как ajax или нет
     *
     * @return bool
     */
    static public function GetIsAjaxRequest() {

        return F::AjaxRequest();
    }

    /**
     * Блокируем копирование/клонирование объекта роутинга
     *
     */
    protected function __clone() {

    }

    /**
     * Возвращает правильную адресацию по переданому названию страницы (экшену)
     *
     * @param  string $sAction Экшен
     *
     * @return string
     */
    static public function GetPath($sAction) {

        // Если пользователь запросил action по умолчанию
        $sPage = ($sAction == 'default')
            ? static::getInstance()->aConfigRoute['config']['action_default']
            : $sAction;

        // Смотрим, есть ли правило rewrite
        $sPage = static::getInstance()->Rewrite($sPage);
        return rtrim(F::File_RootUrl(true), '/') . "/$sPage/";
    }

    /**
     * Try to find rewrite rule for given page.
     * On success returns right page, otherwise returns given param.
     *
     * @param  string $sPage
     * @return string
     */
    public function Rewrite($sPage) {

        return (isset($this->aConfigRoute['rewrite'][$sPage]))
            ? $this->aConfigRoute['rewrite'][$sPage]
            : $sPage;
    }

    /**
     * Стандартизирует определение внутренних ресурсов.
     *
     * Пытается по переданому экшену найти rewrite rule и
     * вернуть стандартное название ресусрса.
     *
     * @see    Rewrite
     * @param  string $sPage
     * @return string
     */
    public function Standart($sPage) {

        $aRewrite = array_flip($this->aConfigRoute['rewrite']);
        return (isset($aRewrite[$sPage]))
            ? $aRewrite[$sPage]
            : $sPage;
    }

    /**
     * Выполняет редирект, предварительно завершая работу Engine
     *
     * URL для редиректа:
     *      - полный:           http://ya.ru
     *      - относительный:    /path/to/go/
     *      - виртуальный:      action/event/params/
     *
     * @param string $sLocation    URL для редиректа
     */
    static public function Location($sLocation) {

        static::getInstance()->oEngine->Shutdown();
        if (substr($sLocation, 0, 1) !== '/') {
            // Проверка на "виртуальный" путь
            $sRelLocation = trim($sLocation, '/');
            if (preg_match('|^[a-z][\w\-]+$|', $sRelLocation)) {
                // задан action
                $sLocation = static::GetPath($sRelLocation);
            } elseif (preg_match('|^([a-z][\w\-]+)(\/.+)$|', $sRelLocation)) {
                // задан action/event/...
                list($sAction, $sRest) = explode('/', $sLocation, 2);
                $sLocation = static::GetPath($sAction) . '/' . $sRest;
            }
        }
        F::HttpLocation($sLocation);
    }

    /**
     * @param   array $aData
     * @param   string $sPart  One of values: 'url', 'link', 'root', 'path', 'action', 'event', 'params'
     * @return  string
     */
    protected function _getUrlPart($aData, $sPart) {

        $sResult = '';
        if ($sPart == 'url') {
            $sResult = $this->_getUrlPart($aData, 'link');
            if (isset($aData['query'])) {
                $sResult .= '?' . $aData['query'];
            }
            if (isset($aData['fragment'])) {
                $sResult .= '#' . $aData['fragment'];
            }
        } elseif ($sPart == 'link') {
            if (isset($aData['root'])) {
                $sResult = trim($aData['root'], '/');
            }
            if (isset($aData['action'])) {
                $sResult .= $this->_getUrlPart($aData, 'path');
            }
        } elseif ($sPart == 'path') {
            if (isset($aData['action'])) {
                $sResult = '/' . $aData['action'];
            }
            if (isset($aData['event'])) {
                $sResult .= '/' . $aData['event'];
            }
            if (isset($aData['params'])) {
                $sResult .= '/' . $aData['params'];
            }
        } elseif (isset($aData[$sPart])) {
            $sResult = $aData[$sPart];
        }
        return $sResult;
    }

    /**
     * @param string|null $sPart
     *
     * @return array|string
     */
    public function GetCurrentUrlInfo($sPart = null) {

        if (!$sPart) {
            return $this->aCurrentUrl;
        }
        return $this->_getUrlPart($this->aCurrentUrl, $sPart);
    }

    /**
     * @param string|null $sPart
     *
     * @return array|string
     */
    public function GetBackwardUrlInfo($sPart = null) {

        if (!$sPart) {
            return $this->aBackwardUrl;
        }
        return $this->_getUrlPart($this->aBackwardUrl, $sPart);
    }

    /**
     * Данные о текущем URL
     *
     * @param   string|null $sPart
     * @return  array|string
     */
    static public function Url($sPart = null) {

        return static::getInstance()->GetCurrentUrlInfo($sPart);
    }

    /**
     * Данные о предыдущем URL
     *
     * @param   string|null $sPart
     * @return  array|string
     */
    static public function Backward($sPart = null) {

        return static::getInstance()->GetBackwardUrlInfo($sPart);
    }

    /**
     * Переход к предыдущему URL
     */
    static public function GotoBack() {

        $sUrl = static::Backward('link');
        if ($sUrl) static::Url(('link'));
        static::Location($sUrl);
    }

    /**
     * Возврат к предыдущему URL
     * В отличие от GotoBack() анализирует переданные POST-параметры
     *
     * @param   bool $bSecurity  - защита от CSRF
     */
    static public function ReturnBack($bSecurity = null) {

        if (!$bSecurity || E::ModuleSecurity()->ValidateSendForm(false)) {
            if (($sUrl = F::GetPost('return_url')) || ($sUrl = F::GetPost('return-path'))) {
                static::Location($sUrl);
            }
        }
        static::GotoBack();
    }

    /**
     * Возвращает маску формирования URL топика
     *
     * @param  bool     $bEmptyIfWrong
     * @return string
     */
    static public function GetTopicUrlMask($bEmptyIfWrong = true) {

        if (is_null(static::$sTopicUrlMask)) {
            $sUrlMask = Config::Get('module.topic.url');
            if ($sUrlMask) {
                // WP compatible
                $sUrlMask = str_replace('%post_id%', '%topic_id%', $sUrlMask);
                $sUrlMask = str_replace('%postname%', '%topic_url%', $sUrlMask);
                $sUrlMask = str_replace('%author%', '%login%', $sUrlMask);

                // NuceURL compatible
                $sUrlMask = str_replace('%id%', '%topic_id%', $sUrlMask);
                $sUrlMask = str_replace('%blog%', '%blog_url%', $sUrlMask);
                $sUrlMask = str_replace('%title%', '%topic_url%', $sUrlMask);

                // В маске может быть только одно входение '%topic_id%' и '%topic_url%'
                if (substr_count($sUrlMask, '%topic_id%') > 1) {
                    $aParts = explode('%topic_id%', $sUrlMask, 2);
                    $sUrlMask = $aParts[0] . '%topic_id%' . str_replace('%topic_id%', '', $aParts[1]);
                }
                if (substr_count($sUrlMask, '%topic_url%') > 1) {
                    $aParts = explode('%topic_url%', $sUrlMask, 2);
                    $sUrlMask = $aParts[0] . '%topic_url%' . str_replace('%topic_url%', '', $aParts[1]);
                }
                $sUrlMask = preg_replace('#\/+#', '/', $sUrlMask);
            }
            static::$sTopicUrlMask = $sUrlMask;
        } else {
            $sUrlMask = static::$sTopicUrlMask;
        }

        if ($bEmptyIfWrong && (strpos($sUrlMask, '%topic_id%') === false) && (strpos($sUrlMask, '%topic_url%') === false)) {
            // В маске обязательно должны быть либо '%topic_id%', либо '%topic_url%'
            $sUrlMask = '';
        }
        return $sUrlMask;
    }

    /**
     * Returns pattern for topics' URL
     *
     * @return string
     */
    static public function GetTopicUrlPattern() {

        $sUrlPattern = static::GetTopicUrlMask();
        if ($sUrlPattern) {
            $sUrlPattern = preg_quote($sUrlPattern);
            $aReplace = array(
                '%year%'       => '\d{4}',
                '%month%'      => '\d{2}',
                '%day%'        => '\d{2}',
                '%hour%'       => '\d{2}',
                '%minute%'     => '\d{2}',
                '%second%'     => '\d{2}',
                '%login%'      => '[\w_\-]+',
                '%blog_url%'   => '[\w_\-]+',
                '%topic_type%' => '[\w_\-]+',
                '%topic_id%'   => '(\d+)',
                '%topic_url%'  => '([\w\-]+)',
            );
            // brackets in the pattern may be only once
            if (strpos($sUrlPattern, '%topic_id%') !== false && strpos($sUrlPattern, '%topic_url%') !== false) {
                // if both of masks are present then %topic_id% is main
                $aReplace['%topic_url%'] = '[\w\-]+';
            }
            // Если последним символом в шаблоне идет слеш, то надо его сделать опциональным
            if (substr($sUrlPattern, -1) == '/') {
                $sUrlPattern .= '?';
            }
            $sUrlPattern = '#^' . strtr($sUrlPattern, $aReplace) . '$#i';
        }
        return $sUrlPattern;
    }

    /**
     * Возвращает маску формирования URL профиля пользователя
     *
     * @param  bool     $bEmptyIfWrong
     * @return string
     */
    static public function GetUserUrlMask($bEmptyIfWrong = true) {

        $sUrlMask = ''; //Config::Get('module.user.profile_url');
        if ($bEmptyIfWrong && (strpos($sUrlMask, '%user_id%') === false) && (strpos($sUrlMask, '%login%') === false)) {
            // В маске обязательно должны быть либо '%user_id%', либо '%login%'
            $sUrlMask = '';
        }
        return $sUrlMask;
    }

    /**
     * Returns pattern for user's profile URL
     *
     * @return string
     */
    static public function GetUserUrlPattern() {

        $sUrlPattern = static::GetUserUrlMask();
        if ($sUrlPattern) {
            $sUrlPattern = preg_quote($sUrlPattern);
            $aReplace = array(
                '%login%' => '([\w_\-]+)',
                '%user_id%' => '(\d+)',
            );
            // Если последним символом в шаблоне идет слеш, то надо его сделать опциональным
            if (substr($sUrlPattern, -1) == '/') {
                $sUrlPattern .= '?';
            }
            $sUrlPattern = '#^' . strtr($sUrlPattern, $aReplace) . '$#i';
        }
        return $sUrlPattern;
    }

    /**
     * Compare each item of array with controller path
     *
     * @see GetControllerPath
     *
     * @param $aPaths - array of compared paths
     *
     * @return string
     */
    static public function CompareWithLocalPath($aPaths) {

        $sControllerPath = static::GetControllerPath();
        $aPaths = F::Val2Array($aPaths);
        if ($aPaths) {
            foreach($aPaths as $nKey => $sPath) {
                if ($sPath == '*') {
                    $aPaths[$nKey] = Config::Get('router.config.action_default') . '/*';
                } elseif($sPath == '/') {
                    $aPaths[$nKey] = Config::Get('router.config.action_default') . '/';
                } elseif (!in_array(substr($sPath, -1), array('/', '*'))) {
                    $aPaths[$nKey] = $sPath . '/*';
                }
            }
            return F::File_InPath($sControllerPath, $aPaths);
        }
        return null;
    }

}

// EOF