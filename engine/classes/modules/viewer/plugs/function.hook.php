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
 * Плагин для смарти
 * Запускает хуки из шаблона на выполнение
 *
 * @param   array  $aParams
 * @param   Smarty $oSmarty
 *
 * @return  string
 */
function smarty_function_hook($aParams, &$oSmarty) {

    if (empty($aParams['run'])) {
        trigger_error('Hook: missing "run" parametr', E_USER_WARNING);
        return;
    }

    $sHookName = 'template_' . strtolower($aParams['run']);
    unset($aParams['run']);
    $aResultHook = E::Hook_Run($sHookName, $aParams);

    $sReturn = '';
    if (array_key_exists('template_result', $aResultHook)) {
        $sReturn = join('', $aResultHook['template_result']);
    }

    if (!empty($aParams['assign'])) {
        $oSmarty->assign($aParams['assign'], $sReturn);
    } else {
        return $sReturn;
    }
}

// EOF