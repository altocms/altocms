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
 * Загружает список языковых текстовок в шаблон
 *
 * @param  $params
 * @param  $smarty
 *
 * @return array|null;
 */
function smarty_function_lang_load($params, &$smarty) {

    if (!array_key_exists('name', $params)) {
        trigger_error("lang_load: missing 'name' parameter", E_USER_WARNING);
        return;
    }

    $aLangName = explode(',', $params['name']);

    $aLangMsg = array();
    foreach ($aLangName as $sName) {
        $aLangMsg[$sName] = E::ModuleLang()->Get(trim($sName), array(), false);
    }

    if (!isset($params['json']) || $params['json'] !== false) {
        $aLangMsg = F::jsonEncode($aLangMsg);
    }

    if (!empty($params['assign'])) {
        $smarty->assign($params['assign'], $aLangMsg);
    } else {
        return $aLangMsg;
    }
}

// EOF