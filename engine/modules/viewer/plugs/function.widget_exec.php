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
 * Плагин для Smarty
 * Позволяет получать данные из конфига
 *
 * @param   array $aParams
 * @param   Smarty_Internal_Template $oSmartyTemplate
 * @return  string
 */
function smarty_function_widget_exec($aParams, $oSmartyTemplate) {
	
    if (!isset($aParams['name'])) {
        trigger_error('Parameter "name" does not define in {widget ...} function', E_USER_WARNING);
        return;
    }
    $sWidgetName = $aParams['name'];
    $aWidgetParams = (isset($aParams['params']) ? $aParams['params'] : array());

    $sWidget = ucfirst(basename($sWidgetName));

    $sDelegatedClass = E::Plugin_GetDelegate('widget', $sWidget);
    if ($sDelegatedClass == $sWidget) {
        // Пробуем получить делегата по старинке, для совместимости с LS
        // * LS-compatible * //
        $sDelegatedClass = E::Plugin_GetDelegate('block', $sWidget);
    }

    // Если делегатов нет, то определаем класс виджета
    if ($sDelegatedClass == $sWidget) {
        if (isset($aParams['params']) && isset($aParams['params']['plugin'])) {
            $sPlugin = $aParams['params']['plugin'];
        } else {
            $sPlugin = '';
        }
        // Проверяем наличие класса виджета штатными средствами
        $sWidgetClass = E::Widget_FileClassExists($sWidget, $sPlugin, true);
        if ($sWidgetClass) {
            // Проверяем делегирование найденного класса
            $sWidgetClass = E::Plugin_GetDelegate('widget', $sWidgetClass);
            if ($sPlugin) {
                $sTemplate = Plugin::GetTemplatePath($sPlugin) . '/widgets/widget.' . $sWidgetName . '.tpl';
                if (!F::File_Exists($sTemplate)) {
                    // * LS-compatible * //
                    $sTemplate = Plugin::GetTemplatePath($aParams['params']['plugin']) . '/blocks/block.' . $sWidgetName . '.tpl';
                }
            } else {
                $sTemplate = E::Plugin_GetDelegate('template', 'widgets/widget.' . $sWidgetName . '.tpl');
                if (!F::File_Exists(Config::Get('path.smarty.template') . $sTemplate)) {
                    // * LS-compatible * //
                    $sTemplate = E::Plugin_GetDelegate('template', 'blocks/block.' . $sWidgetName . '.tpl');
                }
            }
        } else {
            // * LS-compatible * //
            // Класс не найден
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
    } else {
        $sWidgetClass = $sDelegatedClass;
    }

    // * Подключаем необходимый обработчик
    $oWidgetHandler = new $sWidgetClass($aWidgetParams);

    // * Запускаем обработчик
    $sResult = $oWidgetHandler->Exec();

    // Если обработчик ничего не вернул, то рендерим шаблон
    if (!$sResult && $sTemplate) {
        $sResult = $oSmartyTemplate->fetch($sTemplate);
    }

    return $sResult;
}

// EOF