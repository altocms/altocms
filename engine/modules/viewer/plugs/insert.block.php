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
 * @param array $aParams
 * @param Smarty $oSmarty
 * @return string
 */
function smarty_insert_block($aParams, &$oSmarty)
{
    if (!isset($aParams['block'])) {
        trigger_error('Parameter "block" not define in {insert name="block" ...}', E_USER_WARNING);
        return;
    }
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
            $sWidgetClass = 'Plugin' . ucfirst($aParams['params']['plugin']) . '_Widget' . $sWidget;
            /**
             * TODO: Сделать проверку существования файла класса по-человечески
             */
            $sFile = F::File_RootDir() . '/plugins/' . $aParams['params']['plugin'] . '/classes/widgets/Widget' . $sWidget . '.class.php';
            if (!F::File_Exists($sFile)) {
                // Если такого класса нет, то пытаемся по старинке задать класс "LS-блока" плагина
                $sWidgetClass = 'Plugin' . ucfirst($aParams['params']['plugin']) . '_Block' . $sWidget;
            }
            $sTemplate = Plugin::GetTemplatePath($aParams['params']['plugin']) . '/widgets/widget.' . $aParams['block'] . '.tpl';
            if (!F::File_Exists($sTemplate)) {
                // * LS-compatible * //
                $sTemplate = Plugin::GetTemplatePath($aParams['params']['plugin']) . '/blocks/block.' . $aParams['block'] . '.tpl';
            }
        } else {
            $sWidgetClass = 'Widget' . $sWidget;
            /**
             * TODO: Сделать проверку существования файла класса по-человечески
             */
            $sFile = F::File_RootDir() . '/classes/widgets/Widget' . $sWidget . '.class.php';
            if (!F::File_Exists($sFile)) {
                // Если такого класса нет, то пытаемся по старинке задать класс "LS-блока"
                $sWidgetClass = 'Block' . $sWidget;
            }
            $sTemplate = $oEngine->Plugin_GetDelegate('template', 'widgets/widget.' . $aParams['block'] . '.tpl');
            if (!F::File_Exists($sTemplate)) {
                // * LS-compatible * //
                $sTemplate = $oEngine->Plugin_GetDelegate('template', 'blocks/block.' . $aParams['block'] . '.tpl');
            }
        }
        // Проверяем делигирование найденного класса
        $sWidgetClass = $oEngine->Plugin_GetDelegate('widget', $sWidgetClass);
    } else {
        $sWidgetClass = $sDelegatedClass;
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
}

// EOF