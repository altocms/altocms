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
    protected $aSteps = array(0 => 'Start', 1 => 'Db', 2 => 'Admin', 3 => 'End', 4 => 'Extend', 5 => 'Finish');

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
            //'safe_mode' => array('0', 'off', ''),
            //'register_globals' => array('0', 'off', ''),
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

    /**
     * Инициализация основных настроек
     *
     */
    public function __construct() {

        $this->sConfigDir = ALTO_DIR . '/app/config';
        $this->sSkinDir = ALTO_DIR . '/common/templates/skin';
        $this->sLangDir = ALTO_DIR . '/common/templates/language';

        $this->sLangDefault = $this->SelectDefaultLang($this->sLangDefault);
        /**
         * Загружаем языковые файлы
         */
        $this->LoadLanguageFile($this->sLangDefault);
        if ($sLang = $this->GetRequest('lang')) {
            $this->sLangCurrent = $sLang;
            if ($this->sLangCurrent != $this->sLangDefault) {
                $this->LoadLanguageFile($this->sLangCurrent);
            }
        }
        /**
         * Передаем языковые тикеты во вьювер
         */
        foreach ($this->aLang as $sKey => $sItem) {
            $this->Assign("lang_{$sKey}", $sItem);
        }
    }

    protected function SelectDefaultLang($sLangDefault) {

        $aClentLang = array();
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && ($list = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']))) {
            if (preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $list, $list)) {
                $aClentLang = array_combine($list[1], $list[2]);
                foreach ($aClentLang as $n => $v) {
                    $aClentLang[$n] = $v ? $v : 1;
                }
                arsort($aClentLang, SORT_NUMERIC);
            }
        }
        if ($aClentLang) {
            foreach (array_keys($aClentLang) as $sClentLang) {
                if (in_array($sClentLang, $this->aLangAvailable)) {
                    return $sClentLang;
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
     * @return null
     */
    protected function Layout($sTemplate) {

        if (!$sLayoutContent = $this->Fetch($sTemplate)) {
            return false;
        }
        /**
         * Рендерим сообщения по списку
         */
        if (count($this->aMessages)) {
            /**
             * Уникализируем содержимое списка сообщений
             */
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

        /**
         * Если переменная уже определена в конфиге,
         * то меняем значение.
         */
        if (substr_count($sConfig, $sName)) {
            $sConfig = preg_replace('~' . preg_quote($sName) . '.+;~Ui', $sName . ' = ' . $sVar . ';', $sConfig, 1);
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
     * @param      $sName
     * @param null $default
     * @param null $bSession
     *
     * @return mixed|null|string
     */
    protected function GetRequest($sName, $default = null, $bSession = null) {
        if (array_key_exists($sName, $_REQUEST)) {
            $sResult = (is_string($_REQUEST[$sName]))
                ? trim(stripslashes($_REQUEST[$sName]))
                : $_REQUEST[$sName];
        } else {
            $sResult = ($bSession == self::GET_VAR_FROM_SESSION)
                ? $this->GetSessionVar($sName, $default)
                : $default;
        }
        /**
         * При необходимости сохраняем в сессию
         */
        if ($bSession == self::SET_VAR_IN_SESSION) {
            $this->SetSessionVar($sName, $sResult);
        }

        return $sResult;
    }

    /**
     * Функция отвечающая за проверку входных параметров
     * и передающая управление на фукнцию текущего шага
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
        /**
         * Если была нажата кнопка "Назад", перемещаемся на шаг назад
         */
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
     * @param $sStepName
     *
     * @return null
     */
    protected function SetStep($sStepName) {

        if (!$sStepName || !in_array($sStepName, $this->aSteps)) {
            return null;
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

        if (!$this->ValidateEnviroment()) {
            $this->Assign('next_step_disabled', 'disabled');
        } else {
            // * Прописываем в конфигурацию абсолютные пути
            $this->SavePath();

            if ($this->GetRequest('install_step_next')) {
                return $this->Run('Db');
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
            /**
             * Получаем данные из сессии (если они туда были вложены на предыдущих итерациях шага)
             */
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
        /**
         * Если переданны данные формы, проверяем их на валидность
         */
        $aParams['server'] = $this->GetRequest('install_db_server', '');
        $aParams['port'] = $this->GetRequest('install_db_port', '');
        $aParams['name'] = $this->GetRequest('install_db_name', '');
        $aParams['user'] = $this->GetRequest('install_db_user', '');
        $aParams['password'] = $this->GetRequest('install_db_password', '');
        $aParams['create'] = $this->GetRequest('install_db_create', 0);
        $aParams['convert_from_097'] = $this->GetRequest('install_db_convert_from_alto_097', 0);
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
        $this->Assign('install_db_prefix', $aParams['prefix'], self::SET_VAR_IN_SESSION);
        $this->Assign('install_db_engine', $aParams['engine'], self::SET_VAR_IN_SESSION);
        /**
         * Передаем данные о выделенном пункте в списке tables engine
         */
        $this->Assign(
            'install_db_engine_innodb', ($aParams['engine'] == 'InnoDB') ? 'selected="selected"' : '',
            self::SET_VAR_IN_SESSION
        );
        $this->Assign(
            'install_db_engine_myisam', ($aParams['engine'] == 'MyISAM') ? 'selected="selected"' : '',
            self::SET_VAR_IN_SESSION
        );

        if ($oDb = $this->ValidateDBConnection($aParams)) {
            $bSelect = $this->SelectDatabase($aParams['name'], $aParams['create']);
            /**
             * Если не удалось выбрать базу данных, возвращаем ошибку
             */
            if (!$bSelect) {
                if ($aParams['create']) {
                    $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('error_db_invalid'));
                } else {
                    $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('error_db_connection_invalid'));
                }
                $this->Layout('steps/db.tpl');
                return false;
            }

            /**
             * Сохраняем в config.local.php настройки соединения
             */
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
                /**
                 * Проверяем поддержку InnoDB в MySQL
                 */
                $aParams['engine'] = 'MyISAM';
                if ($aRes = @mysql_query('SHOW ENGINES')) {
                    while ($aRow = mysql_fetch_assoc($aRes)) {
                        if ($aRow['Engine'] == 'InnoDB' && in_array($aRow['Support'], array('DEFAULT', 'YES'))) {
                            $aParams['engine'] = 'InnoDB';
                        }
                    }
                }
            }
            $this->SaveConfig('db.tables.engine', $aParams['engine'], $sLocalConfigFile);
            /**
             * Сохраняем данные в сессию
             */
            $this->SetSessionVar('INSTALL_DATABASE_PARAMS', $aParams);
            /**
             * Проверяем была ли проведена установка базы в течении сеанса.
             * Открываем .sql файл и добавляем в базу недостающие таблицы
             */
            if ($this->GetSessionVar('INSTALL_DATABASE_DONE', '') != md5(serialize(array($aParams['server'], $aParams['name'])))) {

				// * Отдельным файлом запускаем создание GEO-базы
                $bResult = $this->CreateTables('geo_base.sql', array_merge($aParams, array('check_table' => 'geo_city')));
                if (!$bResult && $this->aErrors) {
                    foreach ($this->aErrors as $sError) {
                        $this->aMessages[] = array('type' => 'error', 'text' => $sError);
                    }
                    $this->Layout('steps/db.tpl');
                    return false;
                }

                if (!$aParams['convert_from_097'] && !$aParams['convert_to_alto']) {
					$bResult = $this->CreateTables('sql.sql', array_merge($aParams, array('check_table' => 'topic')));
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
                    /**
                     * Если указана конвертация Livestreet 1.0.3 to Alto CMS
                     */
                    list($bResult, $aErrors) = array_values(
                        $this->ConvertDatabaseToAlto('convert_1.0.3_to_alto.sql', $aParams)
                    );
                    if (!$bResult) {
                        foreach ($aErrors as $sError) {
                            $this->aMessages[] = array('type' => 'error', 'text' => $sError);
                        }
                        $this->Layout('steps/db.tpl');
                        return false;
                    }
                } elseif ($aParams['convert_from_097']) {
                    /**
                     * Если указана конвертация AltoCMS 0.9.7 в Alto CMS 1.0
                     */
                    list($bResult, $aErrors) = array_values(
                        $this->ConvertDatabaseToAlto10('convert_from_097.sql', $aParams)
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
            /**
             * Сохраняем в сессии информацию о том, что преобразование базы данных уже было выполнено.
             * При этом сохраняем хеш сервера и названия базы данных, для последующего сравнения.
             */
            $this->SetSessionVar('INSTALL_DATABASE_DONE', md5(serialize(array($aParams['server'], $aParams['name']))));
            /**
             * Передаем управление на следующий шаг
             */
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
        /**
         * Передаем данные из запроса во вьювер, сохраняя значение в сессии
         */
        $this->Assign(
            'install_admin_login', $this->GetRequest('install_admin_login', 'admin', self::GET_VAR_FROM_SESSION),
            self::SET_VAR_IN_SESSION
        );
        $this->Assign(
            'install_admin_mail',
            $this->GetRequest('install_admin_mail', 'admin@admin.adm', self::GET_VAR_FROM_SESSION),
            self::SET_VAR_IN_SESSION
        );
        /**
         * Если данные формы не были отправлены, передаем значения по умолчанию
         */
        if (!$this->GetRequest('install_admin_params', false)) {
            return $this->Layout('steps/admin.tpl');
        }
        /**
         * Проверяем валидность введенных данных
         */
        list($bResult, $aErrors) = $this->ValidateAdminFields();
        if (!$bResult) {
            foreach ($aErrors as $sError) {
                $this->aMessages[] = array('type' => 'error', 'text' => $sError);
            }
            $this->Layout('steps/admin.tpl');
            return false;
        }
        $sLocalConfigFile = $this->sConfigDir . '/' . self::LOCAL_CONFIG_FILE_NAME;

        // make and save "salt"
        $aSalt = array();
        $this->SaveConfig('security.salt_sess', $aSalt['sess'] = F::RandomStr(64, false), $sLocalConfigFile);
        $this->SaveConfig('security.salt_pass', $aSalt['pass'] = F::RandomStr(64, false), $sLocalConfigFile);
        $this->SaveConfig('security.salt_auth', $aSalt['auth'] = F::RandomStr(64, false), $sLocalConfigFile);
        /**
         * Подключаемся к базе данных и сохраняем новые данные администратора
         */
        $aParams = $this->GetSessionVar('INSTALL_DATABASE_PARAMS');
        if (!$this->ValidateDBConnection($aParams)) {
            $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('error_db_connection_invalid'));
            $this->Layout('steps/admin.tpl');
            return false;
        }
        $this->SelectDatabase($aParams['name']);

        // make salted password
        $sPass = F::DoSalt($this->GetRequest('install_admin_pass'), $aSalt['pass']);

        $bUpdated = $this->UpdateDBUser(
            $this->GetRequest('install_admin_login'),
            $sPass,
            $this->GetRequest('install_admin_mail'),
            $aParams['prefix']
        );
        if (!$bUpdated) {
            $this->aMessages[] = array(
                'type' => 'error',
                'text' => $this->Lang('error_db_saved') . '<br />' . mysql_error()
            );
            $this->Layout('steps/admin.tpl');
            return false;
        }
        /**
         * Обновляем данные о пользовательском блоге
         */
        $this->UpdateUserBlog('Blog by ' . $this->GetRequest('install_admin_login'), $aParams['prefix']);

        /**
         * Передаем управление на следующий шаг
         */
        return $this->StepEnd();
    }

    /**
     * Завершающий этап. Переход в расширенный режим
     */
    protected function StepEnd() {
        // TODO: Проверка, что эта страница уже выводилась, и усиленно заставить юзера удалить install
        $this->SetStep('End');
        $this->Assign('next_step_display', 'none');
        $this->SetSessionVar(self::SESSION_KEY_STEP_NAME, 'End');
        /**
         * Если пользователь выбрал расширенный режим, переводим на новый шаг
         */
        return ($this->GetRequest('install_step_extend'))
            ? $this->StepExtend()
            : $this->Layout('steps/end.tpl');
    }

    /**
     * Расширенный режим ввода дополнительных настроек.
     */
    protected function StepExtend() {
        /**
         * Выводим на экран кнопку @Next
         */
        $this->Assign('next_step_display', 'inline-block');
        /**
         * Сохраняем в сессию название текущего шага
         */
        $this->SetSessionVar(self::SESSION_KEY_STEP_NAME, 'Extend');
        $this->SetStep('Extend');
        /**
         * Получаем значения запрашиваемых данных либо устанавливаем принятые по умолчанию
         */
        $aParams['install_view_name'] = $this->GetRequest('install_view_name', 'Your Site', self::GET_VAR_FROM_SESSION);
        $aParams['install_view_description'] = $this->GetRequest(
            'install_view_description', 'Description your site', self::GET_VAR_FROM_SESSION
        );
        $aParams['install_view_keywords'] = $this->GetRequest(
            'install_view_keywords', 'site, google, internet', self::GET_VAR_FROM_SESSION
        );
        $aParams['install_view_skin'] = $this->GetRequest('install_view_skin', 'synio', self::GET_VAR_FROM_SESSION);

        $aParams['install_mail_sender'] = $this->GetRequest(
            'install_mail_sender', $this->GetSessionVar('install_admin_mail', 'rus.engine@gmail.com'),
            self::GET_VAR_FROM_SESSION
        );
        $aParams['install_mail_name'] = $this->GetRequest(
            'install_mail_name', 'Почтовик Your Site', self::GET_VAR_FROM_SESSION
        );

        $aParams['install_general_close'] = (bool)$this->GetRequest(
            'install_general_close', false, self::GET_VAR_FROM_SESSION
        );
        $aParams['install_general_invite'] = (bool)$this->GetRequest(
            'install_general_invite', false, self::GET_VAR_FROM_SESSION
        );
        $aParams['install_general_active'] = (bool)$this->GetRequest(
            'install_general_active', false, self::GET_VAR_FROM_SESSION
        );

        $aParams['install_lang_current'] = $this->GetRequest(
            'install_lang_current', 'russian', self::GET_VAR_FROM_SESSION
        );
        $aParams['install_lang_default'] = $this->GetRequest(
            'install_lang_default', 'russian', self::GET_VAR_FROM_SESSION
        );

        /**
         * Передаем параметры во Viewer
         */
        foreach ($aParams as $sName => $sParam) {
            /**
             * Если передано булево значение, значит это чек-бокс
             */
            if (!is_bool($sParam)) {
                $this->Assign($sName, trim($sParam));
            } else {
                $this->Assign($sName . '_check', ($sParam) ? 'checked' : '');
            }
        }
        /**
         * Передаем во вьевер список доступных языков
         */
        $aLangs = $this->GetLangList();
        $sLangOptions = '';
        foreach ($aLangs as $sLang) {
            $this->Assign('language_array_item', $sLang);
            $this->Assign(
                'language_array_item_selected',
                ($aParams['install_lang_current'] == $sLang) ? 'selected="selected"' : ''
            );
            $sLangOptions .= $this->FetchString(
                "<option value='___LANGUAGE_ARRAY_ITEM___' ___LANGUAGE_ARRAY_ITEM_SELECTED___>___LANGUAGE_ARRAY_ITEM___</option>"
            );
        }
        $this->Assign('install_lang_options', $sLangOptions);
        /**
         * Передаем во вьевер список доступных языков для дефолтного определения
         */
        $sLangOptions = '';
        foreach ($aLangs as $sLang) {
            $this->Assign('language_array_item', $sLang);
            $this->Assign(
                'language_array_item_selected',
                ($aParams['install_lang_default'] == $sLang) ? 'selected="selected"' : ''
            );
            $sLangOptions .= $this->FetchString(
                "<option value='___LANGUAGE_ARRAY_ITEM___' ___LANGUAGE_ARRAY_ITEM_SELECTED___>___LANGUAGE_ARRAY_ITEM___</option>"
            );
        }
        $this->Assign('install_lang_default_options', $sLangOptions);
        /**
         * Передаем во вьевер список доступных скинов
         */
        $aSkins = $this->GetSkinList();
        $sSkinOptions = '';
        foreach ($aSkins as $sSkin) {
            $this->Assign('skin_array_item', $sSkin);
            $this->Assign(
                'skin_array_item_selected', ($aParams['install_view_skin'] == $sSkin) ? 'selected="selected"' : ''
            );
            $sSkinOptions .= $this->FetchString(
                "<option value='___SKIN_ARRAY_ITEM___' ___SKIN_ARRAY_ITEM_SELECTED___>___SKIN_ARRAY_ITEM___</option>"
            );
        }
        $this->Assign('install_view_skin_options', $sSkinOptions);

        /**
         * Если были переданные данные формы, то обрабатываем добавление
         */
        if ($this->GetRequest('install_extend_params')) {
            $bOk = true;
            $sLocalConfigFile = $this->sConfigDir . '/' . self::LOCAL_CONFIG_FILE_NAME;

            /**
             * Название сайта
             */
            if ($aParams['install_view_name'] && strlen($aParams['install_view_name']) > 2) {
                if ($this->SaveConfig('view.name', $aParams['install_view_name'], $sLocalConfigFile)) {
                    $this->SetSessionVar('install_view_name', $aParams['install_view_name']);
                }
            } else {
                $bOk = false;
                $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('site_name_invalid'));
            }
            /**
             * Описание сайта
             */
            if ($aParams['install_view_description']) {
                if ($this->SaveConfig('view.description', $aParams['install_view_description'], $sLocalConfigFile)) {
                    $this->SetSessionVar('install_view_description', $aParams['install_view_description']);
                }
            } else {
                $bOk = false;
                $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('site_description_invalid'));
            }
            /**
             * Ключевые слова
             */
            if ($aParams['install_view_keywords'] && strlen($aParams['install_view_keywords']) > 2) {
                if ($this->SaveConfig('view.keywords', $aParams['install_view_keywords'], $sLocalConfigFile)) {
                    $this->SetSessionVar('install_view_keywords', $aParams['install_view_keywords']);
                }
            } else {
                $bOk = false;
                $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('site_keywords_invalid'));
            }
            /**
             * Название шаблона оформления
             */
            if ($aParams['install_view_skin'] && strlen($aParams['install_view_skin']) > 1) {
                if ($this->SaveConfig('view.skin', $aParams['install_view_skin'], $sLocalConfigFile)) {
                    $this->SetSessionVar('install_view_skin', $aParams['install_view_skin']);
                }
            } else {
                $bOk = false;
                $this->aMessages[] = array('type' => 'error', 'text' => 'skin_name_invalid');
            }

            /**
             * E-mail, с которого отправляются уведомления
             */
            if ($aParams['install_mail_sender'] && strlen($aParams['install_mail_sender']) > 5) {
                if ($this->SaveConfig('sys.mail.from_email', $aParams['install_mail_sender'], $sLocalConfigFile)) {
                    $this->SetSessionVar('install_mail_sender', $aParams['install_mail_sender']);
                }
            } else {
                $bOk = false;
                $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('mail_sender_invalid'));
            }
            /**
             * Имя, от которого отправляются уведомления
             */
            if ($aParams['install_mail_name'] && strlen($aParams['install_mail_name']) > 1) {
                if ($this->SaveConfig('sys.mail.from_name', $aParams['install_mail_name'], $sLocalConfigFile)) {
                    $this->SetSessionVar('install_mail_name', $aParams['install_mail_name']);
                }
            } else {
                $bOk = false;
                $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('mail_name_invalid'));
            }

            /**
             * Использовать закрытый режим работы сайта
             */
            if ($this->SaveConfig('general.close', $aParams['install_general_close'], $sLocalConfigFile)) {
                $this->SetSessionVar('install_general_close', $aParams['install_general_close']);
            }
            /**
             * Использовать активацию при регистрации
             */
            if ($this->SaveConfig('general.reg.activation', $aParams['install_general_active'], $sLocalConfigFile)) {
                $this->SetSessionVar('install_general_active', $aParams['install_general_active']);
            }
            /**
             * Использоватьт режим регистрации по приглашению
             */
            if ($this->SaveConfig('general.reg.invite', $aParams['install_general_invite'], $sLocalConfigFile)) {
                $this->SetSessionVar('install_general_invite', $aParams['install_general_invite']);
            }

            /**
             * Текущий язык
             */
            if ($aParams['install_lang_current'] && strlen($aParams['install_lang_current']) > 1) {
                if ($this->SaveConfig('lang.current', $aParams['install_lang_current'], $sLocalConfigFile)) {
                    $this->SetSessionVar('install_lang_current', $aParams['install_lang_current']);
                    /**
                     * Если выбран русский язык, то перезаписываем название блога
                     */
                    if ($aParams['install_lang_current'] == 'russian') {
                        $aDbParams = $this->GetSessionVar('INSTALL_DATABASE_PARAMS');
                        $oDb = $this->ValidateDBConnection($aDbParams);

                        if ($oDb && $this->SelectDatabase($aDbParams['name'])) {
                            $this->UpdateUserBlog(
                                'Блог им. ' . $this->GetSessionVar('install_admin_login'), $aDbParams['prefix']
                            );
                        }
                    }
                }
            } else {
                $bOk = false;
                $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('lang_current_invalid'));
            }
            /**
             * Язык, который будет использоваться по умолчанию
             */
            if ($aParams['install_lang_default'] && strlen($aParams['install_lang_default']) > 1) {
                if ($this->SaveConfig('lang.default', $aParams['install_lang_default'], $sLocalConfigFile)) {
                    $this->SetSessionVar('install_lang_default', $aParams['install_lang_default']);
                }
            } else {
                $bOk = false;
                $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('lang_default_invalid'));
            }
        }

        return ($this->GetRequest('install_step_next'))
            ? $this->StepFinish()
            : $this->Layout('steps/extend.tpl');
    }

    /**
     * Окончание работы инсталлятора. Предупреждение о необходимости удаления.
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
    protected function ValidateEnviroment() {

        $bOk = true;

        if (!version_compare(PHP_VERSION, ALTO_PHP_REQUIRED, '>=')) {
            $bOk = false;
            $this->Assign('validate_php_version', '<span style="color:red;">' . $this->Lang('no') . '</span>');
        } else {
            $this->Assign('validate_php_version', '<span style="color:green;">' . $this->Lang('yes') . '</span>');
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

        $sLocalConfigPath = $this->sConfigDir . '/config.local.php';
        if (!file_exists($sLocalConfigPath) || !is_writeable($sLocalConfigPath)) {
            // пытаемся создать файл локального конфига
            @copy($this->sConfigDir . '/config.local.php.txt', $sLocalConfigPath);
        }
        if (!is_file($sLocalConfigPath) || !is_writeable($sLocalConfigPath)) {
            $bOk = false;
            $this->Assign('validate_local_config', '<span style="color:red;">' . $this->Lang('no') . '</span>');
        } else {
            $this->Assign('validate_local_config', '<span style="color:green;">' . $this->Lang('yes') . '</span>');
        }

        // * Проверяем доступность и права у соответствующих папок
        $sTempDir = dirname(dirname(__FILE__)) . '/_tmp';
        if (!is_dir($sTempDir) || !is_writable($sTempDir)) {
            $bOk = false;
            $this->Assign('validate_local_temp', '<span style="color:red;">' . $this->Lang('no') . '</span>');
        } else {
            $this->Assign('validate_local_temp', '<span style="color:green;">' . $this->Lang('yes') . '</span>');
        }

        $sLogsDir = dirname(dirname(__FILE__)) . '/_run';
        if (!is_dir($sLogsDir) || !is_writable($sLogsDir)) {
            $bOk = false;
            $this->Assign('validate_local_runtime', '<span style="color:red;">' . $this->Lang('no') . '</span>');
        } else {
            $this->Assign('validate_local_runtime', '<span style="color:green;">' . $this->Lang('yes') . '</span>');
        }

        $sUploadsDir = ALTO_DIR . '/uploads';
        if (!is_dir($sUploadsDir) || !is_writable($sUploadsDir)) {
            $bOk = false;
            $this->Assign('validate_local_uploads', '<span style="color:red;">' . $this->Lang('no') . '</span>');
        } else {
            $this->Assign('validate_local_uploads', '<span style="color:green;">' . $this->Lang('yes') . '</span>');
        }

        $sPluginsDir = ALTO_DIR . '/app/plugins';
        if (!is_dir($sPluginsDir) || !is_writable($sPluginsDir)) {
            $bOk = false;
            $this->Assign('validate_local_plugins', '<span style="color:red;">' . $this->Lang('no') . '</span>');
        } else {
            $this->Assign('validate_local_plugins', '<span style="color:green;">' . $this->Lang('yes') . '</span>');
        }

        return $bOk;
    }

    protected function sqlQuery($sSql) {

        $xResult = mysql_query($sSql);
        if (!$xResult) {
            $this->aErrors[] = mysql_error();
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

        $oDb = @mysql_connect($aParams['server'] . ':' . $aParams['port'], $aParams['user'], $aParams['password']);
        if ($oDb) {
            /**
             * Валидация версии MySQL сервера
             */
            if (!version_compare(mysql_get_server_info(), ALTO_MYSQL_REQUIRED, '>')) {
                $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('valid_mysql_server'));
                return false;
            }

            mysql_query('set names utf8');
            return $oDb;
        }

        $this->aMessages[] = array('type' => 'error', 'text' => $this->Lang('error_db_connection_invalid'));
        return null;
    }

    /**
     * Выбрать базу данных (либо создать в случае необходимости).
     *
     * @param  string $sName
     * @param  bool   $bCreate
     *
     * @return bool
     */
    protected function SelectDatabase($sName, $bCreate = false) {

        if (@mysql_select_db($sName)) {
            return true;
        }

        if ($bCreate) {
            @mysql_query("CREATE DATABASE $sName");
            return @mysql_select_db($sName);
        }
        return false;
    }

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
     * @param $sFileName
     * @param $aParams
     *
     * @return array|bool
     */
    protected function CreateTables($sFileName, $aParams) {

        $aQuery = $this->_loadQueries($sFileName, $aParams);

        // * Смотрим, какие таблицы существуют в базе данных
        $aDbTables = array();
        $aResult = @mysql_query('SHOW TABLES');
        if (!$aResult) {
            $this->aErrors[] = $this->Lang('error_db_no_data');
            return false;
        }
        while ($aRow = mysql_fetch_array($aResult, MYSQL_NUM)) {
            $aDbTables[] = $aRow[0];
        }

        // * Если указано проверить наличие таблицы и она уже существует, то выполнять SQL-дамп не нужно
        if (in_array($aParams['prefix'] . $aParams['check_table'], $aDbTables)) {
            return false;
        }

        // * Выполняем запросы по очереди
        foreach ($aQuery as $sQuery) {
            $sQuery = trim($sQuery);

            // * Заменяем движок, если таковой указан в запросе
            if (isset($aParams['engine'])) {
                $sQuery = str_ireplace('ENGINE=InnoDB', "ENGINE={$aParams['engine']}", $sQuery);
            }

            $bResult = true;
            if ($sQuery != '' && !$this->IsUseDbTable($sQuery, $aDbTables)) {
                $bResult = $bResult && $this->sqlQuery($sQuery);
            }
        }

        return $bResult;
    }

    /**
     * Проверяем, нуждается ли база в конвертации или нет
     *
     * @param  array $aParams
     *
     * @return bool
     */
    protected function ValidateConvertDatabase($aParams) {
        /**
         * Проверяем, нуждается ли база в конвертации или нет
         * Смотрим, какие таблицы существуют в базе данных
         */
        $aDbTables = array();
        $aResult = @mysql_query('SHOW TABLES');
        if (!$aResult) {
            return array('result' => false, 'errors' => array($this->Lang('error_db_no_data')));
        }
        while ($aRow = mysql_fetch_array($aResult, MYSQL_NUM)) {
            $aDbTables[] = $aRow[0];
        }
        /**
         * Смотрим на наличие в базе таблицы prefix_user_note
         */
        return !in_array($aParams['prefix'] . 'user_note', $aDbTables);
    }

    /**
     * Проверяем, нуждается ли база в конвертации из 0.9.7 в 1.0 или нет
     *
     * @param array $aParams
     *
     * @return bool
     */
    protected function ValidateConvertDatabaseToAlto10($aParams) {
        /**
         * Проверяем, нуждается ли база в конвертации или нет
         * Смотрим, какие таблицы существуют в базе данных
         */
        $aDbTables = array();
        $aResult = @mysql_query('SHOW TABLES');
        if (!$aResult) {
            return array('result' => false, 'errors' => array($this->Lang('error_db_no_data')));
        }
        while ($aRow = mysql_fetch_array($aResult, MYSQL_NUM)) {
            $aDbTables[] = $aRow[0];
        }
        /**
         * Смотрим на наличие в базе таблицы prefix_content
         */
        return !in_array($aParams['prefix'] . 'blog_type', $aDbTables);
    }

    /**
     * Конвертирует базу данных версии 0.9.7 в базу данных версии 1.0
     *
     * @param $sFileName
     * @param $aParams
     *
     * @return array
     */
    protected function ConvertDatabaseToAlto10($sFileName, $aParams) {

        if (!$this->ValidateConvertDatabaseToAlto10($aParams)) {
            return array('result' => false, 'errors' => array($this->Lang('error_database_converted_already')));
        }

        $aQuery = $this->_loadQueries($sFileName, $aParams);
        /**
         * Массив для сбора ошибок
         */
        $aErrors = array();

        /**
         * Выполняем запросы по очереди
         */
        foreach ($aQuery as $sQuery) {
            $sQuery = trim($sQuery);
            /**
             * Заменяем движок, если таковой указан в запросе
             */
            if (isset($aParams['engine'])) {
                $sQuery = str_ireplace('ENGINE=InnoDB', "ENGINE={$aParams['engine']}", $sQuery);
            }

            if ($sQuery != '') {
                $bResult = mysql_query($sQuery);
                if (!$bResult) {
                    $aErrors[] = mysql_error();
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
     * @param array $aParams
     *
     * @return bool
     */
    protected function ValidateConvertDatabaseToAlto($aParams) {
        /**
         * Проверяем, нуждается ли база в конвертации или нет
         * Смотрим, какие таблицы существуют в базе данных
         */
        $aDbTables = array();
        $aResult = @mysql_query('SHOW TABLES');
        if (!$aResult) {
            return array('result' => false, 'errors' => array($this->Lang('error_db_no_data')));
        }
        while ($aRow = mysql_fetch_array($aResult, MYSQL_NUM)) {
            $aDbTables[] = $aRow[0];
        }
        /**
         * Смотрим на наличие в базе таблицы prefix_content
         */
        return !in_array($aParams['prefix'] . 'content', $aDbTables);
    }

    /**
     * Конвертирует базу данных LiveStreet 1.0.3 в AltoCMS 1.0
     *
     * @param   $sFileName
     * @param   $aParams
     *
     * @return array
     */
    protected function ConvertDatabaseToAlto($sFileName, $aParams) {

        if (!$this->ValidateConvertDatabaseToAlto($aParams)) {
            return array('result' => false, 'errors' => array($this->Lang('error_database_converted_already')));
        }

        $aQuery = $this->_loadQueries($sFileName, $aParams);
        /**
         * Массив для сбора ошибок
         */
        $aErrors = array();

        /**
         * Выполняем запросы по очереди
         */
        foreach ($aQuery as $sQuery) {
            $sQuery = trim($sQuery);
            /**
             * Заменяем движок, если таковой указан в запросе
             */
            if (isset($aParams['engine'])) {
                $sQuery = str_ireplace('ENGINE=InnoDB', "ENGINE={$aParams['engine']}", $sQuery);
            }

            if ($sQuery != '') {
                $bResult = mysql_query($sQuery);
                if ($bResult === false) {
                    $aErrors[] = mysql_error();
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
     * @param string $sTableName
     * @param string $sFieldName
     * @param string $sType
     */
    public function addEnumTypeDatabase($sTableName, $sFieldName, $sType) {

        $sQuery = "SHOW COLUMNS FROM  `{$sTableName}`";
        if ($res = mysql_query($sQuery)) {
            while ($aRow = mysql_fetch_assoc($res)) {
                if ($aRow['Field'] == $sFieldName) {
                    break;
                }
            }
            if (strpos($aRow['Type'], "'{$sType}'") === FALSE) {
                $aRow['Type'] = str_ireplace('enum(', "enum('{$sType}',", $aRow['Type']);
                $sQuery = "ALTER TABLE `{$sTableName}` MODIFY `{$sFieldName}` " . $aRow['Type'];
                $sQuery .= ($aRow['Null'] == 'NO') ? ' NOT NULL ' : ' NULL ';
                $sQuery .= is_null($aRow['Default']) ? ' DEFAULT NULL ' : " DEFAULT '{$aRow['Default']}' ";
                mysql_query($sQuery);
            }
        }
    }

    /**
     * Проверяет существование таблицы
     *
     * @param string $sTableName
     *
     * @return bool
     */
    public function isTableExistsDatabase($sTableName) {

        $sQuery = "SHOW TABLES LIKE '{$sTableName}'";
        if ($res = mysql_query($sQuery)) {
            return true;
        }
        return false;
    }

    /**
     * Проверяет существование поля таблицы
     *
     * @param string $sTableName
     * @param string $sFieldName
     *
     * @return bool
     */
    public function isFieldExistsDatabase($sTableName, $sFieldName) {

        $sQuery = "SHOW FIELDS FROM `{$sTableName}`";
        if ($res = mysql_query($sQuery)) {
            while ($aRow = mysql_fetch_assoc($res)) {
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

        return array($bOk, $aErrors);
    }

    /**
     * Сохраняет данные об администраторе в базу данных
     *
     * @param  string $sLogin
     * @param  string $sPassword
     * @param  string $sMail
     * @param  string $sPrefix
     *
     * @return bool
     */
    protected function UpdateDBUser($sLogin, $sPassword, $sMail, $sPrefix = 'prefix_') {

        $sQuery = "
        	UPDATE `{$sPrefix}user`
        	SET
        		`user_login`    = '{$sLogin}',
        		`user_mail`     = '{$sMail}',
        		`user_password` = '{$sPassword}'
			WHERE `user_id` = 1";

        return mysql_query($sQuery);
    }

    /**
     * Перезаписывает название блога в базе данных
     *
     * @param  string $sBlogName
     * @param         string [$sPrefix = "prefix_"
     *
     * @return bool
     */
    protected function UpdateUserBlog($sBlogName, $sPrefix = 'prefix_') {

        $sQuery = "
        	UPDATE `{$sPrefix}blog`
        	SET
        		`blog_title`    = '" . mysql_real_escape_string($sBlogName) . "'
			WHERE `blog_id` = 1";

        return mysql_query($sQuery);
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
        /**
         * Получаем список каталогов
         */
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
        /**
         * Получаем список каталогов
         */
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
        $this->SaveConfig('path.root.url', $this->GetPathRootWeb(), $sLocalConfigFile);
        $this->SaveConfig('path.root.dir', $this->GetPathRootServer(), $sLocalConfigFile);

        $aDirs = array();
        $sDirs = trim(str_replace('http://' . $_SERVER['HTTP_HOST'], '', $this->GetPathRootWeb()), '/');
        if ($sDirs != '') {
            $aDirs = explode('/', $sDirs);
        }
        $this->SaveConfig('path.offset_request_url', count($aDirs), $sLocalConfigFile);
    }

    protected function GetPathRootWeb() {
        return
            rtrim('http://' . $_SERVER['HTTP_HOST'], '/') . str_replace('/install/index.php', '', $_SERVER['PHP_SELF'])
            . '/';
    }

    protected function GetPathRootServer() {

        return rtrim(dirname(dirname(__FILE__)), '/') . '/';
    }

}

session_start();
$oInstaller = new Install;
$oInstaller->Run();

// EOF