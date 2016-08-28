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

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);

/**
 * От этого класса наследуются все остальные
 *
 * @package engine
 * @since   0.9
 */
abstract class LsObject {

    public function __construct() {

    }

    /**
     * Ставим хук на вызов неизвестного метода и считаем что хотели вызвать метод какого либо модуля
     * @see Engine::_CallModule
     *
     * @param $sName
     * @param $aArgs
     *
     * @return mixed
     * @throws Exception
     */
    public function __call($sName, $aArgs) {

        // Ввзовом метода модуля считаем, если есть подчеркивание и оно не в начале
        if (strpos($sName, '_')) {
            return E::getInstance()->_CallModule($sName, $aArgs);
        } else {
            // Если подчеркивания нет, то вызов несуществующего метода
            $oException = new Exception('Method "' . $sName . '" not exists in class "' . get_class($this) . '"');
            $aStack = $oException->getTrace();

            if (!$aStack) {
                $aStack = debug_backtrace();
            }
            // Инвертируем стек вызовов
            $aStack = array_reverse($aStack);
            // И пытаемся определить, откуда был этот некорректный вызов
            foreach ($aStack as $aCaller) {
                if (isset($aCaller['file']) && isset($aCaller['function'])) {
                    if (preg_match('/[A-Z]\w+\_' . preg_quote($sName) . '/', $aCaller['function'])
                        || $aCaller['function'] == $sName
                    ) {
                        $oException->sAdditionalInfo = 'In file ' . $aCaller['file'];
                        if (isset($aCaller['line'])) {
                            $oException->sAdditionalInfo .= ' on line ' . $aCaller['line'];
                        }
                        break;
                    }
                }
            }
            throw $oException;
        }
    }

    public function __get($sName) {

        // LS compatibility
        if ($sName === 'oEngine') {
            $this->oEngine = E::getInstance();
            return $this->oEngine;
        }

        $trace = debug_backtrace();
        $sError = 'Undefined property via __get(): ' . $sName . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'];
        F::SysWarning($sError);

        return null;
    }
}

// EOF