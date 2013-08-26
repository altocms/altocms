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

F::IncludeFile(__DIR__ . '/LangArray.class.php');

/**
 * Модуль поддержки языковых файлов
 *
 * @package engine.modules
 * @since   1.0
 */
class ModuleLang extends Module {

    const LANG_PATTERN = '%%lang%%';

    /**
     * Текущий язык ресурса
     *
     * @var string
     */
    protected $sCurrentLang;

    /**
     * Язык ресурса, используемый по умолчанию
     *
     * @var string
     */
    protected $sDefaultLang;

    /**
     * Путь к языковым файлам
     *
     * @var string
     */
    protected $aLangPaths;

    /**
     * Список языковых текстовок
     *
     * @var array
     */
    protected $aLangMsg = array();

    /**
     * Список текстовок для JS
     *
     * @var array
     */
    protected $aLangMsgJs = array();

    /**
     * Инициализация модуля
     *
     */
    public function Init() {

        $this->Hook_Run('lang_init_start');

        $this->sDefaultLang = Config::Get('lang.default');
        $this->aLangPaths = F::File_NormPath(Config::Get('lang.paths'));

        // Проверку на языки делаем, только если сайт мультиязычный
        if (Config::Get('lang.multilang')) {
            // Время хранение языка в куках
            $nSavePeriod = F::ToSeconds(Config::Get('lang.save'));
            $sLangKey = (is_string(Config::Get('lang.in_get')) ? Config::Get('lang.in_get') : 'lang');

            // Получаем язык, если он был задан в URL
            $this->sCurrentLang = Router::GetLang();

            // Проверка куки, если требуется
            if (!$this->sCurrentLang && $nSavePeriod) {
                $sLang = (string)$this->Session_GetCookie($sLangKey);
                if ($sLang) {
                    $this->sCurrentLang = $sLang;
                }
            }
            if (!$this->sCurrentLang) {
                $this->sCurrentLang = Config::Get('lang.current');
            }
        } else {
            $this->sCurrentLang = Config::Get('lang.current');
        }
        // Проверяем на случай старого обозначения языков
        $this->sDefaultLang = $this->_checkLang($this->sDefaultLang);
        $this->sCurrentLang = $this->_checkLang($this->sCurrentLang);

        if ($this->sCurrentLang && Config::Get('lang.multilang') && $nSavePeriod) {
            // Пишем в куки, если требуется
            $this->Session_SetCookie($sLangKey, $this->sCurrentLang, $nSavePeriod);
        }

        $this->InitLang();
    }

    protected function _checkLang($sLang) {

        if (!UserLocale::getLocale($sLang)) {
            $aLangs = UserLocale::getAvailableLanguages();
            if (!isset($aLangs[$sLang])) {
                // Возможно в $sLang полное название языка, поэтому проверяем
                foreach($aLangs as $sLangCode=>$aLangInfo) {
                    if (strtolower($sLang) == strtolower($aLangInfo['name'])) {
                        return $sLangCode;
                    }
                }
            }
        }
        return $sLang;
    }

    public function __get($sName) {

        if (substr($sName, 0, 1) == '_') {
            $sKey = substr($sName, 1);
        }
        else {
            $sKey = $sName;
        }
        return $this->Get($sKey);
    }

    /**
     * Инициализирует языковой файл
     *
     */
    protected function InitLang($sLang = null) {

        if (!$sLang) {
            $sLang = $this->sCurrentLang;
        }

        UserLocale::setLocale(
            Config::Get('lang.current'),
            array('locale' => Config::get('i18n.locale'), 'timezone' => Config::get('i18n.timezone'))
        );

        // * Если используется кеширование через memcaсhed, то сохраняем данные языкового файла в кеш
        if (Config::Get('sys.cache.type') == 'memory') {
            if (false === ($this->aLangMsg = $this->Cache_Get("lang_{$sLang}_" . Config::Get('view.skin')))) {
                $this->aLangMsg = array();
                $this->LoadLangFiles($this->sDefaultLang);
                if ($sLang != $this->sDefaultLang) {
                    $this->LoadLangFiles($sLang);
                }
                $this->Cache_Set($this->aLangMsg, "lang_{$sLang}_" . Config::Get('view.skin'), array(), 60 * 60);
            }
        } else {
            $this->LoadLangFiles($this->sDefaultLang);
            if ($sLang != $this->sDefaultLang) {
                $this->LoadLangFiles($sLang);
            }
        }
        if ($sLang != Config::Get('lang.current')) {
            //Config::Set('lang.current', $sLang);
        }
        $this->LoadLangJs();
    }

