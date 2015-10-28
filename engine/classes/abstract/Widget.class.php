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
 * Абстрактный класс виджета
 * Это те блоки которые обрабатывают шаблоны Smarty перед выводом(например блок "Облако тегов")
 *
 * @package engine
 * @since 1.0
 */
abstract class Widget extends LsObject {

    /**
     * Список параметров блока
     *
     * @var array
     */
    protected $aParams = array();

    /**
     * При создании блока передаем в него параметры
     *
     * @param array $aParams Список параметров блока
     */
    public function __construct($aParams) {

        $this->aParams = $aParams;
    }

    /**
     * Возвращает параметр по имени
     *
     * @param   string  $sName      - Имя параметра
     * @param   mixed   $xDefault   - Значение параметра по умолчанию
     * @return  mixed
     */
    protected function GetParam($sName, $xDefault = null) {

        if (isset($this->aParams[$sName])) {
            return $this->aParams[$sName];
        } else {
            return $xDefault;
        }
    }

    /**
     * Return widget params
     *
     * @return array
     */
    protected function GetParams() {

        return $this->aParams;
    }

    /**
     * Метод запуска обработки блока.
     * Его необходимо определять в конкретном блоге.
     *
     * @abstract
     */
    abstract public function Exec();

    /**
     * Fetch widget template
     *
     * @param string $sTemplate
     * @param array  $aVars
     *
     * @return mixed
     */
    protected function Fetch($sTemplate, $aVars = array()) {

        $aVars = F::Array_Merge(array('aWidgetParams' => $this->aParams), $aVars);
        return E::ModuleViewer()->FetchWidget($sTemplate, $aVars);
    }
}

// EOF