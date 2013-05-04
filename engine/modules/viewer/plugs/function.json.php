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
 * Позволяет транслировать данные в json
 *
 * @param  $params
 * @param  $smarty
 * @return string
 */
function smarty_function_json($params, &$smarty) {
	
    if (!array_key_exists('var', $params)) {
        trigger_error("json: missing 'var' parameter", E_USER_WARNING);
        return;
    }

    if (class_exists('Entity') && is_object($params['var']) && $params['var'] instanceof Entity) {
        $oEntity = $params['var'];
        $aMethods = null;
        if (!empty($params['methods'])) {
            $aMethods = is_array($params['methods'])
                ? $params['methods']
                : explode(',', $params['methods']);
        }
        //$var = func_convert_entity_to_array($params['var'], $aMethods);
        $var = $oEntity->ToArray($aMethods);
    } else {
        $var = $params['var'];
    }

    $_contents = F::jsonEncode($var);

    if (!empty($params['assign'])) {
        $smarty->assign($params['assign'], $_contents);
    } else {
        return $_contents;
    }
}

// EOF