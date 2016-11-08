<?php

include_once __DIR__ . '/function.hook.php';

/**
 * Формирует меню
 *
 * @param $aParams
 * @param $oSmarty
 *
 * @internal param $sMenuId
 * @internal param bool $sMenuClass
 * @internal param string $sActiveClass
 *
 * @return string
 */
function smarty_function_menu($aParams, &$oSmarty = NULL) {

    // Меню нет - уходим
    /** ModuleMenu_EntityMenu $oMenu */
    if (empty($aParams['id']) || !($oMenu = E::ModuleMenu()->GetMenu($aParams['id'], $aParams))) {
        return '';
    }

    /** @var ModuleMenu_EntityItem[] $aMenuItems Элементы меню */
    $aMenuItems = $oMenu->GetItems();
    if (!$aMenuItems) {
        return '';
    }

    /** @var string $sMenu Запрашиваемое меню */
    $sMenu = '';

    // Установим класс меню, если он задан
    /** @var string $sMenuClass Класс меню */
    $sMenuClass = isset($aParams['class']) ? $aParams['class'] : $oMenu->GetCssClass();
    if ($sMenuClass) {
        $sMenuClass = 'class="' . $sMenuClass . '"';
    }

    // Открываем меню
    if (!isset($aParams['hideul'])) {
        $sMenu .= "<ul {$sMenuClass}>";
    }

    // Меню пустое
    $bEmpty = TRUE;

    // Заполним меню его элементами
    foreach ($aMenuItems as $sItemMenuName => $oMenuItem) {

        // Вызовем хук
        if (is_string($oMenuItem)) {
            $sMenu .= smarty_function_hook(array_merge(array('run' => $sItemMenuName), isset($aParams[$oMenuItem]) ? $aParams[$oMenuItem] : array()), $oSmarty);
            continue;
        }

        if (!$oMenuItem->isEnabled(isset($aParams['type'])?$aParams['type']:false)) {
            continue;
        }

        // Сформируем подменю если нужно
        $oMenuItem->setMenuId($aParams['id']);
        if (strpos($sMenuItemHtml = $oMenuItem->getHtml(), "[[submenu_") !== FALSE) {
            $sMenuItemHtml = preg_replace_callback('~\[\[submenu_(\S*)\]\]~', function ($sSubmenuId) use($oMenuItem, $oSmarty) {
                if (isset($sSubmenuId[1])) {
                    $sSubmenuId = $sSubmenuId[1];
                } else {
                    return '';
                }
                $aSettings = array('id' => $sSubmenuId, 'parent_item' => $oMenuItem);
                if (preg_match('~[a-f0-9]{10}~', $sSubmenuId)) {
                    $aSettings = array_merge($aSettings, array('class' => Config::Get('menu.submenu.class')));
                }
                $sSubmenuHtml = smarty_function_menu($aSettings, $oSmarty);
                if (!empty($sSubmenuHtml)) {
                    return $sSubmenuHtml;
                }
                return '';
            }, $sMenuItemHtml);
        }

        // Добавим html в вывод
        $sMenu .= $sMenuItemHtml;

        $bEmpty = FALSE;
    }

    // Закрываем меню
    if (!isset($aParams['hideul'])) {
        $sMenu .= '</ul>';
    }

    // Пустое меню не показываем, если не указано другое
    if (!isset($aParams['show_empty'])) {
        $aParams['show_empty'] = FALSE;
    }

    // Если меню не пустое, то покажем
    if (!($bEmpty && $aParams['show_empty'])) {
        return $sMenu;
    }

    return '';
}

// EOF