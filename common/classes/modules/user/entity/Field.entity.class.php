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
 * Сущность пользовательского поля у пользователя
 *
 * @package modules.user
 * @since   1.0
 */
class ModuleUser_EntityField extends Entity {
    /**
     * Возвращает ID поля
     *
     * @return int|null
     */
    public function getId() {

        return $this->getProp('id');
    }

    /**
     * Возвращает имя поля(уникальное)
     *
     * @return string|null
     */
    public function getName() {

        return $this->getProp('name');
    }

    /**
     * Возвращает тип поля
     *
     * @return string|null
     */
    public function getType() {

        return $this->getProp('type');
    }

    /**
     * Возвращает заголовок/описание поля
     *
     * @return string|null
     */
    public function getTitle() {

        return $this->getLangTextProp('title');
    }

    /**
     * Возвращает паттерн подстановки поля
     *
     * @return string|null
     */
    public function getPattern() {

        return $this->getProp('pattern');
    }

    /**
     * Возвращает значение поля у пользователя
     *
     * @param bool $bEscapeValue    Экранировать значение
     * @param bool $bTransformed    Применять паттерн или нет
     *
     * @return string
     */
    public function getValue($bEscapeValue = false, $bTransformed = false) {

        $sValue = $this->getProp('value');
        if (!$sValue) {
            return $sValue;
        }
        if ($bEscapeValue) {
            $sValue = htmlspecialchars($sValue);
        }

        if ($bTransformed) {
            if (!($sPattern = $this->getProp('pattern'))) {
                return $sValue;
            }
            $sValue = str_replace('{*}', $sValue, $sPattern);
            /**
             * Грязный хак сайта в профиле (
             * @todo Сделать валидацию полей в профиле
             */
            if ($this->getName() == 'www') {
                $sValue = str_replace(
                    array('http://http://', 'http://https://'), array('http://', 'https://'), $sValue
                );
            }
        }

        return $sValue;
    }


    /**
     * Устанавливает ID поля
     *
     * @param int $iId
     */
    public function setId($iId) {

        $this->setProp('id', $iId);;
    }

    /**
     * Устанавливает имя поля(уникальное)
     *
     * @param string $sName
     */
    public function setName($sName) {

        $this->setProp('name', $sName);;
    }

    /**
     * Устанавливает тип поля
     *
     * @param string $sName
     */
    public function setType($sName) {

        $this->setProp('type', $sName);;
    }

    /**
     * Устанавливает заголовок/описание поля
     *
     * @param string $sTitle
     */
    public function setTitle($sTitle) {

        $this->setProp('title', $sTitle);;
    }

    /**
     * Устанавливает паттерн подстановки поля
     *
     * @param string $sPattern
     */
    public function setPattern($sPattern) {

        $this->setProp('pattern', $sPattern);;
    }

    /**
     * Устанавливает значение поля у пользователя
     *
     * @param string $sValue
     */
    public function setValue($sValue) {

        $this->setProp('value', $sValue);;
    }

}

// EOF