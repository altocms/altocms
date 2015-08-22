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
 * Объект типа контента
 *
 * @method setContentTitle($xParam)
 * @method setContentTitleDecl($xParam)
 * @method setContentUrl($xParam)
 * @method setContentCandelete($xParam)
 * @method setContentAccess($xParam)
 * @method setContentSort($xParam)
 *
 * @method getContentId()
 * @method getContentTitle()
 * @method getContentTitleDecl()
 * @method getContentUrl()
 * @method getContentCandelete()
 * @method getContentAccess()
 *
 * @package modules.topic
 * @since   1.0
 *
 */
class ModuleTopic_EntityContentType extends Entity {

    protected $aFields = null;

    protected $aExtra = null;

    public function getId() {

        return $this->getContentId();
    }

    /**
     * Get all additional fields of the content type
     *
     * @return ModuleTopic_EntityField[]
     */
    public function getFields() {

        if (is_null($this->aFields)) {
            $aFilter = array();
            $aFilter['content_id'] = $this->getContentId();
            $this->aFields = E::ModuleTopic()->GetContentFields($aFilter);
        }
        return $this->aFields;
    }

    /**
     * Get additional field by ID
     *
     * @param $iFieldId
     *
     * @return ModuleTopic_EntityField|null
     */
    public function getField($iFieldId) {

        $aFields = $this->getFields();

        return isset($aFields[$iFieldId]) ? $aFields[$iFieldId] : null;
    }

    public function setFields($data) {

        $this->aFields = $data;
    }

    public function isAllow($sAllow) {

        if ($sAllow == 'poll') {
            $bResult = $this->getExtraValue('poll') || $this->getExtraValue('question');
        } else {
            $bResult = $this->getExtraValue($sAllow);
        }
        return (bool)$bResult;
    }

    /**
     * Возвращает сериализованные строку дополнительный данных типа контента
     *
     * @return string
     */
    public function getExtra() {

        return $this->getProp('content_config') ? $this->getProp('content_config') : serialize('');
    }

    /**
     * Извлекает сериализованные данные
     */
    protected function extractExtra() {

        if (is_null($this->aExtra)) {
            $this->aExtra = @unserialize($this->getExtra());
        }
    }

    /**
     * Устанавливает значение нужного параметра
     *
     * @param string $sName    Название параметра/данных
     * @param mixed  $data     Данные
     */
    public function setExtraValue($sName, $data) {

        $this->extractExtra();
        $this->aExtra[$sName] = $data;
        $this->setExtra($this->aExtra);
    }

    /**
     * Извлекает значение параметра
     *
     * @param string $sName    Название параметра
     *
     * @return null|mixed
     */
    public function getExtraValue($sName) {

        $this->extractExtra();
        if (isset($this->aExtra[$sName])) {
            return $this->aExtra[$sName];
        }
        return null;
    }

    public function getTemplateName() {

        return $this->getContentUrl();
    }

    /**
     * @param  string $sMode
     *
     * @return string
     */
    public function getTemplate($sMode = null) {

        $sTemplate = $this->getProp('_template_' . $sMode);
        if (!$sTemplate) {
            $sTemplate = 'topic.type_' . $this->getTemplateName() . '-' . $sMode . '.tpl';
            if (!E::ModuleViewer()->TemplateExists('topics/' . $sTemplate)) {
                $sTemplate = 'topic.type_default-' . $sMode . '.tpl';
            }
            $this->setProp('_template_' . $sMode, $sTemplate);
        }
        return $sTemplate;
    }

    /**
     * Устанавливает сериализованную строчку дополнительных данных
     *
     * @param string $data
     */
    public function setExtra($data) {

        $this->_aData['content_config'] = serialize($data);
    }

    /**
     * Проверяет доступность на создание текущего типа контента
     */
    public function isAccessible() {

        if ($oUser = E::ModuleUser()->GetUserCurrent()) {
            if ($this->getContentAccess() == ModuleTopic::CONTENT_ACCESS_ONLY_ADMIN && !$oUser->isAdministrator()) {
                return false;
            } else {
                return true;
            }
        }
        return false;

    }

}

// EOF