<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

// For Smarty
define('DS', '/');

F::IncludeFile(Config::Get('path.root.engine') . '/lib/external/Smarty/libs/Smarty.class.php');
F::IncludeFile(Config::Get('path.root.engine') . '/lib/external/CSSTidy-1.3/class.csstidy.php');
F::IncludeFile(Config::Get('path.root.engine') . '/lib/external/JSMin-1.1.1/jsmin.php');

/**
 * Модуль обработки шаблонов используя шаблонизатор Smarty
 *
 * @package engine.modules
 * @since 1.0
 */
class ModuleViewer extends Module {

    /** @var bool Устанавливаем признак предзагрузки (влияет на порядок шатдауна) */
    protected $bPreloaded = true;

    /**
     * Объект Smarty
     *
     * @var Smarty
     */
    protected $oSmarty;

    protected $aPresetTemplateDirs = array();

    /**
     * Коллекция(массив) виджетов
     *
     * @var array
     */
    protected $aWidgets = array();

    /**
     * Признак сортировки виджетов
     *
     * @var bool
     */
    protected $bWidgetsSorted = false;

    /**
     * Массив правил организации виджетов
     *
     * @var array
     */
    protected $aBlockRules = array();

    /**
     * Стандартные настройки вывода js, css файлов
     *
     * @var array
     */
    protected $aFilesDefault = array(
        'js' => array(),
        'css' => array()
    );

    /**
     * Параметры отображения js- и css-файлов
     *
     * @var array
     */
    protected $aFilesParams = array(
        'js' => array(),
        'css' => array()
    );

    /**
     * Наборы сливаемых js- и css-файлов
     *
     * @var array
     */
    protected $aFilesAssets = array(
        'js' => array(),
        'css' => array()
    );

    /**
     * Правила переопределение массивов js и css
     *
     * @var array
     */
    protected $aFileRules = array();

    /**
     * Список JS, которые нужно добавить в начало и в конец
     *
     * @var array
     */
    protected $aJsInclude = array(
        'append' => array(),
        'prepend' => array()
    );

    /**
     * Список CSS, которые нужно добавить в начало и в конец
     *
     * @var array
     */
    protected $aCssInclude = array(
        'append' => array(),
        'prepend' => array()
    );

    /**
     * Каталог для кешировния js,css файлов
     *
     * @var string
     */
    protected $sCacheDir = '';

    /**
     * Объект CSSTidy для компрессии css-файлов
     *
     * @var csstidy
     */
    protected $oCssCompressor = null;

    /**
     * Заголовок HTML страницы
     *
     * @var string
     */
    protected $sHtmlTitle;

    /**
     * SEO ключевые слова страницы
     *
     * @var string
     */
    protected $sHtmlKeywords;

    /**
     * SEO описание страницы
     *
     * @var string
     */
    protected $sHtmlDescription;

    /**
     * Разделитель заголовка HTML страницы
     *
     * @var string
     */
    protected $sHtmlTitleSeparation = ' / ';

    /**
     * Альтернативный адрес страницы по RSS
     *
     * @var array
     */
    protected $aHtmlRssAlternate = null;

    /**
     * Указание поисковику основного URL страницы, для борьбы с дублями
     *
     * @var string
     */
    protected $sHtmlCanonical;

    /**
     * Html код для подключения js,css
     *
     * @var array
     */
    protected $aHtmlHeadFiles = array(
        'js' => '',
        'css' => ''
    );

    /**
     * Html-теги, добавляемые в HEAD страницы
     *
     * @var array
     */
    protected $aHtmlHeadTags = array();

    /**
     * Переменные для отдачи при ajax запросе
     *
     * @var array
     */
    protected $aVarsAjax = array();

    /**
     * Определяет тип ответа при ajax запросе
     *
     * @var string
     */
    protected $sResponseAjax = null;

    /**
     * Отправляет специфичный для ответа header
     *
     * @var bool
     */
    protected $bResponseSpecificHeader = true;

    /**
     * Список меню для рендеринга
     *
     * @var array
     */
    protected $aMenu = array();

    /**
     * Скомпилированные меню
     *
     * @var array
     */
    protected $aMenuFetch = array();

    /**
     * Список ресурсов, который формируется в процессе обработки подключаемых файлов
     *
     * @var array
     */
    protected $aAssets = array();

    /**
     * Локальный вьюер
     *
     * @var bool
     */
    protected $bLocal = false;

    /**
     * Константа для компиляции LESS-файлов
     */
    const ALTO_SRC = '___ALTO_SRC___';

    static protected $_renderCount = 0;
    static protected $_renderTime = 0;
    static protected $_renderStart = 0;
    static protected $_preprocessTime = 0;

    static public function GetRenderCount() {
        return self::$_renderCount;
    }

    static public function GetRenderTime() {
        return self::$_renderTime + (self::$_renderStart ? microtime(true) - self::$_renderStart : 0);
    }

    static public function GetPreprocessingTime() {
        return self::$_preprocessTime + self::GetRenderTime();
    }

    static public function GetTotalTime() {
        return self::GetPreprocessingTime() + self::GetRenderTime();
    }

    /**
     * Инициализация модуля
     *
     */
    public function Init($bLocal = false) {
        $this->Hook_Run('viewer_init_start', compact('bLocal'));

        $this->bLocal = (bool)$bLocal;

        if (!$bLocal) {
            // * Load skin config
            $aConfig = Config::Get('skin.' . Config::Get('view.skin') . '.config');
            if (F::File_Exists($sFile = Config::Get('path.smarty.template') . '/settings/config/config.php')) {
                $aConfig = F::Array_Merge(F::IncludeFile($sFile, false, true), $aConfig);
            }
            if ($aConfig) {
                Config::Load($aConfig, false);
            }
            // * Load skin widgets
            if (F::File_Exists($sFile = Config::Get('path.smarty.template') . '/settings/config/widgets.php')) {
                Config::LoadFromFile($sFile, false);
            }
        }
        // * Заголовок HTML страницы
        $this->sHtmlTitle = Config::Get('view.name');

        // * SEO ключевые слова страницы
        $this->sHtmlKeywords = Config::Get('view.keywords');

        // * SEO описание страницы
        $this->sHtmlDescription = Config::Get('view.description');

        // * Создаём объект Smarty и устанавливаем необходимые параметры
        $this->oSmarty = $this->CreateSmartyObject();
        $this->oSmarty->compile_check = Config::Get('smarty.compile_check');
        $this->oSmarty->force_compile = Config::Get('smarty.force_compile');

        // * Подавляем NOTICE ошибки - в этом вся прелесть смарти )
        $this->oSmarty->error_reporting = error_reporting() & ~E_NOTICE;

        // * Папки расположения шаблонов по умолчанию
        $this->oSmarty->setTemplateDir(F::File_NormPath(array_merge(
                (array)Config::Get('path.smarty.template'),
                array(Config::Get('path.root.dir') . '/plugins/'))
        ));

        // * Для каждого скина устанавливаем свою директорию компиляции шаблонов
        $sCompilePath = Config::Get('path.smarty.compiled');
        F::File_CheckDir($sCompilePath);
        $this->oSmarty->setCompileDir($sCompilePath);
        $this->oSmarty->setCacheDir(Config::Get('path.smarty.cache'));
        $this->oSmarty->addPluginsDir(array(Config::Get('path.smarty.plug'), 'plugins'));
        $this->oSmarty->default_template_handler_func = array($this, 'SmartyDefaultTemplateHandler');

        // * Параметры кеширования, если заданы
        if (Config::Get('smarty.cache_lifetime')) {
            $this->oSmarty->caching = Smarty::CACHING_LIFETIME_SAVED;
            $this->oSmarty->cache_lifetime = F::ToSeconds(Config::Get('smarty.cache_lifetime'));
        }

        // * Загружаем локализованные тексты
        $this->Assign('aLang', $this->Lang_GetLangMsg());
        $this->Assign('oLang', $this->Lang_Dictionary());

        // * Загружаем переменные из конфига
        if (($aVars = Config::Get('view.assign')) && is_array($aVars)) {
            foreach ($aVars as $sKey => $sVal) {
                $this->Assign($sKey, $sVal);
            }
        }

        // * Пустой вызов только для того, чтоб модуль Message инициализиовался, если еще не
        $this->Message_IsInit();

        // * Получаем настройки JS-, CSS-файлов
        $this->InitFileParams();
        $this->sCacheDir = Config::Get('path.runtime.dir');
    }

