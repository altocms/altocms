<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * @package actions
 * @since 1.0
 */
class ActionApi extends Action {


    const ERROR_CMD_NOT_FOUND = "Error #1: 'cmd' param not found in query";
    const ERROR_CMD_NOT_ALLOWED = "Error #2: 'cmd' is not allowed";
    const ERROR_SYSTEM = 'Error #3: System error';
    const ERROR_WRONG_PARAM_LIST = 'Error #4: Wrong param`s list';

    /**
     * Команда API
     * @var
     */
    protected $sCmd = NULL;

    /**
     * Параметры API
     * @var
     */
    protected $aParams = NULL;

    /**
     * Текущий метод обращения к АПИ
     * @var null
     */
    protected $bIsAjax = NULL;

    /**
     * Текщая ошибка
     * @var null
     */
    protected $sError = NULL;

    /**
     * Инициализация
     */
    public function Init() {

        // Закрываем POST запросы
        if ($this->IsPost()) {
            return $this->AccessDenied();
        }

        // Доступ к АПИ возможен как посредством аякса, так и меодом GET
        // пока имеем в виду, что реализованы только методы получения
        // данных.
        if (F::AjaxRequest() && C::Get('module.api.ajax')) {
            $this->bIsAjax = TRUE;
            E::ModuleViewer()->SetResponseAjax('json');

            return TRUE;
        }

        // Если гет-запрос, то установим шаблона для ответа
        if (C::Get('module.api.get')) {
            $this->SetTemplateAction('/../../../api/answer.tpl');

            return TRUE;
        }


        return FALSE;

    }

    /**
     * Метод выода ошибки
     */
    public function EventError() {
        if ($this->bIsAjax) {
            E::ModuleMessage()->AddErrorSingle('error');
            E::ModuleViewer()->AssignAjax('result', json_encode(array('error' => $this->sError)));
        } else {
            E::ModuleViewer()->Assign('result', json_encode(array('error' => $this->sError)));
        }

    }

    public function Access($sEvent) {
        // Получим текущие ошибки от основного метода проверки
        $this->sError = $this->CheckAccess($sEvent);

        if ($this->sError !== TRUE) {
            return R::Action('api', 'error', array('error' => $this->sError));
        }

        return TRUE;
    }

