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
 * CStringValidator class file.
 *
 * @author    Qiang Xue <qiang.xue@gmail.com>
 * @link      http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

/**
 * Валидатор текстовых данных на длину
 *
 * @package engine.modules.validate
 * @since   1.0
 */
class ModuleValidate_EntityValidatorString extends ModuleValidate_EntityValidator {
    /**
     * Максимальня длина строки
     *
     * @var int
     */
    public $max;
    /**
     * Минимальная длина строки
     *
     * @var int
     */
    public $min;
    /**
     * Конкретное значение длины строки
     *
     * @var int
     */
    public $is;
    /**
     * Кастомное сообщение об ошибке при короткой строке
     *
     * @var string
     */
    public $msgTooShort;
    /**
     * Кастомное сообщение об ошибке при слишком длинной строке
     *
     * @var string
     */
    public $msgTooLong;
    /**
     * Допускать или нет пустое значение
     *
     * @var bool
     */
    public $allowEmpty = true;

    /**
     * Запуск валидации
     *
     * @param mixed $sValue    Данные для валидации
     *
     * @return bool|string
     */
    public function validate($sValue) {

        if (is_array($sValue)) {
            return $this->getMessage(
                E::ModuleLang()->get('validate_string_too_short', null, false), 'msgTooShort', array('min' => $this->min)
            );
        }
        if ($this->allowEmpty && $this->isEmpty($sValue)) {
            return true;
        }

        $iLength = mb_strlen($sValue, 'UTF-8');

        if ($this->min !== null && $iLength < $this->min) {
            return $this->getMessage(
                E::ModuleLang()->get('validate_string_too_short', null, false), 'msgTooShort', array('min' => $this->min)
            );
        }
        if ($this->max !== null && $iLength > $this->max) {
            return $this->getMessage(
                E::ModuleLang()->get('validate_string_too_long', null, false), 'msgTooLong', array('max' => $this->max)
            );
        }
        if ($this->is !== null && $iLength !== $this->is) {
            return $this->getMessage(
                E::ModuleLang()->get('validate_string_no_lenght', null, false), 'msg', array('length' => $this->is)
            );
        }
        return true;
    }
}

// EOF