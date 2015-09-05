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
 * Абстрактный класс экшена.
 *
 * От этого класса наследуются все экшены в движке.
 * Предоставляет базовые метода для работы с параметрами и шаблоном при запросе страницы в браузере.
 *
 * @package engine
 * @since   1.0
 */
abstract class Action extends LsObject {
    /**
     * Список зарегистрированных евентов
     *
     * @var array
     */
    protected $aRegisterEvent = array();
    /**
     * Список параметров из URL
     * <pre>/action/event/param0/param1/../paramN/</pre>
     *
     * @var array
     */
    protected $aParams = array();
    /**
     * Список совпадений по регулярному выражению для евента
     *
     * @var array
     */
    protected $aParamsEventMatch = array('event' => array(), 'params' => array());
    /**
     * Объект ядра
     *
     * @var Engine|null
     */
    protected $oEngine = null;
    /**
     * Шаблон экшена
     * @see SetTemplate
     * @see SetTemplateAction
     *
     * @var string|null
     */
    protected $sActionTemplate = null;
    /**
     * Дефолтный евент
     * @see SetDefaultEvent
     *
     * @var string|null
     */
    protected $sDefaultEvent = 'index';
    /**
     * Текущий евент
     *
     * @var string|null
     */
    protected $sCurrentEvent = null;
    /**
     * Имя текущий евента
     * Позволяет именовать экшены на основе регулярных выражений
     *
     * @var string|null
     */
    protected $sCurrentEventName = null;
    /**
     * Текущий экшен
     *
     * @var null|string
     */
    protected $sCurrentAction = null;

    protected static $bPost = null;

    /**
     * Конструктор
     *
     * @param Engine $oEngine Объект ядра
     * @param string $sAction Название экшена
     */
    public function __construct($oEngine, $sAction = null) {

        if (func_num_args() == 1 && is_string($oEngine)) {
            // Передан только экшен
            $this->oEngine = E::getInstance();
            $sAction = $oEngine;
        } else {
            // LS-compatible
            $this->oEngine = $oEngine;
        }
        //Engine::getInstance();
        $this->sCurrentAction = $sAction;
        $this->aParams = R::GetParams();
        $this->RegisterEvent();

        //Config::ResetLevel(Config::LEVEL_ACTION);
        // Get current config level
        $iConfigLevel = C::GetLevel();
        // load action's config if exists
        if ($sFile = F::File_Exists('/config/actions/' . $sAction . '.php', Config::Get('path.root.seek'))) {
            // Дополняем текущий конфиг конфигом экшена
            if ($aConfig = F::File_IncludeFile($sFile, true, true)) {
                // Текущий уровень конфига может быть как меньше LEVEL_ACTION,
                // так и больше. Нужно обновить все уровни
                if ($iConfigLevel <= Config::LEVEL_ACTION) {
                    $iMinLevel = $iConfigLevel;
                    $iMaxLevel = Config::LEVEL_ACTION;
                } else {
                    $iMinLevel = Config::LEVEL_ACTION;
                    $iMaxLevel = $iConfigLevel;
                }
                for ($iLevel = $iMinLevel; $iLevel <= $iMaxLevel; $iLevel++) {
                    if ($iLevel > $iConfigLevel) {
                        Config::ResetLevel(Config::LEVEL_ACTION);
                    }
                    Config::Load($aConfig, false, null, $iLevel, $sFile);
                }
            }
        } elseif ($iConfigLevel < Config::LEVEL_ACTION) {
            Config::ResetLevel(Config::LEVEL_ACTION);
        }
    }

    /**
     * Добавляет евент в экшен
     * По сути является оберткой для AddEventPreg(), оставлен для простоты и совместимости с прошлыми версиями ядра
     *
     * @see AddEventPreg
     *
     * @param string $sEventName     Название евента
     * @param string $sEventFunction Какой метод ему соответствует
     */
    protected function AddEvent($sEventName, $sEventFunction) {

        $this->AddEventPreg('/^' . preg_quote($sEventName) . '$/i', $sEventFunction);
    }

