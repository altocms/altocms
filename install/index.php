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

error_reporting(E_ALL);

define('ALTO_NO_LOADER', 1);
define('ALTO_INSTALL', 1);
define('ALTO_DIR', dirname(dirname(__FILE__)));

require_once(ALTO_DIR . '/engine/loader.php');

class Install {

    /**
     * Название первого шага (используется, если другое не указано)
     *
     * @var string
     */
    const INSTALL_DEFAULT_STEP = 'Start';

    /**
     * Ключ сессии для хранения название следующего шага
     *
     * @var string
     */
    const SESSION_KEY_STEP_NAME = 'alto_install_step';

    /**
     * Название файла локальной конфигурации
     *
     * @var string
     */
    const LOCAL_CONFIG_FILE_NAME = 'config.local.php';

    /**
     * Передача этого ключа как параметра, указавает функции извлечения параметра
     * запросить значение переменной сначала из сессии, в случае не нахождения нужного
     * ключа - установить значение по умолчанию.
     *
     * Используется в фукнциях Assign(), GetRequest().
     *
     * @see $this->Assign()
     * @see $this->GetRequest()
     * @var string
     */
    const GET_VAR_FROM_SESSION = 'get';

    /**
     * Передача этого ключа как параметра, указавает функции предварительно сохранить
     * переменную в сессию с одноименным ключем.
     *
     * Используется в фукнциях Assign(), GetRequest().
     *
     * @see $this->Assign()
     * @see $this->GetRequest()
     * @var string
     */
    const SET_VAR_IN_SESSION = 'set';

    /**
     * Массив разрешенных шагов инсталяции
     *
     * @var array
     */
    protected $aSteps = array(0 => 'Start', 1 => 'Db', 2 => 'Admin', 3 => 'End', 4 => 'Finish');

    /**
     * Шаги в обычном режиме инсталляции
     *
     * @var array
     */
    protected $aSimpleModeSteps = array('Start', 'Db', 'Admin', 'End');

    /**
     * Количество шагов, которые необходимо указывать в инсталляционных параметрах
     *
     * @var int
     */
    protected $iStepCount = null;

    /**
     * Массив сообщений для пользователя
     *
     * @var array
     */
    protected $aMessages = array();

    /**
     * Массив ошибок
     *
     * @var array
     */
    protected $aErrors = array();

    /**
     * Директория с шаблонами
     *
     * @var string
     */
    protected $sTemplatesDir = 'templates';

    /**
     * Директория с языковыми файлами инсталлятора
     *
     * @var string
     */
    protected $sLangInstallDir = 'language';

    /**
     * Массив с переменными шаблонизатора
     *
     * @var array
     */
    protected $aTemplateVars
        = array(
            '___CONTENT___'            => '',
            '___FORM_ACTION___'        => '',
            '___NEXT_STEP_DISABLED___' => '',
            '___NEXT_STEP_DISPLAY___'  => 'block',
            '___PREV_STEP_DISABLED___' => '',
            '___PREV_STEP_DISPLAY___'  => 'block',
            '___SYSTEM_MESSAGES___'    => '',
            '___INSTALL_VERSION___'    => ALTO_VERSION,
        );

    /**
     * Описание требований для успешной инсталяции
     *
     * @var array
     */
    protected $aValidEnv
        = array(
            'allow_url_fopen'  => array('1', 'on'),
            'UTF8_support'     => '1',
            'http_input'       => array('', 'pass'),
            'http_output'      => array('0', 'pass'),
            'func_overload'    => array('0', '4', 'no overload'),
        );
    /**
     * Директория, в которой хранятся конфиг-файлы
     *
     * @var string
     */
    protected $sConfigDir = '';

    /**
     * Директория хранения скинов сайта
     *
     * @var string
     */
    protected $sSkinDir = '';

    /**
     * Директория хранения языковых файлов движка
     *
     * @var string
     */
    protected $sLangDir = '';

    protected $aLangAvailable = array('ru', 'en');

    /**
     * Текущий язык инсталлятора
     *
     * @var string
     */
    protected $sLangCurrent = '';

    /**
     * Язык инсталлятора, который будет использован по умолчанию
     *
     * @var string
     */
    protected $sLangDefault = 'ru';

    /**
     * Языковые текстовки
     *
     * @var array
     */
    protected $aLang = array();

    protected $bSkipAdmin = false;

    /**
     * Инициализация основных настроек
     *
     */
    public function __construct() {

        $this->sConfigDir = ALTO_DIR . '/app/config';
        $this->sSkinDir = ALTO_DIR . '/common/templates/skin';
        $this->sLangDir = ALTO_DIR . '/common/templates/language';

        $this->sLangDefault = $this->SelectDefaultLang($this->sLangDefault);

        // * Загружаем языковые файлы
        $this->LoadLanguageFile($this->sLangDefault);
        if ($sLang = $this->GetRequest('lang')) {
            $this->sLangCurrent = $sLang;
            if ($this->sLangCurrent != $this->sLangDefault) {
                $this->LoadLanguageFile($this->sLangCurrent);
            }
        }
        // * Передаем языковые тикеты во вьювер
        foreach ($this->aLang as $sKey => $sItem) {
            $this->Assign("lang_{$sKey}", $sItem);
        }
    }

