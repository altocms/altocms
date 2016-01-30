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
 * Абстракция хука, от которой наследуются все хуки
 * Дает возможность создавать обработчики хуков в каталоге /hooks/
 *
 * @package engine
 * @since   1.0
 */
abstract class Hook extends LsObject {
    /**
     * Добавляет обработчик на хук
     * @see ModuleHook::AddExecHook
     *
     * @param string      $sName             Название хука на который вешается обработчик
     * @param string      $sCallBack         Название метода обработчика
     * @param null|string $sClassNameHook    Название класса обработчика, по умолчанию это текущий класс хука
     * @param int         $iPriority         Приоритет обработчика хука, чем выше число, тем больше приоритет - хук обработчик выполнится раньше остальных
     */
    protected function AddHook($sName, $sCallBack, $sClassNameHook = null, $iPriority = 1) {

        if (func_num_args() == 3 && is_integer($sClassNameHook)) {
            $iPriority = $sClassNameHook;
            $sClassNameHook = null;
        }
        if (is_null($sClassNameHook)) {
            $sCallBack = array($this, $sCallBack);
            E::ModuleHook()->AddExecFunction($sName, $sCallBack, $iPriority);
        } else {
            E::ModuleHook()->AddExecHook($sName, $sCallBack, $iPriority, array('sClassName' => $sClassNameHook));
        }
    }

    /**
     * Adds template hook
     *
     * @param string          $sName
     * @param string|callable $sCallBack
     * @param string|null     $sClassNameHook
     * @param int             $iPriority
     */
    protected function AddHookTemplate($sName, $sCallBack, $sClassNameHook = null, $iPriority = 1) {

        if (strpos($sName, 'template_') !== 0) {
            $sName = 'template_' . $sName;
        }
        if (is_string($sCallBack) && substr($sCallBack, -4) == '.tpl') {
            E::ModuleHook()->AddExecFunction($sName, array($this, 'FetchTemplate'), $iPriority, array('template' => $sCallBack));
            return;
        }
        if (func_num_args() == 3 && is_integer($sClassNameHook)) {
            $iPriority = $sClassNameHook;
            $sClassNameHook = null;
        }
        if (is_null($sClassNameHook)) {
            if (is_string($sCallBack)) {
                $sCallBack = array($this, $sCallBack);
            }
            E::ModuleHook()->AddExecFunction($sName, $sCallBack, $iPriority);
        } else {
            E::ModuleHook()->AddExecHook($sName, $sCallBack, $iPriority, array('sClassName' => $sClassNameHook));
        }
    }

    /**
     * LS-compatibility
     * @deprecated
     *
     * @param string $sName
     * @param string $sCallBack
     * @param int    $iPriority
     * @param array  $aParams
     */
    public function AddDelegateHook($sName, $sCallBack, $iPriority = 1, $aParams = array()) {

        if (is_string($iPriority) && !is_numeric($iPriority)) {
            $sClassName = $iPriority;
            $iPriority = $aParams;
            $aParams = array();
        } else {
            $sClassName = __CLASS__;
        }
        $aParams['delegate'] = true;
        $aParams['sClassName'] = $sClassName;
        E::ModuleHook()->AddExecHook($sName, $sCallBack, $iPriority, $aParams);
    }

    /**
     * Обязательный метод в хуке - в нем происходит регистрация обработчиков хуков
     *
     * @abstract
     */
    abstract public function RegisterHook();

    /**
     * Метод для обработки хуков шаблнов
     *
     * @param $aParams
     *
     * @return string
     */
    public function FetchTemplate($aParams) {

        if (isset($aParams['template'])) {
            return E::ModuleViewer()->Fetch($aParams['template']);
        }
        return '';
    }

    /**
     * Sets stop handle flag
     *
     * @since   1.1
     */
    public function StopHookHandle() {

        E::ModuleHook()->StopHookHandle();
    }

    /**
     * Returns current hook name
     *
     * @return string
     *
     * @since   1.1
     */
    public function GetHookName() {

        return E::ModuleHook()->GetHookName();
    }

    /**
     * Returns parameters of current hook handler
     *
     * @return array
     *
     * @since   1.1
     */
    public function GetHookParams() {

        return E::ModuleHook()->GetHookParams();
    }

    /**
     * Returns the parameter of current hook handler
     *
     * @param $iParam
     *
     * @return mixed|null
     */
    public function GetHookParam($iParam) {

        $aParams = $this->GetHookParams();
        if (isset($aParams[$iParam])) {
            return $aParams[$iParam];
        }
        return null;
    }

    /**
     * Returns arguments of current hook handler
     *
     * @return array
     *
     * @since   1.1.9
     */
    public function GetHookArguments() {

        return E::ModuleHook()->GetHookArguments();
    }

    /**
     * Returns the argument of current hook handler
     *
     * @param $xArgument
     *
     * @return mixed|null
     *
     * @since   1.1.9
     */
    public function GetHookArgument($xArgument) {

        $aArguments = $this->GetHookArguments();
        if (isset($aArguments[$xArgument])) {
            return $aArguments[$xArgument];
        }
        return null;
    }

    public function GetSourceResult() {

        return E::ModuleHook()->GetSourceResult();
    }
}

// EOF