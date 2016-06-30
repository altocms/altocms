<?php

/**
 * Item.entity.class.php
 * Файл сущности элемента меню
 *
 * @since     1.1 от 13.11.2014 17:56
 */
class ModuleMenu_EntityItem extends Entity {

    /**
     * Переменная для кэширвоания обработки правил хранит
     * значение активности элемента меню
     * @var null|bool
     */
    protected $_isActive = NULL;

    /**
     * Переменная для кэширвоания обработки правил хранит
     * значение вывода элемента меню
     * @var null|bool
     */
    protected $_isDisplay = NULL;

    /**
     * Переменная для кэширвоания обработки правил хранит
     * значение видимости элемента меню
     * @var null|bool
     */
    protected $_isShow = NULL;

    /**
     * Переменная для кэширвоания обработки правил хранит
     * тайтл ссылки
     * @var null|bool
     */
    protected $_title = NULL;

    /**
     * Переменная для кэширвоания обработки правил хранит
     * текст ссылки
     * @var null|bool
     */
    protected $_text = NULL;

    /**
     * Переменная для кэширвоания описания элемента меню
     * @var null|bool
     */
    protected $_description = NULL;


    /**
     * Заполним меню HTML-кодом
     */
    public function Init() {

        if (!isset($this->_aData['item_flags'])) {
            $this->_aData['item_flags'] = $this->formHtml();
        }
    }

    /**
     * Возвращает путь ссылки
     * @return int|null
     */
    public function getUrl() {

        $sLink = isset($this->_aData['item_url']) ? Config::Get($this->_aData['item_url']) : NULL;
        if (!$sLink) {
            return isset($this->_aData['item_url']) ? $this->_aData['item_url'] : NULL;
        }

        return $sLink;
    }

    /**
     * Сопоставление заданных путей с текущим
     *
     * @param   string|array $aPaths
     * @param   bool $bDefault
     * @return  bool
     */
    protected function _checkPath($aPaths, $bDefault = TRUE) {

        if ($aPaths) {
            return R::CmpControllerPath($aPaths);
        }

        return $bDefault;
    }

    /**
     * @param string|array $aPlugins
     *
     * @return bool
     */
    public function checkPlugin($aPlugins) {

        if (is_string($aPlugins)) {
            $aPlugins = array($aPlugins);
        }

        $bResult = FALSE;
        foreach ($aPlugins as $sPluginName) {
            $bResult = $bResult || E::ActivePlugin($sPluginName);
            if ($bResult) {
                break;
            }
            continue;
        }

        return $bResult;
    }

    /**
     * Set parent menu
     *
     * @param ModuleMenu_EntityMenu $oMenu
     */
    public function setMenu($oMenu) {

        $this->setProp('_menu', !empty($oMenu) ? $oMenu : null);
    }

    /**
     * Get parent menu
     *
     * @return ModuleMenu_EntityMenu|null
     */
    public function getMenu() {

        return $this->getProp('_menu');
    }

    /**
     * @return ModuleMenu_EntityItem|null
     */
    public function getParentItem() {

        if ($oMenu = $this->getMenu()) {
            return $oMenu->getParentItem();
        }
        return null;
    }

    /**
     * Проверка на то, нужно выводить элемент или нет
     *
     * @param string|bool $sType Текущий контент
     *
     * @return bool
     */
    public function isEnabled($sType = FALSE) {

        // Проверим по доступности
        if ($this->getDisplay() === FALSE) {
            return FALSE;
        }

        // Проверим по скину
        if ($this->getOptions() && $this->getOptions()->getSkin() && $this->getOptions()->getSkin() != E::ModuleViewer()->GetConfigSkin()) {
            return FALSE;
        } else {
            // Если шкурка совпала, то проверим по теме
            if ($this->getOptions() && $this->getOptions()->getTheme() && $this->getOptions()->getTheme() != E::ModuleViewer()->GetConfigTheme()) {
                return FALSE;
            }
        }

        // Проверим по пути
        if (!($this->_checkPath($this->getOn(), TRUE) && !$this->_checkPath($this->getOff(), FALSE))) {
            return FALSE;
        }

        // Проверим по плагину
        if ($this->getOptions() && $this->getOptions()->getPlugin() && !$this->checkPlugin($this->getOptions()->getPlugin())) {
            return FALSE;
        }

        // Проверим по контексту
        if (($aContext = $this->getType()) && !in_array($sType, $aContext)) {
            return FALSE;
        }

        // Все проверки пройдены
        return TRUE;
    }