    /**
     * Добавляет евент в экшен, используя регулярное выражение для евента и параметров
     *
     */
    protected function AddEventPreg() {

        $iCountArgs = func_num_args();
        if ($iCountArgs < 2) {
            throw new Exception('Incorrect number of arguments when adding events');
        }
        $aEvent = array();
        /**
         * Последний параметр может быть массивом - содержать имя метода и имя евента(именованный евент)
         * Если указан только метод, то имя будет равным названию метода
         */
        $aNames = (array)func_get_arg($iCountArgs - 1);
        $aEvent['method'] = $aNames[0];
        if (isset($aNames[1])) {
            $aEvent['name'] = $aNames[1];
        } else {
            $aEvent['name'] = $aEvent['method'];
        }
        if (!$this->_eventExists($aEvent['method'])) {
            throw new Exception('Method of the event not found: ' . $aEvent['method']);
        }
        $aEvent['preg'] = func_get_arg(0);
        $aEvent['params_preg'] = array();
        for ($i = 1; $i < $iCountArgs - 1; $i++) {
            $aEvent['params_preg'][] = func_get_arg($i);
        }
        $this->aRegisterEvent[] = $aEvent;
    }

    protected function _eventExists($sEvent) {

        return method_exists($this, $sEvent);
    }

    /**
     * Запускает евент на выполнение
     * Если текущий евент не определен то  запускается тот которые определен по умолчанию(default event)
     *
     * @return mixed
     */
    public function ExecEvent() {

        if ($this->GetDefaultEvent() == 'index' && method_exists($this, 'EventIndex')) {
            $this->AddEvent('index', 'EventIndex');
        }
        $this->sCurrentEvent = R::GetActionEvent();
        if ($this->sCurrentEvent == null) {
            $this->sCurrentEvent = $this->GetDefaultEvent();
            R::SetActionEvent($this->sCurrentEvent);
        }
        foreach ($this->aRegisterEvent as $aEvent) {
            if (preg_match($aEvent['preg'], $this->sCurrentEvent, $aMatch)) {
                $this->aParamsEventMatch['event'] = $aMatch;
                $this->aParamsEventMatch['params'] = array();
                foreach ($aEvent['params_preg'] as $iKey => $sParamPreg) {
                    if (preg_match($sParamPreg, $this->GetParam($iKey, ''), $aMatch)) {
                        $this->aParamsEventMatch['params'][$iKey] = $aMatch;
                    } else {
                        continue 2;
                    }
                }
                $this->sCurrentEventName = $aEvent['name'];
                $sMethod = $aEvent['method'];
                $sHook = 'action_event_' . strtolower($this->sCurrentAction);

                E::ModuleHook()->Run($sHook . '_before', array('event' => $this->sCurrentEvent, 'params' => $this->GetParams()));
                //$result = call_user_func_array(array($this, $aEvent['method']), array());
                $xResult = $this->$sMethod();
                E::ModuleHook()->Run($sHook . '_after', array('event' => $this->sCurrentEvent, 'params' => $this->GetParams()));

                return $xResult;
            }
        }
        return $this->EventNotFound();
    }

    /**
     * Устанавливает евент по умолчанию
     *
     * @param string $sEvent Имя евента
     */
    public function SetDefaultEvent($sEvent) {

        $this->sDefaultEvent = $sEvent;
    }

    /**
     * Получает евент по умолчанию
     *
     * @return string
     */
    public function GetDefaultEvent() {

        return $this->sDefaultEvent;
    }

    /**
     * Возвращает элементы совпадения по регулярному выражению для евента
     *
     * @param int|null $iItem    Номер совпадения
     *
     * @return string|null
     */
    protected function GetEventMatch($iItem = null) {

        if ($iItem) {
            if (isset($this->aParamsEventMatch['event'][$iItem])) {
                return $this->aParamsEventMatch['event'][$iItem];
            } else {
                return null;
            }
        } else {
            return $this->aParamsEventMatch['event'];
        }
    }

    /**
     * Возвращает элементы совпадения по регулярному выражению для параметров евента
     *
     * @param int      $iParamNum    Номер параметра, начинается с нуля
     * @param int|null $iItem        Номер совпадения, начинается с нуля
     *
     * @return string|null
     */
    protected function GetParamEventMatch($iParamNum, $iItem = null) {

        if (!is_null($iItem)) {
            if (isset($this->aParamsEventMatch['params'][$iParamNum][$iItem])) {
                return $this->aParamsEventMatch['params'][$iParamNum][$iItem];
            } else {
                return null;
            }
        } else {
            if (isset($this->aParamsEventMatch['event'][$iParamNum])) {
                return $this->aParamsEventMatch['event'][$iParamNum];
            } else {
                return null;
            }
        }
    }

