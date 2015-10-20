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
 * Экшен Api
 * REST API для Alto CMS
 *
 * Экшен принимает запрос к АПИ в виде http://example.com/api/method/cmd/[?param1=val1[&...]]
 * Здесь
 *      - method, определяет метод API (объект запроса, идентификатор ресурса), например user, blog, comment
 *      - cmd, команда, требуемое действие над объектом запроса
 *      - params, парметры запроса, его конкретизация
 *
 * Примеры API для работы с объектом пользователя:
 *      - GET: http://example.com/api/user/list           - список всех пользователей
 *      - GET: http://example.com/api/user/1/info         - информация о пользователе с ид. 1
 *      - GET: http://example.com/api/user/1/friends      - друзья пользователя с ид. 1
 *      - GET: http://example.com/api/user/1/comments     - комментарии пользователя с ид. 1
 *      - GET: http://example.com/api/user/1/publications - публикации пользователя с ид. 1
 *      - GET: http://example.com/api/user/1/blogs/       - блоги пользователя с ид. 1
 *      - GET: http://example.com/api/user/1/images       - изображения пользователя с ид. 1
 *      - GET: http://example.com/api/user/1/activity     - активность пользователя с ид. 1
 *
 *
 * @package actions
 * @since 1.0
 */
class ActionApi extends Action {

    /**
     * Текущий метод обращения к АПИ
     * @var null
     */
    protected $bIsAjax = NULL;

    /**
     * Проверяет метод запроса на соответствие
     *
     * @param string $sRequestMethod
     * @return bool
     */
    private function _CheckRequestMethod($sRequestMethod) {

        $sRequestMethod = mb_strtoupper($sRequestMethod);

        if (!in_array($sRequestMethod, array('GET', 'POST', 'PUT', 'DELETE'))) {
            return FALSE;
        }

        return $this->_getRequestMethod() == $sRequestMethod;
    }

    /**
     * Выводит ошибку
     *
     * @param $aError
     * @return string
     */
    protected function _Error($aError) {

        E::ModuleApi()->SetLastError($aError);
        $this->EventError();
    }

    /**
     * Инициализация
     */
    public function Init() {

        /**
         * Установим шаблон вывода
         */
        $this->SetTemplate('api/answer.tpl');

        return TRUE;
    }

    /**
     * Ошибочные экшены отдаём как ошибку неизвестного API метода
     * @return string
     */
    protected function EventNotFound() {

        E::ModuleApi()->SetLastError(E::ModuleApi()->ERROR_CODE_9002);
        $this->EventError();
    }

    /**
     * Метод выода ошибки
     */
    public function EventError() {

        // Запретим прямой доступ
        if (!($aError = E::ModuleApi()->GetLastError())) {
            $aError = E::ModuleApi()->ERROR_CODE_9002;
        }

        if ($aError['code'] == '0004') {
            F::HttpResponseCode(403);
        } else {
            // Установим код ошибки - Bad Request
            F::HttpResponseCode(400);
        }

        // Отправим ошибку пользователю
        if ($this->bIsAjax) {
            E::ModuleMessage()->AddErrorSingle('error');
            E::ModuleViewer()->AssignAjax('result', json_encode(array('error' => $aError)));
        } else {
            E::ModuleViewer()->Assign('result', json_encode(array('error' => $aError)));
        }

        E::ModuleApi()->SetLastError(NULL);

        return FALSE;
    }

    /**
     * Проверка на право доступа к методу API
     *
     * @param string $sEvent
     * @return bool|string
     */
    public function Access($sEvent) {

        // Возможно это ajax-запрос, тогда нужно проверить разрешены ли
        // вообще такие запросы к нашему API
        if (F::AjaxRequest()) {
            if (C::Get('module.api.ajax')) {
                $this->bIsAjax = TRUE;
                E::ModuleViewer()->SetResponseAjax('json');
            } else {
                return $this->_Error(E::ModuleApi()->ERROR_CODE_9014);
            }
        } else {
            // Проверим, разрешённые типы запросов к АПИ
            foreach (array(
                         'post'   => E::ModuleApi()->ERROR_CODE_9010,
                         'get'    => E::ModuleApi()->ERROR_CODE_9011,
                         'put'    => E::ModuleApi()->ERROR_CODE_9012,
                         'delete' => E::ModuleApi()->ERROR_CODE_9013
                     ) as $sRequestMethod => $aErrorDescription) {
                if ($this->_CheckRequestMethod($sRequestMethod) && !C::Get("module.api.{$sRequestMethod}")) {
                    return $this->_Error($aErrorDescription);
                }
            }
        }

        return TRUE;
    }

    /**
     * @param null $sEvent
     *
     * @return string
     */
    public function AccessDenied($sEvent = null) {

        return $this->_Error(E::ModuleApi()->ERROR_CODE_9004);
    }

    /**
     * Получает все параметры указанного метода запроса вместе с требуемым действием
     *
     * @param array  $aData
     * @param string $sRequestMethod Метод запроса
     *
     * @return array
     */
    protected function _GetParams($aData, $sRequestMethod) {

        $sRequestMethod = strtoupper($sRequestMethod);
        $aParams = $this->_getRequestData($sRequestMethod);

        foreach ($aParams as $k => $v) {
            if (strtoupper($aParams[$k]) == 'TRUE') $aParams[$k] = TRUE;
            if (strtoupper($aParams[$k]) == 'FALSE') $aParams[$k] = FALSE;
        }

        return array_merge($aData, array('params' => $aParams));
    }