    /**
     * Возвращает локальную копию модуля
     *
     * @return ModuleViewer
     */
    public function GetLocalViewer() {
        $sClass = $this->Plugin_GetDelegate('module', __CLASS__);

        $oViewerLocal = new $sClass(Engine::getInstance());
        $oViewerLocal->Init(true);
        $oViewerLocal->VarAssign();
        $oViewerLocal->Assign('aLang', $this->Lang_GetLangMsg());
        return $oViewerLocal;
    }

    /**
     * Возвращает версию Smarty
     *
     * @return string|null
     */
    public function GetSmartyVersion() {
        $sSmartyVersion = null;
        if (property_exists($this->oSmarty, '_version')) {
            $sSmartyVersion = $this->oSmarty->_version;
        } elseif (defined('Smarty::SMARTY_VERSION')) {
            $sSmartyVersion = Smarty::SMARTY_VERSION;
        }
        return $sSmartyVersion;
    }

    /**
     * Возвращает путь к общей asset-папке
     *
     * @return string
     */
    public function GetAssetDir() {
        return F::File_NormPath(Config::Get('path.runtime.dir') . 'assets/');
    }

    public function GetAssetUrl() {
        return Config::Get('path.runtime.url') . 'assets/';
    }

    /**
     * Преобразует путь к файлу в путь к asset-ресурсу
     *
     * @param   string $sFile
     * @return  string
     */
    public function AssetFileDir($sFile) {
        return F::File_NormPath($this->GetAssetDir() . $this->Hash(dirname($sFile)) . '/' . basename($sFile));
    }

    /**
     * Преобразует URL к файлу в URL к asset-ресурсу
     *
     * @param   string $sFile
     * @return  string
     */
    public function AssetFileUrl($sFile) {
        return F::File_NormPath($this->GetAssetUrl() . $this->Hash(dirname($sFile)) . '/' . basename($sFile));
    }

    /**
     * Добавить файл к списку asset-ресурсов, чтобы потом скопировать его в общую asset-папку
     *
     * @param   string $sFile
     */
    public function AddAssetFile($sFile) {
        if (!isset($this->aAssets[$sFile])) {
            $this->aAssets[$sFile] = $this->AssetFileDir($sFile);
        }
    }

    /**
     * Копировать файлы из списка asset-ресурсов в общую папку asset-ресурсов
     */
    protected function CopyAssetFiles() {
        foreach ($this->aAssets as $sSource => $sTarget) {
            F::File_Copy($sSource, $sTarget);
        }
    }

    /**
     * Выполняет загрузку необходимых (возможно даже системных :)) переменных в шаблонизатор
     *
     */
    public function VarAssign() {
        /**
         * Загружаем весь $_REQUEST, предварительно обработав его функцией func_htmlspecialchars()
         */
        $aRequest = $_REQUEST;
        func_htmlspecialchars($aRequest);
        $this->Assign('_aRequest', $aRequest);

        // * Параметры стандартной сессии
        // TODO: Убрать! Не должно этого быть на страницах сайта
        $this->Assign('_sPhpSessionName', session_name());
        $this->Assign('_sPhpSessionId', session_id());

        // * Short Engine aliases
        $this->Assign('LS', LS::getInstance());

        // * Загружаем объект доступа к конфигурации
        // * Перенесено в PluginLs_Viewer
        // TODO: Пока здесь, но надо убирать - незачем таскать в шаблоны объект, если можно в них к стат.классу напрямую обращаться
        $this->Assign('oConfig', Config::getInstance());

        // * Загружаем роутинг с учетом правил rewrite
        $aRouter = array();
        $aPages = Config::Get('router.page');

        if (!$aPages || !is_array($aPages)) throw new Exception('Router rules is underfined.');
        foreach ($aPages as $sPage => $aAction) {
            $aRouter[$sPage] = Router::GetPath($sPage);
        }
        $this->Assign('aRouter', $aRouter);

        // * Загружаем виджеты
        $this->Assign('aWidgets', $this->GetWidgets());

        // * Загружаем HTML заголовки
        $this->Assign('sHtmlTitle', htmlspecialchars($this->sHtmlTitle));
        $this->Assign('sHtmlKeywords', htmlspecialchars($this->sHtmlKeywords));
        $this->Assign('sHtmlDescription', htmlspecialchars($this->sHtmlDescription));
        $this->Assign('aHtmlHeadFiles', $this->aHtmlHeadFiles);
        $this->Assign('aHtmlRssAlternate', $this->aHtmlRssAlternate);
        $this->Assign('sHtmlCanonical', $this->sHtmlCanonical);
        $this->Assign('aHtmlHeadTags', $this->aHtmlHeadTags);

        // * Загружаем список активных плагинов
        $aPlugins = $this->oEngine->GetPlugins();
        $this->Assign('aPluginActive', array_fill_keys(array_keys($aPlugins), true));

        // * Загружаем пути до шаблонов плагинов
        $aTemplateWebPathPlugin = array();
        $aTemplatePathPlugin = array();
        foreach ($aPlugins as $k => $oPlugin) {
            $aTemplateWebPathPlugin[$k] = Plugin::GetTemplateWebPath(get_class($oPlugin));
            $aTemplatePathPlugin[$k] = Plugin::GetTemplatePath(get_class($oPlugin));
        }
        $this->Assign('aTemplateWebPathPlugin', $aTemplateWebPathPlugin);
        $this->Assign('aTemplatePathPlugin', $aTemplatePathPlugin);

        $sSkinTheme = Config::Get('view.theme');
        if (!$sSkinTheme) {
            $sSkinTheme = 'default';
        }
        // Проверка существования темы
        if ($this->CheckTheme($sSkinTheme)) {
            $this->oSmarty->compile_id = $sSkinTheme;
        }
        $this->Assign('sSkinTheme', $sSkinTheme);
    }

    /**
     * Загружаем содержимое menu-контейнеров
     */
    protected function MenuVarAssign() {
        $this->Assign('aMenuFetch', $this->aMenuFetch);
        $this->Assign('aMenuContainers', array_keys($this->aMenu));
    }

    /**
     * Выводит на экран (в браузер) обработанный шаблон
     *
     * @param string $sTemplate     - Шаблон для вывода
     */
    public function Display($sTemplate) {
        // Проверка существования папки с текущим скином
        $this->CheckSkin();

        if ($this->sResponseAjax) {
            $this->DisplayAjax($this->sResponseAjax);
        }
        /**
         * Если шаблон найден то выводим, иначе - ошибка
         * Но предварительно проверяем наличие делегата
         */
        if ($sTemplate) {
            $sTemplate = $this->Plugin_GetDelegate('template', $sTemplate);
            if ($this->TemplateExists($sTemplate, true)) {
                // Установка нового secret key непосредственно перед рендерингом
                $this->Security_SetSessionKey();

                self::$_renderCount++;
                self::$_renderStart = microtime(true);
                $this->oSmarty->display($sTemplate);
                self::$_renderTime += (microtime(true) - self::$_renderStart);
                self::$_renderStart = 0;
            }
        }
    }

    /**
     * Возвращает отрендеренный шаблон
     *
     * @param   string $sTemplate    - Шаблон для рендеринга
     * @param   array  $aOptions     - Опции рендеринга
     *
     * @return  string
     */
    public function Fetch($sTemplate, $aOptions = array()) {

        // * Проверяем наличие делегата
        $sTemplate = $this->Plugin_GetDelegate('template', $sTemplate);
        if ($this->TemplateExists($sTemplate, true)) {
            // Если задаются локальные параметры кеширования, то сохраняем общие
            if (isset($aOptions['cache'])) {
                $nOldCaching = $this->oSmarty->caching;
                $nOldCacheLifetime = $this->oSmarty->cache_lifetime;

                $this->oSmarty->caching = Smarty::CACHING_LIFETIME_SAVED;
                if ($aOptions['cache'] === false) {
                    // Отключаем кеширование
                    $this->oSmarty->cache_lifetime = 0;
                } elseif (isset($aOptions['cache']['time'])) {
                    if ($aOptions['cache']['time'] == -1) {
                        // Задаем бессрочное кеширование
                        $this->oSmarty->cache_lifetime = -1;
                    } elseif ($aOptions['cache']['time']) {
                        // Задаем время кеширования
                        $this->oSmarty->cache_lifetime = F::ToSeconds($aOptions['cache']['time']);
                    } else {
                        // Отключаем кеширование
                        $this->oSmarty->cache_lifetime = 0;
                    }
                }
            }

            self::$_renderCount++;
            self::$_renderStart = microtime(true);

            $sContent = $this->oSmarty->fetch($sTemplate);

            self::$_renderTime += (microtime(true) - self::$_renderStart);
            self::$_renderStart = 0;

            if (isset($aOptions['cache'])) {
                $this->oSmarty->caching = $nOldCaching;
                $this->oSmarty->cache_lifetime = $nOldCacheLifetime;
            }

            return $sContent;
        }
    }

