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
 * CRegularExpressionValidator class file.
 *
 * @author    Qiang Xue <qiang.xue@gmail.com>
 * @link      http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

/**
 * Валидатор текстовых данных на регулярное выражение
 *
 * @package engine.modules.validate
 * @since   1.0
 */
class ModuleValidate_EntityValidatorRegexp extends ModuleValidate_EntityValidator {
    /**
     * Проверяющее регулярное выражение
     *
     * @var string
     */
    public $pattern;
    /**
     * Инвертировать логику проверки на регулярное выражение
     *
     * @var bool
     **/
    public $not = false;
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
            return $this->getMessage(E::ModuleLang()->Get('validate_regexp_invalid_pattern', null, false), 'msg');
        }
        if ($this->allowEmpty && $this->isEmpty($sValue)) {
            return true;
        }

        if ($this->pattern === null) {
            return $this->getMessage(E::ModuleLang()->Get('validate_regexp_invalid_pattern', null, false), 'msg');
        }
        if ((!$this->not && !preg_match($this->pattern, $sValue))
            || ($this->not
                && preg_match(
                    $this->pattern, $sValue
                ))
        ) {
            return $this->getMessage(E::ModuleLang()->Get('validate_regexp_not_valid', null, false), 'msg');
        }
        return true;
    }
}

// EOF