    /**
     * Абстрактный метод регистрации евентов.
     * В нём необходимо вызывать метод AddEvent($sEventName, $sEventFunction)
     * Например:
     *      $this->AddEvent('index', 'EventIndex');
     *      $this->AddEventPreg('/^admin$/i', '/^\d+$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventAdminBlog');
     */
    protected function RegisterEvent() {

        // Установим экшены ресурсов
        $this->AddEventPreg('/^user$/i', '/^\d+$/i', '/^info$/i', 'EventApiUserIdInfo');
        $this->AddEventPreg('/^topic$/i', '/^\d+$/i', '/^info$/i', 'EventApiTopicIdInfo');
        $this->AddEventPreg('/^blog$/i', '/^\d+$/i', '/^info$/i', 'EventApiBlogIdInfo');

        // И экшен ошибки
        $this->AddEventPreg('/^error/i', 'EventError');
    }


    /******************************************************************************************************
     *              МЕТОД USER
     ******************************************************************************************************/
    /**
     * Экшен обработки API вида 'api/user/id/info'
     * @return bool
     */
    public function EventApiUserIdInfo() {

        $sErrorDescription = $this->_ApiResult(
            'api/user/id/info',
            $this->_GetParams(array('uid' => R::GetParam(0), 'cmd' => R::GetParam(1)), 'GET')
        );

        if ($sErrorDescription !== FALSE) {
            return $this->_Error($sErrorDescription);
        }

        return TRUE;
    }


    /******************************************************************************************************
     *              МЕТОД TOPIC
     ******************************************************************************************************/
    /**
     * Экшен обработки API вида topic/*
     * @return bool
     */
    public function EventApiTopicIdInfo() {


        $sErrorDescription = $this->_ApiResult(
            'api/topic/id/rating',
            $this->_GetParams(array('tid' => R::GetParam(0), 'cmd' => R::GetParam(1)), 'GET')
        );

        if ($sErrorDescription !== FALSE) {
            return $this->_Error($sErrorDescription);
        }

        return TRUE;
    }


    /******************************************************************************************************
     *              МЕТОД BLOG
     ******************************************************************************************************/
    /**
     * Экшен обработки API вида topic/*
     * @return bool
     */
    public function EventApiBlogIdInfo() {


        $sErrorDescription = $this->_ApiResult(
            'api/blog/id/info',
            $this->_GetParams(array('uid' => R::GetParam(0), 'cmd' => R::GetParam(1)), 'GET')
        );

        if ($sErrorDescription !== FALSE) {
            return $this->_Error($sErrorDescription);
        }

        return TRUE;
    }


    /******************************************************************************************************
     *              ОБЩИЕ ЗАЩИЩЁННЫЕ И ПРИВАТНЫЕ МЕТОДЫ
     ******************************************************************************************************/
    /**
     * Получение результата от модуля API
     * @param string $sResourceName Имя объекта ресурса
     * @param array $aData Данные для формировния ресурса
     * @return string
     */
    protected function _ApiResult($sResourceName, $aData) {

        $sApiMethod = '';
        foreach (explode('/', $sResourceName) as $sPart) {
            $sApiMethod .= ucfirst($sPart);
        }

        // Если результата нет, выведем ошибку плохого ресурса
        if (!E::ModuleApi()->MethodExists($sApiMethod)) {
            return E::ModuleApi()->ERROR_CODE_9001;
        }
        // Или отсутствие ресурса
        if (!($aResult = E::ModuleApi()->$sApiMethod($aData))) {
            return E::ModuleApi()->ERROR_CODE_9003;
        }

        // Определим формат данных
        if (!empty($aData['params']['tpl'])) {
            $sTemplate = $aData['params']['tpl'];
        } elseif(!empty($aData['params']['role']) && $aData['params']['role'] == 'popover') {
            $sTemplate = 'default';
        } else {
            $sTemplate = null;
        }
        if ($sTemplate) {
            $sResult = $this->_Fetch($sResourceName, $aResult['data'], $sTemplate);
        } else {
            $sResult = $aResult['json'];
        }

        $aResult = array(
            'data'   => $sResult,
            'params' => $aData['params'],
        );

        $sResult = json_encode($aResult);

        if ($this->bIsAjax) {
            E::ModuleViewer()->AssignAjax('result', $sResult);
        } else {
            E::ModuleViewer()->Assign('result', $sResult);
        }

        return FALSE;
    }

    /**
     * Рендеринг шаблона
     *
     * @param string      $sCmd
     * @param array       $aData
     * @param string|null $sTemplate
     *
     * @return string
     */
    protected function _Fetch($sCmd, $aData, $sTemplate = null) {

        /** @var ModuleViewer $oLocalViewer */
        $oLocalViewer = E::ModuleViewer()->GetLocalViewer();

        $sHtml = '';
        if ($sTpl = $sCmd . '/' . str_replace('/', '.', $sCmd . '.' . (is_string($sTemplate) ? $sTemplate : 'default') . '.tpl')) {
            if (!$oLocalViewer->TemplateExists($sTpl)) {
                $sTpl = $sCmd . '/' . str_replace('/', '.', $sCmd . '.' . 'default.tpl');
            }
            $oLocalViewer->Assign($aData);
            $sHtml = $oLocalViewer->Fetch($sTpl);
        }

        return $sHtml;
    }

}

// EOF