    /**
     * Возвращает отрендеренный шаблон виджета
     *
     * @param string $sTemplate    Шаблон для рендеринга
     * @return string
     */
    public function FetchWidget($sTemplate) {
        // * Проверяем наличие делегата
        $sDelegateTemplate = $this->Plugin_GetDelegate('template', $sTemplate);
        if ($sDelegateTemplate == $sTemplate && !$this->TemplateExists($sTemplate)) {
            $sWidgetTemplate = 'widgets/widget.' . $sTemplate;
            $sWidgetTemplate = $this->Plugin_GetDelegate('template', $sWidgetTemplate);
            if ($this->TemplateExists($sWidgetTemplate)) {
                return $this->Fetch($sWidgetTemplate);
            }

            // * LS-compatible *//
            $sWidgetTemplate = 'blocks/block.' . $sTemplate;
            $sWidgetTemplate = $this->Plugin_GetDelegate('template', $sWidgetTemplate);
            if ($this->TemplateExists($sWidgetTemplate)) {
                return $this->Fetch($sWidgetTemplate);
            }
        }
        return $this->Fetch($sWidgetTemplate);
    }

    /**
     * Ответ на ajax запрос
     *
     * @param string $sType Варианты: json, jsonIframe, jsonp
     */
    public function DisplayAjax($sType = 'json') {
        /**
         * Загружаем статус ответа и сообщение
         */
        $bStateError = false;
        $sMsgTitle = '';
        $sMsg = '';
        $aMsgError = $this->Message_GetError();
        $aMsgNotice = $this->Message_GetNotice();
        if (count($aMsgError) > 0) {
            $bStateError = true;
            $sMsgTitle = $aMsgError[0]['title'];
            $sMsg = $aMsgError[0]['msg'];
        } elseif (count($aMsgNotice) > 0) {
            $sMsgTitle = $aMsgNotice[0]['title'];
            $sMsg = $aMsgNotice[0]['msg'];
        }
        $this->AssignAjax('sMsgTitle', $sMsgTitle);
        $this->AssignAjax('sMsg', $sMsg);
        $this->AssignAjax('bStateError', $bStateError);
        if ($sType == 'json') {
            if ($this->bResponseSpecificHeader && !headers_sent()) {
                header('Content-type: application/json');
            }
            echo F::jsonEncode($this->aVarsAjax);
        } elseif ($sType == 'jsonIframe') {
            // Оборачивает json в тег <textarea>, это не дает браузеру выполнить HTML, который вернул iframe
            if ($this->bResponseSpecificHeader && !headers_sent()) {
                header('Content-type: application/json');
            }
            /**
             * Избавляемся от бага, когда в возвращаемом тексте есть &quot;
             */
            echo '<textarea>' . htmlspecialchars(F::jsonEncode($this->aVarsAjax)) . '</textarea>';
        } elseif ($sType == 'jsonp') {
            if ($this->bResponseSpecificHeader && !headers_sent()) {
                header('Content-type: application/json');
            }
            echo getRequest('jsonpCallback', 'callback') . '(' . F::jsonEncode($this->aVarsAjax) . ');';
        }
        exit();
    }

    /**
     * Создает и возвращает объект Smarty
     *
     * @return Smarty
     */
    public function CreateSmartyObject() {
        return new Smarty();
    }

    /**
     * Возвращает объект Smarty
     *
     * @return Smarty
     */
    public function GetSmartyObject() {
        return $this->oSmarty;
    }

    /**
     * Возвращает скин
     *
     * @param   bool    $bSiteSkin - если задано, то возвращает скин, установленный для сайта (игнорирует скин экшена)
     *
     * @return  string
     */
    public function GetSkin($bSiteSkin = true) {
        if ($bSiteSkin)
            return Config::Get('view.skin', Config::DEFAULT_CONFIG_INSTANCE);
        else
            return Config::Get('view.skin');
    }

    /**
     * Возвращает путь к папке скина
     *
     * @param   bool    $bSiteSkin - если задано, то возвращает скин, установленный для сайта (игнорирует скин экшена)
     *
     * @return  string
     */
    public function GetTemplateDir($bSiteSkin = true) {
        if ($bSiteSkin)
            return F::File_NormPath(Config::Get('path.skins.dir') . '/' . $this->GetSkin(true));
        else
            return Config::Get('path.smarty.template');
    }

    /**
     * Возвращает тип отдачи контекта
     *
     * @return string
     */
    public function GetResponseAjax() {
        return $this->sResponseAjax;
    }

    /**
     * Устанавливает тип отдачи при ajax запросе, если null то выполняется обычный вывод шаблона в браузер
     *
     * @param string $sResponseAjax    Тип ответа
     * @param bool $bResponseSpecificHeader    Установливать специфичные тиру заголовки через header()
     * @param bool $bValidate    Производить или нет валидацию формы через {@link Security::ValidateSendForm}
     */
    public function SetResponseAjax($sResponseAjax = 'json', $bResponseSpecificHeader = true, $bValidate = true) {
        // Для возможности кросс-доменных запросов
        if ($sResponseAjax != 'jsonp' && $bValidate) {
            $this->Security_ValidateSendForm();
        }
        $this->sResponseAjax = $sResponseAjax;
        $this->bResponseSpecificHeader = $bResponseSpecificHeader;
    }

    /**
     * Загружает переменную в шаблон
     *
     * @param string $sName    Имя переменной в шаблоне
     * @param mixed $value    Значение переменной
     */
    public function Assign($sName, $value) {
        $this->oSmarty->assign($sName, $value);
    }

    /**
     * Загружаем переменную в ajax ответ
     *
     * @param string $sName    Имя переменной в шаблоне
     * @param mixed $value    Значение переменной
     */
    public function AssignAjax($sName, $value) {
        $this->aVarsAjax[$sName] = $value;
    }

    /**
     * Проверяет существование шаблона
     *
     * @param   string $sTemplate      - Шаблон
     * @param   bool $bException     - Нужно ли генерить ошибку, если шаблон не найден
     * @return  bool
     */
    public function TemplateExists($sTemplate, $bException = false) {
        $bResult = $this->oSmarty->templateExists($sTemplate);
        if (!$bResult && $bException) {
            $sMessage = 'Can not find the template "' . $sTemplate . '"';
            // записываем доп. информацию - пути к шаблонам Smarty
            $sErrorInfo = 'Template Dirs: ' . implode('; ', $this->oSmarty->getTemplateDir());
            return $this->_error($sMessage, $sErrorInfo);
        }
        return $bResult ? $sTemplate : $bResult;
    }

    /**
     * Проверяет существование скина/темы
     *
     * @param   string $sSkin          - Скин (или скин/тема)
     * @param   bool $bException     - Нужно ли генерить ошибку, если скин не найден
     * @return  bool
     */
    public function SkinExists($sSkin, $bException = false) {
        if (strpos($sSkin, '/') !== false) {
            // Разделяем сам скин и тему
            list($sSkin, $sTheme) = explode('/', $sSkin, 2);
            if (!$sSkin) $sSkin = Config::Get('view.skin');
        } else {
            $sTheme = null;
        }
        $sCheckDir = Config::Get('path.smarty.template');
        // Если проверяется не текущий скин, то корректируем путь
        if ($sSkin != Config::Get('view.skin')) {
            $sCheckDir = str_replace('/' . Config::Get('view.skin') . '/', '/' . $sSkin . '/', $sCheckDir);
        }
        // Проверяем только скин или тему скина
        if ($sTheme) {
            $sCheckDir .= 'themes/' . $sTheme . '/';
        }
        $bResult = is_dir($sCheckDir);
        if (!$bResult && $bException) {
            if ($sTheme) {
                $sMessage = 'Can not find the theme "' . $sTheme . '" of skin "' . $sSkin . '"';
                // записываем доп. информацию - пути к шаблонам Smarty
                $sErrorInfo = 'Theme dir: ' . $sCheckDir;
            } else {
                $sMessage = 'Can not find the skin "' . $sSkin . '"';
                // записываем доп. информацию - пути к шаблонам Smarty
                $sErrorInfo = 'Skin dir: ' . $sCheckDir;
            }
            return $this->_error($sMessage, $sErrorInfo);
        }
        return $bResult;
    }