    /**
     * Получает параметр из URL по его номеру, если его нет то null
     *
     * @param   int    $iOffset    Номер параметра, начинается с нуля
     * @param   string $sDefault   - значение по умолчанию
     *
     * @return  mixed
     */
    public function GetParam($iOffset, $sDefault = null) {

        $iOffset = (int)$iOffset;
        return isset($this->aParams[$iOffset]) ? $this->aParams[$iOffset] : $sDefault;
    }

    /**
     * Получает последний парамет из URL
     *
     * @param   string|null $sDefault
     *
     * @return  string|null
     */
    protected function GetLastParam($sDefault = null) {

        $nNumParams = sizeof($this->GetParams());
        if ($nNumParams > 0) {
            $iOffset = $nNumParams - 1;
            return $this->GetParam($iOffset, $sDefault);
        }
        return null;
    }

    /**
     * Получает список параметров из УРЛ
     *
     * @return array
     */
    public function GetParams() {

        return $this->aParams;
    }

    /**
     * Установить значение параметра(эмуляция параметра в URL).
     * После установки занова считывает параметры из роутера - для корректной работы
     *
     * @param int    $iOffset Номер параметра, но по идеи может быть не только числом
     * @param string $value
     */
    public function SetParam($iOffset, $value) {

        R::SetParam($iOffset, $value);
        $this->aParams = R::GetParams();
    }

    /**
     * Устанавливает какой шаблон выводить
     *
     * @param string $sTemplate Путь до шаблона относительно общего каталога шаблонов
     */
    protected function SetTemplate($sTemplate) {

        $this->sActionTemplate = $sTemplate;
    }

    /**
     * Устанавливает какой шаблон выводить
     *
     * @param string $sTemplate Путь до шаблона относительно каталога шаблонов экшена
     */
    protected function SetTemplateAction($sTemplate) {

        if (substr($sTemplate, -4) != '.tpl') {
            $sTemplate = $sTemplate . '.tpl';
        }
        $sActionTemplatePath = $sTemplate;

        if (!F::File_IsLocalDir($sActionTemplatePath)) {
            // If not absolute path then defines real path of template
            $aDelegates = E::ModulePlugin()->GetDelegationChain('action', $this->GetActionClass());
            foreach ($aDelegates as $sAction) {
                if (preg_match('/^(Plugin([\w]+)_)?Action([\w]+)$/i', $sAction, $aMatches)) {
                    // for LS-compatibility
                    $sActionNameOriginal = $aMatches[3];
                    // New-style action templates
                    $sActionName = strtolower($sActionNameOriginal);
                    $sTemplatePath = E::ModulePlugin()->GetDelegate('template', 'actions/' . $sActionName . '/action.' . $sActionName . '.' . $sTemplate);
                    $sActionTemplatePath = $sTemplatePath;
                    if (!empty($aMatches[1])) {
                        $aPluginTemplateDirs = array(Plugin::GetTemplateDir($sAction));
                        if (basename($aPluginTemplateDirs[0]) !== 'default') {
                            $aPluginTemplateDirs[] = dirname($aPluginTemplateDirs[0]) . '/default/';
                        }

                        if ($sTemplatePath = F::File_Exists('tpls/' . $sTemplatePath, $aPluginTemplateDirs)) {
                            $sActionTemplatePath = $sTemplatePath;
                            break;
                        }
                        if ($sTemplatePath = F::File_Exists($sTemplatePath, $aPluginTemplateDirs)) {
                            $sActionTemplatePath = $sTemplatePath;
                            break;
                        }

                        // LS-compatibility
                        if (E::ModulePlugin()->IsActivePlugin('ls')) {
                            $sLsTemplatePath = E::ModulePlugin()->GetDelegate('template', 'actions/Action' . ucfirst($sActionName) . '/' . $sTemplate);
                            if ($sTemplatePath = F::File_Exists($sLsTemplatePath, $aPluginTemplateDirs)) {
                                $sActionTemplatePath = $sTemplatePath;
                                break;
                            }
                            $sLsTemplatePath = E::ModulePlugin()->GetDelegate('template', 'actions/Action' . ucfirst($sActionNameOriginal) . '/' . $sTemplate);
                            if ($sTemplatePath = F::File_Exists($sLsTemplatePath, $aPluginTemplateDirs)) {
                                $sActionTemplatePath = $sTemplatePath;
                                break;
                            }
                        }
                    }
                }
            }
        }

        $this->sActionTemplate = $sActionTemplatePath;
    }

