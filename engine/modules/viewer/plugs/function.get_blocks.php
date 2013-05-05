<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
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
 * Загружает в переменную список блоков
 * LS-compatible
 *
 * @param   array $params
 * @param   $smarty
 * @return  string
 */
function smarty_function_get_blocks($params, &$smarty) {

    if (!array_key_exists('assign', $params)) {
        trigger_error('get_blocks: missing "assign" parameter', E_USER_WARNING);
        return;
    }

    if (E::ActivePlugin('ls')) {
        $smarty->assign($params['assign'], Engine::getInstance()->Viewer_GetBlocks(true));
    }
    return '';
}

// EOF