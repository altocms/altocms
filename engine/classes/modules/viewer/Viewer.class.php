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

// For Smarty
define('DS', '/');

F::IncludeLib('Smarty/libs/Smarty.class.php');

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

    /**
     * Коллекция (массив) виджетов
     *
     * @var array
     */
    protected $aWidgets = array();

    /**
     * Массив дополнительных (добавленных) виджетов
     *
     * @var array
     */
    protected $aWidgetsAppend = array();

    /**
     * Признак сортировки виджетов
     *
     * @var bool
     */
    protected $bWidgetsSorted = false;

    /**
     * Стандартные настройки вывода js, css файлов
     *
     * @var array
     */
    protected $aFilesDefault = array(
        'js' => array(),
        'css' => array()
    );

    protected $aFilesPrepend = array(
        'js' => array(),
        'css' => array()
    );

    protected $aFilesAppend = array(
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
     * Каталог для кешировния js,css файлов
     *
     * @var string
     */
    protected $sCacheDir = '';

    /**
     * Заголовки HTML страницы (объединяются через сепаратор перед выдачей)
     *
     * @var array
     */
    protected $aHtmlTitles = '';

    /**
     * Максимальное число заголовков, из которых строится общий заголовок HTML страницы (тег title)
     *
     * @var int
     */
    protected $iHtmlTitlesMax = 0;

    /**
     * Разделитель заголовка HTML страницы
     *
     * @var string
     */
    protected $sHtmlTitleSeparator = ' / ';

    /**
     * SEO ключевые слова страницы
     *
     * @var array
     */
    protected $aHtmlKeywords;

    /**
     * SEO описание страницы
     *
     * @var string
     */
    protected $sHtmlDescription;

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

    protected $aSpecMetaTagsAttr = array('http-equiv', 'name', 'property', 'itemprop');

    /**
     * Переменные для передачи в шаблон
     *
     * @var array
     */
    protected $aVarsTemplate = array();

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

    /** @var bool Local viewer */
    protected $bLocal = false;

    /** @var  string Current skin */
    protected $sViewSkin;

    /** @var  string Current theme */
    protected $sViewTheme;

    protected $sForcedSkin;

    protected $sForcedTheme;

    protected $bAssetInit = false;

    protected $nMuteErrorsCnt = 0;

    protected $aResponseHeaders = array();

    /** @var bool To prevent double initialization  */
    protected $bInitRender = false;

    /**
     * Константа для компиляции LESS-файлов
     */
    const ALTO_SRC = '___ALTO_SRC___';

    static protected $_renderCount = 0;
    static protected $_renderTime = 0;
    static protected $_renderStart = 0;
    static protected $_preprocessTime = 0;
    static protected $_inRender = 0;

    static protected $_renderOptionsStack = array();

    /**
     * @return int
     */
    static public function GetRenderCount() {

        return self::$_renderCount;
    }

    /**
     * @return int
     */
    static public function GetRenderTime() {

        return self::$_renderTime + (self::$_renderStart ? microtime(true) - self::$_renderStart : 0);
    }

    /**
     * @return int
     */
    static public function GetPreprocessingTime() {

        return self::$_preprocessTime + self::GetRenderTime();
    }

    /**
     * @return int
     */
    static public function GetTotalTime() {

        return self::GetPreprocessingTime() + self::GetRenderTime();
    }

    /**
     * Инициализация модуля
     *
     * @param bool $bLocal
     */
    public function Init($bLocal = false) {

        E::ModuleHook()->Run('viewer_init_start', compact('bLocal'));

        $this->bLocal = (bool)$bLocal;

        if (($iTitleMax = Config::Get('view.html.title_max')) && ($iTitleMax > 0)) {
            $this->iHtmlTitlesMax = Config::Get('view.html.title_max');
        }
        $this->sHtmlTitleSeparator = Config::Get('view.html.title_sep');

        // * Заголовок HTML страницы
        $this->SetHtmlTitle(Config::Get('view.name'));

        // * SEO ключевые слова страницы
        $sValue = (Config::Get('view.keywords') ? Config::Get('view.keywords') : Config::Get('view.html.keywords'));
        $this->SetHtmlKeywords($sValue);

        // * SEO описание страницы
        $sValue = (Config::Get('view.description') ? Config::Get('view.description') : Config::Get('view.html.description'));
        $this->SetHtmlDescription($sValue);

        // * Пустой вызов только для того, чтоб модуль Message инициализировался, если еще не
        E::ModuleMessage()->IsInit();

        $this->sCacheDir = Config::Get('path.runtime.dir');

        $this->SetResponseHeader('X-Powered-By', 'Alto CMS');
        $this->SetResponseHeader('Content-Type', 'text/html; charset=utf-8');
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
     * Get templator Smarty
     *
     * @param array|null $aVariables
     *
     * @return Smarty
     */
    public function GetSmartyObject() {

        $oSmarty = $this->oSmarty;
        // For LS interface compatibility
        if (func_num_args() && ($aVariables = func_get_arg(0)) && is_array($aVariables)) {
            $oSmarty->assign($aVariables);
        }
        return $oSmarty;
    }

    protected function _initTemplator() {

        if (!$this->oSmarty) {
            $this->_tplInit();
        }
    }

    /**
     * Инициализация шаблонизатора
     *
     */
    protected function _tplInit() {

        if ($this->oSmarty) {
            return;
        }

        // * Создаём объект Smarty
        $this->oSmarty = $this->CreateSmartyObject();

        // * Устанавливаем необходимые параметры для Smarty
        $this->oSmarty->compile_check = (bool)Config::Get('smarty.compile_check');
        $this->oSmarty->force_compile = (bool)Config::Get('smarty.force_compile');
        $this->oSmarty->merge_compiled_includes = (bool)Config::Get('smarty.merge_compiled_includes');

        // * Подавляем NOTICE ошибки - в этом вся прелесть смарти )
        $this->oSmarty->error_reporting = error_reporting() & ~E_NOTICE;

        // * Папки расположения шаблонов по умолчанию
        $aDirs = F::File_NormPath(F::Str2Array(Config::Get('path.smarty.template')));
        if (sizeof($aDirs) == 1) {
            $sDir = $aDirs[0];
            $aDirs['themes'] = F::File_NormPath($sDir . '/themes');
            $aDirs['tpls'] = F::File_NormPath($sDir . '/tpls');
        }
        $this->oSmarty->setTemplateDir($aDirs);
        if (Config::Get('smarty.dir.templates')) {
            $this->oSmarty->addTemplateDir(F::File_NormPath(F::Str2Array(Config::Get('smarty.dir.templates'))));
        }

        // * Для каждого скина устанавливаем свою директорию компиляции шаблонов
        $sCompilePath = F::File_NormPath(Config::Get('path.smarty.compiled'));
        F::File_CheckDir($sCompilePath);
        $this->oSmarty->setCompileDir($sCompilePath);
        $this->oSmarty->setCacheDir(Config::Get('path.smarty.cache'));

        // * Папки расположения пдагинов Smarty
        $this->oSmarty->addPluginsDir(array(Config::Get('path.smarty.plug'), 'plugins'));
        if (Config::Get('smarty.dir.plugins')) {
            $this->oSmarty->addPluginsDir(F::File_NormPath(F::Str2Array(Config::Get('smarty.dir.plugins'))));
        }

        $this->oSmarty->default_template_handler_func = array($this, 'SmartyDefaultTemplateHandler');

        // * Параметры кеширования, если заданы
        if (Config::Get('smarty.cache_lifetime')) {
            $this->oSmarty->caching = Smarty::CACHING_LIFETIME_SAVED;
            $this->oSmarty->cache_lifetime = F::ToSeconds(Config::Get('smarty.cache_lifetime'));
        }

        // Settings for Smarty 3.1.16 and more
        $this->oSmarty->inheritance_merge_compiled_includes = false;

        F::IncludeFile('./plugs/resource.file.php');
        $this->oSmarty->registerResource('file', new Smarty_Resource_File());

        // Mutes expected Smarty minor errors
        $this->oSmarty->muteExpectedErrors();
    }

    /**
     * @param string $sTemplate
     * @param bool $bException
     *
     * @return bool|void
     * @throws Exception
     */
    protected function _tplTemplateExists($sTemplate, $bException = false) {

        if (!$this->oSmarty) {
            $this->_tplInit();
        }

        $bResult = $this->oSmarty->templateExists($sTemplate);

        if (!$bResult && $bException) {
            $sSkin = $this->GetConfigSkin();
            $sMessage = 'Can not find the template "' . $sTemplate . '" in skin "' . $sSkin . '"';
            if ($aTpls = $this->GetSmartyObject()->template_objects) {
                if (is_array($aTpls)) {
                    $sMessage .= ' (from: ';
                    foreach($aTpls as $oTpl) {
                        $sMessage .= $oTpl->template_resource . '; ';
                    }
                    $sMessage .= ')';
                }
            }
            $sMessage .= '. ';
            $oSkin = E::ModuleSkin()->GetSkin($sSkin);
            if ((!$oSkin || $oSkin->GetCompatible() != 'alto') && !E::ActivePlugin('ls')) {
                $sMessage .= 'Probably you need to activate plugin "Ls".';
            }

            // записываем доп. информацию - пути к шаблонам Smarty
            $sErrorInfo = 'Template Dirs: ' . implode('; ', $this->oSmarty->getTemplateDir());
            $this->_error($sMessage, $sErrorInfo);
            return false;
        }
        return $bResult ? $sTemplate : $bResult;
    }

    /**
     * @param string $sTemplate
     * @param array  $aVariables
     *
     * @return object
     */
    protected function _tplCreateTemplate($sTemplate, $aVariables = null) {

        $oSmarty = $this->GetSmartyObject($this->getTemplateVars());
        $oTemplate = $oSmarty->createTemplate($sTemplate, null, null, $oSmarty, false);
        if ($aVariables && is_array($aVariables)) {
            $oTemplate->assign($aVariables);
        }
        return $oTemplate;
    }

    /**
     * Set templator options
     *
     * @param array $aOptions
     */
    protected function _tplSetOptions($aOptions) {

        self::$_renderOptionsStack[] = array(
            'caching'        => $this->oSmarty->caching,
            'cache_lifetime' => $this->oSmarty->cache_lifetime,
        );
        if (isset($aOptions['cache'])) {
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

    }

    /**
     * Restore templator options
     */
    protected function _tplRestoreOptions() {

        if (self::$_renderOptionsStack) {
            $aOptions = array_pop(self::$_renderOptionsStack);
            if (isset($aOptions['caching'])) {
                $this->oSmarty->caching = $aOptions['caching'];
            }
            if (isset($aOptions['cache_lifetime'])) {
                $this->oSmarty->cache_lifetime = $aOptions['cache_lifetime'];
            }
        }
    }

    /**
     * Загружает переменную в шаблон
     *
     * @param string $sName  - Имя переменной в шаблоне
     * @param mixed  $xValue - Значение переменной
     */
    protected function _tplAssign($sName, $xValue) {

        $this->oSmarty->assign($sName, $xValue);
    }


    /**
     * Initialization of skin
     *
     */
    protected function _initSkin() {

        $this->sViewSkin = $this->GetConfigSkin();

        // Load skin's config
        $aConfig = array();
        Config::ResetLevel(Config::LEVEL_SKIN);

        $aSkinConfigPaths['sSkinConfigCommonPath'] = Config::Get('path.smarty.template') . '/settings/config/';
        $aSkinConfigPaths['sSkinConfigAppPath']    = Config::Get('path.dir.app')
            . F::File_LocalPath(
                $aSkinConfigPaths['sSkinConfigCommonPath'],
                Config::Get('path.dir.common')
            )
        ;
        // Может загружаться основной конфиг скина, так и внешние секции конфига,
        // которые задаются ключом 'config_load'
        // (обычно это 'classes', 'assets', 'jevix', 'widgets', 'menu')
        $aConfigNames = array('config') + F::Str2Array(Config::Get('config_load'));

        // Load configs from paths
        foreach ($aConfigNames as $sConfigName) {
            foreach ($aSkinConfigPaths as $sPath) {
                $sFile = $sPath . $sConfigName . '.php';
                if (F::File_Exists($sFile)) {
                    $aSubConfig = F::IncludeFile($sFile, false, true);
                    if ($sConfigName !='config' && !isset($aSubConfig[$sConfigName])) {
                        $aSubConfig = array($sConfigName => $aSubConfig);
                    }
                    // загружаем конфиг, что позволяет сразу использовать значения
                    // в остальных конфигах скина (assets и кастомном config.php) через Config::Get()
                    Config::Load($aSubConfig, false, null, null, $sFile);
                }
            }
        }

        // Checks skin's config from users settings
        $sUserConfigKey = 'skin.' . $this->sViewSkin . '.config';
        $aUserConfig = Config::Get($sUserConfigKey);
        if ($aUserConfig) {
            if (!$aConfig) {
                $aConfig = $aUserConfig;
            } else {
                $aConfig = F::Array_MergeCombo($aConfig, $aUserConfig);
            }
        }

        if ($aConfig) {
            Config::Load($aConfig, false, null, null, $sUserConfigKey);
        }

        // Check skin theme and set one in config if it was changed
        if ($this->GetConfigTheme() != Config::Get('view.theme')) {
            Config::Set('view.theme', $this->GetConfigTheme());
        }

        // Load lang files for skin
        E::ModuleLang()->LoadLangFileTemplate(E::ModuleLang()->GetLang());

        // Load template variables from config
        if (($aVars = Config::Get('view.assign')) && is_array($aVars)) {
            $this->Assign($aVars);
        }
    }

    /**
     * Initialization of render before Fetch() or Display()
     *
     * @param bool $bForce
     */
    protected function _initRender($bForce = false) {

        if ($this->bInitRender && !$bForce) {
            return;
        }

        $nTimer = microtime(true);

        E::ModuleHook()->Run('render_init_start', array('bLocal' => $this->bLocal));

        // If skin not initialized (or it was changed) then init one
        if ($this->sViewSkin != $this->GetConfigSkin()) {
            $this->_initSkin($this->bLocal);
        } else {
            // Level could be changed after skin initialization
            Config::SetLevel(Config::LEVEL_SKIN);
        }

        // init templator if not yet
        $this->_initTemplator();

        // Loads localized texts
        $aLang = E::ModuleLang()->GetLangMsg();
        // Old skin compatibility
        $aLang['registration_password_notice'] = E::ModuleLang()->Get('registration_password_notice', array('min' => C::Val('module.security.password_len', 3)));
        $this->Assign('aLang', $aLang);
        //$this->Assign('oLang', E::ModuleLang()->Dictionary());

        if (!$this->bLocal && !$this->GetResponseAjax()) {
            // Initialization of assets (JS-, CSS-files)
            $this->InitAssetFiles();
        }

        E::ModuleHook()->Run('render_init_done', array('bLocal' => $this->bLocal));

        $this->bInitRender = true;
        self::$_preprocessTime += microtime(true) - $nTimer;
    }

    /**
     * Add HTTP header
     *
     * @param string $sHeader
     */
    public function AddResponseHeader($sHeader) {

        if (preg_match('/^([^:]+)\s+:(.+)$/', $sHeader, $aM)) {
            $sHeader = $aM[1] . ': ' . $aM[2];
        }
        $this->aResponseHeaders[] = $sHeader;
    }

    /**
     * Sets HTTP header as "<key>: <value>"
     *
     * @param string $sHeaderKey
     * @param string $sHeaderValue
     */
    public function SetResponseHeader($sHeaderKey, $sHeaderValue) {

        $sSeekKey = strtolower($sHeaderKey) . ':';
        foreach($this->aResponseHeaders as $iIndex => $sHeader) {
            if (strpos(strtolower($sHeader), $sSeekKey) === 0) {
                unset($this->aResponseHeaders[$iIndex]);
                break;
            }
        }
        $this->AddResponseHeader($sHeaderKey . ': ' . $sHeaderValue);
    }

    /**
     * Clears HTTP Headers
     */
    public function ClearResponseHeaders() {

        $this->aResponseHeaders[] = array();
    }

    /**
     * Returns HTTP headers
     *
     * @return array
     */
    public function GetResponseHeaders() {

        return $this->aResponseHeaders;
    }

    /**
     * Send HTTP headers
     *
     * @return bool
     */
    public function SendResponseHeaders() {

        if (headers_sent()) {
            return false;
        }
        foreach($this->aResponseHeaders as $iIndex => $sHeader) {
            header($sHeader);
            unset($this->aResponseHeaders[$iIndex]);
        }
        return true;
    }

    /**
     * Return content type from HTTP headers
     *
     * @param bool $bSystemHeadersOnly
     *
     * @return string
     */
    public function GetContentType($bSystemHeadersOnly = false) {

        $aHeaders = headers_list();
        if (!$bSystemHeadersOnly) {
            if ($aHeaders) {
                $aHeaders = array_merge($aHeaders, $this->aResponseHeaders);
            } else {
                $aHeaders = $this->aResponseHeaders;
            }
        }
        $sResult = null;
        if ($aHeaders) {
            foreach ($aHeaders as $sHeader) {
                if (preg_match('/content-type:\s*([a-z0-9\-\/]+)/i', $sHeader, $aM)) {
                    $sResult = $aM[1];
                }
            }
        }
        return $sResult;
    }

    /**
     * Возвращает локальную копию модуля
     *
     * @return ModuleViewer
     */
    public function GetLocalViewer() {

        $sClass = E::ModulePlugin()->GetDelegate('module', __CLASS__);

        /** @var ModuleViewer $oViewerLocal */
        $oViewerLocal = new $sClass(Engine::getInstance());
        $oViewerLocal->Init(true);
        $oViewerLocal->_initRender();
        $oViewerLocal->VarAssign();

        $oSmarty = $oViewerLocal->GetSmartyObject();
        $oSmarty->assign($oViewerLocal->getTemplateVars());

        return $oViewerLocal;
    }

    /**
     * Возвращает версию Smarty
     *
     * @return string|null
     */
    public function GetSmartyVersion() {

        $sSmartyVersion = null;
        if (defined('Smarty::SMARTY_VERSION')) {
            $sSmartyVersion = Smarty::SMARTY_VERSION;
        }
        return $sSmartyVersion;
    }

    /**
     * Выполняет загрузку необходимых (возможно даже системных :)) переменных в шаблонизатор
     */
    public function VarAssign() {

        // * Загружаем весь $_REQUEST, предварительно обработав его функцией F::HtmlSpecialChars()
        $aRequest = $_REQUEST;
        F::HtmlSpecialChars($aRequest);
        $this->Assign('_aRequest', $aRequest);

        // * Параметры стандартной сессии
        // TODO: Убрать! Не должно этого быть на страницах сайта
        $this->Assign('_sPhpSessionName', session_name());
        $this->Assign('_sPhpSessionId', session_id());

        // * Загружаем роутинг с учетом правил rewrite
        $aRouter = array();
        $aPages = Config::Get('router.page');

        if (!$aPages || !is_array($aPages)) {
            throw new Exception('Router rules is underfined.');
        }
        foreach ($aPages as $sPage => $aAction) {
            $aRouter[$sPage] = R::GetPath($sPage);
        }
        $this->Assign('aRouter', $aRouter);

        // * Загружаем виджеты
        $this->Assign('aWidgets', $this->GetWidgets());

        // * Загружаем HTML заголовки
        $this->Assign('sHtmlTitle', $this->GetHtmlTitle());
        $this->Assign('sHtmlKeywords', $this->GetHtmlKeywords());
        $this->Assign('sHtmlDescription', $this->GetHtmlDescription());

        $this->Assign('aHtmlHeadFiles', $this->aHtmlHeadFiles);
        $this->Assign('aHtmlRssAlternate', $this->aHtmlRssAlternate);
        $this->Assign('sHtmlCanonical', $this->sHtmlCanonical);
        $this->Assign('aHtmlHeadTags', $this->aHtmlHeadTags);

        $this->Assign('aJsAssets', E::ModuleViewerAsset()->GetPreparedAssetLinks());

        // * Загружаем список активных плагинов
        $aPlugins = E::GetActivePlugins();
        $this->Assign('aPluginActive', array_fill_keys(array_keys($aPlugins), true));

        // * Загружаем пути до шаблонов плагинов
        $aPluginsTemplateUrl = array();
        $aPluginsTemplateDir = array();

        /** @var Plugin $oPlugin */
        foreach ($aPlugins as $sPlugin => $oPlugin) {
            $sDir = Plugin::GetTemplateDir(get_class($oPlugin));
            if ($sDir) {
                $this->oSmarty->addTemplateDir(array($sDir . 'tpls/', $sDir), $oPlugin->GetName(false));
                $aPluginsTemplateDir[$sPlugin] = $sDir;
                $aPluginsTemplateUrl[$sPlugin] = Plugin::GetTemplateUrl(get_class($oPlugin));
            }
        }
        if (E::ActivePlugin('ls')) {
            // LS-compatible //
            $this->Assign('aTemplateWebPathPlugin', $aPluginsTemplateUrl);
            $this->Assign('aTemplatePathPlugin', $aPluginsTemplateDir);
        }

        $sSkinTheme = $this->GetConfigTheme();
        if (!$sSkinTheme) {
            $sSkinTheme = 'default';
        }
        // Проверка существования темы
        if ($this->CheckTheme($sSkinTheme)) {
            $this->oSmarty->compile_id = $sSkinTheme;
        }
        $this->Assign('sSkinTheme', $sSkinTheme);

        $oSkin = E::ModuleSkin()->GetSkin($this->sViewSkin);
        if (!$oSkin->GetCompatible() || $oSkin->SkinCompatible('1.1', '<')) {
            // Для старых скинвов загружаем объект доступа к конфигурации
            $this->Assign('oConfig', Config::getInstance());

        }
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
     * @param string $sTemplate - Шаблон для вывода
     */
    public function Display($sTemplate) {

        // Проверка существования папки с текущим скином
        $this->CheckSkin();

        if ($this->sResponseAjax) {
            $this->DisplayAjax($this->sResponseAjax);
        }

        $this->SendResponseHeaders();
        /*
         * Если шаблон найден то выводим, иначе - ошибка
         * Но предварительно проверяем наличие делегата
         */
        if ($sTemplate) {
            $this->_initRender();

            $sTemplate = E::ModulePlugin()->GetDelegate('template', $sTemplate);
            if ($this->TemplateExists($sTemplate, true)) {
                // Установка нового secret key непосредственно перед рендерингом
                E::ModuleSecurity()->SetSecurityKey();

                $oTpl = $this->_tplCreateTemplate($sTemplate, $this->getTemplateVars());

                self::$_renderCount++;
                self::$_renderStart = microtime(true);
                self::$_inRender += 1;

                $oTpl->display();

                self::$_inRender -= 1;
                self::$_renderTime += (microtime(true) - self::$_renderStart);
                self::$_renderStart = 0;
            }
        }
    }

    /**
     * Возвращает отрендеренный шаблон
     *
     * @param string $sTemplate - Шаблон для рендеринга
     * @param array  $aVars     - Переменные для локального рендеринга
     * @param array  $aOptions  - Опции рендеринга
     *
     * @return  string
     */
    public function Fetch($sTemplate, $aVars = array(), $aOptions = array()) {

        $this->_initRender();

        // * Проверяем наличие делегата
        $sTemplate = E::ModulePlugin()->GetDelegate('template', $sTemplate);
        if ($this->TemplateExists($sTemplate, true)) {
            // Если задаются локальные параметры кеширования, то сохраняем общие
            $this->_tplSetOptions($aOptions);

            $oTpl = $this->_tplCreateTemplate($sTemplate);
            if ($aVars) {
                $oTpl->assign($aVars);
            }

            self::$_renderCount++;
            self::$_renderStart = microtime(true);
            self::$_inRender += 1;

            $sContent = $oTpl->fetch();

            self::$_inRender -= 1;
            self::$_renderTime += (microtime(true) - self::$_renderStart);
            self::$_renderStart = 0;

            $this->_tplRestoreOptions();

            return $sContent;
        }
        return null;
    }

    /**
     * Возвращает отрендеренный шаблон виджета
     *
     * @param string $sTemplate - Шаблон для рендеринга
     * @param array  $aVars     - Переменные для локального рендеринга
     * @param array  $aOptions  - Опции рендеринга
     *
     * @return string
     */
    public function FetchWidget($sTemplate, $aVars = array(), $aOptions = array()) {

        // * Проверяем наличие делегата
        $sDelegateTemplate = E::ModulePlugin()->GetDelegate('template', $sTemplate);
        $sRenderTemplate = '';
        if ($sDelegateTemplate == $sTemplate && !$this->TemplateExists($sTemplate)) {
            $sWidgetTemplate = 'widgets/widget.' . $sTemplate;
            $sWidgetTemplate = E::ModulePlugin()->GetDelegate('template', $sWidgetTemplate);
            if ($this->TemplateExists($sWidgetTemplate)) {
                $sRenderTemplate = $sWidgetTemplate;
            }

            if (!$sRenderTemplate) {
                // * LS-compatible *//
                $sWidgetTemplate = 'blocks/block.' . $sTemplate;
                $sWidgetTemplate = E::ModulePlugin()->GetDelegate('template', $sWidgetTemplate);
                if ($this->TemplateExists($sWidgetTemplate)) {
                    $sRenderTemplate = $sWidgetTemplate;
                }
            }

            if (!$sRenderTemplate) {
                $sRenderTemplate = $sWidgetTemplate;
            }
        }
        if (!$sRenderTemplate) {
            $sRenderTemplate = $sDelegateTemplate;
        }

        return $this->Fetch($sRenderTemplate, $aVars, $aOptions);
    }

    /**
     * Ответ на ajax запрос
     *
     * @param string $sType - Варианты: json, jsonIframe, jsonp
     */
    public function DisplayAjax($sType = 'json') {

        $sOutput = '';

        // * Загружаем статус ответа и сообщение
        $bStateError = false;
        $sMsgTitle = '';
        $sMsg = '';
        $aMsgError = E::ModuleMessage()->GetError();
        $aMsgNotice = E::ModuleMessage()->GetNotice();
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
            $this->SetResponseHeader('Content-type', 'application/json');
            $sOutput = F::jsonEncode($this->aVarsAjax);
        } elseif ($sType == 'jsonIframe') {
            // Оборачивает json в тег <textarea>, это не дает браузеру выполнить HTML, который вернул iframe
            $this->SetResponseHeader('Content-type', 'application/json');

            // * Избавляемся от бага, когда в возвращаемом тексте есть &quot;
            $sOutput = '<textarea>' . htmlspecialchars(F::jsonEncode($this->aVarsAjax)) . '</textarea>';
        } elseif ($sType == 'jsonp') {
            $this->SetResponseHeader('Content-type', 'application/json');
            $sOutput = F::GetRequest('jsonpCallback', 'callback') . '(' . F::jsonEncode($this->aVarsAjax) . ');';
        }

        $this->Flush($sOutput);

        exit();
    }

    /**
     * Flush output string to client
     *
     * @param string $sOutput
     */
    public function Flush($sOutput) {

        $this->SendResponseHeaders();

        echo $sOutput;
    }

    /**
     * Sets forced skin
     *
     * @param string $sSkin
     */
    public function SetViewSkin($sSkin) {

        $this->sForcedSkin = $sSkin;
    }

    /**
     * Sets forced theme
     *
     * @param string $sTheme
     */
    public function SetViewTheme($sTheme) {

        $this->sForcedTheme = $sTheme;
    }

    /**
     * Returns theme of current skin from forced settings or config
     *
     * @param  bool $bSiteSkin - if true then returns skin for site (ignore LEVEL_ACTION)
     *
     * @return string
     */
    public function GetViewSkin($bSiteSkin = false) {

        if ($this->sForcedSkin && !$bSiteSkin) {
            return $this->sForcedSkin;
        }
        return $this->GetConfigSkin($bSiteSkin);
    }

    /**
     * Returns theme of current theme from forced settings or config
     *
     * @param  bool $bSiteSkin - if true then returns theme for site (ignore LEVEL_ACTION)
     *
     * @return string
     */
    public function GetViewTheme($bSiteSkin = false) {

        if ($this->sForcedTheme && !$bSiteSkin) {
            return $this->sForcedTheme;
        }
        return $this->GetConfigTheme($bSiteSkin);
    }

    /**
     * Возвращает скин
     *
     * @param bool $bSiteSkin - если задано, то возвращает скин, установленный для сайта (игнорирует скин экшена)
     *
     * @return  string
     */
    public function GetConfigSkin($bSiteSkin = false) {

        if ($bSiteSkin) {
            return Config::Get('view.skin', Config::LEVEL_CUSTOM);
        } else {
            return Config::Get('view.skin');
        }
    }

    /**
     * Returns theme of current skin from config
     *
     * @param  bool $bSiteSkin - if true then returns theme for site (ignore LEVEL_ACTION)
     *
     * @return string
     */
    public function GetConfigTheme($bSiteSkin = false) {

        if ($bSiteSkin) {
            return Config::Get('view.theme', Config::LEVEL_CUSTOM);
        } else {
            return Config::Get('view.theme');
        }
    }

    /**
     * Возвращает путь к папке скина
     *
     * @param bool $bSiteSkin - Если задано, то возвращает скин, установленный для сайта (игнорирует скин экшена)
     *
     * @return  string
     */
    public function GetTemplateDir($bSiteSkin = true) {

        if ($bSiteSkin) {
            return F::File_NormPath(Config::Get('path.skins.dir') . '/' . $this->GetConfigSkin($bSiteSkin));
        } else {
            return Config::Get('path.smarty.template');
        }
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
            E::ModuleSecurity()->ValidateSendForm();
        }
        $this->sResponseAjax = $sResponseAjax;
        $_REQUEST['ALTO_AJAX'] = $sResponseAjax;
        $this->bResponseSpecificHeader = $bResponseSpecificHeader;
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
     * Sets value(s) to template variable(s)
     *
     * @param string|array $xParam - Name of template variable or associate array
     * @param mixed|null   $xValue - Value of variable if $xParam is string
     */
    public function Assign($xParam, $xValue = null) {

        if (is_array($xParam) && is_null($xValue)) {
            foreach($xParam as $sName => $xValue) {
                $this->Assign($sName, $xValue);
            }
        } else {
            $this->aVarsTemplate[$xParam] = $xValue;
            if (self::$_inRender || $this->bLocal) {
                $this->_tplAssign($xParam, $xValue);
            }
        }
    }

    /**
     * Returns template variable(s)
     *
     * @param string|null $sVarName
     *
     * @return mixed
     */
    public function getTemplateVars($sVarName = null) {

        $xResult = null;
        if ($sVarName) {
            if (isset($this->aVarsTemplate[$sVarName])) {
                $xResult = $this->aVarsTemplate[$sVarName];
            }
        } else {
            $xResult = $this->aVarsTemplate;
        }
        return $xResult;
    }

    protected function _muteErrors() {

        if ($this->nMuteErrorsCnt <= 0) {
            $this->oSmarty->muteExpectedErrors();
            $this->nMuteErrorsCnt++;
        }
    }

    protected function _unmuteErrors() {

        if ($this->nMuteErrorsCnt > 0) {
            $this->oSmarty->unmuteExpectedErrors();
            $this->nMuteErrorsCnt--;
        }
    }

    /**
     * Проверяет существование шаблона
     *
     * @param   string $sTemplate   - Шаблон
     * @param   bool   $bException  - Нужно ли генерить ошибку, если шаблон не найден
     *
     * @return  bool
     */
    public function TemplateExists($sTemplate, $bException = false) {

        return $this->_tplTemplateExists($sTemplate, $bException);
    }

    /**
     * Проверяет существование скина/темы
     *
     * @param   string $sSkin      - Скин (или скин/тема)
     * @param   bool   $bException - Нужно ли генерить ошибку, если скин не найден
     *
     * @return  bool
     */
    public function SkinExists($sSkin, $bException = false) {

        if (strpos($sSkin, '/') !== false) {
            // Разделяем сам скин и тему
            list($sSkin, $sTheme) = explode('/', $sSkin, 2);
            if (!$sSkin) {
                $sSkin = $this->GetConfigSkin();
            }
        } else {
            $sTheme = null;
        }
        $sCheckDir = Config::Get('path.skin.dir');
        // Если проверяется не текущий скин, то корректируем путь
        if ($sSkin != $this->GetConfigSkin()) {
            $sCheckDir = str_replace('/' . $this->GetConfigSkin() . '/', '/' . $sSkin . '/', $sCheckDir);
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
            $this->_error($sMessage, $sErrorInfo);
            return false;
        }
        return $bResult;
    }

    /**
     * Проверяет существование папки текущего скина
     *
     * @return  bool
     */
    public function CheckSkin() {

        if (!$this->SkinExists($this->GetConfigSkin(), true)) {
            die('Please check skin folder');
        }
    }

    /**
     * Проверяет существование папки конкретной темы текущего скина
     *
     * @param   string $sTheme
     *
     * @return  bool
     */
    public function CheckTheme($sTheme) {

        return $this->SkinExists($this->GetConfigSkin() . '/' . $sTheme, false);
    }

    /**
     * Генерация ошибки "Шаблон не найден"
     *
     * @param   string $sMessage   - сообщение об ошибке
     * @param   string $sErrorInfo - доп. информация для записи в лог
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
    public function AddWidget($sGroup, $sName, $aParams = array(), $iPriority = null) {

        if (is_null($iPriority)) {
            $iPriority = (isset($aParams['priority']) ? $aParams['priority'] : 0);
        }

        $aWidgetData = array(
            'wgroup'   => $sGroup,
            'name'     => $sName,
            'priority' => $iPriority,
            'params'   => $aParams,
        );
        if (isset($aWidgetData['params']['id'])) {
            $aWidgetData['id'] = $aWidgetData['params']['id'];
            unset($aWidgetData['params']['id']);
        }

        $oWidget = E::ModuleWidget()->MakeWidget($aWidgetData);

        // Если тип виджета определен, то добавляем его
        if (!$oWidget->getType()) {
            return false;
        }

        // Добавляем виджет в массив дополнительных
        $this->aWidgetsAppend[$oWidget->getId()] = $oWidget;

        // Сбрасываем флаг сортировки
        $this->bWidgetsSorted = false;

        return true;
    }

    /**
     * Добавляет список виджетов
     *
     * @param   string $sGroup          - Группа виджетов
     * @param   array  $aWidgets        - Список добавляемых виджетов
     * @param   bool   $ClearWidgets    - Очищать или нет список виджетов, добавленных до этого, в данной группе
     * <pre>
     * E::ModuleViewer()->AddWidgets('right', array('tags', array('widget'=>'stream', 'priority'=>100)));
     * </pre>
     */
    public function AddWidgets($sGroup, $aWidgets, $ClearWidgets = true) {

        // * Удаляем ранее добавленые виджеты
        if ($ClearWidgets) {
            $this->ClearWidgets($sGroup);
        }
        foreach ($aWidgets as $sWidget) {
            if (is_array($sWidget)) {
                $this->AddWidget(
                    $sGroup,
                    $sWidget['widget'],
                    isset($sWidget['params']) ? $sWidget['params'] : array(),
                    isset($sWidget['priority']) ? $sWidget['priority'] : 0
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

        $this->aWidgets = array();
    }

    /**
     * Возвращает список виджетов
     *
     * @param bool $bSort - Выполнять или нет сортировку виджетов
     *
     * @return array
     */
    public function GetWidgets($bSort = true) {

        if ($this->aWidgetsAppend) {
            $this->AddWidgetsToList($this->aWidgetsAppend);
            $this->aWidgetsAppend = array();
        }

        if ($bSort && !$this->bWidgetsSorted) {
            $this->SortWidgets();
        }

        return $this->aWidgets;
    }

    /**
     * Определяет тип виджета
     *
     * @param   string $sName   - Название виджета
     * @param   string $sDir    - Путь до шаблона виджета, обычно определяется автоматически для плагинов,
     *                            если передать параметр 'plugin'=>'myplugin'
     * @param   string $sPlugin - Имя плагина виджета, берется из параметра 'plugin'=>'myplugin'
     *
     * @return  string ('exec', 'block', 'template', '')
     */
    public function DefineWidgetType($sName, $sDir = null, $sPlugin = null) {

        // Добавляем проверку на рсширение, чтобы не делать лишних телодвижений
        $bTpl = (substr($sName, -4) == '.tpl');
        if (!$bTpl) {
            if (E::ModuleWidget()->FileClassExists($sName, $sPlugin)) {
                // Если найден файл класса виджета, то это исполняемый виджет
                return array('type' => 'exec');
            }
        }
        if (strpos($sName, 'block.') && ($sTplName = $this->TemplateExists(is_null($sDir) ? $sName : rtrim($sDir, '/') . '/' . ltrim($sName, '/')))) {
            // * LS-compatible * //
            return array('type' => 'template', 'name' => $sTplName);
        }

        if (!is_null($sDir)) {
            $sDir = rtrim($sDir, '/') . '/';
        }
        $aCheckNames = array(
            $sDir . 'tpls/widgets/widget.' . $sName,
            $sDir . ltrim($sName, '/'),
            $sDir . 'widgets/widget.' . $sName,
        );
        foreach ($aCheckNames as $sCheckName) {
            if ($sTplName = $this->TemplateExists($sCheckName)) {
                // Если найден шаблон, то считаем, что это шаблонный виджет
                return array('type' => 'template', 'name' => $sTplName);
            }
        }

        // Считаем что тип не определен
        //F::SysWarning('Can not define type of widget "' . $sName . '"');

        return array('type' => null);
    }


    /**
     * Вспомагательная функция для сортировки виджетов:
     *  - первыми идут с приоритетом 'top'
     *  - потом те, у кого выше приоритет
     *  - потом те, которые были раньше добавлены
     *
     * @param  ModuleWidget_EntityWidget $oW1
     * @param  ModuleWidget_EntityWidget $oW2
     *
     * @return int
     */
    protected function _SortWidgetsCompare($oW1, $oW2) {

        if ($oW1->getPriority() === $oW2->getPriority()) {
            return $oW1->getOrder() - $oW2->getOrder();
        } elseif ($oW1->isTop()) {
            return 1;
        } elseif ($oW2->isTop()) {
            return -1;
        }
        if ($oW1->getPriority() < $oW2->getPriority()) {
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

    /**
     * Make lists of widgets (separated by groups)
     */
    protected function MakeWidgetsLists() {

        // Load widgets from config files
        $aWidgets = E::ModuleWidget()->GetWidgets();
        $iCount = $this->AddWidgetsToList($aWidgets);

        // Check widgets added from actions
        if ($this->aWidgetsAppend) {
            $iCount += $this->AddWidgetsToList($this->aWidgetsAppend);
            $this->aWidgetsAppend = array();
        }

        if ($iCount) {
            $this->SortWidgets();
        }
    }

    /**
     * Adds widgets from array to current lists
     *
     * @param array $aWidgets
     *
     * @return int
     */
    protected function AddWidgetsToList($aWidgets) {

        $iCount = 0;
        /** @var ModuleWidget_EntityWidget $oWidget */
        foreach ($aWidgets as $oWidget) {
            $sGroup = $oWidget->getGroup();
            if (!$sGroup) {
                // group not defined
                $sGroup = '-';
            }
            // Свойство "order" потребуется для сортировки по порядку добавления, если не задан приоритет
            if (!$oWidget->getOrder()) {
                $oWidget->setOrder(isset($this->aWidgets[$sGroup]) ? sizeof($this->aWidgets[$sGroup]) : 0);
            }

            // if widget must be displayed then we add it to arrays
            if ($oWidget->isDisplay()) {
                // Список всех виджетов, в т.ч. и без группы
                $this->aWidgets['_all_'][$oWidget->GetId()] = $oWidget;
                // Список виджетов с разбивкой по круппам (чтоб не дублировать, сохраняем ссылку на элемент в общем списке)
                $this->aWidgets[$sGroup][$oWidget->GetId()] = & $this->aWidgets['_all_'][$oWidget->GetId()];
                $iCount += 1;
            }
        }
        return $iCount;
    }

    /**
     * Инициализирует параметры вывода js- и css- файлов
     */
    public function InitAssetFiles() {

        if ($this->aFilesPrepend['js']) {
            $this->aFilesPrepend['js'] = array_reverse($this->aFilesPrepend['js'], true);
        }
        if ($this->aFilesPrepend['css']) {
            $this->aFilesPrepend['css'] = array_reverse($this->aFilesPrepend['css'], true);
        }
        if ($this->aFilesPrepend['js'] || $this->aFilesPrepend['css']) {
            E::ModuleViewerAsset()->AddAssetFiles($this->aFilesPrepend);
            $this->aFilesPrepend = array();
        }

        // Compatibility with old style skins
        if ($aAssets = Config::Get('head.default')) {
            E::ModuleViewerAsset()->AddAssetFiles($aAssets);
        } else {
            E::ModuleViewerAsset()->AddAssetFiles(Config::Get('assets.default'));
        }

        if ($this->aFilesAppend['js'] || $this->aFilesAppend['css']) {
            E::ModuleViewerAsset()->AddAssetFiles($this->aFilesAppend);
        }
        $this->bAssetInit = true;
    }

    /**
     * Добавляет js-файл в конец списка
     *
     * @param string $sFile    - js-файл
     * @param array  $aParams  - Параметры, например, можно указать параметр 'name'=>'jquery.plugin.foo'
     *                           для исключения повторного добавления файла с таким именем
     * @param bool   $bReplace - заменять ли файл с таким же именем, если он уже есть
     *
     * @return bool
     */
    public function AppendScript($sFile, $aParams = array(), $bReplace = null) {

        if (is_null($bReplace)) {
            if (!isset($aParams['replace'])) {
                // default
                $bReplace = false;
            } else {
                $bReplace = (bool)$aParams['replace'];
            }
        }
        if (!$this->bAssetInit) {
            $aParams['replace'] = $bReplace;
            $this->aFilesAppend['js'][$sFile] = $aParams;
        } else {
            E::ModuleViewerAsset()->AppendJs($sFile, $aParams, $bReplace);
        }
    }

    /**
     * Добавляет js-файл в начало списка
     *
     * @param string $sFile    - js-файл
     * @param array  $aParams  - Параметры, например, можно указать параметр 'name'=>'jquery.plugin.foo'
     *                           для исключения повторного добавления файла с таким именем
     * @param bool   $bReplace - заменять ли файл с таким же именем, если он уже есть
     *
     * @return bool
     */
    public function PrependScript($sFile, $aParams = array(), $bReplace = null) {

        if (is_null($bReplace)) {
            if (!isset($aParams['replace'])) {
                // default
                $bReplace = false;
            } else {
                $bReplace = (bool)$aParams['replace'];
            }
        }
        if (!$this->bAssetInit) {
            $aParams['replace'] = $bReplace;
            $this->aFilesPrepend['js'][$sFile] = $aParams;
        } else {
            E::ModuleViewerAsset()->PrependJs($sFile, $aParams, $bReplace);
        }
    }

    /**
     * Добавляет css-файл в конец списка
     *
     * @param string $sFile    - css-файл стилей
     * @param array  $aParams  - Параметры, например, можно указать параметр 'name'=>'blueprint'
     *                           для исключения повторного добавления файла с таким именем
     * @param bool   $bReplace - заменять ли файл с таким же именем, если он уже есть
     *
     * @return bool
     */
    public function AppendStyle($sFile, $aParams = array(), $bReplace = null) {

        if (is_null($bReplace)) {
            if (!isset($aParams['replace'])) {
                // default
                $bReplace = false;
            } else {
                $bReplace = (bool)$aParams['replace'];
            }
        }
        if (!$this->bAssetInit) {
            $aParams['replace'] = $bReplace;
            $this->aFilesAppend['css'][$sFile] = $aParams;
        } else {
            E::ModuleViewerAsset()->AppendCss($sFile, $aParams, $bReplace);
        }
    }

    /**
     * Добавляет css-файл в начало списка
     *
     * @param string $sFile      - css-файл стилей
     * @param array  $aParams    - Параметры, например, можно указать параметр 'name'=>'blueprint'
     *                             для исключения повторного добавления файла с таким именем
     * @param bool   $bReplace   - заменять ли файл с таким же именем, если он уже есть
     *
     * @return bool
     */
    public function PrependStyle($sFile, $aParams = array(), $bReplace = null) {

        if (is_null($bReplace)) {
            if (!isset($aParams['replace'])) {
                // default
                $bReplace = false;
            } else {
                $bReplace = (bool)$aParams['replace'];
            }
        }
        if (!$this->bAssetInit) {
            $aParams['replace'] = $bReplace;
            $this->aFilesPrepend['css'][$sFile] = $aParams;
        } else {
            E::ModuleViewerAsset()->PrependCss($sFile, $aParams, $bReplace);
        }
    }

    /**
     * Готовит для подключения js-файл
     *
     * @param string $sFile
     * @param array  $aParams
     * @param bool   $bReplace
     */
    public function PrepareScript($sFile, $aParams = array(), $bReplace = null) {

        if (is_null($bReplace)) {
            if (!isset($aParams['replace'])) {
                // default
                $bReplace = false;
            } else {
                $bReplace = (bool)$aParams['replace'];
            }
        }
        if (is_array($aParams)) {
            $aParams['replace'] = $bReplace;
            $aParams['prepare'] = true;
        } else {
            $aParams = array('prepare' => true);
        }
        return E::ModuleViewerAsset()->AppendJs($sFile, $aParams, $bReplace);
    }

    /**
     * Готовит для подключения css-файл
     *
     * @param string $sFile
     * @param array  $aParams
     * @param bool   $bReplace
     */
    public function PrepareStyle($sFile, $aParams = array(), $bReplace = null) {

        if (is_null($bReplace)) {
            if (!isset($aParams['replace'])) {
                // default
                $bReplace = false;
            } else {
                $bReplace = (bool)$aParams['replace'];
            }
        }
        if (is_array($aParams)) {
            $aParams['replace'] = $bReplace;
            $aParams['prepare'] = true;
        } else {
            $aParams = array('prepare' => true);
        }
        return E::ModuleViewerAsset()->AppendCss($sFile, $aParams, $bReplace);
    }

    /**
     * Строит массив для подключения css и js,
     * преобразовывает их в строку для HTML
     *
     */
    protected function BuildHeadFiles() {

        $sPath = R::GetPathWebCurrent();

        $this->aFileRules = Config::Get('head.rules');
        foreach ((array)$this->aFileRules as $sName => $aRule) {
            if (!$aRule['path']) continue;

            foreach ((array)$aRule['path'] as $sRulePath) {
                $sPattern = "~" . str_replace(array('/', '*'), array('\/', '\w+'), $sRulePath) . "~";
                if (preg_match($sPattern, $sPath)) {

                    // * Преобразование JS
                    if (isset($aRule['js']['empty']) && $aRule['js']['empty']) {
                        E::ModuleViewerAsset()->ClearJs();
                    }
                    if (isset($aRule['js']['exclude']) && is_array($aRule['js']['exclude'])) {
                        E::ModuleViewerAsset()->ExcludeJs($aRule['js']['exclude']);
                    }
                    if (isset($aRule['js']['include']) && is_array($aRule['js']['include'])) {
                        E::ModuleViewerAsset()->AddJsFiles($aRule['js']['include']);
                    }

                    // * Преобразование CSS
                    if (isset($aRule['css']['empty']) && $aRule['css']['empty']) {
                        E::ModuleViewerAsset()->ClearCss();
                    }
                    if (isset($aRule['css']['exclude']) && is_array($aRule['css']['exclude'])) {
                        E::ModuleViewerAsset()->ExcludeCss($aRule['css']['exclude']);
                    }
                    if (isset($aRule['css']['include']) && is_array($aRule['css']['include'])) {
                        E::ModuleViewerAsset()->AddCssFiles($aRule['css']['include']);
                    }

                    // * Продолжаем поиск
                    if (isset($aRule['stop'])) {
                        break(2);
                    }
                }
            }
        }

        E::ModuleViewerAsset()->Prepare();


        // * Объединяем файлы в наборы
        $aHeadFiles = array('js' => array(), 'css' => array());

        // * Получаем HTML код
        $aHtmlHeadFiles = $this->BuildHtmlHeadFiles($aHeadFiles);
        $this->SetHtmlHeadFiles($aHtmlHeadFiles);
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

    /**
     * Строит массив HTML-ссылок на ресурсы
     *
     * @param $aHeadFiles
     * @return array
     */
    protected function BuildHtmlHeadFiles($aHeadFiles) {

        foreach($aHeadFiles as $sType => $aFiles) {
            $aHeaderLinks = E::ModuleViewerAsset()->BuildHtmlLinks($sType);
            if (isset($aHeaderLinks[$sType]) && $aHeaderLinks[$sType]) {
                $aHeadFiles[$sType] = join(PHP_EOL, $aHeaderLinks[$sType]);
            } else {
                $aHeadFiles[$sType] = '';
            }
        }
        return $aHeadFiles;
    }

    /**
     * Устанавливает список файлов для вывода в хидере страницы
     *
     * @param array $aText    Список файлов
     */
    public function SetHtmlHeadFiles($aText) {

        $aCfg = array(
            'url' => array(
                'root' => Config::Get('path.root.url'), // реальный рут сайта
                'ajax' => R::Url('base'), // адрес для ajax-запросов
            ),
            'assets' => E::ModuleViewerAsset()->GetPreparedAssetLinks(),
            'lang' => Config::Get('lang.current'),
            'wysiwyg' => Config::Get('view.wysiwyg') ? true : false,
        );

        $sScript = 'var ls = ls || { };' . PHP_EOL;
        $sScript .= 'ls.cfg = ' . F::JsonEncode($aCfg) . ';' . PHP_EOL;
        $sScript = '<script>' . $sScript . '</script>' . PHP_EOL;

        if (isset($aText['js'])) {
            $aText['js'] = $sScript . $aText['js'];
        } else {
            $aText['js'] = $sScript;
        }
        $this->aHtmlHeadFiles = $aText;
    }

    /**
     * Добавляет тег для вывода в <HEAD>
     *
     * @param string $sTag
     */
    public function AddHtmlHeadTag($sTag) {

        if ($sTag[0] == '<') {
            $sTag = substr($sTag, 1);
        }
        if (substr($sTag, -2) == '/>') {
            $sTag = substr($sTag, 0, strlen($sTag) - 2);
        } elseif (substr($sTag, -1) == '>') {
            $sTag = substr($sTag, 0, strlen($sTag) - 1);
        }

        if (strpos($sTag, ' ')) {
            list($sTagName, $sAttributes) = explode(' ', $sTag, 2);
        } else {
            $sTagName = $sTag;
            $sAttributes = array();
        }
        $this->SetHtmlHeadTag($sTagName, $sAttributes);
    }

    /**
     * @param string       $sTagName
     * @param array|string $xAttributes
     * @param string|bool  $xContent
     */
    public function SetHtmlHeadTag($sTagName, $xAttributes, $xContent = false) {

        $sTagName = strtolower($sTagName);

        $sKey = '';
        $sTag = '<' . $sTagName;
        if (is_string($xAttributes)) {
            $sTag .= ' ' . $xAttributes;
        } elseif (is_array($xAttributes) && sizeof($xAttributes)) {
            $aAttrs = array();
            foreach ($xAttributes as $sName => $sValue) {
                if (is_string($sName)) {
                    $aAttrs[strtolower($sName)] = $sValue;
                } else {
                    $aAttrs[] = $sValue;
                }
            }

            if ($sTagName == 'meta') {
                foreach ($this->aSpecMetaTagsAttr as $sName) {
                    if (isset($aAttrs[$sName])) {
                        // defines special key for meta tags
                        $sKey = $sTagName . ' ' . $sName . '=' . $aAttrs[$sName];
                        break;
                    }
                }
            }

            if ($aAttrs) {
                $sAttr = '';
                foreach ($aAttrs as $sName => $sValue) {
                    if (is_string($sName)) {
                        $sAttr .= ' ' . $sName . '="' . str_replace('"', '&quot;', $sValue) . '"';
                    } else {
                        $sAttr .= ' ' . htmlspecialchars($sValue, ENT_NOQUOTES | ENT_HTML5, 'UTF-8');
                    }
                }
                if ($sAttr) {
                    $sTag .= $sAttr;
                }
            }
        }
        $sTag .= '>';
        // adds content and closing tag
        if ($xContent !== false) {
            $sTag .= htmlspecialchars($xContent);
            $sTag .= '</' . $sTagName . '>';
        }
        // prevents duplication
        if ($sKey) {
            $this->aHtmlHeadTags[$sKey] = $sTag;
        } else {
            $this->aHtmlHeadTags[$sTag] = $sTag;
        }
    }

    /**
     * Additional tags for <head>
     *
     * @param $aParams
     */
    public function SetHtmlHeadTags($aParams) {

        foreach($aParams as $aTag) {
            if (is_string($aTag)) {
                $this->AddHtmlHeadTag($aTag);
            } elseif (is_array($aTag)) {
                $this->SetHtmlHeadTag($aTag[0], isset($aTag[1]) ? $aTag[1] : null, isset($aTag[2]) ? $aTag[2] : false);
            }
        }
    }

    /**
     * Returns all additional tags for <head>
     *
     * @return array
     */
    public function GetHtmlHeadTags() {

        return $this->aHtmlHeadTags;
    }

    /**
     * Clears all additional tags for <head>
     *
     */
    public function ClearHtmlHeadTags() {

        $this->aHtmlHeadTags = array();
    }

    /**
     * Устанавливаем заголовок страницы (тег title)
     *
     * @param string $sText    Заголовок
     */
    public function SetHtmlTitle($sText) {

        $this->aHtmlTitles = array($sText);
    }

    /**
     * Добавляет часть заголовка страницы через разделитель
     *
     * @param string $sText    Заголовок
     */
    public function AddHtmlTitle($sText) {

        $this->aHtmlTitles[] = $sText;
    }

    /**
     * Возвращает текущий заголовок страницы
     *
     * @param bool $bHtmlEncode    Convert special characters of title to HTML entities
     *
     * @return string
     */
    public function GetHtmlTitle($bHtmlEncode = true) {

        $aTitles = array_reverse($this->aHtmlTitles);
        if ($this->iHtmlTitlesMax && sizeof($aTitles) > $this->iHtmlTitlesMax) {
            $aTitles = array_splice($aTitles, 0, $this->iHtmlTitlesMax);
        }
        if (Config::Get('view.html.title')) {
            // required part of the tag <title>
            if (sizeof($aTitles) && (end($aTitles) != Config::Get('view.html.title'))) {
                $aTitles[] = Config::Get('view.html.title');
            }
        }
        $sHtmlTitle = join($this->sHtmlTitleSeparator, $aTitles);
        if ($bHtmlEncode) {
            $sHtmlTitle = htmlspecialchars($sHtmlTitle);
        }
        return $sHtmlTitle;
    }

    /**
     * Устанавливает ключевые слова для мета-тега keywords
     *
     * @param string $sText    ключевые слова
     */
    public function SetHtmlKeywords($sText) {

        $aKeywords = array_map('trim', explode(',', $sText));
        $this->aHtmlKeywords = $aKeywords;
    }

    /**
     * Returns string with keywirds
     *
     * @param bool $bHtmlEncode
     *
     * @return string
     */
    public function GetHtmlKeywords($bHtmlEncode = true) {

        $aKeywords = $this->aHtmlKeywords;
        if ($bHtmlEncode) {
            $aKeywords = array_map('htmlspecialchars', $aKeywords);
        }
        return join(', ', $aKeywords);
    }

    /**
     * Устанавливает описание страницы для мета-тега desciption
     *
     * @param string $sText    Описание
     */
    public function SetHtmlDescription($sText) {

        $this->sHtmlDescription = $sText;
    }

    /**
     * Returns description for HTML
     *
     * @param bool $bHtmlEncode
     *
     * @return string
     */
    public function GetHtmlDescription($bHtmlEncode = true) {

        if ($bHtmlEncode) {
            return htmlspecialchars($this->sHtmlDescription);
        } else {
            return $this->sHtmlDescription;
        }
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
     * @param string $sUrl   URL
     * @param string $sTitle Заголовок
     */
    public function SetHtmlRssAlternate($sUrl, $sTitle) {

        $this->aHtmlRssAlternate['title'] = htmlspecialchars($sTitle);
        $this->aHtmlRssAlternate['url'] = htmlspecialchars($sUrl);
    }

    /**
     * Формирует постраничный вывод
     *
     * @param int    $iCount         Общее количество элементов
     * @param int    $iCurrentPage   Текущая страница
     * @param int    $iCountPerPage  Количество элементов на одну страницу
     * @param int    $iCountPageLine Количество ссылок на другие страницы
     * @param string $sBaseUrl       Базовый URL, к нему будет добавлять постикс /pageN/  и GET параметры
     * @param array  $aGetParamsList Список GET параметров, которые необходимо передавать при постраничном переходе
     *
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

        $iMin = $iCurrentPage - floor($iCountPageLine / 2);
        if ($iMin < 1) {
            $iMin = 1;
        }
        $iMax = $iMin + $iCountPageLine - 1;
        if ($iMax > $iCountPage) {
            $iMax = $iCountPage;
        }
        if ($iMax - $iMin < $iCountPageLine) {
            $iMin = $iMax - $iCountPageLine + 1;
            if ($iMin < 1) {
                $iMin = 1;
            }
        }

        $aPagesLeft = array();
        $aPagesRight = array();
        for ($i = $iMin; $i <= $iMax; $i++) {
            if ($i < $iCurrentPage) {
                $aPagesLeft[] = $i;
            } elseif ($i > $iCurrentPage) {
                $aPagesRight[] = $i;
            }
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
     * @param string $sType       - Тип шаблона/ресурса
     * @param string $sName       - Имя шаблона - имя файла
     * @param string $sContent    -  Возврат содержания шаблона при return true;
     * @param int    $iTimestamp  -  Возврат даты модификации шаблона при return true;
     * @param Smarty $oSmarty     -  Объект Smarty
     *
     * @return string|bool
     */
    public function SmartyDefaultTemplateHandler($sType, $sName, &$sContent, &$iTimestamp, $oSmarty) {
        /**
         * Название шаблона может содержать, как полный путь до файла шаблона,
         * так и относительный любого из каталога в $oSmarty->getTemplateDir()
         * По дефолту каталоги такие: /templates/skin/[name]/ и /plugins/
         */
        /**
         * Задача: если это файл плагина для текущего шаблона, то смотрим этот же файл шаблона плагина в /default/
         */
        if ($this->GetConfigSkin() != 'default') {
            $sSkin = preg_quote($this->GetConfigSkin());
            if (preg_match('@^/plugins/([\w\-_]+)/templates/skin/' . $sSkin . '/(.+)$/@i', $sName, $aMatch)) {
                // => /root/plugins/[plugin name]/templates/skin/[skin name]/dir/test.tpl
                $sPluginDir = Plugin::GetDir($aMatch[1]);
                $sTemplateFile = $aMatch[2];

            } elseif (preg_match('@^([\w\-_]+)/templates/skin/' . $sSkin . '/(.+)$/@i', $sName, $aMatch)) {
                // => [plugin name]/templates/skin/[skin name]/dir/test.tpl
                $sPluginDir = Plugin::GetDir($aMatch[1]);
                $sTemplateFile = $aMatch[2];

            } else {
                $sPluginDir = '';
                $sTemplateFile = '';
            }
            if ($sPluginDir && $sTemplateFile) {
                $sFile = $sPluginDir . '/templates/skin/default/' . $sTemplateFile;
                if ($this->TemplateExists($sFile)) {
                    return $sFile;
                }
            }
        }
        return false;
    }

    /**
     * Clear all viewer's temporary & cache files
     */
    public function ClearAll() {

        $this->ClearSmartyFiles();
        $this->ClearAssetsFiles();
    }

    /**
     * Clear all cached and compiled files of Smarty
     */
    public function ClearSmartyFiles() {

        F::File_ClearDir(Config::Get('path.smarty.compiled'));
        F::File_ClearDir(Config::Get('path.smarty.cache'));
        F::File_ClearDir(Config::Get('path.tmp.dir') . '/templates/');
    }

    public function ClearAssetsFiles() {

        $sDir = F::File_GetAssetDir();
        F::File_RemoveDir($sDir);
        E::ModuleViewerAsset()->ClearAssetsCache();
    }

    /**
     * Загружаем переменные в шаблон при завершении модуля
     *
     */
    public function Shutdown() {

        // Calculation of preprocess time is inside this method
        $this->_initRender();

        $nTimer = microtime(true);

        // * Создаются списки виджетов для вывода
        $this->MakeWidgetsLists();

        // * Добавляем JS и CSS по предписанным правилам
        $this->BuildHeadFiles();

        // * Передача переменных в шаблон
        $this->VarAssign();

        // * Рендерим меню для шаблонов и передаем контейнеры в шаблон
        $this->BuildMenu();
        $this->MenuVarAssign();

        self::$_preprocessTime += microtime(true) - $nTimer;
    }

}

// EOF
