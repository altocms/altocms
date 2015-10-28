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
 * Plugin for Smarty
 *
 * @param   array                    $aParams
 * @param   Smarty_Internal_Template $oSmartyTemplate
 *
 * @return  string|null
 */
function smarty_function_widget_exec($aParams, $oSmartyTemplate) {

    if (!isset($aParams['name'])) {
        trigger_error('Parameter "name" does not define in {widget ...} function', E_USER_WARNING);
        return null;
    }

    $sWidgetName = $aParams['name'];
    $sPlugin = (!empty($aParams['plugin']) ? $aParams['plugin'] : '');
    $aWidgetParams = (isset($aParams['params']) ? array_merge($aParams['params'], $aParams): $aParams);

    $sWidget = ucfirst(basename($sWidgetName));
    $sTemplate = '';

    $sDelegatedClass = E::ModulePlugin()->GetDelegate('widget', $sWidget);
    if ($sDelegatedClass == $sWidget) {
        // Пробуем получить делегата по старинке, для совместимости с LS
        // * LS-compatible * //
        $sDelegatedClass = E::ModulePlugin()->GetDelegate('block', $sWidget);
    }

    // Если делегатов нет, то определаем класс виджета
    if ($sDelegatedClass == $sWidget) {
        // Проверяем наличие класса виджета штатными средствами
        $sWidgetClass = E::ModuleWidget()->FileClassExists($sWidget, $sPlugin, true);
        if ($sWidgetClass) {
            // Проверяем делегирование найденного класса
            $sWidgetClass = E::ModulePlugin()->GetDelegate('widget', $sWidgetClass);
            if ($sPlugin) {
                $sPluginTplDir = Plugin::GetTemplateDir($sPlugin);
                $sTemplate = $sPluginTplDir . 'tpls/widgets/widget.' . $sWidgetName . '.tpl';
                if ($sFound = F::File_Exists('/widgets/widget.' . $sWidgetName . '.tpl', array($sPluginTplDir . 'tpls/', $sPluginTplDir))) {
                    $sTemplate = $sFound;
                } else {
                    // * LS-compatible * //
                    $sLsTemplate = Plugin::GetTemplateDir($sPlugin) . '/blocks/block.' . $sWidgetName . '.tpl';
                    if (F::File_Exists($sLsTemplate)) {
                        $sTemplate = $sLsTemplate;
                    }
                }
            } else {
                $sTemplate = E::ModulePlugin()->GetDelegate('template', 'widgets/widget.' . $sWidgetName . '.tpl');
                $sTemplate = F::File_Exists($sTemplate, $oSmartyTemplate->getTemplateDir());
                if (!$sTemplate) {
                    // * LS-compatible * //
                    $sLsTemplate = E::ModulePlugin()->GetDelegate('template', 'blocks/block.' . $sWidgetName . '.tpl');
                    if (F::File_Exists($sLsTemplate, $oSmartyTemplate->getTemplateDir())) {
                        $sTemplate = $sLsTemplate;
                    }
                }
            }
        } else {
            // * LS-compatible * //
            // Класс не найден
            if ($sPlugin) {
                // Если класс виджета не найден, то пытаемся по старинке задать класс "LS-блока"
                $sWidgetClass = 'Plugin' . ucfirst($sPlugin) . '_Block' . $sWidget;
            } else {
                // Если класс виджета не найден, то пытаемся по старинке задать класс "LS-блока"
                $sWidgetClass = 'Block' . $sWidget;
            }
            // Проверяем делигирование найденного класса
            $sWidgetClass = E::ModulePlugin()->GetDelegate('block', $sWidgetClass);
            if (!$sTemplate) {
                $sLsTemplate = E::ModulePlugin()->GetDelegate('template', 'blocks/block.' . $sWidgetName . '.tpl');
                if (F::File_Exists($sLsTemplate, $oSmartyTemplate->getTemplateDir())) {
                    $sTemplate = $sLsTemplate;
                }
            }
        }
    } else {
        $sWidgetClass = $sDelegatedClass;
    }

    // * Подключаем необходимый обработчик
    /** @var Widget $oWidgetHandler */
    $oWidgetHandler = new $sWidgetClass($aWidgetParams);

    // * Запускаем обработчик
    $sResult = $oWidgetHandler->Exec();

    // Если обработчик ничего не вернул, то рендерим шаблон
    if (!$sResult && $sTemplate) {
        if ($aWidgetParams) {
            $oSmartyTemplate->smarty->assign('aWidgetParams', $aWidgetParams);
        }
        $sResult = $oSmartyTemplate->smarty->fetch($sTemplate);
    }

    return $sResult;
}

// EOF