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
 * Плагин для Smarty
 * Подключает обработчик блоков шаблона (LS-compatible)
 *
 * @param array                    $aParams
 * @param Smarty_Internal_Template $oSmarty
 *
 * @return string
 */
function smarty_insert_block($aParams, &$oSmarty) {

    if (!isset($aParams['block'])) {
        trigger_error('Parameter "block" not define in {insert name="block" ...}', E_USER_WARNING);
        return null;
    }
    $aParams['name'] = $aParams['block'];

    if (!function_exists('smarty_function_widget')) {
        F::IncludeFile(Config::Get('path.smarty.plug') . 'function.widget.php');
    }
    return smarty_function_widget($aParams, $oSmarty);

    /*
    $oEngine = Engine::getInstance();

    $sWidget = ucfirst(basename($aParams['block']));

    $sDelegatedClass = $oEngine->Plugin_GetDelegate('widget', $sWidget);
    if ($sDelegatedClass == $sWidget) {
        // Пробуем получить делегата по старинке, для совместимости с LS
        // * LS-compatible * //
        $sDelegatedClass = $oEngine->Plugin_GetDelegate('block', $sWidget);
    }

    // Если делегатов нет, то определаем класс виджета
    if ($sDelegatedClass == $sWidget) {
        // если указан плагин, то ищем там
        if (isset($aParams['params']) && isset($aParams['params']['plugin'])) {
            $sPlugin = $aParams['params']['plugin'];
        } else {
            $sPlugin = '';
        }
        // Проверяем наличие класса виджета штатными средствами
        $sWidgetClass = E::Widget_FileClassExists($sWidget, $sPlugin, true);

        if (!$sWidgetClass) {
            if ($sPlugin) {
                // Если класс виджета не найден, то пытаемся по старинке задать класс "LS-блока"
                $sWidgetClass = 'Plugin' . ucfirst($aParams['params']['plugin']) . '_Block' . $sWidget;
            } else {
                // Если класс виджета не найден, то пытаемся по старинке задать класс "LS-блока"
                $sWidgetClass = 'Block' . $sWidget;
            }
            // Проверяем делигирование найденного класса
            $sWidgetClass = E::Plugin_GetDelegate('block', $sWidgetClass);
        }

        // Проверяем делигирование найденного класса
        $sWidgetClass = $oEngine->Plugin_GetDelegate('widget', $sWidgetClass);
    } else {
        $sWidgetClass = $sDelegatedClass;
    }

    $sTemplate = $oEngine->Plugin_GetDelegate('template', 'widgets/widget.' . $aParams['block'] . '.tpl');
    if (!F::File_Exists($sTemplate)) {
        //$sTemplate = '';
        // * LS-compatible * //
        $sLsTemplate = $oEngine->Plugin_GetDelegate('template', 'blocks/block.' . $aParams['block'] . '.tpl');
        if (F::File_Exists($sLsTemplate)) {
            $sTemplate = $sLsTemplate;
        }
    }

    //  * параметры
    $aWidgetParams = array();
    if (isset($aParams['params'])) {
        $aWidgetParams = $aParams['params'];
    }

    // * Подключаем необходимый обработчик
    $oWidgetHandler = new $sWidgetClass($aWidgetParams);

    // * Запускаем обработчик
    $sResult = $oWidgetHandler->Exec();

    // Если обработчик ничего не вернул, то рендерим шаблон
    if (!$sResult && $sTemplate)
        $sResult = $oSmarty->fetch($sTemplate);

    return $sResult;
    */
}

// EOF