    /**
     * Получить шаблон
     * Если шаблон не определен то возвращаем дефолтный шаблон евента: action/{Action}.{event}.tpl
     *
     * @return string
     */
    public function GetTemplate() {

        if (is_null($this->sActionTemplate)) {
            $this->SetTemplateAction($this->sCurrentEvent);
        }
        return $this->sActionTemplate;
    }

    /**
     * Получить каталог с шаблонами экшена(совпадает с именем класса)
     * @see Router::GetActionClass
     *
     * @return string
     */
    public function GetActionClass() {

        return R::GetActionClass();
    }

    /**
     * Возвращает имя евента
     *
     * @return null|string
     */
    public function GetCurrentEventName() {

        return $this->sCurrentEventName;
    }

    /**
     * Вызывается в том случаи если не найден евент который запросили через URL
     * По дефолту происходит перекидывание на страницу ошибки, это можно переопределить в наследнике
     *
     * @see Router::Action
     *
     * @return string
     */
    protected function EventNotFound() {

        return R::Action('error', '404');
    }

    /**
     * Выполняется при завершение экшена, после вызова основного евента
     *
     */
    public function EventShutdown() {

    }

    /**
     * Метод инициализации экшена
     * @return bool|string
     */
    public function Init() {

    }

    /**
     * Метод регистрации евентов.
     * В нём необходимо вызывать метод AddEvent($sEventName,$sEventFunction)
     *
     */
    protected function RegisterEvent() {

    }

    /**
     * Были ли ли переданы POST-параметры (или конкретный POST-параметр)
     *
     * @param   string|null $sName
     *
     * @return  bool
     */
    protected function IsPost($sName = null) {

        if (is_null(self::$bPost)) {
            if (E::ModuleSecurity()->ValidateSendForm(false)
                && isset($_SERVER['REQUEST_METHOD'])
                && ($_SERVER['REQUEST_METHOD'] == 'POST')
                && isset($_POST)
            ) {
                self::$bPost = true;
            } else {
                self::$bPost = false;
            }
        }
        if (self::$bPost) {
            if ($sName) {
                return array_key_exists($sName, $_POST);
            } else {
                return is_array($_POST);
            }
        }
        return false;
    }

    /**
     * Получает POST-параметры с валидацией формы
     *
     * @param   string|null $sName
     * @param   string|null $sDefault
     *
     * @return  mixed
     */
    protected function GetPost($sName = null, $sDefault = null) {

        if ($this->IsPost($sName)) {
            if ($sName) {
                return isset($_POST[(string)$sName]) ? $_POST[(string)$sName] : $sDefault;
            } else {
                return $_POST;
            }
        }
        return null;
    }

    /**
     * Returns information about the uploaded file with form validation
     * If a field name is omitted it returns the first of uploaded files
     *
     * @param   string|null $sName
     *
     * @return  array|bool
     */
    protected function GetUploadedFile($sName = null) {

        $aFileData = false;
        if (E::ModuleSecurity()->ValidateSendForm(false) && isset($_FILES)) {
            if (is_null($sName) && is_array($_FILES) && sizeof($_FILES)) {
                $aFileData = reset($_FILES);
            } elseif (isset($_FILES[$sName])) {
                $aFileData = $_FILES[$sName];
            }
            if ($aFileData && isset($aFileData['tmp_name']) && is_uploaded_file($aFileData['tmp_name'])) {
                return $aFileData;
            }
        }

        return false;
    }


    /**
     * Метод проверки прав доступа пользователя к конкретному ивенту
     * @param string $sEvent Наименование ивента
     * @return bool
     */
    public function Access($sEvent) {

//        $sAccessMethodName = 'Access' . $sEvent;
//
//        if (method_exists($this, 'Access' . $sEvent)) {
//            return call_user_func_array(array($this, $sAccessMethodName), array());
//        }

        return true;
    }


    /**
     * Метод запрета доступа к ивенту
     * @param string $sEvent Наименование ивента
     * @return bool
     */
    public function AccessDenied($sEvent = null) {

        return $this->EventNotFound();
    }

}

// EOF