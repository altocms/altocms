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
 * Модуль обработки хуков(hooks)
 * В различных местах кода могут быть определеные вызовы хуков, например:
 * <pre>
 * E::ModuleHook()->Run('topic_edit_before', array('oTopic'=>$oTopic,'oBlog'=>$oBlog));
 * </pre>
 * Данный вызов "вешает" хук "topic_edit_before"
 * Чтобы повесить обработчик на этот хук, его нужно объявить, например, через файл в /classes/hooks/HookTest.class.php
 * <pre>
 * class HookTest extends Hook {
 *    // Регистрируем хуки (вешаем обработчики)
 *    public function RegisterHook() {
 *        $this->AddHook('topic_edit_before','TopicEdit');
 *    }
 *    // обработчик хука
 *    public function TopicEdit($aParams) {
 *        $oTopic=$aParams['oTopic'];
 *        $oTopic->setTitle('My title!');
 *    }
 * }
 * </pre>
 * В данном примере после редактирования топика заголовок у него поменяется на "My title!"
 *
 * Если хук объявлен в шаблоне, например,
 * <pre>
 * {hook run='html_head_end'}
 * </pre>
 * То к имени хука автоматически добаляется префикс "template_" и обработчик на него вешать нужно так:
 * <pre>
 * $this->AddHook('template_html_head_end','InjectHead');
 * </pre>
 *
 * Так же существуют блочные хуки, который объявляются в шаблонах так:
 * <pre>
 * {hookb run="registration_captcha"}
 * ... html ...
 * {/hookb}
 * </pre>
 * Они позволяют заменить содержимое между {hookb ..} {/hookb} или добавть к нему произвольный контент. К имени такого хука добавляется префикс "template_block_"
 * <pre>
 * class HookTest extends Hook {
 *    // Регистрируем хуки (вешаем обработчики)
 *    public function RegisterHook() {
 *        $this->AddHook('template_block_registration_captcha','MyCaptcha');
 *    }
 *    // обработчик хука
 *    public function MyCaptcha($aParams) {
 *        $sContent=$aParams['content'];
 *        return $sContent.'My captcha!';
 *    }
 * }
 * </pre>
 * В данном примере в конце вывода каптчи будет добавлено "My captcha!"
 * Обратите внимаете, что в обработчик в параметре "content" передается исходное содержание блока.
 *
 * @package engine.modules
 * @since   1.0
 */
class ModuleHook extends Module {
    /**
     * Содержит список хуков
     *
     * @var array( 'name' => array(
     *        array(
     *            'type' => 'module' | 'hook' | 'function',
     *            'callback' => 'callback_name',
     *            'priority'    => 1,
     *            'params' => array()
     *        ),
     *    ),
     * )
     */
    protected $aHooks = array();

    protected $aHooksOrders = array();
    /**
     * Список объектов обработки хукков, для их кешировани
     *
     * @var array
     */
    protected $aHooksObject = array();

    protected $aObservers = array();

    protected $bStopHandle = false;

    protected $sCurrentHookName;

    protected $aCurrentHookOptions = array();

    protected function _parseCallback($xCallback, $sClass = null) {

        $aResult = array(
            'function' => $xCallback,
            'class' => null,
            'object' => null,
            'method' => null,
        );
        if (is_array($xCallback) && sizeof($xCallback) == 2) {
            list($oObject, $sMethod) = $xCallback;
            if (is_object($oObject) && is_string($sMethod)) {
                $aResult['function'] = null;
                $aResult['class'] = null;
                $aResult['object'] = $oObject;
                $aResult['method'] = $sMethod;
            } elseif (is_string($oObject) && is_string($sMethod)) {
                $aResult['function'] = $oObject . '::' . $sMethod;
            }
        } elseif ($sClass) {
            // LS-compatibility
            $aResult['class'] = $sClass;
            $aResult['method'] = $aResult['function'];
            $aResult['function'] = null;
        }
        return $aResult;
    }

    /**
     * Инициализация модуля
     *
     */
    public function Init() {

    }

    /**
     * Возвращает информацию о том, включен хук или нет
     *
     * @param string $sHookName
     *
     * @return bool
     */
    public function IsEnabled($sHookName) {

        return isset($this->aHooks[$sHookName]);
    }

    /**
     * Adds handler for hook
     * Call format:
     *   AddHandler($sHookName, $xCallBack [, $iPriority] [, $aParams])
     *
     * @param string $sHookName Hook name
     * @param string $xCallBack Callback function to run for the hook
     * @param int    $iPriority
     * @param array  $aParams
     *
     * @return bool
     *
     * @since   1.1
     */
    public function AddHandler($sHookName, $xCallBack, $iPriority = 1, $aParams = array()) {

        if (is_array($iPriority) && func_num_args() == 3) {
            $aParams = $iPriority;
            $iPriority = null;
        }
        $bResult = $this->Add($sHookName, 'function', $xCallBack, $iPriority, $aParams);
        $aHook = end($this->aHooks[$sHookName]);
        $this->_notifyObserver(array($aHook), $this->aObservers);
        return $bResult;
    }