    protected function SelectDefaultLang($sLangDefault) {

        $aClientLang = array();
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && ($list = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']))) {
            if (preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $list, $list)) {
                $aClientLang = array_combine($list[1], $list[2]);
                foreach ($aClientLang as $n => $v) {
                    $aClientLang[$n] = $v ? $v : 1;
                }
                arsort($aClientLang, SORT_NUMERIC);
            }
        }
        if ($aClientLang) {
            foreach (array_keys($aClientLang) as $sClientLang) {
                if (in_array($sClientLang, $this->aLangAvailable)) {
                    return $sClientLang;
                }
            }
        }
        return $sLangDefault;
    }

    /**
     * Подгружает указанный языковой файл и записывает поверх существующего языкового массива
     *
     * @access protected
     *
     * @param  string $sLang
     *
     * @return bool
     */
    protected function LoadLanguageFile($sLang) {

        $sFilePath = $this->sLangInstallDir . '/' . $sLang . '.php';
        if (!file_exists($sFilePath)) {
            return false;
        }

        $aLang = include($sFilePath);
        $this->aLang = array_merge($this->aLang, $aLang);
        return true;
    }

    /**
     * Возвращает языковую текстовку
     *
     * @param  string $sKey
     * @param  array  $aParams
     *
     * @return string
     */
    protected function Lang($sKey, $aParams = array()) {

        if (!array_key_exists($sKey, $this->aLang)) {
            return 'Unknown language key';
        }

        $sValue = $this->aLang[$sKey];
        if (count($aParams) == 0) {
            return $sValue;
        }

        foreach ($aParams as $k => $v) {
            $sValue = str_replace("%%{$k}%%", $v, $sValue);
        }
        return $sValue;
    }

    /**
     * Вытягивает переменную из сессии
     *
     * @param      $sKey
     * @param null $mDefault
     *
     * @return mixed|null
     */
    protected function GetSessionVar($sKey, $mDefault = null) {

        return array_key_exists($sKey, $_SESSION) ? unserialize($_SESSION[$sKey]) : $mDefault;
    }

    /**
     * Вкладывает переменную в сессию
     *
     * @param  string $sKey
     * @param  mixed  $mVar
     *
     * @return bool
     */
    protected function SetSessionVar($sKey, $mVar) {

        $_SESSION[$sKey] = serialize($mVar);
        return true;
    }

    /**
     * Уничтожает переменную в сессии
     *
     * @param  string $sKey
     *
     * @return bool
     */
    protected function DestroySessionVar($sKey) {

        if (!array_key_exists($sKey, $_SESSION)) {
            return false;
        }

        unset($_SESSION[$sKey]);
        return true;
    }

    /**
     * Выполняет рендеринг указанного шаблона
     *
     * @param  string $sTemplateName
     *
     * @return string
     */
    protected function Fetch($sTemplateName) {

        if (!file_exists($this->sTemplatesDir . '/' . $sTemplateName)) {
            return false;
        }

        $sTemplate = file_get_contents($this->sTemplatesDir . '/' . $sTemplateName);
        return $this->FetchString($sTemplate);
    }

    /**
     * Выполняет рендеринг строки
     *
     * @param  string $sTempString
     *
     * @return string
     */
    protected function FetchString($sTempString) {

        return str_replace(array_keys($this->aTemplateVars), array_values($this->aTemplateVars), $sTempString);
    }

    /**
     * Добавляет переменную для отображение в шаблоне.
     *
     * Если параметр $sFromSession установлен в значение GET,
     * то переменная сначала будет запрошена из сессии.
     *
     * Если параметр $sFromSession установлен в значение SET,
     * то переменная сначала вложена в сессию с одноименным ключем.
     *
     * @param string $sName
     * @param string $sValue
     * @param string $sFromSession
     */
    protected function Assign($sName, $sValue, $sFromSession = null) {

        if ($sFromSession == self::GET_VAR_FROM_SESSION) {
            $sValue = $this->GetSessionVar($sName, $sValue);
        }
        if ($sFromSession == self::SET_VAR_IN_SESSION) {
            $this->SetSessionVar($sName, $sValue);
        }

        $this->aTemplateVars['___' . strtoupper($sName) . '___'] = $sValue;
    }

    /**
     * Выполняет рендер layout`а (двухуровневый)
     *
     * @param  string $sTemplate
     *
     * @return bool
     */
    protected function Layout($sTemplate) {

        if (!$sLayoutContent = $this->Fetch($sTemplate)) {
            return false;
        }
        // * Рендерим сообщения по списку
        if (count($this->aMessages)) {
            // * Уникализируем содержимое списка сообщений
            $aMessages = array();
            foreach ($this->aMessages as &$sMessage) {
                if (array_key_exists('type', $sMessage) && array_key_exists('text', $sMessage)) {
                    $aMessages[$sMessage['type']][md5(serialize($sMessage))]
                        = '<b>' . ucfirst($sMessage['type']) . '</b>: ' . $sMessage['text'];
                }
                unset($sMessage);
            }
            $this->aMessages = $aMessages;

            $sMessageContent = '';
            foreach ($this->aMessages as $sType => $aMessageTexts) {
                $this->Assign('message_style_class', $sType);
                $this->Assign('message_content', implode('<br />', $aMessageTexts));
                $sMessageContent .= $this->Fetch('message.tpl');
            }
            $this->Assign('system_messages', $sMessageContent);
        }

        $this->Assign('content', $sLayoutContent);
        print $this->Fetch('layout.tpl');

        return true;
    }

    /**
     * Сохранить данные в конфиг-файл
     *
     * @param  string $sName
     * @param  string $sVar
     * @param  string $sPath
     *
     * @return bool
     */
    protected function SaveConfig($sName, $sVar, $sPath) {

        if (!file_exists($sPath)) {
            $this->aMessages[] = array(
                'type' => 'error',
                'text' => $this->Lang('config_file_not_exists', array('path' => $sPath)),
            );
            return false;
        }
        if (!is_writeable($sPath)) {
            $this->aMessages[] = array(
                'type' => 'error',
                'text' => $this->Lang('config_file_not_writable', array('path' => $sPath)),
            );
            return false;
        }

        $sConfig = file_get_contents($sPath);
        $sName = '$config[\'' . implode('\'][\'', explode('.', $sName)) . '\']';
        $sVar = $this->ConvertToString($sVar);

        // * Если переменная уже определена в конфиге, то меняем значение.
        if (substr_count($sConfig, $sName)) {
            $sConfig = preg_replace('~([\n\r]+)' . preg_quote($sName) . '.+;~Ui', '$1' . $sName . ' = ' . $sVar . ';', $sConfig, 1);
        } else {
            $sConfig = str_replace(
                'return $config;',
                $sName . ' = ' . $sVar . ';' . PHP_EOL . PHP_EOL . 'return $config;', $sConfig
            );
        }
        file_put_contents($sPath, $sConfig);
        return true;
    }

    /**
     * Преобразует переменную в формат для записи в текстовый файл
     *
     * @param  mixed $mVar
     *
     * @return string
     */
    protected function ConvertToString($mVar) {

        switch (true) {
            case is_string($mVar):
                return "'" . addslashes($mVar) . "'";

            case is_bool($mVar):
                return ($mVar) ? 'true' : 'false';

            case is_array($mVar):
                $sArrayString = '';
                foreach ($mVar as $sKey => $sValue) {
                    $sArrayString .= "'{$sKey}'=>" . $this->ConvertToString($sValue) . ',';
                }
                return 'array(' . $sArrayString . ')';

            default:
            case is_numeric($mVar):
                return "'" . (string)$mVar . "'";
        }
    }

    /**
     * Получает значение переданных параметров
     *
     * @param string $sName
     * @param mixed  $xDefault
     * @param bool   $bSession
     *
     * @return mixed|null|string
     */
    protected function GetRequest($sName, $xDefault = null, $bSession = null) {

        if (array_key_exists($sName, $_REQUEST)) {
            $sResult = (is_string($_REQUEST[$sName]))
                ? trim(stripslashes($_REQUEST[$sName]))
                : $_REQUEST[$sName];
        } else {
            $sResult = ($bSession == self::GET_VAR_FROM_SESSION)
                ? $this->GetSessionVar($sName, $xDefault)
                : $xDefault;
        }
        // * При необходимости сохраняем в сессию
        if ($bSession == self::SET_VAR_IN_SESSION) {
            $this->SetSessionVar($sName, $sResult);
        }

        return $sResult;
    }

    /**
     * Функция отвечающая за проверку входных параметров
     * и передающая управление на фукнцию текущего шага
     *
     * @param string $sStepName
     *
     * @call $this->Step{__Name__}
     */
    public function Run($sStepName = null) {

        if (is_null($sStepName)) {
            $sStepName = $this->GetSessionVar(self::SESSION_KEY_STEP_NAME, self::INSTALL_DEFAULT_STEP);
        } else {
            $this->SetSessionVar(self::SESSION_KEY_STEP_NAME, $sStepName);
        }

        if (!in_array($sStepName, $this->aSteps)) {
            die('Unknown step');
        }

        $iKey = array_search($sStepName, $this->aSteps);

        // * Если была нажата кнопка "Назад", перемещаемся на шаг назад
        if ($this->GetRequest('install_step_prev') && $iKey != 0) {
            $sStepName = $this->aSteps[--$iKey];
            $this->SetSessionVar(self::SESSION_KEY_STEP_NAME, $sStepName);
        }

        $this->Assign('next_step_display', ($iKey == count($this->aSteps) - 1) ? 'none' : 'inline-block');
        $this->Assign('prev_step_display', ($iKey == 0) ? 'none' : 'inline-block');

        // * Если шаг относится к simple mode, то корректируем количество шагов
        if (in_array($sStepName, $this->aSimpleModeSteps)) {
            $this->SetStepCount(count($this->aSimpleModeSteps));
        }

        // * Assign variables for viewer
        $this->Assign('install_step_number', $iKey + 1);
        $this->Assign('install_step_count', is_null($this->iStepCount) ? count($this->aSteps) : $this->iStepCount);

        // * Go to the current step
        $sFunctionName = 'Step' . $sStepName;
        if (@method_exists($this, $sFunctionName)) {
            $this->$sFunctionName();
        } else {
            $sFunctionName = 'Step' . self::INSTALL_DEFAULT_STEP;
            $this->SetSessionVar(self::SESSION_KEY_STEP_NAME, self::INSTALL_DEFAULT_STEP);
            $this->$sFunctionName();
        }
    }

    /**
     * Сохраняет данные о текущем шаге и передает их во вьювер
     *
     * @param string $sStepName
     */
    protected function SetStep($sStepName) {

        if (!$sStepName || !in_array($sStepName, $this->aSteps)) {
            return;
        }
        $this->Assign('install_step_number', array_search($sStepName, $this->aSteps) + 1);
    }

    /**
     * Устанавливает количество шагов для отображения в шаблонах
     *
     * @param int $iStepCount
     */
    protected function SetStepCount($iStepCount) {

        $this->iStepCount = $iStepCount;
    }

    /**
     * Первый шаг инсталяции.
     * Валидация окружения.
     */
    protected function StepStart() {

        if (!$this->ValidateEnvironment()) {
            $this->Assign('next_step_disabled', 'disabled');
        } else {
            // * Прописываем в конфигурацию абсолютные пути
            $this->SavePath();

            if ($this->GetRequest('install_step_next')) {
                $this->Run('Db');
                return;
            }
        }
        $this->SetStep('Start');
        $this->Layout('steps/start.tpl');
    }

    /**
     * Запрос данных соединения с базой данных.
     * Запись полученных данных в лог.
     */
    protected function StepDb() {

        if (!$this->GetRequest('install_db_params')) {

            // * Получаем данные из сессии (если они туда были вложены на предыдущих итерациях шага)
            $this->Assign('install_db_server', 'localhost', self::GET_VAR_FROM_SESSION);
            $this->Assign('install_db_port', '3306', self::GET_VAR_FROM_SESSION);
            $this->Assign('install_db_name', 'alto', self::GET_VAR_FROM_SESSION);
            $this->Assign('install_db_user', 'root', self::GET_VAR_FROM_SESSION);
            $this->Assign('install_db_password', '', self::GET_VAR_FROM_SESSION);
            $this->Assign('install_db_create_check', '', self::GET_VAR_FROM_SESSION);
            $this->Assign('install_db_prefix', 'prefix_', self::GET_VAR_FROM_SESSION);
            $this->Assign('install_db_engine', 'InnoDB', self::GET_VAR_FROM_SESSION);
            $this->Assign('install_db_engine_innodb', '', self::GET_VAR_FROM_SESSION);
            $this->Assign('install_db_engine_myisam', '', self::GET_VAR_FROM_SESSION);

            $this->Layout('steps/db.tpl');
            return true;
        }

        // * Если переданны данные формы, проверяем их на валидность
        $aParams['server'] = $this->GetRequest('install_db_server', '');
        $aParams['port'] = $this->GetRequest('install_db_port', '');
        $aParams['name'] = $this->GetRequest('install_db_name', '');
        $aParams['user'] = $this->GetRequest('install_db_user', '');
        $aParams['password'] = $this->GetRequest('install_db_password', '');
        $aParams['create'] = $this->GetRequest('install_db_create', 0);
        $aParams['convert_from_097'] = $this->GetRequest('install_db_convert_from_alto_097', 0);
        $aParams['convert_to_alto_11'] = $this->GetRequest('install_db_convert_to_alto_11', 0);
        $aParams['convert_to_alto'] = $this->GetRequest('install_db_convert_to_alto', 0);
        $aParams['prefix'] = $this->GetRequest('install_db_prefix', 'prefix_');
        $aParams['engine'] = $this->GetRequest('install_db_engine', 'InnoDB');

        $this->Assign('install_db_server', $aParams['server'], self::SET_VAR_IN_SESSION);
        $this->Assign('install_db_port', $aParams['port'], self::SET_VAR_IN_SESSION);
        $this->Assign('install_db_name', $aParams['name'], self::SET_VAR_IN_SESSION);
        $this->Assign('install_db_user', $aParams['user'], self::SET_VAR_IN_SESSION);
        $this->Assign('install_db_password', $aParams['password'], self::SET_VAR_IN_SESSION);
        $this->Assign(
            'install_db_create_check', (($aParams['create']) ? 'checked="checked"' : ''), self::SET_VAR_IN_SESSION
        );
        $this->Assign(
            'install_db_convert_to_alto_check', (($aParams['convert_to_alto']) ? 'checked="checked"' : ''),
            self::SET_VAR_IN_SESSION
        );
        $this->Assign(
            'install_db_convert_from_alto_097_check', (($aParams['convert_from_097']) ? 'checked="checked"' : ''),
            self::SET_VAR_IN_SESSION
        );
        $this->Assign(
            'install_db_convert_to_alto_11_check', (($aParams['convert_to_alto_11']) ? 'checked="checked"' : ''),
            self::SET_VAR_IN_SESSION
        );
        $this->Assign('install_db_prefix', $aParams['prefix'], self::SET_VAR_IN_SESSION);
        $this->Assign('install_db_engine', $aParams['engine'], self::SET_VAR_IN_SESSION);
        // * Передаем данные о выделенном пункте в списке tables engine
        $this->Assign(
            'install_db_engine_innodb', ($aParams['engine'] == 'InnoDB') ? 'selected="selected"' : '',
            self::SET_VAR_IN_SESSION
        );
        $this->Assign(
            'install_db_engine_myisam', ($aParams['engine'] == 'MyISAM') ? 'selected="selected"' : '',
            self::SET_VAR_IN_SESSION
        );

        if ($oDb = $this->ValidateDBConnection($aParams)) {
            $bSelect = $this->SelectDatabase($oDb, $aParams['name'], $aParams['create']);

            // * Если не удалось выбрать базу данных, возвращаем ошибку
            if (!$bSelect) {
                if ($aParams['create']) {
                    $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('error_db_invalid'));
                } else {
                    $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('error_db_connection_invalid'));
                }
                $this->Layout('steps/db.tpl');
                return false;
            }

            // * Сохраняем в config.local.php настройки соединения
            $sLocalConfigFile = $this->sConfigDir . '/' . self::LOCAL_CONFIG_FILE_NAME;
            if (!file_exists($sLocalConfigFile)) {
                $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('error_local_config_invalid'));
                $this->Layout('steps/db.tpl');
                return false;
            }
            @chmod($sLocalConfigFile, 0777);

            $this->SaveConfig('db.params.host', $aParams['server'], $sLocalConfigFile);
            $this->SaveConfig('db.params.port', $aParams['port'], $sLocalConfigFile);
            $this->SaveConfig('db.params.user', $aParams['user'], $sLocalConfigFile);
            $this->SaveConfig('db.params.pass', $aParams['password'], $sLocalConfigFile);
            $this->SaveConfig('db.params.dbname', $aParams['name'], $sLocalConfigFile);
            $this->SaveConfig('db.table.prefix', $aParams['prefix'], $sLocalConfigFile);

            if ($aParams['engine'] == 'InnoDB') {

                // * Проверяем поддержку InnoDB в MySQL
                $aParams['engine'] = 'MyISAM';
                if ($aRes = @mysqli_query($oDb, 'SHOW ENGINES')) {
                    while ($aRow = mysqli_fetch_assoc($aRes)) {
                        if ($aRow['Engine'] == 'InnoDB' && in_array($aRow['Support'], array('DEFAULT', 'YES'))) {
                            $aParams['engine'] = 'InnoDB';
                        }
                    }
                }
            }
            $this->SaveConfig('db.tables.engine', $aParams['engine'], $sLocalConfigFile);

            // * Сохраняем данные в сессию
            $this->SetSessionVar('INSTALL_DATABASE_PARAMS', $aParams);

            // * Проверяем была ли проведена установка базы в течении сеанса.
            // * Открываем .sql файл и добавляем в базу недостающие таблицы
            if ($this->GetSessionVar('INSTALL_DATABASE_DONE', '') != md5(serialize(array($aParams['server'], $aParams['name'])))) {

                // * Отдельным файлом запускаем создание GEO-базы
                $bResult = $this->CreateTables($oDb, 'geo_base.sql', array_merge($aParams, array('check_table' => 'geo_city')));
                if (!$bResult && $this->aErrors) {
                    foreach ($this->aErrors as $sError) {
                        $this->aMessages[] = array('type' => 'error', 'text' => $sError);
                    }
                    $this->Layout('steps/db.tpl');
                    return false;
                }

                if (!$aParams['convert_from_097'] && !$aParams['convert_to_alto'] && !$aParams['convert_to_alto_11']) {
                    $bResult = $this->CreateTables($oDb, 'sql.sql', array_merge($aParams, array('check_table' => 'topic')));
                    if (!$bResult) {
                        foreach ($this->aErrors as $sError) {
                            $this->aMessages[] = array('type' => 'error', 'text' => $sError);
                        }
                        $this->Layout('steps/db.tpl');
                        return false;
                    } else {
                        return $this->StepAdmin();
                    }
                } elseif ($aParams['convert_to_alto']) {

                    // * Если указана конвертация Livestreet 1.0.3 to Alto CMS
                    list($bResult, $aErrors) = array_values(
                        $this->ConvertDatabaseToAlto($oDb, 'convert_1.0.3_to_alto.sql', $aParams)
                    );
                    if ($bResult) {
                        list($bResult, $aErrors) = array_values(
                            $this->ConvertDatabaseToAlto($oDb, 'convert_1.0_to_1.1.sql', $aParams)
                        );
                    }
                    if (!$bResult) {
                        foreach ($aErrors as $sError) {
                            $this->aMessages[] = array('type' => 'error', 'text' => $sError);
                        }
                        $this->Layout('steps/db.tpl');
                        return false;
                    }
                } elseif ($aParams['convert_from_097']) {

                    // * Если указана конвертация AltoCMS 0.9.7 в Alto CMS 1.0
                    list($bResult, $aErrors) = array_values(
                        $this->ConvertDatabaseToAlto10($oDb, 'convert_0.9.7_to_1.0.sql', $aParams)
                    );
                    if (!$bResult) {
                        foreach ($aErrors as $sError) {
                            $this->aMessages[] = array('type' => 'error', 'text' => $sError);
                        }
                        $this->Layout('steps/db.tpl');
                        return false;
                    }
                } elseif ($aParams['convert_to_alto_11']) {

                    // * Если указана конвертация AltoCMS 1.1 в Alto CMS 1.1
                    list($bResult, $aErrors) = array_values(
                        $this->ConvertDatabaseToAlto11($oDb, 'convert_1.0_to_1.1.sql', $aParams)
                    );
                    if (!$bResult) {
                        foreach ($aErrors as $sError) {
                            $this->aMessages[] = array('type' => 'error', 'text' => $sError);
                        }
                        $this->Layout('steps/db.tpl');
                        return false;
                    }
                }
            }

            // * Сохраняем в сессии информацию о том, что преобразование базы данных уже было выполнено.
            // * При этом сохраняем хеш сервера и названия базы данных, для последующего сравнения.
            $this->SetSessionVar('INSTALL_DATABASE_DONE', md5(serialize(array($aParams['server'], $aParams['name']))));

            // * Передаем управление на следующий шаг
            $this->aMessages[] = array('type' => 'notice', 'text' => $this->Lang('ok_db_created'));
            return $this->StepAdmin();
        } else {
            $this->SetStep('Db');
            $this->Layout('steps/db.tpl');
            return false;
        }
    }

    /**
     * Запрос данных администратора и сохранение их в базе данных
     *
     */
    protected function StepAdmin() {

        $this->SetSessionVar(self::SESSION_KEY_STEP_NAME, 'Admin');
        $this->SetStep('Admin');

        // * Передаем данные из запроса во вьювер, сохраняя значение в сессии
        $this->Assign(
            'install_admin_login', $this->GetRequest('install_admin_login', 'admin', self::GET_VAR_FROM_SESSION),
            self::SET_VAR_IN_SESSION
        );
        $this->Assign(
            'install_admin_mail',
            $this->GetRequest('install_admin_mail', 'admin@admin.adm', self::GET_VAR_FROM_SESSION),
            self::SET_VAR_IN_SESSION
        );

        // * Если данные формы не были отправлены, передаем значения по умолчанию
        if (!$this->GetRequest('install_admin_params', false)) {
            return $this->Layout('steps/admin.tpl');
        }

        // * Проверяем валидность введенных данных
        list($bResult, $aErrors) = $this->ValidateAdminFields();
        if (!$bResult) {
            foreach ($aErrors as $sError) {
                $this->aMessages[] = array('type' => 'error', 'text' => $sError);
            }
            $this->Layout('steps/admin.tpl');
            return false;
        }

        // * Подключаемся к базе данных и сохраняем новые данные администратора
        $aParams = $this->GetSessionVar('INSTALL_DATABASE_PARAMS');
        $oDb = $this->ValidateDBConnection($aParams);
        if (!$oDb) {
            $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('error_db_connection_invalid'));
            $this->Layout('steps/admin.tpl');
            return false;
        }
        $this->SelectDatabase($oDb, $aParams['name']);

        $sLocalConfigFile = $this->sConfigDir . '/' . self::LOCAL_CONFIG_FILE_NAME;

        if (!$this->bSkipAdmin) {

            // make and save "salt"
            $aSalt = array();
            $this->SaveConfig('security.salt_sess', $aSalt['sess'] = F::RandomStr(64, false), $sLocalConfigFile);
            $this->SaveConfig('security.salt_pass', $aSalt['pass'] = F::RandomStr(64, false), $sLocalConfigFile);
            $this->SaveConfig('security.salt_auth', $aSalt['auth'] = F::RandomStr(64, false), $sLocalConfigFile);

            // make salted password
            $sPass = F::DoSalt($this->GetRequest('install_admin_pass'), $aSalt['pass']);

            $bUpdated = $this->UpdateDBUser(
                $oDb,
                $this->GetRequest('install_admin_login'),
                $sPass,
                $this->GetRequest('install_admin_mail'),
                $aParams['prefix']
            );
            if (!$bUpdated) {
                $this->aMessages[] = array(
                    'type' => 'error',
                    'text' => $this->Lang('error_db_saved') . '<br />' . mysqli_error($oDb),
                );
                $this->Layout('steps/admin.tpl');
                return false;
            }

            // * Обновляем данные о пользовательском блоге
            $this->UpdateUserBlog($oDb, 'Blog by ' . $this->GetRequest('install_admin_login'), $aParams['prefix']);
        }

        // * Передаем управление на следующий шаг
        return $this->StepEnd();
    }

    /**
     * Завершающий этап. Переход в расширенный режим
     *
     * @return bool
     */
    protected function StepEnd() {

        // TODO: Проверка, что эта страница уже выводилась, и усиленно заставить юзера удалить install
        $this->SetStep('End');
        $this->Assign('next_step_display', 'none');
        $this->SetSessionVar(self::SESSION_KEY_STEP_NAME, 'End');

        return $this->Layout('steps/end.tpl');
    }

    /**
     * Окончание работы инсталлятора. Предупреждение о необходимости удаления.
     *
     */
    protected function StepFinish() {

        $this->SetStep('Finish');
        $this->Assign('next_step_display', 'none');
        $this->SetSessionVar(self::SESSION_KEY_STEP_NAME, 'Finish');
        $this->Layout('steps/finish.tpl');
    }

    /**
     * Проверяем возможность инсталяции
     *
     * @return bool
     */
    protected function ValidateEnvironment() {

        $bOk = true;

        if (!version_compare(PHP_VERSION, ALTO_PHP_REQUIRED, '>=')) {
            $bOk = false;
            $this->Assign('validate_php_version', '<span style="color:red;">' . $this->Lang('no') . '</span>');
        } else {
            $this->Assign('validate_php_version', '<span style="color:green;">' . $this->Lang('yes') . '</span>');
            $this->Assign('validate_php_version_num', '<span style="color:green;">(' . PHP_VERSION . ')</span>');
        }

        if (@preg_match('//u', '') != $this->aValidEnv['UTF8_support']) {
            $bOk = false;
            $this->Assign('validate_utf8', '<span style="color:red;">' . $this->Lang('no') . '</span>');
        } else {
            $this->Assign('validate_utf8', '<span style="color:green;">' . $this->Lang('yes') . '</span>');
        }

        if (@extension_loaded('mbstring')) {
            $this->Assign('validate_mbstring', '<span style="color:green;">' . $this->Lang('yes') . '</span>');
        } else {
            $bOk = false;
            $this->Assign('validate_mbstring', '<span style="color:red;">' . $this->Lang('no') . '</span>');
        }

        if (@extension_loaded('SimpleXML')) {
            $this->Assign('validate_simplexml', '<span style="color:green;">' . $this->Lang('yes') . '</span>');
        } else {
            $bOk = false;
            $this->Assign('validate_simplexml', '<span style="color:red;">' . $this->Lang('no') . '</span>');
        }

        if ($aGraphicPackages = array_diff(array('Gmagick' => @extension_loaded('Gmagick'), 'Imagick' => @extension_loaded('Imagick'), 'GD' => @extension_loaded('GD')), array(''))) {
            $this->Assign('validate_graphic_packages', '<span style="color:green;">' . $this->Lang('yes') . '</span>');
            $this->Assign('validate_graphic_packages_name', '<small style="color:green;">(' . implode(',', array_keys($aGraphicPackages)) . ')</small>');
        } else {
            $bOk = FALSE;
            $this->Assign('validate_graphic_packages', '<span style="color:red;">' . $this->Lang('no') . '</span>');
            $this->Assign('validate_graphic_packages_name', '');
        }

        $sLocalConfigPath = $this->sConfigDir . '/config.local.php';
        $bOk = $this->checkFile($sLocalConfigPath, $this->sConfigDir . '/config.local.php.txt', 'validate_local_config') && $bOk;

        // * Проверяем доступность и права у соответствующих папок
        $sTempDir = dirname(dirname(__FILE__)) . '/_tmp';
        $bOk = $this->checkDir($sTempDir, 'validate_local_temp') && $bOk;

        $sLogsDir = dirname(dirname(__FILE__)) . '/_run';
        $bOk = $this->checkDir($sLogsDir, 'validate_local_runtime') && $bOk;

        $sUploadsDir = ALTO_DIR . '/uploads';
        $bOk = $this->checkDir($sUploadsDir, 'validate_local_uploads') && $bOk;

        $sPluginsDir = ALTO_DIR . '/app/plugins';
        $bOk = $this->checkDir($sPluginsDir, 'validate_local_plugins') & $bOk;

        $sPluginsDat = ALTO_DIR . '/app/plugins/plugins.dat';
        $bOk = $this->checkFile($sPluginsDat, null, 'validate_local_plugins_dat') && $bOk;

        return $bOk;
    }

    /**
     * @param string      $sDir
     * @param string|null $sVarName
     *
     * @return bool
     */
    protected function checkDir($sDir, $sVarName = null) {

        if (!F::File_CheckDir($sDir)) {
            if ($sVarName) {
                $this->Assign($sVarName, '<span style="color:red;">' . $this->Lang('no') . '</span>');
            }
            $bResult = false;
        } else {
            if ($sVarName) {
                $this->Assign($sVarName, '<span style="color:green;">' . $this->Lang('yes') . '</span>');
            }
            $bResult = true;
        }

        return $bResult;
    }

    /**
     * @param string      $sFile
     * @param string|null $sSource
     * @param string|null $sVarName
     *
     * @return bool
     */
    protected function checkFile($sFile, $sSource = null, $sVarName = null) {

        if (!is_file($sFile)) {
            $sDir = dirname($sFile);
            if ($this->checkDir($sDir)) {
                if ($sSource) {
                    if (is_file($sSource)) {
                        @copy($sSource, $sFile);
                    }
                } else {
                    file_put_contents($sFile, '');
                }
            }
        }

        if (!is_file($sFile) || !is_writable($sFile)) {
            if ($sVarName) {
                $this->Assign($sVarName, '<span style="color:red;">' . $this->Lang('no') . '</span>');
            }
            $bResult = false;
        } else {
            if ($sVarName) {
                $this->Assign($sVarName, '<span style="color:green;">' . $this->Lang('yes') . '</span>');
            }
            $bResult = true;
        }

        return $bResult;
    }

    /**
     * @param mysqli $oDb
     * @param string $sSql
     *
     * @return bool|mysqli_result
     */
    protected function sqlQuery($oDb, $sSql) {

        $xResult = mysqli_query($oDb, $sSql);
        if (!$xResult) {
            $this->aErrors[] = mysqli_error($oDb);
        }
        return $xResult;
    }

    /**
     * Проверяет соединение с базой данных
     *
     * @param  array $aParams
     *
     * @return mixed
     */
    protected function ValidateDBConnection($aParams) {

        $oDb = @mysqli_connect($aParams['server'], $aParams['user'], $aParams['password'], '', $aParams['port']);
        if ($oDb) {
            // * Валидация версии MySQL сервера
            if (!version_compare(mysqli_get_server_info($oDb), ALTO_MYSQL_REQUIRED, '>')) {
                $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('valid_mysql_server'));
                return false;
            }

            mysqli_query($oDb, 'set names utf8');
            return $oDb;
        }

        $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('error_db_connection_invalid'));
        return null;
    }

    /**
     * Выбрать базу данных (либо создать в случае необходимости).
     *
     * @param  mysqli $oDb
     * @param  string $sName
     * @param  bool   $bCreate
     *
     * @return bool
     */
    protected function SelectDatabase($oDb, $sName, $bCreate = false) {

        if (@mysqli_select_db($oDb, $sName)) {
            return true;
        }

        if ($bCreate) {
            @mysqli_query($oDb, "CREATE DATABASE $sName");
            return @mysqli_select_db($oDb, $sName);
        }
        return false;
    }

    /**
     * @param string $sFile
     * @param array $aParams
     *
     * @return array
     */
    protected function _loadQueries($sFile, $aParams) {

        $sFile = __DIR__ . '/db/' . $sFile;

        if (!$aLines = @file($sFile)) {
            return array('result' => false,
                         'errors' => array($this->Lang('config_file_not_exists', array('path' => $sFile))));
        }
        $aQueries = array();
        $nCnt = 0;
        $sQuery = '';
        foreach ($aLines as $sStr) {
            if (isset($aParams['prefix'])) {
                $sStr = str_replace('prefix_', $aParams['prefix'], $sStr);
            }
            if (substr(trim($sStr), -1) == ';') {
                $sQuery .= $sStr;
                $aQueries[$nCnt++] = $sQuery;
                $sQuery = '';
            } else {
                $sQuery .= $sStr;
            }
        }
        return $aQueries;
    }

    /**
     * Добавляет в базу данных необходимые таблицы
     *
     * @param mysqli $oDb
     * @param string $sFileName
     * @param array  $aParams
     *
     * @return array|bool
     */
    protected function CreateTables($oDb, $sFileName, $aParams) {

        $aQuery = $this->_loadQueries($sFileName, $aParams);

        // * Смотрим, какие таблицы существуют в базе данных
        $aDbTables = array();
        $aResult = @mysqli_query($oDb, 'SHOW TABLES');
        if (!$aResult) {
            $this->aErrors[] = $this->Lang('error_db_no_data');
            return false;
        }
        while ($aRow = mysqli_fetch_array($aResult, MYSQLI_NUM)) {
            $aDbTables[] = $aRow[0];
        }

        // * Если указано проверить наличие таблицы и она уже существует, то выполнять SQL-дамп не нужно
        if (in_array($aParams['prefix'] . $aParams['check_table'], $aDbTables)) {
            return true;
        }

        $bResult = true;
        // * Выполняем запросы по очереди
        foreach ($aQuery as $sQuery) {
            $sQuery = trim($sQuery);

            // * Заменяем движок, если таковой указан в запросе
            if (isset($aParams['engine'])) {
                $sQuery = str_ireplace('ENGINE=InnoDB', "ENGINE={$aParams['engine']}", $sQuery);
            }

            $bResult = true;
            if ($sQuery != '' && !$this->IsUseDbTable($sQuery, $aDbTables)) {
                $bResult = $bResult && $this->sqlQuery($oDb, $sQuery);
            }
        }

        return $bResult;
    }

    /**
     * Проверяем, нуждается ли база в конвертации или нет
     *
     * @param mysqli $oDb
     * @param array  $aParams
     *
     * @return bool
     */
    protected function ValidateConvertDatabase($oDb, $aParams) {

        // * Проверяем, нуждается ли база в конвертации или нет
        // * Смотрим, какие таблицы существуют в базе данных
        $aDbTables = array();
        $aResult = @mysqli_query($oDb, 'SHOW TABLES');
        if (!$aResult) {
            return array('result' => false, 'errors' => array($this->Lang('error_db_no_data')));
        }
        while ($aRow = mysqli_fetch_array($aResult, MYSQLI_NUM)) {
            $aDbTables[] = $aRow[0];
        }
        // * Смотрим на наличие в базе таблицы prefix_user_note
        return !in_array($aParams['prefix'] . 'user_note', $aDbTables);
    }

    /**
     * Проверяем, нуждается ли база в конвертации из 0.9.7 в 1.0 или нет
     *
     * @param mysqli $oDb
     * @param array  $aParams
     *
     * @return bool
     */
    protected function ValidateConvertDatabaseToAlto10($oDb, $aParams) {

        // * Проверяем, нуждается ли база в конвертации или нет
        // * Смотрим, какие таблицы существуют в базе данных
        $aDbTables = array();
        $aResult = @mysqli_query($oDb, 'SHOW TABLES');
        if (!$aResult) {
            return array('result' => false, 'errors' => array($this->Lang('error_db_no_data')));
        }
        while ($aRow = mysqli_fetch_array($aResult, MYSQLI_NUM)) {
            $aDbTables[] = $aRow[0];
        }
        // * Смотрим на наличие в базе таблицы prefix_content
        return !in_array($aParams['prefix'] . 'blog_type', $aDbTables);
    }

    /**
     * Проверяем, нуждается ли база в конвертации из 1.0 в 1.1 или нет
     *
     * @param mysqli $oDb
     * @param array  $aParams
     *
     * @return bool
     */
    protected function ValidateConvertDatabaseToAlto11($oDb, $aParams) {

        // * Проверяем, нуждается ли база в конвертации или нет
        // * Смотрим, какие таблицы существуют в базе данных
        $aDbTables = array();
        $aResult = @mysqli_query($oDb, 'SHOW TABLES');
        if (!$aResult) {
            return array('result' => false, 'errors' => array($this->Lang('error_db_no_data')));
        }
        while ($aRow = mysqli_fetch_array($aResult, MYSQLI_NUM)) {
            $aDbTables[] = $aRow[0];
        }
        // * Смотрим на наличие в базе таблицы prefix_content
        return !in_array($aParams['prefix'] . 'prefix_blog_type_content', $aDbTables);
    }

    /**
     * Конвертирует базу данных версии 0.9.7 в базу данных версии 1.0
     *
     * @param mysqli $oDb
     * @param string $sFileName
     * @param array  $aParams
     *
     * @return array
     */
    protected function ConvertDatabaseToAlto10($oDb, $sFileName, $aParams) {

        if (!$this->ValidateConvertDatabaseToAlto10($oDb, $aParams)) {
            return array('result' => false, 'errors' => array($this->Lang('error_database_converted_already')));
        }

        $aQuery = $this->_loadQueries($sFileName, $aParams);

        // * Массив для сбора ошибок
        $aErrors = array();

        // * Выполняем запросы по очереди
        foreach ($aQuery as $sQuery) {
            $sQuery = trim($sQuery);

            // * Заменяем движок, если таковой указан в запросе
            if (isset($aParams['engine'])) {
                $sQuery = str_ireplace('ENGINE=InnoDB', "ENGINE={$aParams['engine']}", $sQuery);
            }

            if ($sQuery != '') {
                $bResult = mysqli_query($oDb, $sQuery);
                if (!$bResult) {
                    $aErrors[] = mysqli_error($oDb) ;
                }
            }
        }

        if (count($aErrors) == 0) {
            return array('result' => true, 'errors' => null);
        }
        return array('result' => false, 'errors' => $aErrors);
    }

    /**
     * Конвертирует базу данных версии 0.9.7 в базу данных версии 1.0
     *
     * @param mysqli $oDb
     * @param string $sFileName
     * @param array $aParams
     *
     * @return array
     */
    protected function ConvertDatabaseToAlto11($oDb, $sFileName, $aParams) {

        if (!$this->ValidateConvertDatabaseToAlto11($oDb, $aParams)) {
            return array('result' => false, 'errors' => array($this->Lang('error_database_converted_already')));
        }

        $aQuery = $this->_loadQueries($sFileName, $aParams);

        // * Массив для сбора ошибок
        $aErrors = array();

        // * Выполняем запросы по очереди
        foreach ($aQuery as $sQuery) {
            $sQuery = trim($sQuery);

            // * Заменяем движок, если таковой указан в запросе
            if (isset($aParams['engine'])) {
                $sQuery = str_ireplace('ENGINE=InnoDB', "ENGINE={$aParams['engine']}", $sQuery);
            }

            if ($sQuery != '') {
                $bResult = mysqli_query($oDb, $sQuery);
                if (!$bResult) {
                    $aErrors[] = mysqli_error($oDb) ;
                }
            }
        }

        if (count($aErrors) == 0) {
            return array('result' => true, 'errors' => null);
        }
        return array('result' => false, 'errors' => $aErrors);
    }


    /**
     * Проверяем, нуждается ли база в конвертации c LiveStreet 1.0.3 в AltoCMS 1.0 или нет
     *
     * @param mysqli $oDb
     * @param array  $aParams
     *
     * @return bool
     */
    protected function ValidateConvertDatabaseToAlto($oDb, $aParams) {

        // * Проверяем, нуждается ли база в конвертации или нет
        // * Смотрим, какие таблицы существуют в базе данных
        $aDbTables = array();
        $aResult = @mysqli_query($oDb, 'SHOW TABLES');
        if (!$aResult) {
            return array('result' => false, 'errors' => array($this->Lang('error_db_no_data')));
        }
        while ($aRow = mysqli_fetch_array($aResult, MYSQLI_NUM)) {
            $aDbTables[] = $aRow[0];
        }
        // * Смотрим на наличие в базе таблицы prefix_content
        return !in_array($aParams['prefix'] . 'content', $aDbTables);
    }

    /**
     * Конвертирует базу данных LiveStreet 1.0.3 в AltoCMS 1.0
     *
     * @param mysqli $oDb
     * @param string $sFileName
     * @param array  $aParams
     *
     * @return array
     */
    protected function ConvertDatabaseToAlto($oDb, $sFileName, $aParams) {

        if (!$this->ValidateConvertDatabaseToAlto($oDb, $aParams)) {
            return array('result' => false, 'errors' => array($this->Lang('error_database_converted_already')));
        }

        $aQuery = $this->_loadQueries($sFileName, $aParams);

        // * Массив для сбора ошибок
        $aErrors = array();

        // * Выполняем запросы по очереди
        foreach ($aQuery as $sQuery) {
            $sQuery = trim($sQuery);

            // * Заменяем движок, если таковой указан в запросе
            if (isset($aParams['engine'])) {
                $sQuery = str_ireplace('ENGINE=InnoDB', "ENGINE={$aParams['engine']}", $sQuery);
            }

            if ($sQuery != '') {
                $bResult = mysqli_query($oDb, $sQuery);
                if ($bResult === false) {
                    $aErrors[] = mysqli_error($oDb) ;
                }
            }
        }

        if (count($aErrors) == 0) {
            return array('result' => true, 'errors' => null);
        }
        return array('result' => false, 'errors' => $aErrors);
    }

    /**
     * Добавление значения в поле таблицы с типом enum
     *
     * @param mysqli $oDb
     * @param string $sTableName
     * @param string $sFieldName
     * @param string $sType
     */
    public function addEnumTypeDatabase($oDb, $sTableName, $sFieldName, $sType) {

        $sQuery = "SHOW COLUMNS FROM  `{$sTableName}`";
        if ($res = mysqli_query($oDb, $sQuery)) {
            while ($aRow = mysqli_fetch_assoc($res)) {
                if ($aRow['Field'] == $sFieldName) {
                    break;
                }
            }
            if (strpos($aRow['Type'], "'{$sType}'") === FALSE) {
                $aRow['Type'] = str_ireplace('enum(', "enum('{$sType}',", $aRow['Type']);
                $sQuery = "ALTER TABLE `{$sTableName}` MODIFY `{$sFieldName}` " . $aRow['Type'];
                $sQuery .= ($aRow['Null'] == 'NO') ? ' NOT NULL ' : ' NULL ';
                $sQuery .= is_null($aRow['Default']) ? ' DEFAULT NULL ' : " DEFAULT '{$aRow['Default']}' ";
                mysqli_query($oDb, $sQuery);
            }
        }
    }

    /**
     * Проверяет существование таблицы
     *
     * @param mysqli $oDb
     * @param string $sTableName
     *
     * @return bool
     */
    public function isTableExistsDatabase($oDb, $sTableName) {

        $sQuery = "SHOW TABLES LIKE '{$sTableName}'";
        if ($res = mysqli_query($oDb, $sQuery)) {
            return true;
        }
        return false;
    }

    /**
     * Проверяет существование поля таблицы
     *
     * @param mysqli $oDb
     * @param string $sTableName
     * @param string $sFieldName
     *
     * @return bool
     */
    public function isFieldExistsDatabase($oDb, $sTableName, $sFieldName) {

        $sQuery = "SHOW FIELDS FROM `{$sTableName}`";
        if ($res = mysqli_query($oDb, $sQuery)) {
            while ($aRow = mysqli_fetch_assoc($res)) {
                if ($aRow['Field'] == $sFieldName) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Валидирует данные администратора
     *
     * @return array;
     */
    protected function ValidateAdminFields() {

        $bOk = true;
        $aErrors = array();

        if ($this->GetRequest('install_admin_skip', false)) {
            $this->bSkipAdmin = true;
        } else {
            if (!($sLogin = $this->GetRequest('install_admin_login', false))
                || !preg_match('/^[\da-z\_\-]{3,30}$/i', $sLogin)
            ) {
                $bOk = false;
                $aErrors[] = $this->Lang('admin_login_invalid');
            }

            if (!($sMail = $this->GetRequest('install_admin_mail', false))
                || !preg_match('/^[\da-z\_\-\.\+]+@[\da-z_\-\.]+\.[a-z]{2,5}$/i', $sMail)
            ) {
                $bOk = false;
                $aErrors[] = $this->Lang('admin_mail_invalid');
            }
            if (!($sPass = $this->GetRequest('install_admin_pass', false)) || strlen($sPass) < 3) {
                $bOk = false;
                $aErrors[] = $this->Lang('admin_password_invalid');
            }
            if ($this->GetRequest('install_admin_repass', '') != $this->GetRequest('install_admin_pass', '')) {
                $bOk = false;
                $aErrors[] = $this->Lang('admin_repassword_invalid');
            }
        }

        return array($bOk, $aErrors);
    }

    /**
     * Сохраняет данные об администраторе в базу данных
     *
     * @param mysqli $oDb
     * @param string $sLogin
     * @param string $sPassword
     * @param string $sMail
     * @param string $sPrefix
     *
     * @return bool
     */
    protected function UpdateDBUser($oDb, $sLogin, $sPassword, $sMail, $sPrefix = 'prefix_') {

        $sQuery = "
        	UPDATE `{$sPrefix}user`
        	SET
        		`user_login`    = '{$sLogin}',
        		`user_mail`     = '{$sMail}',
        		`user_password` = '{$sPassword}'
			WHERE `user_id` = 1";

        return mysqli_query($oDb, $sQuery);
    }

    /**
     * Перезаписывает название блога в базе данных
     *
     * @param mysqli $oDb
     * @param string $sBlogName
     * @param string $sPrefix
     *
     * @return bool
     */
    protected function UpdateUserBlog($oDb, $sBlogName, $sPrefix = 'prefix_') {

        $sQuery = "
        	UPDATE `{$sPrefix}blog`
        	SET
        		`blog_title`    = '" . mysqli_real_escape_string($oDb, $sBlogName) . "'
			WHERE `blog_id` = 1";

        return mysqli_query($oDb, $sQuery);
    }

    /**
     * Проверяет, использует ли mysql запрос, одну из указанных в массиве таблиц
     *
     * @param string $sQuery
     * @param array  $aTables
     *
     * @return bool
     */
    protected function IsUseDbTable($sQuery, $aTables) {

        foreach ($aTables as $sTable) {
            if (substr_count($sQuery, "`{$sTable}`")) {
                return true;
            }
        }
        return false;
    }

    /**
     * Отдает список доступных шаблонов
     *
     * @return array
     */
    protected function GetSkinList() {

        // * Получаем список каталогов
        $aDir = glob($this->sSkinDir . '/*', GLOB_ONLYDIR);

        if (!is_array($aDir)) {
            return array();
        }
        return array_map(create_function('$sDir', 'return basename($sDir);'), $aDir);
    }

    /**
     * Отдает список доступных языков
     *
     * @return array
     */
    protected function GetLangList() {

        // * Получаем список каталогов
        $aDir = glob($this->sLangDir . '/*.php');

        if (!is_array($aDir)) {
            return array();
        }
        return array_map(create_function('$sDir', 'return basename($sDir,".php");'), $aDir);
    }

    /**
     * Сохраняет в конфигурации абсолютные пути
     *
     * @access protected
     * @return null
     */
    protected function SavePath() {

        $sLocalConfigFile = $this->sConfigDir . '/' . self::LOCAL_CONFIG_FILE_NAME;
        $this->SaveConfig('path.root.url', $this->_getPathRootUrl(), $sLocalConfigFile);
        //$this->SaveConfig('path.root.dir', $this->_getPathRootDir(), $sLocalConfigFile);

        $aDirs = array();
        $sDirs = trim(str_replace('http://' . $_SERVER['HTTP_HOST'], '', $this->_getPathRootUrl()), '/');
        if ($sDirs != '') {
            $aDirs = explode('/', $sDirs);
            $this->SaveConfig('path.runtime.url', '/' . $sDirs . '/_run/', $sLocalConfigFile);
            $this->SaveConfig('path.runtime.dir', $this->_getPathRootDir() . '_run/', $sLocalConfigFile);
        }
        $this->SaveConfig('path.offset_request_url', count($aDirs), $sLocalConfigFile);
    }

    protected function _getPathRootUrl() {

        return F::UrlBase() . str_replace('/install/index.php', '', $_SERVER['PHP_SELF']) . '/';
    }

    protected function _getPathRootDir() {

        return str_replace('\\', '/', ALTO_DIR) . '/';
    }

}

session_start();
$oInstaller = new Install;
$oInstaller->Run();

// EOF