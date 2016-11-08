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
 * Плагин для смарти
 * Создает смарти-переменную с содержимым блока
 *
 * @param array $aParams
 * @param string $sContent
 * @param Smarty $oSmarty
 * @return string
 */
function smarty_block_var($aParams, $sContent, &$oSmarty) {
    if (empty($aParams['name'])) {
        trigger_error("Hook: missing 'name' parameter", E_USER_WARNING);

        return;
    }

    if ($sContent) {
        $oSmarty->assign($aParams['name'], $sContent);
        if (!(isset($aParams['cache']) && $aParams['cache']==true)) {
            echo $sContent;
        }
    }
}