    /**
     * Проверяет существование папки текущего скина
     *
     * @return  bool
     */
    public function CheckSkin() {
        if (!$this->SkinExists(Config::Get('view.skin'), true)) {
            die('Please check skin folder');
        }
    }

    /**
     * Проверяет существование папки конкретной темы текущего скина
     *
     * @param   string $sTheme
     * @return  bool
     */
    public function CheckTheme($sTheme) {
        return $this->SkinExists(Config::Get('view.skin') . '/' . $sTheme, false);
    }

    /**
     * Генерация ошибки "Шаблон не найден"
     *
     * @param   string $sMessage       - сообщение об ошибке
     * @param   string|null $sErrorInfo     - доп. информация для записи в лог
     *
     * @throws  Exception
     */
    protected function _error($sMessage, $sErrorInfo = null) {
        $oException = new Exception($sMessage);
        $oException->sAdditionalInfo = $sErrorInfo;
        throw $oException;
    }

    /**
     * Добавляет виджет для отображения
     *
     * @param   string $sGroup     Группа виджетов
     * @param   string $sName      Название виджета - Можно передать название виджета, тогда для обработки данных
     *          будет вызван обработчик из /classes/widgets/, либо передать путь до шаблона, тогда будет выполнено
     *          обычное подключение шаблона
     * @param   array $aParams     Параметры виджета, которые будут переданы обработчику
     * @param   int $iPriority    Приоритет, согласно которому сортируются виджеты
     * @return  bool
     */
    public function AddWidget($sGroup, $sName, $aParams = array(), $iPriority = 0) {
        /**
         * Если не указана директория шаблона, но указана приналежность к плагину,
         * то "вычисляем" правильную директорию
         */
        if (!isset($aParams['dir']) && isset($aParams['plugin'])) {
            $aParams['dir'] = Plugin::GetTemplatePath($aParams['plugin']);
        }

        $aWidgetData = array(
            'name' => $sName,
            'params' => $aParams,
            'priority' => $iPriority,
        );

        // Создавать виджет нужно до определения его типа, чтоб ID виджета сформировался правильно
        $oWidget = Engine::GetEntity('Widget', $aWidgetData);

        $sDir = isset($aParams['dir']) ? $aParams['dir'] : null;
        $sPlugin = isset($aParams['plugin']) ? $aParams['plugin'] : null;
        // Если смогли определить тип виджета то добавляем его
        $sType = $this->DefineWidgetType($sName, $sDir, $sPlugin);
        if ($sType == 'undefined') {
            return false;
        }

        $oWidget->setType($sType);
        if ($sType == 'template') {
            // в $sName возвращается найденный шаблон
            $oWidget->setTemplate($sName);
            if ($sName != $aWidgetData['name']) {
                $oWidget->setName($sName);
            }
        }

        // Добавляем виджет в группу
        $this->aWidgets[$sGroup][] = $oWidget;

        // Сбрасываем флаг сортировки
        $this->bWidgetsSorted = false;
        return true;
    }

    /**
     * Добавляет список виджетов
     *
     * @param   string $sGroup     Группа виджетов
     * @param   array $aWidgets   Список добавляемых виджетов
     * <pre>
     * $this->Viewer_AddWidgets('right', array('tags', array('widget'=>'stream', 'priority'=>100)));
     * </pre>
     * @param   bool $ClearWidgets    Очищать или нет список виджетов, добавленных до этого, в данной группе
     */
    public function AddWidgets($sGroup, $aWidgets, $ClearWidgets = true) {
        /**
         * Удаляем ранее добавленые виджеты
         */
        if ($ClearWidgets) {
            $this->ClearWidgets($sGroup);
        }
        foreach ($aWidgets as $sWidget) {
            if (is_array($sWidget)) {
                $this->AddWidget(
                    $sGroup,
                    $sWidget['widget'],
                    isset($sWidget['params']) ? $sWidget['params'] : array(),
                    isset($sWidget['priority']) ? $sWidget['priority'] : 5
                );
            } else {
                $this->AddWidget($sGroup, $sWidget);
            }
        }
    }

    /**
     * Удаляет виджеты заданной группы
     *
     * @param   string $sGroup
     */
    public function ClearWidgets($sGroup) {
        $this->aWidgets[$sGroup] = array();
    }

    /**
     * Удаляет виджеты из всех групп
     *
     */
    public function ClearAllWidgets() {
        foreach ($this->aWidgets as $sGroup => $aWidget) {
            $this->aWidgets[$sGroup] = array();
        }
    }

    /**
     * Возвращает список виджетов
     *
     * @param bool $bSort    Выполнять или нет сортировку виджетов
     * @return array
     */
    public function GetWidgets($bSort = true) {
        if ($bSort && !$this->bWidgetsSorted) {
            $this->SortWidgets();
        }
        return $this->aWidgets;
    }

    /**
     * Определяет тип виджета
     *
     * @param   string $sName          Название виджета
     * @param   string|null $sDir       Путь до шаблона виджета, обычно определяется автоматически для плагинов,
     *                                  если передать параметр 'plugin'=>'myplugin'
     * @param   string|null $sPlugin    Имя плагина виджета, берется из параметра 'plugin'=>'myplugin'
     * @return  string ('exec', 'block', 'template', 'undefined')
     */
    protected function DefineWidgetType(&$sName, $sDir = null, $sPlugin = null) {
        // Добавляем проверку на рсширение, чтобы не делать лишних телодвижений
        $bTpl = (substr($sName, -4) == '.tpl');
        if (!$bTpl) {
            if ($this->Widget_FileClassExists($sName, $sPlugin)) {
                // Если найден файл класса виджета, то это исполняемый виджет
                return 'exec';
            }
            if ($sLsBlockName = $this->TemplateExists(is_null($sDir) ? 'blocks/block.' . $sName . '.tpl' : rtrim($sDir, '/') . '/blocks/block.' . $sName . '.tpl')) {
                // Если найден шаблон вида block.name.tpl то считаем что тип 'block'
                // * LS-compatible * //
                //$sName = $sLsBlockName;
                return 'block';
            }
        }
        if (strpos($sName, 'block.') && ($sTplName = $this->TemplateExists(is_null($sDir) ? $sName : rtrim($sDir, '/') . '/' . ltrim($sName, '/')))) {
            // * LS-compatible * //
            $sName = $sTplName;
            return 'template';
        } elseif ($sTplName = $this->TemplateExists(is_null($sDir) ? $sName : rtrim($sDir, '/') . '/' . ltrim($sName, '/'))) {
            // Если найден шаблон, то считаем, что это шаблонный виджет
            $sName = $sTplName;
            return 'template';
        } elseif ($sTplName = $this->TemplateExists(is_null($sDir) ? 'widgets/widget.' . $sName : rtrim($sDir, '/') . '/widgets/widget.' . $sName)) {
            // Если найден шаблон вида widget.name.tpl то считаем что тип 'template'
            $sName = $sTplName;
            return 'template';
        }

        // Считаем что тип не определен
        /**
         * TODO: Надо выводить что-то ошибочное на странице в месте этого виджета и писать ошибку в лог
         */
        //throw new Exception('Can not define widget type: '.$sName);
        return 'undefined';
    }


    /**
     * Вспомагательная функция для сортировки виджетов:
     *  - первыми идут с приоритетом 'top'
     *  - потом те, у кого выше приоритет
     *  - потом те, которые были раньше добавлены
     *
     * @param  array $a
     * @param  array $b
     * @return int
     */
    protected function _SortWidgetsCompare($a, $b) {
        if ($a->getPriority() == $b->getPriority()) {
            return $a->getOrder() - $b->getOrder();
        } elseif ($a->isTop()) {
            return 1;
        } elseif ($b->isTop()) {
            return -1;
        }
        if ($a->getPriority() < $b->getPriority()) {
            return -1;
        }
        return 1;
    }

    /**
     * Сортируем виджеты по приоритетам
     *
     */
    protected function SortWidgets() {
        if ($this->aWidgets)
            foreach ($this->aWidgets as $sGroup => $aWidgets) {
                if (sizeof($aWidgets)) {
                    uasort($aWidgets, array($this, '_SortWidgetsCompare'));
                    $this->aWidgets[$sGroup] = array_reverse($aWidgets);
                }
            }
        $this->bWidgetsSorted = true;
    }

