<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * Class ModuleTopic_EntityField
 *
 * @method setFieldId($iParam)
 * @method setFieldType($xParam)
 * @method setContentId($iParam)
 * @method setFieldName($xParam)
 * @method setFieldDescription($xParam)
 * @method setFieldRequired($xParam)
 *
 * @method getContentId()
 * @method getFieldName()
 * @method getFieldDescription()
 * @method getFieldRequired()
 *
 * @method int getFieldId()
 * @method string getFieldType
 *
 * @package modules.topic
 * @since   1.0
 */
class ModuleTopic_EntityField extends Entity {

    protected $aExtra = null;

    public function getCoord($value, $index) {

        if (isset($value[$index])) {
            return $value[$index];
        }
        return null;
    }

    public function getFieldValues() {

        if ($this->getOptionValue('select')) {
            return $this->getOptionValue('select');
        }
        return '';
    }

    public function getSelectVal() {

        if ($this->getOptionValue('select')) {
            $nl = nl2br($this->getOptionValue('select'));
            return explode('<br />', $nl);
        }
        return array();
    }

    protected function extractOptions() {

        if (is_null($this->aExtra)) {
            $this->aExtra = @unserialize($this->getOptions());
        }
    }

    public function getOptions() {

        return $this->getProp('field_options') ? $this->getProp('field_options') : serialize('');
    }

    public function setOptions($data) {

        $this->_aData['field_options'] = serialize($data);
    }

    public function setOptionValue($sName, $data) {

        $this->extractOptions();
        $this->aExtra[$sName] = $data;
        $this->setOptions($this->aExtra);
    }

    public function getOptionValue($sName) {

        $this->extractOptions();
        if (isset($this->aExtra[$sName])) {
            return $this->aExtra[$sName];
        }
        return null;
    }

}

// EOF