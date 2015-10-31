<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

F::IncludeFile('Action.class.php');
F::IncludeFile('ActionPlugin.class.php');

/**
 * Класс роутинга
 * Инициализирует ядро, определяет какой экшен запустить согласно URL'у и запускает его
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
    static protected $sCurrentFullUrl = null;

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

    static protected $aActionPaths = array();

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
     * Call ModuleViewer()->Display() when shutdown
     *
     * @var bool
     */
    static protected $bAutoDisplay = true;

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
        if (self::$bAutoDisplay) {
            E::ModuleViewer()->Display($this->oAction->GetTemplate());
        }
        if ($bExit) {
            exit();
        }
    }

    /**
     * Парсим URL
     * Пример: http://site.ru/action/event/param1/param2/  на выходе получим:
     *    static::$sAction='action';
     *    static::$sActionEvent='event';
     *    static::$aParams=array('param1','param2');
     *
     */
    protected function ParseUrl() {

        $sReq = $this->GetRequestUri();
        $aRequestUrl = $this->GetRequestArray($sReq);

        // Список доступных языков, которые могут быть указаны в URL
        $aLangs = array();
        // Только для мультиязычных сайтов
        if (Config::Get('lang.multilang')) {
            // Получаем список доступных языков
            $aLangs = (array)Config::Get('lang.allow');

            // Проверка языка в URL
            if ($aRequestUrl && $aLangs && Config::Get('lang.in_url')) {
                if (sizeof($aLangs) && sizeof($aRequestUrl) && in_array($aRequestUrl[0], $aLangs)) {
                    static::$sLang = array_shift($aRequestUrl);
                }
            }
        }

        static::$aRequestURI = $aRequestUrl = $this->RewriteRequest($aRequestUrl);

        if (!empty($aRequestUrl)) {
            static::$sAction = array_shift($aRequestUrl);
            static::$sActionEvent = array_shift($aRequestUrl);
        } else {
            static::$sAction = null;
            static::$sActionEvent = null;
        }
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

        $this->aCurrentUrl = parse_url(static::$sCurrentFullUrl);
        $this->aCurrentUrl['protocol'] = F::UrlScheme();
        if (!isset($this->aCurrentUrl['scheme']) && $this->aCurrentUrl['protocol']) {
            $this->aCurrentUrl['scheme'] = $this->aCurrentUrl['protocol'];
        }

        $iPathOffset = intval(C::Get('path.offset_request_url'));
        $aUrlParts = F::ParseUrl();
        $sBase = !empty($aUrlParts['base']) ? $aUrlParts['base'] : null;
        if ($sBase && $iPathOffset) {
            $aPath = explode('/', trim($aUrlParts['path'], '/'));
            $iPathOffset = min($iPathOffset, sizeof($aPath));
            for($i = 0; $i < $iPathOffset; $i++) {
                $sBase .= '/' . $aPath[$i];
            }
        }

        $this->aCurrentUrl['root'] = F::File_RootUrl();
        $this->aCurrentUrl['base'] = $sBase . '/';
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

        // * Формируем $sCurrentFullUrl ДО применения реврайтов
        if (!empty($this->aConfigRoute['domains']['forward'])) {
            // маппинг доменов
            static::$sCurrentFullUrl = strtolower(F::UrlBase() . '/' . join('/', $this->GetRequestArray($sReq)));
        } else {
            static::$sCurrentFullUrl = strtolower(F::File_RootUrl() . join('/', $this->GetRequestArray($sReq)));
        }

        $this->CheckRedirectionRules();

        return $sReq . $sLastChar;
    }

    /**
     * Checks redirection rules and redirects if there is compliance
     */
    protected function CheckRedirectionRules() {

        if (isset($this->aConfigRoute['redirect']) && is_array($this->aConfigRoute['redirect'])) {
            $sUrl = static::$sCurrentFullUrl;

            $iHttpResponse = 301;
            foreach($this->aConfigRoute['redirect'] as $sRule => $xTarget) {
                if ($xTarget) {
                    if (!is_array($xTarget)) {
                        $sTarget = $xTarget;
                        $iCode = 301;
                    } elseif (sizeof($xTarget) == 1) {
                        $sTarget = reset($xTarget);
                        $iCode = 301;
                    } else {
                        $sTarget = reset($xTarget);
                        $iCode = intval(next($xTarget));
                    }
                    if ((substr($sRule, 0, 1) == '[') && (substr($sRule, -1) == ']')) {
                        $sPattern = substr($sRule, 1, strlen($sRule) - 2);
                        if (preg_match($sPattern, $sUrl)) {
                            $sUrl = preg_replace($sPattern, $sTarget, $sUrl);
                            $iHttpResponse = $iCode;
                        }
                    } else {
                        $sPattern = F::StrMatch($sRule, $sUrl, true, $aMatches);
                        if ($sPattern && isset($aMatches[1])) {
                            $sUrl = str_replace('*', $aMatches[1], $sTarget);
                            $iHttpResponse = $iCode;
                        }
                    }
                }
            }
            if ($sUrl && ($sUrl != static::$sCurrentFullUrl)) {
                F::HttpHeader($iHttpResponse, null, $sUrl);
                exit;
            }
        }
    }

    /**
     * Возвращает массив реквеста
     *
     * @param string $sReq    Строка реквеста
     * @return array
     */
    protected function GetRequestArray($sReq) {

        $aRequestUrl = ($sReq == '' || $sReq == '/') ? array() : explode('/', trim($sReq, '/'));
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

        if (!$aRequestUrl) {
            return $aRequestUrl;
        }

        // STAGE 1: Rewrite rules for domains
        if (!empty($this->aConfigRoute['domains']['forward']) && !F::AjaxRequest()) {
            // если в запросе есть контроллер и он есть в списке страниц, то доменный маппинг не выполняется
            if (empty($aRequestUrl[0]) || empty($this->aConfigRoute['page'][$aRequestUrl[0]])) {
                $sHost = parse_url(self::$sCurrentFullUrl, PHP_URL_HOST);
                if (isset($this->aConfigRoute['domains']['forward'][$sHost])) {
                    $aRequestUrl = array_merge(explode('/', $this->aConfigRoute['domains']['forward'][$sHost]), $aRequestUrl);
                } else {
                    $aMatches = array();
                    $sPattern = F::StrMatch($this->aConfigRoute['domains']['forward_keys'], $sHost, true, $aMatches);
                    if ($sPattern) {
                        $sNewUrl = $this->aConfigRoute['domains']['forward'][$sPattern];
                        if (!empty($aMatches[1])) {
                            $sNewUrl = str_replace('*', $aMatches[1], $sNewUrl);
                            $aRequestUrl = array_merge(explode('/', $sNewUrl), $aRequestUrl);
                        }
                    }
                }
            }
        }

        // STAGE 2: Rewrite rules for REQUEST_URI
        $sRequest = implode('/', $aRequestUrl);

        $aRouterUriRules = $this->GetRouterUriRules();
        if ($aRouterUriRules) {
            foreach ($aRouterUriRules as $sPattern => $sReplace) {
                if ($sPattern[0] == '[' && substr($sPattern, -1) == ']') {
                    // regex pattern
                    $sPattern = substr($sPattern, 1, strlen($sPattern) - 2);
                    if (preg_match($sPattern, $sRequest)) {
                        $sRequest = preg_replace($sPattern, $sReplace, $sRequest);
                        break;
                    }
                } else {
                    if (substr($sPattern, -2) == '/*') {
                        $bFoundPattern = F::StrMatch(array(substr($sPattern, 0, strlen($sPattern) - 2), $sPattern), $sRequest, true);
                    } else {
                        $bFoundPattern = F::StrMatch($sPattern, $sRequest, true);
                    }
                    if ($bFoundPattern) {
                        $sRequest = $sReplace;
                        break;
                    }
                }
            }

            if (substr($sRequest, 0, 1) == '@') {
                $this->SpecialAction($sRequest);
            }
        }

        // STAGE 3: Internal rewriting (topic URLs etc.)
        $aRequestUrl = (trim($sRequest, '/') == '') ? array() : explode('/', $sRequest);
        if ($aRequestUrl) {
            $aRequestUrl = $this->RewriteInternal($aRequestUrl);
        }

        // STAGE 4: Rules for actions rewriting
        if (isset($aRequestUrl[0])) {
            $sRequestAction = $aRequestUrl[0];
            if (isset($this->aConfigRoute['rewrite'][$sRequestAction])) {
                $sRequestAction = $this->aConfigRoute['rewrite'][$sRequestAction];
                $aRequestUrl[0] = $sRequestAction;
            }
        }
        return $aRequestUrl;
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
        // * Internal rewrite rules for REQUEST_URI
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
        if (!empty($this->aConfigRoute['rewrite'])) {
            foreach ((array)$this->aConfigRoute['rewrite'] as $sRequest => $sTarget) {
                if (isset($this->aConfigRoute['page'][$sTarget])) {
                    $this->aConfigRoute['page'][$sRequest] = $this->aConfigRoute['page'][$sTarget];
                    unset($this->aConfigRoute['page'][$sTarget]);
                }
            }
        }

        if (!empty($this->aConfigRoute['domain'])) {
            $aDomains = $this->aConfigRoute['domain'];
            $this->aConfigRoute['domains']['forward'] = $aDomains;
            $this->aConfigRoute['domains']['forward_keys'] = array_keys($aDomains);
            $this->aConfigRoute['domains']['backward'] = array_flip($aDomains);
            $this->aConfigRoute['domains']['backward_keys'] = array_keys($this->aConfigRoute['domains']['backward']);
        }
    }

    /**
     * Загружает в шаблонизатор необходимые переменные
     *
     */
    protected function AssignVars() {

        E::ModuleViewer()->Assign('sAction', static::$sAction);
        E::ModuleViewer()->Assign('sEvent', static::$sActionEvent);
        E::ModuleViewer()->Assign('aParams', static::$aParams);
        E::ModuleViewer()->Assign('PATH_WEB_CURRENT', E::ModuleTools()->Urlspecialchars(static::$sCurrentFullUrl));
    }

    /**
     * Запускает на выполнение экшен
     * Может запускаться рекурсивно если в одном экшене стоит переадресация на другой
     *
     */
    public function ExecAction() {

        $this->DefineActionClass();

        // Hook before action
        E::ModuleHook()->Run('action_before');

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
        $sInitResult = $this->oAction->Init();

        if ($sInitResult === 'next') {
            $this->ExecAction();
        } else {
            // Если инициализация экшена прошла успешно,
            // то запускаем запрошенный ивент на исполнение.
            if ($sInitResult !== false) {
                $xEventResult = $this->oAction->ExecEvent();

                static::$sActionEventName = $this->oAction->GetCurrentEventName();
                $this->oAction->EventShutdown();

                if ($xEventResult === 'next') {
                    $this->ExecAction();
                }
            }
        }
        // Hook after action
        E::ModuleHook()->Run('action_after');
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
            /*
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
            */
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

        if ($sAction) {
            // Сначала ищем экшен по таблице роутинга
            if (isset($this->aConfigRoute['page'][$sAction])) {
                $sActionClass = $this->aConfigRoute['page'][$sAction];
            }
        }

        // Если в таблице нет и включено автоопределение роутинга, то ищем по путям и файлам
        if (!$sActionClass && Config::Get('router.config.autodefine')) {
            $sActionClass = Loader::SeekActionClass($sAction, $sEvent);
        }
        return $sActionClass;
    }

    /**
     * Set AutoDisplay value
     *
     * @param bool $bValue
     */
    static public function SetAutoDisplay($bValue) {

        self::$bAutoDisplay = (bool)$bValue;
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
        static::$sAction = static::getInstance()->RewritePath($sAction);
        static::$sActionEvent = $sEvent;
        if (is_array($aParams)) {
            static::$aParams = $aParams;
        }
        return 'next';
    }

    /**
     * LS-compatible
     * Возвращает текущий URL
     *
     * @return string
     */
    static public function GetPathWebCurrent() {

        return static::RealUrl(false);
    }

    /**
     * Returns real URL (or path of URL) without rewrites
     *
     * @param bool $bPathOnly
     *
     * @return null|string
     */
    static public function RealUrl($bPathOnly = false) {

        $sResult = static::$sCurrentFullUrl;
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

        return static::$sAction;
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

        if (empty(static::$aActionPaths[$sAction])) {
            $sAction = trim($sAction, '/');
            static::$aActionPaths[$sAction] = static::getInstance()->_getPath($sAction);
        }
        return static::$aActionPaths[$sAction];
    }

    /**
     * @param string $sAction
     *
     * @return string
     */
    public function _getPath($sAction) {

        // Если пользователь запросил action по умолчанию
        $sPage = (($sAction == 'default') ? $this->aConfigRoute['config']['action_default'] : $sAction);

        // Смотрим, есть ли правило rewrite
        $sPage = static::getInstance()->RestorePath($sPage);
        // Маппинг доменов
        if (!empty($this->aConfigRoute['domains']['backward'])) {
            if (isset($this->aConfigRoute['domains']['backward'][$sPage])) {
                $sResult = $this->aConfigRoute['domains']['backward'][$sPage];
                if ($sResult[1] != '/') {
                    $sResult = '//' . $sResult;
                    if (substr($sResult, -1) !== '/') {
                        $sResult .= '/';
                    }
                }
                // Кешируем
                $this->aConfigRoute['domains']['backward'][$sPage] = $sResult;
                return $sResult;
            }
            $sPattern = F::StrMatch($this->aConfigRoute['domains']['backward_keys'], $sPage, true, $aMatches);
            if ($sPattern) {
                $sResult = '//' . $this->aConfigRoute['domains']['backward'][$sPattern];
                if (!empty($aMatches[1])) {
                    $sResult = str_replace('*', $aMatches[1], $sResult);
                }
                if (substr($sResult, -1) !== '/') {
                    $sResult .= '/';
                }
                // Кешируем
                $this->aConfigRoute['domains']['backward'][$sPage] = $sResult;
                return $sResult;
            }
        }
        return rtrim(F::File_RootUrl(true), '/') . "/$sPage/";
    }

    /**
     * Returns rewrite rule for "from" or for "to" or for both
     *
     * @param string $sFrom
     * @param string $sTo
     *
     * @return array
     */
    protected function _getRewriteRule($sFrom, $sTo) {

        if ($this->aConfigRoute['rewrite']) {
            if ($sFrom) {
                if (isset($this->aConfigRoute['rewrite'][$sFrom])) {
                    if ($sTo) {
                        if ($this->aConfigRoute['rewrite'][$sFrom] == $sTo) {
                            return array($sFrom, $sTo);
                        }
                    } else {
                        return array($sFrom, $this->aConfigRoute['rewrite'][$sFrom]);
                    }
                }
            } elseif ($sTo) {
                $sFrom = array_search($sTo, $this->aConfigRoute['rewrite'], true);
                if ($sFrom) {
                    return array($sFrom , $sTo);
                }
            }
        }
        return array($sFrom, $sTo);
    }

    /**
     * Try to find rewrite rule for the path
     * On success returns right page, otherwise returns given param
     *
     * @param  string $sPath
     *
     * @return string
     */
    public function RewritePath($sPath) {

        list ($sFrom, $sTo) = $this->_getRewriteRule($sPath, null);
        return $sTo ? $sTo : $sPath;
    }

    /**
     * Стандартизирует определение внутренних ресурсов.
     *
     * Пытается по переданому экшену найти rewrite rule и
     * вернуть стандартное название ресусрса.
     *
     * @param  string $sPath
     * @return string
     */
    public function RestorePath($sPath) {

        if (strpos($sPath, '/')) {
            list($sAction, $sOthers) = explode('/', $sPath, 2);
            list ($sFrom, $sTo) = $this->_getRewriteRule(null, $sAction);
            $sResult = ($sFrom ? $sFrom . '/' . $sOthers : $sPath);
        } else {
            list ($sFrom, $sTo) = $this->_getRewriteRule(null, $sPath);
            $sResult = ($sFrom ? $sFrom : $sPath);
        }
        return $sResult;
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
            if (isset($aData['path'])) {
                $sResult = $aData['path'];
            } else {
                if (isset($aData['action'])) {
                    $sResult = '/' . $aData['action'];
                }
                if (isset($aData['event'])) {
                    $sResult .= '/' . $aData['event'];
                }
                if (isset($aData['params'])) {
                    $sResult .= '/' . $aData['params'];
                }
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
     * @param string|array $aPaths   - array of compared paths
     * @param bool         $bDefault - default value if $aPaths is empty
     *
     * @return string
     */
    static public function CompareWithLocalPath($aPaths, $bDefault = null) {

        if ($aPaths) {
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
        }
        return $bDefault;
    }

    /**
     * Check the local path by allow/disallow rules
     *
     * @param string|array|null $aAllowPaths
     * @param string|array|null $aDisallowPaths
     *
     * @return bool
     */
    static public function AllowLocalPath($aAllowPaths, $aDisallowPaths) {

        if (static::CompareWithLocalPath($aAllowPaths, true) && !static::CompareWithLocalPath($aDisallowPaths, false)) {
            return true;
        }
        return false;
    }

    /**
     * Check the current action and event by rules
     *
     * @param $aActions
     *
     * @return bool
     */
    static public function AllowAction($aActions) {

        $bResult = false;
        if ($aActions) {
            $aActions = F::Val2Array($aActions);

            $sCurrentAction = strtolower(static::GetAction());
            $sCurrentEvent = strtolower(static::GetActionEvent());
            $sCurrentEventName = strtolower(static::GetActionEventName());

            foreach ($aActions as $sAction => $aEvents) {
                // приводим к виду action=>array(events)
                if (is_int($sAction) && !is_array($aEvents)) {
                    $sAction = (string)$aEvents;
                    $aEvents = array();
                }
                if ($sAction == $sCurrentAction) {
                    if (!$aEvents) {
                        $bResult = true;
                        break;
                    }
                }
                $aEvents = (array)$aEvents;
                foreach ($aEvents as $sEventPreg) {
                    if (($sCurrentEventName && $sEventPreg == $sCurrentEventName) || $sEventPreg == $sCurrentEvent) {
                        // * Это название event`a
                        $bResult = true;
                        break 2;
                    } elseif ((substr($sEventPreg, 0, 1) == '{') && (trim($sEventPreg, '{}') == $sCurrentEventName)) {
                        // LS-compatibility
                        // * Это имя event'a (именованный евент, если его нет, то совпадает с именем метода евента в экшене)
                        $bResult = true;
                        break 2;
                    } elseif ((substr($sEventPreg, 0, 1) == '[')
                        && (substr($sEventPreg, -1) == ']')
                        && preg_match(substr($sEventPreg, 1, strlen($sEventPreg) - 2), $sCurrentEvent)) {
                        // * Это регулярное выражение
                        $bResult = true;
                        break 2;
                    }
                }
            }
        }

        return $bResult;
    }

}

// EOF