    /**
     * Загружает из конфига текстовки для JS
     *
     */
    protected function LoadLangJs() {

        $aMsg = Config::Get('lang.load_to_js');
        if (is_array($aMsg) && count($aMsg)) {
            $this->aLangMsgJs = $aMsg;
        }
    }

    /**
     * Прогружает в шаблон текстовки в виде js
     *
     */
    protected function AssignToJs() {

        $aLangMsg = array();
        foreach ($this->aLangMsgJs as $sName) {
            $aLangMsg[$sName] = $this->Get($sName, array(), false);
        }
        $this->Viewer_Assign('aLangJs', $aLangMsg);
    }

    /**
     * Добавляет текстовку к js
     *
     * @param array $aKeys    Список текстовок
     */
    public function AddLangJs($aKeys) {

        if (!is_array($aKeys)) {
            $aKeys = array($aKeys);
        }
        $this->aLangMsgJs = array_merge($this->aLangMsgJs, $aKeys);
    }

    /**
     * Make file list for loading
     *
     * @param      $aPaths
     * @param      $sPattern
     * @param      $sLang
     * @param bool $bExactMatch
     * @param bool $bCheckAliases
     *
     * @return array
     */
    public function _makeFileList($aPaths, $sPattern, $sLang, $bExactMatch = true, $bCheckAliases = true) {

        if (!is_array($aPaths)) {
            $aPaths = array((string)$aPaths);
        }

        $aResult = array();
        foreach ($aPaths as $sPath) {
            $sPathPattern = $sPath . '/' . $sPattern;
            $sLangFile = str_replace(static::LANG_PATTERN, $sLang, $sPathPattern);

            if ($bExactMatch) {
                if (F::File_Exists($sLangFile)) {
                    $aResult[] = $sLangFile;
                }
            } else {
                if ($aFiles = glob($sLangFile)) {
                    $aResult = array_merge($aResult, $aFiles);
                }
            }
            if (!$aResult && $bCheckAliases && ($aAliases = F::Str2Array(Config::Get('lang.aliases.' . $sLang)))) {
                //If the language file is not found, then check its aliases
                foreach ($aAliases as $sLangAlias) {
                    $aSubResult = $this->_makeFileList($aPaths, $sPattern, $sLangAlias, $bExactMatch, false);
                    if ($aSubResult) {
                        $aResult = array_merge($aResult, $aSubResult);
                        break;
                    }
                }
            }
        }
        return $aResult;
    }

    /**
     * Loads language files from path
     *
     * @param string|array $xPath
     * @param string $sLang
     * @param array  $aParams
     */
    protected function _loadFiles($xPath, $sLang, $aParams = null) {

        $aFiles = $this->_makeFileList($xPath, static::LANG_PATTERN . '.php', $sLang);
        foreach ($aFiles as $sLangFile) {
            $this->AddMessages(F::File_IncludeFile($sLangFile), $aParams);
        }
    }

    /**
     * Load several files by pattern
     *
     * @param string|array $xPath
     * @param string $sMask
     * @param string $sLang
     * @param string $sPrefix
     */
    protected function _loadFileByMask($xPath, $sMask, $sLang, $sPrefix) {

        $aFiles = $this->_makeFileList($xPath, $sMask, $sLang, false);
        if ($aFiles) {
            foreach ($aFiles as $sLangFile) {
                $sDirModule = basename(dirname($sLangFile));
                $aResult = F::File_IncludeFile($sLangFile);
                if ($aResult) {
                    $this->AddMessages($aResult, array('category' => $sPrefix, 'name' => $sDirModule));
                }
            }
        }
    }

