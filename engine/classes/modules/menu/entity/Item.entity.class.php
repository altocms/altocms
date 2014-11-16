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
     * Возвращает путь ссылки
     * @return int|null
     */
    public function getUrl() {
        if (isset($this->_aData['item_url'])) {
            if (strpos($this->_aData['item_url'], '___') === FALSE) {
                return $this->_aData['item_url'];
            }

            return Config::Get($this->_aData['item_url']);
        }

        return '';
    }

    public function getLangText($sTextTemplate, $sLang = NULL) {

        return preg_replace_callback('~(\{\{\S*\}\})~', function($sTextTemplatePart){
            $sTextTemplatePart = array_shift($sTextTemplatePart);
            if (!is_null($sText = E::Lang_Get(substr($sTextTemplatePart, 2, strlen($sTextTemplatePart) - 4)))) {
                return $sText;
            }
            return $sTextTemplatePart;
        }, $sTextTemplate);

    }

    /**
     * Возвращает название текстовки для ссылки
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

        return $this->getLangText($this->_title);
    }

    /**
     * Возвращает надпись на ссылке
     * @return int|null
     */
    public function getText() {

        // Выведем кэш
        if (is_string($this->_text)) {
            return $this->_text;
        }

        /** @var callable[]|[][] $aActiveRule Правило вычисления активности */
        $aActiveRule = isset($this->_aData['item_text']) ? $this->_aData['item_text'] : '';

        $this->_text = $this->checkCustomRules($aActiveRule, TRUE);

        return $this->getLangText($this->_text);

    }

    /**
     * Возвращает дополнительные опции ссылки
     * @return ModuleMenu_EntityItemOptions|null
     */
    public function getOptions() {
        return isset($this->_aData['item_options']) ? $this->_aData['item_options'] : NULL;
    }

    /**
     * Возвращает массив страниц где ссылка отображается
     * @return int|null
     */
    public function getOn() {
        return isset($this->_aData['item_on']) ? $this->_aData['item_on'] : NULL;
    }

    /**
     * Возвращает массив страниц где ссылка НЕ отображается
     * @return int|null
     */
    public function getOff() {
        return isset($this->_aData['item_off']) ? $this->_aData['item_off'] : NULL;
    }

    /**
     * Возвращает флаг активности ссылки - включена она или нет
     * @return int|null
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
     * @return int|null
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
     * @return mixed
     */
    public function getMenuId() {
        return isset($this->_aData['item_id']) ? $this->_aData['item_id'] : NULL;
    }

    /**
     * Возвращает идентификатор меню
     * @return mixed
     */
    public function getSubMenuId() {
        return isset($this->_aData['item_submenu']) ? $this->_aData['item_submenu'] : NULL;
    }

    /**
     * Возвращает идентификатор меню
     * @return mixed
     */
    public function getMenuConfig() {
        return Config::Get('view.menu.' . $this->getMenuId());
    }

    /**
     * Показывает активна ссылка или нет
     * @return mixed
     */
    public function getActive() {

        // Выведем кэш
        if (is_bool($this->_isActive)) {
            return $this->_isActive;
        }

        /** @var callable[]|[][] $aActiveRule Правило вычисления активности */
        $aActiveRule = isset($this->_aData['item_active']) ? $this->_aData['item_active'] : FALSE;

        $this->_isActive = $this->checkCustomRules($aActiveRule);

        return $this->_isActive;

    }

}