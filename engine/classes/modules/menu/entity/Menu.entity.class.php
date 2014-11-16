<?php

/**
 * Menu.entity.class.php
 * Файл сущности меню для модуля Menu
 *
 * @since     1.1 от 13.11.2014 19:55
 */
class ModuleMenu_EntityMenu extends Entity {

    /**
     * Элементы текущего меню
     * @var ModuleMenu_EntityItem[]
     */
    protected $_aItems = array();

    /**
     * Возвращает числовую позицию элемента
     *
     * @param $xPosition
     * @return int
     */
    private function _getIntPosition($xPosition) {

        if (is_int($xPosition)) {
            return $xPosition;
        }

        // Массив синонимов расположения
        $aMenuPosition = array(
            'first'  => 0,
            'last'   => count($this->_aItems),
            'middle' => (int)floor(count($this->_aItems) / 2),
        );

        // Позиция элемента
        return (int)eval(str_replace(
            array_keys($aMenuPosition),
            array_values($aMenuPosition),
            $xPosition)
        );
    }

    /**
     * Возвращает количество элементов в меню
     *
     * @return int
     */
    public function getLength() {
        return count($this->_aItems);
    }

    /**
     * Добавляет элемент меню в произвольное место списка меню
     *
     * @param ModuleMenu_EntityItem $oMenuItem
     * @param mixed $xPosition Позиция в списке. Может задаваться числом, а
     * может строками 'first'|'last'
     * @param bool $bReplace Заменять или добавлять
     * @return array
     */
    public function AddItem($xPosition, $oMenuItem, $bReplace = FALSE) {

        $this->_aItems = array_splice(
            $this->_aItems,
            $this->_getIntPosition($xPosition),
            (int)!$bReplace,
            array($oMenuItem)
        );

    }

    /**
     * Заменить элемент меню
     *
     * @param mixed $xPosition Позиция в списке.
     * @param ModuleMenu_EntityItem $oMenuItem
     */
    public function ReplaceItem($xPosition, $oMenuItem) {

        $this->AddItem($xPosition, $oMenuItem, TRUE);

    }

    /**
     * Удаляет элемент меню
     *
     * @param $xPosition Позиция в списке.
     * @return array
     */
    public function RemoveItem($xPosition) {

        $this->_aItems = array_splice(
            $this->_aItems,
            $this->_getIntPosition($xPosition),
            1
        );

    }

    /**
     * Возвращает элемент меню из указанной позиции
     *
     * @param $xPosition
     * @return bool|ModuleMenu_EntityItem
     */
    public function SelectItem($xPosition) {

        $iPosition = $this->_getIntPosition($xPosition);

        return isset($this->_aItems[$iPosition])
            ? $this->_aItems[$iPosition]
            : FALSE;

    }

    /**
     * Возвращает элемент меню из указанной позиции, синоним {@see SelectItem}
     *
     * @param $xPosition
     * @return bool|ModuleMenu_EntityItem
     */
    public function GetItem($xPosition) {
        return $this->SelectItem($xPosition);
    }

    /**
     * Возвращает все элементы меню
     *
     * @return ModuleMenu_EntityItem[]
     */
    public function GetItems() {
        return $this->_aItems;
    }

    /**
     * Устанавливает все элементы меню
     *
     * @param $aMenuItems
     */
    public function SetItems($aMenuItems) {
        $this->_aItems = $aMenuItems;
    }

}