    /**
     * Загружает текстовки из языковых файлов
     *
     * @param $sLangName    Язык для загрузки
     */
    protected function LoadLangFiles($sLangName) {

        // Подключаем основной языковой файл
        $this->_loadFiles($this->aLangPaths, $sLangName);

        // * Ищем языковые файлы модулей и объединяем их с текущим
        $this->_loadFileByMask($this->aLangPaths, '/modules/*/' . static::LANG_PATTERN . '.php', $sLangName, 'module');

        // * Ищет языковые файлы экшенов и объединяет их с текущим
        $this->_loadFileByMask($this->aLangPaths, '/actions/*/' . static::LANG_PATTERN . '.php', $sLangName, 'action');

        // * Ищем языковые файлы активированных плагинов
        if ($aPluginList = F::GetPluginsList()) {
            foreach ($aPluginList as $sPluginName) {
                $sDir = Plugin::GetDir($sPluginName);
                $aParams = array('name' => $sPluginName, 'category' => 'plugin');
                $this->_loadFiles($sDir . '/templates/language/', $sLangName, $aParams);
            }

        }
        // * Ищет языковой файл текущего шаблона
        $this->LoadLangFileTemplate($sLangName);
    }

    /**
     * Загружает языковой файл текущего шаблона
     *
     * @param string $sLangName    Язык для загрузки
     */
    public function LoadLangFileTemplate($sLangName) {

        $this->_loadFiles(Config::Get('path.smarty.template') . '/settings/language/', $sLangName);
    }

    /**
     * Установить текущий язык
     *
     * @param string $sLang    Название языка
     */
    public function SetLang($sLang) {

        $this->sCurrentLang = $sLang;
        $this->InitLang();
    }

    /**
     * Получить текущий язык
     *
     * @return string
     */
    public function GetLang() {

        return $this->sCurrentLang;
    }

    /**
     * Получить алиасы текущего языка
     *
     * @return array
     */
    public function GetLangAliases() {

        return F::Str2Array(Config::Get('lang.aliases.' . $this->GetLang()));
    }

    /**
     * Получить язык по умолчанию
     *
     * @return string
     */
    public function GetDefaultLang() {

        return $this->sDefaultLang;
    }

    /**
     * Получить алиасы языка по умолчанию
     *
     * @return array
     */
    public function GetDefaultLangAliases() {

        return F::Str2Array(Config::Get('lang.aliases.' . $this->GetDefaultLang()));
    }

    /**
     * Получить дефолтный язык
     *
     * @return string
     */
    public function GetLangDefault() {

        return $this->GetDefaultLang();
    }

    /**
     * Получить список текстовок
     *
     * @return array
     */
    public function GetLangMsg() {

        return $this->aLangMsg;
    }

    public function GetLangArray() {

        return new LangArray();
    }

    /**
     * Получает текстовку по её имени
     *
     * @param  string $sName       Имя текстовки
     * @param  array  $aReplace    Список параметром для замены в текстовке
     * @param  bool   $bDelete     Удалять или нет параметры, которые не были заменены
     *
     * @return string
     */
    public function Get($sName, $aReplace = array(), $bDelete = true) {

        if (strpos($sName, '.')) {
            $sLang = $this->aLangMsg;
            $aKeys = explode('.', $sName);
            foreach ($aKeys as $k) {
                if (isset($sLang[$k])) {
                    $sLang = $sLang[$k];
                } else {
                    //return  'NOT_FOUND_LANG_TEXT';
                    return strtoupper($sName);
                }
            }
        } else {
            if (isset($this->aLangMsg[$sName])) {
                $sLang = $this->aLangMsg[$sName];
            } else {
                //return 'NOT_FOUND_LANG_TEXT';
                return strtoupper($sName);
            }
        }

        if (is_array($aReplace) && count($aReplace) && is_string($sLang)) {
            foreach ($aReplace as $sFrom => $sTo) {
                $aReplacePairs["%%{$sFrom}%%"] = $sTo;
            }
            $sLang = strtr($sLang, $aReplacePairs);
        }

        if (Config::Get('module.lang.delete_undefined') && $bDelete && is_string($sLang)) {
            $sLang = preg_replace('|\%\%[\S]+\%\%|U', '', $sLang);
        }
        return $sLang;
    }