    protected function MakeWidgetsLists() {
        $aWidgets = $this->Widget_GetWidgets();
        if ($aWidgets) {
            foreach ($aWidgets as $oWidget) {
                if ($sGroup = $oWidget->getGroup()) {
                    // Свойство "order" потребуется для сортировки по поядку добавления, если не задан приоритет
                    if (!$oWidget->getOrder()) {
                        $oWidget->setOrder(isset($this->aWidgets[$sGroup]) ? sizeof($this->aWidgets[$sGroup]) : 0);
                    }
                    if (is_null($oWidget->getType())) {
                        $sName = $oWidget->getName();
                        $sType = $this->DefineWidgetType($sName, $oWidget->getDir(), $oWidget->getPluginId());

                        $oWidget->setType($sType);
                        if ($sType == 'template') {
                            $oWidget->setName($sName);
                            /*
                            $aParams = $oWidget->getParams();
                            if (!isset($aParams['dir'])) {
                                $aParams['dir'] = $oWidget->getDir();
                                $oWidget->setParams($aParams);
                            }
                            */
                        }
                        /* LS-compatible */
                        if (!$oWidget->getParam('plugin') && $oWidget->getPluginId()) {
                            $oWidget->setParam('plugin', $oWidget->getPluginId());
                        }
                    }
                }
                // Список всех виджетов, в т.ч. и без группы
                $this->aWidgets['_all_'][$oWidget->GetId()] = $oWidget;
                // Список виджетов с разбивкой по круппам (чтоб не дублировать, сохраняем ссылку на элемент в общем списке)
                $this->aWidgets[$sGroup][$oWidget->GetId()] = & $this->aWidgets['_all_'][$oWidget->GetId()];
            }
            $this->SortWidgets();
        }
    }

    /**
     * Инициализирует параметры вывода js- и css- файлов
     */
    protected function InitFileParams() {
        foreach (array('js', 'css') as $sType) {
            /**
             * Проверяем наличие списка файлов данного типа
             */
            $aFiles = Config::Get('head.default.' . $sType);
            if (is_array($aFiles) && count($aFiles)) {
                foreach ($aFiles as $sFile => $aParams) {
                    if (!is_array($aParams)) {
                        $sFile = $aParams;
                        $aParams = array();
                    }
                    if ($sType == 'js') {
                        $this->AppendScript($sFile, $aParams);
                    } elseif ($sType == 'css') {
                        $this->AppendStyle($sFile, $aParams);
                    }
                }
            }
        }
    }

    /**
     * Создает css-компрессор и инициализирует его конфигурацию
     *
     * @return bool
     */
    protected function InitCssCompressor() {
        /**
         * Получаем параметры из конфигурации
         */
        $aParams = Config::Get('compress.css');
        $this->oCssCompressor = ($aParams['use']) ? new csstidy() : null;
        /**
         * Если компрессор не создан, завершаем работу инициализатора
         */
        if (!$this->oCssCompressor) return false;
        /**
         * Устанавливаем параметры
         */
        $this->oCssCompressor->set_cfg('case_properties', $aParams['case_properties']);
        $this->oCssCompressor->set_cfg('merge_selectors', $aParams['merge_selectors']);
        $this->oCssCompressor->set_cfg('optimise_shorthands', $aParams['optimise_shorthands']);
        $this->oCssCompressor->set_cfg('remove_last_;', $aParams['remove_last_;']);
        $this->oCssCompressor->set_cfg('css_level', $aParams['css_level']);
        $this->oCssCompressor->load_template($aParams['template']);

        return true;
    }

    /**
     * Проверка набора параметров подключаемого js/css-файла
     *
     * @param   string $sType
     * @param   string $sFile
     * @param   array $aParams
     * @return  array
     */
    protected function CheckFileParams($sType, $sFile, $aParams) {
        // Если слияние отключено, то каждый набор - это отдельный файл
        if (Config::Get('compress.' . $sType . '.merge')) {
            if (isset($aFileParams['asset'])) $sAsset = $aFileParams['asset'];
            elseif (isset($aFileParams['block'])) $sAsset = $aFileParams['block']; // LS compatible
            else $sAsset = 'default';
        } else {
            $sAsset = $sFile;
        }
        return array_merge(array('type' => $sType, 'name' => $sFile, 'merge' => true, 'asset' => $sAsset), $aParams);
    }

    /**
     * Добавляет js-файл в конец списка
     *
     * @param   string $sFile      js-файл
     * @param   array $aParams    - Параметры, например, можно указать параметр 'name'=>'jquery.plugin.foo'
     *                              для исключения повторного добавления файла с таким именем
     * @param   bool $bReplace   - заменять ли файл с таким же именем, если он уже есть
     * @return bool
     */
    public function AppendScript($sFile, $aParams = array(), $bReplace = false) {
        $aParams = $this->CheckFileParams('js', $sFile, $aParams);
        if ($this->ExistsHeadFileByName('js', $aParams)) {
            return true;
        }
        $this->aJsInclude['append'][] = $sFile;
        $this->aFilesParams['js'][$sFile] = $aParams;
        return true;
    }

    /**
     * Добавляет js-файл в начало списка
     *
     * @param   string $sFile    js-файл
     * @param   array $aParams  - Параметры, например, можно указать параметр 'name'=>'jquery.plugin.foo'
     *                              для исключения повторного добавления файла с таким именем
     * @param   bool $bReplace   - заменять ли файл с таким же именем, если он уже есть
     * @return  bool
     */
    public function PrependScript($sFile, $aParams = array(), $bReplace = false) {
        $aParams = $this->CheckFileParams('js', $sFile, $aParams);
        if ($this->ExistsHeadFileByName('js', $aParams)) {
            return true;
        }
        // Prepend js-file to the beginning of an array
        array_unshift($this->aJsInclude['prepend'], $sFile);
        $this->aFilesParams['js'][$sFile] = $aParams;
        return true;
    }

    /**
     * Добавляет css-файл в конец списка
     *
     * @param   string $sFile      css-файл стилей
     * @param   array $aParams    - Параметры, например, можно указать параметр 'name'=>'blueprint'
     *                              для исключения повторного добавления файла с таким именем
     * @param   bool $bReplace   - заменять ли файл с таким же именем, если он уже есть
     * @return bool
     */
    public function AppendStyle($sFile, $aParams = array(), $bReplace = false) {
        $aParams = $this->CheckFileParams('css', $sFile, $aParams);
        if ($this->ExistsHeadFileByName('css', $aParams)) {
            return true;
        }
        $this->aCssInclude['append'][] = $sFile;
        $this->aFilesParams['css'][$sFile] = $aParams;
        return true;
    }

    /**
     * Добавляет css-файл в начало списка
     *
     * @param   string $sFile      css-файл стилей
     * @param   array $aParams    - Параметры, например, можно указать параметр 'name'=>'blueprint'
     *                              для исключения повторного добавления файла с таким именем
     * @param   bool $bReplace   - заменять ли файл с таким же именем, если он уже есть
     * @return bool
     */
    public function PrependStyle($sFile, $aParams = array(), $bReplace = false) {
        $aParams = $this->CheckFileParams('css', $sFile, $aParams);
        if ($this->ExistsHeadFileByName('css', $aParams)) {
            return true;
        }
        // Prepend js-file to the beginning of an array
        array_unshift($this->aJsInclude['prepend'], $sFile);
        $this->aFilesParams['css'][$sFile] = $aParams;
        return true;
    }