    /**
     * @param       $sTextTemplate
     * @param array $aReplace
     * @param null  $sLang
     *
     * @return string
     */
    public function getLangText($sTextTemplate, $aReplace = array(), $sLang = NULL) {

        return preg_replace_callback('~\{\{\S*\}\}~U', function ($aMatches) use ($aReplace, $sLang) {
            return E::ModuleLang()->Text($aMatches[0], $aReplace, true, $sLang);
        }, $sTextTemplate);
    }

    /**
     * Возвращает название текстовки для ссылки
     *
     * @return int|null
     */
    public function getTitle() {
        // Выведем кэш
        if (is_string($this->_title)) {
            return $this->_title;
        }

        /** @var callable[]|[][] $aActiveRule Правило вычисления активности */
        $aActiveRule = isset($this->_aData['item_title']) ? $this->_aData['item_title'] : '';

        $this->_title = $this->checkCustomRules($aActiveRule, TRUE);

        $this->_title = $this->getLangText($this->_title);

        return $this->_title;
    }

    /**
     * Получает описание элемента меню
     *
     * @return string|null
     */
    public function getDescription(){

        if ($this->_description) {
            return $this->_description;
        }
        $this->_description = isset($this->_aData['item_description']) ? $this->_aData['item_description'] : NULL;
        $this->_description = $this->getLangText($this->_description);

        return $this->_description;
    }

    /**
     * Возвращает надпись на ссылке
     *
     * @return string|null
     */
    public function getText() {

        // Выведем кэш
        if (is_string($this->_text)) {
            return $this->_text;
        }

        /** @var callable[]|[][] $aActiveRule Правило вычисления активности */
        $aActiveRule = isset($this->_aData['item_text']) ? $this->_aData['item_text'] : '';

        $this->_text = $this->checkCustomRules($aActiveRule, TRUE);

        $this->_text = $this->getLangText($this->_text);

        return $this->_text;

    }

    /**
     * Возвращает дополнительные опции ссылки
     *
     * @return ModuleMenu_EntityItemOptions|null
     */
    public function getOptions() {

        $aOptions = isset($this->_aData['item_options']) ? $this->_aData['item_options'] : NULL;
        if ($this->getSubMenuId() && preg_match('~submenu_[a-f0-9]{10}~', $this->getSubMenuId())) {
            $aOptions = E::GetEntity('Menu_ItemOptions', Config::Get('menu.submenu.options'));
        }
        return $aOptions;
    }

    /**
     * Возвращает массив страниц где ссылка отображается
     *
     * @return int|null
     */
    public function getOn() {

        return isset($this->_aData['item_on']) ? $this->_aData['item_on'] : NULL;
    }

    /**
     * Возвращает массив страниц где ссылка НЕ отображается
     *
     * @return int|null
     */
    public function getOff() {

        return isset($this->_aData['item_off']) ? $this->_aData['item_off'] : NULL;
    }

    /**
     * Возвращает массив страниц где ссылка НЕ отображается
     *
     * @return array|null
     */
    public function getType() {

        $xData = isset($this->_aData['item_type']) ? $this->_aData['item_type'] : NULL;
        if ($xData && !is_array($xData)) {
            $xData = array($xData);
        }

        return $xData;
    }

    /**
     * Возвращает флаг активности ссылки - включена она или нет
     *
     * @return bool|null
     */
    public function getDisplay() {

        // Выведем кэш
        if (is_bool($this->_isDisplay)) {
            return $this->_isDisplay;
        }

        /** @var callable[]|[][] $aActiveRule Правило вычисления активности */
        $aActiveRule = isset($this->_aData['item_display']) ? $this->_aData['item_display'] : TRUE;

        $this->_isDisplay = $this->checkCustomRules($aActiveRule);

        return $this->_isDisplay;
    }

    /**
     * Возвращает флаг активности ссылки - включена она или нет
     *
     * @return bool|null
     */
    public function getShow() {

        // Выведем кэш
        if (is_bool($this->_isShow)) {
            return $this->_isShow;
        }

        /** @var callable[]|[][] $aActiveRule Правило вычисления активности */
        $aActiveRule = isset($this->_aData['item_show']) ? $this->_aData['item_show'] : TRUE;

        $this->_isShow = $this->checkCustomRules($aActiveRule);

        return $this->_isShow;
    }