    /**
     * Добавить к текстовкам массив сообщений
     *
     * @param array      $aMessages     - Список текстовок для добавления
     * @param array|null $aParams       - Параметры, позволяют хранить текстовки в структурированном виде,
     *                               например, тестовки плагина "test" получать как Get('plugin.name.test')
     */
    public function AddMessages($aMessages, $aParams = null) {

        if (is_array($aMessages)) {
            if (isset($aParams['name'])) {
                $sMsgs = $aMessages;
                if (isset($aParams['category'])) {
                    if (isset($this->aLangMsg[$aParams['category']][$aParams['name']])) {
                        $sMsgs = array_merge($this->aLangMsg[$aParams['category']][$aParams['name']], $sMsgs);
                    }
                    $this->aLangMsg[$aParams['category']][$aParams['name']] = $sMsgs;
                } else {
                    if (isset($this->aLangMsg[$aParams['name']])) {
                        $sMsgs = array_merge($this->aLangMsg[$aParams['name']], $sMsgs);
                    }
                    $this->aLangMsg[$aParams['name']] = $sMsgs;
                }
            } else {
                $this->aLangMsg = array_merge($this->aLangMsg, $aMessages);
            }
        }
    }

    /**
     * Добавить к текстовкам отдельное сообщение
     *
     * @param   string $sKey       - Имя текстовки
     * @param   string $sMessage   - Значение текстовки
     */
    public function AddMessage($sKey, $sMessage) {

        $this->aLangMsg[$sKey] = $sMessage;
    }

    public function Dictionary($sLang = null) {

        if ($sLang && $sLang !== $this->sCurrentLang) {
            $this->InitLang($sLang);
        }
        return $this;
    }

    /**
     * Возвращает список языков сайта
     *
     * @return array
     */
    public function GetLangList() {

        $aLangList = (array)Config::Get('lang.allow');
        if (!$aLangList) {
            $aLangList = array(Config::Get('lang.current'));
        }
        if (!$aLangList) {
            $aLangList = array(Config::Get('lang.default'));
        }
        if (!$aLangList) {
            $aLangList = array('ru');
        }
        return $aLangList;
    }

    /**
     * Возвращает список доступных языков
     */
    public function GetAvailableLanguages() {

        $aLanguages = UserLocale::getAvailableLanguages(true);
        foreach ($aLanguages as $sLang=>$aLang) {
            if (!isset($aLang['aliases']) && isset($aLang['name'])) {
                $aLanguages[$sLang]['aliases'] = strtolower($aLang['name']);
            }
        }
        return $aLanguages;
    }

    /**
     * Завершаем работу модуля
     *
     */
    public function Shutdown() {

        // * Делаем выгрузку необходимых текстовок в шаблон в виде js
        $this->AssignToJs();
        if (Config::Get('lang.multilang')) {
            $this->Viewer_AddHtmlHeadTag(
                '<link rel="alternate" hreflang="x-default" href="' . Router::Url('link') . '">'
            );
            $aLangs = Config::Get('lang.allow');
            foreach ($aLangs as $sLang) {
                $this->Viewer_AddHtmlHeadTag(
                    '<link rel="alternate" hreflang="' . $sLang . '" href="' . trim(F::File_RootUrl($sLang), '/')
                        . Router::Url('path') . '">'
                );
            }
        }
    }
}

// EOF