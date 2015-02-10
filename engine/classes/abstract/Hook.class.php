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
        if ((func_num_args() < 4) && (is_null($sClassNameHook) || is_int($sClassNameHook))) {
            $iPriority = $sClassNameHook;
            E::ModuleHook()->AddExecFunction($sName, $sCallBack, $iPriority);
        } else {
            E::ModuleHook()->AddExecHook($sName, $sCallBack, $iPriority, array('sClassName' => $sClassNameHook));
        }
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
     * Ставим хук на вызов неизвестного метода и считаем что хотели вызвать метод какого либо модуля
     * @see Engine::_CallModule
     *
     * @param string $sName Имя метода
     * @param array  $aArgs Аргументы
     *
     * @return mixed
     */
    public function __call($sName, $aArgs) {

        return E::getInstance()->_CallModule($sName, $aArgs);
    }
}

// EOF