    /**
     * Возвращает идентификатор меню
     *
     * @return string
     */
    public function getId() {

        return isset($this->_aData['item_id']) ? $this->_aData['item_id'] : NULL;
    }

    /**
     * Возвращает идентификатор подменю
     *
     * @return string
     */
    public function getSubMenuId() {

        return isset($this->_aData['item_submenu']) ? $this->_aData['item_submenu'] : NULL;
    }

    /**
     * Возвращает конфигурацию элемента меню
     *
     * @return array
     */
    public function getItemConfig() {

        return $this->getProp('_cfg');
    }

    /**
     * Возвращает ссылку элемента меню
     *
     * @return string
     */
    public function getLink() {

        return $this->getUrl();
    }

    /**
     * Показывает активна ссылка или нет
     *
     * @return bool
     */
    public function getActive() {

        // Выведем кэш
        if (is_bool($this->_isActive)) {
            return $this->_isActive;
        }

        // if this is submenu and parent item is not active then this item cannot be active
        if ($oParentItem = $this->getParentItem()) {
            if (!$oParentItem->getActive()) {
                return false;
            }
        }

        /** @var callable[]|[][] $aActiveRule Правило вычисления активности */
        $aActiveRule = isset($this->_aData['item_active']) ? $this->_aData['item_active'] : FALSE;

        $this->_isActive = $this->checkCustomRules($aActiveRule);

        return $this->_isActive;
    }

    /**
     * Получаем html-код для этого элемента меню
     *
     * @return string
     */
    public function formHtml() {

        // Сформируем параметры-флаги для быстрого формирования html
        $aItemFlags = array();

        // Ссылка
        /*
        if ($this->getOptions()) {
            $aItemFlags['link_id'] = 'id="' . $this->getOptions()->getLinkId() . '"';
            $aItemFlags['link_active'] = $this->getOptions()->getActiveLinkClass();
            $aItemFlags['link_class'] = 'class="' . $this->getOptions()->getLinkClass() . ' [[link_active]]"';
        } else {
            $aItemFlags['link_id'] = '';
            $aItemFlags['link_active'] = 'active';
            $aItemFlags['link_class'] = '';
        }
        */
        $aItemFlags['link_id'] = ($this->getOptions() && ($aLinkId = $this->getOptions()->getLinkId())) ? "id='{$aLinkId}'" : '';
        $aItemFlags['link_active'] = ($this->getOptions() && ($this->getOptions()->getActiveLinkClass())) ? $this->getOptions()->getActiveLinkClass() : 'active';
        $aItemFlags['link_class'] = ($this->getOptions() && ($aLinkClass = $this->getOptions()->getLinkClass())) ? "class='{$aLinkClass} [[link_active]]'" : '';
        $sLinkDataResult = '';
        if ($this->getOptions() && ($aLinkData = $this->getOptions()->getLinkData()) && is_array($aLinkData)) {
            $sLinkDataResult = '';
            foreach ($aLinkData as $sLinkDataName => $sLinkDataValue) {
                $sLinkDataResult .= " data-{$sLinkDataName}='{$sLinkDataValue}' ";
            }
            $sLinkDataResult = trim($sLinkDataResult);
        }
        $aItemFlags['link_data'] = $sLinkDataResult;
        $aItemFlags['item_icon'] = (($this->getOptions() && ($aSkin = $this->getOptions()->getIconClass())) ? "<i class='{$aSkin}'></i>" : '');
        $aItemFlags['item_image_class'] = ($this->getOptions() && $this->getOptions()->getImageClass()) ? "class='{$this->getOptions()->getImageClass()}'" : '';
        $aItemFlags['item_url'] = $this->getUrl();

        // Подменю
        $aItemFlags['item_submenu'] = $this->getSubMenuId() ? "[[submenu_{$this->getSubMenuId()}]]" : '';

        // Элемент меню
        $aItemFlags['item_active'] = ($this->getOptions() && ($this->getOptions()->getActiveClass())) ? $this->getOptions()->getActiveClass() : 'active';
        $aItemFlags['item_class'] = ($this->getOptions() && ($sClass = $this->getOptions()->getClass())) ? "class='{$sClass} [[link_active]]'" : '';

        // получим data
        $sDataResult = '';
        if ($this->getOptions() && ($aData = $this->getOptions()->getData()) && is_array($aData)) {
            $sDataResult = '';
            foreach ($aData as $sDataName => $sDataValue) {
                $sDataResult .= " data-{$sDataName}='{$sDataValue}' ";
            }
            $sDataResult = trim($sDataResult);
        }
        $aItemFlags['item_data'] = $sDataResult;

        return $aItemFlags;
    }

