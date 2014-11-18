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

    public function Init() {
        $this->_aItems = isset($this->_aData['items']) ? $this->_aData['items'] : NULL;
    }

    /**
     * Возвращает числовую позицию элемента
     *
     * @param $xPosition
     * @return int
     */
    private function _getIntPosition($xPosition) {

        if (is_int($xPosition)) {

            if ($xPosition < 0)
                $xPosition = 0;
            if ($xPosition >= $this->getLength())
                $xPosition = $xPosition - 1;

            return $xPosition;
        }

        // Массив синонимов расположения
        $aMenuPosition = array(
            'first'  => 0,
            'last'   => count($this->_aItems),
            'middle' => (int)floor(count($this->_aItems) / 2),
        );

        // Позиция элемента
        return (int)(str_replace(
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
     * @return array
     */
    public function AddItem($xPosition, $oMenuItem) {

        $xPosition = $this->_getIntPosition($xPosition);


        if ($this->GetItemById($oMenuItem->getId())) {
            return TRUE;
        }

        $aIds = array_keys($this->_aItems);
        $aVals = array_values($this->_aItems);
        $aResult = array();

        for ($i = 0; $i < $xPosition; $i++) {
            $aResult[$aIds[$i]] = $aVals[$i];
        }
        $aResult[$oMenuItem->getId()] = $oMenuItem;
        for ($i = $xPosition; $i < count($this->_aItems); $i++) {
            $aResult[$aIds[$i]] = $aVals[$i];
        }

        $this->_aItems = $aResult;

        return TRUE;
    }

    /**
     * Возвращает ид. меню
     * @return int|null
     */
    public function getId() {
        return isset($this->_aData['id']) ? $this->_aData['id'] : NULL;
    }

    /**
     * Заменить элемент меню
     *
     * @param mixed $xPosition Позиция в списке.
     * @param ModuleMenu_EntityItem $oMenuItem
     * @return bool
     */
    public function ReplaceItem($xPosition, $oMenuItem) {

        if ($this->GetItemById($oMenuItem->getId())) {
            return;
        }

        $xPosition = $this->_getIntPosition($xPosition);

        $aIds = array_keys($this->_aItems);
        $aVals = array_values($this->_aItems);
        $aResult = array();


        for ($i = 0; $i < count($this->_aItems); $i++) {
            if ($i == $xPosition) {
                $aResult[$oMenuItem->getId()] = $oMenuItem;
                continue;
            }
            $aResult[$aIds[$i]] = $aVals[$i];
        }

        $this->_aItems = $aResult;

    }

    /**
     * Удаляет элемент меню
     *
     * @param mixed $xPosition Позиция в списке.
     * @return array
     */
    public function RemoveItem($xPosition) {

        $xPosition = $this->_getIntPosition($xPosition);

        $aIds = array_keys($this->_aItems);
        $aVals = array_values($this->_aItems);
        $aResult = array();


        for ($i = 0; $i < count($this->_aItems); $i++) {
            if ($i == $xPosition) {
                continue;
            }
            $aResult[$aIds[$i]] = $aVals[$i];
        }

        $this->_aItems = $aResult;

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
     * Возвращает элемент меню из указанной позиции, синоним {@see SelectItem}
     *
     * @param $sItemId
     * @return bool|ModuleMenu_EntityItem
     */
    public function GetItemById($sItemId) {
        return isset($this->_aItems[$sItemId]) ? $this->_aItems[$sItemId] : NULL;
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