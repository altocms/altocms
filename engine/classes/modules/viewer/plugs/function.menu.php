<?php

include_once __DIR__ . '/function.hook.php';

/**
 * Формирует меню
 *
 * @param $aParams
 * @param $oSmarty
 * @internal param $sMenuId
 * @internal param bool $sMenuClass
 * @internal param string $sActiveClass
 * @return string
 */
function smarty_function_menu($aParams, &$oSmarty) {

    // Меню нет - уходим
    /** @var ModuleMenu_EntityItem[] $aMenuItems Элементы меню */
    if (!isset($aParams['id']) || !($aMenu = Config::Get('menu.data.' . $aParams['id']))) {
        return '';
    }
    /** @var ModuleMenu_EntityItem[] $aMenuItems Элементы меню */
    if (!isset($aMenu['items'])) {
        return '';
    }

    $aMenuItems = $aMenu['items'];

    /** @var string $sMenu Запрашиваемое меню */
    $sMenu = '';

    // Установим класс меню, если он задан
    /** @var string $sMenuClass Класс меню */
    $sMenuClass = isset($aParams['class']) ? 'class="' . $aParams['class'] . '"' : '';
    if (!$sMenuClass) {
        $sMenuClass = isset($aMenu['class']) ? 'class="' . $aMenu['class'] . '"' : '';
    }


    // Открываем меню
    if (!isset($aParams['hideul'])) {
        $sMenu .= "<ul {$sMenuClass}>";
    }


    // Меню пустое
    $bEmpty = TRUE;

    // Заполним меню его элементами
    foreach ($aMenuItems as $sItemMenuName => $oMenuItem) {

        if (is_string($oMenuItem)) {
            smarty_function_hook(array_merge(array('run' => $sItemMenuName), isset($aParams[$oMenuItem]) ? $aParams[$oMenuItem] : array()), $oSmarty);
            continue;
        }

        // Сформируем класс элемента меню
        /** @var string $sItemClass Класс активного меню */
        $sItemClass = '';
        // Возьмем активной клас из конфига элемента меню
        $sItemClass .= ($oMenuItem->getActive() && $oMenuItem->getOptions() && ($oMenuItem->getOptions()->getActiveClass())) ? $oMenuItem->getOptions()->getActiveClass() : ($oMenuItem->getActive() ? 'active' : '');
        // Добавим класс активности из настроек вызова этого плагина
        $sItemClass .= ' ' . ($oMenuItem->getActive() && isset($aParams['active'])) ? $aParams['active'] : '';
        // Добавим класс из настроек
        $sItemClass .= ($oMenuItem->getOptions() && ($oMenuItem->getOptions()->getClass())) ? (' ' . $oMenuItem->getOptions()->getClass()) : '';
        // Добавим класс из вызова
        $sItemClass .= ' ' . (isset($aParams['item_class'])) ? $aParams['item_class'] : '';
        // Уберем пробелы и сформируем класс
        $sItemClass = $sItemClass ? 'class="' . trim($sItemClass) . '"' : '';


        // Получим класс ссылки, если нужно
        /** @var string $sItemClass Класс ссылки меню */
        $sItemLinkClass = '';
        // Возьмем активной клас из конфига элемента меню
        $sItemLinkClass .= ($oMenuItem->getActive() && $oMenuItem->getOptions() && ($oMenuItem->getOptions()->getActiveLinkClass())) ? $oMenuItem->getOptions()->getActiveLinkClass() : '';
        // Добавим класс активности из настроек вызова этого плагина
        $sItemLinkClass .= ' ' . ($oMenuItem->getActive() && isset($aParams['active_link'])) ? $aParams['active_link'] : '';
        // Добавим класс из настроек
        $sItemLinkClass .= ($oMenuItem->getOptions() && ($oMenuItem->getOptions()->getLinkClass())) ? (' ' . $oMenuItem->getOptions()->getLinkClass()) : '';
        // Добавим класс из вызова
        $sItemLinkClass .= ' ' . (isset($aParams['link_class'])) ? $aParams['link_class'] : '';
        // Уберем пробелы и сформируем класс
        $sItemLinkClass = $sItemLinkClass ? 'class="' . trim($sItemLinkClass) . '"' : '';


        // Получим иконку меню
        /** @var string $sIcon */
        $sIcon = '';
        $sIcon .= (($oMenuItem->getOptions() && ($sClass = $oMenuItem->getOptions()->getImageUrl())) ? "<img src='{$sClass}' class='{$oMenuItem->getOptions()->getImageClass()}' title='{$oMenuItem->getOptions()->getImageTitle()}'/>" : '');
        $sIcon .= (($oMenuItem->getOptions() && ($aSkin = $oMenuItem->getOptions()->getIconClass())) ? "<i class='{$aSkin}'></i>" : '');


        // Обработаем текст ссылки
        /** @var string $sItemText */
        $sItemText = $oMenuItem->getText();

        // Обработаем тайтл
        /** @var string $sItemText */
        $sItemTitle = $oMenuItem->getTitle() ? 'title="' . strip_tags($oMenuItem->getTitle()) . '"' : '';


        // Получим рекурсивно подменю, если нужно
        /** @var string $sSubMenu */
        $sSubMenu = $oMenuItem->getSubMenuId() ? smarty_function_menu(array('id' => $oMenuItem->getSubMenuId()), $oSmarty) : '';

        // получим data
        $sDataResult = '';
        if ($oMenuItem->getOptions() && ($aData = $oMenuItem->getOptions()->getData()) && is_array($aData)) {
            $sDataResult = '';
            foreach ($aData as $sDataName => $sDataValue) {
                $sDataResult .= " data-{$sDataName}='{$sDataValue}' ";
            }
            $sDataResult = trim($sDataResult);
        }
        // получим data
        $sLinkDataResult = '';
        if ($oMenuItem->getOptions() && ($aLinkData = $oMenuItem->getOptions()->getLinkData()) && is_array($aLinkData)) {
            $sLinkDataResult = '';
            foreach ($aLinkData as $sLinkDataName => $sLinkDataValue) {
                $sLinkDataResult .= " data-{$sLinkDataName}='{$sLinkDataValue}' ";
            }
            $sLinkDataResult = trim($sLinkDataResult);
        }

        $bShowItem = $oMenuItem->getShow()===FALSE?'style="display: none"':'';

        // Получим id ссылки меню
        $sLinkId = ($oMenuItem->getOptions() && ($aLinkId = $oMenuItem->getOptions()->getLinkId())) ? 'id="{$aLinkId}"' : '';

        // Получим title ссылки меню
        $sLinkTitle = ($oMenuItem->getOptions() && ($aLinkTitle = $oMenuItem->getOptions()->getLinkTitle())) ? 'title="{$aLinkTitle}"' : '';

        // Сформируем элемент меню
        $sCurrentLink = $oMenuItem->getText()?"<a {$sLinkId} {$sItemLinkClass} {$sLinkTitle} {$sLinkDataResult} href='{$oMenuItem->getUrl()}'>{$sIcon}{$sItemText}</a>":'';

        $sMenu .= "<li {$bShowItem} {$sItemClass} {$sItemTitle} {$sDataResult}>{$sCurrentLink}{$sSubMenu}</li>";

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