    /**
     * @return string
     */
    public function getHtml() {

        // Сформируем динамические параметры
        $aParams = array();

        // Ссылка
        $sActive = FALSE;
        if ($aParams['[[link_text]]'] = $this->getText()) {
            // Ссылка есть
            $aParams['[[link_image]]'] = '';
            if (($this->getOptions() && ($sImageUrl = $this->getOptions()->getImageUrl()))) {
                $sImageAlt = ($this->getOptions() && ($sImageTitle = $this->getOptions()->getImageTitle())) ? "alt='{$sImageTitle}'" : '';
                $aParams['[[link_image]]'] = "<img {$this->_aData['item_flags']['item_image_class']} {$sImageAlt} src='$sImageUrl' />";
            }
            $aParams['[[link_title]]'] = '';
            if (($this->getOptions() && ($sLinkTitle = $this->getOptions()->getLinkTitle()))) {
                $aParams['[[link_title]]'] = "title='{$sLinkTitle}'";
            }
            $sActive = $this->getActive();
            if ($sActive && $this->_aData['item_flags']['link_class']) {
                $sLinkClass = str_replace('[[link_active]]', $this->_aData['item_flags']['link_active'], $this->_aData['item_flags']['link_class']);
            } else if ($sActive && !$this->_aData['item_flags']['link_class']) {
                $sLinkClass = "class='{$this->_aData['item_flags']['link_active']}'";
            } else if (!$sActive && $this->_aData['item_flags']['link_class']) {
                $sLinkClass = str_replace('[[link_active]]', '', $this->_aData['item_flags']['link_class']);
            } else {
                $sLinkClass = '';
            }
            $aParams['[[link_class]]'] = $sLinkClass;

            $sCurrentLink = "<a {$this->_aData['item_flags']['link_id']} [[link_class]] [[link_title]] {$this->_aData['item_flags']['link_data']} href='{$this->_aData['item_flags']['item_url']}'>{$this->_aData['item_flags']['item_icon']}[[link_image]][[link_text]]</a>";
        } else {
            $sCurrentLink = '';
        }

        $aParams['[[item_title]]'] = '';
        if ($sItemTitle = $this->getTitle()) {
            $aParams['[[item_title]]'] = "title='{$sItemTitle}'";
        }

        if ($sActive && $this->_aData['item_flags']['item_class']) {
            $sItemClass = str_replace('[[link_active]]', $this->_aData['item_flags']['item_active'], $this->_aData['item_flags']['item_class']);
        } else if ($sActive && !$this->_aData['item_flags']['item_class']) {
            $sItemClass = "class='{$this->_aData['item_flags']['item_active']}'";
        } else if (!$sActive && $this->_aData['item_flags']['item_class']) {
            $sItemClass = str_replace('[[link_active]]', '', $this->_aData['item_flags']['item_class']);
        } else {
            $sItemClass = '';
        }
        $aParams['[[item_class]]'] = $sItemClass;

        $aParams['[[item_show]]'] = '';
        if (!$this->getShow()) {
            $aParams['[[item_show]]'] = "style='display: none;'";
        }

        $sHtml = "<li [[item_show]] [[item_class]] [[item_title]] {$this->_aData['item_flags']['item_data']}>{$sCurrentLink}{$this->_aData['item_flags']['item_submenu']}</li>";

        $sHtml = str_replace(array_keys($aParams), array_values($aParams), $sHtml);

        return $sHtml;
    }

    /**
     * @param string $sKey
     * @param mixed  $xValue
     */
    public function SetConfig($sKey, $xValue) {

        if (is_array($xValue)) {
            // Only scalar can be used as end value
            array_walk_recursive($xValue, function(&$xV){
                if (!is_scalar($xV)) {
                    $xV = null;
                }
            });
        }
        if ($sKey && (is_scalar($xValue) || is_array($xValue))) {
            $aKeys = explode('.', $sKey);
            $aData = &$this->_aData;
            $aCfg = &$this->_aData['_cfg'];
            foreach ($aKeys as $sSubKey) {
                $aData = &$aData[$sSubKey];
                $aCfg = &$aCfg[$sSubKey];
            }
            $aData = $xValue;
            $aCfg = $xValue;
        }
    }

}

// EOF