    /**
     * Метод проверки прав доступа пользователя к конкретному ивенту.
     * Кроме того, если всё в порядке, то данный метод устанавливает
     * свойства $sCmd и $aParams
     * @param string $sEvent Наименование ивента, он же - группа API
     *
     * @return bool
     */
    public function CheckAccess($sEvent) {

        // Обнулим свойства
        $this->sCmd = NULL;
        $this->aParams = NULL;

        /**
         * ОБЩИЙ БЛОК ПРОВЕРОК
         */
        // Вызываемый метод АПИ должен быть передан
        if (!($this->sCmd = F::GetRequest('cmd', FALSE, 'get'))) {
            return self::ERROR_CMD_NOT_FOUND;
        }
        // Имя метода АПИ должно быть в списке разрешенных
        $sAllowedMethodsName = 'Allowed' . ucfirst($sEvent) . 'ApiMethods';
        if (!(method_exists($this, $sAllowedMethodsName))) {
            return self::ERROR_CMD_NOT_ALLOWED;
        }
        $aAllowedMethodsName = $this->$sAllowedMethodsName();
        if (!is_array($aAllowedMethodsName)) {
            return self::ERROR_CMD_NOT_ALLOWED;
        }
        if (!in_array($this->sCmd, array_values($aAllowedMethodsName))) {
            return self::ERROR_CMD_NOT_ALLOWED;
        }


        // Получим параметры запроса
        $aParams = F::GetRequest('params', array(), 'get');
        foreach ($aParams as $k => $v) {
            if ($v == 'true') $aParams[$k] = TRUE;
            if ($v == 'false') $aParams[$k] = FALSE;
        }
        // Получим перечень разрешённых параметров
        $sAllowedParams = 'Allowed' . ucfirst($sEvent) . 'ApiParams';
        if (!method_exists($this, $sAllowedParams)) {
            return self::ERROR_SYSTEM;
        }
        // Проверим, что перечень разрешённых АПи всё таки есть, пусть даже и пустой
        if (($aAllowedParams = $this->$sAllowedParams($this->sCmd)) === FALSE) {
            return self::ERROR_SYSTEM;
        }
        // Проверим параметры и разобъём их на две группы, обязательные и не обяхательны
        $this->aParams['required'] = array();
        $this->aParams['other'] = array();
        foreach ($aParams as $sParamName => $sParamValue) {

            // Параметра нет в списке разрешенных, значит он пользовательский
            if (!in_array($sParamName, array_keys($aAllowedParams))) {
                $this->aParams['other'][$sParamName] = (string)$sParamValue; // Пользовательские параметры толко строковые
                continue;
            }

            // Если в списке разрешённых, то проверим тип
            $aParamData = $aAllowedParams[$sParamName];
            if (!is_array($aParamData[1])) {
                $aParamData[1] = array($aParamData[1]);
            }
            $bAllowedType = TRUE;
            foreach ($aParamData[1] as $sDataType) {
                $bAllowedType = $bAllowedType || $this->_CheckTypes($sParamValue, $sDataType);
            }
            if ($bAllowedType) {
                $this->aParams['required'][$sParamName] = $sParamValue;
                // Удалим из разрешённых параметр, который уже обработали.
                // Таким образом на выходе из цикла в этом массие останутся
                // только Не обязательные параметры, которые и не были указаны
                // вот по этому признаку и проверим успешность проверки
                // параметров
                unset($aAllowedParams[$sParamName]);
            }

        }
        // Проверим все ли параметры прошли
        $bError = FALSE;
        foreach ($aAllowedParams as $aAllowedParam) {
            $bError = $bError || $aAllowedParam[0];
        }
        // Остался какой-то разрешенный параметр, может по типу не прошёл, а
        // может его вообще забыли указать...
        if ($bError) {
            return self::ERROR_WRONG_PARAM_LIST;
        }


        switch ($sEvent) {

            // Проверка экшена 'user' на правильность переданных параметров
            // Для этого метода достаточно общей проверки параметров, которая
            // была проведена ниже
            case 'user':
                return TRUE;
                break;
        }

        return TRUE;
    }

    /**
     * Абстрактный метод регистрации евентов.
     * В нём необходимо вызывать метод AddEvent($sEventName, $sEventFunction)
     * Например:
     *      $this->AddEvent('index', 'EventIndex');
     *      $this->AddEventPreg('/^admin$/i', '/^\d+$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventAdminBlog');
     */
    protected function RegisterEvent() {
        $this->AddEventPreg('/^user$/i', 'EventApiUser');
        $this->AddEventPreg('/^topic$/i', 'EventApiTopic');
        $this->AddEventPreg('/^error/i', 'EventError');
    }



    /******************************************************************************************************
     *              МЕТОД USER
     ******************************************************************************************************/
    /**
     * Экшен обработки API вида user/*
     * @return bool
     */
    public function EventApiUser() {

        $this->_ApiResult($this->_GetFullApiKey($this->sCmd), $this->aParams);

        return TRUE;
    }

    /**
     * Возвращает перечень разрешённых методов АПИ для группы
     * 'user', метод может быть расширен сторонними плагинами
     */
    public function AllowedUserApiMethods() {
        return array(
            'info', // Общая информация о конкретном пользователе
        );
    }

    /**
     * Возвращает перечень разрешённых параметров метода АПИ.
     * Это необходимо для того, что бы в обработке метода участвовали
     * только разрешённые параметры, а все остальные передавались назад
     * клиенту без изменений, например для реализации защиты от XSS, когда
     * вместе с разрешенными параметрами передаётся хэш и потом он же
     * проверяе6тся клиентом как показатель того, что результат пришёл
     * от нас, а не от другого сервера
     *
     * В возвращаемом массиве ключ - имя разрешенного параметра, а значение -
     * тип данных и флаг обязательности этого параметра. При его отсутствии
     * запрос будет воспринят как ошибочный
     *
     *
     * @param string $sMethod Метод АПИ, для которого возвращается список разрешённых параметров
     * @return array|bool
     */
    public function AllowedUserApiParams($sMethod) {
        switch ($sMethod) {
            case 'info':
                return array(
                    'uid' => array(TRUE, 'int'),
                    'tpl' => array(FALSE, array('bool', 'string')),
                );
                break;
        }

        return FALSE;
    }