    /**
     * Adds observer to be notified of new handlers
     *
     * @param string       $sHookName
     * @param string|array $xCallBack
     * @param bool         $bStrict
     */
    public function AddObserver($sHookName, $xCallBack, $bStrict = false) {

        $this->aObservers[] = array(
            'hook' => $sHookName,
            'callback' => $this->_parseCallback($xCallBack),
            'strict' => $bStrict,
        );
        $aObserver = end($this->aObservers);
        if ($this->aHooks) {
            $this->_notifyObserver($this->aHooks, array($aObserver));
        }
    }

    /**
     * Notifies observers about hooks
     *
     * @param array $aHooks
     * @param array $aObservers
     */
    protected function _notifyObserver($aHooks, $aObservers) {

        foreach ($aObservers as $aObserver) {
            foreach ($aHooks as $sHookName => $aHookParams) {
                if ($aObserver['strict']) {
                    $bNotify = ($sHookName === $aObserver['hook']);
                } else {
                    $bNotify = (strpos($sHookName, $aObserver['hook']) === 0);
                }
                if ($bNotify) {
                    if (!empty($aObserver['callback']['object'])) {
                        $oObject = $aObserver['callback']['object'];
                        $sMethod = $aObserver['callback']['method'];
                        $oObject->$sMethod($sHookName);
                    } elseif (!empty($aObserver['callback']['class'])) {
                        $sClass = $aObserver['callback']['class'];
                        $sMethod = $aObserver['callback']['method'];
                        $oObject = new $sClass();
                        $oObject->$sMethod($sHookName);
                    } elseif (!empty($aObserver['callback']['function'])) {
                        call_user_func($aObserver['callback']['function'], $sHookName);
                    }
                }
            }
        }
    }

    /**
     * Добавление обработчика на хук
     *
     * @param string $sHookName    Имя хука
     * @param string $sType        Тип хука, возможны: module, function, hook
     * @param string $xCallback    Функция/метод обработки хука
     * @param int    $iPriority    Приоритер обработки, чем выше, тем раньше сработает хук относительно других
     * @param array  $aParams      Список дополнительных параметров, анпример, имя класса хука
     *
     * @return bool
     */
    public function Add($sHookName, $sType, $xCallback, $iPriority = 1, $aParams = array()) {

        $sHookName = strtolower($sHookName);
        // LS-compatibility
        if ($sHookName == 'init_action') {
            $sHookName = 'action_before';
        }

        $sType = strtolower($sType);
        if (!in_array($sType, array('module', 'hook', 'function', 'template'))) {
            return false;
        }

        if (is_array($xCallback)) {
            $aCallback = $this->_parseCallback($xCallback);
        } else {
            if ($sType == 'module') {
                $aCallback = $this->_parseCallback(array('E', $xCallback));
            } elseif (isset($aParams['sClassName'])) {
                $aCallback = $this->_parseCallback($xCallback, $aParams['sClassName']);
                unset($aParams['sClassName']);
            } else {
                $aCallback = $this->_parseCallback($xCallback);
            }
        }

        $this->aHooks[$sHookName][] = array(
            'type' => $sType,
            'callback' => $aCallback,
            'params' => $aParams,
            'priority' => (int)$iPriority
        );
        if (!empty($this->aHooksOrders)) {
            $this->aHooksOrders = array();
        }

        if ($this->aObservers) {
            $aHook = end($this->aHooks[$sHookName]);
            $this->_notifyObserver(array($aHook), $this->aObservers);
        }

        return true;
    }

    /**
     * Добавляет обработчик хука с типом "module"
     * Позволяет в качестве обработчика использовать метод модуля
     *
     * @see Add
     *
     * @param string $sName        Имя хука
     * @param string $sCallBack    Полное имя метода обработки хука в LS формате: "Module_Method"
     * @param int    $iPriority    Приоритер обработки, чем выше, тем раньше сработает хук относительно других
     *
     * @return bool
     */
    public function AddExecModule($sName, $sCallBack, $iPriority = 1) {

        return $this->Add($sName, 'module', $sCallBack, $iPriority);
    }

    /**
     * Добавляет обработчик хука с типом "function"
     * Позволяет в качестве обработчика использовать функцию
     *
     * @see Add
     *
     * @param string $sName        Имя хука
     * @param string $sCallBack    Функция обработки хука, например, "var_dump"
     * @param int    $iPriority    Приоритер обработки, чем выше, тем раньше сработает хук относительно других
     * @param array  $aParams      Параметры
     *
     * @return bool
     */
    public function AddExecFunction($sName, $sCallBack, $iPriority = 1, $aParams = array()) {

        return $this->Add($sName, 'function', $sCallBack, $iPriority, $aParams);
    }

