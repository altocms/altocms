<?php
/*-------------------------------------------------------
*
*   LiveStreet Engine Social Networking
*   Copyright © 2008 Mzhelskiy Maxim
*
*--------------------------------------------------------
*
*   Official site: www.livestreet.ru
*   Contact e-mail: rus.engine@gmail.com
*
*   GNU General Public License, version 2:
*   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
---------------------------------------------------------
*/

/**
 * От этого класса наследуются все остальные
 *
 * @package engine
 * @since 1.0
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
     * @return mixed
     * @throws Exception
     */
    public function __call($sName, $aArgs) {
        // Ввзовом метода модуля считаем, если есть подчеркивание и оно не в начале
        if (strpos($sName, '_')) {
            return Engine::getInstance()->_CallModule($sName, $aArgs);
        } else {
            // Если подчеркивания нет, то вызов несуществующего метода
            $oExeption = new Exception('Method "' . $sName . '" not exists in class "' . get_class($this) . '"');
            $aStack = $oExeption->getTrace();

            if (!$aStack) {
                $aStack = debug_backtrace();
            }
            // Инвертируем стек вызовов
            $aStack = array_reverse($aStack);
            // И пытаемся определить, откуда был этот некорректный вызов
            foreach ($aStack as $aCaller) {
                if (isset($aCaller['file']) && isset($aCaller['function'])) {
                    if (preg_match('/[A-Z]\w+\_' . preg_quote($sName) . '/', $aCaller['function']) || $aCaller['function'] == $sName) {
                        $oExeption->sAdditionalInfo = 'In file ' . $aCaller['file'];
                        if (isset($aCaller['line'])) $oExeption->sAdditionalInfo .= ' on line ' . $aCaller['line'];
                        break;
                    }
                }
            }
            throw $oExeption;
        }
    }

}

// EOF