    /******************************************************************************************************
     *              МЕТОД TOPIC
     ******************************************************************************************************/
    /**
     * Экшен обработки API вида topic/*
     * @return bool
     */
    public function EventApiTopic() {

        $this->_ApiResult($this->_GetFullApiKey($this->sCmd), $this->aParams);

        return TRUE;
    }

    /**
     * Возвращает перечень разрешённых методов АПИ для группы
     * 'topic', метод может быть расширен сторонними плагинами
     */
    public function AllowedTopicApiMethods() {
        return array(
            'rating', // Общая информация о рейтинге топика
        );
    }

    /**
     * @param string $sMethod Метод АПИ, для которого возвращается список разрешённых параметров
     * @return array|bool
     */
    public function AllowedTopicApiParams($sMethod) {
        switch ($sMethod) {
            case 'rating':
                return array(
                    'tid' => array(TRUE, 'int'),
                    'tpl' => array(FALSE, array('bool', 'string')),
                );
                break;
        }

        return FALSE;
    }



    /******************************************************************************************************
     *              ОБЩИЕ ЗАЩИЩЁННЫЕ И ПРИВАТНЫЕ МЕТОДЫ
     ******************************************************************************************************/
    /**
     * Получение результата от модуля API
     */
    protected function _ApiResult($sCmd, $aParams) {

        $sApiMethod = '';
        foreach (explode('/', $sCmd) as $sPart) {
            $sApiMethod .= ucfirst($sPart);
        }

        // Если результата нет, выведем ошибку
        if (!E::ModuleApi()->MethodExists($sApiMethod) || !($aResult = E::ModuleApi()->$sApiMethod($aParams['required']))) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('system_error'),
                E::ModuleLang()->Get('error')
            );

            return;
        }

        // Определим формат данных
        if (isset($aParams['required']['tpl']) && $aParams['required']['tpl'] !== FALSE) {
            $sResult = $this->_Fetch($sCmd, $aResult['data'], $aParams['required']['tpl']);
        } else {
            $sResult = $aResult['json'];
        }

        $aResult = array(
            'data'   => $sResult,
            'params' => $aParams['other'],
        );

        $sResult = json_encode($aResult);

        if ($this->bIsAjax) {
            E::ModuleViewer()->AssignAjax('result', $sResult);
        } else {
            E::ModuleViewer()->Assign('result', $sResult);
        }

    }

    /**
     * Рендеринг шаблона
     * @param $sCmd
     * @param $aData
     * @return string
     */
    protected function _Fetch($sCmd, $aData, $sTemplate) {

        $sHtml = '';
        if ($sTpl = $sCmd . '/' . str_replace('/', '.', $sCmd . '.' . (is_string($sTemplate) ? $sTemplate : 'default') . '.tpl')) {
            if (E::ModuleViewer()->TemplateExists($sTpl)) {
                E::ModuleViewer()->Assign($aData);
                $sHtml = E::ModuleViewer()->Fetch($sTpl);
            }
        }

        return $sHtml;

    }

    /**
     * Проверяет значение на соответствие типу
     *
     * @param $sValue
     * @param $sType
     * @return bool
     */
    protected function _CheckTypes($sValue, $sType) {
        if ($sType == 'bool' || $sType == 'boolean') {
            return is_bool($sValue);
        }
        if ($sType == 'int' || $sType == 'integer') {
            return preg_match('/^[\-]?\d+$/', $sValue);
        }
        if ($sType == 'str' || $sType == 'string') {
            return is_string($sValue);
        }
        if ($sType == 'scalar' || $sType == 'number') {
            return is_scalar($sValue);
        }

        return FALSE;
    }

    /**
     * Возвращает полный путь команды API
     * @param $sCmd
     * @return string
     */
    protected function _GetFullApiKey($sCmd) {
        return 'api/' . $this->sCurrentEvent . '/' . $sCmd;
    }
}