    /**
     * Добавляет обработчик хука с типом "hook"
     * Позволяет в качестве обработчика использовать метод хука(класса хука из каталога /classes/hooks/)
     *
     * @see Add
     * @see Hook::AddHook
     *
     * @param string $sName        Имя хука
     * @param string $sCallBack    Метод хука, например, "InitAction"
     * @param int    $iPriority    Приоритер обработки, чем выше, тем раньше сработает хук относительно других
     * @param array  $aParams      Параметры
     *
     * @return bool
     */
    public function AddExecHook($sName, $sCallBack, $iPriority = 1, $aParams = array()) {

        return $this->Add($sName, 'hook', $sCallBack, $iPriority, $aParams);
    }

    /**
     * Запускает обаботку хуков
     *
     * @param string $sName        Имя хука
     * @param array  $aVars        Список параметров хука, передаются в обработчик
     * @param bool   $bArgsAsArray аргументы передаются, как массив
     *
     * @return array
     */
    public function Run($sName, $aVars = array(), $bArgsAsArray = true) {

        $xResult = array();
        $sName = strtolower($sName);

        if (isset($this->aHooks[$sName])) {
            $this->sCurrentHookName = strtolower($sName);
            $bTemplateHook = (strpos($this->sCurrentHookName, 'template_') === 0 ? true : false);
            if (empty($this->aHooksOrders[$this->sCurrentHookName])) {
                $this->aHooksOrders = array();
                $iCount = count($this->aHooks[$this->sCurrentHookName]);
                for ($iHookNum = 0; $iHookNum < $iCount; $iHookNum++) {
                    $this->aHooksOrders[$this->sCurrentHookName][$iHookNum] = $this->aHooks[$this->sCurrentHookName][$iHookNum]['priority'];
                }
                arsort($this->aHooksOrders[$this->sCurrentHookName], SORT_NUMERIC);
            }

            $this->bStopHandle = false;
            // Runs hooks in priority order
            foreach ($this->aHooksOrders[$this->sCurrentHookName] as $iHookNum => $iPr) {
                $this->aCurrentHookOptions = $this->aHooks[$this->sCurrentHookName][$iHookNum];
                if ($bTemplateHook || $this->aCurrentHookOptions['type'] == 'template') {
                    if (isset($this->aCurrentHookOptions['params']['template']) && !isset($aVars['template'])) {
                        $aVars['template'] = $this->aCurrentHookOptions['params']['template'];
                    }
                }

                if (isset($this->aCurrentHookOptions['callback'])) {
                    $aCallback = $this->aCurrentHookOptions['callback'];
                    if (!empty($aCallback['object']) || !empty($aCallback['class']) || !empty($aCallback['function'])) {
                        if (!empty($aCallback['object'])) {
                            $oObject = $aCallback['object'];
                            $sMethod = $aCallback['method'];
                        } elseif (!empty($aCallback['class'])) {
                            $sClass = $aCallback['class'];
                            $sMethod = $aCallback['method'];
                            $oObject = new $sClass();
                        } else {
                            $oObject = null;
                            $sMethod = null;
                        }

                        if ($bArgsAsArray) {
                            if ($oObject) {
                                $xHookResult = $oObject->$sMethod($aVars);
                            } else {
                                $xHookResult = call_user_func_array($aCallback['function'], array(&$aVars));
                            }
                        } else {
                            if ($oObject) {
                                $xHookResult = call_user_func_array(array($oObject, $sMethod), $aVars);
                            } else {
                                $xHookResult = call_user_func_array($aCallback['function'], $aVars);
                            }
                        }

                        if ($bTemplateHook) {
                            // * Если это шаблонный хук, то сохраняем результат
                            $xResult['template_result'][] = $xHookResult;
                        } else {
                            $xResult = $xHookResult;
                        }
                    }
                }

                if ($this->bStopHandle) {
                    break;
                }
            }
            $this->sCurrentHookName = null;
        }
        return $xResult;
    }

    /**
     * Returns current hook name
     *
     * @return string
     *
     * @since   1.1
     */
    public function GetHookName() {

        return $this->sCurrentHookName;
    }

    /**
     * Returns parameters of current hook handler
     *
     * @return array
     *
     * @since   1.1
     */
    public function GetHookParams() {

        if (isset($this->aCurrentHookOptions['params'])) {
            return $this->aCurrentHookOptions['params'];
        }
        return array();
    }

    /**
     * Sets stop handle flag
     *
     * @since   1.1
     */
    public function StopHookHandle() {

        $this->bStopHandle = true;
    }

}

// EOF