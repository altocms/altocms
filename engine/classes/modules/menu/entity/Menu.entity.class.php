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
     * Переменная для кэширвоания описания элемента меню
     * @var null|bool
     */
    protected $_description = NULL;

    public function Init() {

        $this->_aItems = isset($this->_aData['items']) ? $this->_aData['items'] : NULL;
    }

    /**
     * @param      $sTextTemplate
     * @param null $sLang
     *
     * @return mixed
     */
    public function getLangText($sTextTemplate, $sLang = NULL) {

        return preg_replace_callback('~(\{\{\S*\}\})~', function ($sTextTemplatePart) {
            $sTextTemplatePart = array_shift($sTextTemplatePart);
            if (!is_null($sText = E::ModuleLang()->Get(substr($sTextTemplatePart, 2, strlen($sTextTemplatePart) - 4)))) {
                return $sText;
            }

            return $sTextTemplatePart;
        }, $sTextTemplate);

    }

    /**
     * Получает описание элемента меню
     *
     * @return bool|mixed|null
     */
    public function getDescription(){

        if ($this->_description) {
            return $this->_description;
        }
        $this->_description = isset($this->_aData['description']) ? $this->_aData['description'] : NULL;
        $this->_description = $this->getLangText($this->_description);

        return $this->_description;
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
     * @param mixed                 $xPosition Позиция в списке. Может задаваться числом, а
     *                                         может строками 'first'|'last'
     *
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
     *
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
     * @param string $sItemId
     *
     * @return ModuleMenu_EntityItem|null
     */
    public function GetItemById($sItemId) {

        return isset($this->_aItems[$sItemId]) ? $this->_aItems[$sItemId] : NULL;
    }

    /**
     * Удаляет элемент меню по его ID
     *
     * @param string|array|object $xItem
     * @param bool                $bClearCache
     */
    public function RemoveItemById($xItem, $bClearCache = false) {

        if (is_array($xItem)) {
            /** @var string|object $xItemId */
            foreach($xItem as $xItemId) {
                if (is_object($xItemId)) {
                    $sItemId = $xItemId->getId();
                } else {
                    $sItemId = (string)$xItemId;
                }
                if (isset($this->_aItems[$sItemId])) {
                    unset($this->_aItems[$sItemId]);
                }
            }
        } else {
            if (is_object($xItem)) {
                $sItemId = $xItem->getId();
            } else {
                $sItemId = (string)$xItem;
            }
            if (isset($this->_aItems[$sItemId])) {
                unset($this->_aItems[$sItemId]);
            }
        }

        if ($bClearCache) {
            Config::ResetCustomConfig('menu.data.' . $this->getId() . '.list');
            E::ModuleMenu()->SaveMenu($this);
        }
    }

    /**
     * Возвращает все элементы меню
     *
     * @return ModuleMenu_EntityItem[]
     */
    public function GetItems() {

        $aAllowedItems = $this->_aItems;
        $aAllowedData = $aAllowedData = array_values(Config::Get("menu.data.{$this->getId()}.init.fill.list"));
        if (count($aAllowedData) > 1 && isset($aAllowedData[0]) && $aAllowedData[0] == '*') {
            unset($aAllowedData[0]);
        }
        if (is_array($aAllowedData) && count($aAllowedData) == 1 && isset($aAllowedData[0]) && $aAllowedData[0] == '*') {
            return $aAllowedItems;
        }


        foreach ($aAllowedItems as $k => $v) {
            if (!in_array($k, $aAllowedData)) {
                unset($aAllowedItems[$k]);
            }
        }

        return $aAllowedItems;
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

// EOF