    /**
     * Проверка на дубль по имени (параметр name) js или css файла
     * Позволяет избежать повторного подключения уже используемой библиотеки
     *
     * @param string $sType Тип файла - css, js
     * @param array $aParams    Параметры
     *
     * @return bool
     */
    protected function ExistsHeadFileByName($sType, $aParams) {
        if (isset($aParams['name'])) {
            // * Проверяем на дубликат по имени
            foreach ($this->aFilesParams[$sType] as $aParamsFile) {
                if (isset($aParamsFile['name']) && strtolower($aParams['name']) == strtolower($aParamsFile['name'])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Предварительная обработка списка файлов - разбивка на наборы, "хаки" и необъединяемые файлы
     *
     * @param $aFiles
     * @return array
     */
    protected function PrepareHeadFilesLists($aFiles) {
        $aResult = array(
            'js' => array(
                'assets' => array(), // наборы объединяемых файлов
                'singles' => array(), // одиночные (необъединяемые) файлы
                'hacks' => array(), // браузеные хаки
            ),
            'css' => array(
                'assets' => array(),
                'singles' => array(),
                'hacks' => array(),
            ),
        );
        foreach ($aFiles as $sType => $aTypeFiles) {
            foreach ($aTypeFiles as $sFile) {
                if (isset($this->aFilesParams[$sType][$sFile])) {
                    $aFileParams = $this->aFilesParams[$sType][$sFile];
                    if (!isset($aFileParams['file'])) $aFileParams['file'] = $sFile;
                    if (isset($aFileParams['browser'])) {
                        // browser hacks
                        $aResult[$sType]['hacks'][$sFile] = $aFileParams;
                    } elseif (isset($aFileParams['merge']) && !$aFileParams['merge']) {
                        $aResult[$sType]['singles'][$sFile] = $aFileParams;
                    } else {
                        $aResult[$sType]['assets'][$aFileParams['asset']][$sFile] = $aFileParams;
                    }
                }
            }
        }
        return $aResult;
    }

    /**
     * Строит массив для подключения css и js,
     * преобразовывает их в строку для HTML
     *
     */
    protected function BuildHeadFiles() {
        $sPath = Router::GetPathWebCurrent();
        /**
         * По умолчанию имеем дефолтные настройки
         */
        $aFiles = $this->aFilesDefault;

        $this->aFileRules = Config::Get('head.rules');
        foreach ((array)$this->aFileRules as $sName => $aRule) {
            if (!$aRule['path']) continue;

            foreach ((array)$aRule['path'] as $sRulePath) {
                $sPattern = "~" . str_replace(array('/', '*'), array('\/', '\w+'), $sRulePath) . "~";
                if (preg_match($sPattern, $sPath)) {
                    /**
                     * Преобразование JS
                     */
                    if (isset($aRule['js']['empty']) && $aRule['js']['empty']) $aFiles['js'] = array();
                    if (isset($aRule['js']['exclude']) && is_array($aRule['js']['exclude'])) $aFiles['js'] = array_diff($aFiles['js'], $aRule['js']['exclude']);
                    if (isset($aRule['js']['include']) && is_array($aRule['js']['include'])) $aFiles['js'] = array_merge($aFiles['js'], $aRule['js']['include']);

                    /**
                     * Преобразование CSS
                     */
                    if (isset($aRule['css']['empty']) && $aRule['css']['empty']) $aFiles['css'] = array();
                    if (isset($aRule['css']['exclude']) && is_array($aRule['css']['exclude'])) $aFiles['css'] = array_diff($aFiles['css'], $aRule['css']['exclude']);
                    if (isset($aRule['css']['include']) && is_array($aRule['css']['include'])) $aFiles['css'] = array_merge($aFiles['css'], $aRule['css']['include']);

                    /**
                     * Продолжаем поиск
                     */
                    if (isset($aRule['stop'])) {
                        break(2);
                    }
                }
            }
        }

        /**
         * Добавляем скрипты и css из массивов
         */
        $aFiles['js'] = array_values(
            array_merge(
                (array)$this->aJsInclude['prepend'],
                (array)$aFiles['js'],
                (array)$this->aJsInclude['append']
            )
        );
        $aFiles['css'] = array_values(
            array_merge(
                (array)$this->aCssInclude['prepend'],
                (array)$aFiles['css'],
                (array)$this->aCssInclude['append']
            )
        );

        $aFiles = $this->PrepareHeadFilesLists($aFiles);

        /**
         * Объединяем файлы в наборы
         */
        $aHeadFiles = array('js' => array(), 'css' => array());

        foreach (array('js', 'css') as $sType) {
            // Обработка по "наборам" (assets)
            if (Config::Get("compress.{$sType}.merge")) {
                // Если задано слияние файлов
                foreach ($aFiles[$sType]['assets'] AS $aFilesAsset) {
                    $sCompressedFile = $this->Compress(array_keys($aFilesAsset), $sType);
                    if ($sCompressedFile) $aHeadFiles[$sType][] = $sCompressedFile;
                }
            } else {
                foreach ($aFiles[$sType]['assets'] AS $aFilesAsset) {
                    $aFileList = $this->MergeHeadFileList(array_keys($aFilesAsset), $sType);
                    if ($aFileList) $aHeadFiles[$sType] = array_merge($aHeadFiles[$sType], $aFileList);
                }
            }
            // * Добавляем файлы хаков
            if (isset($aFiles[$sType]['hacks']) && count($aFiles[$sType]['hacks'])) {
                $aHeadFiles[$sType] = array_merge($aHeadFiles[$sType], array_keys($aFiles[$sType]['hacks']));
            }
            if (isset($aFiles[$sType]['singles']) && count($aFiles[$sType]['singles'])) {
                $aHeadFiles[$sType] = array_merge($aHeadFiles[$sType], array_keys($aFiles[$sType]['singles']));
            }
        }

        /**
         * Получаем HTML код
         */
        $aHtmlHeadFiles = $this->BuildHtmlHeadFiles($aHeadFiles);
        $this->SetHtmlHeadFiles($aHtmlHeadFiles);
    }

    protected function MergeHeadFileList($aFiles, $sType) {
        $aFileList = array();
        foreach ($aFiles as $sFile) {
            if ($sType == 'less' || ($sType == 'css' && F::File_GetExtension($sFile) == 'less')) {
                $sCompiledFile = $this->GetAssetDir() . md5($sFile) . '.css';
                if (F::File_Exists($sCompiledFile) || $this->CompileLess($sFile, $sCompiledFile)) {
                    $aFileList[] = F::File_Dir2Url($sCompiledFile);
                }
            } else {
                $aFileList[] = F::File_Dir2Url($sFile);
            }
        }
        return $aFileList;
    }

    protected function CompileLess($sFile, $sCompiledFile) {
        $sContent = $this->GetCompiledLess($sFile);
        if ($sContent) {
            return F::File_PutContents($sCompiledFile, $sContent);
        }
        return false;
    }

    /**
     * Сжимает все переданные файлы в один,
     * использует файловое кеширование
     *
     * @param  array $aFiles    Список файлов
     * @param  string $sType    Тип файла - js, css
     * @return array
     */
    protected function Compress($aFiles, $sType) {
        //$sCacheDir = $this->GetAssetDir();
        $sCacheName = $this->GetAssetDir() . md5(serialize($aFiles) . '_head') . '.' . $sType;

        /**
         * Если кеш существует, то берем из кеша
         */
        if (!F::File_Exists($sCacheName) || Config::Get('compress.' . $sType . '.force')) {
            /**
             * Считываем содержимое
             */
            ob_start();
            foreach ($aFiles as $sKey => $sFile) {
                // если файл локальный
                if (F::File_IsLocalUrl($sFile)) {
                    $sFile = F::File_Url2Dir($sFile);
                }
                if ($n = strpos($sFile, '?')) $sFile = substr($sFile, 0, $n);
                // Получаем содержимое файла
                if ($sFileContent = $this->GetContent($sFile, $sType)) {
                    //$sFileContent = "\n/* file:" . $sFile . " */\n" . $sFileContent;
                    echo $sFileContent;
                }
            }
            $sContent = ob_get_contents();
            ob_end_clean();

            if (!$sContent) return;

            /**
             * Создаем новый файл (если надо, то и папку) и записываем туда содержимое
             */
            if (F::File_PutContents($sCacheName, $sContent)) {
                @chmod($sCacheName, 0766);
            }
        }
        /**
         * Возвращаем имя файла, заменяя адрес сервера на веб-адрес
         */
        return F::File_Dir2Url($sCacheName);
    }

    /**
     * Get file content with preprocessing
     *
     * @param   string $sFile
     * @param   string|null $sType
     * @return  string|null
     */
    protected function GetContent($sFile, $sType = null) {
        if (!$sType) $sType = F::File_GetExtension($sFile);

        if ($sType == 'less' || ($sType == 'css' && F::File_GetExtension($sFile) == 'less')) {
            $sFileContent = $this->GetCompiledLess($sFile);
        } elseif ($sType == 'css') {
            $sFileContent = $this->GetCompiledCss($sFile);
        } elseif ($sType == 'js') {
            $sFileContent = $this->GetCompiledJs($sFile);
        } else {
            $sFileContent = false;
        }
        return $sFileContent;
    }

    protected function GetCompiledLess($sFile) {
        // Если файлов несколько, то сначала собираем контент из них всех, а потом компилируем
        $aFiles = explode(',', $sFile);
        $sContent = '';
        foreach ($aFiles as $sFileName) {
            $sFileName = trim($sFileName);

            // если задан url текущего сайта, то преобразуем в путь на диске
            if (F::File_IsLocalUrl($sFileName)) {
                $sFileName = F::File_Url2Dir($sFileName);
            }

            $this->Less_AddImportDir(dirname($sFileName));
            if ($sFileName && ($sFileContent = F::File_GetContents($sFileName))) {
                // Запоминаем источник, чтоб скорректировать потом URLs
                $sContent .= "\n/*[" . self::ALTO_SRC . '=' . $sFileName . "]*/\n" . $sFileContent;
            }
        }
        if ($sContent) {
            //if (Config::Get('compress.css.use'))
            //    $this->Less_setFormatter('compressed');
            $this->Less_SetPreserveComments(true);
            $sContent = $this->Less_Compile($sContent);

            // Преобразуем URLs к абсолютному виду
            $sContent = $this->ConvertPathInCss($sContent, $sFile);

            // Удаляем информацию об источниках
            $sContent = preg_replace('|\/\*\[' . self::ALTO_SRC . '=(.*?)\]\*\/|is', '', $sContent);

            if (Config::Get('compress.css.use'))
                $sContent = $this->CompressCss($sContent);
            return $sContent;
        }
    }

    protected function GetCompiledCss($sFile) {
        if ($sFileContent = F::File_GetContents($sFile)) {
            $sFileContent = $this->ConvertPathInCss($sFileContent, $sFile);
            $sFileContent = $this->CompressCss($sFileContent);
            return $sFileContent;
        }
    }

    protected function GetCompiledJs($sFile) {
        if ($sFileContent = F::File_GetContents($sFile)) {
            $sFileContent = $this->CompressJs($sFileContent);
            return $sFileContent;
        }
    }

    /**
     * Выполняет преобразование CSS файлов
     *
     * @param  string $sContent
     * @return string
     */
    protected function CompressCss($sContent) {
        $this->InitCssCompressor();
        if (!$this->oCssCompressor) return $sContent;
        /**
         * Парсим css и отдаем обработанный результат
         */
        $this->oCssCompressor->parse($sContent);
        return $this->oCssCompressor->print->plain();
    }

    /**
     * Конвертирует относительные пути в css файлах в абсолютные
     *
     * @param  string $sContent    Контент CSS
     * @param  string $sSourcePath
     * @return string
     */
    protected function ConvertPathInCss($sContent, $sSourcePath) {
        // Есть ли в файле URLs
        if (!preg_match_all('|url\((.*?)\)|is', $sContent, $aMatchedUrl, PREG_OFFSET_CAPTURE)) {
            return $sContent;
        }
        // Если файл составной, то определяем источники составных частей
        preg_match_all('|\/\*\[' . self::ALTO_SRC . '=(.*?)\]\*\/|is', $sContent, $aMatchedSrc, PREG_OFFSET_CAPTURE);

        if ($nPos = strpos($sSourcePath, ',')) $sSourceDir = dirname(substr($sSourcePath, 0, $nPos)) . '/';
        else $sSourceDir = dirname($sSourcePath) . '/';

        /**
         * Обрабатываем список URLs, ставя им в соответствие исходники
         */
        $aUrls = array();
        foreach ($aMatchedUrl[1] as $aPart) {
            $sUrl = $aPart[0];
            $nPos = $aPart[1];
            if (!isset($aUrls[$sUrl])) {
                // Определяем источник
                $sDir = $sSourceDir;
                if ($aMatchedSrc) {
                    foreach ($aMatchedSrc[1] as $aSrc) {
                        if ($aSrc[1] < $nPos) $sDir = dirname($aSrc[0]) . '/';
                        else break;
                    }
                }
                $aUrls[$sUrl] = $sDir;
            }
        }

        foreach ($aUrls as $sFilePath => $sDir) {
            /**
             * Don't touch data URIs
             */
            if (strstr($sFilePath, 'data:')) {
                continue;
            }
            $sFilePathAbsolute = preg_replace('~\'|"~', '', trim($sFilePath));
            /**
             * Если путь является абсолютным, необрабатываем
             */
            if (substr($sFilePathAbsolute, 0, 1) == "/" || substr($sFilePathAbsolute, 0, 5) == 'http:' || substr($sFilePathAbsolute, 0, 6) == 'https:') {
                continue;
            }
            /**
             * Обрабатываем относительный путь
             */
            $sRealPath = $this->GetRealpath($sDir . $sFilePathAbsolute);
            $this->AddAssetFile($sRealPath);
            //$sFilePathAbsolute = $this->GetWebPath($this->GetRealpath($sDir.$sFilePathAbsolute));
            $sFilePathAbsolute = $this->AssetFileUrl($sRealPath);

            // * Заменяем относительные пути в файле на абсолютные
            $sContent = str_replace($sFilePath, $sFilePathAbsolute, $sContent);
        }
        return $sContent;
    }

    /**
     * Выполняет преобразование JS файла
     *
     * @param  string $sContent
     * @return string
     */
    protected function CompressJs($sContent) {
        $sContent = (Config::Get('compress.js.use'))
            ? JSMin::minify($sContent)
            : $sContent;
        /**
         * Добавляем разделитель в конце файла
         * с расчетом на возможное их слияние в будущем
         */
        return rtrim($sContent, ";") . ";" . PHP_EOL;
    }

    /**
     * Аналог realpath + обработка URL
     *
     * @param string $sPath
     * @return string
     */
    protected function GetRealpath($sPath) {
        if (preg_match("@^(http|https):@", $sPath)) {
            $aUrl = parse_url($sPath);
            $sPath = $aUrl['path'];

            $aParts = array();
            $sPath = preg_replace('~/\./~', '/', $sPath);
            foreach (explode('/', preg_replace('~/+~', '/', $sPath)) as $sPart) {
                if ($sPart === "..") {
                    array_pop($aParts);
                } elseif ($sPart != "") {
                    $aParts[] = $sPart;
                }
            }
            return ((array_key_exists('scheme', $aUrl)) ? $aUrl['scheme'] . '://' . $aUrl['host'] : "") . "/" . implode("/", $aParts);
        } else {
            return realpath($sPath);
        }
    }

    /**
     * Преобразует абсолютный путь к файлу в WEB-вариант
     *
     * @param  string $sFile    Серверный путь до файла
     * @return string
     */
    protected function GetWebPath($sFile) {
        return F::File_Dir2Url($sFile);
    }

    /**
     * Преобразует WEB-путь файла в серверный вариант
     *
     * @param  string $sFile    Web путь до файла
     * @return string
     */
    protected function GetServerPath($sFile) {
        return F::File_Url2Dir($sFile);
    }

    protected function Hash($sPath) {
        return sprintf('%x', crc32($sPath));
    }

    /**
     * Строит HTML код по переданному массиву файлов
     *
     * @param  array $aFileList    Список файлов
     * @return array
     */
    protected function BuildHtmlHeadFiles($aFileList) {
        $aHeader = array('js' => '', 'css' => '');

        foreach ((array)$aFileList['css'] as $sCss) {
            $aHeader['css'] .= $this->WrapHtmlHack("<link rel='stylesheet' type='text/css' href='{$sCss}' />", $sCss, 'css') . PHP_EOL;
        }
        foreach ((array)$aFileList['js'] as $sJs) {
            $aHeader['js'] .= $this->WrapHtmlHack("<script type='text/javascript' src='{$sJs}'></script>", $sJs, 'js') . PHP_EOL;
        }
        return $aHeader;
    }

    /**
     * Обрамляет HTML код в браузер-хак (ex., [if IE 6])
     *
     * @param  string $sHtml
     * @param  string $sFile
     * @param  string $sType (js|css)
     *
     * @return string
     */
    protected function WrapHtmlHack($sHtml, $sFile, $sType) {
        if (!isset($this->aFilesParams[$sType][$sFile]['browser'])) return $sHtml;
        return "<!--[if {$this->aFilesParams[$sType][$sFile]['browser']}]>$sHtml<![endif]-->";
    }

    /**
     * Устанавливает список файлов для вывода в хидере страницы
     *
     * @param array $aText    Список файлов
     */
    public function SetHtmlHeadFiles($aText) {
        $this->aHtmlHeadFiles = $aText;
    }

    /**
     * Добавляет тег для вывода в хидере страницы
     *
     * @param   string  $sTag
     */
    public function AddHtmlHeadTag($sTag) {
        $this->aHtmlHeadTags[] = $sTag;
    }

    /**
     * Устанавливаем заголовок страницы(тег title)
     *
     * @param string $sText    Заголовок
     */
    public function SetHtmlTitle($sText) {
        $this->sHtmlTitle = $sText;
    }

    /**
     * Добавляет часть заголовка страницы через разделитель
     *
     * @param string $sText    Заголовок
     */
    public function AddHtmlTitle($sText) {
        $this->sHtmlTitle = $sText . $this->sHtmlTitleSeparation . $this->sHtmlTitle;
    }

    /**
     * Возвращает текущий заголовок страницы
     *
     * @return string
     */
    public function GetHtmlTitle() {
        return $this->sHtmlTitle;
    }

    /**
     * Устанавливает ключевые слова keywords
     *
     * @param string $sText    Кейворды
     */
    public function SetHtmlKeywords($sText) {
        $this->sHtmlKeywords = $sText;
    }

    /**
     * Устанавливает описание страницы desciption
     *
     * @param string $sText    Описание
     */
    public function SetHtmlDescription($sText) {
        $this->sHtmlDescription = $sText;
    }

    /**
     * Устанавливает основной адрес страницы
     *
     * @param string $sUrl    URL страницы
     * @param bool $bRewrite    Перезаписывать URL, если он уже установлен
     */
    public function SetHtmlCanonical($sUrl, $bRewrite = false) {
        if (!$this->sHtmlCanonical || $bRewrite) {
            $this->sHtmlCanonical = $sUrl;
        }
    }

    /**
     * Устанавливает альтернативный адрес страницы по RSS
     *
     * @param string $sUrl    URL
     * @param string $sTitle    Заголовок
     */
    public function SetHtmlRssAlternate($sUrl, $sTitle) {
        $this->aHtmlRssAlternate['title'] = htmlspecialchars($sTitle);
        $this->aHtmlRssAlternate['url'] = htmlspecialchars($sUrl);
    }

    /**
     * Формирует постраничный вывод
     *
     * @param int $iCount    Общее количество элементов
     * @param int $iCurrentPage    Текущая страница
     * @param int $iCountPerPage    Количество элементов на одну страницу
     * @param int $iCountPageLine    Количество ссылок на другие страницы
     * @param string $sBaseUrl    Базовый URL, к нему будет добавлять постикс /pageN/  и GET параметры
     * @param array $aGetParamsList    Список GET параметров, которые необходимо передавать при постраничном переходе
     * @return array
     */
    public function MakePaging($iCount, $iCurrentPage, $iCountPerPage, $iCountPageLine, $sBaseUrl, $aGetParamsList = array()) {
        if ($iCount == 0) {
            return false;
        }

        $iCountPage = ceil($iCount / $iCountPerPage);
        if (!preg_match("/^[1-9]\d*$/i", $iCurrentPage)) {
            $iCurrentPage = 1;
        }
        if ($iCurrentPage > $iCountPage) {
            $iCurrentPage = $iCountPage;
        }

        $aPagesLeft = array();
        $iTemp = $iCurrentPage - $iCountPageLine;
        $iTemp = $iTemp < 1 ? 1 : $iTemp;
        for ($i = $iTemp; $i < $iCurrentPage; $i++) {
            $aPagesLeft[] = $i;
        }

        $aPagesRight = array();
        for ($i = $iCurrentPage + 1; $i <= $iCurrentPage + $iCountPageLine && $i <= $iCountPage; $i++) {
            $aPagesRight[] = $i;
        }

        $iNextPage = $iCurrentPage < $iCountPage ? $iCurrentPage + 1 : false;
        $iPrevPage = $iCurrentPage > 1 ? $iCurrentPage - 1 : false;

        $sGetParams = '';
        if (is_string($aGetParamsList) || count($aGetParamsList)) {
            $sGetParams = '?' . (is_array($aGetParamsList) ? http_build_query($aGetParamsList, '', '&') : $aGetParamsList);
        }
        $aPaging = array(
            'aPagesLeft' => $aPagesLeft,
            'aPagesRight' => $aPagesRight,
            'iCount' => $iCount,
            'iCountPage' => $iCountPage,
            'iCurrentPage' => $iCurrentPage,
            'iNextPage' => $iNextPage,
            'iPrevPage' => $iPrevPage,
            'sBaseUrl' => rtrim($sBaseUrl, '/'),
            'sGetParams' => $sGetParams,
        );
        /**
         * Избавляемся от дублирования страниц с page=1
         */
        if ($aPaging['iCurrentPage'] == 1) {
            $this->SetHtmlCanonical($aPaging['sBaseUrl'] . '/' . $aPaging['sGetParams']);
        }
        return $aPaging;
    }

    /**
     * Добавить меню в контейнер
     *
     * @param string $sContainer
     * @param string $sTemplate
     */
    public function AddMenu($sContainer, $sTemplate) {
        $this->aMenu[strtolower($sContainer)] = $sTemplate;
    }

    /**
     * Компилирует меню по контейнерам
     *
     */
    protected function BuildMenu() {
        foreach ($this->aMenu as $sContainer => $sTemplate) {
            $this->aMenuFetch[$sContainer] = $this->Fetch($sTemplate);
        }
    }

    /**
     * Обработка поиска файла шаблона, если его не смог найти шаблонизатор Smarty
     *
     * @param string $sType    Тип шаблона/ресурса
     * @param string $sName    Имя шаблона - имя файла
     * @param string $sContent    Возврат содержания шаблона при return true;
     * @param int $iTimestamp    Возврат даты модификации шаблона при return true;
     * @param Smarty $oSmarty    Объект Smarty
     * @return string|bool
     */
    public function SmartyDefaultTemplateHandler($sType, $sName, &$sContent, &$iTimestamp, $oSmarty) {
        /**
         * Название шаблона может содержать, как полный путь до файла шаблона, так и относительный любого из каталога в $oSmarty->getTemplateDir()
         * По дефолту каталоги такие: /templates/skin/[name]/ и /plugins/
         */
        /**
         * Задача: если это файл плагина для текущего шаблона, то смотрим этот же файл шаблона плагина в /default/
         */
        if (Config::Get('view.skin') != 'default') {
            // /root/plugins/[plugin name]/templates/skin/[skin name]/dir/test.tpl
            if (preg_match('@^' . preg_quote(Config::Get('path.root.dir')) . '/plugins/([\w\-_]+)/templates/skin/' . preg_quote(Config::Get('view.skin')) . '/@i', $sName, $aMatch)) {
                $sFile = str_replace($aMatch[0], Config::Get('path.root.dir') . '/plugins/' . $aMatch[1] . '/templates/skin/default/', $sName);
                if ($this->TemplateExists($sFile)) {
                    return $sFile;
                }
            }
            // [plugin name]/templates/skin/[skin name]/dir/test.tpl
            if (preg_match('@^([\w\-_]+)/templates/skin/' . preg_quote(Config::Get('view.skin')) . '/@i', $sName, $aMatch)) {
                $sFile = Config::Get('path.root.dir') . '/plugins/' . str_replace($aMatch[0], $aMatch[1] . '/templates/skin/default/', $sName);
                if ($this->TemplateExists($sFile)) {
                    return $sFile;
                }
            }
        }
        return false;
    }

    public function ClearAll() {

        $this->ClearSmartyFiles();
        $this->ClearAssetsFiles();
    }

    /**
     * Clear all cached and compiled files of Smarty
     */
    public function ClearSmartyFiles() {

        $this->oSmarty->clearCompiledTemplate();
        $this->oSmarty->clearAllCache();
        F::File_ClearDir(Config::Get('path.tmp.dir') . '/templates/');
    }

    public function ClearAssetsFiles() {

        $sDir = $this->GetAssetDir();
        F::File_RemoveDir($sDir);
    }

    /**
     * Загружаем переменные в шаблон при завершении модуля
     *
     */
    public function Shutdown() {
        $timer = microtime(true);

        // * Создаются списки виджетов для вывода
        $this->MakeWidgetsLists();

        // * Добавляем JS и CSS по предписанным правилам
        $this->BuildHeadFiles();
        $this->VarAssign();
        $this->CopyAssetFiles();

        // * Рендерим меню для шаблонов и передаем контейнеры в шаблон
        $this->BuildMenu();
        $this->MenuVarAssign();

        self::$_preprocessTime += microtime(true) - $timer;
    }

}

// EOF
